<?php
$userArr = array(
	array('user' => 'xlb', 'postcode' => 438400),
	array('user' => 'harry', 'postcode' => 430079),
	array('user' => 'potter', 'postcode' => 435656),
	array('user' => 'bill', 'postcode' => 567788),
	array('user' => 'gates', 'postcode' => 334455),
	array('user' => 'aobama', 'postcode' => 101010),
);

var_dump($userArr);
print_r("array_rand() index: " . array_rand($userArr));
echo '<br />  user: <br />';
print_r($userArr[ array_rand($userArr) ]);