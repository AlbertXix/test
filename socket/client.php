<?php 

$serverIp = '127.0.0.1';
$serverPort = 9999;
$sockClient = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$message = 'Connecting to ' . $serverIp . ' at port ' . $serverPort . '...';
if (!socket_connect($sockClient, $serverIp, $serverPort)){
	$errid = socket_last_error();
	socket_clear_error();
	exit($message . ' error: ' . socket_strerror($errid));
}

socket_set_option($sockClient, SOL_SOCKET, SO_REUSEADDR, 1);
// socket_set_nonblock($sockClient);

echo $message . 'OK!' . PHP_EOL;
fwrite(STDOUT, 'What\'s your name: ');
$user = fgets(STDIN);
// fwrite(STDOUT, "welcome, $user");
if (! socket_write($sockClient, 'USERNAME|' . $user))
	exit('Send username faild ' . socket_strerror(socket_last_error($sockClient)));
// $echo_message = socket_read($sockClient, 1024);
// if ($echo_message) echo $echo_message . PHP_EOL;

do {
	$buffer = socket_read($sockClient, 1024);
	echo $buffer;

	echo 'You say: ';
	$buffer = $user . ': ' . fgets(STDIN);
	// socket_send($sockClient, $buffer, strlen($buffer), 0);
	if (! socket_write($sockClient, $buffer)){
		exit(socket_strerror(socket_last_error()));
	}
} while(fgets(STDIN) != 'exit');

echo('Closing the connect...');
exit('OK!');
socket_close($sockClient);
