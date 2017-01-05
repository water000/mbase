<?php 

use modbase\common\CStrTools,
    modbase\common\CDbPool,
    modbase\common\CMemcachedPool,
    modbase\common\CImage,
    modbase\common\CTools,
    modbase\core\CModDef,
    modbase\user\CUserSession,
    modbase\user\CUserInfoCtr;

$user_ins = CUserInfoCtr::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());

$search_keys = array('name'=>'', 'phone'=>'', 'id'=>'', 'career_cid'=>'-1');
if(isset($_REQUEST['career_cid']) && '-1' == $_REQUEST['career_cid'])
    unset($_REQUEST['career_cid']);
$req_search_keys = array_intersect_key($_REQUEST, $search_keys);
foreach($req_search_keys as $k=> &$v){
	$v = trim($v);
	if(0 == strlen($v)){
		unset($req_search_keys[$k]);
	}
}

$usersess = new CUserSession();
list($sess_uid, ) = $usersess->get();

$search_keys = array_merge($search_keys, $req_search_keys);

define('ROWS_PER_PAGE', 20);
define('PAGE_ID',  isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1);
define('ROWS_OFFSET', (PAGE_ID-1)*ROWS_PER_PAGE);
$count = $user_ins->getDB()->count($req_search_keys);
$list = array();
$pageno = array();
if($count > ROWS_OFFSET){
	$opts = array(
		'offset' => ROWS_OFFSET,
		'limit'  => ROWS_PER_PAGE,
		'order'  => ' id desc',
	);
	$list = $user_ins->getDB()->search($req_search_keys, $opts);

	if($count > ROWS_PER_PAGE){
	   $pageno = CTools::genPagination(PAGE_ID, ceil($count/ROWS_PER_PAGE));
	}
}

$img_thumb = new CImage(CUserInfoCtr::AVATAR_SUBDIR);

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
.uavatar{border-radius:50%;border:0;width:40px;height:40px;}
.no-data{text-align:center !important;}
</style>
</head>
<body>
<div class="container">
    <div class="tab clearfix">
    	<h3 class="tit fl"><?php echo $mbs_cur_actiondef[CModDef::P_TLE]?></h3>
		<div class="fr">
			<a href="<?php echo $mbs_appenv->toURL('edit')?>" class="recharge">+ <?php echo $mbs_appenv->lang(array('add', 'user'))?></a>
		</div>
    </div>
    <form name=myform action="" method="post">
        <div class="tab_div">
        	<div class="mt20 clearfix">
        		<div class="refer_left fl">
        			<label><?php echo $mbs_appenv->lang('name')?>: </label>
        			<input type="text" name="name" class="sm_inp small fl" value="<?php echo htmlspecialchars($search_keys['name'])?>" />
        		</div>
        		<div class="refer_left fl">
        			<label><?php echo $mbs_appenv->lang('phone')?>：</label>
        			<input type="text" name="phone" class="sm_inp small  fl" style="width: 150px;" value="<?php echo htmlspecialchars($search_keys['phone'])?>" />
        		</div>
        		<div class="refer_left fl">
        			<label>ID：</label>
        			<input type="text" name="id" class="sm_inp small  fl" style="width: 150px;" value="<?php echo htmlspecialchars($search_keys['id'])?>" />
        		</div>
        		<div class="refer_left fl">
            		<label><?php echo $mbs_appenv->lang('career')?>：</label>
            		<select name=career_cid class="sm_inp small" style="height: auto;">
            		  <option value="-1"></option>
            		  <?php foreach($mbs_appenv->config('career_list') as $k1 => $v1){  ?>
            		  <option value=<?php echo $k1?> <?php echo $k1==intval($search_keys['career_cid']) ? ' selected':''?>><?php echo $v1?></option>
            		  <?php } ?>
            		</select>
            	</div>
            	<a id=IDA_REQ href="javascript:document.myform.submit();" class="inquire fl"><?php echo $mbs_appenv->lang('search')?></a>
            </div>
        </div>
    </form>
    <table class="sm_table" cellspacing="0" cellpadding="0">
	    <thead>
	        <tr>
	            <th><input type="checkbox" onclick="_checkall(this, document.form_list)" /></th>
	            <th>ID</th>
	            <th><?php echo $mbs_appenv->lang('name')?></th>
	            <th><?php echo $mbs_appenv->lang('phone')?></th>
	            <th><?php echo $mbs_appenv->lang('career')?></th>
	            <th><?php echo $mbs_appenv->lang('email')?></th>
	            <th><?php echo $mbs_appenv->lang('operation')?></th>
	        </tr>
	    </thead>
	    <tbody>
	    	<?php 
	    	$k=-1;
	    	$career_cfg = $mbs_appenv->config('career_list');
	    	foreach($list as $k => $row){ ?>
	        <tr>
	            <td><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /></td>
	            <td><?php echo $row['id']?></td>
	            <td class=name><?php echo CStrTools::txt2html($row['name'])?><?php if(!empty($row['avatar_path'])){?>
	               <img class=uavatar src="<?php echo $mbs_appenv->uploadURL($img_thumb->completePath($row['avatar_path'], 'small')) ?>" /><?php }?>
	            </td>
	            <td><?php echo $row['phone']?></td>
	            <td><?php echo $career_cfg[$row['career_cid']]?></td>
	            <td><?php echo $row['email']?></td>
	            <td><a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$row['id']))?>"><?php echo $mbs_appenv->lang(array('edit'))?></a>
	               <a href="<?php echo $mbs_appenv->toURL('history', 'wallet', array('uid'=>$row['id']))?>"><?php echo $mbs_appenv->lang('wallet')?></a></td>
	        </tr>
	     	<?php } if(-1 == $k){ ?>
	     	<tr><td colspan=5 class=no-data><?php echo $mbs_appenv->lang('no_data', 'common')?> !</td></tr>
	     	<?php }?>
	      </tbody>
	</table>
	<div class="btn_all clearfix">
	   <div class="batch fl" id=IDD_VFY>
			<a href="#" id="IDA_BTN_CONFIRM" class="pltg" style="display: none"><?php echo $mbs_appenv->lang(array('confirm', 'select'))?></a>
		</div>
	   <div id=IDA_BTN_DEL class="fl"><?php echo sprintf($mbs_appenv->lang('page_num_count_format'), $count)?></div>
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
<script type="text/javascript">
if(window.parent.on_user_selected){
	var big_title = document.getElementsByTagName("h3")[0];
	big_title.parentNode.parentNode.removeChild(big_title.parentNode);

	var tb = document.getElementsByTagName("table")[0],i;
	tb.className += " pop-table";
	for(i=0; i<tb.rows.length; i++){
		tb.rows[i].deleteCell(tb.rows[i].cells.length-1);
		tb.rows[i].deleteCell(tb.rows[i].cells.length-1);
	}

	var confirm_btn = document.getElementById("IDA_BTN_CONFIRM");
	confirm_btn.style.display = "";
	confirm_btn.onclick = function(e){
		var arr = [], chbox;
		for(i=0; i<tb.rows.length; i++){
			chbox = tb.rows[i].cells[0].getElementsByTagName("input")[0];
			if(chbox.checked){
				arr.push([chbox.value, tb.rows[i].cells[1].innerHTML, 
					tb.rows[i].cells[2].innerHTML]);
			}
		}
		if(arr.length > 0){
			window.parent.on_user_selected(arr);
		}
	}
}
function _checkall(chkbox, form){
	var i, boxes=form.elements["id[]"];
	boxes = boxes.length ? boxes : [boxes];
	for(i=0; i<boxes.length; i++){
		boxes[i].checked = chkbox.checked;
	}
}
function _req(form){
	if(form.elements["name"].value.length > 0 
		|| form.elements["phone"].value.length > 0
		|| form.elements["id"].value.length > 0)
		form.submit();
}
</script>
</body>
</html>