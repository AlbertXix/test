<?php
/*
  收集 _nsm_1804 的hao123任务取到后 将执行任务的结果上传至此接口

  POST方式上传
*/

include_once('../lib/get_client_ip.lib.php');
require_once("../lib/aes_encode.lib.php");
require_once __DIR__ . '/../lib/redis_url.lib.php';

$_client_ip = get_client_ip();

$_post_info = file_get_contents('php://input');


// hao123保存路径
$_log_dir  =  '../../data/_hao_tj';
if( !is_dir($_log_dir) ) mkdir($_log_dir);
$_log_file = $_log_dir.'/_hao_'.date('ymd').'.txt';

// nsm保存路径
$_nsm_dir  =  '../../data/_nsm_tj';
if( !is_dir($_nsm_dir) ) mkdir($_nsm_dir);
$_nsm_file = $_nsm_dir.'/_tj_'.date('ymd').'.txt';


if( isset($_GET['test']) ){
    print_r($_GET);
    print_r($_post_info);
}

if( !empty($_post_info) ){
    $aes = new AES();
    $key = "HR3V3T3nTsyR8A19";
    $_post_info = $aes->decode($key, $_post_info);
    $_post_info = trim($_post_info);
    $_post_info = json_decode($_post_info, true);
    if( !empty($_post_info) ){
        $_post_info['IP']    = $_client_ip;
        $_post_info['dates'] = date('Y-m-d H:i:s');
        $search_num = 0;
        if (isset($_post_info['args']['result'])) {
            $search_num = array_sum(array_column($_post_info['args']['result'], 'searchnum'));
            $click_num = array_sum(array_column($_post_info['args']['result'], 'clicknum'));
        }

        error_log( json_encode($_post_info)."\n", 3, $_log_file );   //hao123

        error_log( json_encode(array(
                'ID'    => $_post_info['ID'],
                'RET'   => isset($_post_info['RET'])?intval($_post_info['RET']):1,
                'dates' => date('Y-m-d H:i:s'),
                'IP'    => $_client_ip
            ))."\n", 3, $_nsm_file );   // nsm

        $key_prefix = 'search.t' . $_post_info['ID'] . '.';
        redis_url('hincrby', $key_prefix . 'ip_back', $_client_ip, 1);
        redis_url('hincrby', $key_prefix . 'pv_back', 'total', 1);
        redis_url('hincrby', $key_prefix . 'search_back', 'total', $search_num);

        $key_prefix = 'click.t' . $_post_info['ID'] . '.';
        redis_url('hincrby', $key_prefix . 'click', 'click_back', $click_num);
    }
}
