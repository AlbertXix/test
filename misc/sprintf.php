<?php 
	print 'Hello, print.' . '<br />';
	echo 'Hello, echo,', ' echo' . '<br />';
	$str = sprintf('Hey, do you think %s, how about %d years later...', 'today is a nice day? ', 1000);
	echo 'Hello, sprintf: ' . $str;