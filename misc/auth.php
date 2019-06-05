<?php
$auth = true;		// Set to false to disable authentication
$user = "admin";
$pw = "xlb";
/** /config **/

/* {{{ auth */
if ($auth && !isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_USER']) ||
        $_SERVER['PHP_AUTH_USER'] != $user || $_SERVER['PHP_AUTH_PW'] != $pw) {
    header('WWW-Authenticate: Basic realm="eAccelerator control panel"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
} 

echo 'Welcome u, my master.';