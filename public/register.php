<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Ngrok\NgrokClient;

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
} catch (\Throwable) {
    respond(400, [
        'error' => 'Invalid JSON',
    ]);
}

if (!is_array($input)) {
    respond(400, [
        'error' => 'Invalid request',
    ]);
}

$apiKey = $input['apiKey'] ?? null;

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

$studentId = $input['studentId'] ?? null;

if (
    !is_string($studentId) ||
    $studentId === ''
) {
    respond(400, [
        'error' => 'Missing studentId',
    ]);
}

try {
    $ngrok = new NgrokClient(
        (string) $config['ngrok']['api_key']
    );

    $credential = $ngrok->createCredential(
        'Student ' . $studentId
    );

    respond(200, [
        'studentId' => $studentId,
        'token' => $credential['token'],
    ]);

} catch (\Throwable $e) {

    error_log(
        'ngrok registration failed: ' .
        get_class($e)
    );

    respond(500, [
        'error' => 'Could not create ngrok credential',
    ]);
}