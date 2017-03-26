<?php
	$dirlist = array(
	0=>"./dir1",
	1=>"dir2",
	3=>"dir3"
)

function writeok(){
	for($i=0;i<=3;i++){
		return writeable($dirlist[$i]));
	}
}

echo writeok();
?>