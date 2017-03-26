<?php
HtmlHead("选择解压文件:") ;

if ( !IsSet($HTTP_POST_VARS['submit']) )
{
	TestWriteable() ;
	$gzip_info = "" ;
	echo "check zlib support... " ;
	if ( !function_exists("gzopen") )
	{
		$gzip_info = "<font color=\"red\">注意! 您的空间没有zlib支持，因此用
		<a href=\"http://www.isphp.net/\" target=\"_blank\"><font color=\"blue\">phpZip</font></a> 
		压缩文件时，不要选择“压缩成Gzip格式”，否则将无法正确解压!</font>" ;
	}
	else
	{
		$gzip_info = "<font color=\"blue\">恭喜! 您的空间支持zlib压缩，强烈建议用 
		<a href=\"http://www.isphp.net/\"><font color=\"red\" target=\"_blank\">phpZip</font></a> 
		压缩文件时，选中“压缩成Gzip格式”，将会大大减少文件大小!</font>" ;
	}
	echo " ----------------- OK!<br>" . $gzip_info ;
	
	echo "<br><br><br><br>
<form action=\"{$HTTP_SERVER_VARS["PHP_SELF"]}\" method=\"post\" enctype=\"multipart/form-data\">
<table align=\"center\" width=\"450\">
<tr><td height=\"20\" colspan=\"2\">请先选择压缩文件的位置，然后点击“确定”按钮： <p></td></tr>
<tr>
<td><input type=\"radio\" name=\"file_type\" value=\"upload\" checked onclick=\"this.form.upload_file.disabled=false; this.form.server_filename.disabled=true\">文件从本地上传： </td> 
<td>
<input name=\"upload_file\" type=\"file\" style=\"color:#0000ff\">
</td>
</tr>

<tr><td colspan=2 height=10></td></tr>

<tr>
<td><input type=\"radio\" name=\"file_type\" value=\"server\" onclick=\"this.form.upload_file.disabled=true; this.form.server_filename.disabled=false\">指定服务器上文件：</td>
<td><input name=\"server_filename\" value=\"data.dat.gz\" style=\"color:#0000ff\" disabled >(可以用\".\"表示当前目录)</td>
</tr>

<tr><td colspan=\"2\" ><br><input type=\"checkbox\" name=\"chmod777\" value=\"chmod777\">将解压出的所有文件及文件夹属性改成777(慎用)</td></tr>
<tr><td colspan=\"2\" align=\"center\"><br><input type=\"submit\" name=\"submit\" value=\"确定\"></td></tr>
</table>
</form>
" ;
	HtmlFoot() ;
	exit ;
}


if ( $HTTP_POST_VARS['file_type'] == 'upload' )
{
	$tmpfile = $upload_file ;
	if ( !tmpfile )
	{
		$tmpfile = $HTTP_POST_FILES['upload_file']['tmp_name'] ;
	}
}
else
{
	$tmpfile = $HTTP_POST_VARS['server_filename'] ;
}

if ( !$tmpfile )
{
	exit("无效的文件或文件不存在,可能原因有文件大小太大，上传失败或没有指定服务器端文件等") ;	
}

$bgzExist = FALSE ;
if ( function_exists("gzopen") )
{
	$bgzExist = TRUE ;
}

$alldata = "" ;
$pos = 0 ;

$gzp = $bgzExist ? @gzopen($tmpfile, "rb") : @fopen($tmpfile, "rb") ;
$szReaded = "has" ;
while ( $szReaded )
{
	$szReaded = $bgzExist ? @gzread($gzp, 2*1024*1024) : @fread($gzp, 2*1024*1024) ;
	$alldata .= $szReaded ;
}
$bgzExist ? @gzclose($gzp) : @fclose($gzp) ;

$nFileCount = substr($alldata, $pos, 16) ;
$pos += 16 ;

$size = substr($alldata, $pos, 16) ;
$pos += 16 ;

$info = substr($alldata, $pos, $size-1) ;		// strip the last '\n'
$pos += $size ;

$info_array = explode("\n", $info) ;

$c_file = 0 ;
$c_dir = 0 ;

foreach ($info_array as $str_row)
{
	list($filename, $attr) = explode("|", $str_row);
	if ( substr($attr,0,6) == "[/dir]" )
	{
		echo "End of dir $filename<br>";
		continue;
	}
	
	if ( substr($attr,0,5)=="[dir]" )
	{
		if ( @mkdir($filename, 0777) )
		{
			echo "Make dir $filename<br>";
			if ( $_POST['chmod777'] == "chmod777" ) 
			{
				@chmod($filename, 0777);
			}
		}
		$c_dir++ ;
	}
	else
	{
		$fp = @fopen($filename, "wb") or exit("不能新建文件 $filename ,因为没有写权限，请修改权限");
		@fwrite($fp, substr($alldata, $pos, $attr) );
		$pos += $attr ;
		fclose($fp);
		if ( $_POST['chmod777'] == "chmod777" ) 
		{
			@chmod($filename, 0777);
		}
		echo "Create file $filename<br>";
		$c_file++ ;
	}
}

if ( $HTTP_POST_VARS['file_type'] == 'upload' )
{
	if ( @unlink($tmpfile) ) echo "删除临时文件 $tmpfile...<br>" ;
}

echo "<h1>操作完毕! 共解出文件 $c_file 个， 文件夹 $c_dir 个，谢谢使用!</h1><p>" ;
HtmlFoot() ;


function TestWriteable()
{
	$safemode = '
新建一文件，命名为 unzip2.php (或其它名字)， 其内容如下：

<?php
copy("unzip.php", "unzip_safe.php") ;
header("location:unzip_safe.php") ;
?>

将这个文件上传到服务器，与unzip.php同一个目录下，
运行 unzip2.php 这个程序。

如果还是不行的话，那就是空间实在不支持，没有办法，很对不住您，浪费您的时间.
	' ;
	echo "check PHP version... " . phpversion() . " -------- OK!<br>" ;
	echo "testing Permission... " ;

	$fp = @fopen("phpzip.test", "wb") ;
	if ( FALSE !== $fp )
	{
		fclose($fp) ;
		@unlink("phpzip.test") ;
	}
	else
	{
		exit("当前目录没有写的权限，请将当前目录属性修改为:777") ;
	}

	$dir = "phpziptest" ;
	$file = "$dir/test.txt" ;
	@mkdir($dir, 0777) ;
	$fp = @fopen($file, "wb") ;
	if ( FALSE === $fp )
	{
		@rmdir($dir) ;
		exit ("没有权限在程序创建的文件夹下创建文件 ，很可能是PHP安全模式所致，解决方法如下：<p><center><textarea cols=110 rows=15>$safemode</textarea></center>") ;
	}
	@fclose($fp) ;
	@unlink($file) ;
	@rmdir($dir) ;
	echo " ----------------- OK!<br>" ;
}

function HtmlHead($title="", $css_file="")
{
	echo "<html>\n"
		. "\n"
		. "<head>\n"
		. "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=gb2312\">\n"
		. "<title>$title</title>\n"
		. "<style type=\"text/css\">\n"
		. "body,pre,td {font-size:12px; background-color:#fcfcfc; font-family:Tahoma,verdana,Arial}\n"
		. "input,textarea{font-size:12px; background-color:#f0f0f0; font-family:Tahoma,verdana,Arial}\n"
		. "</style>\n"
		. "</head>\n"
		. "\n"
		. "<body>\n" ;
}

function HtmlFoot()
{
	echo "<center><font size=\"5\" face=\"楷体_GB2312\" color=\"red\">使用完请立即删除本文件，以避免被其它人发现使用!</font></center>\n"
		. "<br><hr color=\"#003388\">\n"
		. "<center>\n"
		. "<p style=\"font-family:verdana; font-size:12px\">Contact us: \n"
		. "<a href=\"http://www.isphp.net/\" target=\"_blank\">http://www.isphp.net/</a></p>\n"
		. "</center>\n"
		. "</body>\n"
		. "\n"
		. "</html>" ;
}

?>