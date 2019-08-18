<?php

class WxRedPackage 
{
    private $leftMoney = 0;

    private $leftTimes = 0;

    private $minMoney = 0;

    public function __construct($leftMonly, $leftTimes, $minMoney){
        if ($leftMonly <= 0 || $leftTimes <= 0){
            throw new \InvalidArgumentException("Left money and left times must not be zero");
        }

        $this->leftMonly = $leftMonly;
        $this->leftTimes = $leftTimes;
        $this->minMoney = $minMoney;
    }

    public function oneRedPackage(){
        if ($this->leftTimes == 1){
            $this->leftTimes--;
            return (double) round($this->leftMonly * 100) / 100;
        }
    
        $maxMoney = $this->leftMonly / $this->leftTimes * 2;
        //$curMoney = explode(' ', microtime())[0] / 100 * $maxMoney;
        $curMoney = mt_rand() / mt_getrandmax() * $maxMoney;
        echo 'curmoney: ' . $curMoney . PHP_EOL;
        $curMoney = $curMoney <= $this->minMoney ? $this->minMoney : $curMoney;
        $curMoney = floor($curMoney * 100) / 100;
        $this->leftTimes--;
        $this->leftMonly -= $curMoney;

        return $curMoney;
    }

    public function genRedPackages(){
        $redPackages = [];
        $packageSize = $this->leftTimes;
        for ($i = 0; $i < $packageSize; $i++){
            $redPackages[] = $this->oneRedPackage();
        }

        return $redPackages;
    }

}

$wxRedPackage = new WxRedPackage(10, 10, 0.01);
$redPackages = $wxRedPackage->genRedPackages();
echo 'total red package money: ' . array_sum($redPackages) . PHP_EOL;
print_r($redPackages);
