<?php 

require 'AbsBaseDao.php';

class UserImpl extends AbsBaseDao
{
	public function __construct(){
		echo $this->add('mydata 100') . "<br />";
		echo $this->special();
	}

	public function special(){
		return "special overide function in " . __CLASS__;
	}

}
