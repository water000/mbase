<?php 

$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
if(!empty($error)){
    $mbs_appenv->echoex(implode(';', $error), 'INVALID_PARAM');
    exit(1);
}

if(isset($_REQUEST['for'])){
    $for_path = $mbs_appenv->url2path($_REQUEST['for']);
    if(empty($for_path) || !file_exists($for_path)){
        $mbs_appenv->echoex('invalid param: for', 'SEND_SMS_INVALID_PARAM');
        exit(1);
    }
    if(! (require $for_path)){
        exit(1);
    }
}

switch ($_REQUEST['type']){
    case 'captcha':
        mbs_import('common', 'CSMSCaptcha');
        $captcha = mt_rand(100000, 999999);
        $smscap = new CSMSCaptcha(CDbPool::getInstance());
        $ret = $smscap->create($_REQUEST['phone'], $captcha, $_REQUEST['cap_group']);
        if(empty($ret)){
            mbs_sendmessage($_REQUEST['phone'], $mbs_appenv->lang('captcha_title'), 
                sprintf($mbs_appenv->lang('captcha_body'),$captcha));
            $mbs_appenv->echoex(null);
        }else{
            $mbs_appenv->echoex($mbs_appenv->lang($ret), $ret);
        }
        break;
}

function mbs_sendmessage($to_set, $subject_set, $body_set) {
    $data_str = 'to_set='.$to_set.'&subject_set='.$subject_set.'&body_set='.$body_set.'&type=1';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,'http://122.96.62.194:11024/');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec ($ch);
    curl_close ($ch);
    return $server_output;
}



?>