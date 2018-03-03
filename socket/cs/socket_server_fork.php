<?php 
define('HOST', '127.0.0.1');
define('PORT', 9988);

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (! $sock) exit_socket_error($sock);

if (! socket_bind($sock, HOST, PORT)) exit_socket_error($sock);

if (! socket_listen($sock)) exit_socket_error($sock);

socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1); 
socket_set_nonblock($sock);

echo 'Host: ' . HOST, ', Port: ' . PORT . PHP_EOL;
echo 'Listening...' . PHP_EOL;

$arr_sock = []; 
$buff = ''; 

while (true){
    $socka = @socket_accept($sock);
    if ($socka){
        socket_set_nonblock($socka);
        $arr_sock[] = $socka;
    } else {
        continue;
    }   

    if (pcntl_fork() == 0){ 
        // $buf_len = socket_recv($socka, $buff, 1024, MSG_DONTWAIT);
        $message = socket_read($socka, 1024) . PHP_EOL;
        if (! empty($message)){
            echo 'client message: ' . $message . PHP_EOL;
            //socket_write($socka, 'Hello, I\'m socket server.' . PHP_EOL);
            socket_write($socka, 'client: ' . $message, strlen($message));
            // broadcast_message($arr_sock, $message);
        }   

        socket_close($socka);
        exit(0);
    } else {
        socket_close($socka);
    }   

}

function exit_socket_error($socket){
    exit(socket_strerror(socket_last_error($socket)) . PHP_EOL);
}

function broadcast_message($arr_sock, $message){
    foreach ($arr_sock as $sock){
        socket_write($sock, $message, strlen($message));
    }
}
