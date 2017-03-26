<?php
$words_form = 
<<<EOF
<!doctype html>
<html>
<head><title></title>
</head>
<body>
	<form action = "" method="post">
		<input type="text" name="txt_badwords">
		<input type="submit" name="btn_chk" value="Check bad words.">
	</form>
<body></html>
EOF;
echo($words_form);

function swap_badwords($str_words){
	$bad_words = array(
		'你妈', '日', '江泽民',
	);
	$filter_words = array(
		'妈妈', '太阳', '江主席',
	);
	$final_words = str_replace($bad_words, $filter_words, $str_words);
	return $final_words;
	}

if (isset($_POST['btn_chk']) && isset($_POST['txt_badwords'])){
	$bad_word = $_POST['txt_badwords'];
	echo swap_badwords($bad_word);
	}
?>