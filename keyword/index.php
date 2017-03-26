<!doctype html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>关键词分配工具</title>
<style type="text/css">
#wrapper { width: 960px; margin: 20px auto; border: 1px solid #ddd; padding: 20px }
#wrapper .left { float: left; width: 45%; }
#wrapper .right { float: right; width: 45%; }
#wrapper .txtar { width: 95%; height: 600px; border: 1px solid #ddd; }
#wrapper label { width: 40%; height: 33px; clear: both; display: block; }
.clr { content: "."; height: 0; display: block; clear: both; }
</style>
</head>
<body>
	<div id="wrapper">
		<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
			<div class="left">
				<label>域名列表:</label>
				<textarea name="txthost" class="txtar"></textarea>
			</div>
		<div class="right">
			<label>关键词列表:</label>
			<textarea name="txtkeyword" class="txtar"></textarea>
		</div>
			<input type="submit" name="btnmake" value="开始生成" />
		</form>
<?php
if (isset($_POST['btnmake'])){
	$host = explode("\n", trim($_POST['txthost']));
	$kwd = explode("\n", trim($_POST['txtkeyword']));
	$groupnum = ceil(count($kwd)/count($host));
	$arrkey = geneFile($kwd, count($host), $groupnum);
	for($i=0; $i<count($arrkey); $i++){
		for ($k = 0; $k < count($arrkey[$i]); $k++){
			$curdomain = trim($host[$i]) . '.txt';
			$fp = fopen($curdomain, 'a+');
			fwrite($fp, $arrkey[$i][$k] . PHP_EOL);
		}
	}
	fclose($fp);
	echo '<br /><b style="color: #0a0">生成完成。</b>';
}
function geneFile($arrkwd, $row, $len){
	for ($t = 0; $t < $row; $t++){
		for ($x = 0; $x < count($arrkwd); $x + $len){
			$group[] = array_splice($arrkwd, $x, $x + $len);
		}
	}
	return $group;
}
?>
<br class="clr" />
	</div>
</body>
</html>