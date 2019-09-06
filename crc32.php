#!/usr/bin/env php
<?php
ob_start();
$file = file_get_contents($argv[1]);
ob_end_clean();
$crc = sprintf('%u', crc32($file));
$crc_hex = sprintf('%08X', crc32($file));
echo $file . ' dec hash: ' . $crc . PHP_EOL;
echo $file . ' hex hash: ' . $crc_hex . PHP_EOL;
echo $file . ' hex2 hash: ' . dechex($crc) . PHP_EOL;
