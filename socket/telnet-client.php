<?php 
// This will print the banner from a true 'telnet' server (router, switch, host, etc).

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, '127.0.0.1', 23);

while (TRUE) {
        $r = array($socket);
        $c = socket_select($r, $w = NULL, $e = NULL, 5);

        foreach ($r as $read_socket) {
                if ($r = negotiate($read_socket)) {
                        var_dump($r);
                        exit;
                }
        }
}

function negotiate ($socket) {
        socket_recv($socket, $buffer, 1024, 0);

        for ($chr = 0; $chr < strlen($buffer); $chr++) {
                if ($buffer[$chr] == chr(255)) {

                        $send = (isset($send) ? $send . $buffer[$chr] : $buffer[$chr]);

                        $chr++;
                        if (in_array($buffer[$chr], array(chr(251), chr(252)))) $send .= chr(254);
                        if (in_array($buffer[$chr], array(chr(253), chr(254)))) $send .= chr(252);

                        $chr++;
                        $send .= $buffer[$chr];
                } else {
                        break;
                }
        }

        if (isset($send)) socket_send($socket, $send, strlen($send), 0);
        if ($chr - 1 < strlen($buffer)) return substr($buffer, $chr);

}