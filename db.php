<?php
$dbconn = mysql_connect("localhost","xilibo","xlb123") or die("connection error!");
echo "connection success!" . PHP_EOL;
$result = mysql_select_db("bocms");
$sql = "select * from bo_member";
$query = mysql_query($sql);

while ($rs = mysql_fetch_array($query, MYSQLI_ASSOC)){
	echo "Username: " . $rs['username'] . " | Password: " . $rs['password'] . PHP_EOL;
}
