<?php 
$page_title = $mbs_appenv->lang(isset($_REQUEST['id']) ? 'edit_info' : 'record_info');

mbs_import('', 'CUserInfoCtr');

$user_def_pwd = '******';

if(isset($_REQUEST['password']) && $_REQUEST['password'] == $user_def_pwd){
	$_REQUEST['password'] = '';
}


$user = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');

if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
	$user_ins = CUserInfoCtr::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	
	if(isset($_REQUEST['delete'])){
		foreach($_REQUEST['id'] as $id){
			if($id > 3){
				$user_ins->setPrimaryKey(intval($id));
				$user_ins->destroy();
			}
		}
		$mbs_appenv->echoex($mbs_appenv->lang('operation_success'), '', $mbs_appenv->toURL('list'));
		exit(0);
	}
	
	$_REQUEST['id'] = intval($_REQUEST['id']);
	$user_ins->setPrimaryKey($_REQUEST['id']);
	
	$user_spec = $user_ins->get();
	if(empty($user_spec)){
	    $mbs_appenv->echoex('no such user', 'NO_USER');
	    exit(0);
	}
	
	if(isset($_REQUEST['name'])){
		$user = array_intersect_key($_REQUEST, $user);
		$exclude = array();
		if(!empty($user['password'])){
			$user['password']= CUserInfoCtr::passwordFormat($user['password']);
		}else{
			unset($user['password']);
			$exclude[] = 'password';
		}
		$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'), $exclude);
		if(empty($error)){
		    $diff = array_diff_assoc($user, $user_spec);
		    if(!empty($diff)){
		        $user = $diff + $user_spec;
    			try {
    			    $ret = $user_ins->set($user);
    			} catch (Exception $e) {
    			    if($e->getCode() == $mbs_appenv->config('PDO_ER_DUP_ENTRY', 'common')){
    			        $error[] = $mbs_appenv->lang('existed'. 'common').':'.$_REQUEST['phone'];
    			        $user = $user_spec;
    			    }else{
    			        throw $e;
    			    }
    			}
		    }
		    if(!isset($user['password'])){
		        $user['password'] = $user_def_pwd;
		    }
		}
	}else{
	    $user = array_intersect_key($user_spec, $user);
	}
}
else if(isset($_REQUEST['__timeline'])){
	$new_user = array_intersect_key($_REQUEST, $user);
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	if(empty($error)){
		$user_ins = CUserInfoCtr::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$new_user['password'] = CUserInfoCtr::passwordFormat($new_user['password']);
		$new_user['reg_ts'] = time();
		try {
		    $ret = $user_ins->add($new_user);
		} catch (Exception $e) {
		    if($e->getCode() == $mbs_appenv->config('PDO_ER_DUP_ENTRY', 'common')){
		        $error[] = $mbs_appenv->lang('existed'. 'common').':'.$_REQUEST['phone'];
		        $user = $new_user;
		    }else{
		        throw $e;
		    }
		}
		
	}else{
		$user = $new_user;
	}
}else{
	$user = array_merge($user, array_intersect_key($_REQUEST, $user));
}

if(!empty($user['password'])){
	$user['password'] = $user_def_pwd;
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0">
<title><?php mbs_title()?></title>
<!--[if lt ie 9]>
	<script>
		document.createElement("article");
		document.createElement("section");
		document.createElement("aside");
		document.createElement("footer");
		document.createElement("header");
		document.createElement("nav");
</script>
<![endif]-->
<link href="<?php echo $mbs_appenv->sURL('reset.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('style.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('iconfont.css')?>" rel="stylesheet">
<style type="text/css">
select{width: 360px !important;}
.form_layer {margin-top:1.5%;}
</style>
</head>
<body>
<?php if(isset($_REQUEST['__timeline'])){ if(!empty($error)){ ?>
<div class=error><p><?php echo implode('<br/>', $error)?></p>
<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
</div>
<?php }else {?>
<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common'), 'UID:', $ret?>
	<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a></div>
<script type="text/javascript">
if(window.top._on_user_create)
	window.top._on_user_create(<?php echo $ret?>);
</script>
<?php }}?>

<form name="_form" method="post" style="margin:0 3%;">
	<input type="hidden" name="__timeline" value="<?php echo time()?>" />
	<input type="hidden" name="id" value="<?php echo isset($_REQUEST['id'])?intval($_REQUEST['id']):0?>" />
    <div class="form_div" style="padding:2% 1%;">
		<div class="form_layer clearfix">
			<label><font class="red pr5">*</font><?php echo $mbs_appenv->lang('name')?>：</label>
			<input type="text" class="sm_inp large fl" name="name" value="<?php echo $user['name']?>" 
	    	     placeholder="<?php echo $mbs_appenv->lang('please_input')?>" required />
		</div>
		<div class="form_layer clearfix">
			<label><font class="red pr5">*</font><?php echo $mbs_appenv->lang('phone')?>：</label>
			<input type="text" name="phone" class="sm_inp large fl" placeholder="" value="<?php echo $user['phone']?>" />
		</div>
		<div class="form_layer clearfix">
			<label><font class="red pr5">*</font><?php echo $mbs_appenv->lang('password')?>：</label>
			<input type="text" name="password" class="sm_inp large fl" placeholder="" value="<?php echo $user['password']?>" />
		</div>
		<div class="form_layer clearfix">
			<label><font class="red pr5"></font><?php echo $mbs_appenv->lang('gender')?>：</label>
			<select name="gender" class="sm_inp large fl" style="height:auto;">
			<?php foreach($mbs_appenv->config('gender_list') as $k => $v){?>
			   <option value="<?php echo $k?>" <?php echo $k==$user['gender']?'selected':''?>><?php echo $v?></option>
			<?php }?>
			</select>
		</div>
		<div class="form_layer clearfix">
			<label><font class="red pr5"></font><?php echo $mbs_appenv->lang('career')?>：</label>
			<select name="career_cid" class="sm_inp large fl" style="height:auto;">
			<?php foreach($mbs_appenv->config('career_list') as $k => $v){?>
			   <option value="<?php echo $k?>" <?php echo $k==$user['career_cid']?'selected':''?>><?php echo $v?></option>
			<?php }?>
			</select>
		</div>
		<div class="form_layer clearfix">
			<label><font class="red pr5"></font><?php echo $mbs_appenv->lang('email')?>：</label>
			<input name="email" type="text" class="sm_inp large fl" placeholder="" value="<?php echo $user['email']?>" />
		</div>
		<div class="btn_form">
			<a href="javascript:document._form.submit();" class="blue"><?php echo $mbs_appenv->lang('submit')?></a>
			<a href="javascript:top._history_back(1);" class="white"><?php echo $mbs_appenv->lang('cancel')?></a>
		</div>
	</div>
</form>
</body>
</html>