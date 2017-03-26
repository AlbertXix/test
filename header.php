<?php 
/*
 * @author: HarryXlb <harryxlb@gmail.com>
 * http://www.harenwang.com
 * @file: header.php
 * create date: 2014-12-13 23:56:42
 */

$lastModified = 'Last-Modified: ' . date('D, d M Y H:i:s') . ' GMT';
header($lastModified);
header('Cache-Control: max-age=3600');
header('Expires: '. gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
session_start();
$_SESSION['username'] = 'admin';
echo $lastModified . PHP_EOL; 
echo 'Hello, ' . $_SESSION['username'] . PHP_EOL;
