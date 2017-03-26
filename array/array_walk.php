<?php 
$arr = array('apple' => 'red', 'orange' => 'orange', 'banana' => 'yellow');

$arr = array_walk($arr, "myFunc");

// $arr = array_walk($arr, function($value, $key){
// 	$value = 'golden';
// });

print_r($arr);

function myFunc(&$value, $key){
	$value = 'golden';
}
