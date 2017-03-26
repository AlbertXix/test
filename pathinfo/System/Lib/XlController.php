<?php 
abstract class XlController
{
	public static function __callStatic($name, $arguments) {
		// print_r($arguments); echo ' function: ' . rtrim($name, '--');
		$obj = new $arguments[0]();
		$obj->{rtrim($name, '--')}();
	}

	public function __call($name, $arguments){
		throw new Exception(":( $name cannot found.", 1);
		
	}
}
