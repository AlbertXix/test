<?php
class Bitmap
{
    private $max_number;
    private $bitmapArr;
    private $phpIntBitSize;
    private $outputSortArr = [];

    public function __construct(int $max_number = 10) {
        $this->max_number = $max_number;
        //设置php bitmap存储数组
        $this->bitmapArr = array_fill(0, $max_number, 0);
        //php中默认 int类型占用空间为8byte，即：64bit。如果数组个数设置为的10个，则存储最大不能超过 10 * 64 = 640;
        $this->phpIntBitSize = PHP_INT_SIZE * 8;
    }


    public function shiftBit(array $sortArray = []) {
        foreach ($sortArray as $value) {
            if ($this->phpIntBitSize * $this->max_number < $value) {
                throw new \Exception('排序数组中' . $value . '超出最大值,请设置合适的数组', 400);
            }
            $key = $value / $this->phpIntBitSize;
            $key = floor($key);
            $remainde = $value % $this->phpIntBitSize;
            $this->bitmapArr[$key] = $this->bitmapArr[$key] | (1 << $remainde);
        }
        return $this->bitmapArr;
    }


    public function outputSortResult() {
        foreach ($this->bitmapArr as $key => $item) {
            for ($i = 0; $i < $this->phpIntBitSize; $i++) {
                if ((1 << $i) & $item) {
                    //存在数字
                    $this->outputSortArr[] = $key * $this->phpIntBitSize + $i;
                }
            }
        }
        return $this->outputSortArr;
    }
}

$BitMap = new Bitmap(20);
$bitArr = $BitMap->shiftBit([10, 6, 3, 4, 5, 8, 2, 3, 600, 100, 640, 400, 200, 350]);
echo 'bitArr: ' . PHP_EOL;
print_r($bitArr);
echo PHP_EOL . 'restore array -- after unshift bit:' . PHP_EOL;
$sortResult = $BitMap->outputSortResult();
var_dump($sortResult);
