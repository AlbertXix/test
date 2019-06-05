<!DOCTYPE HTML>
<html>
<head><title>SQL Toolkit1.0</title>
<meta name="charset" content="utf-8">
<style type="text/css">
html{margin:0;padding:0;background:#eee}
body{padding:100px;font-family:"microsoft yahei"}
#sql_form{width:800px;height:100px;border:1px solid #aee;background:#add;margin:0 auto;padding:30px}
.note_text{border:1px dashed #eff;background:#eee;width:90%;height:25px;margin-top:20px;padding:10px;font:11px;color:skyblue}
.return_info{background:#ffe;border:1px solid red;width:800px;margin:0 auto;padding:10px}
.clear{clear:both;width:0;height:0;line-height:0;}
</style>
</head>
<body>
<div id="sql_form">
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
数据库：<input type="text" name="db_name" />
表文件：<input type="file" name="sql_file" /><input type="submit" name="btn_post" value="Execute Drop Tables!" />
</form>
<div class="note_text">其中，表文件为一个含有将要删除目标数据表的列表文件，表名每行一个。</div>
</div>

<?php
if(isset($_POST['btn_post'])){
	//$strfile = $_POST['sql_file'];
	$db_name = $_POST['db_name'];
	$link = mysql_connect('localhost','harryxlb','ligexi007',$db_name) or exit('database connect failed!');
	mysql_query('set names utf8');
	mysql_select_db($db_name);
	echo '<div class="return_info">database connected succss!</div>';	
	$strfile = $_FILES['sql_file']['name'];
	$fp = fopen($strfile,'r');
	$fp2 = fopen('magento_new.txt','w');
	while(!feof($fp)){
		$sqlcontent = trim(fgets($fp));
		//$sqlcontent = trim(fgets($fp),'\x00..\x1F');
		//mysql_query($sqlcontent) or die('Query error! ' . myreturn_infoor());
		//echo 'Now is execute sql: ' . $sqlcontent . '<br />';
		$result = mysql_query('drop table ' . $sqlcontent) or exit('<div class="return_info">SQL error occured!<br />drop table '.$sqlcontent.'<br />'.mysql_error().'</div><br class="clear" />');
		echo('<div class="return_info">drop table '.$sqlcontent.'......success!</div><br class="clear" />');
		$sqlnew .= str_replace($sqlcontent,$sqlcontent.";\n",$sqlcontent);
		}
	fwrite($fp2,$sqlnew);
	fclose($fp);
}
?>
</body>
</html>