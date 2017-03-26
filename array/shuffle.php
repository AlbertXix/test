<?php 
$data[] = array(
	"name" => "简明现代魔法",
	"rank" => "40"
);

$data[] = array(
	"name" => "博客园",
	"rank" => "50"
);

$data[] = array(
	"name" => "CSDN",
	"rank" => "60"
);

$data[] = array(
	"name" => "ITEYE",
	"rank" => "50"
);

print_r($data);

shuffle($data);

$i = 0;
foreach($data as $key =>$value ){
	if($i < 2) {
		echo $data[$key]['name'].'<br />';
	}
	$i++;
}