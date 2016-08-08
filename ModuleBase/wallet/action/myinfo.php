<?php 

mbs_import('', 'CWalletInfoCtr');

mbs_import('user', 'CUserSession');
$usess = new CUserSession();
list($sess_uid,) = $usess->get();

$wlt_ctr = CWalletInfoCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance(), $sess_uid);
$info = $wlt_ctr->get();
if(empty($info)){
    $info =array(
        'uid'             => $sess_uid,
        'amount'          => 0,
        'withdraw_amount' => 0,
        'change_ts'       => time(),
    );
    $wlt_ctr->add($info);
}
$mbs_appenv->echoex($info);

?>