<?php 

interface IBaseDao {

	public function add ( $data );

	public function delete ( $id );

	public function update ( $id );

	public function read ( $id = 0 );
}