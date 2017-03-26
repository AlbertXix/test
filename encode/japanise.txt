<?php
define("PATH", dirname(__FILE__));

$original_dir = '.';    /* 待转码目录 */
$dest_dir = './../dest';
if (!file_exists($dest_dir)) {
    mkdir($dest_dir);
}

/* 文件转码(日文转换UTF8) */
function shjs2utf8($filename) {
    $content = file_get_contents($filename);   
    if (function_exists('mb_detect_encoding')) {
        $original_encode = mb_detect_encoding($content, "UTF-8, SJIS");
        $content = mb_convert_encoding($content, 'UTF-8', $original_encode);
    }
    else {
        $content = iconv('SJIS', 'UTF-8', $content);
    }
    return $content;
}

/* 遍历目录 */
function traversal($dir, $dir_to = null) {
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (false !== ($file_name = readdir($dh))) {
                if ($file_name == '.' || $file_name == '..') {
                    continue;
                } else {
                    $_item = $dir . '/' . $file_name;    
                    $_item_to = $dir_to . '/' . $file_name;

                    /* 判断文件格式 */
                    if (is_dir($_item)) {
                        if(!is_dir($_item_to)){
                            mkdir($_item_to);
                        }
                        traversal($_item, $_item_to);
                    } else {
                        /* 获取文件扩展名 */
                        $path_arr = pathinfo($_item);
                        $path_arr['extension'] = strtolower($path_arr['extension']);

                        /* 控制要转换的类型 */
                        if (in_array($path_arr['extension'], array('php', 'js', 'html', 'css', 'htm'))) {
                            /* 调用转码函数 */
                            $content = shjs2utf8($_item);
                            file_put_contents($_item_to, $content);
                        } else {
                            /*其他的直接拷贝副本*/
                            copy($_item, $_item_to);
                        }   
                    }
                }
            }
        }
    }
}

/* 计算时间 */
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/* begin time */
$time_start = microtime_float();

/* 执行脚本 */
traversal($original_dir, $dest_dir);

/* end time */
$time_end = microtime_float();

echo $time = $time_end - $time_start;

?>