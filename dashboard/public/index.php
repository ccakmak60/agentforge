<?php

declare(strict_types=1);

$app = require __DIR__ . '/../bootstrap/app.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rtrim((string) $uri, '/');
$path = $path === '' ? '/' : $path;

$rawBody = file_get_contents('php://input');
$decodedBody = json_decode(is_string($rawBody) ? $rawBody : '', true);
$body = is_array($decodedBody) ? $decodedBody : [];

$response = $app($method, $path, $body);

http_response_code((int) ($response['status'] ?? 200));
header('Content-Type: application/json');
echo json_encode($response['body'] ?? [], JSON_UNESCAPED_SLASHES);
