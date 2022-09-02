<?php
    $a = 7;
    $b = 2;
    echo "$a & $b: " . ($a & $b) . PHP_EOL;
    echo "$a | $b: " . ($a | $b) . PHP_EOL;
    echo "$a >> $b: " . ($a >> $b) . PHP_EOL;
    echo 'bin 00001111 to dec: ', ' ', bindec('00001111') . PHP_EOL;
    echo 'dec 15 to bin: ', ' ',  decbin('15') . PHP_EOL;
    echo 'memory_get_usage: ', memory_get_usage(), PHP_EOL;

