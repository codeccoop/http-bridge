<?php

namespace HTTP_BRIDGE;

use Exception;
use Error;
use WP_Error;
use WP_REST_Server;
use WPCT_ABSTRACT\REST_Settings_Controller as Base_Controller;

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
    private static function get_auth()
    {
        $auth_header = isset($_SERVER['HTTP_AUTHORIZATION'])
            ? sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZATION']))
            : (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])
                ? sanitize_text_field(
                    wp_unslash($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])
                )
                : null);

        if ($auth_header === null) {
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

        $namespace = self::namespace();
        $version = self::version();

        register_rest_route("{$namespace}/v{$version}", '/auth/', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => static function () {
                return self::auth();
            },
            'permission_callback' => static function ($request) {
                return self::auth_permission_callback($request);
            },
        ]);

        register_rest_route("{$namespace}/v{$version}", '/validate-token/', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => static function () {
                return self::validate();
            },
            'permission_callback' => static function () {
                return self::validate_permission_callback();
            },
        ]);
    }

    /**
     * Auth callback.
     *
     * @return array<string, string> Login token.
     */
    private static function auth()
    {
        $issuedAt = time();
        $notBefore = apply_filters(
            'wpct_http_auth_not_before',
            $issuedAt,
            $issuedAt
        );
        $expire = apply_filters(
            'wpct_http_auth_expire',
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

        return apply_filters(
            'wpct_http_auth_response',
            [
                'token' => $token,
                'user_email' => self::$user->data->user_email,
                'user_login' => self::$user->data->user_login,
                'display_name' => self::$user->data->display_name,
            ],
            self::$user
        );
    }

    /**
     * Validate callback.
     *
     * @return array<string, string> $token Validated token.
     */
    private static function validate()
    {
        $token = self::get_auth();
        return apply_filters(
            'wpct_http_validate_response',
            [
                'token' => $token,
                'user_email' => self::$user->data->user_email,
                'user_login' => self::$user->data->user_login,
                'display_name' => self::$user->data->display_name,
            ],
            self::$user
        );
    }

    /**
     * Performs auth requests permisison checks.
     *
     * @return boolean $success Request has permisisons.
     */
    private static function auth_permission_callback($request)
    {
        $data = $request->get_json_params();
        if ($data === null) {
            return self::error(
                'rest_bad_request',
                __('Invalid JSON data', 'http-bridge'),
                400
            );
        }

        if (!(isset($data['username']) && isset($data['password']))) {
            return self::error(
                'rest_bad_request',
                __('Missing login credentials', 'http-bridge'),
                400
            );
        }

        $user = wp_authenticate($data['username'], $data['password']);
        if (is_wp_error($user)) {
            return self::error(
                'rest_unauthorized',
                __('Invalid credentials', 'http-bridge'),
                403
            );
        }

        self::$user = $user;
        return true;
    }

    /**
     * Performs validation requests permission checks.
     *
     * @return boolean $success Request has permissions.
     */
    private static function validate_permission_callback()
    {
        try {
            $token = self::get_auth();
        } catch (Exception $e) {
            return self::error(
                'rest_unauthorized',
                $e->getMessage(),
                $e->getCode()
            );
        }

        try {
            $payload = (new JWT())->decode($token);
        } catch (Exception) {
            return self::error(
                'rest_unauthorized',
                __('Invalid authorization token', 'http-bridge'),
                403
            );
        } catch (Error) {
            return self::error(
                'rest_internal_error',
                __('Internal Server Error', 'http-bridge'),
                500
            );
        }

        if ($payload['iss'] !== get_bloginfo('url')) {
            return self::error(
                'rest_unauthorized',
                __('The iss do not match with this server', 'http-bridge'),
                403
            );
        }

        $now = time();
        if ($payload['exp'] <= $now) {
            return self::error(
                'rest_unauthorized',
                __('The token is expired', 'http-bridge'),
                403
            );
        }

        if ($payload['nbf'] >= $now) {
            return self::error(
                'rest_unauthorized',
                __('The token is not valid yet', 'http-bridge'),
                403
            );
        }

        if (!isset($payload['data']['user_id'])) {
            return self::error(
                'rest_unauthorized',
                __('User ID not found in the token', 'http-bridge'),
                403
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

        $namespace = self::namespace();
        $version = self::version();

        $validate_uri = strpos(
            $requested_url,
            "{$namespace}/v{$version}/validate-token"
        );
        if ($validate_uri > 0) {
            return $user_id;
        }

        try {
            $auth = self::get_auth();
        } catch (Exception) {
            return $user_id;
        }

        try {
            $payload = (new JWT())->decode($auth);
        } catch (Exception $e) {
            if ($e->getMessage() === 'Invalid token format') {
                self::$auth_error = self::error(
                    'rest_unauthorized',
                    $e->getMessage(),
                    ['status' => $e->getCode()]
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
    private static function rest_pre_dispatch($result, $server, $request)
    {
        if (is_wp_error($result) || is_wp_error(self::$auth_error)) {
            return self::$auth_error;
        }

        $namespace = self::namespace();
        $version = self::version();
        if (
            preg_match(
                "/^\/{$namespace}\/v{$version}\//",
                $request->get_route()
            )
        ) {
            if ($error = self::cors_allowed()) {
                return $error;
            }
        }

        return $result;
    }

    /**
     * Check CORS policies based on configured backends.
     *
     * @return null|WP_Error CORS error.
     */
    private static function cors_allowed()
    {
        $whitelist = SettingsStore::setting('general')->whitelist;

        if (!$whitelist) {
            return;
        }

        try {
            $self = wp_parse_url(get_option('siteurl'));
            $backends = apply_filters('http_bridge_backends', []);
            $sources = array_map(function ($backend) {
                return wp_parse_url($backend->base_url);
            }, $backends);
            $sources[] = array_merge($self, ['scheme' => 'http']);
            $sources[] = array_merge($self, ['scheme' => 'https']);

            $origin = isset($_SERVER['HTTP_ORIGIN'])
                ? sanitize_text_field(wp_unslash($_SERVER['HTTP_ORIGIN']))
                : (isset($_SERVER['HTTP_REFERER'])
                    ? sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER']))
                    : null);

            if (!$origin) {
                return self::error(
                    'rest_bad_request',
                    __('HTTP Origin is required', 'http-bridge'),
                    400
                );
            }

            $origin = wp_parse_url($origin);
            foreach ($sources as $source) {
                if (
                    $origin['host'] === $source['host'] &&
                    $origin['scheme'] === $source['scheme']
                ) {
                    return;
                }
            }

            return self::error(
                'rest_unauthorized',
                __('HTTP Origin blacklisted', 'http-bridge'),
                [
                    'status' => '403',
                ]
            );
        } catch (Exception $e) {
            return self::error(
                'rest_internal_error',
                __('Internal Server Error', 'http-bridge'),
                [
                    'status' => '500',
                ]
            );
        }
    }
}
