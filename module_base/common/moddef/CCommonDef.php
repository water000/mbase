<?php
namespace modbase\common;

class CCommonDef extends \modbase\core\CModDef{
	protected function desc(){
		return array(
		    self::MOD => array(self::G_NM=>'common', self::G_CS=>'公共模块', self::M_CS=>'utf-8', ),
		    self::DEPEXT => array('curl', 'Imagick'),
		    self::FTR => array(
		    	'ApiSignFtr' => array(self::G_CS => 'CApiSignFtr', self::G_DC => '对接口请求参数的加密进行检查'),
		    ),
		    /*self::LTN => array(
		    	'class' => 'mod.action1,mod.action2,...'
		    ),*/
		    self::TBDEF => array(
		        'common_sms_captcha' => '(
                    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `phone`      varchar(16) CHARACTER SET latin1 NOT NULL default "",
                    `captcha`    char(6) CHARACTER SET latin1 NOT NULL default "",
                    `created_at` int(10) unsigned NOT NULL default 0,
                    `verify_num` tinyint(4) NOT NULL default 0,
                    `send_num`   tinyint(4) NOT NULL default 0,
                    `succeed`    tinyint(4) NOT NULL default 0,
                    `group_id`   tinyint(4) NOT NULL default 0,
                    PRIMARY KEY (`id`),
		            KEY `phone` (`phone`,`group_id`)
		        )', 
		        'common_session' => '(
		            id       char(32) CHARACTER SET latin1 NOT NULL  default "", 
		            data     varchar(512) NOT NULL default "",
		            write_ts int unsigned NOT NULL default 0,
		            primary key(id)
		        )',
		        'common_province_dict' => '(
		            code int unsigned NOT NULL default 0,
		            name varchar(16) NOT NULL default "",
		            primary key(code)
		        )',
		        'common_city_dict' => '(
		            code int unsigned NOT NULL default 0,
		            name varchar(16) NOT NULL default "",
		            prov_code int unsigned NOT NULL default 0,
		            tel_code int unsigned NOT NULL default 0,
		            ltr_1st char(1) NOT NULL default "",
		            primary key(code),
		            key(prov_code)
		        )',
		    ),
			self::PAGES => array(
				'send_sms' => array(
					self::P_TLE => '发送短信',
					self::G_DC  => '给指定手机发送短信, 每次发送的间隔1分钟.',
					self::P_ARGS => array(
						'phone' => array(self::PA_REQ=>1, self::G_DC=>'手机号码*S*'),
					    'type'  => array(self::PA_REQ=>1, self::G_DC=>'sms类型(值:captcha)'),
					    'cap_group' => array(self::PA_REQ=>1, self::G_DC=>'验证码分组(值:USER_PWD),请求和验证需一致'),
					    'for'  => array(self::PA_REQ=>0, self::G_DC=>'需要验证码的接口URL，将会检查phone的有效性'),
					),
				    //self::LD_FTR => array(array('common', 'ApiSignFtr', true),),
					self::P_OUT => 'data:{}'
				),
				'img_captcha' => array(
					self::P_TLE => '图形验证码',
					self::G_DC  => '输出图形验证码到客户端',
					self::P_ARGS => array(
					),
				),
				'version'    => array(
					self::P_TLE => '版本信息',
					self::G_DC  => '提供了版本的信息，以及修改，app的大概介绍',
					self::P_OUT => '{"version_id":"1.0", "version_content"=>"", "content"=>"", "APP_URL"=>""}'
				),
			    'IM_cfg'    => array(
			        self::P_TLE => 'IM服务的配置',
			        self::G_DC  => '',
			        self::P_OUT => '{"msg_server":""}'
			    ),
			    'prov_city'    => array(
			        self::P_TLE => '省市信息',
			        self::G_DC  => '默认返回所有省得列表；如果带上prov_code参数，则返回相应城市列表',
			        self::P_ARGS => array(
			        ),
			        self::P_OUT => '[{"code":"110000", "name":"江苏/南京"}, ...]'
			    ),
			),
	  );
	}
	
	function install($dbpool, $mempool=null){
	    global $mbs_appenv;
	    
	    $dbpool->createdb();
	    
	    $err = parent::install($dbpool, $mempool);
	    if(!empty($err)) return $err;
	
	    mbs_import('', 'CCommonCityDictCtr', 'CCommonProvinceDictCtr');
	    
        
        $prov = CCommonProvinceDictCtr::getInstance(self::$appenv, $dbpool, $mempool);
        $prov->getDB()->getConnection()->exec('set character_set_database = utf8');
                
        $prov->getDB()->getConnection()->exec(sprintf('load data LOCAL infile "%s" into table %s',
            addcslashes($mbs_appenv->getPath(CAppEnv::FT_CONFIG.'/provinces.txt', 'common'), '\\'), 
            $prov->getDB()->tbname()));
        
        $city = CCommonCityDictCtr::getInstance(self::$appenv, $dbpool, $mempool);
        $city->getDB()->getConnection()->exec(sprintf('load data LOCAL infile "%s" into table %s',
            addcslashes($mbs_appenv->getPath(CAppEnv::FT_CONFIG.'/cities.txt', 'common'), '\\'),
            $city->getDB()->tbname()));
	    
	}
}

?>