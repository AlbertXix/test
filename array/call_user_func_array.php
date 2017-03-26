<?php
$myArr = array(1, 2);

function addFunc($num1, $num2){
	return $num1 + $num2;
}

$result = call_user_func_array('addFunc', $myArr);
echo 'call_user_func_array(): ' . $result . '<br />';
echo 'call_user_func: ' . call_user_func('addFunc', 3, 4) . '<br />';
echo 'call_user_func("md5", "xlb"): ' . call_user_func('md5', 'xlb');

