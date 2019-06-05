<?php 

function &refTest(){
	static $apple = 0;
	$apple++;
	echo 'apple: ' . $apple . '<br />';
	return $apple;
}

$app = & refTest();
$app = 10;
$app2 = & refTest();
echo 'app2: ' . $app2;