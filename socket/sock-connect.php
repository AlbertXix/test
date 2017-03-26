<?php 
// Socket服务端

set_time_limit(0);

$ip = '127.0.0.1';
$port = 9999;
// $hSocket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
$hSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or exit('Socket init failed:' . socket_strerror(socket_last_error()));
echo 'Socket Server Init OK!' . PHP_EOL;
$sockClient = socket_connect($hSocket, $ip, $port) or exit('Connect error: ' . socket_strerror(socket_last_error()));

while (true){
	$stdin = file_get_contents('php://STDIN');
	if ($stdin == 'quit') exit();
	socket_send($hSocket, $stdin, 1024, MSG_OOB);
}