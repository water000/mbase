<?php 

$priv_group = array(
	'core'      => array('mod_info', 'action_info'),
	'privilege' => array('index')
);


?>
<!doctype html>
<html>
<head>
<link href="<?=$mbs_appenv->getURL('core.css', 'core')?>" rel="stylesheet">
<style type="text/css">
iframe{width:100%;border:0;}
.actions{width:150px;padding:8px;position:fixed;left:10px;;bottom:20px;}
.actions div.groups{width:130px;}

.actions a{color:rgb(0,100,200);}
.actions a.mod{}
.actions a.mod span{margin-left:2px;color:#777;font-size:12px;}
.actions div.group{display:none;}
.actions div.group a{padding-left: 15px;}

</style>
</head>
<body>
<iframe scrolling=no src="/index.php?mod=privilege&m=core&a=mod_info" onload="this.height=this.contentWindow.document.body.scrollHeight;"></iframe>
<div class=actions>
	<div class="vertical-manu groups">
	<p class=title><?=$mbs_appenv->lang('mgrlist')?></p>
	<?php foreach($priv_group as $mod => $actions){ $moddef=mbs_moddef($mod);if(empty($moddef)) continue; ?>
	<a href="#" class=mod>
		<?=$moddef->item(CModDef::MOD, CModDef::G_TL)?><span>&gt;</span>
	</a><div class=group><?php foreach($actions as $ac){?>
		<a href="#" onclick="_to('<?=$mbs_appenv->toURL($ac, $mod)?>', this)"><?=$moddef->item(CModDef::PAGES, $ac, CModDef::P_TLE)?></a><?php }?></div>
	<?php } ?>
	</div>
</div>
<script type="text/javascript">
var links = document.getElementsByTagName("a"), i, visit_mod_list = [];
function _push_mod(mod){
	var i;
	for(i=0; i<visit_mod_list.length; i++){
		if(visit_mod_list[i] == mod){
			return;
		}
	}
	if(1 == visit_mod_list.length){
		var m = visit_mod_list.shift();
		m.style.display = "none";
	}
	visit_mod_list.push(mod);
}
function _pull_mod(mod){
	var i;
	for(i=0; i<visit_mod_list.length; i++){
		if(visit_mod_list[i] == mod){
			visit_mod_list.splice(i, 1);
			return;
		}
	}
}
for(i=0; i<links.length; i++){
	if("mod" == links[i].className){
		links[i].nextSibling.style.display = "none";
		links[i].onclick = function(e){
			if("none" == this.nextSibling.style.display){
				this.nextSibling.style.display = "block";
				_push_mod(this.nextSibling);
			}else{
				this.nextSibling.style.display = "none";
				_pull_mod(this.nextSibling);
			}
		}
	}
}
var frame = document.getElementsByTagName("iframe")[0], prev = null, visit_actions = [];
function _to(url, link, mod, ac){
	if(prev != null){
		prev.className = '';
	}
	frame.src = url;
	link.className = "cur";
	prev = link;
	
}
</script>
</body>
</html>