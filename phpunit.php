<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

function create_request(string $method = 'GET', string $url = 'http://example.com') {
    $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

    return $psr17Factory->createRequest($method, $url);
}

function create_response(int $status = 200, array $parameters = []) {
    $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
    $response = $psr17Factory->createResponse($status);

    $parameters = array_replace([
        'access_token' => '2YotnFZFEjr1zCsicMWpAA',
        'custom_param' => 'custom_value',
        'expires_in' => 3600,
        'refresh_token' => 'tGzv3JOkF0XG5Qx2TlKWIA',
        'token_type' => 'example',
    ], $parameters);

    $body = json_encode(
        array_filter($parameters)
    );

    return $response->withBody(
        $psr17Factory->createStream($body)
    );
}
