<?php

namespace WPCT_HTTP;

use Exception;

class JWT
{
    private static $key = WPCT_HTTP_AUTH_SECRET;

    public function encode($payload)
    {
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);

        $header = $this->base64URLEncode($header);
        $payload = json_encode($payload);
        $payload = $this->base64URLEncode($payload);

        $signature = hash_hmac('sha256', $header . '.' . $payload, self::$key, true);
        $signature = $this->base64URLEncode($signature);
        return $header . '.' . $payload . '.' . $signature;
    }

    public function decode($token)
    {
        if (
            preg_match(
                '/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/',
                $token,
                $matches
            ) !== 1
        ) {
            throw new Exception('Invalid token format', 400);
        }

        $signature = hash_hmac(
            'sha256',
            $matches['header'] . '.' . $matches['payload'],
            self::$key,
            true
        );

        $signature_from_token = $this->base64URLDecode($matches['signature']);

        if (!hash_equals($signature, $signature_from_token)) {
            throw new Exception('Signature doesn\'t match', 401);
        }

        $payload = json_decode($this->base64URLDecode($matches['payload']), true);
        return $payload;
    }


    private function base64URLEncode($text)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }

    private function base64URLDecode($text)
    {
        return base64_decode(
            str_replace(
                ['-', '_'],
                ['+', '/'],
                $text
            )
        );
    }
}
