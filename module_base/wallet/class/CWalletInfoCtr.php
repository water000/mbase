<?php

require_once __DIR__.'/CWalletInfoTB.php';

class CWalletInfoCtr extends CUniqRowControl{

	private static $instance = null;
	
	protected function __construct($db, $cache, $primarykey = null){
		parent::__construct($db, $cache, $primarykey);
	}

	/**
	 *
	 * @param CAppEnv $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $primarykey = null){
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CWalletInfoCtr(
						new CWalletInfoTB($dbpool->getDefaultConnection(),
								mbs_tbname('wallet_info'), 'uid', $primarykey),
						null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}else {
			self::$instance->setPrimaryKey($primarykey);
		}
		return self::$instance;
	}
	
	function incr($amount){
	    $ret = $this->oDB->incr($amount);
	    if($ret > 0){
	        if(!empty($this->oCache)){
	            $this->oCache->destroy();
	        }
	        if(isset($this->arrBuf[$this->primaryKey])){
	            unset($this->arrBuf[$this->primaryKey]);
	        }
	    }
	    return $ret;
	}
	
	static function response($ev, $args){
	    global $mbs_appenv;
	
	    $ins = self::getInstance($mbs_appenv,
	        CDbPool::getInstance(), CMemcachedPool::getInstance(), $args['id']);
	    switch ($ev){
	        case 'user.CUserInfoCtr.add':
	            $arr = array(
    	            'change_ts' => time(),
    	            'uid'       => $args['id'],
    	            'amount'    => 0,
    	        );
	            $ins->add($arr);
	            break;
	        case 'user.CUserInfoCtr.destroy':
	            //$ins->destroy();
	            break;
	    }
	}
}
?>