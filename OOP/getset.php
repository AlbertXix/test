<?php 

class MyApp{

	private $_property = array();
	
	function __set($key, $value){
		$this->_property[$key] = $value;
	}
	
	function __get($key){
        if (isset($this->_property[$key]))
		    return $this->_property[$key];
        else
            return NULL;
	}
}

$MyApp = new MyApp();
$MyApp->__set('MyName', 'XLB');
echo 'get MyName: ' . $MyApp->__get('MyName') . PHP_EOL;

$MyApp->company = 'KOTEI';
echo 'company: ' . $MyApp->company . PHP_EOL;
var_dump($MyApp->compa);

