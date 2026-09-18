<?php

declare(strict_types=1);

namespace App\Ngrok;

final class NgrokClient
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return array{
     *     id: string,
     *     token: string
     * }
     */
    public function createCredential(string $description): array
    {
        $payload = json_encode([
            'description' => $description,
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init(
            'https://api.ngrok.com/credentials'
        );

        if ($ch === false) {
            throw new \RuntimeException(
                'Could not initialize cURL'
            );
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,

            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
                'ngrok-version: 2',
            ],

            CURLOPT_POSTFIELDS => $payload,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CONNECTTIMEOUT => 5,

            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);

            curl_close($ch);

            throw new \RuntimeException(
                'ngrok API request failed: ' . $error
            );
        }

        $status = curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException(
                'ngrok API returned HTTP ' . $status
            );
        }

        $data = json_decode(
            $response,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (
            !is_array($data) ||
            !isset($data['id']) ||
            !isset($data['token'])
        ) {
            throw new \RuntimeException(
                'Invalid response from ngrok API'
            );
        }

        return [
            'id' => (string) $data['id'],
            'token' => (string) $data['token'],
        ];
    }
}