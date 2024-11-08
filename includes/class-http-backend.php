<?php

namespace WPCT_HTTP;

use Exception;

class Http_Backend
{
    private $backend = null;

    public function __construct($name)
    {
        $this->backend = $this->get_backend($name);
        if (!$this->backend) {
            throw new Exception("Http backend error: Unkown backend with name {$name}");
        }
    }

    private function get_backend($name)
    {
        $setting = get_option('wpct-http-bridge_general');
        if (!isset($setting['backends'])) {
            return null;
        }

        $backends = $setting['backends'];
        foreach ($backends as $backend) {
            if ($backend['name'] === $name) {
                return $backend;
            }
        }
    }

    public function __get($attr)
    {
        if (isset($this->backend[$attr])) {
            return $this->backend[$attr];
        }

        return null;
    }

    public function get_endpoint_url($path)
    {
        $url_data = parse_url($path);
        if (isset($url_data['scheme'])) {
            return $path;
        }

        $base_url = $this->base_url;
        return preg_replace('/\/$', '', $base_url) . '/' . preg_replace('/^\//', '', $path);
    }

    public function get_headers()
    {
        $headers = [];
        foreach ($this->headers as $header) {
            [$name, $value] = explode(':', $header);
            $headers[strtolower(trim($name))] = trim($value);
        }

        return $headers;
    }
}

add_filter('wpct_http_backend', function ($null, $name) {
    return new Http_Backend($name);
}, 10, 2);
