<?php 

mbs_import('', 'CWalletHistoryCtr', 'CWalletWithdrawApplyCtr', 'CWalletInfoCtr');

$uid = 0;

if(isset($_REQUEST['uid'])){
    if(mbs_moddef('privilege')->filter('privFtr', array('action'=>'list', 'mod'=>'user')))
        ;
    else{
        $mbs_appenv->echoex('access denied', 'WALLET_HISTORY_ERR');
        exit(0);
    }
    $uid = intval($_REQUEST['uid']);
}else{
    mbs_import('user', 'CUserSession');
    $usess = new CUserSession();
    list($uid,) = $usess->get();
}

$page_id = 1;
if(isset($_REQUEST['page_id'])){
    $page_id = intval($_REQUEST['page_id']);
    $page_id = $page_id > 0 ? $page_id : 1;
}

$wlthty_ctr = CWalletHistoryCtr::getInstance($mbs_appenv,
    CDbPool::getInstance(), CMemcachedPool::getInstance(), $uid);
$wlthty_ctr->setPageId($page_id);
$list = $wlthty_ctr->get();
foreach($list as &$row){
    $tp = CWalletHistoryCtr::tpconv($row['type']);
    $row['amount'] = CStrTools::currconv(intval($row['amount']));
    $row['balance'] = CStrTools::currconv(intval($row['balance']));
    $row['title'] = $tp !== false ? sprintf($mbs_appenv->lang(strtolower($tp).'_desc'), $row['msg']) : $row['type'];
    $row['msg'] = '';
}

if(1 == $page_id){
    $wltwda_ctr = CWalletWithdrawApplyCtr::getInstance($mbs_appenv, 
        CDbPool::getInstance(), CMemcachedPool::getInstance(), $uid);
    $wda = $wltwda_ctr->get();
    if(!empty($wda) && ($st=CWalletWithdrawApplyCtr::stconv($wda['status']))!='SUCCEEDED'){
        $wlt_ctr = CWalletInfoCtr::getInstance($mbs_appenv, 
            CDbPool::getInstance(), CMemcachedPool::getInstance(), $uid);
        $winfo = $wlt_ctr->get();
        array_unshift($list, array(
            'amount'   => CStrTools::currconv(-intval($wda['amount'])),
            'title'    => sprintf($mbs_appenv->lang('withdraw_desc'), ''),
            'msg'      => $mbs_appenv->lang($st).('FAILED' == $st ? '('.$wda['fault_msg'].')':''),
            'create_ts'=> $wda['submit_ts'],
            'balance'  => CStrTools::currconv(intval($winfo['amount'])),
        ));
    }
}

if($mbs_appenv->item('client_accept') != 'html'){
    $mbs_appenv->echoex(array('list'=>$list, 'has_more'=>count($list)==$wlthty_ctr->getDB()->getNumPerPage()));
    exit(0);
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('reset.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('style.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('iconfont.css')?>" rel="stylesheet">
<style type="text/css">
</style>
</head>
<body>
<?php 
mbs_import('user', 'CUserInfoCtr');
$user_ctr = CUserInfoCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance(), $uid);
$count = $wlthty_ctr->getTotal();
define('PAGE_ID',  $page_id);
$pageno = null;
if($count > $wlthty_ctr->getDB()->getNumPerPage()){
    mbs_import('common', 'CTools');
    $pageno = CTools::genPagination(PAGE_ID, ceil($count/ROWS_PER_PAGE));
}
?>
<div class="container">
    <div class="clearfix">
		<h3 class="tit fl"><?php echo $user_ctr->name(), $mbs_appenv->lang(array('of', 'wallet', 'log'))?></h3>
		<a href="javascript:;" onclick="top._history_back(1);" class="btn_back ml15 fr">&lt;<?php echo $mbs_appenv->lang('back')?></a>
	</div>
	<table class="sm_table" cellspacing="0" cellpadding="0">
    	<tr>
    		<th width="10%"><?php echo $mbs_appenv->lang('id')?></th>
    		<th width="25%"><?php echo $mbs_appenv->lang('type')?></th>
    		<th width="10%"><?php echo $mbs_appenv->lang(array('time'))?></th>
    		<th width="8%"><?php echo $mbs_appenv->lang('amount')?></th>
    		<th width="8%"><?php echo $mbs_appenv->lang('balance')?></th>
    	</tr>
    	<?php foreach($list as $k=>$row){ ?>
    	<tr>
    	   <td><?php echo $k+1?></td>
    	   <td><?php echo $row['title']?></td>
    	   <td><?php echo date('Y-m-d H:i:s', $row['create_ts'])?></td>
    	   <td><?php echo $row['amount']?></td>
    	   <td><?php echo $row['balance']?></td>
    	</tr>
    	<?php } ?>
    </table>
    <div class="btn_all clearfix">
	   <div class="fl"><?php echo sprintf($mbs_appenv->lang('page_num_count_format'), $count)?></div>
	  <?php if(!empty($pageno)){?>
		<div class="page fr">
			<?php if(PAGE_ID>1){?><a href="?<?php echo http_build_query(array_merge($_GET, array('page_id', PAGE_ID-1)))?>"><?php echo $mbs_appenv->lang('prev_page')?></a><?php }?>
			<?php foreach($pageno as $k=>$v){?>
			<a href="?<?php echo http_build_query(array_merge($_GET, array('page_id', $k)))?>" <?php echo PAGE_ID==$k?'class=on':''?>><?php echo $v?></a>
			<?php }?>
			<?php if(PAGE_ID != $k){?><a href="?<?php echo http_build_query(array_merge($_GET, array('page_id', PAGE_ID+1)))?>"><?php echo $mbs_appenv->lang('next_page')?></a><?php }?>
		</div>
		<?php }?>
	</div>
</div>
</body>
</html>