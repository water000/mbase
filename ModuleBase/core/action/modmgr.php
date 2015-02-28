<?php

if(count($_FILES) > 0 || count($_REQUEST) > 0){
	CFileType::import('common', 'CDbPool.php');
	CFileType::import('core', 'CCore.php', 'IModInstall.php', 'CModule.php', 'CFileParser.php', 'CMacroParser.php');
	$dbp = CDbPool::getInstance();
	$pdoconn = $dbp->getDefaultConnection();
	$oMod = new CModule($pdoconn);
	$oFilePsr = new CFileParser(new CMacroParser());
	
	if(isset($_FILES['newmod'])){
		$nerror = array();
		if(0 == $_FILES['newmod']['error']){
			if(stripos($_FILES['newmod']['name'], '.zip') > 0){
				$modinfo = $oMod->install($_FILES['newmod']['tmp_name'], $_FILES['newmod']['name'], $oFilePsr);
				$nerror = $oMod->getErrorMsg();
				//if(!$modinfo)
				//	array_unshift($nerror, 'ģ�鰲װʧ�ܣ�');
			}else $nerror[] = sprintf('unsupport file format "%s", only zip needed!', $_FILES['newmod']['name']);
		}else $nerror[] = sprintf('�ļ��ϴ�ʧ�ܣ�CODE��%d', $_FILES['newmod']['error']);
	}else if(isset($_FILES['updatemod'])){
		$uerror = array();
		if(0 == $_FILES['updatemod']['error']){
			$oMod->setModule($_REQUEST['mod']);
			$modinfo = $oMod->update($_FILES['updatemod']['tmp_name'], $_FILES['updatemod']['name'], $oFilePsr);
			$uerror = $oMod->getErrorMsg();
			//if(!$modinfo)
			//	array_unshift($uerror, 'ģ�����ʧ�ܣ�');
		}else $uerror[] = sprintf('�ļ��ϴ�ʧ�ܣ�CODE��%d', $_FILES['updatemod']['error']);
	}else if(isset($_REQUEST['delmod'])){
		$derror = array();
		$oMod->setModule($_REQUEST['delmod']);
		$oMod->delete();
		$derror = $oMod->getErrorMsg();
	}
}

?>
<!doctype html>
<html>
<head>
<title>ģ�����</title>
<link href="#NTAG_CALL(core,url,common, common.css)" rel="stylesheet" type="text/css"  />
<style type="text/css">
table{width:100%;margin:5px 0px 5px;}
caption{font-size:16px;font-weight:bold;padding:5px;}
td,th{padding:5px 0;}
td{padding-left:5px;}
th{border-right:1px solid #ddd;}
.bg{background-color:#ddc;}
fieldset{margin:30px 0;}
input{height:25px;padding:0 10px;}
</style>
</head>
<body>
<div class="wrap">
	<div class="main">
		<form action="#NTAG_CALL(core,url,core,modmgr)" method="post" enctype="multipart/form-data" onsubmit="return this.newmod.value!='';">
		<fieldset>
			<legend><b>��װ��ģ��</b></legend>
			<?php if(isset($_FILES['newmod'])){  ?>
			<p><b>��װ�����<?php echo empty($modinfo)?'ʧ��':'�ɹ�'?></b></p>
			<?php if(count($nerror) > 0){ ?>
			<ul>
				<?php foreach($nerror as $err){ ?>
				<li style="color:red;"><?php echo htmlspecialchars($err)?></li>
				<?php } ?>
			</ul>
			<?php } }?>
			<div style="margin:10px;"><input type="file" name="newmod" value="" /><input type="submit" value="�ύ" /></div>
		</fieldset>
		</form>
		<fieldset>
			<legend><b>�Ѱ�װģ��</b></legend>
			<?php if(isset($_FILES['updatemod'])){  ?>
			<p><b>���½����<?php echo empty($modinfo)?'ʧ��':'�ɹ�'?></b></p>
			<?php if(count($uerror) > 0){ ?>
			<ul>
				<?php foreach($uerror as $err){ ?>
				<li style="color:red;"><?php echo htmlspecialchars($err)?></li>
				<?php } } ?>
			</ul>
			<?php }else if(isset($_REQUEST['demod'])){ ?>
			<p><b>ɾ���ɹ�</b></p>
			<?php if(count($derror) > 0){ ?>
			<ul>
				<?php foreach($derror as $err){ ?>
				<li style="color:red;"><?php echo htmlspecialchars($err)?></li>
				<?php } } ?>
			</ul>
			<?php } ?>
			<table>
			<?php $modlist = CFileType::getModules(); $i=0; foreach($modlist as $mod){ ++$i; ?>
				<tr <?php echo (0 == $i % 2)?'class=bg':''?>>
					<td><a href="#NTAG_CALL(core,url,core,detail)&mod=<?php echo $mod?>"><?php echo $mod?></a></td>
					<td><?php echo date('Y-m-d H:i:s', filemtime(CFileType::getDir(CFileType::ENV_COMPILE, $mod, '')))?></td>
					<td>
						<form action="" method="post" enctype="multipart/form-data" onsubmit="return this.updatemod.value!='';">
							<input type="hidden" name="mod" value="<?php echo $mod?>" />
							<input type="file" name="updatemod" value="" /><input type="submit" value="�޸�" />
							<?php if(!in_array($mod, array('core', 'common'))){ ?>&nbsp;|&nbsp;<a href="#NTAG_CALL(core,url,core,modmgr)&delmod=<?php echo $mod?>">ɾ��</a><?php } ?>
						</form>
					</td></tr>
			<?php } ?>
			</table>
		</fieldset>
	</div>
</div>
</body>
</html>