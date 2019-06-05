<?php
// $url = "http://www.baidu.com/link?url=nS2MGJqjJ4zBBpC8yDF8xDh8vibi1lVeE7gGr9UONBu";
$url = "http://www.baidu.com/link?url=gCAr459n1blO5GoEfDsHCBdl6rpYRuuMG-zPNBwsXW4xJEDsR1rOD0ApP7VJH-tF";
$info = parse_url($url);
print_r($info);
$fp = fsockopen($info['host'], 80,$errno, $errstr, 30);
fwrite($fp,"GET {$info['path']}?{$info['query']} HTTP/1.1\r\n");
fwrite($fp, "Host: {$info['host']}\r\n");
fwrite($fp, "Connection: close\r\n\r\n");
$rewrite = '';
while(!feof($fp)) {
    $line = fgets($fp);
    if($line != "\r\n" ) {
        if(strpos($line,'Location:') !== false) {
			// echo($line);
            $rewrite = str_replace(array("\r","\n","Location: "),'',$line);
        }
    }else {
        break;
    }
}
var_dump($rewrite);
