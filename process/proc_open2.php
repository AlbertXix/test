 <?php
  $descriptorspec = array(
   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
   1 => array("pipe.txt", "w"),  // stdout is a pipe that the child will write to
   2 => array("file", "output.txt", "a") // stderr is a file to write to
);
 
 $prop = pro_open("md xlb", $descriptorspec, $pipes);
 
 if( is_resource( $prop ) )  
    {  
        fputs( $pipes[0] , '<?php echo \'Hello you!\n\'; ?>' );  
        fclose( $pipes[0] );  
  
        /** 
        while( ! feof( $pipes[1] ) ) 
        { 
            $line = fgets( $pipes[ 1 ] ); 
            echo urlencode( $line ); 
        } 
        */  
  
        proc_close( $res );  
    } else {
		exit('Cannot open the process.');
	}