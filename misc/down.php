<?php
$file = "http://localhost/test/xlb.csv";
// header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($file).rand(0, 100));
header('Content-Length: ' . filesize($file));
// header('Content-Transfer-Encoding: binary');
// header('Expires: 0');
// header('Cache-Control: must-revalidate');
// header('Pragma: public');
ob_clean();
readfile($file);