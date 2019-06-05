<?php 
	session_start();
	$xhost = isset($_SESSION['host']) ? $_SESSION['host']: '';
	$xuser = isset($_SESSION['user']) ? $_SESSION['user']: '';
	$xpass = isset($_SESSION['pass']) ? $_SESSION['pass']: '';
	$xdb = isset($_SESSION['db']) ? $_SESSION['db']: '';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MySQL Command</title>
<style>
html,body { padding:0; margin: 0; }
#main { width: 80%; margin: 10px auto; padding: 10px; border: 1px solid #ddd; }
#stb {width:800px;}
#stb tr { width: 100%;  border:1px solid #ddd; }
#stb tr, #stb td  { padding: 0; margin: 0; height: auto; }
stb td { border:1px solid #ddd;}
</style>
</head>
<body>
<div id="main">
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
	Host: <input type="text" name="txtHost" id="txtHost" value="<?php echo $xhost;?>" />
	User: <input type="text" name="txtUser" id="txtUser" value="<?php echo $xuser;?>" />
	Pass: <input type="password" name="txtPass" id="txtPass" value="<?php echo $xpass;?>" />
	Database: <input type="text" name="txtDB" id="txtDB" value="<?php echo $xdb;?>" /><br />
	SQL: <input type="text" name="txtSQL" id="txtSQL" size="80" />
    <input type="submit" name="subExec" value="Execute" />
</form>
	<?php 
    	if (isset($_POST['subExec'])) {
			$host = isset($_POST['txtHost']) ? $_POST['txtHost']: '';
			$user = isset($_POST['txtUser']) ? $_POST['txtUser']: '';
			$pass = isset($_POST['txtPass']) ? $_POST['txtPass']: '';
			$db = isset($_POST['txtDB']) ? $_POST['txtDB']: '';
			$link = mysql_connect($host, $user, $pass) or exit('DB connect failed.');
			if ( ! empty($db) ) mysql_select_db($db);
			mysql_query('SET NAMES utf8');
			$_SESSION['host'] = $_POST['txtHost'];
			$_SESSION['user'] = $_POST['txtUser'];
			$_SESSION['pass'] = $_POST['txtPass'];
			$_SESSION['db'] = $_POST['txtDB'];
            $sql = isset($_POST['txtSQL']) ? trim($_POST['txtSQL']) : '';
            $query = mysql_query($sql);
			echo '<table id="stb">';
            while ($rs = mysql_fetch_array($query, MYSQL_ASSOC)){
				echo '<tr>';
                foreach ($rs as $v) {
                    echo '<td>' . $v . '</td>';
                }
                echo '</tr>';
            }
			echo '</table>';
			mysql_close();
          }
    ?>
</div>
</body>
</html>
