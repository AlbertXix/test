<?php
$pdo = new \PDO('mysql:host=127.0.0.1;dbname=bocms;charset=utf8', 'xlb', 'xlb123');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

$page = $_GET['page'] ?? 'home';

$validPages = ['home', 'games', 'rankings', 'detail', 'search'];
if (!in_array($page, $validPages)) {
    $page = 'home';
}

require __DIR__ . '/engine/Template.php';

$controllerClass = ucfirst($page) . 'Controller';
require __DIR__ . "/controllers/{$controllerClass}.php";
$controller = new $controllerClass($pdo);
$data = $controller->execute();

$data['page'] = $page;

$engine = new Template(__DIR__ . '/templates', __DIR__ . '/cache');
$engine->render($page, $data);
