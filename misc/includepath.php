<?php
echo 'get_include_path: '. get_include_path() . '<br />';

set_include_path(get_include_path() . PATH_SEPARATOR . 'D:/');
include 'x.php';
