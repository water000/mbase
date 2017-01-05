<?php 

$output = array(
	'version_id'      => 2,
	'APP_URL'         => $mbs_appenv->uploadURL((isset($_REQUEST['client']) && 'b'==$_REQUEST['client']) ? 'app-release_B.apk' : 'app-release_C.apk', 'common'),
	//'version_content' => 'v1, 守望者提供了任务的发布、提交、奖励等功能',
    'version_content' => '1.1版本更新日志
1、修复余额不能全部提现
2、修复任务上传图片不清晰
3、修复聊天列表不能显示头像的问题',
);

$mbs_appenv->echoex($output);

?>