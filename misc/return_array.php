<?php
	function get_myarr(){
		return array(
			'001', 'xlb', 'xilige',
		);
	}

	list($me_id, $me_name, $me_pass) = get_myarr();
	$tempvar = 'id: ' . $me_id . '<br />';
	$tempvar .= 'username: ' . $me_name . '<br />';
	$tempvar .= 'password: ' . $me_pass . '<br /><hr />';
	echo $tempvar;

	function get_myarr2(){
			return array(
			'id'=>'1',
			'username'=>'harryxlb',
			'password'=>'letmein',
			);
		}
			
		$my_info = array();
		$my_info = get_myarr2();
		//echo $my_info['username'];
		foreach($my_info as $k => $v){
			echo $k . ': ' . $v . '<br />';
		}
?>