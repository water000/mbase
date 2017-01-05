<?php
namespace modbase\user;

/**
 * @include $mbs_appenv, session 
 * @author tiger
 *
 */

class CUserSession extends \modbase\core\CModTag {

	function __construct(){
	    if(!isset($_SESSION)) session_start();
	    //else if (isset($_COOKIE[session_name()])) session_start();
	   // else $_SESSION = array();
	}
	
	function set($user_id, $userinfo=null){
		$userinfo = empty($userinfo) ? array() : $userinfo;
		$_SESSION['user_login'] = array($user_id, $userinfo);
	}

	function get(){
		return isset($_SESSION['user_login']) ? $_SESSION['user_login'] : null;
	}

	function free(){
		unset($_SESSION['user_login']);
	}
	
	function checkLogin(){
		global $mbs_appenv;
		
		if(isset($_SESSION['user_login'])){
			return $_SESSION['user_login'][0];
		}
		
		$mbs_appenv->echoex($mbs_appenv->lang('login_first', 'user'), 'NOT_LOGIN', 
				$mbs_appenv->toURL('login', 'user', array('redirect'=>$_SERVER['REQUEST_URI'])));
		
		return false;
	}
	
	function oper($params, $tag=''){
		switch ($tag){
			case '':
			case 'checkLogin':
				return $this->checkLogin($params);
				break;
		}
		return false;
	}
	
}

?>