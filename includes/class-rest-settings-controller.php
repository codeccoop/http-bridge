<?php

namespace HTTP_BRIDGE;

use Exception;
use Error;
use WP_Error;
use WP_REST_Server;
use WPCT_PLUGIN\REST_Settings_Controller as Base_Controller;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * REST API Controller.
 */
class REST_Settings_Controller extends Base_Controller
{
    /**
     * REST API version handler.
     *
     * @var string $version REST API version.
     */
    private static $user = null;

    /**
     * Authorization error handler.
     *
     * @var WP_Error|null $auth_error authorization error.
     */
    private static $auth_error = null;

    /**
     * Authorization header getter.
     *
     * @return string $token Bearer token.
     */
    private static function get_jwt_auth()
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = sanitize_text_field(
                wp_unslash($_SERVER['HTTP_AUTHORIZATION'])
            );
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth_header = sanitize_text_field(
                wp_unslash($_SERVER['HTTP_AUTHORIZATION'])
            );
        }

        if (!isset($auth_header)) {
            throw new Exception('Authorization header not found', 400);
        }

        [$token] = sscanf($auth_header, 'Bearer %s');
        if (!$token) {
            throw new Exception('Authorization header malformed', 400);
        }

        return $token;
    }

    /**
     * Binds auth checks to rest api hooks and registers routes.
     */
    protected function construct(...$args)
    {
        parent::construct(...$args);

        add_action('determine_current_user', static function ($user_id) {
            return self::determine_current_user($user_id);
        });

        add_filter(
            'rest_pre_dispatch',
            static function ($result, $server, $request) {
                return self::rest_pre_dispatch($result, $server, $request);
            },
            10,
            3
        );

        add_action('rest_api_init', static function () {
            self::init();
        });
    }

    /**
     * Callback to the `rest_api_init` hook. Registers custom REST API endpoint routes
     * to handle JWT authorization and validation.
     */
    protected static function init()
    {
        parent::init();

        register_rest_route('http-bridge/v1', '/jwt/auth', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => static function () {
                return self::jwt_auth();
            },
            'permission_callback' => static function ($request) {
                return self::jwt_auth_permission_callback($request);
            },
        ]);

        register_rest_route('http-bridge/v1', '/jwt/validate', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => static function () {
                return self::jwt_validate();
            },
            'permission_callback' => static function () {
                return self::jwt_validate_permission_callback();
            },
        ]);

        register_rest_route('http-bridge/v1', '/oauth/grant', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => static function ($request) {
                return self::oauth_grant($request);
            },
            'permission_callback' => [self::class, 'permission_callback'],
            'args' => ['credential' => Credential::schema()],
        ]);

        register_rest_route('http-bridge/v1', '/oauth/revoke', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => static function ($request) {
                return self::oauth_revoke($request);
            },
            'permission_callback' => [self::class, 'permission_callback'],
            'args' => ['credential' => Credential::schema()],
        ]);

        register_rest_route('http-bridge/v1', '/oauth/redirect', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => static function ($request) {
                return self::oauth_redirect($request);
            },
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Auth callback.
     *
     * @return array
     */
    private static function jwt_auth()
    {
        $issuedAt = time();
        $notBefore = $issuedAt;

        $expire = apply_filters(
            'http_bridge_jwt_auth_expire',
            $issuedAt + 60 * 60 * 24 * 7,
            $issuedAt
        );

        $claims = [
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire,
            'data' => [
                'user_id' => self::$user->data->ID,
            ],
        ];

        $token = (new JWT())->encode($claims);

        return [
            'token' => $token,
            'user_email' => self::$user->data->user_email,
            'user_login' => self::$user->data->user_login,
            'display_name' => self::$user->data->display_name,
        ];
    }

    /**
     * Validate callback.
     *
     * @return array
     */
    private static function jwt_validate()
    {
        $token = self::get_jwt_auth();

        return [
            'token' => $token,
            'user_email' => self::$user->data->user_email,
            'user_login' => self::$user->data->user_login,
            'display_name' => self::$user->data->display_name,
        ];
    }

    /**
     * Performs auth requests permisison checks.
     *
     * @return boolean
     */
    private static function jwt_auth_permission_callback($request)
    {
        $data = $request->get_json_params();

        if ($data === null) {
            return self::bad_request(__('Invalid JSON data', 'http-bridge'));
        }

        if (!(isset($data['username']) && isset($data['password']))) {
            return self::bad_request(
                __('Missing login credentials', 'http-bridge')
            );
        }

        $user = wp_authenticate($data['username'], $data['password']);
        if (is_wp_error($user)) {
            return self::unauthorized(__('Invalid credentials', 'http-bridge'));
        }

        self::$user = $user;
        return true;
    }

    /**
     * Performs validation requests permission checks.
     *
     * @return boolean
     */
    private static function jwt_validate_permission_callback()
    {
        try {
            $token = self::get_jwt_auth();
        } catch (Exception $e) {
            return self::unauthorized($e->getMessage());
        }

        try {
            $payload = (new JWT())->decode($token);
        } catch (Exception) {
            return self::unauthorized(
                __('Invalid authorization token', 'http-bridge')
            );
        } catch (Error) {
            return self::internal_server_error(
                __('Internal Server Error', 'http-bridge')
            );
        }

        if ($payload['iss'] !== get_bloginfo('url')) {
            return self::unauthorized(
                __('The iss do not match with this server', 'http-bridge')
            );
        }

        $now = time();
        if ($payload['exp'] <= $now) {
            return self::unauthorized(
                __('The token is expired', 'http-bridge')
            );
        }

        if ($payload['nbf'] >= $now) {
            return self::unauthorized(
                __('The token is not valid yet', 'http-bridge')
            );
        }

        if (!isset($payload['data']['user_id'])) {
            return self::unauthorized(
                __('User ID not found in the token', 'http-bridge')
            );
        }

        self::$user = get_user_by('ID', (int) $payload['data']['user_id']);
        return true;
    }

    /**
     * Determine current user from bearer authentication.
     *
     * @param int|null $user_id Already identified user ID.
     *
     * @return int|null Identified user ID.
     */
    private static function determine_current_user($user_id)
    {
        $rest_api_slug = rest_get_url_prefix();
        $requested_url = isset($_SERVER['REQUEST_URI'])
            ? sanitize_url(wp_unslash($_SERVER['REQUEST_URI']))
            : '';

        $is_rest_request =
            (defined('REST_REQUEST') && REST_REQUEST) ||
            strpos($requested_url, $rest_api_slug);

        if ($is_rest_request && $user_id) {
            return $user_id;
        }

        $validate_uri = strpos($requested_url, 'http-bridge/v1/jwt/validate');

        if ($validate_uri > 0) {
            return $user_id;
        }

        try {
            $auth = self::get_jwt_auth();
        } catch (Exception) {
            return $user_id;
        }

        try {
            $payload = (new JWT())->decode($auth);
        } catch (Exception $e) {
            if ($e->getMessage() === 'Invalid token format') {
                self::$auth_error = self::unauthorized(
                    $e->getMessage(),
                    $e->getCode()
                );
            }

            return $user_id;
        } catch (Error) {
            return $user_id;
        }

        return (int) $payload['data']['user_id'];
    }

    /**
     * Abort rest dispatches if auth errors.
     *
     * @return object|WP_Error REST Request instance.
     */
    private static function rest_pre_dispatch($result)
    {
        if (is_wp_error($result) || is_wp_error(self::$auth_error)) {
            return self::$auth_error;
        }

        return $result;
    }

    private static function oauth_grant($request)
    {
        $data = $request['credential'];
        $credential = new Credential($data);
        $result = $credential->oauth_grant_transient();

        if (!$result) {
            return self::bad_request();
        }

        return ['success' => true];
    }

    private static function oauth_revoke($request)
    {
        $data = $request['credential'];
        $credential = new Credential($data);
        $result = $credential->oauth_revoke();

        if (!$result) {
            return self::bad_request();
        }

        return ['success' => true];
    }

    private static function oauth_redirect($request)
    {
        $credential = Credential::get_transient();
        if (!$credential) {
            wp_die(__('OAuth redirect timeout error', 'http-bridge'));
            return;
        }

        $result = $credential->oauth_redirect_callback($request);
        if (!$result) {
            wp_die(__('Invalid OAuth redirect callback', 'http-bridge'));
            return;
        }

        $url = site_url() . '/wp-admin/options-general.php?page=http-bridge&tab=http';

        if (wp_redirect($url)) {
            exit(302);
        }

        return ['success' => false];
    }
}
