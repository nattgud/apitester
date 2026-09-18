<?php

declare(strict_types=1);

namespace App\Testing;

final class TestRunner
{
    public function run(
        string $name,
        TestContext $context
    ): TestResult {
        $class = TestRegistry::get($name);

        /** @var Test $test */
        $test = new $class();

        return $test->run($context);
    }
}