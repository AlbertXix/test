<!DOCTYPE HTML>
<html>
<head><title>SQL Toolkit1.0</title></head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
<input type="file" name="sql_file" /><input type="submit" name="btn_post" value="Execute!" />
</form>

<?php
$link = mysql_connect('localhost','lf2mq4','mq13906905835','lf2mq4') or exit('database connect failed!');
mysql_query('set names utf8');
mysql_select_db('lf2mq4');
echo 'database connected succss!<br />';
if(isset($_POST['btn_post'])){
	//$strfile = $_FILES['sql_file']['name'];
	$strfile = "dz.txt";
	$fp = fopen($strfile,'r');
	while(!feof($fp)){
		$sqlrow = trim(fgets($fp),'\x00..\x1F');
		$sqlrow = "drop table" . $sqlrow;
		mysql_query($sqlrow) or die('Query error! ' . mysql_error());
		echo 'Now is execute sql: ' . $sqlrow . '......OK!<br />';
			
		}
	fclose($fp);
}			
?>

</body>
</html>