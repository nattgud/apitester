<?php

declare(strict_types=1);

namespace App\Tests;

use App\Testing\Test;
use App\Testing\TestContext;
use App\Testing\TestResult;

final class Crud implements Test
{
    public function run(TestContext $context): TestResult
    {
        $result = new TestResult();

        $userId = null;

        $name = 'Test User';
        $email = 'test-' . uniqid() . '@example.com';
        $updatedName = 'Updated Test User';

        try {
            $response = $context->api->post('/users', [
                'name' => $name,
                'email' => $email,
            ]);

            $result->expectStatus(
                'POST /users',
                $response,
                201
            );

            if (!$response->isJson()) {
                throw new \RuntimeException(
                    'Response is not valid JSON'
                );
            }

            $json = json_decode(
                $response->body,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!is_array($json)) {
                throw new \RuntimeException(
                    'Response JSON is not an object'
                );
            }

            if (!array_key_exists('id', $json)) {
                throw new \RuntimeException(
                    "Field 'id' is missing"
                );
            }

            $userId = $json['id'];

            if (!is_string($userId) && !is_int($userId)) {
                throw new \RuntimeException(
                    "Field 'id' must be a string or integer"
                );
            }

            $result->check(
                'POST /users returnerar id',
                true,
                'OK'
            );

        } catch (\Throwable $e) {
            $result->check(
                'POST /users',
                false,
                $e->getMessage()
            );
        }

        if ($userId !== null) {
            $path = '/users/' . rawurlencode((string) $userId);

            try {
                $response = $context->api->get($path);

                $result->expectStatus(
                    'GET /users/{id}',
                    $response,
                    200
                );

                if (!$response->isJson()) {
                    throw new \RuntimeException(
                        'Response is not valid JSON'
                    );
                }

                $json = json_decode(
                    $response->body,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                if (!is_array($json)) {
                    throw new \RuntimeException(
                        'Response JSON is not an object'
                    );
                }

                foreach (['id', 'name', 'email'] as $field) {
                    if (!array_key_exists($field, $json)) {
                        throw new \RuntimeException(
                            "Field '{$field}' is missing"
                        );
                    }
                }

                if (
                    !is_string($json['id']) &&
                    !is_int($json['id'])
                ) {
                    throw new \RuntimeException(
                        "Field 'id' must be a string or integer"
                    );
                }

                if ((string) $json['id'] !== (string) $userId) {
                    throw new \RuntimeException(
                        'Returned id does not match the created user'
                    );
                }

                if (!is_string($json['name'])) {
                    throw new \RuntimeException(
                        "Field 'name' must be a string"
                    );
                }

                if ($json['name'] !== $name) {
                    throw new \RuntimeException(
                        'Returned name does not match the created user'
                    );
                }

                if (!is_string($json['email'])) {
                    throw new \RuntimeException(
                        "Field 'email' must be a string"
                    );
                }

                if (filter_var($json['email'], FILTER_VALIDATE_EMAIL) === false) {
                    throw new \RuntimeException(
                        "Field 'email' is not a valid email address"
                    );
                }

                if ($json['email'] !== $email) {
                    throw new \RuntimeException(
                        'Returned email does not match the created user'
                    );
                }

                $result->check(
                    'GET /users/{id} returnerar rätt data',
                    true,
                    'OK'
                );

            } catch (\Throwable $e) {
                $result->check(
                    'GET /users/{id}',
                    false,
                    $e->getMessage()
                );
            }
        } else {
            $result->check(
                'GET /users/{id}',
                false,
                'Kan inte testa GET eftersom POST /users inte returnerade något id'
            );
        }

        if ($userId !== null) {
            $path = '/users/' . rawurlencode((string) $userId);

            try {
                $response = $context->api->put($path, [
                    'name' => $updatedName,
                    'email' => $email,
                ]);

                $status = $response->status;

                $passed = $status === 200 || $status === 204;

                $result->check(
                    'PUT /users/{id}',
                    $passed,
                    $passed
                        ? 'OK'
                        : 'Expected HTTP 200 or 204, got HTTP ' . $status
                );

            } catch (\Throwable $e) {
                $result->check(
                    'PUT /users/{id}',
                    false,
                    $e->getMessage()
                );
            }

            try {
                $response = $context->api->get($path);

                $result->expectStatus(
                    'GET /users/{id} efter uppdatering',
                    $response,
                    200
                );

                if (!$response->isJson()) {
                    throw new \RuntimeException(
                        'Response is not valid JSON'
                    );
                }

                $json = json_decode(
                    $response->body,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                if (!is_array($json)) {
                    throw new \RuntimeException(
                        'Response JSON is not an object'
                    );
                }

                if (!array_key_exists('name', $json)) {
                    throw new \RuntimeException(
                        "Field 'name' is missing"
                    );
                }

                if (!is_string($json['name'])) {
                    throw new \RuntimeException(
                        "Field 'name' must be a string"
                    );
                }

                if ($json['name'] !== $updatedName) {
                    throw new \RuntimeException(
                        'Updated name was not saved'
                    );
                }

                if (!array_key_exists('email', $json)) {
                    throw new \RuntimeException(
                        "Field 'email' is missing"
                    );
                }

                if (!is_string($json['email'])) {
                    throw new \RuntimeException(
                        "Field 'email' must be a string"
                    );
                }

                if (filter_var($json['email'], FILTER_VALIDATE_EMAIL) === false) {
                    throw new \RuntimeException(
                        "Field 'email' is not a valid email address"
                    );
                }

                if ($json['email'] !== $email) {
                    throw new \RuntimeException(
                        'Email address was changed unexpectedly'
                    );
                }

                $result->check(
                    'Uppdateringen sparas',
                    true,
                    'OK'
                );

            } catch (\Throwable $e) {
                $result->check(
                    'Uppdateringen sparas',
                    false,
                    $e->getMessage()
                );
            }
        }

        if ($userId !== null) {
            $path = '/users/' . rawurlencode((string) $userId);

            try {
                $response = $context->api->delete($path);

                $status = $response->status;

                $passed = $status === 200 || $status === 204;

                $result->check(
                    'DELETE /users/{id}',
                    $passed,
                    $passed
                        ? 'OK'
                        : 'Expected HTTP 200 or 204, got HTTP ' . $status
                );

            } catch (\Throwable $e) {
                $result->check(
                    'DELETE /users/{id}',
                    false,
                    $e->getMessage()
                );
            }

            try {
                $response = $context->api->get($path);

                $passed = $response->status === 404;

                $result->check(
                    'GET /users/{id} efter DELETE',
                    $passed,
                    $passed
                        ? 'OK'
                        : 'Expected HTTP 404, got HTTP ' .
                          $response->status
                );

            } catch (\Throwable $e) {
                $result->check(
                    'GET /users/{id} efter DELETE',
                    false,
                    $e->getMessage()
                );
            }
        }

        return $result;
    }
}