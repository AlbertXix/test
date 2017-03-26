<?php 
// 调用魔术方法__get(), __set()
// 用以实现属性的赋值&取值

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

