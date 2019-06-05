<?php
define('HOST', '0.0.0.0');
define('PORT', 9999);
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($sock, HOST, PORT);
socket_listen($sock);

$clients = array($sock);
$write =  $except = NULL;

while (true) {
   // create a copy, so $clients doesn't get modified by socket_select()
   $read = $clients;
   // get a list of all the clients that have data to be read from
   // if there are no clients with data, go to next iteration
   if (socket_select($read, $write, $except, 30) === false)
       continue;
   
   // check if there is a client trying to connect
   if (in_array($sock, $read)) {
       // accept the client, and add him to the $clients array
       $newsock = socket_accept($sock);
       $clients[] = $newsock;
       socket_write($newsock, "no noobs, but ill make an exception :)\n".
       "There are ".(count($clients) - 1)." client(s) connected to the server\n");
       socket_getpeername($newsock, $ip);
       echo "New client connected: {$ip}\n";
       
       // remove the listening socket from the clients-with-data array
       $key = array_search($sock, $read);
       unset($read[$key]);
   }
   
   foreach ($read as $read_sock) {
       $data = @socket_read($read_sock, 1024, PHP_NORMAL_READ);
       if ($data === false) {
           $key = array_search($read_sock, $clients);
           unset($clients[$key]);
           echo "client disconnected.\n";
           continue;
       }
       
       $data = trim($data);
       // check if there is any data after trimming off the spaces
       if (!empty($data)) {
           // send this to all the clients in the $clients array (except the first one, which is a listening socket)
           foreach ($clients as $send_sock) {
               // if its the listening sock or the client that we got the message from, go to the next one in the list
               if ($send_sock == $sock)
                   continue;
               
               // write the message to the client -- add a newline character to the end of the message
               socket_write($send_sock, $data."\n");
               
           } // end of broadcast foreach
           
       }
       
   }
}

socket_close($sock);