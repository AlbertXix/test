<?php
namespace Cls\H;

abstract class AbsHandler {
	
	function __construct(){
		echo 'AbsHandler is here.';
	}
	
	abstract protected function showLog();
	
}