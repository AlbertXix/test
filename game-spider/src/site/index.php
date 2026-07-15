<?php
session_start();

$host = $_SERVER['HTTP_HOST'] ?? '';
$hostName = explode(':', $host)[0];
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    if (!in_array($hostName, ['localhost', '127.0.0.1', '::1'], true)) {
        header('Location: https://' . $host . $_SERVER['REQUEST_URI'], true, 301);
        exit;
    }
}

if (isset($_SERVER['HTTP_ORIGIN'])) {
    $originHost = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST) ?: '';
    if ($originHost !== $hostName) {
        http_response_code(403);
        echo 'Cross-origin requests are not allowed';
        exit;
    }
}

require __DIR__ . '/engine/BotDetector.php';
$bot = new BotDetector();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_ch_token'])) {
    if ($bot->validateChallengeToken($_POST['_ch_token'])) {
        $bot->markPassed();
        header('Location: ' . $_SERVER['REQUEST_URI'], true, 303);
        exit;
    }
    http_response_code(403);
    echo '验证失败';
    exit;
}

if ($bot->isBlocked()) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

if ($bot->isSearchEngine()) {
    $isCrawler = false;
} else {
    $score = $bot->getFingerprintScore();
    $rateExceeded = $bot->checkRate();

    if (!$bot->hasPassedChallenge() && ($score >= 40 || $rateExceeded)) {
        $bot->markCrawler();
        if ($score >= 60 || $rateExceeded) {
            $bot->blockIP();
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        $bot->issueChallenge();
    }

    $isCrawler = $bot->isCrawler();
}

$api = $_GET['api'] ?? '';
if ($api) {
    $apiFile = __DIR__ . '/api/' . basename($api) . '.php';
    if (file_exists($apiFile)) {
        require $apiFile;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API not found']);
    }
    exit;
}

$pdo = new \PDO('mysql:host=127.0.0.1;dbname=bocms;charset=utf8', 'xlb', 'xlb123');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

$page = $_GET['page'] ?? 'home';

$validPages = ['home', 'games', 'rankings', 'detail', 'search'];
if (!in_array($page, $validPages)) {
    $page = 'home';
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require __DIR__ . '/engine/XlTemplate.php';

$controllerClass = ucfirst($page) . 'Controller';
require __DIR__ . "/controllers/{$controllerClass}.php";
$controller = new $controllerClass($pdo, $bot);
$data = $controller->execute();

$data['page'] = $page;
$data['csrf_token'] = $_SESSION['csrf_token'];

$engine = new XlTemplate(__DIR__ . '/templates', __DIR__ . '/cache');
$skipCache = $page === 'search';
$engine->render($page, $data, $skipCache);
