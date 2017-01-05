<?php
namespace modbase\common;

class CUniqRowControl
{
	protected $oDB        = null;
	protected $oCache     = null;
	protected $primaryKey = null;
	protected $arrBuf     = array();
	protected $error      = '';
	
	protected $listener   = array(
	    'add'     => array(),
	    'set'     => array(),
	    'destroy' => array(),
	    'addNode' => array(),
	    'setNode' => array(),
	    'delNode' => array(),
	);
	
	/**
	 * 
	 * @param CUniqRowOfTable $db the instance that extends the interface 'CUniqRowOfTable'
	 * @param CUniqRowOfCache $cache the instance that extends the interface 'CUniqRowOfCache'
	 * @param variable $primaryKey the primary key of the object
	 */
	protected function __construct($db, $cache=null, $primaryKey=null)
	{
		$this->oDB = $db;
		$this->oCache = $cache;
		$this->append($primaryKey);
	}
	
	function append($key)
	{
		$this->primaryKey = $key;
		$this->oDB->setPrimaryKey($key);
		if($this->oCache)
			$this->oCache->setPrimaryKey($key);
	}
	
	/**
	 * set the db connection or(and) memcahce connection if they are not empty
	 * @param resource $dbconn the database connection resource that implements by class PDO
	 * @param resource $memconn the memcache connection resource that implements by class Memcached
	 * @return empty
	 */
	function setConnection($dbconn=null, $memconn=null)
	{
		if(!empty($dbconn))
			$this->oDB->setConnection($dbconn);
		if(!empty($memconn) && $this->oCache)
			$this->oCache->setConnection($memconn);
	}
	
	function getDB()
	{
		return $this->oDB;
	}
	
	function getCache()
	{
		return $this->oCache;
	}
	
	function setPrimaryKey($key)
	{
		$this->append($key);
	}
	
	function getPrimaryKey()
	{
		return $this->primaryKey;
	}
	
	function error(){
		return $this->oDB->error();
	}
	
	function add($arr)
	{
		
		$prikey = $this->oDB->add($arr);
		if($prikey){
			$this->append($prikey);
			$this->arrBuf[$prikey] = $arr;
			
			if($this->oCache)
				$this->oCache->set($arr); // use the 'set' to replace 'add' which that the multi-add will cause failure
		}
		
		$this->consume(__FUNCTION__, $arr);
		
		return $prikey;
	}
	
	function get()
	{
		if(isset($this->arrBuf[$this->primaryKey]))
			return $this->arrBuf[$this->primaryKey];
		
		$arr = null;
		if($this->oCache)
		{
			$arr = $this->oCache->get();
			if(false === $arr)
			{
				$arr = $this->oDB->get();
				$this->oCache->set($arr);
			}
		}
		else
		{
			$arr = $this->oDB->get();
		}
		$this->arrBuf[$this->primaryKey] = $arr;
		return $arr;
	}

	function union($map)
	{
		$diff = array_diff_key($map, $this->arrBuf);
		$itc = array_intersect_key($this->arrBuf, $map);
		
		if(!empty($diff))
		{
			if($this->oCache)
			{
				$ret = $this->oCache->getMulti(array_keys($diff));
				if($ret !== false)
				{
					$diff = array_diff_key($diff, $ret);
					$itc += $ret;
					$this->arrBuf += $ret;
				}
			}
			if(!empty($diff))
			{
				$ret = $this->oDB->union(array_keys($diff));
				if($this->oCache)
					$this->oCache->setMulti($ret);
				$itc += $ret;
				$this->arrBuf += $ret;
			}
		}
		
		return $itc;
	}
	
	function set($newcache)
	{
		$ret = $this->oDB->set($newcache);
		if($this->oCache && $ret > 0){
		    $info = $this->get();
		    if(!empty($info)){
		        $newcache = array_merge($info, $newcache);
			    $this->oCache->set($newcache);
		    }
		}
		$this->arrBuf[$this->primaryKey] = $newcache;
		
		$this->consume(__FUNCTION__, $newcache);
		
		return $ret;
	}
	
	function destroy($condtions=array())
	{
		$ret = $this->oDB->del($condtions);
		if($this->oCache && $ret>0)
			$this->oCache->destroy();
		unset($this->arrBuf[$this->primaryKey]);
		
		$this->consume(__FUNCTION__, array($this->oDB->keyname()=>$this->primaryKey));
		return $ret;
	}
	
	function produce($func, $lis, $detail){
	    if(isset($this->listener[$func])){
	        $this->listener[$func] = array($detail, $lis);
	    }
	}
	
	function consume($func, $args){
	    if(!empty($this->listener[$func])){
	        foreach($this->listener[$func][1] as $modresp){
	            list($mod, $resp) = explode('.', $modresp);
	            $resp::response($this->listener[$func][0], $args);
	        }
	    }
	}
}
?>