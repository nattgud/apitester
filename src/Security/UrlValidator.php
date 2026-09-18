<?php

declare(strict_types=1);

namespace App\Security;

final class UrlValidator
{
    public function validate(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            throw new \InvalidArgumentException('Invalid URL');
        }

        $scheme = isset($parts['scheme'])
            ? strtolower($parts['scheme'])
            : '';

        if ($scheme !== 'https') {
            throw new \InvalidArgumentException(
                'API URL must use HTTPS'
            );
        }

        if (empty($parts['host'])) {
            throw new \InvalidArgumentException(
                'API URL must contain a hostname'
            );
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new \InvalidArgumentException(
                'Credentials in URL are not allowed'
            );
        }

        if (isset($parts['port']) && $parts['port'] !== 443) {
            throw new \InvalidArgumentException(
                'Only HTTPS port 443 is allowed'
            );
        }

        $host = $parts['host'];

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException(
                'IP addresses are not allowed'
            );
        }

        $host = rtrim($host, '.');

        if ($host === '') {
            throw new \InvalidArgumentException(
                'Invalid hostname'
            );
        }

        $ips = $this->resolve($host);

        if (empty($ips)) {
            throw new \InvalidArgumentException(
                'Hostname could not be resolved'
            );
        }

        foreach ($ips as $ip) {
            if (!$this->isPublicIp($ip)) {
                throw new \InvalidArgumentException(
                    'Hostname resolves to a private or reserved IP'
                );
            }
        }

        if (
            isset($parts['query']) ||
            isset($parts['fragment'])
        ) {
            throw new \InvalidArgumentException(
                'Query strings and fragments are not allowed in base URL'
            );
        }

        $path = isset($parts['path'])
            ? rtrim($parts['path'], '/')
            : '';

        return 'https://' . $host . $path;
    }

    /**
     * @return string[]
     */
    public function resolve(string $host): array
    {
        $ips = [];

        $records = @dns_get_record(
            $host,
            DNS_A | DNS_AAAA
        );

        if ($records === false) {
            return [];
        }

        foreach ($records as $record) {
            if (isset($record['ip'])) {
                $ips[] = $record['ip'];
            }

            if (isset($record['ipv6'])) {
                $ips[] = $record['ipv6'];
            }
        }

        return array_values(array_unique($ips));
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE |
            FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}