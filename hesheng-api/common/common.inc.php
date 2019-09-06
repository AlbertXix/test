<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set("date.timezone","Asia/Shanghai");
set_time_limit(0);
$root_dir   = dirname(dirname(dirname(dirname(__FILE__))));
$src_dir    = $root_dir."/entry";
$lib_dir    = $root_dir."/entry/lib";
$cache_path = $root_dir.'/cache/_nsm/';   //Cache文件保存目录

require_once("$lib_dir/normal_help.php");
require_once("$lib_dir/json_help.php");
require_once("$lib_dir/aes_encode.lib.php");
require_once("$lib_dir/log_help.php");
require_once("$lib_dir/database.php");
require_once("$lib_dir/curl_file_get_contents.lib.php");
require_once("$lib_dir/redis_url.lib.php");
require_once("$lib_dir/cache.lib.php");    //redis
require_once("$root_dir/config/conf.php");

function get_db(){
    global $app_conf;
    get_db_help()->connect($app_conf,$app_conf['debug']);
    return get_db_help();
}
