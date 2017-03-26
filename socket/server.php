<?php 
// Socket服务端

set_time_limit(0);

$ip = '127.0.0.1';
$port = 9999;

$sockServer = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or exit('Socket init failed.' . socket_strerror());
echo 'Socket Server Init OK!' . PHP_EOL;

socket_bind($sockServer, $ip, $port) or exit('Binding IP and PORT error: ' . socket_strerror());
echo 'Binding ip: ' . $ip . ', port: ' . $port . '...OK!' . PHP_EOL;

socket_listen($sockServer);
echo 'Begin listen...OK!' . PHP_EOL;

socket_set_nonblock($sockServer);

$conn = socket_accept($sockServer);

$buffer = 'No data in buffer.';

// do {
// 	if ($buffer != '')
// 		socket_write($sockServer, $buffer, strlen($buffer));
// 	else echo $buffer . PHP_EOL;

// 	while ($buffer = socket_read($sockServer, 1024, PHP_NORMAL_READ)) {
// 		// socket_write($sockServer, 'Message received.');
// 		// printf($$buffer . PHP_EOL);
// 		socket_write($sockServer, $buffer, strlen($buffer));
// 	}
// } while (true);

while(true)
{
    if(($newc = socket_accept($sockServer)) !== false)
    {
        echo "Client $newc has connected\n";
        $clients[] = $newc;
    }
}

socket_close($sockServer);