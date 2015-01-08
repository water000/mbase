<?php 

if(!CFileType::hasMod($_REQUEST['mod']))
	CCore::abort(sprintf('the module "%s" speciafied do not existed', $_REQUEST['mod']));
	
define('CUR_MOD', $_REQUEST['mod']);

$moddef = CFileType::getModDef(CUR_MOD);
if(!$moddef)
	CCore::abort('failed on loading moddef class');
$info = $moddef->desc();

$types = CFileType::getTypes();
$error = array();
if(count($_FILES) > 0){
	CFileType::import('core', 'CCore.php', 'IModInstall.php', 'CModule.php', 'CFileParser.php', 'CMacroParser.php');
	$dbp = CDbPool::getInstance();
	try {
		$pdoconn = $dbp->getDefaultConnection();
	} catch (Exception $e) {
		CCore::abort($e);
	}
	
	$oMod = new CModule($pdoconn, CUR_MOD);
	$oFilePsr = new CFileParser(new CMacroParser());
	
	foreach($_FILES as $name => $value){
		if($value['size'] > 0){
			list($type, $path) = explode('_', $name, 2);
			$path = str_replace('+', '.', $path);
			if(in_array($type, $types)){
				if(strpos($path, '..') === false){
					$pos = strrpos($path, '/');
					$fname = $pos === false ? $path : substr($path, $pos+1);
					if($fname == $value['name']){
						$realpath = CFileType::getPath(CFileType::ENV_COMPILE, CUR_MOD, $path, $type);
						if(file_exists($realpath)){
							if(move_uploaded_file($value['tmp_name'], $realpath)){
								$oMod->updateFile($path, $type, $realpath, $oFilePsr);
								$error += $oMod->getErrorMsg();
							}else $error[] = sprintf('unable to move uploaded file "%s"', $value['name']);
						}else $error[] = sprintf('file not found "%s"', $path);
					}else $error[] = sprintf('src "%s" is not equal to submited file "%s"', $fname, $value['name']);
				}else $error[] = sprintf('invalid file name "%s"', $path);
			}else $error[] = sprintf('invalid file type "%s"', $type);
		}
	}
}
else if(isset($_REQUEST['tb_edit_name'])){
	$tb = trim($_REQUEST['tb_edit_name']);
	if(isset($info[IModDef::TBDEF][$tb])){
		$edit_text = trim($_REQUEST['tb_edit_text']);
		if(!empty($edit_text)){
			$ntb = $mbs_appenv->formatTableName($tb);
			$count = 1;
			$edit_text = str_replace($tb, $ntb, $edit_text, $count);
			if(1 == $count){
				$dbp = CDbPool::getInstance();
				try {
					$pdoconn = $dbp->getDefaultConnection();
					$ret = $pdoconn->exec($edit_text);
					if(false === $ret){
						$ei = $pdoconn->errorInfo();
						$error[] = sprintf('edit_table_fail: %s: [%s] "%s"', $tb, $edit_text, $ei[2]);
					}
				} catch (Exception $e) {
					CCore::abort($e);
				}
			}else $error[] = sprintf('edit_table_fail: %s: no such table found in submit content', $tb);
		}
	}else $error[] = sprintf('no such table "%s"', $tb);
}
?>
<!doctype html>
<html>
<head>
<title>ģ�����</title>
<link href="#NTAG_CALL(core,url,common, common.css)" rel="stylesheet" type="text/css"  />
<style type="text/css">
table{width:100%;border-top:1px solid green;border-bottom:2px solid green;margin-top:30px;}
caption{font-size:16px;font-weight:bold;padding:5px;text-align:left;}
td,th{padding:2px 0;}
td{padding-left:5px;}
th{border-right:1px solid #ddd;}
input{height:23px;padding: 0 10px;}
.bg{background-color:#ddc;}
fieldset{padding:3px;}
ul li{font-size:14px;margin: 10px 15px;}
</style>
<script type="text/javascript">
function _submit(oForm){
	for(var i=0, j=oForm.elements.length; i<j; i++){
		if('file' == oForm.elements[i].type 
			&& oForm.elements[i].value != '')
			return true;
	}
	return false;
}
</script>
</head>
<body>
<div class="wrap">
	<div class="main">
		<div style="margin:20px 0;"><a href="#NTAG_CALL(core, url, core, modmgr)">module management</a> &nbsp;&gt;&nbsp; <b><?=CUR_MOD?></b></div>
		<div>
			<?php if( isset($_REQUEST['tb_edit_name']) || count($_FILES) > 0) {?>
			<fieldset>
				<legend><b>�޸Ľ��</b></legend>
				<ul>
			<?php if(count($error) > 0){ foreach($error as $e){ ?>
				<li style="color:red;"><?=htmlspecialchars($e)?></li>
			<?php }}else{ ?>
				<li>ȫ���ɹ�!</li>
			<?php } ?>
				</ul>
			</fieldset>
			<?php } ?>
			
			<?php if(isset($info[IModDef::MOD])){ ?>
			<table>
				<caption>������Ϣ</caption>
				<?php foreach($info[IModDef::MOD] as $key => $val){ ?>
				<tr><td width="40%"><?=$key?>:</td><td><?=htmlspecialchars($val)?></td></tr>
				<?php } ?>
			</table>
			<?php }?>
			<?php if(isset($info[IModDef::TBDEF])){ ?>
			<table>
				<caption>���ݿ���Ϣ</caption>
				<?php $i=0; foreach($info[IModDef::TBDEF] as $key => $val){ ++$i; ?>
				<tr <?=(0 == $i % 2)?'class=bg':''?>><td width="55%"><?=$key.str_replace(array("\n", "\t"), array('<br/>', '&nbsp;&nbsp;'), htmlspecialchars($val))?></td>
					<td><form action="" method="post" onsubmit="return (this.tb_edit_text.value.replace(/^\s*([\w\W]+)\s*$/, '$1').length!=0 && confirm('�޸ĺ����ݲ����ָܻ���ȷ����'))">
						<input type="hidden" name="tb_edit_name" value="<?=$key?>" />
						<div><textarea name="tb_edit_text" style="width:100%;"></textarea></div>
						<div style="margin-top:3px;" >
							<input type="submit" name="" value="�޸�" />
							<?php if(isset($_REQUEST['tb_edit_name']) && $key == $_REQUEST['tb_edit_name'] && 0 == count($error)) echo 'affected rows: ', $ret; ?>
						</div></form>
					</td></tr>
				<?php } ?>
			</table>
			<?php }?>
			<?php if(isset($info[IModDef::FTR])){ ?>
			<table>
				<caption>������(����,��Ӧ��,���÷���)</caption>
				<?php $i=0; foreach($info[IModDef::FTR] as $key => $val){ ++$i; ?>
				<tr <?=(0 == $i % 2)?'class=bg':''?>><td width="40%"><?=$key?></td><td><?=htmlspecialchars($val[IModDef::G_CS])?></td><td><?=htmlspecialchars($val[IModDef::G_DC])?></td></tr>
				<?php } ?>
			</table>
			<?php }?>
			<?php if(isset($info[IModDef::LTN])){ ?>
			<table>
				<caption>������(��Ӧ��,�����¼�)</caption>
				<?php $i=0; foreach($info[IModDef::LTN] as $key => $val){ ++$i;  ?>
				<tr <?=(0 == $i % 2)?'class=bg':''?>><td width="40%"><?=$key?></td><td><?=htmlspecialchars($val)?></td></tr>
				<?php } ?>
			</table>
			<?php }?>
			<?php if(isset($info[IModDef::TAG])){ ?>
			<table>
				<caption>��ǩ(����,��Ӧ��,���÷���)</caption>
				<?php $i=0; foreach($info[IModDef::TAG] as $key => $val){ ++$i; ?>
				<tr <?=(0 == $i % 2)?'class=bg':''?>><td width="40%"><?=$key?></td><td><?=htmlspecialchars($val[IModDef::G_CS])?></td><td><?=htmlspecialchars($val[IModDef::G_DC])?></td></tr>
				<?php } ?>
			</table>
			<?php } ?>
			<?php if(isset($info[IModDef::PAGE_ARG])){ $argnum = 0; ?>
			<table>
				<caption>ҳ�����(����,������Ϣ&lt;����,����,����,��ѡ,Ϊ��,trim&gt;)</caption>
				<?php foreach($info[IModDef::PAGE_ARG] as $key => $val){ ++ $argnum; ?>
				<tr <?=(0 == $argnum % 2)?'class=bg':''?>><td width="40%"><?=$key?></td>
				<td>
				<?php foreach($val as $name => $arr){ ?>
				<div><?=htmlspecialchars($name)?>
				/<?=isset($arr[IModDef::PARG_TYP]) ? $arr[IModDef::PARG_TYP] : 'string'?>
				/<?=isset($arr[IModDef::PARG_DEP]) ? $arr[IModDef::PARG_DEP] : '-'?>
				/<?=isset($arr[IModDef::PARG_REQ]) ? $arr[IModDef::PARG_REQ] : '1'?>
				/<?=isset($arr[IModDef::PARG_EMP]) ? $arr[IModDef::PARG_EMP] : '0'?>
				/<?=isset($arr[IModDef::PARG_TRI]) ? $arr[IModDef::PARG_TRI] : '1'?>
				</div>
				<?php } ?>
				</td></tr>
				<?php } ?>
			</table>
			<?php } ?>
			<form action="" method="post" enctype="multipart/form-data" onsubmit=" return _submit(this)">
			<?php 
			foreach($types as $tp){
				if($tp == CFileType::FT_MODDEF)
					continue;
			?>
			<table>
				<caption><?=$tp?>�ļ�</caption>
				<?php 
				$queue = array(CFileType::getDir(CFileType::ENV_COMPILE, CUR_MOD, $tp));
				$superlen = strlen($queue[0]);
				for($i=0; $i<count($queue); ++$i){
					$dir = $queue[$i];
					$filenum = 0;
					if(file_exists($dir)){
						$dh = dir($dir);
						while (false !== ($entry = $dh->read())) {
							if('.' == $entry || '..' == $entry)
								continue;
							if(is_dir($dir.$entry)){
								$queue[] = $dir.$entry.'/';
								continue;
							}
							++ $filenum;
							$mtime = filemtime($dir.$entry);
							$entry = substr($dir, $superlen).$entry;
				?>
				<tr <?=(0 == $filenum % 2)?'class=bg':''?>><td width="20%"><?php if($tp == CFileType::FT_IMG){ ?><img src="<?=CFileType::getURL(CUR_MOD, $entry, $tp)?>" style="width:35px;height:35px;" alt="<?=$entry?>" title="<?=$entry?>" />
				<?php }else{ ?> <a href="#NTAG_CALL(core, url, core, source_code)&mod=<?=CUR_MOD?>&type=<?=$tp?>&file=<?=rawurlencode($entry)?>"><?=$entry?></a> <?php } ?></td>
				<td><?=date('Y-m-d H:i:s', $mtime)?></td>
				<td><input type="file" name="<?=$tp?>_<?=str_replace('.', '+', $entry)?>" /><input type="submit" value="�޸�"/>&nbsp;|&nbsp;<a href="#NTAG_CALL(core, url, core, download)&mod=<?=CUR_MOD?>&type=<?=$tp?>&file=<?=rawurlencode($entry)?>">����</a></td></tr>
				<?php } $dh->close(); }} ?>
				<tr><td>��<i><?=$filenum?></i>���ļ�</td><td><?php if($filenum > 0){?><input type="submit" value="�޸�" /><?php } ?></td></tr>
			</table>
			<?php } ?>
			<div style="margin:20px 0;"><input type="submit" value="ȫ���޸�" /></div>
			</form>
		</div>
	</div>
</div>
</body>
</html>