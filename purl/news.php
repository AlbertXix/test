<?php
class News{
	private $id, $title, $desc;

	function __construct($id, $title, $desc){
		$this->id = $id;
		$this->title = $title;
		$this->desc = $desc;
	}

	public function showNews(){
		echo "Here is your order news:\n";
		echo 'ID: ' . $this->id . PHP_EOL;
		ECHO 'Title: ' . $this->title . PHP_EOL;
		echo 'Desc: ' . $this->desc . PHP_EOL;
	}
}
