<?php
$password = "isphp" ;	// 管理密码，运行时会要求输入，一定要修改掉。不然程序无法运行。

//////  下面是主程序，不必修改  ////////////////////////////////////////////////////////
echo '<style>'
	. 'body{font-family:Verdana; font-size:12px; background-color:#fcfcfc}'
	. 'input{font:12px Tahoma} '
	. '</style>';

if ( $password == "isphp" )
{
	echo "<h3 align=center>您没有修改管理密码，为避免不安全，请修改成其它的!</h3>";
	echo "<center>修改方法如下：<br>"
		. '用记事本打开 本文件(rm.php), 将第二行的 <font color=red>$password = "isphp" </font> 中的 isphp 改成您想要的密码, 再上传至服务器</center>';
	exit;
}

if ( !IsSet($HTTP_POST_VARS['dirname']) ) 
{
	$self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $PHP_SELF;
	echo "<form action=\"{$self}\" method=post name=delform><center><font color=red>欲删除的文件或文件夹</font>: <input name=dirname style=\"color:red\"> "
		. "密码: <input type=password name=pass size=6> <input type=submit value=\"确定\"></center></form>"; 
	echo " <SCRIPT> document.delform.dirname.focus() </SCRIPT> ";
}
else 
{
	if ( $password != $HTTP_POST_VARS['pass'] )
	{
		exit("<script>alert('错误的管理员密码, 无法继续操作！ 如果您忘了密码，可以在本文件的第二行查到密码!');</script>");
	}
	$dir_name = $HTTP_POST_VARS['dirname'];
	if ( is_file( $dir_name ) )
	{
        	if ( unlink($dir_name) )		echo "Del file \"$dirname\" successfully!<br>"; 
                else				echo "<FONT COLOR=RED>Fail to del file \"$dirname\"!</FONT><br>"; 
	}
	else
	{
        	CleanDir($dir_name);
			if ( rmdir($dir_name) )		echo "Remove dir \"$dirname\" successfully!<br>";
            	else                 		echo "<FONT COLOR=RED>Fail to Remove dir \"$dirname\"!</FONT><br>"; 
	}
} 

function CleanDir($dir)
{
	$handle=opendir($dir);
	while ( $file=readdir($handle) )
	{
		if ( ($file==".") || ($file=="..") ) continue;
		if ( is_dir("$dir/$file") )
		{
			CleanDir("$dir/$file");
			if ( rmdir("$dir/$file") )	echo "Remove dir \"$dir/$file\" successfully!<br>";
                        else				echo "<FONT COLOR=RED>Fail to Remove dir \"$dir/$file\"!</FONT><br>"; 
		} 
		else      
		{ 
			if ( unlink("$dir/$file") )     echo "Del file \"$dir/$file\" successfully!<br>"; 
			else                 		echo "<FONT COLOR=RED>Fail to del file \"$dir/$file\"!</FONT><br>";  
		}
	}
	closedir($handle);
}

?> 
