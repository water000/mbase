<?php 

mbs_import('', 'CWalletInfoCtr');

mbs_import('user', 'CUserSession');
$usess = new CUserSession();
list($sess_uid,) = $usess->get();

$page_id = 1;
if(isset($_REQUEST['page_id'])){
    $page_id = intval($_REQUEST['page_id']);
    $page_id = $page_id > 0 ? $page_id : 1;
}

$wlthty_ctr = CWalletHistoryCtr::getInstance($mbs_appenv,
    CDbPool::getInstance(), CMemcachedPool::getInstance(), $sess_uid);
$wlthty_ctr->setPageId($page_id);
$list = $wlthty_ctr->get();
foreach($list as &$row){
    $tp = CWalletHistoryCtr::tpconv($row['type']);
    $row['desc'] = $tp !== false ? sprintf($mbs_appenv->lang($tp), $row['mark']) : $row['type'];
}

$mbs_appenv->echoex(array('list'=>$list, 'has_more'=>count($list)==$wlthty_ctr->getDB()->getNumPerPage()));

?>