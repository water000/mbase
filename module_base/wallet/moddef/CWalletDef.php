<?php

//批量支付 https://doc.open.alipay.com/doc2/detail?treeId=64&articleId=103569&docType=1 
//curl "https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo=银行卡卡号&cardBinCheck=true"
//银联代付 https://open.unionpay.com/ajweb/product/detail?id=67
//银联批量代付文件标准 https://open.unionpay.com/ajweb/help?id=207#4.4.2
//https://cschannel.alipay.com/newPortal.htm?scene=mt_zczx&token=&pointId=&enterurl=https%3A%2F%2Fsupport.open.alipay.com%2Falipay%2Fsupport%2Findex.htm
class CWalletDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'wallet',
				self::M_CS=>'utf-8',
				self::G_TL=>'用户钱包',
				self::G_DC=>'提供支付功能，包括ali，银联等等'
			),
			self::TBDEF => array(
				'wallet_pay' => '(
					id                 int unsigned not null auto_increment,
					user_id            int unsigned not null default 0,
					product_desc       varchar(16) not null default "",
					product_img_url    varchar(128)  default "",
					product_num        int unsigned  default 0,
					product_unit_price int unsigned default 0,
					product_extra      varchar(32) default "", -- product extra info 
					create_ts          int unsigned not null default 0, -- register timestamp
					pay_type           tinyint unsigned default 0, -- 1:points, 2: voucher, 4:alipay, 8:unionpay, 16:weixin, 4:..
					pay_extra          varchar(32) not null default "", -- extra info of which pay_type
					status             tinyint unsigned default 0, -- 0:unpaid, 1:paid
					primary key(id),
					key(user_id)
				)',
			    'wallet_info' => '(
			        uid            int unsigned not null default 0,
			        amount         int not null default 0,
			        history_amount int unsigned not null default 0,
			        change_ts      int unsigned not null default 0,
			        status         tinyint not null default 0, -- 0: normal, 1: banned(banned by sys if the user operate with some exceptions)
			        primary key(uid)
			    )',
			    'wallet_history' => '( -- M(a_uid, id)
			        id         int unsigned auto_increment not null,
			        a_uid      int unsigned not null default 0, -- a->b($10): insert(a, b, -10),(b,a, +10)
			        b_uid      int unsigned not null default 0,
			        type       tinyint unsigned not null default 0, -- 0: task, 1: withdraw, 2: recharge
			        amount     int not null default 0,
			        balance    int unsigned not null default 0,
			        create_ts  int unsigned not null default 0,
			        msg        varchar(16) not null default "",
			        primary key(id),
			        key(a_uid)
			    )',
			    // insert the record to 'wallet_withdraw_history' if successful and delete it after user visit
			    'wallet_withdraw_apply' => '(
			        uid          int unsigned not null default 0,
			        amount       int unsigned not null default 0,
			        dest_account varchar(64) not null default 0,
			        account_name varchar(32) not null default 0,
			        account_type tinyint not null default 0, -- 0:uni-pay, 1:wx-pay, 2:ali-pay
			        submit_ts    int unsigned not null default 0,
			        update_ts    int unsigned not null default 0,
			        status       tinyint not null default 0, -- 0: APPLIED, 1: ACCEPTED, 2: SUCCEDDED, 3: FAILED
			        primary key(uid),
			        key(submit_ts)
			    )',
			    'wallet_withdraw_history' => '( -- M(uid, id)
			        id           int unsigned auto_increment not null,
			        uid          int unsigned not null default 0,
			        amount       int unsigned not null default 0,
			        dest_account varchar(64) not null default "",
			        account_name varchar(8) not null default "",
			        account_type tinyint not null default 0,
			        submit_ts    int unsigned not null default 0,
			        notify_ts    int unsigned not null default 0,
			        is_succ      tinyint not null default 0,
			        fault_msg    varchar(255) not null default "",
			        batch_no     int unsigned not null default 0,
			        order_id     varchar(32) not null default "", -- yyyymmddhhiiss+uid
			        query_id     varchar(32) not null default "",
			        primary key(id),
			        key(uid),
			        key(batch_no)
			    )',
			    'wallet_withdraw_batch' => '(
			        number      int unsigned not null default 0,
			        submit_ts   int unsigned not null default 0,
			        total_qty   int unsigned not null default 0,
			        total_amt   int unsigned not null default 0,
			        success_qty int unsigned not null default 0,
			        fault_qty   int unsigned not null default 0,
			        resp_code   varchar(16) not null default "",
			        resp_msg    varchar(64) not null default "",
			        notify_ts   int unsigned not null default 0,
			        notify_msg  varchar(64) not null default "",
			        primary key(number)
			    )',
			),
			self::PAGES => array(
			    'myinfo' => array(
			        self::P_TLE => '钱包信息',
			        self::G_DC  => '获取我的钱包信息，如果没有则初始化',
			        self::LD_FTR => array(
			            array('user', 'checkLogin', true)
			        ),
			        self::P_ARGS => array(
			        ),
			        self::P_OUT => '{详见#wallet_info#, withdraw_amount:0(提现金额)}',
			    ),
			    'history' => array(
			        self::P_TLE => '历史记录',
			        self::G_DC  => '获取钱包的历史记录',
			        self::LD_FTR => array(
			            array('user', 'checkLogin', true)
			        ),
			        self::P_ARGS => array(
			            'page_id'   => array(self::G_DC=>'分页id', self::PA_TYP=>'integer'),
			        ),
			        self::P_OUT => '{详见#wallet_history#, title:任务#1323奖励/提现/..., msg:额外信息，例如失败信息}',
			    ),
			    'withdraw' => array(
			        self::P_TLE => '钱包提现申请',
			        self::G_DC  => '接受申请后的几个工作日内完成',
			        self::LD_FTR => array(
			            array('user', 'checkLogin', true)
			        ),
			        self::P_ARGS => array(
			            'amount'   => array(self::G_DC=>'提现金额', self::PA_TYP=>'float'),
			            'account'  => array(self::G_DC=>'提现帐号', self::PA_TYP=>'string'),
			            'acc_name' => array(self::G_DC=>'帐号姓名', self::PA_TYP=>'string'),
			        ),
			        self::P_OUT => '{}',
			    ),
			    'withdraw_mgr' => array(
			        self::P_TLE => '提现管理',
			        self::G_DC  => '对提现进行审核',
			        self::P_ARGS => array(
			            //'stat'     => array(self::G_DC=>'统计', self::PA_TYP=>'string'),
			        ),
			        self::P_MGR => true,
			    ),
			    'recharge_mgr' => array(
			        self::P_TLE => '充值管理',
			        self::G_DC  => '对指定帐号进行充值',
			        self::P_ARGS => array(
			            //'stat'     => array(self::G_DC=>'统计', self::PA_TYP=>'string'),
			        ),
			        self::P_MGR => true,
			    ),
			),
		);
	}
	
	function install($dbpool, $mempool=null){
	    parent::install($dbpool, $mempool);
	
	    mbs_import('', 'CWalletInfoCtr');
	    mbs_import('user', 'CUserInfoCtr');
	    
	    try {
	        $usr_ins = CUserInfoCtr::getInstance(self::$appenv, $dbpool, $mempool);
	        $ins = CWalletInfoCtr::getInstance(self::$appenv, $dbpool, $mempool);
	        
	        $ins->getDB()->getConnection()->exec(sprintf('INSERT IGNORE INTO %s(uid, change_ts) SELECT id, UNIX_TIMESTAMP() FROM %s',
	            $ins->getDB()->tbname(), $usr_ins->getDB()->tbname()));
	        //$wlt_hty_ctr = CWalletHistoryCtr::getInstance(self::$appenv, $dbpool, $mempool);
	       // CWalletHandle::recharge($ins, $wlt_hty_ctr, $arr['uid'], 10000);
	    } catch (Exception $e) {
	        throw $e;
	    }
	}
}

?>