<?php      
function gbk2utf8($filename)      
{      
    $content = file_get_contents($filename);      
    $fp = fopen($filename, "w");       
    $content = utf8_encode($content);      
    $content = "\xEF\xBB\xBF".$content;      
    fputs($fp, $content);       
    fclose($fp);      
}      
 
$dir = 'D:\inetpub\Web';//所要转换的文件或文件夹路径
listDir($dir);      
function listDir($dir)      
{      
    $dp = opendir($dir);      
    while($file = readdir($dp))      
    {      
        if($file == '.' || $file == '..')      
            continue;      
        if(eregi('.php', $file))//如果有其他文本文件就把 eregi('.php|.txt|.htm', $file) 改动一下。会覆盖掉原先的文件，生成的时候文件名变换下，或另建目录生成。   
        {      
            echo $dir.'\\'.$file."<br>";    
            gbk2utf8($dir.'\\'.$file);    
        }    
        else    
        {    
            if(is_dir($dir.'\\'.$file))    
                listDir($dir.'\\'.$file);      
        }      
        clearstatcache();      
    }      
}      
?>