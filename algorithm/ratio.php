#!/usr/bin/env php
<?php

$ip_num = 2500;
$rnd_size = 24;
$rnd_nums = [];
$start_num = $ip_num / ($rnd_size * 2);
$end_num = $ip_num / ($rnd_size / 2);

for ($i = 0; $i < $rnd_size; $i++){
    $rnd_num = rand($start_num, $end_num);
    if (! in_array($rnd_num, $rnd_nums))
        $rnd_nums[] = $rnd_num;
    else 
        $i--;
}

//$rnd_nums[] = $ip_num - array_sum($rnd_nums);
echo 'sum: ' . array_sum($rnd_nums) . ', max: ' . max($rnd_nums) . ', min: ' . min($rnd_nums) . PHP_EOL;
print_r($rnd_nums);

echo 'rand ratio in 100%:' . PHP_EOL;
$sum_ratios = random_ratio();
echo 'sum of random ratios: ' . array_sum($sum_ratios) . PHP_EOL;
print_r($sum_ratios);

$target_num = 300;
$total_size = 15;
$min_num = 30;
$max_num = 150;
$rnd_num = mt_rand($min_num, $max_num);
$rnd_nums = [];
$left_num = $target_num - $rnd_num;
echo 'mt_rand in 300 with 30, 150' . PHP_EOL;
for ($x = 2; $x <= $total_size; $x++){
    $rnd_num = assign_percent_nums($left_num, $x, $total_size, $min_num, $max_num);
    if (!in_array($rnd_num, $rnd_nums) && is_numeric($rnd_num)){
        $rnd_nums[] = $rnd_num;
        $left_num = $target_num - array_sum($rnd_nums); 
    } else {
        $x--;
    }
}

print_r($rnd_nums);

function assign_percent_nums($left_num, $cur_size, $total_size, $min_num,  $max_num){
    $left_size = $total_size - $cur_size;
    //echo 'left size: ' . $left_size . ', total: ' . $total_size . ', cur_size: ' . $cur_size . PHP_EOL;exit;
    if ($left_size <= 0) return false;
    $rnd_num = mt_rand($min_num, $left_num) / $left_size;
    return $rnd_num;
}

function random_ratio($percent, $min, $max){
    if ($min >= $percent || $max >= $percent) return false;
    if ($min >= $max) return false;

    $rnd_ratios = [];
    $counter = 0;
    while ($counter < $percent) {
        $rnd_ratios[] = mt_rand($min, $max);
        $rnd_ratios = array_unique($rnd_ratios);
        $counter = array_sum($rnd_ratios);
        if ($counter > $percent) {
            array_pop($rnd_ratios);
            $rnd_ratios[] = $percent - array_sum($rnd_ratios);
            break;
        }
    }

    return $rnd_ratios;
}
