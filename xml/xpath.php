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

$result = $xml->xpath("from");

echo $result[0] . "<br />";

print_r($result);
?>