<?php
define('HOST', '127.0.0.1');
define('PORT', 9999);

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (! $sock) exit_socket_error($sock);

if (! socket_connect($sock, HOST, PORT)) exit_socket_error($sock, 'connect faild.');

socket_write($sock, 'Hello, 2018.' . PHP_EOL);

$message = '';

do {
    $message = read_socket_message($sock);
    if ($message == false) continue;
    else echo $message;

    echo 'Plz input message: ';
    $message = trim(fgets(STDIN));
    $len = socket_write($sock, $message, strlen($message));
    if (! $len) exit_socket_error($sock);

} while ($message != 'quit');

socket_close($sock);
exit(0);

function exit_socket_error($socket, $errstr = ''){
    exit(socket_strerror(socket_last_error($socket)) . ' ' . $errstr . PHP_EOL);
}

function read_socket_message($sock){
    $message = '';
    $msg_recv = @socket_read($sock, 1024) . PHP_EOL;
    // while($buf_len = socket_recv($sock, $msg_recv, 1024, MSG_PEEK)){
    // while($msg_recv = @socket_read($sock, 1024)){
    if ($msg_recv !== false)
        $message .= 'Server: ' . $msg_recv . PHP_EOL;
    else 
        $message = false;
    // }

    return $message;
}
