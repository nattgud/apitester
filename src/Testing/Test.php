<?php

declare(strict_types=1);

namespace App\Testing;

interface Test
{
    public function run(
        TestContext $context
    ): TestResult;
}