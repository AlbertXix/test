<?php
//$reg_attach = '<img.*?zoomfile=\"(.*?)\" \/>/i';
$reg_attach = '/<img\s[^>]*?zoomfile=[\'"](.+?)[\'"]/is';
//$reg_attach = '<img.*? \/>';
$img_str = '<img id="aimg_1828" src="static/image/common/none.gif" zoomfile="data/attachment/forum/201204/17/080053izxecrncz048qaua.jpeg" file="data/attachment/forum/201204/17/080053izxecrncz048qaua.jpeg" class="zoom" onclick="zoom(this, this.src)" width="540" id="aimg_1828" inpost="1" alt="2.jpeg" title="2.jpeg" onmouseover="show" />';
preg_match($reg_attach, $img_str, $img);
print_r($img);
?>