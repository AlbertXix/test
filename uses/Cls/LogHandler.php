<?php
namespace Cls\H;

// use Cls\H as H;
use Cls\Data\DataView as D;

// class LogHandler extends \H\AbsHandler {
class LogHandler {
	
	function __construct(){
		echo 'LogHandler is here.<br />';
		echo 'use Src\data class test, dbname is: ' . D::DBNAME;
	}
	
	
}