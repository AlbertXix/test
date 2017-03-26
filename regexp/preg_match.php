<?php
$jsop_pattern = '/<ignore_js_op>(.*?)<\/ignore_js_op>/';

$str = '<a href="forum.php?mod=forumdisplay&fid=36"><img src="data/attachment/common/19/common_36_icon.jpg" align="left" alt="" /></a>';
//$pattern = '/<a href=\"(.*)\"><img src=\"(.*)\" align=\"left\" alt=\"\" /><\/a>/isU';
$pattern = "/<img src=\"(.*)\" align=\"left\" alt=\"\" \/>/isU";
 preg_match($pattern,$str,$match);
print_r($match);

echo '<hr />';

$a = '<IMG border=0 src="/news/uploadfile/201103/20110307091700882.jpg">';
preg_match("/<IMG border=0 src=\"(.*)\">/isU",$a,$match2);

print_r($match2);

?>