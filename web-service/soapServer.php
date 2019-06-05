<?php

/* * *
 *  Workin Server with Client for localhost
 * 
 * @Id: soapServer.php
 * 
 * @DateTime: 2014-4-1 22:25:51
 * 
 * @Author: harryxlb
 * 
 */

class MyClass {
  public function helloWorld() {
//        return 'Hallo Welt <br />'. print_r(func_get_args(), true);
  }
}
 
try {
  $server = new SOAPServer(
    NULL,
    array(
     'uri' => 'http://localhost/WebService/soapServer.php'
    )
  );
 
  $server->setClass('MyClass');
  $server->handle();
}
 
catch (SOAPFault $f) {
  print $f->faultstring;
}


