<?php

require './class/DB.class.php';

$DB = new DB();
$sql = 'SELECT * FROM 58tc_category';
//print_r($RS = $DB->fetch($sql));
//print_r($RS = $DB->fetchAll($sql));

$sql2 = 'INSERT INTO test VALUES (null, "hello", "helloworld.")';
//$DB->exec($sql2);
//$DB->execute($sql2);

$data = array('name' => 'BillGates', 'password' => '123!@#');
//$DB->insert('test', $data);

$data = array('name' => 'aobama', 'password' => 'guessit..');
//$DB->update('test', $data, 'id % 2 = 0');

//$DB->delete('usertest', 'id>2');
$DB->delete('usertest', array('id' => 5, 'user' => 'hey'));

print_r($DB->fetchAll('SELECT * FROM `usertest`'));