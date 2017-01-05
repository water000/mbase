<?php
class CWalletWithdrawBatchCtr extends CUniqRowControl{

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
				self::$instance = new CWalletWithdrawBatchCtr(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('wallet_withdraw_batch'), 'number', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CWalletWithdrawBatchCtr') : null,
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
	
	function lastBatch(){
	    $ret = parent::getDB()->search(array(), array('limit'=>1, 'order'=>'number desc'));
	    if(false === $ret) return null;
	    $ret = $ret->fetchAll();
	    return empty($ret) ? null : $ret[0];
	}
}
?>