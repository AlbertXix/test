<?php
// echo urlencode(serialize("admin"));
if (!get_magic_quotes_gpc())
{
	if (!empty($_GET)) $_GET = addslashes_deep($_GET);
	if (!empty($_GET)) $_POST = addslashes_deep($_POST);
	$_REQUEST = addslashes_deep($_REQUEST);
	$_COOKIE = addslashes_deep($_COOKIE);
}
if (isset($_GET['q'])) echo $_GET['q'];

function addslashes_deep($value)
{
	if (empty($value))
	{
		return $value;
	}
	else
	{
		return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
	}
}