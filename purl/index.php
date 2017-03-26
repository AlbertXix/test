<?php
// phpinfo();
require 'news.php';
require 'purl.php';
$news = new News(001, 'The Life', 'What\'s mean of the life.');
// $news->showNews();
$strUrl = isset($argv[1]) ? $argv[1] : $_GET['u'];
// $purl = new Purl($strUrl, 'c');
$purl = new Purl($strUrl, 'g');

