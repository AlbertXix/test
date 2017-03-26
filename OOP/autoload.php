<?php
error_reporting(E_ALL);

function __autoload($cls){
	require './class/' . $cls . '.php';
}

new OneCls();
$Two = new TwoCls();
$Three = new ThreeCls();
