#!/usr/bin/env php
<?php

$fixed_money = $total = 2500; //红包总额
$num = 24; // 分成N个红包，支持N人随机领取
$min= 50; //每个人最少能收到min元
$counter = 0;

for ($i = 1; $i <= $num; $i++) {
    $left_num = $num - $i;
    if ($left_num == 0){
        $last_money = $fixed_money - $counter;
        $counter += $last_money;
        echo '最后一个红包，金额' . $last_money . '元' . PHP_EOL;
        break;
    } else {
        $safe_total = ($total - $left_num * $min) / $left_num; //随机安全上限
        $money = mt_rand($min * 100, $safe_total * 100) / 100;
        $total = $total - $money;
        $counter += $money;
        echo '第'. $i. '个红包：' . $money . ' 元，余额：' . $total . ' 元' . PHP_EOL;
    }
}

echo '总计发了' . $counter . '元的红包' . PHP_EOL;
