<?php
class employee{//定义员工父类
protected function working(){//定义员工工作，需要在子类的实现
echo "本方法需要在子类中重载!";
}
}
class painter extends employee{//定义油漆工类
public function working(){//实现继承的工作方法
echo "油漆工正在刷漆！/n";
}
}
class typist extends employee{//定义打字员类
public function working(){
echo "打字员正在打字！/n";
}
}
class manager extends employee{//定义经理类
public function working(){
echo "经理正在开会！";
}
}
function printworking($obj){//定义处理方法
if($obj instanceof employee){//若是员工对象，则显示其工作状态
$obj->working();
}else{//否则显示错误信息
echo "Error: 对象错误！";
}
}
printworking(new painter());//显示油漆工的工作
printworking(new typist());//显示打字员的工作
printworking(new manager());//显示经理的工作
?> 
