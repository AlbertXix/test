<!DOCTYPE HTML>
<html>
<head><title>SQL Toolkit1.0</title></head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
<input type="file" name="sql_file" /><input type="submit" name="btn_post" value="Execute!" />
</form>

<?php
$link = mysql_connect('localhost','pt567','o3I9E5Ced','pt567') or exit('database connect failed!');
mysql_query('set names utf8');
mysql_select_db('pt567');
echo 'database connected succss!<br />';
if(isset($_POST['btn_post'])){
	$strfile = $_FILES['sql_file']['name'];
	//$strfile = "cenwor.txt";
	$fp = fopen($strfile,'r');
	while(!feof($fp)){
		$sqlrow = 'drop table ' . trim(fgets($fp),'\x00..\x1F');
		echo 'Now is execute sql: ' . $sqlrow . '......';
		mysql_query($sqlrow) or die('<span style="float: right; color: red; font-size: 14px;font-weight:bold">X</span><br />Query error! ' . mysql_error());
		echo '<span style="float: right; color: green; font-size: 14px; font-weight:bold">¡Ì</span><br />';
		//sleep(1);
		}
	fclose($fp);
}			
?>

</body>
</html>