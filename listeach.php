<?php 
/*
 * @author: HarryXlb <harryxlb@gmail.com>
 * http://www.harenwang.com
 * @file: listeach.php
 * create date: 2015-04-26 00:00:28
 */

error_reporting(E_ALL ^ E_NOTICE);

$employee = new ArrayObject ( array ( array('name' => 'xlb', 'department' => 'odc-java', 'employee_id' => '1912'),
                  array('name' => 'chengting', 'department' => 'odc-java', 'employee_id' => '1911') ) );
foreach ( $employee as $k => $v ) {
    while ( list($key, $val) = each($employee[$k]) ) 
        echo $key .  ' : ' . $val . ' ';
    echo PHP_EOL;
}
