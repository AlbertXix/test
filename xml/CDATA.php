<?php
  $xmlDoc=new DOMDocument(); //建立一个XMLDOM对象
  $xmlDoc->load('CDATA.xml'); //加载xml文档
  $nodes = $xmlDoc->getElementsByTagName("node");

  $button = iconv('utf-8','gb2312',$nodes->item(0)->nodeValue);
  $title = iconv('utf-8','gb2312',$nodes->item(1)->nodeValue);
  $pic = iconv('utf-8','gb2312',$nodes->item(2)->nodeValue);
  $download = iconv('utf-8','gb2312',$nodes->item(3)->nodeValue);
	echo 'node name: ' . $nodes->item(1)->nodeName . '<br />';
	echo '$title:<br/><textarea style="width:400px;height:200px;overflow:auto;">' . $title . '</textarea><br/>';
	echo '$pic:<br/><textarea style="width:400px;height:200px;overflow:auto;">' . $pic . '</textarea>';

	echo '<pre>';
	echo($nodes->item(2)->firstChild->getAttribute('url'));
	echo '</pre>';
echo '<pre>' . $title . '</pre>';
  $xmlDoc=null //释放对象
?>