<?php
    $list_arr = array(
        'Myname' => 'xlb',
        'Myinfo' => 'Harryxlb is me',
        'From' => 'WuHan, Hubei',
        'HomePage' => 'http://blog.csdn.net/harryxlb',
    );
    while (list($k, $v) = each($list_arr)) {
        echo $k . ': ' . $v . '<br />';
    }
?>

