<?php
if (!isset($_SERVER['PATH_INFO'])){
	$pathbits= array('');
}else{
	$pathbits = explode("/",  $_SERVER['PATH_INFO']);
}
if (!isset($pathbits[1]) || $pathbits[1] == ""){
	$page = "index"
}else{
	$page = basename($pathbits[1]);
}
$file = "./Home/{$page}.php";
if (!is_file($file)){
	echo "File not found";
}else{
	require $file;
}
?>