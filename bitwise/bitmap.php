<?php
error_reporting(E_ALL & ~E_NOTICE);

define('DATA_FILE', './nums.txt');
define('END_NUM', 1024 * 1024);
define('DIV_NUM', PHP_INT_SIZE);

$data_file = DATA_FILE;

if ($argc <= 1) {
    show_usage();
} else if ($argc == 3) {
    $data_file = $argv[2];
    echo 'data file: ' . $data_file . PHP_EOL;
}

if ($argv[1] == 'gen') {
    echo date('Y-m-d H:i:s'), ' ' . 'start generate data...' . PHP_EOL;
    $start_time = time();
    foreach (make_data(END_NUM) as $value)
        file_put_contents($data_file, $value . PHP_EOL, FILE_APPEND | LOCK_EX);
    $used_time = time() - $start_time;
    echo date('Y-m-d H:i:s'), ' ' . 'end generate data.' . PHP_EOL;
    echo 'used ' . $used_time . ' seconds.' . PHP_EOL;
} else if ($argv[1] == 'read') {
    echo date('Y-m-d H:i:s'), ' ' . 'start read data...' . PHP_EOL;
    $start_time = time();
    $slots = read_data($data_file);
    // print_r($slots);
    print_r(restore_data($slots));
    echo PHP_EOL;
    $used_time = time() - $start_time;
    echo date('Y-m-d H:i:s'), ' ' . 'end read data.' . PHP_EOL;
    echo 'used ' . $used_time . ' seconds.' . PHP_EOL;
} else show_usage();


function show_usage() {
    echo '$argc: ' . $argc . PHP_EOL;
    print_r($argv);
    echo 'php ' . $argv[0] . ' <gen> | <read>' . PHP_EOL;
    exit;
}

function make_data($end_num) {
    foreach (range(1, $end_num) as $value) {
        yield $value;
    }
}

function read_data($data_file) {
    if (!file_exists($data_file)) {
        throw new InvalidArgumentException('file not found. ' . $data_file);
    }
    $slots = [];
    $fp = fopen($data_file, 'r');
    // while (!feof($fp)) {
    while (($num = fgets($fp, 8)) !== false) {
        $num = intval(trim($num));
        // echo $num . ' ';
        // $num = intval(fgets($fp));
        $key = intval($num / DIV_NUM);
        if (!array_key_exists($key, $slots)) {
            $slots[$key] = 1 << ($num % DIV_NUM);
        } else {
            $slots[$key] = $slots[$key] | (1 << ($num % DIV_NUM));
        }
    }
    echo PHP_EOL . 'memery usage: ' . (memory_get_usage() / pow(1024, 2)) . PHP_EOL;
    echo 'memery peak usage: ' . (memory_get_peak_usage() / pow(1024, 2)) . PHP_EOL;
    return $slots;
}

function restore_data(array $data) {
    if (empty($data)) {
        return $data;
    }

    $out_data = [];
    foreach ($data as $key => $value) {
        for ($i = 0; $i < DIV_NUM; $i++) {
             if (1 << $i & $value) {
                $out_data[] = $key * DIV_NUM + $i;
             }
        }
    }
    unset($data);
    return $out_data;
}
