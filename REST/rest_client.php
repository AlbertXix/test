<?php 
/*
 * @author: HarryXlb <harryxlb@gmail.com>
 * http://www.harenwang.com
 * @file: rest_client.php
 * create date: 2015-08-23 15:37:11
 */

class RestClient
{
    public static function curl_request($url, $method = 'GET', $params= array(), $timeout = 15){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($ch);
        if (curl_errno($ch)) return curl_error($ch);
        curl_close($ch);
        return $result;
    }
}

error_reporting(E_ALL & ~E_NOTICE);
$result = RestClient::curl_request('http://localhost/test/REST/index.php/user/1', $argv[1], $arg[2]);
echo str_repeat('=', 110) . PHP_EOL; 
print_r($result);

