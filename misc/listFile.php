<?php 
/*
 * @author: HarryXlb <harryxlb@gmail.com>
 * http://www.harenwang.com
 * @file: listFile.php
 * create date: 2015-08-01 14:09:33
 */


$d = dir(dirname(__file__)); 

while (false !== ($entry = $d->read())) { 
    echo $entry . PHP_EOL; 
}

