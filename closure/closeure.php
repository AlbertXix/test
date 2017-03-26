<?php 

$func = function(){
	return 'simple anonymouse function without parameters.';
};

$user = 'xlb';
$func2 = function($who = 'Harry') use ($user){
	$who = 'HarryXLB';
	return $who . ' says: Hello, ' . $user;   //使用全局变量
};

echo $func();
echo PHP_EOL;
echo $func2();