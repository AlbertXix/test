<?php

/**
 * Class Algorithm
 * 点击业务和搜索业务概率控制
 */
class Algorithm
{
    private $config = [];

    private $haoPageArea = [];	// h123各区域点击比例预设值 Array

    private $hotSites = [];     // h123 hot 词连接列表 Array

	private $topSites = [];     // h123 top 词连接列表 Array

    private $allowedType = ['search_fixed', 'search_float'];

	// __ H123业务
	public $now_hour;             // 当时小时 Str
	public $time_zone;            // 小时曲线 [ '00'=>Number, '01'=>Number......]
	public $search_number;        // 总搜索数 Number
	public $ip_ratio_pv    = 1; // IP:PV 的比 (可设置)
	public $ip_ratio_click = 2.5; // IP:点击比
	public $hs_search_rate = 50;  // 搜索IP/总IP数:的 比例预置 (可设置)
	public $hs_hao_bd_rate = 55;  // 产生的搜索数中 h123占比预置 (可设置)
	public $hao_pvs        = 0;   // h123 的PV数
	public $nhour_spv      = 0;   // 当前小时需要搜索数
	public $page_staytime  = 220; // 页面平均停留时间
	// 各子操作用时
	public $extime = [
				'time_c'  => [38,57], // Click
				'time_hs' => [39,68], // H123.search
				'time_bs' => [49,75]  // Bd.search
			];
	// -- 	
    public function __construct($search_number, $time_zone, $n_hour = 0, $config = [])
    {
        if( empty($config) || !is_array($config) || count($config) == 0 ){
            $config = require(__DIR__ . '/../config/api_config.inc.php');
        }

        $this->config = $config;
        $this->haoPageArea = $this->config['hao123_page_area'];
        $this->topSites = $this->config['top_sites'];
        $this->hotSites = $this->config['hot_sites'];
		
		// ++
		if( !is_array($time_zone) && strpos($time_zone,'}') > 0 ) $time_zone = json_decode($time_zone, true);
		if( !is_array($time_zone) ) $time_zone = [];
		$this->search_number = $search_number;
		$this->time_zone     = $time_zone;
		$this->now_hour      = empty($n_hour)?date("H"):$n_hour;
		// 当前小时应搜索数
		if( !empty($time_zone) && array_key_exists($this->now_hour, $time_zone) ){
			$this->nhour_spv     = $this->hour_search_spv_number();
		}
    }
	
    /**
     * 获取一个或全部批次搜索需要达到的指标数据
     * @param array $searchOption
     * @param int $batchNum
     * @param string $site
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function getBatchSearch(array $searchOption, $batchNum = -1, $site = '', $type = ''){
        $site = strtolower($site);
        $type = $this->defaultBatchType($type);
        if ($batchNum > 0) $batchNum -= 1;
        $batchSearch = [];
        $result = [];

        try {
           if ($type == 'fixed') {
               $batchSearch = $this->calcBatchSearchFixed($searchOption['total_search'], $searchOption['total_ip'], $searchOption['ip_search_rate']);
           } else {
               $batchSearch = $this->calcBatchSearchFloat($searchOption['total_search'], $searchOption['total_ip'], $searchOption['ip_search_rate']);
           }
        } catch (\Exception $e){
           throw $e;
        }

        if ($batchNum == -1){
            return $batchSearch;
        } else {
           if ($site == 'hao123' || $site == 'baidu') {
               $result = [$site => $batchSearch[$site]['batch'][$batchNum]];
           } else {
               $result = [
                   'hao123' => $batchSearch['hao123']['batch'][$batchNum],
                   'baidu' => $batchSearch['baidu']['batch'][$batchNum],
               ];
           }
        }

        return $result;
    }

    /**
     * 获取批次类型
     * @param $type
     * @return mixed|string
     */
    public function defaultBatchType($type = ''){
        $type = strtolower($type);
        if (empty($type)) $type = str_replace('search_', '', $this->config['search_batch.default']);
        if (!in_array($type, ['fixed', 'float'])) $type = 'fixed';
        return $type;
    }

    /**
     * 预处理基础搜索数据
     * @param $type
     * @param $totalSearch
     * @param $totalIp
     * @param float $ipSearchRate
     * @return array
     */
    public function prepareSearchData($type, $totalSearch, $totalIp, $ipSearchRate = 0.5){
        if (!in_array($type, $this->allowedType)) {
            throw new \InvalidArgumentException('搜索比例类型不正确');
        }

        $totalSearch = intval($totalSearch);
        $totalIp = intval($totalIp);
        $ipSearchRate = floatval($ipSearchRate);
        $batchSearch = [];
        if ($totalSearch <= 0 || $totalIp <= 0 || $ipSearchRate <= 0){
            throw new \InvalidArgumentException('参数不能小于等于0');
        }

        $batchSearch['hao123']['avalable_ip'] = round($totalIp * $ipSearchRate);
        $batchSearch['hao123']['total_search'] = round($totalSearch * $this->config[$type]['site']['hao123']);

        $batchSearch['baidu']['avalable_ip'] = round($totalIp * $ipSearchRate);
        $batchSearch['baidu']['total_search'] = round($totalSearch * $this->config[$type]['site']['baidu']);

        return $batchSearch;
    }

    /**
     * 计算各站点各批次理论上应该搜索的IP执行数和搜索次数，固定比例
     * @param $totalSearch
     * @param $totalIp
     * @param float $ipSearchRate
     * @return array
     * @throws Exception
     */
    public function calcBatchSearchFixed($totalSearch, $totalIp, $ipSearchRate = 0.5){
        try {
            $batchSearch = $this->prepareSearchData('search_fixed', $totalSearch, $totalIp, $ipSearchRate);
        } catch (Exception $e){
            throw $e;
        }

        $batchSearch['hao123']['batch'][0]['ip'] = round($batchSearch['hao123']['avalable_ip'] * $this->getConfig()['search_fixed']['batch'][1]);
        if ($batchSearch['hao123']['batch'][0]['ip'] == 0){
            throw new \InvalidArgumentException('hao123 IP数不能为0');
        }

        $batchSearch['hao123']['batch'][0]['ip_sum'] = $batchSearch['hao123']['batch'][0]['ip'];
        $batchSearch['hao123']['batch'][0]['search'] = round($batchSearch['hao123']['batch'][0]['ip']);
        $batchSearch['hao123']['batch'][0]['search_sum'] = $batchSearch['hao123']['batch'][0]['search'];
        $batchSearch['hao123']['batch'][0]['keywords'] = round($batchSearch['hao123']['batch'][0]['search'] / $batchSearch['hao123']['batch'][0]['ip'], 2);

        $batchSearch['baidu']['batch'][0]['ip'] = round($batchSearch['baidu']['avalable_ip'] * $this->config['search_fixed']['batch'][1]);
        $batchSearch['baidu']['batch'][0]['search'] = round($batchSearch['baidu']['total_search'] * $this->config['search_fixed']['batch'][1]);
        $batchSearch['baidu']['batch'][0]['search_sum'] = $batchSearch['baidu']['batch'][0]['search'];
        if ($batchSearch['baidu']['batch'][0]['ip'] == 0){
            throw new \InvalidArgumentException('baidu IP数不能为0');
        }

        $batchSearch['baidu']['batch'][0]['ip_sum'] = $batchSearch['baidu']['batch'][0]['ip'];
        $batchSearch['baidu']['batch'][0]['keywords'] = round($batchSearch['baidu']['batch'][0]['search'] / $batchSearch['baidu']['batch'][0]['ip'], 2);

        $otherHao123Search = $batchSearch['hao123']['total_search'] - $batchSearch['hao123']['batch'][0]['search'];
        $otherBaiduSearch = $batchSearch['baidu']['total_search'] - $batchSearch['baidu']['batch'][0]['search'];

        $batchConfig = array_slice($this->config['search_fixed']['batch'], 1, count($this->config['search_fixed']['batch']) - 1);
        $sumBatch = array_sum($batchConfig);

        foreach ($batchConfig as $key => $batch) {
            $key++;
            $batchSearch['hao123']['batch'][$key]['ip'] = round($batchSearch['hao123']['avalable_ip'] * $batch, 2);
            $batchSearch['hao123']['batch'][$key]['ip_sum'] = array_sum(array_column($batchSearch['hao123']['batch'], 'ip'));
            $batchSearch['hao123']['batch'][$key]['search'] = round($otherHao123Search * $batch / $sumBatch, 2);
            $batchSearch['hao123']['batch'][$key]['search_sum'] = array_sum(array_column($batchSearch['hao123']['batch'], 'search'));
            $batchSearch['hao123']['batch'][$key]['keywords'] = round($batchSearch['hao123']['batch'][$key]['search'] / $batchSearch['hao123']['batch'][$key]['ip'], 2);

            $batchSearch['baidu']['batch'][$key]['ip'] = round($batchSearch['baidu']['avalable_ip'] * $batch, 2);
            $batchSearch['baidu']['batch'][$key]['ip_sum'] = array_sum(array_column($batchSearch['baidu']['batch'], 'ip'));
            $batchSearch['baidu']['batch'][$key]['search'] = round($otherBaiduSearch * $batch / $sumBatch, 2);
            $batchSearch['baidu']['batch'][$key]['search_sum'] = array_sum(array_column($batchSearch['baidu']['batch'], 'search'));
            $batchSearch['baidu']['batch'][$key]['keywords'] = round($batchSearch['baidu']['batch'][$key]['search'] / $batchSearch['baidu']['batch'][$key]['ip'], 2);
        }

        return $batchSearch;
    }

    /**
     * 计算各站点各批次理论上应该搜索的IP执行数和搜索次数，浮动比例
     * @param $totalSearch
     * @param $totalIp
     * @param float $ipSearchRate
     * @return array
     * @throws Exception
     */
    public function calcBatchSearchFloat($totalSearch, $totalIp, $ipSearchRate = 0.5){
        try {
            $batchSearch = $this->prepareSearchData('search_float', $totalSearch, $totalIp, $ipSearchRate);
        } catch (Exception $e){
            throw $e;
        }

        if (!$this->config['search_float']['batch_random']['enable']) {
            $batchSearch['hao123']['batch'][0]['ip'] = round($batchSearch['hao123']['avalable_ip'] * $this->config['search_float']['batch'][1]);
            if ($batchSearch['hao123']['batch'][0]['ip'] == 0){
                throw new \InvalidArgumentException('hao123 IP数不能为0');
            }

            $batchSearch['hao123']['batch'][0]['ip_sum'] = $batchSearch['hao123']['batch'][0]['ip'];
            $batchSearch['hao123']['batch'][0]['search'] = round($batchSearch['hao123']['batch'][0]['ip']);
            $batchSearch['hao123']['batch'][0]['search_sum'] = $batchSearch['hao123']['batch'][0]['search'];
            $batchSearch['hao123']['batch'][0]['keywords'] = round($batchSearch['hao123']['batch'][0]['search'] / $batchSearch['hao123']['batch'][0]['ip'], 2);

            $batchSearch['baidu']['batch'][0]['ip'] = round($batchSearch['baidu']['avalable_ip'] * $this->config['search_float']['batch'][1]);
            if ($batchSearch['baidu']['batch'][0]['ip'] == 0){
                throw new \InvalidArgumentException('baidu IP数不能为0');
            }

            $batchSearch['baidu']['batch'][0]['ip_sum'] = $batchSearch['baidu']['batch'][0]['ip'];
            $batchSearch['baidu']['batch'][0]['search'] = round($batchSearch['baidu']['total_search'] * $this->config['search_float']['batch'][1]);
            $batchSearch['baidu']['batch'][0]['search_sum'] = $batchSearch['baidu']['batch'][0]['search'];
            $batchSearch['baidu']['batch'][0]['keywords'] = round($batchSearch['baidu']['batch'][0]['search'] / $batchSearch['baidu']['batch'][0]['ip'], 2);

            $otherBaiduSearch = $batchSearch['baidu']['total_search'] - $batchSearch['baidu']['batch'][0]['search'];
        } else {
            $otherBaiduSearch = $batchSearch['baidu']['total_search'];
        }

        $batchConfig = [];

        if ($this->config['search_float']['batch_random']['enable']) {
            $rndMin = floatval($this->config['search_float']['batch_random']['min']);
            $rndMax = floatval($this->config['search_float']['batch_random']['max']);
            $batchConfig = self::randomBatchRatios($rndMin, $rndMax);
        } else {
            $batchConfig = array_slice($this->config['search_float']['batch'], 1, count($this->config['search_float']['batch']) - 1);
        }

        $sumBatch = array_sum($batchConfig);

        foreach ($batchConfig as $key => $batch) {
            if (!$this->config['search_float']['batch_random']['enable']) {
                $key++;
            }
            
            $batKey = $key + 1;
            $batchSearch['hao123']['batch'][$key]['ip'] = round($batchSearch['hao123']['avalable_ip'] * $batch, 2);
            $batchSearch['hao123']['batch'][$key]['ip_sum'] = array_sum(array_column($batchSearch['hao123']['batch'], 'ip'));
            $batchSearch['hao123']['batch'][$key]['search'] = round($batchSearch['hao123']['batch'][$key]['ip'] * $batKey, 2);
            $batchSearch['hao123']['batch'][$key]['search_sum'] = array_sum(array_column($batchSearch['hao123']['batch'], 'search'));
            $batchSearch['hao123']['batch'][$key]['keywords'] = round($batchSearch['hao123']['batch'][$key]['search'] / $batchSearch['hao123']['batch'][$key]['ip'], 2);

            $batchSearch['baidu']['batch'][$key]['ip'] = round($batchSearch['baidu']['avalable_ip'] * $batch, 2);
            $batchSearch['baidu']['batch'][$key]['ip_sum'] = array_sum(array_column($batchSearch['baidu']['batch'], 'ip'));
            $batchSearch['baidu']['batch'][$key]['search'] = round($otherBaiduSearch * $batch / $sumBatch, 2);
            $batchSearch['baidu']['batch'][$key]['search_sum'] = array_sum(array_column($batchSearch['baidu']['batch'], 'search'));
            $batchSearch['baidu']['batch'][$key]['keywords'] = round($batchSearch['baidu']['batch'][$key]['search'] / $batchSearch['hao123']['batch'][$key]['ip'], 2);
        }

        return $batchSearch;
    }

    public function getConfig(){
        return $this->config;
    }

    /**
     * 获取当前所在批次
     * @param $totalIp
     * @param $type
     * @param $searchOption
     * @param array $batchSearch
     * @return array|mixed
     */
    public function currentSearchBatch($totalIp, $type, $searchOption, array $batchSearch = []){
        $totalIp = intval($totalIp);
        try {
            $batchSearch = !empty($batchSearch) ? $batchSearch : $this->getBatchSearch($searchOption);
        } catch (Exception $e){
            throw new \RuntimeException($e->getMessage());
        }

        if (empty($batchSearch[$type]['batch'])){
            return [];
        }

        foreach ($batchSearch[$type]['batch'] as $key => $search){
            if ($totalIp < $search['ip_sum']){
                $search['batch_index'] = $key;
                return $search;
            }
        }

        return end($batchSearch);
    }

    /**
     * 随机分配批次及批次比例
     * @param float $min 随机最小值
     * @param float $max 随机最大值
     * @param int $percent
     * @return array|bool
     */
    private static function randomBatchRatios($min, $max, $percent = 100){
         $min *= $percent;
         $max *= $percent;
         if ($min >= $percent || $max >= $percent) return false;
         if ($min >= $max) return false;

         $i = 0;
         $rndNums = [];
         $rndRatios = [];
         for ($i = 0; $i < $max; $i++) {
             $rndNum = mt_rand($min, $max);
             if (array_search($rndNum, $rndNums) === false){
                 $rndNums[] = $rndNum;
                 $rndRatios[] = round($rndNum / $percent, 2);
             }

             if (array_sum($rndNums) > $percent) {
                 break;
             }
         }

         if (array_sum($rndNums) != $percent) {
             unset($rndRatios[array_search(max($rndRatios), $rndRatios)]);
             $rndRatios[] = 1 - array_sum($rndRatios);
         }

         $rndRatios = array_values(array_filter($rndRatios));
         shuffle($rndRatios);

         return $rndRatios;
    }
    // 抽中点击返回
    public function clickData(){
        $arr = array();
        foreach ($this->haoPageArea as $key => $val) {
            $arr[$val['id']] = $val['v']*100;
        }
        $rid = $this->getRand($arr);
        $res = $this->haoPageArea[$rid-1];
		// OpenUrl
		if(  in_array($res['value'], array('hot_','top_') )  ){
			$_href_value  = $res['value']=='hot_'?$this->hotSites[array_rand($this->hotSites)]:$this->topSites[array_rand($this->topSites)];
			$res['burl']  = $_href_value;
			if( date('ymd')>190604 ) $res['value'] = $_href_value;
		}
		else $res['burl'] = $this->hotSites[array_rand($this->hotSites)];
        return $res;
    }
	
	// 热点点击返回
    public function clickDataHot(){
        $res = $this->haoPageArea[27];
		// OpenUrl
		if(  in_array($res['value'], array('hot_','top_') )  ){
			$_href_value  = $res['value']=='hot_'?$this->hotSites[array_rand($this->hotSites)]:$this->topSites[array_rand($this->topSites)];
			$res['burl']  = $_href_value;
			if( date('ymd')>190604 ) $res['value'] = $_href_value;
		}
		else $res['burl'] = $this->hotSites[array_rand($this->hotSites)];
        return $res;
    }
	
	/**
	 * --extend
	 * 
	 * 比例区间子函数
	 * 起始值 $a,
	 * 结束值 $b,
	 *
	 **/
	private function ab_mumber($a, $b){
		$response = array(
			'sum'    => 0,
			'number' => []
		);
		// echo "{$a}-{$b};<br/>";
		$li = 0;
		for($i=$a;$i<=$b;$i++){
			$li ++;
			$response['number'][] = $li;
		}		
		$response['sum']    = array_sum( $response['number'] );
		$response['number'] = count(     $response['number'] );
		return $response;
	}
	/*
	 * 自动生成比例区间的值
	 * -- IN
	   $number_avg 平均值
	   $number_min 起始值
	   $number_max 最大值
	   $c_avg_ratio 平均值占比 Number Default:40
	   $plan Number Default:5 平均值前后偏移值(<5?前<后:前>后)
	 * -- OUT
	 	Array {生成搜索数N: 占比,...}
	 **/
	public function number_limit( float $number_avg, int $number_min, float $number_max, int $c_avg_ratio = 0, float $plan = 5 ){
		// 1  0-1
		$response   = array();
		$_avg_ratio = 40;
		if( intval($c_avg_ratio) > 0 ) $_avg_ratio  = $c_avg_ratio;
		if( $number_avg == $number_max || $number_min > $number_max ) $number_min = floor($number_avg);
		if( is_float($number_avg) && strpos($number_avg,'.') )  $number_avgs = [ floor($number_avg), ceil($number_avg) ];
		else $number_avgs = [ $number_avg ];
		if( in_array($number_avgs[0],[0,1]) ){
			$_avg_ratio = 100;
			$number_max = $number_avgs[0]+1; // 2
		}		
		$_number_remaining = (100-$_avg_ratio)*($plan/10);
		if( count($number_avgs) == 1 ){
			$response[ $number_avgs[0] ] = $_avg_ratio;
		}else{
			$response[ $number_avgs[0] ] = $_avg_ratio*(1-($number_avg-$number_avgs[0]));
			$response[ $number_avgs[1] ] = $_avg_ratio*(   $number_avg-$number_avgs[0] );
		}
		if( $number_avgs[0]==$number_min && $number_avgs[1]==$number_max ) return $response;
		
		$_min_ab = $this->ab_mumber( $number_min, $number_avgs[0]-1 );
		$_ab_sum = 100/$_min_ab['sum'];
		$li      = $_min_ab['number'];
		$rk      = $number_avgs[0]-1;
		while( $li >= 1 ){
			$response[$rk] = $_ab_sum*$li/100*$_number_remaining;	
			$rk--;
			$li--;
		}		
		// -- 
		$_number_remaining       =  (100-$_avg_ratio)*(1-$plan/10);
		if( count($number_avgs) == 2 ) $_ab_start = $number_avgs[1]+1;
		else  $_ab_start = $number_avgs[0]+1;
		
		$_min_ab = $this->ab_mumber($_ab_start, $number_max);
		$_ab_sum = 100/$_min_ab['sum'];
		$li      = $_min_ab['number'];
		while( $_ab_start <= $number_max ){
			$response[ $_ab_start ] = $_ab_sum*$li/100*$_number_remaining;
			$_ab_start++;
			$li--;
		}
		ksort($response);
		return $response;
	}
	/**
	 * 生成停留时间
	 * -- In 
	 *	
	 *	$max_rtime 最大停留时间 Number s
	 * -- Out 
	 *  Array[ stime1,=>Number, stime1,=>Number ]
	 */
	public function get_page_stay_time( $max_rtime=0 ){
		$response        = [];
		$_units_split    = 20; // 区间递增值
		$_avg_proportion = 35; // 平均值优先占比
		$_us_time        = 1;
		if( empty($max_rtime) ) $max_rtime    = $this->page_staytime * 2 + $_units_split;
		$_split_arr   = [];
		while( $_us_time < $max_rtime ){
			$_tmp_rtime   = $_us_time + $_units_split;
			$_split_arr[] = [$_us_time+1, $_tmp_rtime];
			$_us_time     = $_tmp_rtime;
		}
		// reset  $avg, $max
		$avg_index    = ceil( $this->page_staytime/$_units_split );
		$max_index    = count( $_split_arr );
		if( $_GET['test'] == 1 ) error_log(__FUNCTION__, 0);
		$_ratio_sk_pvs  = $this->number_limit( $avg_index, 0, $max_index, $_avg_proportion );
		//print_r($_split_arr);exit();
		//-print_r($_ratio_sk_pvs);exit();
		// get rate
		for( $i=0; $i<$this->hao_pvs; $i++ ){
			$response[]     = $_split_arr[$this->getRand( $_ratio_sk_pvs )];
		}
		return $response;
		// foreach($_split_arr as $sk => $sv ) echo "{$sk}\t{$sv[0]}-{$sv[1]}:\t{$_ratio_sk_pvs[$sk]}\n";
	}
	/**
	 * 根据 ip:点击 比 需要的点击次数 序列
	 * -- IN
	 * # -- OUT: Array
	 * [
	 * 	0: 第1个PV的点击数
	 *	1: 第2个PV的点击数...
	 * ]
	 */
	function get_click_number_split( $pv_stay_time ){
		$response   = [];
		$_avg_pv_click    = $this->ip_ratio_click/$this->ip_ratio_pv;
		$_ratio_ab_click  = $this->number_limit( $_avg_pv_click, 0, $_avg_pv_click*2, 50, 5 );
		foreach( $pv_stay_time as $pv_sttime ){
			$response[]  = $this->getRand($_ratio_ab_click);
		}// print_r($response);exit;
		return $response;
	}
	/**
	 * 根据PV生成 搜索次数 序列
	 * -- IN
	 * $pv_stay_time  Array[ pv1:PV区间范围, pv2:PV区间范围 ]
	 * # -- OUT
	 * 本次接口调用可的次数 Key:偶数为H123搜索, 否则为百度搜索
	 * [
	 *		0: Hao123搜索数
	 *		1: baidu 搜索数
	 * ]
	 */
	function get_search_number_split( $pv_stay_time, $max_limit=0, $min_number=1, $max_number=0 ){
		$response = [ 0,0 ];
		// 单个PV最大搜索次数控制
		// 当前 搜索:IP(PV)比
		$_ratio_of_snum_avg = $this->nhour_spv / ($this->time_zone[$this->now_hour] * $this->hs_search_rate / 100);
		// -- set Max Number
		if( $max_number <= $_ratio_of_snum_avg ) $_ratio_of_snum_max = ceil($_ratio_of_snum_avg*2);
		else $_ratio_of_snum_max = $max_number;
		// Null
		if( $_ratio_of_snum_max == 0 ) return $response;
		// 搜索次数比例
		if( $_GET['test'] == 1 ) error_log(__FUNCTION__, 0);
		$_ratio_ab_spv   = $this->number_limit( $_ratio_of_snum_avg, $min_number, $_ratio_of_snum_max );
		// $_ratio_key_spv = $this->getRand( $_ratio_ab_spv );
		// set snumber
		$_ratio_key_spv = 0;
		foreach( $pv_stay_time as $pv_sttime ){
			$_ratio_key_spv += round( $pv_sttime[0] / $this->extime['time_hs'][1] );
		}
		// limit 
		// print_r($_ratio_ab_spv);
		//-- echo ($_ratio_key_spv-$max_limit)." | max_limit:{$max_limit} < _tatio_key:{$_ratio_key_spv} < {$this->nhour_spv} => ";		
		if( $max_limit < 1  ) return $response;
		elseif( $_ratio_key_spv-$max_limit > 0 ) $_ratio_key_spv = $max_limit;
		
		if( $this->hao_pvs == 0 ) $this->set_pvs();
		
		return $this->ratio_hsn_bsn($_ratio_key_spv, $this->hao_pvs);	
	}
	/**
	 * 搜索次数 比例 分隔
	 * -- IN
	   $max_limit  最大可生成的搜索数(剩余可搜索数),生成搜索数大于此值时意味着将会超过预设搜索，应该使用此值代替生成的搜索数
	   $min_number 最少搜索次数起始值
	   $max_number 最大搜索次数上限控制
	 # -- OUT
	   本次接口调用可的次数 Key:偶数为H123搜索,否则为百度搜索
	   Array[
			0: Hao123搜索数
			1: baidu 搜索数
		}
	 */
	function search_number_split( $max_limit=0, $min_number = 1, $max_number = 0 ){ //$avg_snum, $max_snum, $min_number = 1 ){
		$response = [0,0];
		// 单个PV最大搜索次数控制
		// 当前 搜索:IP(PV)比
		$_ratio_of_snum_avg = $this->nhour_spv/($this->time_zone[$this->now_hour]*$this->hs_search_rate/100);
		// -- set Max Number
		if( $max_number    <= $_ratio_of_snum_avg ) $_ratio_of_snum_max = ceil($_ratio_of_snum_avg*2);
		else $_ratio_of_snum_max = $max_number;
		// Null
		if( $_ratio_of_snum_max == 0 ) return 0;
		// 搜索次数比例
		$debug = 0;
		$_ratio_ab_spv  = $this->number_limit( $_ratio_of_snum_avg, $min_number, $_ratio_of_snum_max );
		$_ratio_key_spv = $this->getRand( $_ratio_ab_spv );
		if( $max_limit < 1  ) return $response;
		elseif( $_ratio_key_spv-$max_limit > 0 ) $_ratio_key_spv = $max_limit;
		if( $debug == 1 ){
			print_r($_ratio_ab_spv); echo " => max_limit:{$max_limit}, _ratio_key_spv:{$_ratio_key_spv} <br/>";
		}
		// set PV
		if( $this->hao_pvs == 0 ) $this->set_pvs();
		return $this->ratio_hsn_bsn($_ratio_key_spv, $this->hao_pvs);
	}
	/**
	 * 生成1个PV数
	 * -- IN
	 * -- OUT
	 	Number 当前IP需要执行的PV数
	 */
	public function set_pvs( $pvs = 0 ){
		// PV 比例控制
		if( $pvs > 0 ){
			$this->hao_pvs  = $pvs;
		}else{
			// 考虑到执行失败的情况 均值增加0.3
			$_ratio_ab_pvs  = $this->number_limit( $this->ip_ratio_pv, 1, $this->ip_ratio_pv*2 );
			$_tatio_key_pvs = $this->getRand( $_ratio_ab_pvs );
			// print_r($_ratio_ab_pvs); print_r($_tatio_key_pvs); echo "]<br/>\n";
			$this->hao_pvs  = $_tatio_key_pvs;		
		}
		return $this->hao_pvs;
	}
	/**
	 * hao123 搜索数, 百度搜索次数分离
	 * -- IN  
	   $avg_search_number
	 * -- OUT
	   Array {
	   		0: hao123 search number （奇数H123搜索）
			1: baidu search number （偶数BD搜索）
			...
	   }
	 * 
	 */
	public function ratio_hsn_bsn( $s_number, $_hb_pvs ){
		$response = array();
		$_arr_hbr = [
			$this->hs_hao_bd_rate,
			100-$this->hs_hao_bd_rate
		];
		if( is_float($s_number) ) $s_number = ceil($s_number);
		$_key_hbr = $this->getRand( $_arr_hbr );
		$_pv_s_avg = ceil($s_number/$_hb_pvs);
		for( $i=0; $i<$_hb_pvs; $i++ ){
			if( $_pv_s_avg >= $s_number ) $_pv_s_avg = $s_number;
			$s_number  -= $_pv_s_avg;
			$_tmp_hsn   = round($_arr_hbr[$_key_hbr]/100*$_pv_s_avg);
			$response[] = $_tmp_hsn;
			$response[] = $_pv_s_avg-$_tmp_hsn;
		}
		return $response;
	}
	/**
	 * 当前IP是否进行搜索
	 */
	public function ipv_hit( ) {
		$_ipvs_ratio = [
			0 => 100-$this->hs_search_rate,
			1 => $this->hs_search_rate
		];
		return $this->getRand( $_ipvs_ratio );	// return substr(microtime(true),-1)%2;
	}
	/**
	 * 比例的生成
	 */
	private function getRand($proArr) {
        $result = array_rand($proArr);
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result   = $key;
                break;
            } else {
                $proSum  -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
	/**
	 * 获取当前小时需要发布的搜索数
	 * 1 $_hour_list 小时曲线
	 * 2 $search_number 总搜索数
	 */
	function hour_search_spv_number( $hour = 0  ){
		if( empty($hour) ) $hour = $this->now_hour;
		$response   = array();
		$_hour_num  = array_sum($this->time_zone);
		
		foreach($this->time_zone as $h=>$value){
			$response[$h] = $value/$_hour_num*$this->search_number;
		}
		return $response[$hour];
	}
	/**
	 * 生成搜索列表 
	 * ——IN 0:第1个pv的H123搜索数，1:第1个pv的BD搜索数,3:第2个pv的H123搜索数，4:第2个pv的BD搜索数
	 	{}
	 * 
	 */
	function get_search_tags_list( $_hb_sn ){
		/*
		-- IN
		array(
		 0: Number,
		 1: Number,
		 2: Number,
		 ...
		)
		-- OUT Array
		[
			'p'  => [],
			'sn' => [
				'hwn' => $hpv,
				'bwn' => $kn
			]
		]
		<p3 ss="1" config="hao123_kw1" keyword="baidu_kw1, baidu_kw2, baidu_kw3" keywordsize="3">
        <p4 ss="1" config="hao123_kw2" keyword="baidu_kw4, baidu_kw5" keywordsize="2">
		*/
		$response = array();
		$_sum_sn  = array_sum($_hb_sn);
		
		if( $_sum_sn   == 0 ) return '';
		$_keyword_list  = file_get_contents($this->config['keyword_api']['url'].$_sum_sn);
		//echo "[".$this->config['keyword_api']['url'].$_sum_sn."]";
		if( strpos($_keyword_list,']') ) $_keyword_list = json_decode($_keyword_list);
		else $_keyword_list = [];
		if( $_GET['test']  == 1 ){
			echo "\n_hb_sn:\n";
			print_r($_hb_sn);
			echo "\n_keyword_list:\n";
			print_r($_keyword_list);
		}
		// keyword index		
		$_keyword_index  = 0;
		// -- get H123 pv stay time
		// --PV Info
		foreach($_hb_sn as $ki=>$kn){
			$pv = ceil(($ki+1)/2);
			if( $ki%2 != 0 ){
				
				$hpv = $_hb_sn[$ki-1];
				// echo "{$ki}: hws:{$hpv} - bws:{$kn} ]\n";
				// 分离SPV中的 H123词和 BD词 && reset
				$_tmp_hb_key = [ 'hwn'=>[], 'bwn'=>[] ];
				// $_arr = '<p3 ss="1" config="hao123_kw1" keyword="baidu_kw1, baidu_kw2, baidu_kw3" keywordsize="3">';
				if( $hpv == 0 && $kn == 0 )  continue;
				$_tmp_hb_key['hwn']         = array_slice($_keyword_list, $_keyword_index, 1);
				$_keyword_index++;
				// set hwn
				if( $hpv > 1 ){
					$pi  = 2;
					while( $pi <= $hpv ){
						$_tmp_hb_key['hwn'] = array_merge($_tmp_hb_key['hwn'] , array_slice($_keyword_list, $_keyword_index, 1) );
						$_keyword_index  += 1;
						$pi++;
					}
				}
				// set bwn	
				if( 0 == $hpv ){
					$hpv = 1;
					$kn -= 1;
				}
				if( $kn > 0 ){
					$_tmp_hb_key['bwn'] = array_slice($_keyword_list, $_keyword_index, $kn);
					$_keyword_index      += $kn;
				}
				// ++
				$_len_hwn = count($_tmp_hb_key['hwn'])-1;
				$_key_out = floor($ki/2);
				
				// -- control
				if( !isset($response[$_key_out]) ) $response[$_key_out] = [
					'p'  => [],
					'sn' => [ 'hwn' => $hpv, 'bwn'=>$kn ]
				];
				
				foreach($_tmp_hb_key['hwn'] as $_khw=>$_vhw){
					$_vbw = [];
					if( $_khw == $_len_hwn ) $_vbw = $_tmp_hb_key['bwn'];
					$response[$_key_out]['p'][] = "\r\n<p[i] ss=\"1\" config=\"".$_vhw."\" keyword=\"".implode("|",$_vbw)."\" keywordsize=\"".count($_vbw)."\" />";
				}
			}
		}
		if( $_GET['test'] == 1 ){
			echo "response:";
			print_r($response);
		}
		return $response;
	}
	/**
	 * 生成本次 IP 需要的PV数
	 * 1 $_hour_list 小时曲线
	 * 2 $search_number 总搜索数
	 
	function hour_hao_search_ippv( ){
		// echo "IP:{$this->time_zone[$hour]}, SPV:{$response[$hour]}, 本次PV：[{$_tatio_key}];\n";
		// print_r();
		return $response[$hour];
	}*/
}
