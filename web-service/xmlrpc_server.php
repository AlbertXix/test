<?php
/**
 * 函数：提供给RPC客户端调用的函数
 * 参数：
 * $method 客户端需要调用的函数
 * $params 客户端需要调用的函数的参数数组
 * 返回：返回指定调用结果
 */

function lifecycle($method, $params) { 
/* $method = 'cycle', $params = (array of) request parameter(s); $data is also passed from xmlrpc_server_call_method, if we had any data to pass */ 
    switch($params[0]) { 
        case 'egg': 
            $reply = 'All eggs will be birds one day.'; 
        break; 
        default: 
            $reply = 'That must have been an otheregg'; 
    } 
    return $reply; 
} 

//产生一个XML-RPC的服务器端

$server = xmlrpc_server_create(); 

/* register the 'external' name and then the 'internal' name */ 
xmlrpc_server_register_method($server, "cycle", "lifecycle"); 

$request = $HTTP_RAW_POST_DATA; // no you don't need 'always on', and no $_POST doesn't work. 


/* the parameters here are 'server, xml-string and user data'. There's supposed to be an optional 'output options' array too, but I can't get it working :( hence header() call */ 
$response = xmlrpc_server_call_method($server, $request, null); 
header('Content-Type: text/xml'); 
print $response; 
//销毁XML-RPC服务器端资源 

xmlrpc_server_destroy($server); 


