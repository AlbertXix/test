<?php




$url=$_GET['loc'];
if(!preg_match("#^[a-z]{3,4}://#", $url)){
	$url="http://$url";
}
$arr_url=parse_url($url);
$ipaddress=gethostbyname($arr_url['host']);
if($ipaddress==$arr_url['host']){
	$ipaddress="";
}
$arr_ipinfo=geoip_record_by_name ($ipaddress);
$isp=geoip_isp_by_name($ipaddress); 
$arr_ipinfo['isp']=$isp;
$org=geoip_org_by_name($ipaddress); 
$arr_ipinfo['org']=$org;
$arr_ipinfo['ip']=$ipaddress;


?><html>
<head></head>

<body>


<h1>ip2info Service</h1>

<form action="/" method="get">

Enter URL to get Info about the remote Server's IP:<br />
<input type="text" style="width:400px;" name="loc" value=""><input type="submit" value="ok" name="ok" />

</form>

<br /><br /><br />

<h2>Info for </h2><br />
<br />
(C)ip2info<br />
This product includes Geo data created by MaxMind, available from http://www.maxmind.com

</body>

</html>

