<?php
namespace modbase\common;

class CMultiRowOfTable extends CUniqRowOfTable
{
	protected $skeyname    = '';
	protected $secondKey  = null;
	protected $pageId     = 1;
	protected $numPerPage = 20;
	protected $orderSecondKey = true;
	protected $autoAssignLidOnScdKey = true; // auto assign the last insert id for second key on addNode called
	
	
	/*protected */function __construct($oPdoConn, $tbname, 
									$pkeyname, $primaryKey, 
									$skeyname, $secondKey=null)
	{
		parent::__construct($oPdoConn, $tbname, $pkeyname, $primaryKey);
		$this->skeyname   = $skeyname;
		$this->secondKey = $secondKey;
	}
	
	function setSecondKey($key)
	{
		$this->secondKey = $key;
	}
	function getSecondKey()
	{
		return $this->secondKey;
	}
	function getSecondKeyName()
	{
		return $this->skeyname;
	}
	function setPageId($pid=1)
	{
		$this->pageId = $pid;
	}
	function getPageId()
	{
		return $this->pageId;
	}
	function setNumPerPage($num)
	{
		$this->numPerPage = $num;
	}
	function getNumPerPage()
	{
		return $this->numPerPage;
	}
	function disableOrderSecondKey(){
		$this->orderSecondKey = false;
	}
	function setAutoAssign($s){
		$this->autoAssignLidOnScdKey = $s;
	}
	
	function get(){
		$sql = sprintf('SELECT * FROM %s WHERE %s=%d %s Limit %d, %d', 
			$this->tbname, $this->keyname, $this->primaryKey, 
			$this->orderSecondKey ? 'ORDER BY '.$this->skeyname.' DESC ': '',
			($this->pageId-1)*$this->numPerPage, $this->numPerPage);
		$pdos = $this->oPdoConn->query($sql);
		return $pdos->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	function getAll(){
		$sql = sprintf('SELECT * FROM %s WHERE %s=%d',
				$this->tbname, $this->keyname, $this->primaryKey);
		return $this->oPdoConn->query($sql);
	}
	
	function addNode(&$param){	
		$ret = false;
		$sql = sprintf('INSERT INTO %s(%s) VALUES(%s)', 
				$this->tbname, 
				implode(',', array_keys($param)), 
				str_repeat('?,', count($param)-1).'?'
		);
		
		$pdos = $this->oPdoConn->prepare($sql);
		$ret = $pdos->execute(array_values($param));
		if($ret !== false && $this->autoAssignLidOnScdKey){
			$ret = $this->secondKey = $param[$this->skeyname] = $this->oPdoConn->lastInsertId();
		}
		return $ret;
	} 
	
	function setNode($param){
		$sql = '';
		
		foreach($param as $k => $v){
			$sql .= sprintf('`%s`=?,', $k);
		}
		$sql = sprintf('UPDATE %s SET %s WHERE %s=%d AND %s=%d', 
			$this->tbname, 
			substr($sql, 0, -1), 
			$this->keyname, $this->primaryKey,
			$this->skeyname, $this->secondKey);
		$ret = false;
		$pre = $this->oPdoConn->prepare($sql);
		$ret = $pre->execute(array_values($param));
		if($ret === false){
			$this->_seterror($pre);
		}else{
			$ret = $pre->rowCount();
		}
		
		return $ret;
	}
	
	function getNode(){
		$sql = sprintf('SELECT * FROM %s WHERE %s=%d AND %s=%d', 
			$this->tbname, $this->keyname, $this->primaryKey,
			$this->skeyname, $this->secondKey);
		$pdos = $this->oPdoConn->query($sql);
		$ret = $pdos->fetchAll($this->fetch_type);
		$ret = empty($ret) ? array() : $ret[0];
		return $ret;
	}
	
	function delNode($condtions=array()){
		$sql = sprintf('DELETE FROM %s WHERE %s=%d AND %s=%d', 
 			$this->tbname, $this->keyname, $this->primaryKey,
 			$this->skeyname, $this->secondKey);
		
		if(!empty($condtions)){
			$sql .= ' AND '.implode('=? AND ', array_keys($condtions)).'=?';
		}
		
		if(empty($condtions)){
			$ret = $this->oPdoConn->exec($sql);
		}else{
			$pdos = $this->oPdoConn->prepare($sql);
			$ret = $pdos->execute(array_values($condtions));
			if($ret !== false){
				$ret = $pdos->rowCount();
			}
		}
		if($ret === false){
			$this->_seterror($this->oPdoConn);
		}
		
		return $ret;
	}
	
	function getTotal(){
		$sql = sprintf('SELECT count(1) FROM %s WHERE %s=%d', 
				$this->tbname, $this->keyname, $this->primaryKey);
		$ret = $this->oPdoConn->query($sql)->fetchAll();
		$ret = empty($ret) ? 0 : $ret[0][0];
		return $ret;
	}
}

?>