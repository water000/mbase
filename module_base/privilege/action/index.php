<?php 

use modbase\common\CDbPool,
    modbase\common\CMemcachedPool,
    modbase\core\CModDef,
    modbase\user\CUserSession, 
    modbase\user\CUserInfoCtr,
    modbase\privilege\CPrivUserControl,
    modbase\privilege\CPrivGroupControl;
    
$us = new CUserSession();
$user_id = $us->checkLogin();
if(empty($user_id)){
	echo $us->getError();
	exit(0);
}
$user_ctr = CUserInfoCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance(), $user_id);
$uinfo = $user_ctr->get();

$priv_info = null;

$pu = CPrivUserControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$priv_info = $pu->getDB()->search(array('user_id' => $user_id));

if(empty($priv_info) || !($priv_info = $priv_info->fetchAll(PDO::FETCH_ASSOC))){
	echo 'access denied(1)';
	exit(0);
}
$priv_info = $priv_info[0];

$pg = CPrivGroupControl::getInstance($mbs_appenv, CDbPool::getInstance(), 
	CMemcachedPool::getInstance(), $priv_info['priv_group_id']);
$priv_list = $pg->get();
if(empty($priv_list)){
	echo 'access denied(2)';
	exit(0);
}

$priv_group = CPrivGroupControl::decodePrivList($priv_list['priv_list']);

$mod_entry = array(
    'user.list' => '&#xe60e;',
	'privilege.group_list'   => '&#xe602;',
);

function _fn_icon($mod, $ac){
    global $mod_entry;
	echo isset($mod_entry[$mod.'.'.$ac]) ?  '<i class="iconfont">'. $mod_entry[$mod.'.'.$ac]. '</i>' : '';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
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
	<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('zebra-dialog/zebra_dialog.css')?>" />
	<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('style.css')?>" />
	<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" />
	<link href="<?php echo $mbs_appenv->sURL('iconfont.css')?>" rel="stylesheet">
	<style type="text/css">
	iframe{width:100%;border:0;}
	.myclass td.col1{font-size:12px;font-weight:bold;}
	.myclass td.col2{padding-left:15px;cursor:pointer;color:rgb(20, 90,170);}
	.myclass td.col2 b{color:red;padding-right:3px;}
	.myclass td.col2 a{text-decoration:none;}
	.myclass td.col2 a:hover{text-decoration:underline;}
	
	/*
	 * .tab li{padding-right: 15px; margin-right: 15px; border-right: 1px solid #dedede;}
	 * .nav a:hover=>.nav a.on, .nav a:hover
	 * .section_ul li:hover => .section_ul li.on, .section_ul li:hover
	 * .section_ul li{padding:15px} => .section_ul li{padding:10px}
	*/
	</style>
</head>
<body style="overflow:hidden;">
<div class="sm_left" style="background: #1b3b6a;">
		<div class="logo2"></div>
		<div class="nav">
<?php 
foreach($mod_entry as $ma => $font){
    list($mod, $ac) = explode('.', $ma, 2);
    if(!$pg->privExists($mod, $ac)) continue;
    $moddef=mbs_moddef($mod);
    if(empty($moddef)) continue;
    $def = $moddef->item(CModDef::PAGES, $ac);
    if(empty($def)) continue;
?>
<a href="#" data="<?php echo $mbs_appenv->toURL($ac, $mod)?>" onclick="_to(this)">
			<i class="iconfont"><?php echo $font?></i><?php echo $def[CModDef::P_TLE]?></a>
<?php
}


$mgr_notify_list = array();

$mod_list = $pg->topmost() ? 
	$mbs_appenv->getModList() : $pg->privMod();
foreach($mod_list as $mod){
	$moddef=mbs_moddef($mod);
	if(empty($moddef)) continue;
	$actions = $moddef->filterActions(CModDef::P_MGNF);
	foreach($actions as $ac => $def){
	    if($pg->privExists($mod, $ac)) 
	        $mgr_notify_list[$mod.'.'.$ac] = $def[CModDef::P_MGNF];;
	}
}
?>
	</div>
</div>
<!-- 左边栏end -->
<!-- 内容主体 -->
<div class="sm_right">
	<div id=IDD_TOP class="top"><?php echo $mbs_appenv->lang('welcome'), $uinfo['name']?>
	   <a href="<?php echo $mbs_appenv->toURL('logout', 'user')?>"><?php echo $mbs_appenv->lang('logout')?></a></div>
	<iframe src=""></iframe>
</div>
<!-- 内容主体end -->

<!-- 加载jquery.js -->
<!--[if ie 6]>
<script src="<?php echo $mbs_appenv->sURL('jquery-1.10.2.js')?>"></script>
<script src="<?php echo $mbs_appenv->sURL('fixIE6.js')?>"></script>
<![endif]-->

<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery-1.10.2.js')?>"></script>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('zebra_dialog.js')?>"></script>

<script type="text/javascript">

var frame = document.getElementsByTagName("iframe")[0], prev = null, visit_actions = [];
var links = document.getElementsByTagName("a"), i, j=0, firstlink=null, history_stack=[];

frame.style.height=(document.body.clientHeight-document.getElementById("IDD_TOP").offsetHeight-5)+"px";
window.onresize = function(){
	frame.style.height=(document.body.clientHeight-document.getElementById("IDD_TOP").offsetHeight-5)+"px";
}

for(i=0; i<links.length; i++){
	if(links[i].getAttribute("data") != null){
		if(document.location.search.indexOf("to=") != -1){
			if(document.location.href.indexOf(encodeURIComponent(links[i].getAttribute("data"))) != -1){
				_to.call(links[i], decodeURIComponent(document.location.search.substr(4)));
				break;
			}
		}
		else{
			_to(links[i]);
			break;
		}
	}
}

window._history_back=function(id){
	if(0 == arguments.length) id = 1;
	for(var i=0; i<id && history_stack.length>0; i++) history_stack.pop();
	if(history_stack.length>0) frame.src=history_stack.pop();
}

function _to(link, is_redirect){
	var url;
	
	if("object" == typeof link){
		url = link.getAttribute("data");
	}else{
		url = link;
		link = this;
	}

	is_redirect = 1 == arguments.length ? true : is_redirect;
	
	if(prev != null){
		prev.className = prev.className.replace(" on", "");
	}
	link.className += " on";
	prev = link;
	
	if(is_redirect){
		frame.contentWindow.document.body.innerHTML = "Loading...";
		frame.src = url;
	}
}

frame.onreadystatechange = frame.onload = function(e){ //onload: for chrom
	if (frame.contentWindow.document.readyState=="complete"){
		//frame.style.height=(document.getElementsByTagName("html")[0].clientHeight-5)+"px";
		document.title = frame.contentWindow.document.title;
		history.pushState(null, null, "<?php echo $mbs_appenv->item('cur_action_url') ?>?to="
				+encodeURIComponent( frame.contentWindow.location.href));

		if(history_stack.length > 0 && history_stack[history_stack.length-1] == frame.contentWindow.location.href)
			;
		else
			history_stack.push(frame.contentWindow.location.href);
		//frame.contentWindow.document.body.onclick = function(e){
		//	if(prev)
		//		prev.className = "blur_a";
		//}
		
		if( -1 == frame.contentWindow.document.location.href.indexOf(prev.getAttribute("data")) ){
			for(var i=0; i<links.length; i++){
				if(frame.contentWindow.document.location.href.indexOf(links[i].getAttribute("data")) != -1){
					_to(links[i], false);
					break;
				}
			}
			if(i == links.length && frame.contentWindow.document.location.href.indexOf("<?php echo $mbs_appenv->toURL('login', 'user')?>")!=-1){
				document.location = frame.contentWindow.document.location.href;
			}
		}

		document.onkeydown = frame.contentWindow.document.onkeydown = function(e){
			e = e || this.parentWindow.event;
			if(116 == (e.keyCode || e.which)){ // forriden F5 key in parent window
				frame.contentWindow.document.location.reload();
				e.returnValue = false;
				e.cancelBubble = true;
				e.keyCode = 0;
				return false;
			}
		}
	}
}

var _zbdlg, _body, item_map={};
function _init_dialog(){
	_zbdlg = new $.Zebra_Dialog("<table id=IDT_ZDDLG></table>", {
		'title': '<?php echo $mbs_appenv->lang('mgr_msg_notify')?>',
		 'custom_class':  'myclass',
	    'buttons':  false,
	    'modal': false,
	    'type':     'question',
	    'position': ['right - 20', 'bottom - 20'],
	    'onClose' : function(){_zbdlg = null;item_map={};}
	});
	_body = document.getElementById("IDT_ZDDLG");
}
var _click = function(url, obj, id){
	frame.src = url;
	obj.parentNode.parentNode.parentNode.removeChild(obj.parentNode.parentNode);
	delete item_map[id];
	if(0 == _body.rows.length){
		_zbdlg.close();
	}
}
function _handle(data){
	if('SUCCESS' == data.retcode && data.data.length > 0){
		if(!_zbdlg)
			_init_dialog();
		var tr;
		for(var i=0; i<data.data.length; i++){
			if(item_map[data.data[i].id]){
				tr = item_map[data.data[i].id];
				tr.innerHTML = "";
			}else{
				tr = _body.insertRow();
				item_map[data.data[i].id] = tr;
			}
			tr.insertCell().innerHTML = data.data[i].title;
			tr.insertCell().innerHTML = "<a href='#' onclick='_click(\""+data.data[i].redirect
				+"\", this, \""+data.data[i].id+"\")'>"+data.data[i].html+"</a>";
			tr.cells[0].className = "col1";
			tr.cells[1].className = "col2";
		}
	}
}
<?php foreach($mgr_notify_list as $ac => $interval){ list($mod, $ac) = explode('.', $ac, 2); ?>
setInterval(function(){
	$.ajax({url:"<?php echo $mbs_appenv->toURL($ac, $mod)?>", headers:{Accept:"application/json"}, dataType:"json", success:_handle});
}, <?php echo (is_integer($interval) ? $interval : CModDef::MGR_NOTIFY_INTERVAL_SEC)*1000?>);
<?php } ?>
</script>
</body>
</html>