<?php
//echo(basename("D:\\www\\ecshop\\themes\\lativ\\brand.dwt")) . '<br />';
gbk2utf8("D:\\www\\dz2013\\template\\default\\category\\header.htm");
function gbk2utf8($myfile){
$content = file_get_contents($myfile);
//			if(!mb_check_encoding($content, 'UTF-8')){ 
//				$content8 = mb_convert_encoding($content, 'UTF-8', 'GBK');
				$content8 = utf8_encode($content);
				$content8 = "\xEF\xBB\xBF" . $content8; 
				//$content8 = iconv('GBK', 'UTF-8', $content);
				$file_w = fopen('convert' . basename($myfile), 'w');
				fwrite($file_w, $content8); // or exit('convert and write to file <font color="#660000">' . $myfile . '</font> ...error!<br />');
				echo('convert and write to file <font color="#006600">' . $myfile . '</font> ...OK!<br />');
}
?>