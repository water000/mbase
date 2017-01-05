<?php
namespace modbase\core;

class CDBLogAPI extends CLogAPI{
    private $pdoconn;
    private $dbpool;
    private $table = '';

    function __construct($dbpool){
        $this->dbpool = $dbpool;
        $this->table = mbs_tbname('core_api_log');
    }

    function write(array $output, $other=''){
        parent::write($output, $other);
        if(!$this->pdoconn) $this->pdoconn = $this->dbpool->getDefaultConnection();
        $pdos = $this->pdoconn->prepare(sprintf(
            'INSERT INTO %s(input, output, time, other) values(?, ?, ?, ?)', $this->table));
        return $pdos->execute(array_values($this->elems));
    }

    function read($timeline, $limit = 10){
        if(!$this->pdoconn) $this->pdoconn = $this->dbpool->getDefaultConnection();
        return $this->pdoconn->query(sprintf('SELECT * FROM %s WHERE time>=%d ORDER BY id desc limit %d ',
            $this->table, $timeline, $limit));
    }
}

?>