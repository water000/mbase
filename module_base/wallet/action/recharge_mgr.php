<?php 

mbs_import('', 'CWalletInfoCtr', 'CWalletHistoryCtr', 'CWalletWithdrawHistoryCtr', 'CWalletHandle');

$sys_payer_uid = $mbs_appenv->config('sys_payer_uid', 'user');

$wlt_ctr = CWalletInfoCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance(), $sys_payer_uid);

$hty_ctr = CWalletHistoryCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance());
$conn = $hty_ctr->getDB()->getConnection();

$wdr_htr_ctr = CWalletWithdrawHistoryCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance());
    
if(isset($_REQUEST['recharge_amount'])){
    $amount = floatval($_REQUEST['recharge_amount']);
    if($amount > 0)
        CWalletHandle::recharge($wlt_ctr, $hty_ctr, 
            $sys_payer_uid, CStrTools::currconv($amount));
}

$wlt_info = $wlt_ctr->get();
if(empty($wlt_info)){
    $mbs_appenv->echoex('no such sys_payer init', 'WALLET_RECHARGE_MGR_ERR');
    exit(0);
}


$sql = sprintf('SELECT sum(amount) FROM %s WHERE is_succ=1', 
    $wdr_htr_ctr->getDB()->tbname());
$wdr_total = $wdr_htr_ctr->getDB()->getConnection()->query($sql)->fetchAll(PDO::FETCH_NUM)[0][0];

define('ROWS_PER_PAGE', 20);
$page_id = 1;
if(isset($_REQUEST['page_id'])){
    $page_id = intval($_REQUEST['page_id']);
    if($page_id < 1) $page_id = 1;
}

$types = array('TASK_PAY', 'RECHARGE'); // defined in CWalletHistoryCtr
$req_type = null;
if(isset($_REQUEST['type'])){
    $req_type = array_search($_REQUEST['type'], $types) === false ? null : $_REQUEST['type'];
}

$arr_ts = null; 
if(isset($_REQUEST['ts'])){
    $arr_ts = explode('-', trim($_REQUEST['ts']));
    $arr_ts = empty($arr_ts) || empty($arr_ts[0]) ? null : $arr_ts;
    if(!empty($arr_ts)){
        $arr_ts[0] = strtotime($arr_ts[0]);
        if(isset($arr_ts[1])){
            $arr_ts[1] = strtotime($arr_ts[1]);
            if($arr_ts[1] < $arr_ts[0])
                unset($arr_ts[1]);
        }
        else $arr_ts[1] = $arr_ts[0] + 86400;
    }
}

$sql = sprintf('FROM %s WHERE a_uid=%d %s %s',
    $hty_ctr->getDB()->tbname(), 
    $sys_payer_uid,
    is_null($req_type) ? '' : ' AND type='.CWalletHistoryCtr::tpconv($req_type),
    is_null($arr_ts) ? '' : sprintf(' AND create_ts>=%d AND create_ts<%d', $arr_ts[0], $arr_ts[1]));
$sql_list = sprintf('SELECT * '.$sql.'  ORDER BY id DESC LIMIT %d,%d',
    ($page_id-1)*ROWS_PER_PAGE,
    ROWS_PER_PAGE);
$sql_count = 'SELECT count(1) '.$sql;
$count = $conn->query($sql_count)->fetchAll(PDO::FETCH_NUM)[0][0];
$list = $pageno = array();
if($count > 0){
    $list = $conn->query($sql_list);
    if($count > ROWS_PER_PAGE){
        mbs_import('common', 'CTools');
        $pageno = CTools::genPagination($page_id, ceil($count/ROWS_PER_PAGE), 8);
    }
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
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('ui.daterangepicker.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('jquery-ui-1.7.1.custom.css')?>" type="text/css" title="ui-theme" />
<style type="text/css">
.sm_table td{padding: 6px 8px;}
</style>
</head>
<body>
<div class="container">
	<h3 class="tit"><?php echo $mbs_appenv->lang(array('money', 'manager'))?></h3>
	<div class="cash_control mt20 clearfix">
		<div class="cash_control_left fl">
			<p class="f16"><?php echo $mbs_appenv->lang(array('platform', 'account', 'balance'))?>（<?php echo $mbs_appenv->lang('unit_yuan')?>）</p>
			<div class="mt10 clearfix">
				<h3 class="cash_num fl"><?php echo CStrTools::currconv(intval($wlt_info['amount']))?></h3>
				<form action="" method="post" name="form_recharge" style="display: inline-block;">
				<input type="text" name="recharge_amount" class="sm_inp small fl" style="display:none;margin-left: 30px;padding: 3px;" /><a 
				    href="#" onclick="if('none'==this.previousSibling.style.display){this.previousSibling.style.display='';this.previousSibling.focus();} else if(this.previousSibling.value.length>0) document.form_recharge.submit(); else this.previousSibling.style.display='none';" class="recharge fl" style="margin-left: 5px;">+ 平台充值</a>
				</form>
			</div>
		</div>
		<div class="cash_control_right mt5 fr">
			<ul class="clearfix">
				<li><?php echo $mbs_appenv->lang(array('total', 'RECHARGE'))?>:￥<?php echo CStrTools::currconv(intval($wlt_info['history_amount']))?></li>
				<li><?php echo $mbs_appenv->lang(array('total', 'TASK_PAY'))?>:￥<?php echo CStrTools::currconv($wlt_info['history_amount'] - $wlt_info['amount'])?></li>
				<li><?php echo $mbs_appenv->lang(array('total', 'WITHDRAW'))?>:￥<?php echo $wdr_total?></li>
				<!-- li>累计手续费:￥0</li> -->
			</ul>
		</div>
	</div>
	<form action="" name="form_search" method="post">
	<div class="mt20 clearfix">
		<div class="refer_left fl">
    		<label><?php echo $mbs_appenv->lang('type')?>：</label>
    		<select name=type class="sm_inp small" style="height: auto;">
    		  <option value=0></option>
    		  <?php foreach($types as $t){?>
    		  <option value="<?php echo $t?>" <?php echo $t==$req_type?'selected':''?>><?php echo $mbs_appenv->lang($t)?></option>
    		  <?php }?>
    		</select>
    	</div>
		<div class="refer_left fl">
    		<div class="refer_left prnone fl">
    			<label><?php echo $mbs_appenv->lang(array('transaction', 'time'))?>：</label>
    			<input type="text" name=ts class="sm_inp fl" value="<?php echo isset($_REQUEST['ts']) ? htmlspecialchars($_REQUEST['ts']) : ''?>" style="width: 150px; font-size:12px;" />
    		</div>
    	</div>
		<a href="javascript:document.form_search.submit();" class="inquire fl"><?php echo $mbs_appenv->lang('search')?></a>
	</div>
	</form>
		<table class="sm_table" cellspacing="0" cellpadding="0">
			<tr>
				<th><?php echo $mbs_appenv->lang(array('transaction', 'time'))?></th>
				<th><?php echo $mbs_appenv->lang(array('transaction', 'type'))?></th>
				<th><?php echo $mbs_appenv->lang(array('transaction', 'money'))?>（<?php echo $mbs_appenv->lang('unit_yuan')?>）</th>
				<th><?php echo $mbs_appenv->lang(array('platform', 'balance'))?>（<?php echo $mbs_appenv->lang('unit_yuan')?>）</th>
				<th><?php echo $mbs_appenv->lang('desc')?></th>
			</tr>
			<?php foreach($list as $row){?>
			<tr>
				<td><?php echo date('Y-m-d H:i:s', $row['create_ts'])?></td>
				<td><?php echo $mbs_appenv->lang(CWalletHistoryCtr::tpconv($row['type']))?></td>
				<td><span class="<?php echo $row['amount']>0 ? 'green' : 'red'?>"><?php echo $row['amount'] > 0 ? '+':'', CStrTools::currconv(intval($row['amount']))?></span></td>
				<td><?php echo CStrTools::currconv(intval($row['balance']))?></td>
				<td><?php echo htmlspecialchars($row['msg'])?></td>
			</tr>
			<?php }?>
		</table>
		<div class="btn_all clearfix">
			<div class="record fl"><?php echo sprintf($mbs_appenv->lang('page_num_count_format'), $count)?></div>
			<?php if(!empty($pageno)){?>
			<div class="page fr">
				<?php if($page_id>1){?><a href="?page_id=<?php echo $page_id-1?>"><?php echo $mbs_appenv->lang('prev_page')?></a><?php }?>
				<?php foreach($pageno as $k=>$v){?>
				<a href="?page_id=<?php echo $k?>" <?php echo $page_id==$k?'class=on':''?>><?php echo $v?></a>
				<?php }?>
				<?php if($page_id != $k){?><a href="?page_id=<?php echo $page_id+1?>"><?php echo $mbs_appenv->lang('next_page')?></a><?php }?>
			</div>
			<?php }?>
		</div>
	</div>
</div>
<script type="text/javascript" src="/static/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript" src="/static/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="/static/js/daterangepicker_cn.jQuery.js"></script>
<script type="text/javascript" src="/static/js/global.js"></script>
<script type="text/javascript">
$(document.form_search.elements["ts"]).daterangepicker({dateFormat:"yy/mm/dd"});
</script>
</body>
</html>