<?php
class painter{ //定义油漆工类
	public function paintbrush(){ //定义油漆工动作
		echo "油漆工正在刷漆！/n";
	}
}

class typist{ //定义打字员类
	public function typed(){ //定义打字员工作
		echo "打字员正在打字！/n";
	}
}

function printworking($obj){ //定义处理类
	if($obj instanceof painter){ //若对象是油漆工类，则显示油漆工动作
		$obj->paintbrush();
	}elseif($obj instanceof typist){ //若对象是打字员类，则显示打字员动作
		$obj->typed();
	}else{ //若非以上类，则显示出错信息
		echo "Error: 对象错误！";
	}
}
printworking(new painter()); //显示员工工作
printworking(new typist()); //显示员工工作
