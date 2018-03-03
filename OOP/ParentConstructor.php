<?php 

class A
{
    function __construct(){
        echo "I'm class A constructor." . PHP_EOL; 
    }
}

class B extends A
{
   // function __construct() {
   //     parent::__construct();    
   // }
}

$b = new B;
// output: I'm class A constructor.
