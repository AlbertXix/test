<?php
$xmldoc = simplexml_load_file("CDATA.xml");
echo 'node name: ' . $xmldoc->getName() . '<br />';
echo 'sub node[1] name: ' . $xmldoc->node[1]->getName() . '<br />';
echo 'sub node[1] and node[2] value: <br /><textarea cols=100 ros=20>' . $xmldoc->node[1];
echo $xmldoc->node[2] . '</textarea><br />';
echo htmlspecialchars($xmldoc->node[1]);
echo '<hr />';
foreach ($xmldoc->children() as $child){
	echo (string)$child->getname() . ': <input type="text" value="' . $child . '"  style="width: 300px;"/><br />';
	}
?>