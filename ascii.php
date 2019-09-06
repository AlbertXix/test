#!/usr/bin/env php
<?php
echo PHP_EOL;
array_map(function($v){
    echo chr($v) . ' => ' . $v . "\n";
}, range(0, 127));
