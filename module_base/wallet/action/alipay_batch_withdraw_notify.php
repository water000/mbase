<?php

require_once $mbs_appenv->getDir('wallet', CAppEnv::FT_CLASS).'/alipay/alipay_notify.class.php';

$alipayNotify = new AlipayNotify($mbs_appenv->config('alipay'));
$verify_succ = $alipayNotify->verifyNotify();

$error_log = ini_get('error_log');
if(!empty($error_log)){
    error_log(date('Y/m/d H:i:s e')."\t".json_encode(array_merge($_POST, array('verify_res'=>$verify_succ)))."\n", 3,
        pathinfo($error_log, PATHINFO_DIRNAME).'/ali_batch_trans_notify.log');
}

if($verify_succ){
    
    mbs_import('', 'CWalletInfoCtr', 'CWalletHistoryCtr', 'CWalletWithdrawApplyCtr', 
        'CWalletWithdrawHistoryCtr', 'CWalletWithdrawBatchCtr', 'CWalletHandle');
    
    $hty_ctr = CWalletHistoryCtr::getInstance($mbs_appenv, 
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    
    $wdr_ctr = CWalletWithdrawApplyCtr::getInstance($mbs_appenv, 
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    
    $wdr_hty_ctr = CWalletWithdrawHistoryCtr::getInstance($mbs_appenv,
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    
    $wdr_bat_ctr = CWalletWithdrawBatchCtr::getInstance($mbs_appenv, 
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    
    $wlt_ctr = CWalletInfoCtr::getInstance($mbs_appenv,
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    
    CWalletHandle::ali_withdraw_batch_resp($_POST, $hty_ctr, 
        $wdr_ctr, $wdr_hty_ctr, $wdr_bat_ctr, $wlt_ctr);
    
    echo "success";
    
}else{
    echo "fail";
}

?>