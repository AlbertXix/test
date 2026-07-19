<?php
// 根据游戏ID和下载类型生成短网址二维码
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

session_start();
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if ($token !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

$gameId = isset($_POST['game_id']) ? (int) $_POST['game_id'] : 0;
$type = $_POST['type'] ?? '';

if (!$gameId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing game_id']);
    exit;
}

// 连接数据库
$dbConfig = require __DIR__ . '/../../Config/database.config.php';
$pdo = new \PDO($dbConfig['dsn'], $dbConfig['username'], $dbConfig['password']);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

// 查询游戏下载 URL
$fieldMap = [
    'xunlei' => 'xunlei_url',
    'quark'  => 'quark_url',
    'baidu'  => 'baidu_url',
    'direct' => 'download_url',
];

$columns = implode(', ', array_values($fieldMap));
$stmt = $pdo->prepare("SELECT id, $columns FROM bo_game WHERE id = :id AND visible = 1 LIMIT 1");
$stmt->execute([':id' => $gameId]);
$game = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$game) {
    http_response_code(404);
    echo json_encode(['error' => 'Game not found']);
    exit;
}

// 确定目标 URL
$downloadUrl = '';
if ($type && isset($fieldMap[$type])) {
    $downloadUrl = $game[$fieldMap[$type]] ?? '';
} else {
    // 未指定 type 时按优先级取第一个可用 URL
    foreach ($fieldMap as $key => $col) {
        if (!empty($game[$col])) {
            $downloadUrl = $game[$col];
            break;
        }
    }
}

if (!$downloadUrl) {
    http_response_code(400);
    echo json_encode(['error' => 'No download URL available']);
    exit;
}

// 获取或创建短网址
require __DIR__ . '/../engine/ShortUrl.php';
$shortUrlUtil = new ShortUrl($pdo);
$code = $shortUrlUtil->getOrCreate($downloadUrl);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$shortUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?s=' . $code;

// 生成二维码
require __DIR__ . '/phpqrcode.php';
ob_start();
QRcode::png($shortUrl, false, QR_ECLEVEL_M, 4);
$pngData = ob_get_clean();

echo json_encode(['qr' => 'data:image/png;base64,' . base64_encode($pngData)]);
