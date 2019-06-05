<?php
header('Content-Type: text/html; charset=UTF-8');
$str = '┼┘┌Sнр╣дсявп';
//echo iconv('GBK','UTF-8'//IGNORE,$str);
echo iconv('GBK','UTF-8',$str);
?>