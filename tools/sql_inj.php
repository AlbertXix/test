<!doctype html>
<html>
<head>
<title>SQL Injection Test</title>
<style type="text/css">
html, body {background: silver; padding: 0; margin: 0; color: #0a0; font: 1.1em/.8 "Microsoft Yahei", simhei bold; }
#wrapper { width: 980px; margin: 10px auto;background: skyblue; padding: 30px 10px;  }
#wrapper #frmDiv { width: 100%; height: 200px; }
input, button { border: 1px solid #0a0; color: #0a0; }
</style>
</head>
<body>
<div id="wrapper">
	<div id="frmDiv">
		<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
			<label>Username: </label><input type="text" name="user" />
			<label>Password: </label><input type="password" name="password" />
			<input type="submit" name="btnEntry" value="Entry" />
			<!--<button name="btnEntry">Let Me In</button> -->
		</form>
	</div>
	<div id="result">
<?php
	$link = mysql_connect('localhost', 'root', 'xlb') or exit('DB Connect Error.');
	mysql_select_db('hacker');
	mysql_query('set names utf8');
	$id = isset($_GET['id']) ? $_GET['id'] : 1;
	$sql_app = "SELECT id, app_name, app_desc FROM app WHERE id=$id";
	$query = mysql_query($sql_app);
	$rs = mysql_fetch_array($query);
	echo 'id: ' . $rs['id'] . '  app name: ' . $rs['app_name'] . '  app description: ' . $rs['app_desc'] . '<br /><br />';
	
if (isset($_REQUEST['btnEntry'])){
	// $id = $_REQUEST['id'];
	$user = $_REQUEST['user'];
	$password = $_REQUEST['password'];
	// $sql = "SELECT id, user, password FROM `admin` WHERE id=$id";
	$sql = "SELECT id, user, password FROM `admin` WHERE user = '$user' AND password = '$password'";
	$query = mysql_query($sql);
	if (mysql_num_rows($query)>0){
		echo 'Hello, Administrator:  ' . $user ;
	} else {
		echo 'Username or Password invalid, once again plz.';
	}
}
?>
	</div>
</div>
</body>
</html>