<?php 
	$host = gethostbyname('www.qq.com');
	echo 'www.qq.com\'s IP is: ' . $host . '<br />';
	$long_ip = ip2long($host);
	echo 'output its Long format: ' . $long_ip . '<br />';
	echo 'back to its IP: ' . long2ip($long_ip);