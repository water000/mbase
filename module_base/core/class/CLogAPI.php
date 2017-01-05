<?php
namespace modbase\core;
      



class CLogAPI{
	protected $elems;
	
	function __construct(){
		
	}
	
	static function _format($arr){
		$ret = '';
		foreach($arr as $k=>$v){
			$ret .= $k.':'.(is_array($v)?var_export($v, true):$v)."\n";
		}
		return $ret;
	}
	
	static function _files(){
	    $ret = '';
	    foreach ($_FILES as $name => $v){
	        if(is_array($v['size'])){
	            for($i=0, $j=count($v['size']); $i<$j; ++$i){
	                $ret .= sprintf("%s:%s(%d)\n", $name.'['.$i.']', $v['name'][$i], $v['size'][$i]);
	            }
	        }else{
	            $ret .= sprintf("%s:%s(%d)\n", $name, $v['name'], $v['size']);
	        }
	    }
	    return $ret;
	}

	function write(array $output, $other=''){
		static $need = array(
			'SCRIPT_URI'     =>'',
			'HTTP_ACCEPT'    => '',
			'CONTENT_TYPE'   => '',
			'REQUEST_URI'    => '',
			'REMOTE_ADDR'    => '',
			'REQUEST_METHOD' => '',
			'HTTP_X_LOGIN_TOKEN'  => '',
		);
		$this->elems = array(
			'input'  => '[SERVER]'."\n".self::_format(array_intersect_key($_SERVER, $need))."\r\n"
						.'[COOKIE]'."\n".self::_format($_COOKIE)."\r\n"
						.'[REQUEST]'."\n".self::_format($_REQUEST)."\r\n"
		                .'[FILES]'."\n".self::_files(),
			'output' => var_export($output, true)."\n[headers]\n".var_export(headers_list(), true),
			'time'   => time(),
			'other'  => $other,
		);
	}
	
	function read($timeline){
		
	}
	
}



?>