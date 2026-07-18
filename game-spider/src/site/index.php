<?php
// PHP 环境配置
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '1');
date_default_timezone_set('PRC');
set_time_limit(15);

// 注册错误/异常处理器
require __DIR__ . '/engine/ErrorHandler.php';
ErrorHandler::init();

session_start();

// 路径常量
define('SITE_PATH', dirname(__FILE__));
define('SRC_PATH', dirname(dirname(__FILE__)));
define('CFG_PATH', SRC_PATH . '/Config');
define('LOG_PATH', '/var/log/game-site');

// HTTPS 强制跳转（本地开发环境除外）
$host = $_SERVER['HTTP_HOST'] ?? '';
$hostName = explode(':', $host)[0];
$isDev = in_array($hostName, ['localhost', '127.0.0.1', '::1'], true) || (getenv('APP_ENV') ?: '') === 'dev';
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    if (!$isDev) {
        header('Location: https://' . $host . $_SERVER['REQUEST_URI'], true, 301);
        exit;
    }
}

// 跨域请求拦截（仅允许同源）
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $originHost = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST) ?: '';
    if ($originHost !== $hostName) {
        http_response_code(403);
        echo 'Cross-origin requests are not allowed';
        exit;
    }
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
}

// 初始化日志
require __DIR__ . '/engine/Logger.php';
$logger = new Logger(LOG_PATH, $isDev ? Logger::DEBUG : Logger::INFO);
ErrorHandler::setLogger($logger);
$logger->debug('Client: ' . $_SERVER['REMOTE_ADDR'] . ', ' . 'Request: ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']);

// 初始化爬虫检测器
require __DIR__ . '/engine/BotDetector.php';
$bot = new BotDetector();

// SQL 注入检测（扫描 GET/POST/COOKIE）
require __DIR__ . '/engine/SqlInjectDetector.php';
$sqliInputs = array_merge($_GET, $_POST, $_COOKIE);
foreach ($sqliInputs as $key => $value) {
    if (SqlInjectDetector::hasInjection($value)) {
        $bot->reportSqlInjection($logger);
        http_response_code(403);
        echo 'Request blocked';
        exit;
    }
}

// JS Challenge 验证回调处理
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

// 检查 IP 封禁
if ($bot->isBlocked()) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

// 爬虫识别与防护（UA 可伪造，故不依赖搜索引擎白名单）
$score = $bot->getFingerprintScore();
$rateExceeded = $bot->checkRate();

// 高嫌疑请求：标记爬虫 → 极高则封禁 → 否则弹 Challenge
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

// API 路由
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

// 连接数据库
$dbConfig = require CFG_PATH . '/database.config.php';
$dsn = $dbConfig['dsn'] . ';charset=' . ($dbConfig['charset'] ?? 'utf8');
$pdo = new \PDO($dsn, $dbConfig['username'], $dbConfig['password']);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

// 页面路由
$page = $_GET['page'] ?? 'home';
$validPages = ['home', 'games', 'rankings', 'detail', 'search'];
if (!in_array($page, $validPages)) {
    $page = 'home';
}

// CSRF Token 生成
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// MVC：加载控制器并执行
require __DIR__ . '/engine/XlTemplate.php';

$controllerClass = ucfirst($page) . 'Controller';
require __DIR__ . "/controllers/{$controllerClass}.php";
$controller = new $controllerClass($pdo, $bot);
$data = $controller->execute();

$data['page'] = $page;
$data['csrf_token'] = $_SESSION['csrf_token'];
$data['meta_keywords'] = $data['meta']['keywords'] ?? '';
$data['meta_description'] = $data['meta']['description'] ?? '';

// 模板渲染（搜索页面不缓存）
$engine = new XlTemplate(__DIR__ . '/templates', __DIR__ . '/cache');
$skipCache = $page === 'search';
$engine->render($page, $data, $skipCache);
