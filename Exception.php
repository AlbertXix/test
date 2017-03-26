<?php 

try {
	throw new Exception('Some Exception Give You.');
} catch (Exception $e) {
	echo 'Oops !!! ==> ' . $e->getMessage();
}