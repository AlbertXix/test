<?php 

function myFunc(){
	return __FUNCTION__;
}

echo 'function name is: ' . myFunc() . '<br />';

class myClass{
	static function clsFunc(){
		return __METHOD__;
	}

	static function cls(){
		return __CLASS__;
	}
}

echo '__CLASS__ name is: ' . myClass::cls() . ', its __METHOD__ is: ' . myClass::clsFunc();
echo '<br />';
echo 'current __DIR__ is: ' . __DIR__;