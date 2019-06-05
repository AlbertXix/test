<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post">
<textarea name="txtcode" cols="100" rows="20"></textarea>
	<input type="submit" value="decrypt it" name="btndec" />
</form>
<?php 
// echo "nDECODE nested eval(gzinflate()) by DEBO Jurgen <mailto:jurgen@person.benn";
// echo "1. Reading coded.txtn";
// $fp1 = fopen ("coded.txt", "r");
// $contents = fread ($fp1, filesize ("coded.txt"));
// fclose($fp1);
$contents = isset($_POST['txtcode']) ? $_POST['txtcode'] : '';
if ( ! empty($contents) ) {
	while (preg_match("/eval\(gzinflate/",$contents)) {
		$contents=preg_replace("/(<\?\sphp)|\?>/is", "", $contents); 
		eval(preg_replace("/eval/", "\$contents=", $contents)); 
	} 
	echo "Decryped it.";
	$fp2 = fopen("xcode.txt","w");
	fwrite($fp2, trim($contents)); fclose($fp2);
}
?>