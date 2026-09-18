<?php

declare(strict_types=1);

namespace App\Auth;

use App\Testing\TestContext;

final class JwtAuth
{
    public static function login(
        TestContext $context,
        string $endpoint = '/login'
    ): string {
        $response = $context->api->post(
            $endpoint,
            [
                'username' => $context->username,
                'password' => $context->password,
            ]
        );

        if ($response->status !== 200) {
            throw new \RuntimeException(
                "Login failed with HTTP {$response->status}"
            );
        }

        if (!$response->isJson()) {
            throw new \RuntimeException(
                'Login response is not JSON'
            );
        }

        $json = $response->json();

        $token = null;

        if (isset($json['token'])) {
            $token = $json['token'];
        } elseif (isset($json['access_token'])) {
            $token = $json['access_token'];
        }

        if (!is_string($token) || $token === '') {
            throw new \RuntimeException(
                'Login response did not contain a JWT token'
            );
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \RuntimeException(
                'Login did not return a valid JWT format'
            );
        }

        return $token;
    }

    public static function bearer(
        string $token
    ): array {
        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }
}