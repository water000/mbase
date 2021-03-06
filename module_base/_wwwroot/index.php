<?php

date_default_timezone_set('Asia/Shanghai');

define('RTM_DEBUG', 1);
define('RTM_INDEX', 1); //ref file: CAppEnv.php
if(RTM_DEBUG){
    error_reporting(E_ALL);
    ini_set('display_startup_errors', '1');
}else{// do not display errors on genaration env. HIGH recommend that the errors put in logs
    ini_set('display_errors', '0');
}

//env and conf init;there are two kinds of const in the system.
//one start with 'RTM_' what means 'run-time' defined;the other start
//with 'CFG_' what means 'configuration(installing)' defined
require 'CAppEnv.php';
use modbase\CAppEnv;
$mbs_appenv     = CAppEnv::getInstance();
$mbs_cur_moddef = $mbs_cur_actiondef = null;


spl_autoload_register(function($class){
    $names = explode('\\', $class);
    if(isset($names[2]) && 'modbase'==$names[0]){
        global $mbs_appenv;
        $path = $mbs_appenv->getClassPath(implode('/', array_slice($names, 2, count($names)-2)), $names[1]);
        require_once $path;
    }else{
        trigger_error('unknown class: '.$class, E_USER_ERROR);
    }
}, true);

function mbs_moddef($mod){
    global $mbs_appenv;
    static $modbuf = array();

    if(isset($modbuf[$mod])){
        return $modbuf[$mod];
    }

    $obj = null;
    list($class, $path) = $mbs_appenv->getModDefInfo($mod);
    if(file_exists($path)){
        require_once $path;
        $class = 'modbase\\'.$mod.'\\'.$class;
        $obj = new $class($mbs_appenv);
    }else{
        trigger_error($mod.' mod not exists', E_USER_WARNING);
    }
    $modbuf[$mod] = $obj;
    return $obj;
}
    

//DO not call the function directly, instead of using the trigger_error function.
//mbs_error_log('[int]error type/no', 'some errors', __FILE__, __LINE__);
define('RTM_LOG_TRACE_SEP', '@@@');
function mbs_error_log($errno, $msg, $file, $lineno, $ctx){
	global $mbs_appenv;
	static $map = array(E_WARNING=>'PHP WARN', E_NOTICE=>'PHP NOTICE', 
	    E_USER_ERROR=>'USER ERROR', E_USER_WARNING=>'USER WARN', E_USER_NOTICE=>'USER NOTICE');
	
	$backtrace='';
	if(is_array($ctx)){ // from trigger_error
	    foreach(debug_backtrace() as $k => $arr){
	        $backtrace .= '#'.$k.' '
	   	        .(isset($arr['class']) ? $arr['class']:'')
	   	        .(isset($arr['type']) ? $arr['type'] : '')
	   	        .$arr['function'].'(';
	        foreach($arr['args'] as $a){
	            if(is_array($a))
	                $backtrace .= 'array,';
	            else if(is_object($a))
	                $backtrace .= 'object<'.get_class($a).'>,';
	            else if(is_resource($a))
	                $backtrace .= 'resource<'.get_resource_type($a).'>,';
	            else $backtrace .= gettype($a).'<'.$a.'>,';
	        }
	        $backtrace .= ') called at ['
	            .(isset($arr['file']) ? $arr['file']:'')
	            .(isset($arr['line']) ? ':'.$arr['line'] : '')
	            .']'.RTM_LOG_TRACE_SEP;
	    }
	}else $backtrace = str_replace("\n", RTM_LOG_TRACE_SEP, $ctx); // from exception
	
	$error = sprintf("%s: %s.%s: '%s'(%s:%d)".RTM_LOG_TRACE_SEP,
        isset($map[$errno]) ? $map[$errno] : 'UNDEF('.$errno.')',
		$mbs_appenv->item('cur_mod'),
		$mbs_appenv->item('cur_action'),
		$msg,
        $file,
        $lineno
	);
	$error .= $backtrace;
	
	$mbs_appenv->echoex( RTM_DEBUG ? $msg.'('.$file.':'.$lineno.')' 
	    : $mbs_appenv->lang('db_exception'), 'SYS_ERR');
	error_log($error, 0);
	if(E_USER_ERROR == $errno)
	    exit(1);
}
set_error_handler('mbs_error_log');// php.ini (log_errors=ture, error_log=path)
set_exception_handler(function($e){// handle some uncaught exceptions
	mbs_error_log(E_USER_ERROR, 
	    $e->getMessage(), 
	    $e->getFile(), 
	    $e->getLine(),
	    $e->getTraceAsString());
});

use modbase\common\CStrTools, modbase\common\CDbPool, modbase\common\CMemcachedPool;
use modbase\core\CModDef;


function mbs_tbname($name){
	return $GLOBALS['mbs_appenv']->config('table_prefix', 'common').$name;
}

function mbs_title($action='', $mod='', $system=''){
	global $mbs_cur_moddef, $mbs_appenv;

	$argc = func_num_args();
	if(0 == $argc){
		echo $mbs_cur_moddef->item(CModDef::PAGES, $mbs_appenv->item('cur_action'), CModDef::P_TLE), 
			'-', $mbs_cur_moddef->item(CModDef::MOD, CModDef::G_TL), 
			'-', $mbs_appenv->lang('site_name');
	}
	else if(1 == $argc){
		echo $action , 
			'-', $mbs_cur_moddef->item(CModDef::MOD, CModDef::G_TL),
			'-', $mbs_appenv->lang('site_name');
	}else if(2 == $argc){
		echo $action , '-', $mod, '-', $mbs_appenv->lang('site_name');
	}else{
		echo $action , '-', $mod, '-', $system;
	}
}

function mbs_runtime_close_debug(){ // call the function before the echoex invoked if json request coming
	global $mbs_cur_moddef, $mbs_appenv;
	
	$mbs_cur_actiondef[CModDef::P_DOF] = ''; // set the key to close the output when app terminated
}

function _main(){
	global $mbs_appenv, $mbs_cur_moddef, $mbs_cur_actiondef, $argc;
	
	if(false !== strpos(PHP_SAPI, 'cli')){
		if($argc < 3){
			trigger_error('BIN/php index.php module action', E_USER_ERROR);
		}
		list($mod, $action, $args) = $mbs_appenv->fromCLI();
	}else{
		if(false === stripos(ini_get('request_order'), 'GP'))
			$_REQUEST = array_merge($_GET, $_POST);
	
		//check on installing first
		if((get_magic_quotes_gpc() || ini_get('magic_quotes_runtime')) && ini_get('magic_quotes_sybase'))
		{// the system use the method 'prepare' in class PDO to prevent the sql injection
			$func = create_function('&$v, $k', "\$v=str_replace(\"''\", \"'\", \$v);");
			array_walk_recursive($_GET, $func);
			array_walk_recursive($_POST, $func);
			array_walk_recursive($_COOKIE, $func);
			array_walk_recursive($_REQUEST, $func);
		}
		else if(get_magic_quotes_gpc())
		{
			$func = create_function('&$v, $k', "\$v=stripslashes(\$v);");
			array_walk_recursive($_GET, $func);
			array_walk_recursive($_POST, $func);
			array_walk_recursive($_COOKIE, $func);
			array_walk_recursive($_REQUEST, $func);
		}
	
		list($mod, $action, $args) = $mbs_appenv->fromURL(
			$mbs_appenv->config('default_module', 'common'),
			$mbs_appenv->config('default_action', 'common')
		);
	}
	
	if(!CStrTools::isModifier($mod) || !CStrTools::isModifier($action)){
		http_response_code(404);
		exit(404);
	}
	
	$mbs_cur_moddef = mbs_moddef($mod);
	if(empty($mbs_cur_moddef)){
	    http_response_code(404);
		exit(404);
	}
	$mbs_cur_actiondef = $mbs_cur_moddef->item(CModDef::PAGES, $mbs_appenv->item('cur_action'));
	
	$db = $mbs_appenv->config('database', 'common');
	if(!empty($db)){
		CDbPool::setConf($db);
	}
	CDbPool::setCharset($mbs_appenv->item('db_charset'));
	
	$mem = $mbs_appenv->config('memcache', 'common');
	if(!empty($mem)){
		CMemcachedPool::setConf($mem);
	}
	
	session_set_save_handler(new modbase\common\CSessionDBCache(
	    CDbPool::getInstance(), CMemcachedPool::getInstance()), true);
	
	if(RTM_DEBUG && !isset($mbs_cur_actiondef[CModDef::P_DOF])){
		CDbPool::getInstance()->setClass(CDbPool::CLASS_PDODEBUG);
		CMemcachedPool::getInstance()->setClass(CMemcachedPool::CLASS_MEMCACHEDDEBUG);
	
		register_shutdown_function(function($mbs_appenv, $mbs_cur_actiondef){
			if(!isset($mbs_cur_actiondef[CModDef::P_DOF])){ // for runtime modify
				if(false !== strpos(PHP_SAPI, 'cli')){
					CDbPool::getInstance()->cli();
					CMemcachedPool::getInstance()->cli();
					echo "\n";
				}else if('html' == $mbs_appenv->item('client_accept')){
					echo '<div><a href="javascript:;" style="font-size:12px;color:#888;display:block;text-align:right;" onclick="open(null, null, \'width=800,height=600\').document.write(this.parentNode.nextSibling.innerHTML)">.</a></div><div style="display:none">';
					CDbPool::getInstance()->html();
					CMemcachedPool::getInstance()->html();
					echo '</div>';
				}else{
				    $other = '[unsupport ob_start() to buffer the db and cache trace info]';
				    if(function_exists('ob_start')){
				        ob_start();
				        CDbPool::getInstance()->cli();
				        CMemcachedPool::getInstance()->cli();
				        $other = ob_get_clean();
				    }
				    (new modbase\core\CDBLogAPI(CDbPool::getInstance()))
				        ->write($mbs_appenv->output(), $other);
				}
			}
		}, $mbs_appenv, $mbs_cur_actiondef);	
	}

	if(false !== strpos(PHP_SAPI, 'cli') && CModDef::isReservedAction($action)){
	    if($mod != 'common'){
    	    $listeners = $mbs_appenv->config('listener', 'common');
    	    if(!empty($listeners)){
    	        foreach($listeners as $k => $v){
    	            list($mod, $class, $func) = explode('.', $k);
    	            $ins = $class::getInstance($mbs_appenv,
    	                CDbPool::getInstance(), CMemcachedPool::getInstance());
    	            $ins->produce($func, $v, $k);
    	        }
    	    }
	    }
	    
		$err = $mbs_cur_moddef->$action(CDbPool::getInstance(), CMemcachedPool::getInstance());
		echo $action, empty($err)? ' successed!' : " error: \n". implode("\n<br/>", $err);
	}else{

	    $path = $mbs_appenv->getActionPath($action, $mod);
	    if(!file_exists($path)){
	        http_response_code(404);
	        exit(404);
	    }
		//do filter checking
		if(!$mbs_cur_moddef->loadFilters($action, $err)){
		    $mbs_appenv->echoex($err, 'MOD_FTR_ERROR');
			exit(1);
		}
		
		$filters = $mbs_appenv->config('action_filters', 'common');
		if(!empty($filters) && !empty($mbs_cur_actiondef)){
			foreach($filters as $ftr){
				if(count($ftr) >=3 && $ftr[0]($mbs_cur_actiondef)){
					$mdef = mbs_moddef($ftr[1]);
					if(!$mdef->filter($ftr[2], isset($ftr[3])?$ftr[3]:null, $err)){
						$mbs_appenv->echoex($err, 'AC_FTR_ERROR');
						exit(1);
					}
				}
			}
		}
		
		$listeners = $mbs_appenv->config('listener', 'common');
		if(!empty($listeners)){
		    foreach($listeners as $k => $v){
		        list($mod, $class, $func) = explode('.', $k);
		        $class = strpos($class, '\\') === false ? sprintf('modbase\%s\%s', $mod, $class) : $class;
		        $ins = $class::getInstance($mbs_appenv, 
		            CDbPool::getInstance(), CMemcachedPool::getInstance());
		        $ins->produce($func, $v, $k);
		    }
		}
		
		include $path;
	}

	if(!RTM_DEBUG && function_exists('fastcgi_finish_request'))
		call_user_func('fastcgi_finish_request');	
}

_main();
exit(0);
?>





