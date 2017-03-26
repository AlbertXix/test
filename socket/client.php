<?php 
// socket客户端

set_time_limit(0);

$serverIp = '127.0.0.1';
$serverPort = 9999;
$sockClient = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$message = 'Connecting to ' . $serverIp . ' at port ' . $serverPort . '...';
if (!socket_connect($sockClient, $serverIp, $serverPort)){
	$errid = socket_last_error();
	socket_clear_error();
	exit($message . ' error: ' . socket_strerror($errid));
}
echo $message . 'OK!' . PHP_EOL;
fwrite(STDOUT, 'What\' your name: ');
$user = fgets(STDIN);
fwrite(STDOUT, "welcome, $user");
while(fgets(STDIN) != 'exit'){
	$buffer = $user . ': ' . fread(STDIN, 1024) . PHP_EOL;
	socket_send($sockClient, $buffer, strlen($buffer), 0);
	while ($buffer = socket_read($sockClient, 1024, PHP_NORMAL_READ)) {
		socket_write($sockClient, $buffer, strlen($buffer));
	}

	if (fgets(STDIN) == 'exit') { 
		echo('Closing the connect...');
		socket_close($sockClient);
		exit('OK!');
	}
}
socket_close($sockClient);
