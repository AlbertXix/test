<?php
$password = "isphp" ;	// 管理密码，运行时会要求输入，一定要修改掉。不然程序无法运行。

//////  下面是主程序，不必修改  ////////////////////////////////////////////////////////
session_start();

if ( $password == "isphp" )
{
	HtmlHead("需要重设管理员密码:") ;
	echo "<h3 align=center>您没有修改管理密码，为避免不安全，请修改成其它的!</h3>";
	echo "<center>修改方法如下：<br>"
		. '用记事本打开 本文件(zip.php),, 将第二行的 <font color=red>$password = "isphp" </font> 中的 isphp 改成您想要的密码, 再上传至服务器</center>';
	hg_exit();
}

if ( !IsSet($_SESSION['administrator']) )
{
	if ( !IsSet($_POST['user_pass']) )
	{
		HtmlHead("输入管理员密码：");
		echo '<h3 align="center">为安全起见，以下操作需要密码认证：</h3>';
		hg_exit('<br><form action=' . $_SERVER['PHP_SELF']
			. ' method="post">请输入管理员密码：<input name="user_pass"> <input type="submit" value＝"确定"> </form>');
	}
	else
	{
		if ( $password != $_POST['user_pass'] )
		{
			HtmlHead("错误的管理员密码!");
			MessageBox("错误的管理员密码, 无法继续操作！ 如果您忘了密码，可以在本文件的第二行查到密码!", true);
			hg_exit("", true);
		}
		$_SESSION['administrator'] = "seted";
		header("Location: {$_SERVER['PHP_SELF']}");
	}
	hg_exit();
}


if ( !IsSet($_GET['dirname']) )
{
	show_input_form() ;
}
else
{
	// check if empty
	if ( empty($_GET['dirname']) )
	{
		hg_exit("请输入文件夹名!") ;
	}

	// check valid dirname
	if ( FALSE !== strpos($_GET['dirname'], "/") )
	{
		hg_exit("\"/\" 是非法的文件夹名!") ;
	}
	if ( FALSE !== strstr($_GET['dirname'], "..") )
	{
		hg_exit("\"..\" 是非法的文件夹名!") ;
	}

	// check valid dir
	if ( !is_dir($_GET['dirname']) )
	{
		hg_exit("\"{$_GET['dirname']}\" 不是一个有效的文件夹!") ;
	}

	$szData = "" ;
	$szInfo = "" ;

	$file_count = @ZipDir($_GET['dirname'], &$szData, &$szInfo) ;
	$info_size_16byte = @sprintf("%016d", @strlen($szInfo)) ;
	$szData = @sprintf("%016d",$file_count) . $info_size_16byte . $szInfo . $szData ;
	$filename = $_GET['dirname'] . ".dat" ;
	if ( function_exists("gzencode") )
	{
		$szData = gzencode($szData) ;
		$filename .= ".gz" ;
	}
	
	Header("Content-type: application/octet-stream");
	Header("Accept-Ranges: bytes");
	Header("Accept-Length: " . strlen($szData));
	Header("Content-Disposition: attachment; filename=$filename");

	echo $szData ;
}


function show_input_form()
{
	echo HtmlHead("文件打包") ;
	echo "<center><h4><font color=red>密码验证成功！</font></h4></center>\n";
	echo "<form name=\"input\"><br><br>\n"
		. "<center><h3>请输入要打包下载的文件夹,注意: 仅当前目录下的文件夹才可以下载!</h3></center><p>\n"
		. "<center><input name=\"dirname\">\n"
		. "<input type=\"button\" value=\"确定\" onClick=\"show_download_link(dirname.value);\"></center>\n"
		. "</form>\n" ;
	echo "<script>\n"
		. "input.dirname.focus();\n"
		. "function show_download_link(dir)\n"
		. "{"
		. "   var top = (screen.height-200)/2 ;\n"
		. "   var left = (screen.width-300)/2 ;\n"
		. "   newwin=window.open('', '', 'width=300,height=200,top=' + top + ',left=' + left + ', resizable=0,scrollbars=auto');\n"
		. "   url = \"{$_SERVER['PHP_SELF']}\" + \"?dirname=\" + dir ;\n"
		. "	  newwin.document.write('<a href=' + url + '>点击此链接下载，<br>或者右键点击此处选择\"另存为\"</a>');\n"
		. "}"
		. "</script>\n" ;
	echo HtmlFoot() ;
}


function ZipDir($szDirName, &$szData, &$szInfo)
{
	// write dir header
	$szInfo .= "$szDirName|[dir]\n" ;
	$file_count = 0 ;
	$hDir = OpenDir($szDirName) ;
	while ( $file = ReadDir($hDir) )
	{
		if ( $file=="." || $file==".." )	continue ;

		$szCurFile = "$szDirName/$file" ;

		if ( Is_Dir($szCurFile) )
		{
			$file_count += ZipDir($szCurFile, &$szData, &$szInfo) ;
		}
		else if ( Is_File($szCurFile) )
		{
			$hCurFile = fopen($szCurFile, "rb") ;
			$size = filesize($szCurFile) ;
			$szStream = fread( $hCurFile, $size ) ;
			fclose($hCurFile) ;
			$file_count++ ;

			// write info
			$szInfo .= "$szCurFile|$size\n" ;

			// write data
			$szData .= $szStream ;
		}
	}

	// write dir footer
	$szInfo .= "$szDirName|[/dir]\n" ;
	return $file_count ;
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
	echo "<br><hr color=\"#003388\">\n"
		. "<center>\n"
		. "<p style=\"font-family:verdana; font-size:12px\">Contact us: \n"
		. "<a href=\"http://www.isphp.net/\" target=\"_blank\">http://www.isphp.net/</a></p>\n"
		. "</center>\n"
		. "</body>\n"
		. "\n"
		. "</html>" ;
}

function MessageBox($str)
{
	echo "<script>alert('$str');</script>\n";
}

function hg_exit($str="", $goback=false)
{
	if ( !empty($str) )
	{
		echo ("<center>$str</center>");
	}
	if ( $goback )
	{
		echo ('<big><center><a href="JavaScript:history.go(-1);">点此返回前一页</a></center></big>');
	}
	HtmlFoot();
	exit;
}

?>