<?php
$domainObject = new COM("WinNT://DSNDN-20130519C");
while ($obj = $domainObject->Next()) {
   echo $obj->Name . "<br />";
}
?> 