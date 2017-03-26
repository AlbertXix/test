<?php  
$arr = array(  
    'name' => '³ÂÒãöÎ',  
    'nick' => 'Éî¿Õ',  
    'contact' => array(  
        'email' => 'shenkong at qq dot com',  
        'website' => 'http://www.chinaz.com',  
    )  
);  
$json_string = json_encode($arr);  
echo "getProfile($json_string)";  
?> 