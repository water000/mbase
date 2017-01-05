<?php 

mbs_import('', 'CWalletHistoryCtr', 'CWalletInfoCtr', 'CWalletWithdrawBatchCtr', 
    'CWalletWithdrawApplyCtr', 'CWalletWithdrawHistoryCtr', 'CWalletHandle');
mbs_import('user', 'CUserInfoCtr');

$wdr_ctr = CWalletWithdrawApplyCtr::getInstance($mbs_appenv,
    CDbPool::getInstance(), CMemcachedPool::getInstance());

$wdr_hty_ctr = CWalletWithdrawHistoryCtr::getInstance($mbs_appenv,
    CDbPool::getInstance(), CMemcachedPool::getInstance());
    
$user_ctr = CUserInfoCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance());
    
$wdr_bat_ctr = CWalletWithdrawBatchCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance());
    
$wlt_ctr = CWalletInfoCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance());
    
$wlt_hty_ctr = CWalletHistoryCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance());
    
if(isset($_REQUEST['bat_detail'])){
    $bat_list = array();
    $wdr_bat_ctr->setPrimaryKey(intval($_REQUEST['bat_detail']));
    $bat =  $wdr_bat_ctr->get();
    if(!empty($bat)){
        if($bat['notify_ts']){
            $bat_list = $wdr_hty_ctr->getDB()->search(array('batch_no'=>intval($_REQUEST['bat_detail'])))->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $bat_list = $wdr_ctr->getDB()->search(array('update_ts'=>intval($_REQUEST['bat_detail'])))->fetchAll(PDO::FETCH_ASSOC);
            if($bat['fault_qty'] > 0)
                $bat_list += $wdr_hty_ctr->getDB()->search(array('batch_no'=>intval($_REQUEST['bat_detail'])))->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}else{ 
    $status_list = array('do_oper', 'done_oper');
    $req_status = 'do_oper';
    if(isset($_REQUEST['status'])){
        $req_status = array_search($_REQUEST['status'], $status_list) === false 
            ? $req_status : $_REQUEST['status'];
    }
    
    if('do_oper' == $req_status){
        $popwin_select_res = '';
        if(isset($_REQUEST['uid']) || isset($_REQUEST['refuse_uid'])){
            $ret = CWalletHandle::withdraw_batch_req(
                isset($_REQUEST['uid']) ? $_REQUEST['uid'] : array(), 
                isset($_REQUEST['refuse_uid']) ? $_REQUEST['refuse_uid'] : array(), 
                $wdr_ctr, $wdr_bat_ctr, $wdr_hty_ctr, $wlt_ctr, $wlt_hty_ctr);
            if(empty($ret)) exit($mbs_appenv->lang('success'));
            else exit($ret);
        }else{
            $lastBat = $wdr_bat_ctr->lastBatch();
            if(!empty($lastBat) && empty($lastBat['resp_code'])){
                if(isset($_REQUEST['resp_code'])){
                    CWalletHandle::ali_withdraw_batch_return($lastBat['number'], 
                        intval($_REQUEST['resp_code'])?'SUCCESS':'FAULT', $wdr_bat_ctr, $wdr_ctr);
                }else{
                    $popwin_select_res = sprintf($mbs_appenv->lang('popwin_select_res_notice'), 
                        date('Y-m-d H:i', $lastBat['number']), 
                        $lastBat['number']);
                }
            }
        }
        
        $wdr_count = 0;
        $wdr_list = array();
        define('ROWS_PER_PAGE', 50);
        $keyval = array(
            'status' => CWalletWithdrawApplyCtr::stconv('APPLIED')
        );
        $opt = array(
            'order' => 'submit_ts',
            'limit' => ROWS_PER_PAGE
        );
        if(!$popwin_select_res){
            $wdr_count = $wdr_ctr->getDB()->count($keyval);
            $wdr_list = $wdr_count > 0 ? $wdr_ctr->getDB()->search($keyval, $opt) : array();
        }
    }else {
        $bat_count = $wdr_bat_ctr->getDB()->count(array());
        
        define('ROWS_PER_PAGE', 20);
        $page_id = isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1;
        $page_id = $page_id > 0 ? $page_id : 1;
        $opt = array(
            'order' => 'submit_ts desc',
            'offset' => ($page_id-1)*ROWS_PER_PAGE,
            'limit' => ROWS_PER_PAGE
        );
        $bat_list = $bat_count > 0 ? $wdr_bat_ctr->getDB()->search(array(), $opt) : array();
        
        if($bat_count > ROWS_PER_PAGE){
            mbs_import('common', 'CTools');
            $pageno = CTools::genPagination($page_id, ceil($bat_count/ROWS_PER_PAGE), 8);
        }
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
<style type="text/css">
.sm_table td{padding: 6px 8px;}
.tab li a{color: #333;}
b{color:#555;padding:0 2px;}
select{height: auto !important;}
.pw-bat{width:930px;height:550px;}
.pw-bat .bd{overflow:hidden !important;}
.pw-bat iframe{width:100%;height:100%;}
<?php if(isset($_REQUEST['bat_detail'])){?>
.sm_table{border:0;}
.sm_table th{border:0;padding:3px 8px;background: #ddd;}
.sm_table td{border-top:0; border-bottom: 1px solid #C8C8C8;}
<?php }?>
.pw-pay{width:50%;height:auto;left:25%;top:8%;}
.res-btn{margin-top:10%;}
.res-btn a{display:block;background:#eee;border: 1px solid #eee;width:50%;padding:1em 0;margin:2em 0;margin:50px auto;font-size:18px;text-align:center;}
.res-btn a:hover{text-decoration:underline;}
.res-btn .succ{color:#fff;background-color:#3EB33E;}
.res-btn .err{color:#fff;background-color:#C56262;}
</style>
</head>
<body>
<div class="container" <?php if(isset($_REQUEST['bat_detail'])) echo 'style="padding:20px 0;height:100%;"'?>>
    <?php if(isset($req_status)){ ?>
    <ul class="tab clearfix">
    	<?php foreach($status_list as $s){ if($req_status == $s){ ?>
    	<li class=on><?php echo $mbs_appenv->lang($s)?></li>
    	<?php }else{ ?>
    	<li><a href="?<?php echo http_build_query(array_merge($_GET, array('status'=>$s)))?>"><?php echo $mbs_appenv->lang($s)?></a></li>
    	<?php }}?>
    </ul>
    
    <?php if('do_oper' == $req_status){?>
    <div class="refer clearfix" style="margin:10px 0;">
		<h3 class="refer_tit fl"><?php echo sprintf($mbs_appenv->lang('withdraw_stat_desc'), count($wdr_list), $wdr_count-count($wdr_list))?></h3>
	</div>
	<form action="" method="post" name="form_withdraw" target=_blank >
	<table class="sm_table" cellspacing="0" cellpadding="0" style="margin:0;">
		<tr>
		    <th width="2%"><input id=IDI_CHK_ALL type="checkbox" class="top2" onclick="_checkall(this, document.form_withdraw)" /></th>
			<th width="20%"><?php echo $mbs_appenv->lang(array('withdraw', 'person'))?></th>
			<th><?php echo $mbs_appenv->lang(array('apply', 'time'))?></th>
			<th><?php echo $mbs_appenv->lang(array('withdraw', 'money'))?>（<?php echo $mbs_appenv->lang('unit_yuan')?>）</th>
			<th><?php echo $mbs_appenv->lang('account')?></th>
		</tr>
		<?php $row=null; foreach($wdr_list as $k=> $row){ $user_ctr->setPrimaryKey($row['uid']); ?>
		<tr>
		    <td><input id=inp<?php echo $k?> type="checkbox" name="uid[]" value="<?php echo $row['uid']?>" /></td>
			<td><label for="inp<?php echo $k?>"><?php echo $user_ctr->name()?></label><span style="color:red"></span></td>
			<td><?php echo date('Y-m-d H:i:s', $row['submit_ts'])?></td>
			<td><?php echo CStrTools::currconv(intval($row['amount']))?></td>
			<td><?php echo htmlspecialchars($row['dest_account'])?>(<?php echo htmlspecialchars($row['account_name'])?>)</td>
		</tr>
		<?php }?>
	</table>
	<div class="btn_all clearfix">
		<div class="batch fl" >
		<?php if($row != null){?>
			<a href="javascript:;" onclick="_withdraw(document.form_withdraw, this);" class="pltg blue"><?php echo $mbs_appenv->lang('batch_withdraw')?></a>
		<?php } ?>
		</div>
		<span id=IDS_STAT_DESC></span>
	</div>
	</form>
	<?php }else if('done_oper' == $req_status){ ?>
	<div class="tab_div mt20">
		<div class="refer clearfix">
			<h3 class="refer_tit fl"><?php echo sprintf($mbs_appenv->lang('page_num_count_format'), $bat_count)?></h3>
		</div>
		<table class="sm_table" cellspacing="0" cellpadding="0">
			<tr>
				<th><?php echo $mbs_appenv->lang('bat_no')?></th>
				<th><?php echo $mbs_appenv->lang(array('submit', 'time'))?></th>
				<th><?php echo $mbs_appenv->lang(array('total', 'quantity'))?></th>
				<th><?php echo $mbs_appenv->lang(array('total', 'amount'))?></th>
				<th><?php echo $mbs_appenv->lang(array('success', 'quantity'))?></th>
				<th><?php echo $mbs_appenv->lang(array('fault', 'quantity'))?></th>
				<th><?php echo $mbs_appenv->lang(array('response', 'time'))?></th>
				<th><?php echo $mbs_appenv->lang('operation')?></th>
			</tr>
			<?php $time_unit = $mbs_appenv->lang('time_unit');foreach($bat_list as $row){?>
			<tr>
				<td><?php echo date('YmdHis', $row['number'])?></td>
				<td><?php echo date('Y-m-d', $row['submit_ts'])?></td>
				<td><?php echo $row['total_qty']?></td>
				<td><?php echo CStrTools::currconv(intval($row['total_amt']))?></td>
				<td><?php echo $row['success_qty']?></td>
				<td><span><?php echo $row['fault_qty']?></span></td>
				<td><?php if(!empty($row['notify_ts'])){$secdesc = CStrTools::secdesc($row['notify_ts']-$row['submit_ts']);echo $secdesc[0], '(', $time_unit[$secdesc[1]], ')';}?></td>
				<td>
					<a href="javascript:_detail(<?php echo $row['number']?>);" class="base"><?php echo $mbs_appenv->lang('detail')?></a>
				</td>
			</tr>
			<?php }?>
		</table>
		<div class="btn_all clearfix">
		<?php if(!empty($pageno)){?>
    		<div class="page fr">
    			<?php if($page_id>1){?><a href="?status=done_oper&page_id=<?php echo $page_id-1?>"><?php echo $mbs_appenv->lang('prev_page')?></a><?php }?>
    			<?php foreach($pageno as $k=>$v){?>
    			<a href="?status=done_oper&page_id=<?php echo $k?>" <?php echo $page_id==$k?'class=on':''?>><?php echo $v?></a>
    			<?php }?>
    			<?php if($page_id != $k){?><a href="?status=done_oper&page_id=<?php echo $page_id+1?>"><?php echo $mbs_appenv->lang('next_page')?></a><?php }?>
    		</div>
    		<?php }?>
    	</div>
	</div>
	<?php }}else if(isset($_REQUEST['bat_detail'])){ ?>
	<table class="sm_table" cellspacing="0" cellpadding="0" style="margin:0;">
		<tr>
		    <th style="text-align: center;width:90px;">#</th>
		    <th><?php echo $mbs_appenv->lang(array('withdraw', 'person'))?></th>
		    <th><?php echo $mbs_appenv->lang(array('withdraw', 'money'))?>（<?php echo $mbs_appenv->lang('unit_yuan')?>）</th>
			<th><?php echo $mbs_appenv->lang(array('apply', 'time'))?></th>
			<th><?php echo $mbs_appenv->lang(array('apply', 'result'))?></th>
			<th><?php echo $mbs_appenv->lang('remark')?></th>
		</tr>
		<?php foreach($bat_list as $k=> $row){ ?>
		<tr>
		    <td style="text-align: center;"><?php echo $k+1?></td>
			<td><?php echo $row['dest_account'], '(', $row['account_name'], ')'?></td>
			<td><?php echo 0==$row['amount']? 0 : CStrTools::currconv(intval($row['amount']))?></td>
			<td><?php echo date('Y-m-d H:i:s', $row['submit_ts'])?></td>
			<td><?php echo isset($row['is_succ']) ? ($row['is_succ'] ? $mbs_appenv->lang('success') : '<span class=red>'.$mbs_appenv->lang('fault').'</span>') : $mbs_appenv->lang('ACCEPTED')?></td>
			<td><?php echo isset($row['fault_msg'])&&!empty($row['fault_msg']) ? '<span class=red>'.$row['fault_msg'].'</span>':''?></td>
		</tr>
		<?php }?>
	</table>
	<div class="btn_all clearfix">
		<div class="record fl" style="padding-left:30px;font-size:14px;"><?php echo sprintf($mbs_appenv->lang('page_num_count_format'), $k+1)?></div>			
	</div>
	<?php } ?>
</div>

<script type="text/javascript" src="/static/js/global.js"></script>
<script type="text/javascript">
<?php if(isset($req_status) && 'do_oper' == $req_status){ ?>
var num=0, amount=0, uncheck_users={}, 
	pw_refuse = popwin('', "<select class=sm_inp><option><?php echo $mbs_appenv->lang('refuse_notice')?></option><?php foreach($mbs_appenv->lang('sys_refuse_reason') as $s){?><option value='<?php echo $s?>'><?php echo $s?></option><?php }?></select>"),
	rfu_sel = pw_refuse.getElementsByTagName("select")[0];
pw_refuse.onclose = function(e){rfu_sel.selectedIndex = 0;}	

function _checkall(chkbox, form, init){
	var i, boxes=form.elements["uid[]"], 
		stat_format = "<?php echo $mbs_appenv->lang('batch_select_desc')?>",
		stat_desc = document.getElementById("IDS_STAT_DESC");
	if(!boxes) return;
	var _chxclick = function(chk){
		var m = parseFloat(chk.parentNode.parentNode.cells[3].innerHTML);
		if(chk.checked){
			num++;
			amount += m;
			if(uncheck_users[chk.value]) delete uncheck_users[chk.value];
			chk.parentNode.parentNode.getElementsByTagName("span")[0].innerHTML="";
		}else{
			num--;
			amount -= m;
			uncheck_users[chk.value] = '';
			pw_refuse.note(chk).show();
			rfu_sel.onchange=function(e){chk.parentNode.parentNode.getElementsByTagName("span")[0].innerHTML=uncheck_users[chk.value] = this.options[this.selectedIndex].value;pw_refuse.hide();}
		}
		stat_desc.innerHTML = stat_format.replace("%d", num)
			.replace("%d", amount).replace("%d", 0 == num ? 0 : amount/num);
		if(0 == num && chkbox.checked){
			chkbox.checked = false;
			return;
		}
	}
	boxes = boxes.length ? boxes : [boxes];
	for(i=0; i<boxes.length; i++){
		if(!init){
			if(boxes[i].checked != chkbox.checked){
				boxes[i].checked = chkbox.checked;
				_chxclick(boxes[i]);
			}
			else boxes[i].checked = chkbox.checked;
		}else boxes[i].onclick = function(e){_chxclick(this);event.cancelBubble =true;}
	}
}
_checkall(document.getElementById("IDI_CHK_ALL"), document.form_withdraw, true);

function _withdraw(form, link){
	var k = null, inp;
	for(k in uncheck_users){
		inp = document.createElement("input");
        inp.type = "hidden";
        inp.name = "refuse_uid[]";
        inp.value = k+':'+(uncheck_users[k].length > 0 ? uncheck_users[k] : rfu_sel.options[rfu_sel.options.length-1]);
        form.appendChild(inp);
	}
	if(num > 0 || k != null){
		form.submit();
		_withdraw_pop();
	}
}
function _withdraw_pop(msg){
	var body="<div class=res-btn><a class=succ href='?resp_code=1'><?php echo $mbs_appenv->lang('I_have_been_payed')?></a><a class=err href='?resp_code=0'><?php echo $mbs_appenv->lang('some_wrongs_happened')?></a></div>";
	if(msg) body += "<p style='text-align:center;'>"+msg+"</p>";
	var pw = popwin("", body).mask().noclose();
	pw.className += " pw-pay";
	pw.show();
}
<?php if($popwin_select_res){?>
_withdraw_pop("<?php echo $popwin_select_res?>");
<?php }?>
<?php }else{ ?>
var pw_detail = pw_refuse = popwin("<?php echo $mbs_appenv->lang(array('withdraw', 'detail'))?>", "<iframe src=''></iframe>").mask(),
	frame = pw_detail.getElementsByTagName("iframe")[0];
pw_detail.className += " pw-bat";
pw_detail.childNodes[0].className = "pop_tit";
function _detail(batno){
	frame.src = "?bat_detail="+batno;
	pw_detail.show();
}
<?php } ?>
</script>

</body>
</html>