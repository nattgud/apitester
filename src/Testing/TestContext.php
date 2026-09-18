<?php

declare(strict_types=1);

namespace App\Testing;

use App\Http\ApiClient;

final class TestContext
{
    /**
     * @var ApiClient
     */
    public $api;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    public function __construct(
        ApiClient $api,
        string $username,
        string $password
    ) {
        $this->api = $api;
        $this->username = $username;
        $this->password = $password;
    }
}