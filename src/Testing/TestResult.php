<?php

declare(strict_types=1);

namespace App\Testing;

final class TestResult
{
    /**
     * @var array
     */
    private $checks = [];

    public function check(
        string $name,
        bool $passed,
        string $message
    ): void {
        $this->checks[] = [
            'name' => $name,
            'passed' => $passed,
            'message' => $message,
        ];
    }

    public function expectStatus(
        string $name,
        $response,
        int $expected
    ): void {
        $actual = $response->status;

        $this->check(
            $name,
            $actual === $expected,
            "Expected HTTP {$expected}, got {$actual}"
        );
    }

    public function expectJson(
        string $name,
        $response
    ): void {
        $valid = $response->isJson();

        $this->check(
            $name,
            $valid,
            $valid
                ? 'Valid JSON'
                : 'Response is not valid JSON'
        );
    }

    public function expectField(
        string $name,
        $json,
        string $field
    ): void {
        $exists = is_array($json)
            && array_key_exists($field, $json);

        $this->check(
            $name,
            $exists,
            $exists
                ? "Field '{$field}' exists"
                : "Field '{$field}' is missing"
        );
    }

    public function passed(): bool
    {
        if (empty($this->checks)) {
            return false;
        }

        foreach ($this->checks as $check) {
            if (!$check['passed']) {
                return false;
            }
        }

        return true;
    }

    public function toArray(): array
    {
        $total = count($this->checks);

        $passed = 0;

        foreach ($this->checks as $check) {
            if ($check['passed']) {
                $passed++;
            }
        }

        return [
            'passed' => $total > 0 && $passed === $total,

            'summary' => [
                'total' => $total,
                'passed' => $passed,
                'failed' => $total - $passed,
            ],

            'checks' => $this->checks,
        ];
    }
}