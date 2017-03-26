<?php

/**
 * Account 用户帐户操作
 * @date 2014-4-1 17:22:39
 * @author harryxlb
 */
class Account {
    
    public function index(){
        $unpaySql = "SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE supplier_id='$m_check_uid' AND status='1'";
        $unorderSql = "SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE supplier_id='$m_check_uid' AND status>='2' AND status<='4'";
        $rtlunpay = $DB->fetch($unpaySql);
        $rtlunorder = $DB->fetch($unorderSql);
    }
    
    
    
}
