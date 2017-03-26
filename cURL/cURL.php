<?php
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://www.baidu.com');
$myopt = curl_exec($curl);
$info = curl_getinfo($curl);
print_r($info);
echo $myopt;
curl_close($curl);
?>