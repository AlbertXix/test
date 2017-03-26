<?php 
/*
 * @author: HarryXlb <harryxlb@gmail.com>
 * http://www.harenwang.com
 * @file: index.php
 * create date: 2015-08-23 15:14:54
 */
// ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL ^ E_NOTICE);
require './UserModel.php';

$title = 'REST API test by $_SESRVER[REQUEST_METHOD]';
$path_info = explode('/', $_SERVER['PATH_INFO']);
// print_r($path_info); exit1
$obj = ucfirst($path_info[1]) . 'Model';
// exit('obj: ' . $obj . PHP_EOL);
$param = isset($path_info[2]) ? intval($path_info[2]) : 0;
$model = new $obj;
$request_method = $_SERVER['REQUEST_METHOD'];
echo str_repeat('=', 110) . PHP_EOL; 
$file = './file.txt';

switch($request_method) {
case 'GET':
    echo 'GET method action.' . PHP_EOL;
    print_r($model->findOne($param));
    break;
case 'POST':
    echo 'POST method action.' . PHP_EOL;
    $userData = isset($_POST) ? $_POST : file_get_contents('php://input');
    $usersAll = $model->findAll();
    $usersData = array_merge($usersAll, $_POST);
    file_put_contents($file, var_export($usersData, true));
    print_r($userData);
    break;
case 'PUT':
    echo 'PUT method action.' . PHP_EOL;
    file_put_contents($file, 'update like this content, user: ' . file_get_contents('php://input'));
    print_r(file($file));
    break;
case 'DELETE':
    echo 'DELETE method action.' . PHP_EOL;
    unlink($file);
    echo 'delete the file success';
    break;
default:
    header("HTTP/1.1 405 $request_method METHOD IS NOT ALLOWED");
    break;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?=$title?></title>
</head>
<body>
<div id="wrapper">
<header></header>
<main>
<h1><?=$title?></h1>
<form id="form1" action="<?=$_SERVER['REQUEST_URI'];?>" method="POST">
    <input type="text" name="username">
    <input type="password" name="password">
    <input type="submit" name="btnSubmit" value="submit">
</form>
</main>
<footer>
   <p id="pfoot" style="height: 5em; width: 80%; text-align:center;">
      Copy &copy; <?=date('Y');?>
   </p>
</footer>
</body>
</html>
