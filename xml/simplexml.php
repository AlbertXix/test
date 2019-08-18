<?php
$xml_file = 'simple.xml';
$simxml = simplexml_load_file($xml_file);
//print_r($simxml);exit;
echo 'client 1: ' . $simxml->client[0]->name . PHP_EOL;
// print_r($simxml->client[0]->attributes());

foreach($simxml->client as $c){
    echo 'name: ' . $c->name . ', total: ' . $c->total . PHP_EOL;
    foreach($c->attributes() as $attr => $value){
        echo 'attr: ' . $attr . ', value: ' . $value . str_repeat(PHP_EOL, 2);
    }
}
