<?php 

Router::get('/', function(){
    
    echo 'closure call success !' . PHP_EOL;

    $arg = func_get_arg(0);

    echo 'arg: ' . $arg . PHP_EOL;

    $args = func_get_args();

    echo 'hello, ' . $args[0]  . ' ' . $args[1] . PHP_EOL;

}, array('user' => 'xlb'));


Router::post('/post', function(){
    echo 'POST here' . PHP_EOL;
});


class Router
{
    static function get($path, $function, $params = array()){
        
        array_unshift($params, $path);

        call_user_func_array($function, $params);
    }


    static function post($path, $function, $params = array()){

        call_user_func_array($function, $params);
    }

}