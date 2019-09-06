#!/usr/bin/env php
<?php
require '/var/www/hesheng/entry/lib/aes_encode.lib.php';
//$api_url = 'http://hesheng.loc/api/_nsm_1908.php?RC=1&CID=8000&UID=&VER=20180601&MVER=20180601&MID=e85e4f41e6017ab505ab08ec56b3c7ef&BW=32&NTMJ=5&NTMN=1&NTBL=2600&NTSPMJ=3&NTSPMN=0&NP=4&MM=1073192960&OSTC=1&test=1';
$api_url = 'http://mn.ixpben.com/api/_nsm_1908.php?RC=1&CID=6501&UID=&VER=20180601&MVER=20180601&MID=e85e4f41e6017ab505ab08ec56b3c7ef&BW=32&NTMJ=5&NTMN=1&NTBL=2600&NTSPMJ=3&NTSPMN=0&NP=4&MM=1073192960&OSTC=1&test=1';
//$search_num = 12500;
$pv_ip_ratio = 2.5;
$start = 1;
$end = 2500;
$aes_key = 'HR3V3T3nTsyR8A19';
//$ids = [724, 725, 722];
$ids = [794, 795, 796];
$api_back_url = 'http://hesheng.loc/api/_hao_tj.php';
$aes = new AES();
$data = '{"ID":"722","args":{"result":[{"clicknum":2,"clickresult":1,"clickurl":";https:\/\/tuijian.hao123.com\/?type=rec;https:\/\/www.hao123.com\/mid?from=shoubai&key=9130584482330797465&type=rec","openurl":"https:\/\/www.hao123.com\/?tn=654123_hao_pg","searchnum":0},{"clicknum":0,"clickresult":0,"clickurl":";https:\/\/www.hao123.com\/?tn=654123_hao_pg","openurl":"https:\/\/www.hao123.com\/?tn=654123_hao_pg","searchnum":0},{"clicknum":0,"clickresult":0,"clickurl":";https:\/\/www.hao123.com\/?tn=654123_hao_pg","openurl":"https:\/\/www.hao123.com\/?tn=654123_hao_pg","searchnum":0}]},"method":0,"IP":"111.147.170.227","dates":"2018-12-25 17:12:35"}';
$cmd = 'php ./cron_hour_search.php';

if ($argc >= 2)
    $start = intval($argv[1]) > 0 ? intval($argv[1]) : $start;
if ($argc >= 3)
    $end = intval($argv[2]) > 0 ? intval($argv[2]) : $end;
//$batch_num = 4;
if ($argc >= 4 && !in_array($argv[3], [ 'req', 'ret' ])){
    show_usage();
    exit;
}

for ($i = $start; $i <= $end; $i++){
    echo 'HTTP request GET _nsm_1804.php...' . ($i + 1) . PHP_EOL;
    $ip = '192.168.1.' . $i;
    $url = $api_url . "&ip=$ip";
    $rnd = rand(floor($pv_ip_ratio), ceil($pv_ip_ratio));
    //echo "Sending request with IP: $ip, $j times...";
    echo "Sending request with IP: $ip...";
    if (empty($argv[3]) || $argv[3] == 'req'){
        $ret = curl_requset($url);
        if (!$ret) {
            echo 'FAIL.' . PHP_EOL;
            break;
        }
    }

    echo "OK!\n";
    //echo $ret . "\n\n";
    usleep(100000);
    echo "Sending back data...\n";

    $back_data = json_decode($data, true);
    $back_data['ID'] = $ids[array_rand($ids)];
    $back_data['IP'] = $ip;
   // foreach ($back_data['args']['result'] as $key => $val) {
   //    //$back_data['args']['result'][$key]['searchnum'] = rand(floor($pv_ip_ratio), ceil($pv_ip_ratio));
   // }

    if (empty($argv[3]) || $argv[3] == 'ret'){
        if ($i <= $end / 2)
            $back_data['args']['result'][0]['searchnum'] = 1;
        else
            $back_data['args']['result'][0]['searchnum'] = rand(4, 5);

        $back_data = json_encode($back_data);
        $back_data = $aes->encode($aes_key, $back_data);
        //$back_data = base64_encode($back_data);
        for ($j = 0; $j < $rnd; $j++) {
            $back = curl_requset($api_back_url . '?ip=' . $ip, 'POST', $back_data);
            echo "BACK: " . $back . PHP_EOL;
        }
    }

    if (($i + 1) % 100 == 0) {
        exec($cmd);
    }
}

echo 'All request done.' . PHP_EOL;
exit(0);

function curl_requset($url, $method = 'GET', $param = []){
    $method = strtoupper($method);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    if ($method == 'POST'){
        curl_setopt($ch, CURLOPT_POST, 1);
        if (is_array($param)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        } else {
            if (!empty($param))
                curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }
    }

    $result = curl_exec($ch);
    if (curl_errno($ch)){
        echo 'Error: ' . curl_error($ch) . PHP_EOL;
        return false;
    } 

    return $result;
}

function show_usage(){
    global $argv;
    echo $argv[0] . ' [start_num] [end_num] [req|ret]' . PHP_EOL;
    echo $argv[0] . ' 1 2500 req' . PHP_EOL;
}
