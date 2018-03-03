<?php
// 调用魔术方法__call
// 其在调用一个不存在的方法时被执行

class Magic{
	public function __call($func, $param = array()){
		// throw new Exception('Fuck Error!!!');
		exit($func . ' function is not exits!');
	}
}

$Magic = new Magic();
$Magic->show();
