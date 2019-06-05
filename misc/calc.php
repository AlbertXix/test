<?php 
/*
 * @author: HarryXlb <harryxlb@gmail.com>
 * http://www.harenwang.com
 * @file: calc.php
 * create date: 2015-08-06 23:28:31
 */

$a = 1; 
$b = 3;

function calc(){
    static $a, $b; // $a = 0, $b = 0; by default
    // static $a = 1, $b = 2; // give the initial value to them
    // global $a, $b; // import the outter global variable $a, $b
    $a++;
    $b++;
    echo 'a inner: ' . $a . PHP_EOL;
    echo 'b inner: ' . $b . PHP_EOL;
}

calc(); calc();
print str_repeat('=', 100) . PHP_EOL;
echo "a: $a, b: $b";
echo PHP_EOL;
