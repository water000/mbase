<?php
namespace modbase\user;
     
class CUserDeviceCtr extends CUniqRowControl{

	private static $instance = null;
	
	static $type = array(
	    'PC',
	    'PAD',
	    'PHONE'
	);
	
	static $os = array(
	    'WINDOWS',
	    'ANDROID',
	    'IOS',
	    'OTHER'
	);
	
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
				self::$instance = new CUserDeviceCtr(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('user_device'), 'uid', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CUserDeviceCtr') : null,
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
	
	static function stos($s){
	    if(is_numeric($s)) return isset(self::$os[$s]) ? self::$os[$s] : false;
	    else if(is_string($s)) return array_search($s, self::$os);
	    else return self::$os;
	}
	
	static function sttype($s){
	    if(is_numeric($s)) return isset(self::$type[$s]) ? self::$type[$s] : false;
	    else if(is_string($s)) return array_search($s, self::$type);
	    else return self::$type;
	}

}
?>