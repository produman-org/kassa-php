<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ('/health' === $path) {
    echo getenv('KASSA_FIXTURE_MARKER');

    return;
}
$body = file_get_contents('php://input');
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$entry = ['method' => $_SERVER['REQUEST_METHOD'], 'path' => $path, 'query' => $_GET, 'headers' => $headers, 'body' => $body];
file_put_contents(getenv('KASSA_FIXTURE_DIRECTORY') . '/' . getenv('KASSA_FIXTURE_ROLE') . '.requests', json_encode($entry, JSON_THROW_ON_ERROR) . "\n", FILE_APPEND | LOCK_EX);
header('Content-Type: application/json');
header('X-RateLimit-Remaining: 9');
header('X-RateLimit-Limit: 10');
if ('capture' === getenv('KASSA_FIXTURE_ROLE')) {
    echo '{}';

    return;
}
if (str_ends_with($path, '/redirect')) {
    header('Location: http://localhost:' . getenv('KASSA_FIXTURE_CAPTURE_PORT') . '/capture', true, (int) ($_GET['status'] ?? 302));

    return;
}
if (preg_match('/error-(400|429|500)$/', $path, $matches)) {
    http_response_code((int) $matches[1]);
    echo '{"message":"fixture error","code":"VALIDATION_ERROR","details":[]}';

    return;
}
if ('DELETE' === $_SERVER['REQUEST_METHOD']) {
    http_response_code(204);

    return;
}
if (str_ends_with($path, '/malformed')) {
    echo '{broken';

    return;
}
$data = '' === $body ? [] : json_decode($body, true, 512, JSON_THROW_ON_ERROR);
if (($data['cashboxId'] ?? null) === 'timeout') {
    sleep(3);
}
if (str_ends_with($path, '/operations')) {
    if (($data['cashboxId'] ?? null) === 'error-500') {
        http_response_code(500);
    }
    echo '{"id":"operation-1"}';

    return;
}
if (str_contains($path, '/application-webhooks/')) {
    echo '{"id":"endpoint-1","status":"ACTIVE","events":[]}';

    return;
}
$order = $data + ['id' => 'order-1', 'positions' => [['name' => 'Товар', 'itemCode' => '000123', 'quantity' => 1, 'price' => 100]]];
if ('GET' === $_SERVER['REQUEST_METHOD'] && str_ends_with($path, '/orders')) {
    echo json_encode(['items' => [$order], 'nextCursor' => null], JSON_THROW_ON_ERROR);
} else {
    http_response_code('POST' === $_SERVER['REQUEST_METHOD'] ? 201 : 200);
    echo json_encode($order, JSON_THROW_ON_ERROR);
}
