<?php
class SecQA{
	
	var $image_width;
	var $image_height;
	var $image = '';
	
	function __construct($image_width = 100, $image_height = 30){
		$this->image_width = $image_width;
		$this->image_height = $image_height;
	}

	public function rand4string($i,$num){
		$font=mt_rand(4, 5);
		$x=mt_rand(1, 8) + $this->image_width * $i / 6;
		$y=mt_rand(1, $this->image_height / 4); 
		$color=imagecolorallocate($this->image, mt_rand(180, 240), mt_rand(180, 240),mt_rand(180, 240));
		// $color=imagecolorallocate($this->image, mt_rand(50, 160), mt_rand(50, 160),mt_rand(50, 160));
		imagestring($this->image, $font, $x, $y, $num, $color);
	}
	
	public function addMask($filter = IMG_FILTER_EMBOSS){
		// imagefilter($this->image, $filter);
		$colorline = imagecolorallocate($this->image, rand(80, 160), rand(80, 160), rand(80, 160));
		$colorpixel = imagecolorallocate($this->image, rand(160, 250), rand(160, 250), rand(160, 250));
		for ($x = 0; $x < $this->image_width; $x++){
			imageline($this->image, 1, rand(1, $this->image_height), rand(10, $this->image_width), rand(1, $this->image_height), $colorline);
			imagesetpixel($this->image, rand(0, $this->image_width), rand(0, $this->image_height), $colorpixel);
		}
	}
	
	public function makeOut(){
		session_start();
		header("content-type:image/png");
		$this->image = imagecreate($this->image_width, $this->image_height);
		imagecolorallocate($this->image,rand(80, 160), rand(80, 160), rand(80, 160));
		$random4symbol = array("+","-","x"); 
		$result = 0;
		$symbol = $random4symbol[mt_rand(0,count($random4symbol)-1)];
		$num1 = mt_rand(1,9);
		$this->addMask();
		$this->rand4string(0,$num1);
		$num2 = mt_rand(1,9);
		switch ($symbol){
			case "+":
				$result = $num1 + $num2;
				$this->rand4string(1,"+");
				break;
			case "-":
				$result = $num1 - $num2;
				$this->rand4string(1,"-");
				break;
			case "x":
				$result = $num1 * $num2;
				$this->rand4string(1,"x");
				break;
		}
		$this->rand4string(2,$num2);
		$this->rand4string(3,"=");
		$this->rand4string(4,"?");
		$_SESSION['code'] = $result;
		imagepng($this->image);
		imagedestroy($this->image);
	}
}

$qa = new SecQA(100, 30);
$qa->makeOut();
