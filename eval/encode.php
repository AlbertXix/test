<!doctype html>
<html>
<head><title>base64 encode</title>
<style>
textarea { width: 80%; height: 300px; }
</style>
<body>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
	<textarea name="txtcode"></textarea>
	<input type="submit" name="btnencode" value="encode it" />
	<input type="submit" name="btndecode" value="decode it" />
</form>
<textarea><?php 
if (isset($_POST['txtcode']) && isset($_POST['btnencode'])) {
	$xcode = $_POST['txtcode'];
	$xcode = base64_encode(gzdeflate($xcode));
	echo '<?php eval(gzinflate(base64_decode("' . $xcode . '")));?>';
	file_put_contents('enc_' . date('His') . '.php', '<?php eval(gzinflate(base64_decode("' . $xcode . '")));?>');
} else if (isset($_POST['txtcode']) && isset($_POST['btndecode'])) {
	$xcode = $_POST['txtcode'];
	$xcode = gzinflate(base64_decode($xcode));
	// echo $xcode;
	echo htmlentities($xcode, ENT_QUOTES);
} ?>
</textarea>
</body>
</html>