<?php

namespace WPCT_HTTP;

use Exception;

/**
 * JWT REST API authentication.
 *
 * @since 2.0.0
 */
class JWT
{
    /**
     * Handle auth secret.
     *
     * @since 2.0.0
     */
    private static $key = WPCT_HTTP_AUTH_SECRET;

    /**
     * Get encoded payload token.
     *
     * @since 2.0.0
     *
     * @param array $payload Token payload.
     * @return string $token JWT encoded token.
     */
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

    /**
     * Get decoded token payload.
     *
     * @since 2.0.0
     *
     * @param string $token JWT encoded token.
     * @return array $payload Token payload.
     */
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

    /**
     * URL conformant base64 encoder.
     *
     * @since 2.0.0
     *
     * @param string $text Source string.
     * @return string $base64 Encoded string.
     */
    private function base64URLEncode($text)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }

    /**
     * URL conformant base64 decoder.
     *
     * @since 2.0.0
     *
     * @param string $base64 Encoded string.
     * @return string $text Decoded string.
     */
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
