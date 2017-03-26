<?php 
    echo "\nDECODE nested eval(gzinflate()) by DEBO Jurgen <jurgen@person.be>\n\n"; 
    
    echo "1. Reading coded.txt\n"; 
    $fp1      = fopen ("coded.txt", "r"); 
    $contents = fread ($fp1, filesize ("coded.txt")); 
    fclose($fp1); 
    
    echo "2. Decoding\n"; 
    while (preg_match("/eval\(gzinflate/",$contents)) { 
        $contents=preg_replace("/<\?\sphp|\?>/", "", $contents); 
        eval(preg_replace("/eval/", "\$contents=", $contents)); 
    } 
        
    echo "3. Writing decoded.txt\n"; 
    $fp2 = fopen("decoded.txt","w"); 
    fwrite($fp2, trim($contents)); 
    fclose($fp2); 