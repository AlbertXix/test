<?php
$xmlDoc = new DOMDocument();
$xmlDoc->load("DOM.xml");

print $xmlDoc->saveXML();
?>