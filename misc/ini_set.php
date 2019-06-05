<?php
echo 'max_execution_time = ' . ini_get('max_execution_time') . '<br />';
echo 'memory_limit = ' . ini_get('memory_limit') ;
echo '<hr />';
ini_set('max_execution_time','10');
ini_set('memory_limit','1024M');
echo 'max_execution_time = ' . ini_get('max_execution_time') ;
echo '<br />';
echo 'memory_limit = ' . ini_get('memory_limit') ;
echo '<br />';
echo 'post_max_size = ' . ini_get('post_max_size');
echo '<br />';
echo 'upload_max_filesize = ' . ini_get('upload_max_filesize');
