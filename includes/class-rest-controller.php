<?php

namespace WPCT_HTTP;

use Exception;
use Error;
use WP_Error;
use WP_REST_Server;

/**
 * REST API Controller.
 *
 * @since 2.0.0
 */
class REST_Controller
{
    /**
     * REST API namespaces handler.
     *
     * @var string $namespace REST API namespace.
     *
     * @since 2.0.0
     */
    private $namespace = 'wpct';

    /**
     * REST API version handler.
     *
     * @var string $version REST API version.
     *
     * @since 2.0.0
     */
    private $version = 1;

    /**
     * REST API version handler.
     *
     * @var string $version REST API version.
     *
     * @since 2.0.0
     */
    private $user = null;

    /**
     * Authorization error handler.
     *
     * @var WP_Error|null $auth_error authorization error.
     *
     * @since 2.0.0
     */
    private $auth_error = null;

    /**
     * WP_Error proxy.
     *
     * @param string $code Error code.
     * @param string $message Error message.
     * @param string $status HTTP status code.
     * @return WP_Error API error.
     *
     * @since 2.0.0
     */
    private static function error($code, $message, $status)
    {
        return new WP_Error(
            $code,
            __($message, 'wpct-http-bridge'),
            [
                'status' => $status,
            ],
        );
    }

    /**
     * Authorization header getter.
     *
     * @return string $token Bearer token.
     *
     * @since 2.0.0
     */
    private static function get_auth()
    {
        $auth_header = isset($_SERVER['HTTP_AUTHORIZATION'])
            ? sanitize_text_field($_SERVER['HTTP_AUTHORIZATION'])
            : (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])
                ? sanitize_text_field($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])
                : null);

        if ($auth_header === null) {
            throw new Exception('Authorization header not found', 400);
        }

        [ $token ] = sscanf($auth_header, 'Bearer %s');
        if (!$token) {
            throw new Exception('Authorization header malformed', 400);
        }

        return $token;
    }

    /**
     * Bind methods to WP REST API hooks.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        add_action('rest_api_init', function () {
            $this->init();
        });

        add_action('determine_current_user', function ($user_id) {
            return $this->determine_current_user($user_id);
        });

        add_filter('rest_pre_dispatch', function ($req) {
            return $this->rest_pre_dispatch($req);
        });
    }

    /**
     * Register API routes.
     *
     * @since 2.0.0
     */
    private function init()
    {
        register_rest_route("{$this->namespace}/v{$this->version}", '/http-bridge/auth', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => function () {
                return $this->auth();
            },
            'permission_callback' => function () {
                return $this->auth_permission_callback();
            }
        ]);

        register_rest_route("{$this->namespace}/v{$this->version}", '/http-bridge/validate-token', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => function () {
                return $this->validate();
            },
            'permission_callback' => function () {
                return $this->validate_permission_callback();
            }
        ]);
    }

    /**
     * Auth callback.
     *
     * @returns array<string, string> $token Login token.
     *
     * @since 2.0.0
     */
    private function auth()
    {
        $issuedAt = time();
        $notBefore = apply_filters('wpct_http_auth_not_before', $issuedAt, $issuedAt);
        $expire = apply_filters('wpct_http_auth_expire', $issuedAt + (60 * 60 * 24 * 7), $issuedAt);

        $claims = [
            'iss'  => get_bloginfo('url'),
            'iat'  => $issuedAt,
            'nbf'  => $notBefore,
            'exp'  => $expire,
            'data' => [
                'user_id' => $this->user->data->ID,
            ],
        ];
        $token = (new JWT())->encode($claims);

        return apply_filters(
            'wpct_http_auth_response',
            [
                'token' => $token,
                'user_email' => $this->user->data->user_email,
                'user_login' => $this->user->data->user_login,
                'display_name' => $this->user->data->display_name,
            ],
            $this->user
        );
    }

    /**
     * Validate callback.
     *
     * @return array<string, string> $token Validated token.
     *
     * @since 2.0.0
     */
    private function validate()
    {
        $token = self::get_auth();
        return apply_filters(
            'wpct_http_validate_response',
            [
                'token' => $token,
                'user_email' => $this->user->data->user_email,
                'user_login' => $this->user->data->user_login,
                'display_name' => $this->user->data->display_name,
            ],
            $this->user
        );
    }

    /**
     * Performs auth requests permisison checks.
     *
     * @return boolean $success Request has permisisons.
     *
     * @since 2.0.0
     */
    private function auth_permission_callback()
    {
        $data = (array) json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            return self::error('rest_bad_request', 'Invalid JSON data', 400);
        }

        if (!(isset($data['username']) && isset($data['password']))) {
            return self::error('rest_bad_request', 'Missing login credentials', 400);
        }

        $user = wp_authenticate($data['username'], $data['password']);
        if (is_wp_error($user)) {
            return self::error('rest_unauthorized', 'Invalid credentials', 403);
        }

        $this->user = $user;
        return true;
    }

    /**
     * Performs validation requests permission checks.
     *
     * @return boolean $success Request has permissions.
     *
     * @since 2.0.0
     */
    private function validate_permission_callback()
    {
        try {
            $token = self::get_auth();
        } catch (Exception $e) {
            return self::error('rest_unauthorized', $e->getMessage(), $e->getCode());
        }

        try {
            $payload = (new JWT())->decode($token);
        } catch (Exception) {
            return self::error('rest_unauthorized', 'Invalid authorization token', 403);
        } catch (Error) {
            return self::error('rest_internal_error', 'Internal server error', 500);
        }

        if ($payload['iss'] !== get_bloginfo('url')) {
            return self::error('rest_unauthorized', 'The iss do not match with this server', 403);
        }

        $now = time();
        if ($payload['exp'] <= $now) {
            return self::error('rest_unauthorized', 'The token is expired', 403);
        }

        if ($payload['nbf'] >= $now) {
            return self::error('rest_unauthorized', 'The token is not valid yet', 403);
        }

        if (!isset($payload['data']['user_id'])) {
            return self::error('rest_unauthorized', 'User ID not found in the token', 403);
        }

        $this->user = get_user_by('ID', (int) $payload['data']['user_id']);
        return true;
    }

    /**
     * Determine current user from bearer authentication.
     *
     * @param int|null $user_id Already identified user ID.
     * @return int|null $user_id Identified user ID.
     *
     * @since 2.0.0
     */
    private function determine_current_user($user_id)
    {
        $rest_api_slug = rest_get_url_prefix();
        $requested_url = sanitize_url($_SERVER['REQUEST_URI']);
        $is_rest_request = defined('REST_REQUEST') && REST_REQUEST || strpos(
            $requested_url,
            $rest_api_slug
        );
        if ($is_rest_request && $user_id) {
            return $user_id;
        }

        $validate_uri = strpos($requested_url, "{$this->namespace}/v{$this->version}/validate-token");
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
                $this->auth_error = self::error('rest_unauthorized', $e->getMessage(), ['status' => $e->getCode()]);
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
     * @since 2.0.0
     */
    private function rest_pre_dispatch($req)
    {
        if (is_wp_error($this->auth_error)) {
            return $this->auth_error;
        }

        return $req;
    }
}
