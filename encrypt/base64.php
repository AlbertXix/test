<!DOCTYPE HTML>
<html><head>
<title>base64() Encode & Decode</title>
<style type="text/css">
body{background:#333;color:#0f0;font:14px/.8;}
input{background:#666;color:#0f0;font:14px;}
textarea{background:#efe;font:14px/0.8;margin:20px;}
}
</style>
</head>
<body>
<form action="<?php echo $_SERVER['script_name']?>" method="post">
<textarea name="TxtCode" id="TxtCode" cols="100" rows="20"></textarea>
<input type="submit" name="subok" value="Encode it!" />
</form>

<form action="<?php echo $_SERVER['script_name']?>" method="post" id="form2">
<textarea name="TxtCode2" id="TxtCode2" cols="100" rows="20"></textarea>
<input type="submit" name="subok2" value="Decode it!" />
</form>

<?php

function CodeIt($str)
{
	$TxtCoded = base64_encode($str);
	return $TxtCoded;

}

function DecodeIt($str)
{
	$TxtCoded = base64_decode($str);
	return $TxtCoded;

}

	if(isset($_POST['TxtCode']))
	{
		echo CodeIt($_POST['TxtCode']);
	}

	if(isset($_POST['TxtCode2']))
	{
		echo DecodeIt($_POST['TxtCode2']);
	}
?>
</body></html>