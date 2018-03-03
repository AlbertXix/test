<?php 
define('HOST', '192.168.1.115');
define('PORT', 9999);

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (! $sock) exit_socket_error($sock);

socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);

if (! socket_bind($sock, HOST, PORT)) exit_socket_error($sock);

if (! socket_listen($sock, 2018)) exit_socket_error($sock);

socket_set_nonblock($sock);

echo 'Host: ' . HOST, ', Port: ' . PORT . PHP_EOL;
echo 'Listening...' . PHP_EOL;

$arr_sock = []; 
$buff = ''; 
$echo_message = '';
$username = 'Anonymous';
$read = [];
$write =  $except = NULL;

while (true){
    $conn = socket_accept($sock);
    if (is_resource($conn)){
        // socket_set_nonblock($conn);
        $read[] = $conn;
    } else {
        continue;
    }

    // var_dump($conn);

    if (socket_select($read, $write, $except, 3)  == false){
        continue;
    }

    if (socket_getpeername($conn, $client_host, $client_port)){
        echo 'Client ' . $client_host . ': ' . $client_port . ' connected.' . PHP_EOL;
    }

    // $buf_len = socket_recv($conn, $message, 1024, MSG_OOB);
    $message = trim(@socket_read($conn, 1024, PHP_NORMAL_READ));
    if (empty($message)) continue;
    
    broadcast_message($read, $message); continue;

    if (strpos($message, '|')){
        $arr_message = explode('|', $message);
        if ($arr_message[0] == 'USERNAME'){
            $username = $arr_message[1];
            $echo_message = 'Welcome ' . $username . PHP_EOL;
        } else {
            $echo_message = 'Welcome user ' . (int) $conn;
        }
    } else {
        $echo_message = $username . ': ' . $message . PHP_EOL;
        // $len = socket_write($conn, $message, strlen($message));
        // if (! $len) exit_socket_error($conn);
    }
    
    echo $echo_message;
    // $len = socket_write($conn, $echo_message, strlen($echo_message));
    // if (! $len) exit_socket_error($conn);
    
    broadcast_message($read, $echo_message);
}

socket_close($sock);
exit(0);

function exit_socket_error($socket = NULL){
    exit(socket_strerror(socket_last_error($socket)) . PHP_EOL);
}

function broadcast_message($arr_sock, $message){
    foreach ($arr_sock as $sock){
        socket_write($sock, $message, strlen($message));
    }
}

function test_echo($conn){
    socket_set_nonblock($conn);
    for(;;){
        $str_rand = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
        echo $str_rand;
        if (is_array($conn)){
            foreach ($conn as $sk) {
                socket_write($sk, $str_rand);
            }
        } else {
            if (!socket_write($conn, $str_rand)) exit(socket_strerror(socket_last_error($conn)));
        }
    }
}
