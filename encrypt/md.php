<form action="<?php echo$_SERVER['php_self']?>" method="post">
<textarea name="txtCode" id="txtCode" rows="20" cols="100"></textarea>
<input type="submit" value="MD5 Encode it!">
</form>
<?php
function MD_she($str){
	return md5($str);
}

if(isset($_POST['txtCode'])){
	echo "<div style='background:dfd;width:auto;height:100px;padding:30px;line-height:50px;font-size:20px;'>";
	echo "The result is:<br /><strong>".MD_she($_POST['txtCode'])."</strong>";
	echo "</div>";
}
?>