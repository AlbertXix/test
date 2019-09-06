#!/usr/bin/env php
<?php
$hao_kw = $bd_kw = 0;
if ($argc < 2 || intval($argv[1]) <= 0) show_usage();
if ($argc >= 3) $hao_kw = intval($argv[2]);
if ($argc >= 4) $bd_kw = intval($argv[3]);

$ip_len = intval($argv[1]);
$key_pre = 'search.t725.';
$key_ip =  $key_pre . 'ip_access';
$key_hao = $key_pre . 'hao123';
$key_bd = $key_pre . 'baidu';
$ip_pre = '127.0.0.';

$redis = new \Redis();
$h_redis = $redis->connect('127.0.0.1');
if (! $h_redis) exit('connect to redis fail.');

echo 'begin to generate ip hash set...';
for ($i = 0; $i < $ip_len; $i++){
    $redis->hset($key_ip, $ip_pre . $i . '.' . uniqid(microtime(true).rand(1,99999)), 1);
}

echo 'done.' . PHP_EOL;

if ($hao_kw || $bd_kw){
    echo 'begin to set keywods count...';
    if ($hao_kw) $redis->hset($key_hao, 'kw_count', $hao_kw);
    if ($bd_kw) $redis->hset($key_bd, 'kw_count', $bd_kw);
    echo 'done.' . PHP_EOL;
}

function show_usage(){
    global $argv;
    echo 'usage: ' . $argv[0] . ' <ip_count> <hao_kw_count> <bd_kw_count>' . PHP_EOL;
    echo 'e.g. ' . $argv[0] . ' 10' . PHP_EOL;
    exit;
}
