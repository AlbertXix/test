<?php
define('TOKEN', 'avpd-converter-is-good');
define('AES_KEY', 'Plt0kTpgTxB1feDCNc4hOcXxU7qgN5yTfE9XvZnlUIu');


$signature = $_GET["signature"] ?? '';
$timestamp = $_GET["timestamp"] ?? '';
$nonce = $_GET["nonce"] ?? '';
$echoStr = $_GET["echostr"] ?? '';

if (empty($signature) || empty($timestamp) || empty($nonce)) 
    exit('invalid parameters');

if (WxUtil::checkSignature($signature, $timestamp, $nonce))
    echo $echoStr;
else echo 'invalid signature';

class WxUtil {
    public static function checkSignature($signature, $timestamp, $nonce) {
        $token = TOKEN;
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        echo 'calculated sign: ' . $tmpStr . ', request sign: ' . $signature . PHP_EOL;

        if ($tmpStr == $signature ) {
            return true;
        } else {
            return false;
        }
    }
}
