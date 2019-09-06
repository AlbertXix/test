<?php

/*
 GET:
		/api/_nsm_1804.html?RC=1&CID=8000&UID=8000&VER=20180427&MVER=20180426&MID=e85e4f41e6017ab505ab08ec56b3c7ef&BW=32&NTMJ=5&NTMN=1&NTBL=2600&NTSPMJ=3&NTSPMN=0&NP=4&MM=1073192960&OSTC=1
		
	接口说明: 2018-04-19+
	   输出新业务模块的部署包
				
				内容格式：
				[
					{
									"module":
											{
														"name":"standard",
														"enc_hash":"模块文件 加密前HASH",
														"dec_hash":"模块文件 加密后文件HASH",
														"ver":"1.0",
														"url":"模块文件 下载地址",
														"config":"配置文件下载地址",
														"cft_hash":"配置文件HASH"
											}
									
						},
					{
									"module":
												{
															"name":"mb_gmodule",
															"enc_hash":"hfgsdgfafdsafsad",
															"dec_hash":"deeafdsafsadfaad",
															"ver":"2.0",
															"url":"",
															"config":"http://www.kukun.com/2.xml",
														 "cft_hash":"配置文件HASH"
												}
						}
			]	
*/

ini_set("date.timezone","Asia/Shanghai");
$xhprof_on = false;
// --- --- --- --- --- --- --- --- --- --- --- start profiling
/*
if ( in_array(intval(date('is')) ,array(1000)) ) {
   // xhprof_enable();
			xhprof_enable(XHPROF_FLAGS_MEMORY);
   $xhprof_on  = true;
			$_run_stime = microtime(true);
}
*/
// ----- //  profiling

// --- 
$_root_dir = str_replace('entry', '',     $_SERVER['DOCUMENT_ROOT']);
$_data_dir = str_replace('entry', 'data', $_SERVER['DOCUMENT_ROOT']);

include_once($_root_dir.'/config/conf.php');
include_once($_root_dir.'/entry/lib/aes_encode.lib.php');
include_once($_root_dir.'/entry/lib/get_client_ip.lib.php');
require_once($_root_dir.'/entry/lib/Ip2Region.php');
require_once($_root_dir.'/entry/lib/redis_url.lib.php');
require_once($_root_dir.'/entry/lib/_nsm_issued.php');
require_once($_root_dir.'/entry/lib/task_rnum.php');
require_once($_root_dir.'/entry/lib/area_conf.php');
require_once($_root_dir.'/entry/lib/Algorithm.php');
require_once($_root_dir.'/entry/lib/function_lib_bak.php');

// --- Task Formal
class Task_Formal {
	public $task_list = array();  // task list ,task info
	public $aes_key   = array(
	  'v20180x' => 'HR3V3T3nTsyR8A19',
	);
	
	/*
	 * -- GET.CID/MID 2 array: GID.UID
		* input: CID,MID   (array)
		* return: Array (array)
		*/
	public function get_gid($_get_cid, $_get_mid ) {
		$res       = array( 'uid' => 0 );
	 $_get_cid  = intval($_get_cid);
		// test channel
		if( $_get_cid <  1 )        $_get_cid = 9098;
		if( $_get_cid != 86868686 ) $_get_cid = substr($_get_cid,0,4);
		// get mid 
		$_get_mid = strip_tags($_get_mid);
		// child id
		if( in_array( $_get_cid, array(6408,6605,6405,8300) ) ){
			$res['uid'] = intval( redis_url('hget', 'cm_2_uid', $_get_cid.'_'.$_get_mid) );
		}
		$res['gid']  = intval(redis_url('hget','_uid_group',$_get_cid));
		return $res;
	}
	/*
	 * port log
		*/
	public function port_log($log_content,$task_id=0){
		if( !in_array($task_id,array(469)) ) return; // close log
		if( empty($log_content) ) return;
		error_log(
		 $log_content."\t".date('Y-m-d H:i:s')."\n", 
			3,
			'../../log/_nsm_port_'.date('ymd').'.txt'
		);
	}
	
	/*
	 * -- Access Task Info
		* input:  rds_key name , Task_id  (int)
		* return:    Array  (array)
	public function task_info( $rds_key, $task_skey ){
		// Null
		if( intval($task_skey) == 0 || empty($rds_key) ) return false;
		// Use Cache?
		if( array_key_exists($task_skey, $this->task_list) ){
			 return $this->task_list[$task_skey];
		}
		// -- : access task info
		$tinfo = redis_url('hget', $rds_key, $task_skey );
		if( !$tinfo ) return false;		
		$tinfo = json_decode( $temp, true);
		$this->task_list[$task_skey] = $tinfo;
		return $tinfo;				
	}
	
		*/
	/*
	 *
		*/	
 // function __construct(){}	
	// function __destruct(){}
}

$_class_task_formal = new Task_Formal();

$yuan_file_path = $_data_dir.'/y_nsm_xml/';   // xml原文件保存目录
$ua_file_path   = $_data_dir.'/m_baidu/';     // ua文件保存目录
$aes_KEY        = $_class_task_formal->aes_key['v20180x']; // "HR3V3T3nTsyR8A19";
$_class_aes     = new AES();

// client ip
$_client_ip = get_client_ip();

// access gid && uid	
$res = $_class_task_formal->get_gid($_GET['CID'], $_GET['MID']);

if( $_client_ip=='58.48.128.5X6' ){
	 echo "<h4>area_127</h4>\n";
		//echo redis_url('hget', 'mn_task_info', 'area_127');
  //print_r( $_class_task_formal->task_info('mn_task_info','area_127') );
		exit;
}

// access grou task
$_task_list = redis_url('hget','mn_group_task',$res['gid']); // 21
$task_list  = json_decode($_task_list, true);
$taskList   = $task_list['uid_area_list'];
$task_array[] = $taskList[0];
if($res['uid']>0) $task_array[] = $taskList[$res['uid']];

// Task ratio/time_start-time_end/max_issued
function task_continue($task_id,$tinfo){
	 global $_class_task_formal;
    $_response_status = 0;
    // -- Control Task max_issued
    if( !array_key_exists('max_issued',$tinfo) ) $tinfo['max_issued'] = 0;
    //    ? Use nax_issued
				// 任务修改时间为今天之前
    if( $tinfo['max_issued'] > 0 ){
        // 获取当前已经执行数
								$_use_issued = 'sum';
								if( $tinfo['time_edTime'] > 0 || date('ymd') == date('ymd',$tinfo['time_edTime']) ){
									$_use_issued = 'hour';
								}
								// $now_issued = _nsm_issued($task_id,$_use_issued);
								$now_issued = _issued_dh_number($task_id, $_use_issued);
        //   max_Issued set
        list($now_hour,$now_minute,$now_sec) = explode(':',date('H:i:s'));
        $now_hour   = $now_hour;
        $now_mmin   = intval(substr($now_minute,-1))+1;
        // if( 0 == $now_hour ) $now_hour = 1;
        // $now_issued+= round($now_issued/($now_hour*60)*$now_mmin);
        if( $tinfo['time_zone'] && strlen($tinfo['time_zone'])>4 && $_response_status==0){
										$time_zone = json_decode($tinfo['time_zone'], true);
										if( $_GET['test']==1 ){
														echo "day_issued_max: ".$tinfo['max_issued']."<br/>\n";
														print_r($time_zone);
										}
										//<<-- max_issued hour
										$tinfo['max_issued']  = $time_zone[$now_hour];
										// max_issued use sum
										if( $_use_issued == 'sum' ){
											$tinfo['max_issued'] = 0;
											foreach ($time_zone as $key_hour => $v_num){
												if( intval($key_hour) <= intval($now_hour) ){
													 $tinfo['max_issued'] +=$v_num;
												}
										 }
										}
										// -->>
										if( $_GET['test']==1 ){
												echo "hour_issued_max: ".$tinfo['max_issued']."<br/>\n";
										}
										// if( $_GET['test']==1 ) print_r($tinfo['max_issued']);
        }
        if( $tinfo['we_choose']==1 ) $tinfo['max_issued'] = _holiday_random($tinfo['max_issued']);  //周末数据勾选

        /*
        if( $now_issued > 0 && $now_issued >= $tinfo['max_issued'] ) $_response_status = 1;
        */
						// temporary log
						$log_content = "TaskID:{$task_id}\tLimit:{$_use_issued}\tIssued(N/M):[{$now_issued}/{$tinfo['max_issued']}]\tContinue:[{$_response_status}]";
						$_class_task_formal->port_log( $log_content ,$task_id);
    }

    // -- Control Task end time
    if( !array_key_exists('time_start',$tinfo) || empty($tinfo['time_start']) ) $tinfo['time_start'] = 0;
    if( !array_key_exists('time_end',  $tinfo) || empty($tinfo['time_end'])   ) $tinfo['time_end']   = 0;
    // use time_issued auth

    if( strlen($tinfo['time_issued']) > 10 ){
        $time_issued = json_decode($tinfo['time_issued'], true);
        foreach ($time_issued as $timeStr){
									 $timeStr = explode("=>", $timeStr);
										$tinfo['time_start'] = strtotime($timeStr[0]); // 21:30  17:44
										$tinfo['time_end']   = strtotime($timeStr[1]); // 22:30
										if( time() > $tinfo['time_start']  &&  time() < $tinfo['time_end'] ) break;
        }
    }

    if( strlen($tinfo['time_issued']) < 10 &&  $tinfo['time_start']==0 && $tinfo['time_end']==0){
        $_why_ignore = "time_limit no set";
        $_response_status = 1;  //计划任务未开始
    }

    // use time_start time_end auth
    if( intval($tinfo['time_start']) > 0 && time() < $tinfo['time_start'] ) {
        $_why_ignore = "time_limit:<".date('Y-m-d H:i:s',$tinfo['time_start']);
        $_response_status = 1;  //未开始
    }
    if( intval($tinfo['time_end'])  > 0 && time() > $tinfo['time_end']   ){
        $_why_ignore = "time_limit:>".date('Y-m-d H:i:s',$tinfo['time_end']);
        $_response_status = 1;  //已结束
    }
    /* -- Ratio
    根据比例判断 是否派发任务
    tatio: $temp['ratio']
    范围: 5% ~ 100%
    \*  */
    if( isset($tinfo['ratio']) && intval(substr(time(),-2))>=intval($tinfo['ratio']) ){
		$_why_ignore = "ratio:>{$tinfo['ratio']}";
		 $_response_status = 1;
	}

	// ---  周末数据勾选
    if(intval($tinfo['we_choose']) >0){
        //$_response_status = 1;

    }

	// debug log
	if( $_response_status == 1 ){
	  $log_content = "------:----\tIgnore:{$_why_ignore}";
	  $_class_task_formal->port_log( $log_content ,$task_id);
	}
    return $_response_status;
}

if( isset($_GET['MVER']) && $_GET['MVER']>='20180601' ) $mver = 1.1;
else $mver = 1.0;

$_response = array();

if($mver > 1.0){
    $app_cfg['wait_time'] = 0;
    $interval_time = intval(redis_url('get', 'mn_interval_time'));
    $app_cfg['interval_time'] = empty($interval_time) ? 1200:$interval_time; //任务运行间隔时间
    //$app_cfg['interval_time'] = 1200;
    $_response['config'] =$app_cfg;
    $_response['task'] = array();
}

$exec_count = 0;
foreach ($task_array as $v){
	  if( !is_array($v) || count($v) == 0 ) continue;
      foreach($v as $vv){
							     if( intval($vv) ==0 ) continue;
            $temp = redis_url('hget','mn_task_info', 'area_'.$vv ); // $_class_task_formal->task_info();
            if(!$temp) continue;
            $temp = json_decode($temp, true);
            // 判断任务状态是否继续执行
            if( task_continue($vv,$temp) == 1 ) continue;

            /*
												 $log_content = "TaskID:{$vv}\tNormal Operation";
													$_class_task_formal->port_log( $log_content );
												*/
												
												$_key_trnum  = $_client_ip;
												if( intval(date('Ymd')) > 20180825 ) $_key_trnum = sprintf('%u' , ip2long($_client_ip)); 
            $_task_rnum  = task_rnum($vv.'ta' , $_key_trnum);
            												// 开机验证
            if( $temp['r_mnsc']==0 && $_GET['MVER']>20180701 && $_GET['RC']>0 ) continue;
            // 单台机器执行次数限制
          /*
            if( ($temp['r_mnsc']>0 && $_task_rnum>=$temp['r_mnsc']) || $exec_count>=6 ) continue;
            else set_task_rnum($vv.'ta' , $_key_trnum, $_task_rnum );
          */

												// 记录通用接口执行日志
												$log_content = "ta----:{$vv}\tNormal Operation\t[RC:{$_GET['RC']},RMSC:{$_task_rnum}/{$temp['r_mnsc']}]\t".$_SERVER['REQUEST_URI'];
												$_class_task_formal->port_log( $log_content , $vv );

            $exec_count++;

            if( $temp['config'] ){
                $temp['has_cfg'] = true; //有配置文件
                $cg = $temp['config'];   //文件名
                $temp['config'] = 'http://sc.28wm.com/nsm_xml/'.$cg;
                if($temp['rf_list'] || $temp['s_list'] || $temp['gjc_list'] || $temp['is_ua']==1 || $temp['is_hao']==1 || $temp['conf_type']==4){    //动态s, rf, 关键词, UA 配置，hao123，多次点击
                    $rf_result = replace_rf_s($vv, $cg, $temp);

                    if ($rf_result === false){     //找不到文件
                        $log_name  =   str_replace('entry', 'log', $_SERVER['DOCUMENT_ROOT'])."/task_error_log/".date('Ymd')."_task_error.log";
                        error_log(date('H:i:s')."\t".$_SERVER['REQUEST_URI']."\t渠道任务area_{$vv} 找不到文件\n", 3, $log_name);
                        continue;
                    }
                    //print_r($rf_result);die;

                    if( $mver > 1 ){    //1.1版本
                        $temp['config']     = $rf_result['config'];
                        $temp['cfg_hash']   = $rf_result['cfg_hash'];
                        $temp['cfg_content']= $rf_result['cfg_content'];
                    }else{
                        $temp['config']     = $rf_result['config'];
                        $temp['cfg_hash']   = $rf_result['cfg_hash'];
                    }
                }
            }else{
                $temp['has_cfg'] = false;  //没有配置文件
                $temp['config'] = '';
            }
            if($temp['cfg_hash'] == null) $temp['cfg_hash']='';
            $temp['url'] = 'http://sc.28wm.com/C0m/'.$temp['url'];
            $temp['launch_as_user'] =false;
												// -- model_name -> task_id
												$temp['name'] = $vv;

          unset($temp['rf_list'], $temp['s_list'], $temp['cfg_id'], $temp['gjc_list'], $temp['ratio'], $temp['max_issued'], $temp['max_return'], $temp['time_start'], $temp['time_end'], $temp['time_issued'], $temp['r_mnsc'], $temp['time_zone'], $temp['config_p'], $temp['we_choose']);

          $temp['is_big'] = $temp['is_big']?true:false;
          if($mver > 1.0) $_response['task'][]['module'] = $temp;  //双版本兼容
          else $_response[]['module'] = $temp;

            //统计日志
            if(!$_GET['NTMJ'] || !$_GET['NTMN'] || !$_GET['BW']) $os='';
            else $os=$_GET['NTMJ'].'.'.$_GET['NTMN'].'-'.$_GET['BW'];
            $logArr = array(
                'date'=>date('Y-m-d H:i:s'),
                'IP'=>$_client_ip,
                'CID'=>$_GET['CID'],
                'taskID'=>$vv,
                'MID'=>$_GET['MID'],
                'OS'=>$os,
                'MVER'=>$_GET['MVER'],
                'taskType'=>'channel'
            );
            hour_log_record(json_encode($logArr));
    }
}

if( $_GET['test'] == 1 ){
	print_r($res);
	echo "<h4>A Tash</h4>";
	print_r($_response);
    if( isset($_GET['client_ip']) && !empty($_GET['client_ip']) ) $_client_ip=$_GET['client_ip'];
				echo $_client_ip;
}

// 获取地区任务   当前只能一个地区取一个任务
// -- 根据IP 查询IP库获取地区ID
function get_area_task($_client_ip){
	  global $_class_task_formal, $_root_dir, $province_conf, $mver, $area_conf_json;
    $area_conf_json = json_decode($area_conf_json, true);
    $ip2regionObj = new Ip2Region($_root_dir.'/entry/lib/Ip2Region/ip2region.db');
    $data = $ip2regionObj->memorySearch($_client_ip);
				
    $arr  = explode('|', $data['region']);   //Array ( [city_id] => 0 [region] => 中国|0|湖南|长沙|电信 )
    $proID = 0;
    if($arr[0] != '中国'){  //其它
        $areaID = "0_0";
    }elseif($arr[3]=='0' && $arr[2]=='0'){  //中国
        $areaID = "99_0";
    }elseif($arr[1]=='0' && in_array($arr[3],['北京','上海','天津','重庆'])){  //直辖市
        $proID = array_search($arr[3], $province_conf) +1;
        $areaID = $proID."_0";
    }else{    //normal
        $proID = array_search($arr[2], $province_conf) +1;
        $cityID = array_search($arr[3], $area_conf_json[$proID]);
        $areaID = $proID."_".$cityID;
    }

    if(explode("_", $areaID)[1] > 0) {    //先取省份任务 后取城市任务
        $taskIDStr = redis_url('lpop', 'mn_task_area_'.$proID."_0");
        if(empty($taskIDStr)) $taskIDStr = redis_url('lpop', 'mn_task_area_'.$areaID);
        else $areaID=$proID."_0";
    }else{
        $taskIDStr = redis_url('lpop', 'mn_task_area_'.$areaID);
    }

    $taskIDArr = explode(',', $taskIDStr);
    $result = array();

    foreach ($taskIDArr as $taskID){   //一个地区多个任务
				   if( intval($taskID) == 0 ) continue;
					   $temp  = redis_url('hget', 'mn_task_info', 'area_'.$taskID); //redis_url('hget', 'mn_task_info', 'area_'.$taskID);
        if(empty($temp)) continue;
        $temp    = json_decode($temp, true);

       /** 通过IP控制执行次数 start **/
        $_key_trnum  = $_client_ip;
        //if( intval(date('Ymd')) > 20180825 ) $_key_trnum = printf('%u',	ip2long($_client_ip));
        if( intval(date('Ymd')) > 20180825 ) $_key_trnum = sprintf('%u' , ip2long($_client_ip));
        $_task_rnum  = task_rnum($taskID.'area' , $_key_trnum);

        // 开机验证
        if( $temp['r_mnsc']==0 && $_GET['MVER']>20180701 && $_GET['RC']>0 ){
            put_area_task_tofile($areaID, $taskID);
            continue;
        }
        // 单台机器执行次数限制
        if( $temp['r_mnsc']>0 && $_task_rnum>=$temp['r_mnsc'] ){
            put_area_task_tofile($areaID, $taskID);
            continue;
        }else{
            set_task_rnum($taskID.'area' , $_key_trnum, $_task_rnum );
        }
        /** 通过IP控制执行次数 end **/

        // 判断任务状态是否继续执行
        if( task_continue($taskID,$temp) == 1 )
            continue;
							
							 $log_content = "A-----:{$taskID}\tNormal Operation\t".time()."\t".$_SERVER['REQUEST_URI'];
								$_class_task_formal->port_log( $log_content , $taskID );
        
								if($temp['config']){
            $temp['has_cfg'] = true;
												$temp['name']    = $taskID;
            $cg = $temp['config'];
            $temp['config'] = 'http://sc.28wm.com/nsm_xml/'.$cg;
            if($temp['rf_list'] || $temp['s_list'] || $temp['gjc_list'] || $temp['is_ua']==1 || $temp['is_hao']){    //动态s, rf, 关键词, UA 配置，hao123
                $rf_result = replace_rf_s($taskID, $cg, $temp);
                if ($rf_result === false){     //找不到文件
                    $log_name = str_replace('entry', 'log', $_SERVER['DOCUMENT_ROOT'])."/task_error_log/".date('Ymd')."_task_error.log";
                    error_log(date('H:i:s')."\t".$_SERVER['REQUEST_URI']."\t渠道任务area_{$taskID} 找不到文件\n", 3, $log_name);
                    continue;
                }

                if( $mver > 1 ){    //1.1版本
                    $temp['config']     = $rf_result['config'];
                    $temp['cfg_hash']   = $rf_result['cfg_hash'];
                    $temp['cfg_content'] = $rf_result['cfg_content'];
                }else{
                    $temp['config']     = $rf_result['config'];
                    $temp['cfg_hash']   = $rf_result['cfg_hash'];
                }
                unset($temp['rf_list']);
            }
        }else{
            $temp['has_cfg'] = false;
            $temp['config'] = '';
        }
        if($temp['cfg_hash'] == null) $temp['cfg_hash']='';
        $temp['url']    = 'http://sc.28wm.com/C0m/'.$temp['url'];
        $temp['launch_as_user'] =false;

        unset($temp['rf_list'], $temp['s_list'], $temp['cfg_id'], $temp['gjc_list'], $temp['ratio'], $temp['max_issued'], $temp['max_return'], $temp['time_start'], $temp['time_end'], $temp['time_issued'], $temp['r_mnsc'], $temp['time_zone'], $temp['config_p']);

        $temp['is_big'] = $temp['is_big']?true:false;
        $result[]['module'] = $temp;

        //统计日志
        if(!$_GET['NTMJ'] || !$_GET['NTMN'] || !$_GET['BW']) $os='';
        else $os=$_GET['NTMJ'].'.'.$_GET['NTMN'].'-'.$_GET['BW'];
        $logArr = array(
            'date'=>date('Y-m-d H:i:s'),
            'IP'=>$_client_ip,
            'CID'=>$_GET['CID'],
            'taskID'=>$taskID,
            'MID'=>$_GET['MID'],
            'OS'=>$os,
            'MVER'=>$_GET['MVER'],
            'taskType'=>'area'
        );
        hour_log_record(json_encode($logArr));
    }
    if(!empty($result)) return $result;
    else return false;
}

$_client_data = get_area_task($_client_ip);

if( strpos($_client_ip, '192.168.') == false && $_client_data !== false){
    if($mver > 1.0) $_response['task'] = array_merge($_response['task'], $_client_data);    //双版本兼容
    else $_response = array_merge($_response, $_client_data);
}

//任务优先级排序
if($mver > 1.0){
    usort($_response['task'], 'level_sort');
}else{
    usort($_response, 'level_sort');
}

if( $_GET['test'] == 1 ){
	echo "<h4>B Tash</h4>";
	print_r($_client_data);
    echo "<h3>Content</h3>";
    exit(json_encode($_response));
}

$_key_aes   = $app_conf['password']['nsm1804'];
echo $_class_aes->encode($_key_aes,json_encode($_response)); // base64_encode()

//记录每一小时领取任务的日志
function hour_log_record($data){
    $file_path = str_replace('entry','log',$_SERVER['DOCUMENT_ROOT']).'/_nsm_log';
    if(!is_dir($file_path)) @mkdir($file_path,0777,true);
    $file_path .= '/'.date('y-m-d_H').'_nsm_hour.txt';
    file_put_contents($file_path, "\n".$data, FILE_APPEND);
}

//替换xml文件内容的rf和s属性 重新计算hash
//function replace_rf_s($taskID, $cfg_id, $cnf_url, $rf_list, $s_list, $gjc_list, $is_ua=0, $is_hao=0, $conf_type, $config_p){
//$rf_result = replace_rf_s($vv, $temp['cfg_id'], $cg, $temp['rf_list'], $temp['s_list'], $temp['gjc_list'], $temp['is_ua'], $temp['is_hao'], $temp['conf_type'], $temp['config_p']);

function replace_rf_s($taskID, $cg, $task_info){
    global $aes_KEY, $_class_aes, $yuan_file_path, $ua_file_path, $mver;

    $xmlStr = file_get_contents($yuan_file_path.$cg.'s');  //源文件路径，内容
    if($xmlStr === false) return false;  //有缓存没文件
    if( $task_info['rf_list'] ){
        $rf_list = json_decode($task_info['rf_list'], true);
        $countRf_list = count($rf_list);
        $rf_index = rand(0, $countRf_list);
        $rf = $rf_list[$rf_index]['url'];  //随机获取rf链接

        $pos    = stripos($xmlStr, "rf");
        $xmlStr = preg_replace('/\srf="(.*?)"/', "", $xmlStr, 1);
        $xmlStr = preg_replace("/\srf='(.*?)'/", "", $xmlStr, 1);
        $xmlStr = str_insert($xmlStr, $pos, "rf='$rf' ");           //修改后的文件内容
    }
    if( $task_info['s_list'] ){
        $s_list = json_decode($task_info['s_list'], true);
        $countS_list = count($s_list);
        $s_index = rand(0, $countS_list);
        $s = rtrim($s_list[$s_index]['url']);  //随机获取s链接
        if( !empty($s) ){
            $pos    = stripos($xmlStr, "s=\"");
            $xmlStr = preg_replace('/\ss="(.*?)"/', "", $xmlStr, 1);
            $xmlStr = preg_replace("/\ss='(.*?)'/", "", $xmlStr, 1);
            $xmlStr = str_insert($xmlStr, $pos, "s='$s' ");           //修改后的文件内容
        }
    }
    if( $task_info['gjc_list'] ){
        $gjc_list = json_decode($task_info['gjc_list'], true);
        $countgjc_list = count($gjc_list);
        $gjc_index = rand(0, $countgjc_list);
        $keyword = $gjc_list[$gjc_index]['keyword'];  //随机获取关键词

        $kwd_pos    = stripos($xmlStr, "keyword=\"");
        if($kwd_pos !== false && !empty($keyword)){
            $keyword = urlencode($keyword);
            $xmlStr = preg_replace('/\skeyword="(.*?)"/', "", $xmlStr, 1);
            $xmlStr = preg_replace("/\skeyword='(.*?)'/", "", $xmlStr, 1);
            $xmlStr = str_insert($xmlStr, $kwd_pos, "keyword='$keyword' ");
        }
    }
    if( $task_info['is_ua'] == 1 ){
        $lineStr = substr($xmlStr, 0, strpos($xmlStr, "\n"));  // t标签
        $t_pos    = stripos($xmlStr, "<t");

//        preg_match("/mp=\"(.*?)\" /", $lineStr, $t_mp); // t标签 mp属性的值
//        $t_mp = $t_mp[1];
        $t_mp = rand(1, 3);
        preg_match("/a=\"(.*?)\" /", $lineStr, $t_a); // t标签 a属性的值
        $t_a = $t_a[1];

        if( $t_mp==1 ){
            $ua_content = file_get_contents($ua_file_path.'UA-ios.txt');
            $android_size = file_get_contents($ua_file_path.'ios-size.txt');
        } else {
            $ua_content = file_get_contents($ua_file_path.'UA-android.txt');
            $android_size = file_get_contents($ua_file_path.'android-size.txt');
        }
        $ua_content = explode("\n", $ua_content);
        $ua_index   = rand(0, count($ua_content)-1);
        $ua_content = $ua_content[$ua_index];
        $ua_content = trim(preg_replace("/\s\d{2,4}\s+/", "", $ua_content, 1));

        $android_size = explode("\n", $android_size);
        $android_index = rand(0, count($android_size)-1);
        $android_size = $android_size[$android_index];

        $t_a = " a='". $t_a."|". $android_size ."'";
        $xmlStr = preg_replace('/\sa="(.*?)"/', $t_a, $xmlStr, 1);
        $xmlStr = str_insert($xmlStr, $t_pos+3, "ua='$ua_content' ");
    }

    if( $task_info['is_hao'] ==1 ) $xmlStr = hao_123($xmlStr, $taskID);

    if( $task_info['conf_type']==4 && $task_info['is_hao']==0){   //多次点击
        $xmlStr = recur_click($taskID, $task_info, $xmlStr);
    }

    if( $mver >1 ){            //1.1版本
        $return['cfg_content'] = $xmlStr;
        $return['cfg_hash']   = sprintf('%08X', crc32($xmlStr));
    }else{                     //1.0版本
        $encStr = $_class_aes->encode($aes_KEY, $xmlStr);   //加密xml
        $return['cfg_hash'] = sprintf('%08X', crc32($encStr));
        $dir    = $_SERVER['DOCUMENT_ROOT'].'/_tmp_xml/';
        if(!is_dir($dir)) mkdir($dir, 0777, true);

        $cfg_id = $task_info['cfg_id'];
        if( $rf_index && $s_index ){    //避免内容更新文件名没更新
            $xml_name = $cfg_id.'_r'.$rf_index.'_s'.$s_index.'.xml';
        }elseif ( $rf_index && !$s_index ){
            $xml_name = $cfg_id.'_r'.$rf_index.'.xml';
        }elseif ( !$rf_index && $s_index ){
            $xml_name = $cfg_id.'_s'.$s_index.'.xml';
        }

        $filename = $dir.$xml_name;
        if( !file_exists($filename) ) file_put_contents($filename, $encStr);
        $return['config'] = "https://".$_SERVER['HTTP_HOST'].'/_tmp_xml/'.$xml_name;
    }
    log_xml_config($taskID, $xmlStr, $return['cfg_hash']);
    return $return;
}

function hao_123($xmlStr, $taskID)
{
    global $_client_ip;
    $algo    = new Algorithm();
    $taskCnt = mt_rand(1, 3);    //P标签的随机个数

    $tCnt_pos = stripos($xmlStr, "<t");
    $xmlStr   = str_insert($xmlStr, $tCnt_pos+3, "taskCnt=\"$taskCnt\" ");  //写入taskCnt字段

    $click_area = [];
    $p = "";
    for($i=1; $i<=$taskCnt; $i++){     //多个任务循环多次
        $hao = $algo->seleteData();    //标签属性
        $p .= "\r\n";
        $click_area[] = $hao['id'].":".$hao['titlename'].':'.$hao['tagname'].':'.$hao['attr'].':'.$hao['value'];
        $p .= "<p{$i} s1=\"1000\" s2=\"1000\" click=\"1\" arear_id=\"{$hao['id']}\" config=\"{$hao['titlename']}|{$hao['tagname']}|{$hao['attr']}|{$hao['value']}|OpenUrl(\'http://www.4399.com/?tn=sitehao123_15/\',1)|\" />";
    }

    $t_pos  = stripos($xmlStr, ">\r\n")+1;
    $xmlStr = str_insert($xmlStr, $t_pos, $p);

    $log['id'] = $taskID;
    $log['ip'] = $_client_ip;
    $log['dates'] = date('Y-m-d H:i:s');
    $log['taskCnt']    = $taskCnt;  //点击数
    $log['click_area'] = $click_area;
    hao123_log_record( json_encode($log)."\n", 'hao_active_log' );
    return $xmlStr;
}

function hao123_log_record($content, $dir){
    global $_data_dir;
    $file_path = $_data_dir.'/'.$dir;
    if(!is_dir($file_path)) @mkdir($file_path,0777,true);
    file_put_contents( $file_path.'/'.date('ymdH').'.txt', $content, FILE_APPEND );
}


//字符串插入
function str_insert($str, $i, $substr){
    $startstr='';
    $laststr='';
    for($j=0; $j<$i; $j++){
        $startstr .= $str[$j];
    }
    for ($j=$i; $j<strlen($str); $j++){
        $laststr .= $str[$j];
    }
    $str = ($startstr . $substr . $laststr);
    return $str;
}

function level_sort($x, $y) {
    return ($x['module']['level'] > $y['module']['level']);
}

//记录领取配置到日志
function log_xml_config($task_id, $xml_content, $cfg_hash){
    $file_path = str_replace('entry','log',$_SERVER['DOCUMENT_ROOT']).'/_nsm_xml_log';
    if(!is_dir($file_path)) @mkdir($file_path,0777,true);
    $file_path .= '/'.date('y-m-d')."_$task_id.txt";
    $data = ['content'=>$xml_content, 'hash'=>$cfg_hash];
    if(!file_exists($file_path)){
        file_put_contents($file_path, json_encode($data));
    }else{
        $filemtime = filemtime($file_path);
        if ( (time()-$filemtime)>300 ){
            file_put_contents($file_path, json_encode($data));
        }
    }
}


function put_area_task_tofile($areaID, $taskID){
    global $_data_dir;
    $data = json_encode( [$areaID=>$taskID] )."\n";
    $file_path = $_data_dir.'/area_update_log';
    if(!is_dir($file_path)) @mkdir($file_path,0777,true);
    $file_path .= '/'.date('ymdH').'_update_info.txt';
    file_put_contents($file_path, $data, FILE_APPEND);
}

// --- --- --- --- --- --- --- --- --- --- --- --- --- end profiling
/*
if( $xhprof_on ) {
	$xhprof_data = xhprof_disable();  
	$XHPROF_ROOT = realpath(dirname(__FILE__) .'/..');
	include_once $XHPROF_ROOT . "/xhprof/xhprof_lib/utils/xhprof_lib.php";
	include_once $XHPROF_ROOT . "/xhprof/xhprof_lib/utils/xhprof_runs.php";
	// save raw data for this profiler run using default
	// implementation of iXHProfRuns.
	$xhprof_runs = new XHProfRuns_Default();
	// save the run under a namespace "xhprof_foo"
	$run_id = $xhprof_runs->save_run($xhprof_data, "nsm1804");
	error_log($run_id."\t".(microtime(true)-$_run_stime)."\t".date('m/d/Y H:i:s')."\n",3,'../../log/xhprof.txt');
}
*/