<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
文件路径：<input type="file" name="txt_file">
<input type="submit" value="解密" name="btn_decode">
</form>

<?php
/***********************************  
*威盾PHP加密专家解密算法 By：Neeao  
*http://Neeao.com  
*2009-09-10  
***********************************/  
if (isset($_FILES['txt_file']['name'])){
$filename = $_FILES['txt_file']['name'];
$lines = file($filename);//0,1,2行  

//第一次base64解密  
$content="";  
if(preg_match("/O0O0000O0('.*')/",$lines[1],$y))  
{  
    $content=str_replace("O0O0000O0('","",$y[0]);  
    $content=str_replace("')","",$content);  
    $content=base64_decode($content);  
}  
//第一次base64解密后的内容中查找密钥  
$decode_key="";  
if(preg_match("/),'.*',/",$content,$k))  
{  
    $decode_key=str_replace("),'","",$k[0]);  
    $decode_key=str_replace("',","",$decode_key);  
}  
//截取文件加密后的密文  
$Secret=substr($lines[2],380);  
//echo $Secret;  

//直接还原密文输出  
echo "<?php ".base64_decode(strtr($Secret,$decode_key,'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'))."?>";  
}
?>