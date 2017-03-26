<?php
/**
 * 函数：提供给客户端进行连接XML-RPC服务器端的函数
 * 参数：
 * $host 需要连接的主机
 * $port 连接主机的端口
 * $request 封装的XML请求信息
 * 返回：连接成功成功返回由服务器端返回的XML信息，失败返回false
 */

function do_call($host, $port, $request) { 
    //打开指定的服务器端

    $fp = fsockopen($host, $port, $errno, $errstr); 
    //构造需要进行通信的XML-RPC服务器端的查询POST请求信息

    $query  = "POST /WebService/xmlrpc_server.php HTTP/1.0" . PHP_EOL;
    $query .= "Host: ". $host . PHP_EOL;
    $query .= "Content-Type: text/xml" . PHP_EOL;
    $query .= "User_Agent: My Egg Client" . PHP_EOL;
    $query .= "Content-Length: ".strlen($request). PHP_EOL . PHP_EOL;
    $query .= $request . PHP_EOL;
    
    //把构造好的HTTP协议发送给服务器，失败返回false
    if (!fputs($fp, $query, strlen($query))) { 
        $errstr = "Write error"; 
        return 0; 
    } 

    //获取从服务器端返回的所有信息，包括HTTP头和XML信息
    $contents = ''; 
    while (!feof($fp)) { 
        $contents .= fgets($fp); 
    } 

    //关闭连接资源后返回获取的内容
    fclose($fp); 
    return $contents; 
} 

//构造连接RPC服务器端的信息
$host = 'localhost'; 
$port = 80; 

//把需要发送的XML请求进行编码成XML，需要调用的方法是cycle，参数是egg
$request = xmlrpc_encode_request('cycle', 'egg'); 

//调用do_call函数把所有请求发送给XML-RPC服务器端后获取信息
$response = do_call($host, $port, $request); 

//分析从服务器端返回的XML，去掉HTTP头信息，并且把XML转为PHP能识别的字符串

$split = '<?xml version="1.0" encoding="iso-8859-1"?>';

$xml = explode($split, $response);
$xml = $split . array_pop($xml);
//$response = xmlrpc_decode($xml);

//输出从RPC服务器端获取的信息
print_r($response);

