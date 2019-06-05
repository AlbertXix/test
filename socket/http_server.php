<?php
  // Reduce the amount of warnings displayed
  error_reporting(E_ALL ^ E_NOTICE);

  // Set up socket for listening 
  $host_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
  if(!$host_socket) die("Failed to start event server. socket_create: ". socket_strerror(socket_last_error())."\n");

  // set the option to reuse the port
  if(! socket_set_option($host_socket, SOL_SOCKET, SO_REUSEADDR, 1) )
    die("Failed to start event server. socket_set_option: ". socket_strerror(socket_last_error())."\n");

  // bind host socket to localhost or 0.0.0.0 on port 8080
  if(! socket_bind($host_socket,"0.0.0.0",8080) )
    die("Failed to start event server. socket_bind: ". socket_strerror(socket_last_error())."\n");

  // start listening for connections
  if(! socket_listen($host_socket,10) )
    die("Failed to start event server. socket_listen: ".socket_strerror(socket_last_error())."\n");

  while (true) {
    // Make list of sockets to listen for changes in, including host
    $read = array($host_socket);
         
    // get a list of all the clients that have data to be read from
    $ready=@socket_select($read, $write = NULL, $except = NULL,0);
    if ($ready=== false)
      die("Failed to listen for clients: ". socket_strerror(socket_last_error()));

    // a client request service
    elseif($ready>0){
      // accept new client
      $newsocket = socket_accept($host_socket);

      // Read from socket
      $input = socket_read($newsocket, 1024);
      if($input){
        unset($client_header);
        // Read headers; Split into safe lines
        $line=explode("\n",preg_replace('/[^A-Za-z0-9\-+\n :;=%*?.,\/_]/','',substr($input,0,2000)));
        // Split request line into its parts
        list($client_header["method"],$client_header["url"],$client_header["protocol"])=explode(" ",$line[0]);
        // Remove the request line again.
        unset($line[0]);
        // Make key=value array of headers
        foreach($line as $l){
          list($key,$val)=explode(": ",$l);
          if($key) $client_header[strtolower($key)]=$val;
        }
        // Get IP of client
        socket_getpeername($newsocket, $client_header['ip']);

        // Decode url
        $client_header+=(array)parse_url($client_header['url']);
        parse_str($client_header['query'],$client_header['arg']);

        print_r($client_header);

        // Serve file
        if(strpos($client_header['path'],".html") && file_exists(__DIR__.$client_header['path'])){
          echo "Sending a HTML page to client\n";
          socket_write($newsocket,"$client_header[protocol] 200 OK\r\n");    
          socket_write($newsocket,"Content-type: text/html; charset=utf-8\r\n\r\n");
          socket_write($newsocket,file_get_contents(__DIR__.$client_header['path'])."\r\n\r\n");
          socket_close($newsocket);
        }elseif($client_header['path']=="/test"){
          echo "Sending test HTML page to client\n";
          socket_write($newsocket,"<!DOCTYPE HTML><html><head><html><body><h1>Its working!</h1>Have fun\r\n");
          socket_write($newsocket,"<pre>Request header: ". print_r($client_header,true) . "</pre>\r\n");
          socket_write($newsocket,"</body></html>\r\n\r\n");
          socket_close($newsocket);
        }else{
          echo "$client_header[protocol] 404 Not Found\r\n";
          socket_write($newsocket,"$client_header[protocol] 404 Not Found\r\n\r\n");   
          socket_close($newsocket);
        }
      }
    }
  }
  socket_close($host_socket);