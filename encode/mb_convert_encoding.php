<style type="text/css">
#apDiv1 {
	margin: 10px auto;
	padding: 30px;
	border: 1px solid #ddd;
	font:"Times New Roman", Times, serif;
	width:980px;
	min-height:600px;
	z-index:1;
	background-color: #B9EBF9;
}
</style>
<div id="apDiv1">
  <form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
    <label for="txtFolder"></label>
    请输入待转换编码的文件夹路径：
    <input type="text" name="txtFolder" id="txtFolder">
    <input type="submit" name="btnUTF8" id="btnUTF8" value="开始转换为UTF-8">
  </form>
  
<?php
function gbk2utf8($the_file, $savedir){
		$content = file_get_contents($the_file) or exit('cannot to open for read.');
		if(!mb_check_encoding($content, 'UTF-8')){
					//$content8 = mb_convert_encoding($content, 'UTF-8', 'GBK');
//					$content8 = "\xEF\xBB\xBF" . $content8; 
//					below utf8_encode function cannot encode crectly..I dont know why.

					$content8 = utf8_encode($content);
					$content8 = "\xEF\xBB\xBF" . $content8; 
					
					/**/
					//$content8 = iconv('GBK', 'UTF-8', $content);
					$file_w = fopen($savedir . '\\'. basename($the_file), 'w');
					fwrite($file_w, $content8) or exit('convert and write to file <font color="#660000">' . iconv('GBK', 'UTF-8', basename($the_file)) . '</font> ...error!<br />');
					echo('convert and write to file <font color="#006600">' . basename($the_file) . '</font> ...OK!<br />');
					fclose($file_w);
			//		echo $content8;
			}
		}

if ($_POST['txtFolder']!=''){
	$the_folder = trim($_POST['txtFolder']);
//	exit('folder: ' . $the_folder . '<br /> dirname: ' . dirname($the_folder) . '<br /> dirname(dirname): ' . dirname(dirname($the_folder)));
	$fhandler = opendir($the_folder);
	$savedir = $the_folder . '\\convert';
	mkdir($savedir);
	while (FALSE !== ($myfile = readdir($fhandler))){
		if (($myfile != "." && $myfile != "..") && eregi('.htm|.html|.dwt|.lbi|.php', $myfile)) {
			gbk2utf8($the_folder . '\\' . $myfile, $savedir);
			clearstatcache();
		}
	}

		closedir($fhandler);

}
?>

</div>
