<?php

declare(strict_types=1);

namespace App\Http;

final class Response
{
    /**
     * @var int
     */
    public $status;

    /**
     * @var string
     */
    public $body;

    /**
     * @var array
     */
    public $headers;

    public function __construct(
        int $status,
        string $body,
        array $headers = []
    ) {
        $this->status = $status;
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * @return mixed
     */
    public function json()
    {
        return json_decode(
            $this->body,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    public function isJson(): bool
    {
        try {
            $this->json();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}