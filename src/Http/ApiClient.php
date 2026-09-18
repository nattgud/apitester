<?php

declare(strict_types=1);

namespace App\Http;

use App\Security\UrlValidator;

final class ApiClient
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var int
     */
    private $connectTimeout;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var int
     */
    private $maxResponseBytes;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $resolvedIp;

    public function __construct(
        string $baseUrl,
        array $config
    ) {
        $validator = new UrlValidator();

        $this->baseUrl = $validator->validate($baseUrl);

        $parts = parse_url($this->baseUrl);

        $this->host = $parts['host'];

        $ips = $validator->resolve($this->host);

        if (empty($ips)) {
            throw new \InvalidArgumentException(
                'Could not resolve API hostname'
            );
        }

        $this->resolvedIp = $ips[0];

        $http = isset($config['http'])
            ? $config['http']
            : [];

        $this->connectTimeout = isset($http['connect_timeout'])
            ? (int) $http['connect_timeout']
            : 3;

        $this->timeout = isset($http['timeout'])
            ? (int) $http['timeout']
            : 5;

        $this->maxResponseBytes = isset($http['max_response_bytes'])
            ? (int) $http['max_response_bytes']
            : 1024 * 1024;
    }

    public function get(
        string $path,
        array $headers = []
    ): Response {
        return $this->request(
            'GET',
            $path,
            null,
            $headers
        );
    }

    public function post(
        string $path,
        ?array $body = null,
        array $headers = []
    ): Response {
        return $this->request(
            'POST',
            $path,
            $body,
            $headers
        );
    }

    public function put(
        string $path,
        ?array $body = null,
        array $headers = []
    ): Response {
        return $this->request(
            'PUT',
            $path,
            $body,
            $headers
        );
    }

    public function delete(
        string $path,
        array $headers = []
    ): Response {
        return $this->request(
            'DELETE',
            $path,
            null,
            $headers
        );
    }

    private function request(
        string $method,
        string $path,
        ?array $body,
        array $headers
    ): Response {
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }

        if (
            strpos($path, '://') !== false ||
            strpos($path, '//') === 0
        ) {
            throw new \InvalidArgumentException(
                'Absolute URLs are not allowed'
            );
        }

        $url = $this->baseUrl . $path;

        $curl = curl_init($url);

        if ($curl === false) {
            throw new \RuntimeException(
                'Could not initialize cURL'
            );
        }

        $requestHeaders = [
            'Accept: application/json',
            'Connection: close',
        ];

        foreach ($headers as $name => $value) {
            $requestHeaders[] =
                $name . ': ' . $value;
        }

        $encodedBody = null;

        if ($body !== null) {
            $encodedBody = json_encode(
                $body,
                JSON_THROW_ON_ERROR
            );

            $requestHeaders[] =
                'Content-Type: application/json';
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_POSTFIELDS => $encodedBody,
        ]);

        curl_setopt($curl, CURLOPT_RESOLVE, [
            $this->host . ':443:' . $this->resolvedIp,
        ]);

        $body = curl_exec($curl);

        if ($body === false) {
            $error = curl_error($curl);

            curl_close($curl);

            throw new \RuntimeException(
                'HTTP request failed: ' . $error
            );
        }

        $status = (int) curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );

        $contentType = curl_getinfo(
            $curl,
            CURLINFO_CONTENT_TYPE
        );

        curl_close($curl);

        if (strlen($body) > $this->maxResponseBytes) {
            throw new \RuntimeException(
                'Response was too large'
            );
        }

        return new Response(
            $status,
            $body,
            [
                'content-type' => $contentType,
            ]
        );
    }
}