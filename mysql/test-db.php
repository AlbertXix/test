<?php
define('DB_HOST', 'mysql-1.cp6iyq2ay531.us-west-1.rds.amazonaws.com');
define('DB_NAME', 'mysql');
define('DB_USER', 'jeetow');
define('DB_PASSWORD', 'jeetow1234');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_errno) exit("mysql connect failed. [$mysqli->errno] " . $mysqli->connect_error . PHP_EOL);
echo 'MySQL connect success via mysqli extension.' . PHP_EOL;

$sql = "select user, host from mysql.user";
$result = $mysqli->query($sql);
while($row = $result->fetch_array(MYSQLI_ASSOC)) {
    echo 'user: ' . $row['user'] . ', host: ' . $row['host'] . PHP_EOL;
}
