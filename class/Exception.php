<?php
try {
    $error = 'Always throw this error';
    throw new Exception($error);

    // 从这里开始，tra 代码块内的代码将不会被执行
    echo 'Never executed';

} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br />";
	echo 'Caught exception File: ',  $e->getFile(), "<br />";
	echo 'Caught exception Code: ',  $e->getcode(), "<br />";
	echo 'Caught line: ',  $e->getLine(), "<br />";
	echo 'Caught exception: ',  $e->getTrace(), "<br />";
	echo 'Caught exception: ',  $e->getTraceAsString(), "<br />";
	echo 'Caught exception: ',  $e->getMessage(), "<br />";
}

// 继续执行
echo 'Hello World';
?> 
