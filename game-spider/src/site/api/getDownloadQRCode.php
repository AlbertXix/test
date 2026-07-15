<?php
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

session_start();
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if ($token !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

$url = $_POST['url'] ?? $_GET['url'] ?? '';
if (!$url) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing url']);
    exit;
}

require __DIR__ . '/phpqrcode.php';

ob_start();
QRcode::png($url, false, QR_ECLEVEL_M, 4);
$pngData = ob_get_clean();

echo json_encode(['qr' => 'data:image/png;base64,' . base64_encode($pngData)]);
