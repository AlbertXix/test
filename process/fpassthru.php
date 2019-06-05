<?php
// readfile("http://www.baidu.com");exit;
$file = fopen("http://www.baidu.com","rb");
echo fgets($file);
// echo fpassthru($file);
fclose($file);
?>