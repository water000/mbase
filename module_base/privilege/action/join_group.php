<?php

use modbase\common\CDbPool,
    modbase\common\CMemcachedPool,
    modbase\core\CModDef,
    modbase\user\CUserInfoCtr,
    modbase\user\CUserSession,
    modbase\privilege\CPrivUserControl,
    modbase\privilege\CPrivGroupControl;

$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
$priv_info = null;
if(empty($error)){
	try {
		
		$pg = CPrivGroupControl::getInstance($mbs_appenv, 
				CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['group_id']);
		$priv_info = $pg->get();
		if(empty($priv_info)){
			$mbs_appenv->echoex('invalid group_id', 'PRIV_JOIN_REQ_INVALID');
			exit(0);
		}
		
		
		$pu = CPrivUserControl::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['group_id']);
		
		if(isset($_REQUEST['del'])){
			foreach($_REQUEST['del'] as $uid){
				$pu->setSecondKey($uid);
				$pu->delNode();
			}
		}
		else if(isset($_REQUEST['join'])){
			$us = new CUserSession();
			list($user_id, ) = $us->get();
			foreach($_REQUEST['join'] as $uid){
			    $arr = array(
					'priv_group_id' => $_REQUEST['group_id'],
					'user_id'       => $uid,
					'creator_id'    => $user_id,
					'join_ts'       => time()
				);
				$ret = $pu->addNode($arr);
			}
		}
		
		$pu_list = $pu->get();
		
		$usr = CUserInfoCtr::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance());
	} catch (\Exception $e) {
	    $error[] = sprintf($mbs_appenv->lang('user_exsits'), $uid);
		//$error[] = $e->getMessage();
	}
	
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('reset.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('style.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('iconfont.css')?>" rel="stylesheet">
<style type="text/css">
.tab span{color:green; margin-right:20px;}
.sm_table th, .sm_table td{padding:8px 6px;}
.recharge{height: 26px;line-height: 26px;font-size: 12px;}
.pw-bat{width:75%;left:12%;}
.pw-bat .bd{overflow:hidden;}
</style>
</head>
<body>
<div class="container">
    <div class="tab clearfix">
    	<h3 class="tit fl"><?php echo $mbs_cur_actiondef[CModDef::P_TLE]?></h3>
		<a href="javascript:;" onclick="top._history_back(1);" class="btn_back ml15 fr">&lt;<?php echo $mbs_appenv->lang('back')?></a>
    </div>
    <div class="tab clearfix" style="border-bottom: 0;padding: 26px 0 0;">
    	<?php echo $mbs_appenv->lang(array('group', 'name')), '&nbsp;:&nbsp;<span>', $priv_info['name'], '</span>'?>
		<?php echo $mbs_appenv->lang(array('member', 'num')), '&nbsp;:&nbsp;<span>', count($pu_list), $mbs_appenv->lang('person'), '</span>'?>
		<a href="javascript:;" onclick="_add_member(this)" class="recharge fr">+<?php echo $mbs_appenv->lang(array('add', 'member'))?></a>
		
    </div>
<?php if(!empty($error)){ ?>
<div class=error><p><?php echo implode('<br/>', $error)?></p>
<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
</div>
<?php }else if(isset($_REQUEST['del']) || isset($_REQUEST['join'])){?>
<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?>
	<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a></div>
<?php }?>
	<table class="sm_table" style="margin-top:10px;" cellspacing="0" cellpadding="0">
	    <thead>
	        <tr>
	            <th>#</th>
	            <th><?php echo $mbs_appenv->lang('name')?></th>
	            <th><?php echo $mbs_appenv->lang('join_ts')?></th>
	            <th><?php echo $mbs_appenv->lang('operation')?></th>
	        </tr>
	    </thead>
	    <tbody>
	    <?php foreach($pu_list as $k=>$row){ $usr->setPrimaryKey($row['user_id']); $uinfo=$usr->get();?>
	        <tr>
	            <td class="first-col"><?php echo $k+1?></td>
	            <td class=name><?php echo $usr->name();?></td>
	            <td><?php echo date('Y-m-d H:i:s', $row['join_ts'])?></td>
	            <td><form action="" method="post"><input type=hidden name="del[]" value="<?php echo $row['user_id']?>" />
	             <a href="#" onclick="if(confirm('<?php echo $mbs_appenv->lang('confirmed')?>')) this.parentNode.submit();"><?php echo $mbs_appenv->lang('remove')?></a></form></td>
	        </tr>
	    <?php }?>
	    </tbody>
	</table>
	
	<form action="" method="post" name="form_join">
	</form>
</div>
<script type="text/javascript" src="/static/js/global.js"></script>
<script type="text/javascript">
function _add_member(btn){
	if(!btn._pw) btn._pw = popwin("<?php echo $mbs_appenv->lang(array('select', 'member'))?>", 
		'<iframe style="width:100%;height:100%;" src="<?php echo $mbs_appenv->toURL('list', 'user')?>"></iframe>').mask();
	btn._pw.className += " pw-bat";
	btn._pw.childNodes[0].className = "pop_tit";
	btn._pw.show();
}
window.on_user_selected = function(arr){
	var inp;
	for(var i=0; i<arr.length; i++){
		inp = document.createElement("input");
		inp.type = "hidden";
		inp.name = "join[]";
		inp.value = arr[i][0];
		document.form_join.appendChild(inp);
	}
	document.form_join.submit();
}
window.onbeforeunload = function(e){
	window.top.on_user_selected = null;
}
</script>
</body>
</html>