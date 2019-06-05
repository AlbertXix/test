<?php
    $a = 7;
    $b = 2;
    echo "$a & $b: " . ($a & $b) . '<br />';
     echo "$a | $b: " . ($a | $b) . '<br />';
//    echo '$a >> $b: ' . $a >> $b . '<br />';
     echo bindec('00001111') . '<br />';
     echo decbin('15') . '<br />';
    echo memory_get_usage();
?>

