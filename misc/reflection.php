<?php 
/*
 * @author: HarryXlb <harryxlb@gmail.com>
 * http://www.harenwang.com
 * @file: reflection.php
 * create date: 2015-08-04 18:50:00
 */

class MyReflector
{
    static public function showMessageStatic(){
        echo 'Hello, static girl.' . PHP_EOL;
    }

    public function showMessage(){
        echo 'Hello, girl.' . PHP_EOL;
    }
}

$r = new ReflectionClass('MyReflector');
echo $r;
echo str_repeat('=', 100), PHP_EOL;
$n = $r->newInstance();
print_r($r);
$n->showMessageStatic();
$n->showMessage();
