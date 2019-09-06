<?php
/**
 * 搜索业务控制模块
 */

require_once __DIR__ . '/../lib/Algorithm.php';
require_once __DIR__ . '/../lib/curl_file_get_contents.lib.php';


class Module_Search
{
    private $ip = '';

    private $config = [];

    private $algorithm;

    private $search_entry = [ 'hao123', 'baidu' ];

    private $batch_search = [];

    private $task_size = 0;

    private $current_batch = [];

    private $search_option = [];

    public function __construct($ip, array $config, Algorithm $algorithm)
    {
        $this->ip = $ip;
        $this->config = $config;
        $this->algorithm = $algorithm;
    }

    /**
     * 解析任务参数为内部统一参数
     * @param $task_id
     * @return array|bool
     */
    private function parse_option($task_id)
    {
        $task_info = redis_url('hget', 'mn_task_info', 'area_' . $task_id);
        $task_info = json_decode($task_info, true);
        if (empty($task_info) || !$task_info['is_hao']) return false;

        $task_info['hao_sn'] = intval($task_info['hao_sn']);
        $task_info['hao_ip_total'] = intval($task_info['hao_ip_total']);
        $task_info['hao_search_rate'] = intval($task_info['hao_search_rate']);

        if ($task_info['hao_sn'] <= 0 || $task_info['hao_ip_total'] <= 0 || $task_info['hao_search_rate'] <= 0) {
            return false;
        }

        // 后台设置的搜索参数
        $this->search_option = [
            'total_search' => $task_info['hao_sn'],
            'total_ip' => $task_info['hao_ip_total'],
            'ip_search_rate' => $task_info['hao_search_rate'] / 100,
        ];

        return $this->search_option;
    }

    /**
     * 根据后台设置和配置文件来分配任务关键词和生成xml
     * @param $task_id
     * @param $xml_root
     * @return array|bool
     */
    public function generate_xml($task_id, $xml_root)
    {
        if (empty($this->search_option)) {
            $this->search_option = $this->parse_option($task_id);
        }

        if (!$this->search_option) {
            return false;
        }

        if ($this->task_completed($task_id, $this->search_option)) {
            return true;
        }

        if (self::hourly_task_done($task_id)){
            return true;
        }

        if (self::client_task_executed($task_id, $this->ip)) {
            return true;
        }

        $kw_list = $this->catch_remote_keywords($task_id, $this->search_option);
        if (empty($kw_list)) {
            return false;
        }

        $this->task_size = ceil($this->current_batch['hao123']['keywords']);
        if ($this->task_size < 1) $this->task_size = 1;

        $xml_root = str_replace('</t>', '', $xml_root);
        $task_xmls = $this->task_multiple_xml($task_id, $this->task_size, $xml_root, $kw_list);

        return $task_xmls;
    }

    /**
     * 将一个任务分解成N个独立的XML结构
     * @param $task_id
     * @param $task_size
     * @param $xml_str
     * @param array $kw_list
     * @return array
     */
    private function task_multiple_xml($task_id, $task_size, $xml_str, array $kw_list)
    {
        $task_num = 0;
        $bd_task_size = 0;
        $kw_other_count = 0;
        $count_per_group = 0;
        $bd_kw_count = 0;
        $hao_kw_count = 0;
        $kw_other_str = '';
        $kw_other_size = 0;
        $kw_search = [];
        $tags = [];
        $task_list = [];
        $group_list = [];
        $prop_tag = [];
        $task_group_num = 1;
        $group_pv_ip = false;

        if (isset($this->config['group_pv_ip']) && $this->config['group_pv_ip']) {
            $group_pv_ip = true;
            $pv_ip_ratio = self::pv_ip_ratio($task_id);
            if ($pv_ip_ratio == 0) {
                $pv_ip_ratio = floatval($this->config['pv_ip_ratio']);
            }

            $task_group_num = ceil($this->current_batch['hao123']['keywords'] / $pv_ip_ratio);
            if ($task_group_num < $this->config['pv_ip_ratio']) {
                $task_group_num = rand(floor($pv_ip_ratio), ceil($pv_ip_ratio));
            }
        } else {
            $task_group_num = ceil($this->current_batch['hao123']['keywords']);
        }

        $redis_bd_count = intval(redis_url('hget', self::task_key_format($task_id) . 'baidu', 'kw_count'));

        if ($redis_bd_count < $this->current_batch['baidu']['search_sum']) {
            $bd_task_size = rand(floor($this->current_batch['baidu']['keywords']), ceil($this->current_batch['baidu']['keywords']));
            $count_per_group = $bd_task_size / $this->task_size;
            $count_per_group = rand(floor($count_per_group), ceil($count_per_group));
        }

        for ($i = 0; $i < $task_size; $i++) {
            $multi_flag = $i && ($i % $task_group_num == 0);
            $primary_item = array_shift($kw_list);
            if (empty($primary_item)) break;
            $kw_search[$i]['primary'] = $primary_item;

            if ($bd_task_size > 0) {
                if ($count_per_group > 1) {
                    for ($j = 0; $j < $count_per_group; $j++) {
                        $other_item = array_shift($kw_list);
                        if (empty($other_item)) break;
                        $kw_search[$i]['other'][] = $other_item;
                    }
                } else {
                    if ($kw_other_count < $bd_task_size) {
                        $kw_search[$i]['other'][] = array_shift($kw_list);
                        $kw_other_count++;
                    }
                }
            }

            if (is_array($kw_search[$i]['other']) && !empty($kw_search[$i]['other'])) {
                $kw_search[$i]['other'] = array_unique(array_filter($kw_search[$i]['other']));
                $kw_other_str = implode('|', $kw_search[$i]['other']);
                $kw_other_size = count($kw_search[$i]['other']);
                $bd_kw_count += $kw_other_size;
            } else {
                $kw_other_str = '';
                $kw_other_size = 0;
            }

            $kw_search[$i] = array_filter($kw_search[$i]);
            if (empty($kw_search[$i])) continue;

            $kw_primary_str = $kw_search[$i]['primary'];
            $prop_str = '<p[i] ss="1" config="' . $kw_primary_str . '" keyword="' . $kw_other_str . '" keywordsize="' . $kw_other_size . '" />';
            $prop_tag[$i] .= $prop_str;
            $group_list[] = $prop_tag[$i];

            if ($multi_flag){
                $tags[$i] .= '</t>' . $xml_str;
                $task_num++;
                $task_list[$task_num] = $group_list;
                unset($group_list);
            }

            $tags[$i] .= $prop_str;
            $hao_kw_count++;
        }

        $xmls_str = '';
        if ($group_pv_ip) {
            $xmls_str = self::replace_xml_value($xml_str) . implode('', $tags) . '</t>';
            $task_pv = count(array_filter(explode('</t>', $xmls_str)));
        } else {
            $task_pv = count($tags);
            foreach ($tags as $tag) {
                $xmls_str .= self::replace_xml_value($xml_str) . $tag . '</t>';
            }
        }

        $task_xmls = [
            'group' => $task_list,
            'xml' => $xmls_str,
            'task_pv' => $task_pv,
            'total_search' => $hao_kw_count + $bd_kw_count,
            'hao_search' => $hao_kw_count,
            'baidu_search' => $bd_kw_count,
        ];

        self::redis_incr_count($task_id, $this->ip, $task_pv, $hao_kw_count, $bd_kw_count);

        return $task_xmls;
    }

    /**
     * 抓取远程API的关键词
     * @param $task_id
     * @param array $search_option
     * @param string $url
     * @return array|bool
     */
    private function catch_remote_keywords($task_id, array $search_option, $url = ''){
        $kw_list = [];
        try {
            $this->current_batch = $this->current_batch($task_id, $search_option);
            $search_num = ceil($this->current_batch['hao123']['keywords'] + $this->current_batch['baidu']['keywords']);
            if ($search_num <= 0) return false;
        } catch (\Exception $e) {
            $log_file = str_replace('entry', 'log', $_SERVER['DOCUMENT_ROOT']) . '/task_error_log/search-' . date('Ymd') . '.log';
            $err_msg = date('Y-m-d H:i:s') . "\ttask_id: {$task_id}, 执行批次搜索任务时出错, {$e->getMessage()}\t" . $_SERVER['REQUEST_URI'] . "\n";
            self::show_test_exception($e, $err_msg);
            error_log($err_msg, 3, $log_file);
            return false;
        }

        if (empty($url)) $url = $this->config['keyword_api']['url'];
        $url .= $search_num;
        $kw_remote = curl_file_get_contents($url);

        if (!empty($kw_remote) && strpos($kw_remote, ']')) {
            $kw_remote = json_decode($kw_remote, true);
        } else {
            return false;
        }

        $kw_remote = array_unique(array_filter($kw_remote));

        foreach ($kw_remote as $val) {
            $val = trim($val);
            if (!empty($val)) {
                $kw_list[] = $val;
            }
        }

        return $kw_list;
    }

    /**
     * 指定的搜索任务是否已完成
     * @param $task_id
     * @param $search_option
     * @return bool
     */
    public function task_completed($task_id, $search_option)
    {
        $key_prefix = self::task_key_format($task_id);
        $task_ip = intval(redis_url('hlen', $key_prefix . 'ip_access'));
        $hao123_kw_count = intval(redis_url('hget', $key_prefix . 'hao123', 'kw_count'));
        $baidu_kw_count = intval(redis_url('hget', $key_prefix . 'baidu', 'kw_count'));
        $task_search = $hao123_kw_count + $baidu_kw_count;
        try {
            $this->search_option = $search_option;
            $batch_search = $this->get_batch_search($task_id);
            $this->batch_search = $batch_search;
            $total_ip = $batch_search['hao123']['avalable_ip'] + $batch_search['baidu']['avalable_ip'];
            $total_search = $batch_search['hao123']['total_search'] + $batch_search['baidu']['total_search'];
//            if ($task_ip >= $total_ip && $hao123_kw_count >= $batch_search['hao123']['total_search'] && $baidu_kw_count >= $batch_search['baidu']['total_search']) {
            if ($task_ip >= $total_ip && $task_search >= $total_search) {
                $total_pv = intval(redis_url('hget', $key_prefix . 'pv', 'total'));
                $pv_ip_ratio = $total_pv / $total_ip;
                $pv_ip_ratio_config = self::pv_ip_ratio($task_id);
                if ($pv_ip_ratio == 0) {
                    $pv_ip_ratio_config = floatval($this->config['pv_ip_ratio']);
                    $pv_ip_ratio_config = $pv_ip_ratio_config ? $pv_ip_ratio_config : 2.5;
                }

                if ($pv_ip_ratio >= $pv_ip_ratio_config) {
                    return true;
                }
            }
        } catch (Exception $e) {
            self::show_test_exception($e);
        }

        return false;
    }

    /**
     * 点击任务是否已完成
     * @param $task_id
     * @return bool
     */
    public function click_task_done($task_id)
    {
        if (empty($this->search_option)) {
            $this->search_option = $this->parse_option($task_id);
        }

        if (!$this->search_option) return false;
        $key_prefix = self::task_key_format($task_id, 'click.');
        $task_ip = intval(redis_url('hlen', $key_prefix . 'ip_access'));
        $task_pv = intval(redis_url('hget', $key_prefix . 'pv', 'total'));
        $target_pv = $this->search_option['total_ip'] * self::pv_ip_ratio($task_id);
        if ($task_ip >= $this->search_option['total_ip'] && $task_pv >= $target_pv) {
            return true;
        }

        return false;
    }

    /**
     * 判断单个客户端是否执行过指定次数的任务
     * @param $task_id
     * @param $client_ip
     * @return bool
     */
    public static function client_task_executed($task_id, $client_ip)
    {
        $key_prefix = self::task_key_format($task_id);
        // single user page visit
        $supv = intval(redis_url('hget', $key_prefix . 'ip_access', $client_ip));
        $exec_num = ceil(self::pv_ip_ratio($task_id));
        if ($supv >= $exec_num) return true;

        return false;
    }

    /**
     * 判断单个客户端是否完成了任务
     * @param $task_id
     * @param $client_ip
     * @param array $search_option
     * @param string $type
     * @return bool|int true: 已完成, -1: hao123已完成baidu未完成 -2: hao123未完成baidu已完成
     */
    public function client_task_done($task_id, $client_ip, array $search_option, $type = 'all')
    {
        $batch_count = count((array)$this->config[$this->config['search_batch.default']]['batch']);
        $key_prefix = self::task_key_format($task_id);
        // single user visit
        $suv = intval(redis_url('hget', $key_prefix . 'ip_access', $client_ip));
        $hao123_kw_count = intval(redis_url('hget', $key_prefix . 'hao123', 'kw_count'));
        $baidu_kw_count = intval(redis_url('hget', $key_prefix . 'baidu', 'kw_count'));
        $batch_search = [];
        $kw_count = 0;
        $target_kw_total = 0;

        try {
            $batch_search = $this->get_batch_search($search_option);
        } catch (Exception $e) {
            self::show_test_exception($e);
        }

        // 单个客户端应该完成的各目标总数
        $hao_target_total = array_sum(array_column((array)$batch_search['hao123']['batch'], 'search'));
        $baidu_target_total = array_sum(array_column((array)$batch_search['baidu']['batch'], 'search'));

        if (in_array($type, $this->search_entry)) {
            if ($type == 'hao123') {
                $kw_count = $hao123_kw_count;
                $target_kw_total = $hao_target_total;
            } else {
                $kw_count = $baidu_kw_count;
                $target_kw_total = $baidu_target_total;
            }

            if ($suv >= $batch_count && $kw_count >= $target_kw_total){
                return true;
            }
        } else {
            if ($suv >= $batch_count) {
                if ($hao123_kw_count >= $hao_target_total && $baidu_kw_count >= $baidu_target_total) {
                    return true;
                } else if ($hao123_kw_count >= $hao_target_total && !($baidu_kw_count >= $baidu_target_total)) {
                    return -1;
                } else if (!($hao123_kw_count >= $hao_target_total) && $baidu_kw_count >= $baidu_target_total) {
                    return -2;
                }
            }
        }

        return false;
    }

    /**
     * 单个客户端执行到第几个批次
     * @param $task_id
     * @param $client_ip
     * @return int
     */
    public function client_batch_num($task_id, $client_ip)
    {
        $key_prefix = self::task_key_format($task_id) . 'ip_access';
        if (!redis_url('hexists', $key_prefix, $client_ip)) {
            return 1;
        }

        $batch_num = intval(redis_url('hget', $key_prefix, $client_ip));
        $batch_count = count((array)$this->config[$this->config['search_batch.default']]['batch']);
        if ($batch_num > $batch_count) return -1;

        return $batch_num;
    }

    /**
     * 全局数量上的当前批次及配置
     * @param $task_id
     * @param array $search_option
     * @return array|bool
     */
    public function current_batch($task_id, array $search_option)
    {
        $current_batch = [];
        $key_prefix = self::task_key_format($task_id) . 'ip_access';
        $task_ip_num = intval(redis_url('hlen', $key_prefix));
        try {
            $batch_search = $this->get_batch_search($task_id);
            $current_batch['hao123'] = $this->algorithm->currentSearchBatch($task_ip_num, 'hao123', $search_option, $batch_search);
            $current_batch['baidu'] = $this->algorithm->currentSearchBatch($task_ip_num, 'baidu', $search_option, $batch_search);
        } catch (Exception $e) {
            self::show_test_exception($e);
            return false;
        }

        return $current_batch;
    }

    /**
     * 获取默认批次配置
     * @param $task_id
     * @return array|false
     */
    private function get_batch_search($task_id)
    {
        try {
            $batch_type = $this->algorithm->defaultBatchType();
            if ($batch_type == 'float'){
                $batch_search = redis_url('hget', self::task_key_format($task_id. 'batch_conf', 'float'));
                if (empty($batch_search)) {
                    $batch_search = json_encode($this->algorithm->getBatchSearch($this->parse_option($task_id)));
                    redis_url('hset', self::task_key_format($task_id) . 'batch_conf', 'float', $batch_search);
                }
                $batch_search = json_decode($batch_search, true);
            } else {
                $batch_search = $this->algorithm->getBatchSearch($this->parse_option($task_id));
            }
        } catch (Exception $e) {
            self::show_test_exception($e);
            return false;
        }

        return $batch_search;
    }

    /**
     * 在redis设值计数搜索
     * @param $task_id
     * @param $client_ip
     * @param $task_pv
     * @param $hao_kw_count
     * @param $bd_kw_count
     */
    private function redis_incr_count($task_id, $client_ip, $task_pv, $hao_kw_count, $bd_kw_count)
    {
        $key_prefix = self::task_key_format($task_id);
        $batch_num = intval($this->current_batch['hao123']['batch_index']) + 1;
        $key_hour = date('H');
        redis_url('hincrby', $key_prefix . 'ip_hourly', $key_hour, 1);
        redis_url('hincrby', $key_prefix . 'ip_access', $client_ip, 1);
        redis_url('hincrby', $key_prefix . 'pv', 'total', intval($task_pv));
        redis_url('hincrby', $key_prefix . 'pv_hourly', $key_hour, intval($task_pv));
        redis_url('hincrby', $key_prefix . 'hao123', 'kw_count', intval($hao_kw_count));
        redis_url('hincrby', $key_prefix . 'baidu', 'kw_count', intval($bd_kw_count));
        redis_url('hincrby', $key_prefix . 'hao123', 'batch_' . $batch_num, intval($hao_kw_count));
        redis_url('hincrby', $key_prefix . 'baidu', 'batch_' . $batch_num, intval($bd_kw_count));

        // 任务被读取次数
        if (redis_url('exists', $key_prefix . 'read_num')) {
            redis_url('incr', $key_prefix . 'read_num');
        } else {
            redis_url('set', $key_prefix . 'read_num', 1);
        }

        if (!redis_url('hexists', $key_prefix . 'hao123', 'issue_num')) {
            redis_url('hset', $key_prefix . 'hao123', 'issue_num', $this->batch_search['hao123']['total_search']);
        }

        if (!redis_url('hexists', $key_prefix . 'baidu', 'issue_num')) {
            redis_url('hset', $key_prefix . 'baidu', 'issue_num', $this->batch_search['baidu']['total_search']);
        }
    }

    /**
     * 在redis设值计数点击
     * @param $task_id
     * @param $client_ip
     * @param $click_cnt
     * @param int $task_pv
     */
    public function redis_incr_click($task_id, $client_ip, $click_cnt, $task_pv = 0)
    {
        $key_prefix = self::task_key_format($task_id, 'click.');
        $key_hour = date('H');
        redis_url('hincrby', $key_prefix . 'ip_hourly', $key_hour, 1);
        redis_url('hincrby', $key_prefix . 'ip_access', $client_ip, 1);
        redis_url('hincrby', $key_prefix . 'click', 'total', $click_cnt);

        if ($task_pv > 0) {
            redis_url('hincrby', $key_prefix . 'pv', 'total', intval($task_pv));
            redis_url('hincrby', $key_prefix . 'pv_hourly', $key_hour, intval($task_pv));
        }

        if (!redis_url('hexists', $key_prefix . 'click', 'issue_num')) {
            $issue_num = intval($this->parse_option($task_id)['total_ip'] * self::pv_ip_ratio($task_id));
            redis_url('hset', $key_prefix . 'click', 'issue_num', $issue_num);
        }
    }

    /**
     * 格式化任务的redis key前缀
     * @param $task_id
     * @param string $key_prefix
     * @return string
     */
    public static function task_key_format($task_id = '', $key_prefix = 'search.')
    {
        $key_prefix = trim($key_prefix) != '' ? trim($key_prefix) : 'search.';
        $key_prefix .= 't' . $task_id . '.';
        return $key_prefix;
    }

    /**
     * 获取PV IP比值（PV/IP）
     * @param $task_id
     * @param string $type
     * @return float|int
     */
    public static function pv_ip_ratio($task_id, $type = 'area')
    {
        $task_info = self::task_info($task_id, $type);
        if (!empty($task_info['ip_pv_ratio'])) {
            return floatval($task_info['ip_pv_ratio']);
        }

        return 0;
    }

    /**
     * 时间曲线任务是否已完成
     * @param $task_id
     * @param string $type
     * @param string $key_prefix
     * @return bool
     */
    public static function hourly_task_done($task_id, $type = 'area', $key_prefix = 'search.')
    {
        $task_info = self::task_info($task_id, $type);
        $cur_hour = date('H');
        if (!empty($task_info['time_zone'])) {
            if (is_array($task_info['time_zone'])) {
                $timed_task = $task_info['time_zone'];
            } else {
                $timed_task = json_decode($task_info['time_zone'], true);
            }

            $key_prefix = self::task_key_format($task_id, $key_prefix);
            $hourly_task_num = intval(redis_url('hget', $key_prefix . 'ip_hourly', $cur_hour));
            if ($hourly_task_num >= $timed_task[$cur_hour]) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取指定任务信息
     * @param $task_id
     * @param string $type
     * @return array
     */
    public static function task_info($task_id, $type = 'area')
    {
        $task_info = redis_url('hget', 'mn_task_info', $type . '_' . $task_id);
        $task_info = json_decode($task_info, true);
        return (array)$task_info;
    }

    /**
     * 替换XML属性值
     * @param $xml_str
     * @param int $min_st
     * @param int $max_st
     * @return mixed|string|string[]|null
     */
    public static function replace_xml_value($xml_str, $min_st = 10, $max_st = 300)
    {
        $stay_time = 0;
        if (preg_match('/st="([^"]+)"/i', $xml_str, $matches)) {
            if (strstr($matches[1], ',')) {
                list($min_mat, $max_mat) = explode(',', $matches[1]);
                $min_mat = intval($min_mat);
                $max_mat = intval($max_mat);
                if ($min_mat > 0 && $max_mat > 0 && $min_mat < $max_mat) {
                    $stay_time = rand($min_mat, $max_mat);
                } else if ($min_mat > 0) {
                    $stay_time = rand($min_mat, $max_st);
                } else if ($max_mat > 0) {
                    $stay_time = rand($min_st, $max_mat);
                } else {
                    $stay_time = rand($min_st, $max_st);
                }
            } else {
                $stay_time = intval($matches[1]);
                $stay_time = $stay_time > 0 ? $stay_time : rand($min_st, $max_st);
            }

            $xml_str = preg_replace('/st="(.*)"/i', 'st="' . $stay_time . '"', $xml_str);
        } else {
            if (preg_match('/\<t([\s\S]+)s="(.*)"/i', $xml_str, $matches)) {
                $xml_str = str_replace($matches[0], $matches[0] . ' st="' . rand($min_st, $max_st) . '"', $xml_str);
            }
        }

        return $xml_str;
    }

    /**
     * 测试状态就显示异常信息
     * @param Exception $e
     * @param string $custom_msg
     */
    private static function show_test_exception(Exception $e, $custom_msg = '')
    {
        if ($_GET['test']) {
            echo $e->getFile() . '(' . $e->getLine() . '), error: ' . $e->getMessage() . '<br>' . $custom_msg . '<br>';
        }
    }
}
