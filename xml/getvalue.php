<?php
$str_xml = <<<xmls
<?xml version="1.0" encoding="ISO-8859-1"?>
<note>
<to>George</to>
<from>John</from>
<heading>Reminder</heading>
<body>
<id1>
<username>harryxlb</username>
</id1>
<memo>Don't forget the meeting!</memo>
</body>
</note>
xmls;

$xml = simplexml_load_string($str_xml);
$nodename = $xml->getname();
echo 'node name: ' . $nodename . '<br />';
foreach ($xml as $k=>$v){
	echo "$k: $v <br />";
}

echo '<hr />';

$user = $xml->body[0]->id1[0]->username[0];
echo $user;
?>