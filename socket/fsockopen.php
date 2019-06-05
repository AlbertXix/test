<?php
echo "fsockopen usage" . PHP_EOL;
echo "localhost:" . PHP_EOL;
$fp = fsockopen("localhost", 80, $errno, $errstr, 30);
if (!$fp) exit("$errstr ($errno)<br />\n");
$out = "GET /dede_ylm HTTP/1.1\r\n";
$out .= "Host: localhost\r\n";
$out .= "Connection: Close\r\n\r\n";
fwrite($fp, $out);
while (!feof($fp)) {
	echo fgets($fp, 128);
}
fclose($fp);

echo "www.qq.com:" . PHP_EOL;
$fp2 = fsockopen("www.qq.com", 80, $errno2, $errorstr2, 20);
if (!$fp2) exit("$errorstr2");
$ReqData = "GET /boiis HTTP/1.1\r\n";
$ReqData .= "HOST: localhost\r\n";
$ReqData .= "Connection: Close\r\n\r\n";
fwrite($fp2, $ReqData);
while (!feof($fp2)){
	echo fgets($fp2, 1024);
}
fclose($fp2);
?> 
