<!doctype html>
<html>
<head><title></title></head>
<body>
<div id="wrapper">
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
<input type="text" name="txtcode">
<input type="submit" name="btnok" value="Translate it">
</form>
<?php 
	if (isset($_POST['btnok']) && isset($_POST['txtcode'])){
		echo hex($_POST['txtcode']);
	}
	
	function hex($strcode){
		$temp_result = '';
		for ($i=0; $i<mb_strlen($strcode); $i++){
			$temp_result .= dechex(ord($strcode[$i]));
		}
		return $temp_result;
	}
?>
</div>
</body
</html>