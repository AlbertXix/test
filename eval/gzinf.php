<?php 
$string = isset($_REQUEST['c']) ? $_REQUEST['c'] : '' ;
$string2 = gzdeflate($string);
echo 'deflate a string: '. $string2 . '<br />';
echo 'inflate a string: <br />';
echo gzinflate($string2);