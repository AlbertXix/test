<?php 
$str = 'abcdefg';
for ($i = strlen($str); $i >= 0; $i--) 
	$tempstr .= $str[$i];
echo $tempstr;