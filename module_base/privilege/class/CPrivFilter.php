<?php
namespace modbase\privilege;
/**
 *@depend user.CUserSession, privilege.CPrivUserControl
 */

use \modbase\user\CUserSession,
    \modbase\core\CModTag,
    \modbase\common\CStrTools,
    \modbase\common\CDbPool,
    \modbase\common\CMemcachedPool;


class CPrivFilter extends CModTag{
	
	function oper($params, $tag=''){
		global $mbs_appenv;
		
		$us = new CUserSession();
		$user_id = $us->checkLogin();
		if(empty($user_id)){
			$this->error = $us->getError();
			return false;
		}
		
		$priv_info = null;
		$pu = CPrivUserControl::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$priv_info = $pu->getDB()->search(array('user_id' => $user_id));
		if(empty($priv_info) || !($priv_info = $priv_info->fetchAll(\PDO::FETCH_ASSOC))){
			$this->error = 'access denied';
			return false;
		}
		$priv_info = $priv_info[0];
		
		$pg = CPrivGroupControl::getInstance($mbs_appenv, CDbPool::getInstance(),
		    CMemcachedPool::getInstance(), $priv_info['priv_group_id']);
		$mod = isset($params['mod']) ? $params['mod'] : $mbs_appenv->item('cur_mod');
		$ac  = isset($params['action']) ? $params['action'] : $mbs_appenv->item('cur_action');
		if(!$pg->privExists($mod, $ac)){
		    $this->error = 'access denied';
		    return false;
		}
		
		return true;
	}
	
	static function isTopmost($uid){
	    global $mbs_appenv;
	    
	    $priv_info = null;
	    $pu = CPrivUserControl::getInstance($mbs_appenv,
	        CDbPool::getInstance(), CMemcachedPool::getInstance());
	    $priv_info = $pu->getDB()->search(array('user_id' => $uid));
	    if(empty($priv_info) || !($priv_info = $priv_info->fetchAll(\PDO::FETCH_ASSOC))){
	        $this->error = 'access denied';
	        return false;
	    }
	    $priv_info = $priv_info[0];
	    
	    $pg = CPrivGroupControl::getInstance($mbs_appenv, CDbPool::getInstance(),
	        CMemcachedPool::getInstance(), $priv_info['priv_group_id']);
	    return $pg->topmost();
	}

}

?>