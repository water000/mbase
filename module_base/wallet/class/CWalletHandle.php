<?php

class CWalletHandle{
    
    static function response($ev, $args){
       global $mbs_appenv;
       
       switch ($ev){
           case 'task.CTaskSubmitCtr.setNode':
               mbs_import('wallet', 'CWalletInfoCtr', 'CWalletHistoryCtr');
               mbs_import('task', 'CTaskSubmitCtr', 'CTaskInfoCtr');
               if('USED' == CTaskSubmitCtr::stconv($args['status'])){
                   $wlt_ctr = CWalletInfoCtr::getInstance($mbs_appenv, 
                        CDbPool::getInstance(), CMemcachedPool::getInstance());
                   $wlt_hty_ctr = CWalletHistoryCtr::getInstance($mbs_appenv, 
                       CDbPool::getInstance(), CMemcachedPool::getInstance());
                   $task_ctr = CTaskInfoCtr::getInstance($mbs_appenv,
                       CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['task_id']);
                   $task = $task_ctr->get();
                   self::transfer($wlt_ctr, $wlt_hty_ctr, 
                       $mbs_appenv->config('sys_payer_uid', 'user'), 
                       $args['uid'], $task['price'], time());
               }
               break;
       }
    }
    
    //@$amount: the param should be convert to sys uint(fen) 
    static function transfer($wlt_ctr, $wlt_hty_ctr, $payer_uid, $payee_uid, $amount, $timestamp, $msg=''){
        $ret = '';

        $conn = $wlt_ctr->getDB()->getConnection();       
        try {
            $conn->beginTransaction();
            
            $wlt_ctr->setPrimaryKey($payer_uid);
            if( 0 == $wlt_ctr->incr(-$amount) ){
                $ret = 'WALLET_BALANCE_NOT_ENOUGH';
            }else{
                $wlt_ctr->setPrimaryKey($payee_uid);
                if(0 == $wlt_ctr->incr($amount)){
                    $ret = 'WALLET_USER_TRANSER_ERR';
                }else{
                    $wlt_ctr->setPrimaryKey($payer_uid);
                    $info = $wlt_ctr->get();
                    $arr = array(
                        'a_uid'     => $payer_uid,
                        'b_uid'     => $payee_uid,
                        'amount'    => -$amount,
                        'balance'   => $info['amount'],
                        'create_ts' => $timestamp,
                        'type'      => CWalletHistoryCtr::tpconv('TASK_PAY'),
                        'msg'       => $msg
                    );
                    $wlt_hty_ctr->addNode($arr);
                    
                    $wlt_ctr->setPrimaryKey($payee_uid);
                    $info = $wlt_ctr->get();
                    $arr = array(
                        'a_uid'     => $payee_uid,
                        'b_uid'     => $payer_uid,
                        'amount'    => $amount,
                        'balance'  => $info['amount'],
                        'create_ts' => $timestamp,
                        'type'      => CWalletHistoryCtr::tpconv('TASK_PAY'),
                        'msg'       => $msg
                    );
                    $wlt_hty_ctr->addNode($arr);
                }
            }
            if(empty($ret))
                $conn->commit();
            else 
                $conn->rollBack();
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
        return $ret;
    }

    static function recharge($wlt_ctr, $wlt_hty_ctr, $uid, $amount){
        $conn = $wlt_ctr->getDB()->getConnection();
        $conn->beginTransaction();
        try {
            $wlt_ctr->setPrimaryKey($uid);
            $wlt_ctr->incr($amount);
            $info = $wlt_ctr->get();
            $arr = array(
                'a_uid'     => $uid,
                'amount'    => $amount,
                'balance'   => $info['amount'],
                'create_ts' => time(),
                'type'      => CWalletHistoryCtr::tpconv('RECHARGE')
            );
            $wlt_hty_ctr->addNode($arr);
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
    
    static function withdraw_apply($wlt_ctr, $wlt_wdr_ctr, $uid, $amount, $account, $acc_name){
        global $mbs_appenv;
        
        $ret = '';
        
        $conn = $wlt_ctr->getDB()->getConnection();
        $conn->beginTransaction();
        
        try {
            $wlt_ctr->setPrimaryKey($uid);
            $afrows = $wlt_ctr->incr(-$amount);
            if( 0 == $afrows ){
                $ret = 'WALLET_BALANCE_NOT_ENOUGH';
            }else{
                $arr = array(
                    'uid'          => $uid,
                    'amount'       => $amount,
                    'dest_account' => $account,
                    'account_name' => $acc_name,
                    'submit_ts'    => time(),
                );
                $wlt_wdr_ctr->add($arr);
            }
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            if($e->getCode() == $mbs_appenv->config('PDO_ER_DUP_ENTRY', 'common'))
                $ret = 'WALLET_WITHDRAW_EXISTS';
            else
                throw $e;
        }
        return $ret;
    }
    
    static function _withdraw_resp($wlt_wdr_ctr, $wlt_wdr_hty_ctr, $wlt_ctr, $wlt_hty_ctr, 
        $uid, $notify_ts, $batch_no, $fault_msg='', $order_id='', $query_id='')
    {
        $conn = $wlt_wdr_ctr->getDB()->getConnection();
        try {
            $conn->beginTransaction();
            $wlt_wdr_ctr->setPrimaryKey($uid);
            $wdr = $wlt_wdr_ctr->get();
            if(empty($wdr)) continue;
            $wlt_wdr_ctr->destroy();
            
            $wlt_ctr->setPrimaryKey($uid);
            if(!empty($fault_msg)){
                $wlt_ctr->incr($wdr['amount']);
            }
            $info = $wlt_ctr->get();
            $arr = array(
                'a_uid'     => $uid,
                'amount'    => -$wdr['amount'],
                'balance'   => $info['amount'],
                'create_ts' => time(),
                'type'      => CWalletHistoryCtr::tpconv('WITHDRAW'),
                'msg'       => $fault_msg,
            );
            $wlt_hty_ctr->addNode($arr);
            
            $arr = array(
                'uid'          => $uid,
                'amount'       => $wdr['amount'],
                'dest_account' => $wdr['dest_account'],
                'account_name' => $wdr['account_name'],
                'account_type' => $wdr['account_type'],
                'submit_ts'    => $wdr['submit_ts'],
                'notify_ts'    => $notify_ts,
                'is_succ'      => empty($fault_msg) ? 1 : 0,
                'fault_msg'    => $fault_msg,
                'batch_no'     => $batch_no,
                'order_id'     => $order_id,
                'query_id'     => $query_id,
            );
            $wlt_wdr_hty_ctr->addNode($arr);
            
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
    
    static function withdraw_batch_req($succ_uid_list, $fail_uid_list, $wlt_wdr_ctr, $wlt_wdr_bat_ctr, 
        $wlt_wdr_hty_ctr, $wlt_ctr, $wlt_hty_ctr)
    {
        $error = array();
        $list = array();
        $batch = array(
            'number'     => 0,
            'submit_ts'  => time(),
            'total_qty' => count($succ_uid_list) + count($fail_uid_list),
            'total_amt'  => 0,
            'fault_qty'  => count($fail_uid_list),
            'resp_code'  => '',
        );
        //$z = mktime(0, 0, 0);
        //$batch['number'] = $z + ceil(($batch['submit_ts'] - $z)/9); // [1 ~ 86400]/9 = [1 ~ 9600]
        $batch['number'] = $batch['submit_ts'];
        $now = date('YmdHis', $batch['submit_ts']);
        
        foreach($succ_uid_list as $uid){
            $wlt_wdr_ctr->setPrimaryKey($uid);
            $wdr = $wlt_wdr_ctr->get();
            if(empty($wdr)) $error[] = $uid;
            else{
                $batch['total_amt'] += $wdr['amount'];
                $list[] = array($wdr['dest_account'], $wdr['account_name'],
                    $wdr['amount'], $wdr['account_type'], $now.$uid);
            }
        }

        if(!empty($fail_uid_list)){
            foreach($fail_uid_list as $us){
                list($uid, $err) = explode(':', $us, 2);
                self::_withdraw_resp($wlt_wdr_ctr, $wlt_wdr_hty_ctr, $wlt_ctr, $wlt_hty_ctr,
                    $uid, time(), $batch['number'], $err);
            }
        }
        
        if(0 == $batch['total_amt']){
            $batch['resp_code'] = 'NO_REQ_PAY';
            $ret = '';
        }else{
            foreach($succ_uid_list as $uid){
                $wlt_wdr_ctr->setPrimaryKey($uid);
                $wlt_wdr_ctr->set(array(
                    'status'    => CWalletWithdrawApplyCtr::stconv('ACCEPTED'),
                    'update_ts' => $batch['submit_ts'],
                ));
            }
            $ret = self::ali_withdraw_batch_req($batch, $list);
        }
        $wlt_wdr_bat_ctr->add($batch);
        return $ret;
    }
    
    static function ali_withdraw_batch_return($batch_no, $resp_code, $wlt_wdr_bat_ctr, $wlt_wdr_ctr){
        /*if(($pos=strpos($ret, 'class="ExclaimedInfo')) !== false){
            $code = 'UNKNOWN_ERR';
            $msg  = 'ALIPAY_ERR';
            if(preg_match('/<div class="?Todo"?>(.+?)<\/div>/', substr($ret, $pos), $match)){
                $errno = '';
                $err = trim($match[1]);
                for($i=strlen($err)-1;
                    $i>=0 && (($err[$i] >= 'A' && $err[$i]<='Z') || '_'==$err[$i]); --$i){
                    $errno = $err[$i].$errno;
                }
                if(!empty($errno)) {
                    $error_map = $mbs_appenv->lang('alierr', 'wallet');
                    $code = $errno;
                    $msg  = isset($error_map[$errno]) ? $error_map[$errno] : $errno;
                }else {
                    $msg = $err;
                }
            }
        }*/
        
        $batch['resp_code'] = $resp_code;
        //$batch['resp_msg']  = $msg;
        $wlt_wdr_bat_ctr->setPrimaryKey($batch_no);
        $wlt_wdr_bat_ctr->set($batch);
        
        if($resp_code  != 'SUCCESS'){
            $wlt_wdr_ctr->getDB()->getConnection()->query(sprintf('UPDATE %s SET status=%d WHERE update_ts=%d',
                $wlt_wdr_ctr->getDB()->tbname(), 
                CWalletWithdrawApplyCtr::stconv('APPLIED'), 
                $batch_no));
        }
    }
    
    static function ali_withdraw_batch_req($batch, $list){
        global $mbs_appenv;
        
        require_once __DIR__.'/alipay/alipay_submit.class.php';
        
        $detail_data = '';
        foreach($list as $elem){
            $detail_data .= $elem[4].'^'.$elem[0].'^'.$elem[1].'^'
                .CStrTools::currconv(intval($elem[2])).'^|';
        }
        $detail_data = substr($detail_data, 0, -1);
        
        $alipay_config = $mbs_appenv->config('alipay', 'wallet');
        $parameter = array(
            "service"        => "batch_trans_notify",
            "partner"        => $alipay_config['partner'],
            "notify_url"	 => $alipay_config['notify_url'],
            "email"	         => $alipay_config['pay_email'],
            "account_name"	 => $alipay_config['pay_name'],
            "_input_charset" => $alipay_config['input_charset'],
            "pay_date"	     => date('Ymd', $batch['submit_ts']),
            "batch_no"	     => date('YmdHis', $batch['number']),
            "batch_fee"	     => CStrTools::currconv($batch['total_amt']),
            "batch_num"	     => count($list),
            "detail_data"	 => $detail_data,
        );
        
        $alipaySubmit = new AlipaySubmit($alipay_config);
        //$ret = $alipaySubmit->buildRequestHttp($parameter);
        $error_log = ini_get('error_log');
        if(!empty($error_log)){
            error_log(date('Y/m/d H:i:s e')."\t".json_encode($parameter)."\n", 3, 
                pathinfo($error_log, PATHINFO_DIRNAME).'/ali_batch_trans.log');
        }
        return $alipaySubmit->buildRequestForm($parameter,'post', 'Loading...');
    }
    
    static function ali_withdraw_batch_resp($data, 
        $hty_ctr, $wdr_ctr, $wdr_hty_ctr, $wdr_bat_ctr, $wlt_ctr){
        global $mbs_appenv;
        
        $succ_num = $err_num = 0;
        $data['batch_no'] = strtotime($data['batch_no']);
        
        try {
            foreach(explode('|', $data['success_details']) as $str){
                if(empty($str)) continue;
                ++$succ_num;
                list($order_id, $acc, $acc_name, $amount, $flag, $reason, $query_id, $time) = explode('^', $str);
                $uid = substr($order_id, 14);
                self::_withdraw_resp($wdr_ctr, $wdr_hty_ctr, $wlt_ctr, $hty_ctr, 
                    $uid, strtotime($time), $data['batch_no'], '', $order_id, $query_id);
            }
             
            $error_map = $mbs_appenv->lang('alierr', 'wallet');
            
            if(isset($data['fail_details'])){
                foreach(explode('|', $data['fail_details']) as $str){
                    if(empty($str)) continue;
                    ++$err_num;
                    list($order_id, $acc, $acc_name, $amount, $flag, $reason, $query_id, $time) = explode('^', $str);
                    $uid = substr($order_id, 14);
                    $fault_msg = isset($error_map[$reason]) ? $error_map[$reason] : $reason;
                    self::_withdraw_resp($wdr_ctr, $wdr_hty_ctr, $wlt_ctr, $hty_ctr,
                        $uid, strtotime($time), $data['batch_no'], $fault_msg, $order_id, $query_id);
                }
            }
            
            $wdr_bat_ctr->setPrimaryKey($data['batch_no']);
            $bat = $wdr_bat_ctr->get();
            $wdr_bat_ctr->set(array(
                'success_qty' => $succ_num,
                'resp_code'   => 'SUCCESS',
                'fault_qty'   => $err_num+$bat['fault_qty'],
                'notify_ts'  => strtotime($data['notify_time']),
            ));
        } catch (Exception $e) {
            throw $e;
        }
    }
}

?>