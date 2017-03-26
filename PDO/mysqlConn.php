<?php
$dsn = 'mysql:host=localhost; dbname=boiis';
try{
	$pdo = new PDO($dsn, 'harryxlb', 'ligexi007');
}catch(PDOException $ex){
	exit($ex->getMessage());
}
$rs = $pdo->query('SELECT * FROM bo_blog');
$rs->setFetchMode(PDO::FETCH_ASSOC);
// print_r($rs->fetch());
// print_r($rs->fetchAll());

while ($info = $rs->fetch()){
	echo 'id: ' . $info['id'] . '  title: ' . $info['title'];
	echo '<br />';
}

