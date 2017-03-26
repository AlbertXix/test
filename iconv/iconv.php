<?php 
header('content-type:text/html; charset=utf-8');
$xifile = file_get_contents('xi.txt');
echo iconv('gbk', 'utf-8', $xifile);