#!/usr/bin/env php
<?php
$log_file = __DIR__ . '/' . pathinfo(__FILE__, PATHINFO_FILENAME) . '.log';
echo 'log file: ' . $log_file . PHP_EOL;
$content = date('Y-m-d H:i:s') . ' somthing to log, ' . mt_rand(1000, 9999) . "\n";
file_put_contents($log_file, $content, FILE_APPEND);
