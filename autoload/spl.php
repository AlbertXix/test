<?php

function autoMyLoad($className){
	$filepath = './class/' . $className . '.php';
	if (is_file($filepath))
		require_once $filepath;
}

spl_autoload_register('autoMyLoad');

$apple = new Apple();
$orange = new Orange;