<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Http\ApiClient;
use App\Testing\TestContext;
use App\Testing\TestRunner;

header('Content-Type: application/json; charset=utf-8');

function respond(
    int $status,
    array $data
): void {
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, [
        'error' => 'Method not allowed',
    ]);
}

$raw = file_get_contents('php://input');

if ($raw === false || strlen($raw) > 16384) {
    respond(400, [
        'error' => 'Invalid request',
    ]);
}

try {
    $input = json_decode(
        $raw,
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (\Throwable $e) {
    respond(400, [
        'error' => 'Invalid JSON',
    ]);
}

if (!is_array($input)) {
    respond(400, [
        'error' => 'Invalid request',
    ]);
}


$apiKey = isset($input['apiKey'])
    ? $input['apiKey']
    : null;

if (
    !is_string($apiKey) ||
    !hash_equals(
        (string) $config['api_key'],
        $apiKey
    )
) {
    respond(401, [
        'error' => 'Unauthorized',
    ]);
}


$url = isset($input['url'])
    ? $input['url']
    : null;

$testName = isset($input['test'])
    ? $input['test']
    : null;

$account = isset($input['account'])
    ? $input['account']
    : null;

if (!is_string($url)) {
    respond(400, [
        'error' => 'Missing url',
    ]);
}

if (!is_string($testName)) {
    respond(400, [
        'error' => 'Missing test',
    ]);
}

if (!is_array($account)) {
    respond(400, [
        'error' => 'Missing account',
    ]);
}

$username = isset($account['username'])
    ? $account['username']
    : null;

$password = isset($account['password'])
    ? $account['password']
    : null;

if (
    !is_string($username) ||
    !is_string($password)
) {
    respond(400, [
        'error' => 'Invalid account',
    ]);
}


try {
    $api = new ApiClient(
        $url,
        $config
    );

    $context = new TestContext(
        $api,
        $username,
        $password
    );

    $runner = new TestRunner();

    $result = $runner->run(
        $testName,
        $context
    );

    respond(200, [
        'test' => $testName,
        'result' => $result->toArray(),
    ]);

} catch (\InvalidArgumentException $e) {

    respond(400, [
        'error' => $e->getMessage(),
    ]);
} catch (\Throwable $e) {
    respond(500, [
        'error' => $e->getMessage(),
        'type' => get_class($e),
    ]);
}