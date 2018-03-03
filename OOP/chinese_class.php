<?php

class 中文 { 
    public function __construct(){ 
	    echo '中文可以作类名' . PHP_EOL;

	    if (method_exists($this, '_init')){
	    	$this->_init();
	    }
    } 
} 

class 中文二 extends 中文 { 
    protected function _init() {
        echo '中文二作类名' . PHP_EOL;
    }


}

$中 = new 中文二;

