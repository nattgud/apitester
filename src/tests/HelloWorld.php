<?php

declare(strict_types=1);

namespace App\Tests;

use App\Auth\JwtAuth;
use App\Testing\Test;
use App\Testing\TestContext;
use App\Testing\TestResult;

final class HelloWorld implements Test
{
    public function run(
        TestContext $context
    ): TestResult {
        $result = new TestResult();

        try {
            $response = $context->api->get('/');

            if (!is_string($response->body)) {
                throw new \RuntimeException(
                    'Svaret är inte en sträng.'
                );
            }

            if ($response->status !== 200) {
                throw new \RuntimeException(
                    'Svarskod ska vara HTTP 200, fick HTTP ' . $response->status
                );
            }

            $result->check(
                'GET /author ska returnera en sträng',
                true,
                'OK'
            );

        } catch (\Throwable $e) {

            $result->check(
                'GET / ska returnera en valfri sträng',
                false,
                $e->getMessage()
            );
        }
        return $result;
    }
}