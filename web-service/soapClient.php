<?php

/* * *
 * @Package: soap
 * 
 * @Id: soapClient.php
 * 
 * @DateTime: 2014-3-31 23:32:06
 * 
 * @Author: harryxlb
 * 
 */

$client = new SoapClient(null, array(
      'location' => "http://localhost/WebService/soapServer.php",
      'uri'      => "http://localhost/WebService/soapServer.php",
      'trace'    => 1 ));

echo $return = $client->__soapCall("helloWorld",array("world", 'girl', 'car', 'house', 'baby', 'home', 'family', 'happiness'));
