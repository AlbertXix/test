<?php
$dom = new domDocument;
$dom->loadXML('<note><from>John</from></note>');

$xml = simplexml_import_dom($dom);

echo $xml->from;
?>