<?php
$data = implode("", file("gzen.xlsx"));
$gzdata = gzencode($data, 9);
$fp = fopen("gzen.xlsx.gz", "w");
fwrite($fp, $gzdata);
fclose($fp);
?>