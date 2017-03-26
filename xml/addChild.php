<?php
$xml = simplexml_load_file("test.xml");

$xml->body[0]->addChild("date", "2008-08-08");

foreach ($xml->body->children() as $child)
  {
  echo "Child node: " . $child . '<br />';
  }
 
var_dump($xml);
?>