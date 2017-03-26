<?php
	$arr_test = array(
			'uid'=>'001',
			'username' => 'xlb',	
			'password' => 'ABd0912CEF',
	);
	echo is_array($arr_test) ? 'ok' : 'not a array';
	echo '<br />';
	foreach ($arr_test as $key => $value){
		echo $key . ": " . $value . "<br />";
	}
?>