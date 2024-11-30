<?php

namespace HTTP_BRIDGE;

use Exception;
use Error;
use WP_Error;
use WP_REST_Server;
use WPCT_ABSTRACT\Singleton;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * REST API Auth Controller.
 */
class REST_Auth_Controller extends Singleton
{
    /**
     * Handle REST API controller namespace.
     *
     * @var string $namespace REST API namespace.
     */
    private $namespace = 'wp-bridges';

    /**
     * Handle REST API controller namespace version.
     *
     * @var int $version REST API namespace version.
     */
    private $version = 1;

    /**
     * REST API version handler.
     *
     * @var string $version REST API version.
     */
    private $user = null;

    /**
     * Authorization error handler.
     *
     * @var WP_Error|null $auth_error authorization error.
     */
    private $auth_error = null;

    /**
     * Authorization header getter.
     *
     * @return string $token Bearer token.
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

        [$token] = sscanf($auth_header, 'Bearer %s');
        if (!$token) {
            throw new Exception('Authorization header malformed', 400);
        }

        return $token;
    }

    /**
     * Setup a new rest settings controller.
     *
     * @return object Instance of REST_Controller.
     */
    public static function setup()
    {
        return new REST_Auth_Controller();
    }

    /**
     * Internal WP_Error proxy.
     *
     * @param string $code
     * @param string $message
     * @param int $status
     */
    private static function error($code, $message, $status)
    {
        return new WP_Error($code, __($message, 'http-bridge'), [
            'status' => $status,
        ]);
    }

    /**
     * Binds auth checks to rest api hooks and registers routes.
     */
    public function __construct()
    {
        add_action('determine_current_user', function ($user_id) {
            return $this->determine_current_user($user_id);
        });

        add_filter(
            'rest_pre_dispatch',
            function ($result, $server, $request) {
                return $this->rest_pre_dispatch($result, $server, $request);
            },
            10,
            3
        );

        add_action('rest_api_init', function () {
            $this->init();
        });
    }

    private function init()
    {
        register_rest_route(
            "{$this->namespace}/v{$this->version}",
            '/http-bridge/auth',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => function () {
                    return $this->auth();
                },
                'permission_callback' => function () {
                    return $this->auth_permission_callback();
                },
            ]
        );

        register_rest_route(
            "{$this->namespace}/v{$this->version}",
            '/http-bridge/validate-token',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => function () {
                    return $this->validate();
                },
                'permission_callback' => function () {
                    return $this->validate_permission_callback();
                },
            ]
        );
    }

    /**
     * Auth callback.
     *
     * @return array<string, string> Login token.
     */
    private function auth()
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
     */
    private function auth_permission_callback()
    {
        $data = (array) json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            return self::error('rest_bad_request', 'Invalid JSON data', 400);
        }

        if (!(isset($data['username']) && isset($data['password']))) {
            return self::error(
                'rest_bad_request',
                'Missing login credentials',
                400
            );
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
     */
    private function validate_permission_callback()
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
                'Invalid authorization token',
                403
            );
        } catch (Error) {
            return self::error(
                'rest_internal_error',
                'Internal Server Error',
                500
            );
        }

        if ($payload['iss'] !== get_bloginfo('url')) {
            return self::error(
                'rest_unauthorized',
                'The iss do not match with this server',
                403
            );
        }

        $now = time();
        if ($payload['exp'] <= $now) {
            return self::error(
                'rest_unauthorized',
                'The token is expired',
                403
            );
        }

        if ($payload['nbf'] >= $now) {
            return self::error(
                'rest_unauthorized',
                'The token is not valid yet',
                403
            );
        }

        if (!isset($payload['data']['user_id'])) {
            return self::error(
                'rest_unauthorized',
                'User ID not found in the token',
                403
            );
        }

        $this->user = get_user_by('ID', (int) $payload['data']['user_id']);
        return true;
    }

    /**
     * Determine current user from bearer authentication.
     *
     * @param int|null $user_id Already identified user ID.
     * 
     * @return int|null Identified user ID.
     */
    private function determine_current_user($user_id)
    {
        $rest_api_slug = rest_get_url_prefix();
        $requested_url = sanitize_url($_SERVER['REQUEST_URI']);
        $is_rest_request =
            (defined('REST_REQUEST') && REST_REQUEST) ||
            strpos($requested_url, $rest_api_slug);

        if ($is_rest_request && $user_id) {
            return $user_id;
        }

        $validate_uri = strpos(
            $requested_url,
            "{$this->namespace}/v{$this->version}/validate-token"
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
                $this->auth_error = self::error(
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
    private function rest_pre_dispatch($result, $server, $request)
    {
        if (is_wp_error($this->auth_error)) {
            return $this->auth_error;
        }

        if (
            preg_match(
                "/^\/{$this->namespace}\/v{$this->version}\//",
                $request->get_route()
            )
        ) {
            if ($error = $this->cors_allowed()) {
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
    private function cors_allowed()
    {
        $whitelist = (bool) Settings::get_setting(
            'http-bridge',
            'general',
            'whitelist'
        );

        if (!$whitelist) {
            return;
        }

        try {
            $self = parse_url(get_option('siteurl'));
            $backends = apply_filters('http_bridge_backends', []);
            $sources = array_map(function ($backend) {
                return parse_url($backend->base_url);
            }, $backends);
            $sources[] = $self;

            $origin = isset($_SERVER['HTTP_ORIGIN'])
                ? $_SERVER['HTTP_ORIGIN']
                : (isset($_SERVER['HTTP_REFERER'])
                    ? $_SERVER['HTTP_REFERER']
                    : null);

            if (!$origin) {
                return self::error(
                    'rest_bad_request',
                    'HTTP Origin is required',
                    400
                );
            }

            $origin = parse_url($origin);
            foreach ($sources as $source) {
                if (
                    $origin['host'] === $source['host'] &&
                    $origin['scheme'] === $source['scheme']
                ) {
                    return;
                }
            }

            return self::error('rest_unauthorized', 'HTTP Origin blacklisted', [
                'status' => '403',
            ]);
        } catch (Exception $e) {
            return self::error('rest_internal_error', 'Internal Server Error', [
                'status' => '500',
            ]);
        }
    }
}
