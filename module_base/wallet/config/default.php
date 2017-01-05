<?php
$default=array(
    'alipay' => array(
        'partner'       => '2088801896905022',
        'key'           => 'c3s1jjwog9luei8xpd0nt4z6iknxee96',
        'pay_email'     => 'westel_alipay@jjjjjs.cn',
        'pay_name'      => '南京西电网络科技有限公司',
        'cacert'        => $mbs_appenv->getDir('wallet', CAppEnv::FT_CLASS).'alipay/cacert.pem',
        'input_charset' => 'utf-8',
        'transport'     => 'http',
        'sign_type'     => 'MD5',       
        'notify_url'    => 'http://task.jjjjjs.cn:8081'.$mbs_appenv->toURL('alipay_batch_withdraw_notify', 'wallet'),
    ),
);
?>