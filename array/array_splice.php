<?php
	$arr_mobile = array('Apple', 'HTC', 'Nokia', 'SAMSUNG');
	print_r($arr_mobile);
	echo '<br />return the element deleted by array_splice() :<br />';
	$arr_sp = array_splice($arr_mobile, 0, 2);
	echo '<br />';
	print_r($arr_sp);
	echo 'return the element exist after array_splice() :<br />';
	foreach ($arr_mobile as $arr_obj){
		echo $arr_obj . '<br />';
	}
?>