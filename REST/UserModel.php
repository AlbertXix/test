<?php 
/*
 * @author: HarryXlb <harryxlb@gmail.com>
 * http://www.harenwang.com
 * @file: UserModel.php
 * create date: 2015-08-23 16:40:05
 */

class UserModel
{

    public $userInfo = [
        1 => ['name' => 'xilibo', 'age' => 32, 'sex' => 'male'],
        2 => ['name' => 'xlb', 'age' => 26, 'sex' => 'male']
    ];

    public function findOne($id){
        if (array_key_exists($id, $this->userInfo)) 
            return $this->userInfo[$id];
        else
            return [];
    } 

    public function findAll(){
        return $this->userInfo;
    }
}

