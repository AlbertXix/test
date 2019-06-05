<!DOCTYPE HTML>
<html>
<head><title>SQL Toolkit1.0</title></head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
<input type="file" name="sql_file" /><input type="submit" name="btn_post" value="Execute!" />
</form>

<?php
$link = mysql_connect('localhost','harryxlb','ligexi007','discuzz2') or exit('database connect failed!');
mysql_query('set names utf8');
mysql_select_db('discuzz2');
echo 'database connected succss!<br />';
if(isset($_POST['btn_post'])){
	//$strfile = $_POST['sql_file'];
	$strfile = "win87_tables.txt";
	$fp = fopen($strfile,'r');
	$fp2 = fopen('tablelist.txt','w');
	while(!feof($fp)){
		$sqlcontent = trim(fgets($fp));
		//$sqlcontent = trim(fgets($fp),'\x00..\x1F');
		//mysql_query($sqlcontent) or die('Query error! ' . mysql_error());
		//echo 'Now is execute sql: ' . $sqlcontent . '<br />';
		$sqlnew .= str_replace($sqlcontent,$sqlcontent.";\n",$sqlcontent);
		}
	fwrite($fp2,$sqlnew);
	fclose($fp);
}			
?>

</body>
</html>