<?php
// 创建短网址 API — 返回 JSON {short_code, short_url}
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

// 简单 URL 合法性校验
if (!preg_match('#^https?://#i', $url)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid url, must start with http:// or https://']);
    exit;
}

$dbConfig = require __DIR__ . '/../../Config/database.config.php';
$pdo = new \PDO($dbConfig['dsn'], $dbConfig['username'], $dbConfig['password']);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

require __DIR__ . '/../engine/ShortUrl.php';
$shortUrl = new ShortUrl($pdo);

try {
    $code = $shortUrl->generate($url);
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base = $scheme . '://' . $host . $_SERVER['SCRIPT_NAME'];
    $shortUrl = $base . '?s=' . $code;

    echo json_encode([
        'short_code' => $code,
        'short_url' => $shortUrl,
        'target_url' => $url,
    ]);
} catch (\RuntimeException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
