<?php 

require 'IBaseDao.php';

abstract class AbsBaseDao implements IBaseDao
{
	public function add ( $data ) {
		return 'Data add ok: ' . $data;
	}

	public function delete ( $id ) {
		return "id: $id is delete.";
	}

	public function update ( $id ) {

	}

	public function read ( $id = 0 ) {

	}

	public function special(){
		return "special function in " . __CLASS__;
	}
}