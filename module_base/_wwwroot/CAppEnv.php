<?php
namespace modbase;

defined('RTM_INDEX') or exit('access denied');

class CAppEnv{

	CONST FT_CLASS      = 'class';	
	CONST FT_ACTION     = 'action';
	CONST FT_MODDEF     = 'moddef';
	CONST FT_CONFIG     = 'config';
	
	private static $instance = null;
	
	private $env = array(
		/**********config item **********/
		'charset'           => 'utf-8',
	    'db_charset'        => 'utf8mb4',
		'lang'              => 'zh_CN',
		'class_file_suffix' => '.php',
	    'req_sep'           => '/',
		/********** config end **********/
		
		/********** runtime item **********/
		'app_root'          => '', // assigned by __construct()
		'web_root'          => '/', // assigned by __construct(). NOTICE: must be '/' if using url-rewrite conditions , else to empty
		'client_ip'         => '', // assigned by __construct()
		'client_accept'     => '', // assigned by __construct()
		'cur_mod'           => '', // assigned by fromURL()
		'cur_action'        => '', // assigned by fromURL()
		'cur_action_url'    => '', // assigned by fromURL()		
	);
	
	private $mod_cfg = array();

	private $echo = array(
	   'extra' => array(),
	   'code'  => null,
	   'data'  => null,
	   'url'   => null,
	);
		
	private function __construct(){
		$this->env['app_root'] = realpath(dirname(__FILE__).'/..').'/';
		$this->env['web_root'] = empty($this->env['web_root']) ? 
			substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')+1) : $this->env['web_root'];
		$this->env['client_ip'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] 
			: (isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] 
			: (isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '0.0.0.0' ));
		
		if(isset($_SERVER['HTTP_ACCEPT'])){
			if(stripos($_SERVER['HTTP_ACCEPT'], 'json') !== false)
				$this->env['client_accept'] = 'json';
			else if(stripos($_SERVER['HTTP_ACCEPT'], 'html') !== false ||
				stripos($_SERVER['HTTP_ACCEPT'], 'xhtml') !== false || 
				stripos($_SERVER['HTTP_ACCEPT'], '*/*') !== false)
				$this->env['client_accept'] = 'html';
			else if(stripos($_SERVER['HTTP_ACCEPT'], 'xml') !== false)
				$this->env['client_accept'] = 'xml';
		}
		if($this->env['client_accept'] != '')
			header(sprintf('Content-Type: text/%s; charset=%s', 
				$this->env['client_accept'], $this->env['charset']));
		
		register_shutdown_function(function($appenv){$appenv->doecho();}, $this);
	}
	
	static function getInstance(){
		if(empty(self::$instance)){
			self::$instance = new CAppEnv();
		}
		return self::$instance;
	}
	
	function item($key){
		return isset($this->env[$key]) ? $this->env[$key] : null;
	}
	
	function getDir($mod, $file_type=''){
		return $this->env['app_root'].$mod.'/'.(empty($file_type) ? '' : $file_type.'/');
	}
	
	function setLogAPI($log){
		$this->log_api = $log;
	}
	
	function getPath($filename, $mod=''){
		return $this->getDir(empty($mod) ? $this->env['cur_mod'] : $mod).$filename;
	}
	
	function getClassPath($classname, $mod=''){
		return $this->getPath(self::FT_CLASS.'/'.$classname.$this->env['class_file_suffix'], $mod);
	}
	
	function getActionPath($action, $mod=''){
		return $this->getPath(self::FT_ACTION.'/'.$action.'.php', $mod);
	}
	
	//@file: a relative path in action dir
	function file2action($file){
		return ($pos = strrpos($file, '.php')) !== false ? substr($file, 0, $pos) : $file;
	}
	
	static function isModifier($name)
	{
	    if(($name[0]>='a' && $name[0]<='z')
	        || ($name[0]>='A' && $name[0]<='Z') || '_' == $name[0])
	        ;
	        else
	            return false;
	        $i = 1;
	        $len = strlen($name);
	        for(; $i<$len; ++$i){
	            if(($name[$i] >= 'a' && $name[$i]<='z')
	                || ($name[$i]>='A' && $name[$i]<='Z')
	                || ($name[$i]>='0' && $name[$i]<='9')
	                || '_' == $name[$i]
	            )
	                ;
	                else
	                    return false;
	        }
	        return true;
	}
	
	function toURL($action='index', $mod='', $args=array()){
		/*
		$args['m'] = $mod;
		$args['a'] = $action;
		return $this->env['web_root'].'index.php?'.http_build_query($args);
		*/
		
		// detail at the 'fromURL()'
		return $this->env['web_root'].(empty($mod)?$this->env['cur_mod']:$mod).$this->item('req_sep').$action
			.(empty($args) ? '' : '?'.http_build_query($args));
	}
	
	function url2path($url){
	    $arr = explode($this->item('req_sep'), trim($url, '/'));

	    if(!empty($arr[0]) && isset($arr[0]) && !empty($arr[1]))
	        ;
	    else return '';
	    
	    if(self::isModifier($arr[0]) && self::isModifier($arr[1]))
	        ;
	    else{
	        trigger_error('invalid modifier: mod.action');
	        return '';
	    }
	    
	    return $this->getActionPath($arr[1], $arr[0]);
	}
	
	/**
	 * 
	 * @param string $mod default module if not exists
	 * @param string $action default action if not exits
	 * @param unknown $args
	 * @return array 
	 */
	function fromURL($mod='', $action='', $args=array()){
		/*
		// this version use the 'm' and 'a' to request directly without using any url-rewrite conditions
		parse_str($_SERVER['QUERY_STRING'], $arr);
		$arr2 = array();
		$arr2[0] = isset($arr['m']) ? $arr['m'] : '';
		$arr2[1] = isset($arr['a']) ? $arr['a'] : '';
		$arr2[] = $arr;
		*/
		
		// to enable the url-rewrite on server
		//RewriteEngine on
  		//RewriteRule ^/static/(.+)   -                       [L,QSA]
		//RewriteRule ^/upload/(.+)   -                       [L,QSA]
  		//RewriteRule ^/favicon.ico   -                       [L,QSA]
  		//RewriteRule ^(.*)$          /index.php?__path__=$1  [B,L,QSA]
		$arr2 = array('', '', '');
		
  		if(isset($_GET['__path__'])){ 
			$arr = explode($this->item('req_sep'), trim($_GET['__path__'], '/'));
			$arr2[0] = empty($arr[0]) ? $mod : $arr[0];
			$arr2[1] = isset($arr[1]) ? $arr[1] : $action;
			unset($_GET['__path__'], $_REQUEST['__path__']);
			$arr2[] = $_GET;			
  		}else{
  			$arr2[0] = $mod;
  			$arr2[1] = $action;
  			$arr2[]  = $args;
  		}
  		$this->env['cur_mod']    = $arr2[0];
  		$this->env['cur_action'] = $arr2[1];
  		$this->env['cur_action_url'] = $this->toURL($arr2[1], $arr2[0]);

		return $arr2;
	}
	
	function fromCLI(){
	    global $argv;
	    $this->env['cur_mod']    = $arr2[0] = $argv[1];
	    $this->env['cur_action'] = $arr2[1] = $argv[2];
	    $this->env['cur_action_url'] = $this->toURL($arr2[1], $arr2[0]);
	    $arr2[] = array_slice($argv, 0, 3);
	    return $arr2;
	}
	
	/**
	 * get the static resource URL
	 * @param string $filename the resource name like 'core.css, core.js, core/a.png'.
	 * @return string
	 */
	function sURL($filename){
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		switch (strtolower($ext)){
			case 'css':
				$filename = 'css/'.$filename;
				break;
			case 'js':
				$filename = 'js/'.$filename;
				break;
			default :
				$filename = 'images/'.$filename;
				break;
		}
		return $this->env['web_root'].'static/'.$filename;
	}
	
	function getModDefInfo($mod){
		$arr = explode('_', $mod);
		foreach($arr as &$v){
			$v = ucfirst($v);
		}
		$class = 'C'.implode('', $arr).'Def';
		$path = $this->getDir($mod, self::FT_MODDEF).$class.$this->env['class_file_suffix'];
		return array($class, $path);
	}
	
	function getModList(){
		$list = array();
		
		if ($dh = opendir($this->env['app_root'])) {
			while (($file = readdir($dh)) !== false) {
				if($file[0] != '.' && is_dir($this->env['app_root'].$file) 
						&& is_dir($this->env['app_root'].$file.'/'.self::FT_MODDEF)){
					$list[] = $file;
				}
			}
			closedir($dh);
			
			sort($list);
		}
		
		return $list;
	}

	function uploadURL($filename, $mod='', $host=''){
		return $host.'/upload/'.(empty($mod)?$this->item('cur_mod'):$mod).'/'.$filename;
	}
	
	function uploadPath($filename, $mod=''){
		return dirname(__FILE__).'/upload/'.(empty($mod)?$this->item('cur_mod'):$mod).'/'.$filename;
	}
	
	function unlinkUploadFile($url){
		$arr = parse_url($url);
		if(!empty($arr['path']) 
			&& 0 === strpos($arr['path'], '/upload')
			&& strpos($arr['path'], '..') === false){
			unlink(dirname(__FILE__).$arr['path']);
		}
	}

	function config($item, $mod='', $cfg='default'){
		$mod = empty($mod) ? $this->env['cur_mod'] : $mod;
		if(!isset($this->mod_cfg[$mod][$cfg])){
			$path = $this->getPath('config/'.$cfg.'.php', $mod);
			if(file_exists($path)){
				$mbs_appenv = $this;
				require_once $path;
				if(isset($$cfg)){
					$this->mod_cfg[$mod][$cfg] = $$cfg;
				}else{
					$this->mod_cfg[$mod][$cfg] = null;
					trigger_error('no such config item defined: '.$cfg, E_USER_WARNING);
				}
			}else{
			    trigger_error('no such config file found: '.$path, E_USER_WARNING);
				return false;
			}
		}
		
		return isset($this->mod_cfg[$mod][$cfg][$item]) ? $this->mod_cfg[$mod][$cfg][$item] : false;
	}
	
	function lang($item, $mod=''){
		$arr = is_array($item) ? $item : array($item);
		
		$str = '';
		foreach($arr as $item){
			$ret = $this->config($item, $mod, 'lang_'.$this->env['lang']);
			if($ret === false){
				if(empty($mod)){
					$ret = $this->config($item, 'common', 'lang_'.$this->env['lang']);
					$ret = $ret === false ? $item : $ret;
				}else{
					$ret = $item;
				}
			}
			if(is_string($ret))
				$str .= $ret;
			else 
				$str = $ret;
		}
		return $str;
	}
	
	static function _print_array($arr){
	    $ret = '';
	    if(isset($arr[1]) && is_array($arr[1])){
	        $ret .= '<tr>';
	        foreach(array_keys($arr[0]) as $k)
	            $ret .= '<th>'.$k.'</th>';
	        $ret .= '</tr>';
	        foreach($arr as $row){
	            $ret .= '<tr>';
	            foreach($row as $item)
	                $ret .= '<td>'.htmlspecialchars(is_array($item)?json_encode($item):$item).'</td>';
	            $ret .= '</tr>';
	        }
	    }else{
	        if(isset($arr[0]) && is_array($arr[0]))
	            $arr = $arr[0];
	        foreach ($arr as $k => $v){
	            if(is_array($v) && !(isset($v[0]) && is_array($v[0]))){
	                foreach($v as $subk =>$subv){
	                    $ret .= '<tr><th>'.$k.'/'.$subk.'</th><td>'.htmlspecialchars(is_array($subv)?json_encode($subv):$subv).'</td></tr>';
	                }
	            }else
	               $ret .= '<tr><th>'.$k.'</th><td>'.htmlspecialchars(is_array($v)?json_encode($v):$v).'</td></tr>';
	        }
	    }
	    return $ret;
	}
	
	static function _print_data($data){
	    $tb = '';
	    switch (gettype($data)){
	        case 'string':
	            $data = str_replace(RTM_LOG_TRACE_SEP, '<br/>', htmlspecialchars($data));
	        case 'boolean':
	        case 'integer':
	        case 'double':
	        case 'NULL':
	            $tb .= $data;
	            break;
	        case 'array':
	            $tb = '<table>';
	            $tb .= self::_print_array($data);
	            $tb .= '</table>';
	            break;
	        default:
	            $tb .= 'Error: unsupport data format';
	            break;
	    }
	    return $tb;
	}
	
	static function _echo_as_xml($arr){
		foreach ($arr as $k => $val){
			$item = (is_numeric($k) ? 'item-':'').$k;
			echo '<', $item, '>';
			if(is_array($val)){
				self::_echo_as_xml($val);
			}else{
				echo '<![CDATA[',$val,']]>';
			}
			echo '</', $item, '>';
		}
	}
	
	function outarr(){
	    $out = null;
	    if(empty($this->echo['code'])){
	        $out = array('retcode'=>'SUCCESS', 'data'=>$this->echo['data']);
	    }else{
	        $out = array('retcode'=>$this->echo['code'], 'error'=>$this->echo['data'], 'data'=>null);
	    }
	    if(RTM_DEBUG){
	        $out['extra'] = $this->echo['extra'];
	    }
	    return $out;
	}

	function doecho(){
	    if(false !== strpos(PHP_SAPI, 'cli')){
	        if($this->echo['code'])
	           echo 'failed: '.$this->echo['code'], "\n";
	        echo 'data: ', "\n";
	        var_export($this->echo['data']);
	        echo "\n", 'extra: ', "\n";
	        var_export($this->echo['extra']);
	        echo "\n";
	        return;
	    }
	    
	    if('json' == $this->env['client_accept']
	        || 'xml' == $this->env['client_accept'])
	    {
	        if(empty($this->echo['code'])){
	            $out = array('retcode'=>'SUCCESS', 'data'=>$this->echo['data']);
	        }else{
	            $out = array('retcode'=>$this->echo['code'], 'error'=>$this->echo['data'], 'data'=>null);
	        }
	        if(RTM_DEBUG){
	           $out['extra'] = $this->echo['extra'];
	        }
	        if('json' == $this->env['client_accept'])
	            echo json_encode($out);
	        else{
	            echo '<?xml version="1.0" standalone="yes"?><response>';
	            self::_echo_as_xml($out);
	            echo '</response>';
	        }
	    }else{
	        if(empty($this->echo['data']) && empty($this->echo['code']) 
	            && empty($this->echo['url']) && empty($this->echo['extra']))
	            return;
	        $meta = '';
	        $style = empty($this->echo['code']) ? 'success' : 'error';
	        if(RTM_DEBUG){
	           $msg = empty($this->echo['data']) ? '' : '<p name=data>'.self::_print_data($this->echo['data']).'</p>';
	           $msg .= empty($this->echo['extra']) ? '' : '<p name=extra>'.self::_print_data($this->echo['extra']).'</p>';
	        }else{
	            $msg = $this->echo['code'] ? $this->lang('operation_success') : $this->echo['data'];
	        }
	        
	        if(empty($msg) && !empty($this->echo['url'])){
	            header('Location: '.$this->echo['url']);
	            return ;
	        }
	        else if(!empty($this->echo['url'])){
	            $meta = '<meta http-equiv="Refresh" content="'.(empty($this->echo['code'])? 3 : 8).';'.$this->echo['url'].'">';
	            $msg = sprintf('<p style="font-size: 12px;padding: 0 10px;">%s&nbsp;<a href="%s">%s</a></p>',
	                $this->lang('click_if_not_redirect', 'common'), $this->echo['url'], $this->echo['url']).$msg;
	        }
	        echo sprintf($this->lang('notice_page', 'common'), $meta, $style, str_replace("\n", '<br/>', $msg));
	    }
	}
	
	function htmlBacktrace($trace){
	    echo '<div class=error style="width:96%;"><table><tr><th>#</th><th>Function and Args</th><th>Location</th></tr>';
	    foreach($trace as $k => $arr){
	        echo '<tr><td>', $k, '</td><td>', 
	           isset($arr['class']) ? $arr['class']:'', 
	           isset($arr['type']) ? $arr['type'] : '', 
	           isset($arr['function']) ? $arr['function'] : '',
	           '()<br/>';
	        if(isset($arr['args'])){
    	        foreach($arr['args'] as $n => $a){
    	            echo '<div>$', $n, ')';
    	            if(is_array($a))
    	                echo 'array';
    	            else if(is_object($a))
    	                echo 'object&lt;',get_class($a),'&gt;';
    	            else if(is_resource($a))
    	                echo 'resource&lt;',get_resource_type($a),'&gt;';
    	            else 
    	                echo gettype($a),'&lt;',htmlspecialchars($a),'&gt;';
    	            echo '</div>';
    	        }
	        }
	        echo '</td><td>', 
	           isset($arr['file']) ? $arr['file']:'',
	           isset($arr['line']) ? ':'.$arr['line'] : '',
	           '</td></tr>';
	        
	    }
	    echo '</table></div>';
	}
	
	function echoex($data, $errcode='', $redirect_url=''){
	    if(isset($this->echo['code'])){
	        $this->echo_extra[] = 'code: '.$errcode.', data: '.json_encode($data);
	    }else{
    	    $this->echo['data'] = $data;
    	    $this->echo['code'] = $errcode;
    	    $this->echo['url']  = $redirect_url;
    	    if(!empty($errcode) && RTM_DEBUG && 'html' == $this->item('client_accept')){
    	        $trace = debug_backtrace(); // exclude the current function
    	        array_shift($trace);
    	        $this->htmlBacktrace($trace);
    	    }
	    }
	}
	
	function newURI($new_args){
		return $this->toURL($this->item('cur_action'), 
				$this->item('cur_mod'), array_merge($_GET, $new_args));
	}
}

?>
