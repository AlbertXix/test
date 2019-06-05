<?php
	$csv = array('user'=>'harryxlb', 'pass' => 'xlb', 'info' => array('sex' => 'm', age => '100', 'memo' => 'Haker') );
	// $fp = fopen('xlb.csv', 'w');
	// fputcsv($fp, $csv);
	$fp2 = fopen('xlb2.csv', 'r');
	while ( ! feof($fp2) ) {
		$csv_r = fgetcsv($fp2);
		print_r($csv_r);
	}