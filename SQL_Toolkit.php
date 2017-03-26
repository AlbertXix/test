<!DOCTYPE HTML>
<html>
<head><title>SQL Toolkit1.0</title>
<style type="text/css">
#dbconn{background: #ddeefe; border: 1px solid #abb; width: 800px; margin: 100px auto; padding: 20px; font-size:16px; font-family: "Microsoft YaHei",黑体}
#dbconn ul{list-style:none;}
#dbconn ul li{line-height: 30px;}
#dbconn ul li textarea{width:600px; height:200px;border:1px solid $ffa}
</style>
</head>
<body>
<div id="dbconn"><ul>
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
<li>Host/IPad: <input type="text" name="txthost" value="localhost">
Database: <input type="text" name="txtdatabase" value="discuzz2"></li>
<li>Username: <input type="text" name="txtusername" value="harryxlb">
Password: <input type="password" name="txtpassword"></li>
<li><textarea name="sql_cmd" /></textarea>
<input type="file" name="sql_file" /><input type="submit" name="btn_post" value="Execute->" /></li>
</form></ul>


<?php
if(isset($_POST['btn_post'])){
$host = $_POST['txthost']; $dbname = $_POST['txtdatabase']; $username = $_POST['txtusername']; $password = $_POST['txtpassword'];
$link = mysql_connect($host, $username, $password) or exit('database connect failed!');
echo 'connected success!<br />';
mysql_select_db($dbname) or exit('database cannnt be selected!');
mysql_query('set names utf8',$link);

if(isset($_POST['sql_file'])){
	$strfile = $_POST['sql_file'];
	_sql_exec($strfile);
}elseif(isset($_POST['sql_cmd'])){
	$strcmd = $_POST['sql_cmd'];
	$sqlcontent = exec_split_sqls($strcmd);
								}
}


function _sql_exec($tmdfile){
	$fp = fopen($tmdfile,'r');
	$sqlcontent = fread($fp,filesize($tmdfile));
	while(!feof($fp)){
	$sqlcontent = exec_split_sqls($sqlcontent);
					}
	fclose($fp);
}

function exec_split_sqls($sqls){
$sqls = explode(';',$sqls);
	if(is_array($sqls)){
		foreach($sqls as $sql){
				echo $sql;
				$query = mysql_query($sql, $link) or exit('Query error! '.mysql_error());
				//echo 'Connected succuss! <br />Now is executing sql: "'.$sql.'"';
								}
						}
	else{
		echo $sqls;
		mysql_query($sqls,$link) or exit('mysql query error...'.mysql_error());
		}

}
?>
</div>
</body>
</html>
