<?php

use modbase\common\CStrTools,
    modbase\common\CDbPool,
    modbase\common\CMemcachedPool,
    modbase\core\CModDef,
    modbase\user\CUserInfoCtr,
    modbase\privilege\CPrivUserControl,
    modbase\privilege\CPrivGroupControl;

$priv_group = CPrivGroupControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$all = $priv_group->getDB()->listAll()->fetchAll();

$user_ctr = CUserInfoCtr::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$priv_user_ctr = CPrivUserControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$page_num_list = array();
?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('reset.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('style.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('iconfont.css')?>" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('ui.daterangepicker.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('jquery-ui-1.7.1.custom.css')?>" type="text/css" title="ui-theme" />
<style type="text/css">
.tab li a{color: #333;}
select{padding:3px;}
.ing{background-color:gray;}
.sm_table th, .sm_table td{padding:8px 6px;}
</style>
</head>
<body>
<div class="container">
    <div class="tab clearfix">
    	<h3 class="tit fl"><?php echo $mbs_cur_actiondef[CModDef::P_TLE]?></h3>
		<div class="fr">
			<a href="<?php echo $mbs_appenv->toURL('edit_group')?>" class="recharge">+ <?php echo $mbs_appenv->lang('create_group')?></a>
		</div>
    </div>
    <div class="btn_all clearfix">
        <div class="fl" style="font-size:15px;"><?php echo sprintf($mbs_appenv->lang('page_num_count_format'), count($all))?></div>
    </div>
    <form name="form_list" action="<?php echo $mbs_appenv->toURL('edit_group', '', array('delete'=>1))?>" method="post" >
		<table class="sm_table" cellspacing="0" cellpadding="0">
		    <thead>
		        <tr>
		            <th class="first-col col-chbox"></th>
		            <th><?php echo $mbs_appenv->lang('group_name')?></th>
					<th><?php echo $mbs_appenv->lang('creator')?></th>
					<th><?php echo $mbs_appenv->lang('create_time')?></th>
					<th><?php echo $mbs_appenv->lang('group_type')?></th>
					<th style="width:30%"><?php echo $mbs_appenv->lang('priv_list')?></th>
		            <th><?php echo $mbs_appenv->lang('member')?></th>
		            <th><?php echo $mbs_appenv->lang('operation')?></th>
		        </tr>
		    </thead>
		    <tbody>
		    	<?php 
		    	$k=-1;
		    	foreach($all as $k => $row){ $priv_user_ctr->setPrimaryKey($row['id']);?>
		        <tr >
		            <td class="first-col"><?php if($row['id'] != 1){?><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /><?php }?></td>
		            <td class=name><?php echo CStrTools::txt2html($row['name'])?></td>
				<td><?php if($row['creator_id'] != 0){$user_ctr->setPrimaryKey($row['creator_id']); $uinfo=$user_ctr->get(); echo empty($uinfo)?'(delete)':$uinfo['name'];}?></td>
				<td><?php echo date('Y-m-d', $row['create_ts'])?></td>
				<td><?php echo $mbs_appenv->lang($row['type'] == CPrivGroupControl::TYPE_ALLOW ? 'type_allow' : 'type_deny')?></td>
				<td>
<?php 
$modified_priv = array();
$priv_list = CPrivGroupControl::decodePrivList($row['priv_list']);
if(CPrivGroupControl::isTopmost($priv_list)){
	echo '<b>',$mbs_appenv->lang('topmost_group'), '</b>';
}else{
	$_moddef = null;
	echo '<ul>';
	foreach($priv_list as $mod => $actions){
		$_moddef = mbs_moddef($mod);
		if(empty($_moddef)){
			$modified_priv[$row['id']][] = $mod;
			continue;
		}
		echo '<li>', $_moddef->item(CModDef::MOD, CModDef::G_TL), '&nbsp;(&nbsp;';
		foreach($actions as $k => $action){
			if($k > 0){
				echo $mbs_appenv->lang('slash');
			}
			$ac = $_moddef->item(CModDef::PAGES, $action, CModDef::P_TLE);
			if(empty($ac)){
				$modified_priv[$row['id']][] = $mod.'.'.$action;
			}else{
				echo '<span>', $ac, '</span>';
			}
		}
		echo '&nbsp;)</li>';
	}
	echo '</ul>';
}
?>
				</td>
				<td><a class=total-person href="<?php echo $mbs_appenv->toURL('join_group', '', array('group_id'=>$row['id']))?>">
					<?php echo sprintf($mbs_appenv->lang('total_person'), $priv_user_ctr->getTotal())?></a></td>
				<td><a href="<?php echo $mbs_appenv->toURL('edit_group', '', array('group_id'=>$row['id']))?>">
					<?php echo $mbs_appenv->lang(array('edit'))?></a></td>
		        </tr>
		     	<?php } if(-1 == $k){ ?>
		     	<tr><td colspan=5 class=no-data><?php echo $mbs_appenv->lang('no_data', 'common')?></td></tr>
		     	<?php }?>
		      </tbody>
		</table>
	 <div class="btn_all clearfix">
		<div class="batch fl" id=IDD_VFY>
			<a href="javascript:if(confirm('<?php echo $mbs_appenv->lang('confirmed')?>')) document.form_list.submit();" class="plbh"><?php echo $mbs_appenv->lang('delete')?></a>
		</div>
		<div class="page fr" id="IDD_PAGE_LIST"></div>
	</div>
	</form>
</div>
</body>
</html>