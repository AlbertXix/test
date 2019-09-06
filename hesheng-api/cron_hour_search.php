<?php
/**
 * 搜索与点击任务统计
 */

require __DIR__ . '/common/common.inc.php';

$table = 'tb_search_statistic';

main();

function main(){
    sync_search_stastics_to_mysql();
}

function sync_search_stastics_to_mysql(){
    global $table;
    $task_keys = search_task_keys();

    foreach ($task_keys as $task_id) {
        $key_prefix = task_key_format($task_id);
        $key_prefix_click = task_key_format($task_id, 'click.');
        $issue_ip_total = 0;
        $issue_ip_num = 0;
        $ip_pv_ratio = 0;
        $issue_sn = 0;
        $issue_hourly_task = '';
        $task_info = area_task_info($task_id);
        if (!empty($task_info)) {
            $issue_ip_total = round($task_info['hao_ip_total']);
            $issue_ip_num = round($task_info['hao_ip_total'] * $task_info['hao_search_rate'] / 100);
            $ip_pv_ratio = !empty($task_info['hao_pv_ratio']) ? $task_info['hao_pv_ratio'] : (!empty($task_info['ip_pv_ratio']) ? $task_info['ip_pv_ratio'] : 0);
            $ip_pv_ratio = floatval($ip_pv_ratio);
            $issue_sn = intval($task_info['hao_sn']);
            $issue_hourly_task = $task_info['time_zone'];
            if (!is_string($issue_hourly_task)) {
                $issue_hourly_task = json_encode($issue_hourly_task);
            }
        }

        $real_ip_num = intval(redis_url('hlen', $key_prefix . 'ip_access'));
        $ip_back = intval(redis_url('hlen', $key_prefix . 'ip_back'));
        $task_pv = intval(redis_url('hget', $key_prefix . 'pv', 'total'));
        $pv_back = intval(redis_url('hget', $key_prefix . 'pv_back', 'total'));
        $search_back = intval(redis_url('hget', $key_prefix . 'search_back', 'total'));
        $issue_hao_sn = redis_url('hget', $key_prefix . 'hao123', 'issue_num');
        $issue_baidu_sn = redis_url('hget', $key_prefix . 'baidu', 'issue_num');
        $real_hao_sn = intval(redis_url('hget', $key_prefix . 'hao123', 'kw_count'));
        $real_baidu_sn = intval(redis_url('hget', $key_prefix . 'baidu', 'kw_count'));
        // 点击统计
        $issue_click = intval(redis_url('hget', $key_prefix_click . 'click', 'issue_num'));
        $real_click = intval(redis_url('hget', $key_prefix_click . 'click', 'total'));
        $click_back = intval(redis_url('hget', $key_prefix_click . 'click', 'click_back'));

        $read_num = intval(redis_url('get', $key_prefix . 'read_num'));
        $real_sn = $real_hao_sn + $real_baidu_sn;
        $real_hourly_task = json_encode(redis_url('hgetall', $key_prefix . 'ip_hourly'));
        $task_batch = [
            'hao123' => redis_url('hgetall', $key_prefix . 'hao123'),
            'baidu' => redis_url('hgetall', $key_prefix . 'baidu'),
            ];

        if (!empty($task_batch['hao123']) || !empty($task_batch['baidu'])){
            $task_batch = json_encode($task_batch);
        } else {
            $task_batch = '';
        }

        $field_values = [
            'task_id' => $task_id,
            'issue_ip_total' => $issue_ip_total,
            'issue_ip_num' => $issue_ip_num,
            'real_ip_num' => $real_ip_num,
            'ip_back' => $ip_back,
            'total_pv' => $task_pv,
            'pv_back' => $pv_back,
            'ip_pv_ratio' => $ip_pv_ratio,
            'search_back' => $search_back,
            'issue_sn' => $issue_sn,
            'real_sn' => $real_sn,
            'issue_hao_sn' => $issue_hao_sn,
            'real_hao_sn' => $real_hao_sn,
            'issue_baidu_sn' => $issue_baidu_sn,
            'real_baidu_sn' => $real_baidu_sn,
            'issue_click' => $issue_click,
            'real_click' => $real_click,
            'click_back' => $click_back,
            'read_num' => $read_num,
            'issue_hourly_task' => "'" . $issue_hourly_task . "'",
            'real_hourly_task' => "'" . $real_hourly_task . "'",
            'task_batch' => "'" . $task_batch . "'",
        ];

        $sql = '';
        if (search_statistics_exists($task_id)) {
            $sql = "UPDATE `{$table}` SET ";
            foreach ($field_values as $key => $value) {
                $sql .= $key . ' = ' . $value . ',';
            }
            $sql = rtrim($sql, ',') . " WHERE task_id = $task_id AND date(`updated_at`) = '" . date('Y-m-d') . "'";
        } else {
            $fields = implode(', ', array_keys($field_values));
            $field_values = implode(', ', $field_values);
            $sql = "INSERT INTO `{$table}` ( $fields ) VALUES ($field_values)";
        }

        get_db()->query($sql);
    }
}

/**
 * 指定任务当天的搜索统计是否存在
 * @param $task_id
 * @return bool
 */
function search_statistics_exists($task_id){
    global $table;
    $today = date('Y-m-d');
    $sql = "SELECT * FROM `{$table}` WHERE task_id = $task_id AND date(`updated_at`) = '$today'";
    $result = get_db()->query($sql)['data'];

    return !empty($result);
}

function search_task_keys(){
    $task_keys = redis_url('keys', 'search.t*.ip_access');
    foreach ($task_keys as $key => $task_key) {
        $task_keys[$key] = preg_replace('/[^\d]+/', '', $task_key);
    }

    return $task_keys;
}

function get_area_task_keys(){
    $task_keys = redis_url('hkeys', 'mn_task_info');
    foreach ($task_keys as $key => $task_id) {
        if (strpos($task_id, 'area_') === false){
            unset($task_keys[$key]);
        } else {
            $task_id = str_replace('area_', '', $task_id);
            $exists = intval(redis_url('exists', 'search.t' . $task_id . '.ip_access'));
            if ($exists) {
                $task = json_decode(redis_url('hget', 'mn_task_info', 'area_' . $task_id), true);
                if (empty($task) || $task['is_hao'] != 1) {
                    unset($task_keys[$key]);
                    continue;
                }

                $task_keys[$key] = str_replace('area_', '', $task_id);
            } else {
                unset($task_keys[$key]);
            }
        }
    }

    return $task_keys;
}

function area_task_info($task_id){
    $task = redis_url('hget', 'mn_task_info', 'area_' . $task_id);
    $task = json_decode($task, true);
    return $task;
}

function task_key_format($task_id = '', $key_prefix = 'search.') {
    $key_prefix = trim($key_prefix) != '' ? trim($key_prefix) : 'search.';
    $key_prefix .= 't' . $task_id . '.';
    return $key_prefix;
}
