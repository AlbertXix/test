<?php
define('HOST', '192.168.1.115');
define('PORT', 9999);

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (! $sock) exit_socket_error($sock);

if (! socket_connect($sock, HOST, PORT)) exit_socket_error($sock, 'connect faild.');

// socket_set_nonblock($sock);

socket_write($sock, 'Hello, 2018.' . PHP_EOL);

while(TRUE){
    $msg_recv = @socket_read($sock, 1024);
    // while($buf_len = socket_recv($sock, $msg_recv, 1024, MSG_PEEK)){
    // while($msg_recv = socket_read($sock, 1024)){
        echo 'recv: ' . $msg_recv . PHP_EOL;
    // }

    echo 'Plz input message: ';
    $message = trim(fgets(STDIN));

    if ($message == 'quit'){
        socket_close($sock);
        exit(0);
    }   

    $len = socket_write($sock, $message, strlen($message));
    if (! $len) exit_socket_error($sock);

    $msg_recv = socket_read($sock, 1024);
    if ($msg_recv){
        echo 'recv: ' . $msg_recv . PHP_EOL;
    }
}


function exit_socket_error($socket, $errstr = ''){
    exit(socket_strerror(socket_last_error($socket)) . ' ' . $errstr . PHP_EOL);
}
