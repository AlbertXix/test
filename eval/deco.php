<?php 
$codefile = "function.php.txt";
$fp1 = fopen($codefile, "r");
$contents = fread($fp1, filesize($codefile));
fclose($fp1);
// while (preg_match("/eval\s\(gzinflate/s",$contents)) {
while (preg_match("/gzinflate/s",$contents)) {
	$contents=preg_replace("/<\?php|\?>/", "", $contents); 
	eval(preg_replace("/eval/", "\$contents=", $contents)); 
} 
$fp2 = fopen("decoded.txt","w"); 
fwrite($fp2, trim($contents)); 
fclose($fp2); 