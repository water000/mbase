<?php 

$mod_list = $mbs_appenv->getModList();
$selected_mod = $mod_list[0];
if(isset($_REQUEST['mod'])){
	if(!in_array($_REQUEST['mod'], $mod_list)){
		trigger_error('Invalid module selected', E_USER_ERROR);
	}
	$selected_mod = $_REQUEST['mod'];
}
$moddef = mbs_moddef($selected_mod);
$modinfo = $moddef->desc();
?>
<!doctype html>
<html>
<head>
<style type="text/css">
body{font-family:"Lucida Grande", "Lucida Sans Unicode", "STHeiti", "Helvetica","Arial","Verdana","sans-serif"}
body, p, td, ul{margin:0;padding:0;border:0;}
.header{height:40px;background: #252525; color:white;border-bottom: 1px solid #eee;}
.footer{height:40px;background: #fff;border-top: 1px solid #eee;clear:both;margin-top:50px;}
.warpper{width:100%;min-height:100%;background-color:#ddd;font-size:12px;}
.content{margin:0 auto;width:1000px;min-height:800px;}

.left{width:170px;float:left;margin-top:80px;border:1px solid #bbb;border-top:3px solid #85BBEF;background-color:#fff;}
.left p{font-size:12px; font-weight:bold; text-align:center;padding:6px 0; border-bottom:1px solid #ddd;}
.left a{display:block;font-size:14px;text-decoration:none;padding:3px 8px;border-bottom:1px solid #e0e0e0;}
.left a:hover{text-decoration:underline;}
.left a.current{background-color:#e0e0e0;font-weight:bold;}
.right{float:left;width:700px;padding:8px 13px;margin:20px;background-color:#fff;}
h2{color:#555;margin:0;text-align:center;}
table{width:100%;border:1px solid #666;margin-bottom:30px;}
table , ul{background-color:#fff;}
.right p{font-size:14px; font-weight:bold;padding:8px 3px;color:#555;}
tbody th, li.head{font-size:12px;padding:3px 0; font-weight:bold;text-align:center;background-color: #ccccff;border-bottom:1px solid #666;}
tbody td, ul li{border-bottom:1px solid #888;padding:3px;}
ul{float:left;width:120px;word-wrap:break-word;}
ul li{list-style-type:none;}
li.head{width:120px;}
.even{background-color:#eee}
</style>
</head>
<body>
<div class="warpper">
	<div class=header></div>
	<div class=content>
		<div class=left>
			<p>模块列表</p>
			<?php foreach($mod_list as $mod){?>
			<a href="<?=$mbs_appenv->toURL(RTM_MOD, RTM_ACTION, array('mod'=>$mod))?>" <?=$mod==$selected_mod?' class=current':''?>><?=$mod?></a>
			<?php }?>
		</div>
		<div class=right>
			<p><?=$moddef->lang(CModDef::MOD)?></p>
			<table cellspacing=0>
			<?php foreach($moddef->item(CModDef::MOD) as $key => $val){ ?>
			<tr><th><?=$moddef->lang($key)?></th><td><?=$val?></td></tr>
			<?php } ?>
			</table>
			<p><?=$moddef->lang(CModDef::TBDEF)?></p>
			<table cellspacing=0>
				<tr>
					<th><?=$moddef->lang(CModDef::G_NM)?></th>
					<th><?=$moddef->lang(CModDef::G_DC)?></th>
				</tr>
			<?php $n = 1; $tbdef=$moddef->item(CModDef::TBDEF); if(!empty($tbdef)){ foreach($tbdef as $key => $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>><td><?=$key?></td><td><?=CStrTools::txt2html(htmlspecialchars($val))?></td></tr>
			<?php }} ?>
			</table>
			<p><?=$moddef->lang(CModDef::TAG)?></p>
			<table cellspacing=0>
				<tr>
					<th><?=$moddef->lang(CModDef::G_NM)?></th>
					<th><?=$moddef->lang(CModDef::G_CS)?></th>
					<th><?=$moddef->lang(CModDef::G_DC)?></th>
				</tr>
			<?php $n = 1; $tag = $moddef->item(CModDef::TAG); if(!empty($tag)){ foreach($tag as $key => $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>><td><?=$key?></td><td><?=$val[CModDef::G_CS]?></td>
				<td><?=CStrTools::txt2html(htmlspecialchars($val[CModDef::G_DC]))?></td></tr>
			<?php }} ?>
			</table>
			<p><?=$moddef->lang(CModDef::FTR)?></p>
			<table cellspacing=0>
				<tr>
					<th><?=$moddef->lang(CModDef::G_NM)?></th>
					<th><?=$moddef->lang(CModDef::G_CS)?></th>
					<th><?=$moddef->lang(CModDef::G_DC)?></th>
				</tr>
			<?php $n = 1; $ftr=$moddef->item(CModDef::FTR); if(!empty($ftr)){foreach($ftr as $key => $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>><td><?=$key?></td><td><?=$val[CModDef::G_CS]?></td>
				<td><?=CStrTools::txt2html(htmlspecialchars($val[CModDef::G_DC]))?></td></tr>
			<?php }} ?>
			</table>
			<p><?=$moddef->lang(CModDef::LD_FTR)?></p>
			<table cellspacing=0>
				<tr>
					<th><?=$moddef->lang(CModDef::MOD)?></th>
					<th><?=$moddef->lang(CModDef::G_NM)?></th>
					<th>isExitOnFilterUndefined</th>
					<th>args</th>
				</tr>
			<?php $n = 1; $ftr=$moddef->item(CModDef::LD_FTR); if(!empty($ftr)){foreach($ftr as $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>></td><td><?=$val[0]?></td><td><?=$val[1]?></td>
				<td><?php echo isset($val[2])?$val[2]:''?></td><td><?php echo isset($val[3])?$val[3]:''?></td></tr>
			<?php }} ?>
			</table>
			<p>files</p>
			<?php 
			$dir = $mbs_appenv->getDir($selected_mod);
			$types = scandir($dir);
			$n = 1;
			foreach($types as $t){
				if('.' == $t[0])
					continue;
				$sub = array($t);
			?>
			<ul <?php echo 0 == $n++%2 ? 'class=even':''?>>
				<li class=head><?=$t?></li>
				<?php 
				//foreach($sub as $st){
				for($i=0; $i<count($sub); ++$i){
					$st = $sub[$i];
					$files = scandir($dir.$st);
					$pre = ($pos=strpos($st, '/')) !== false ? substr($st, $pos+1).'/':'';
					foreach($files as $f){
						if('.' == $f[0]);
						else if(is_dir($dir.$st.'/'.$f))
							$sub[] = $st.'/'.$f;
						else
							echo '<li>', $pre, $f, '</li>';
					}
				}
				?>
			</ul>
			<?php 
			}
			?>
		</div>
		<div style="clear: both"></div>
	</div>
	<div class=footer></div>
</div>
</body>
</html>