<?php 

$email = "abc@efg.com";
echo $email . ' is a ' . ( validateEmail($email) ? 'valid' : 'invalid' ) . ' email address.<br />';


$email = "abc@efg";
echo $email . ' is a ' . ( validateEmail($email) ? 'valid' : 'invalid' ) . ' email address.<br />';

function validateEmail($emailAddress){
	return filter_input(INPUT_GET, $emailAddress);
}