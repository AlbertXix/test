<?php
	$dirlist = array(
	1=>"dir1",
	2=>"dir2",
	3=>"dir3",
)

function writeok($arr){
		$msg = is_writeable($arr])?"可写":"不可写";
		return $msg;
						}

for ($i=1;$i<=3;$i++){
	echo writeok($dirlist[$i])."<br />";
	}
?>