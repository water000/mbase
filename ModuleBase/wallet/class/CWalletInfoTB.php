<?php

class CWalletInfoTB extends CUniqRowOfTable{
    function incr($amount){
        $sql = sprintf('UPDATE %s SET amount = amount+%d WHERE uid=%d AND %s ',
            $this->tbname, $amount, $this->primaryKey, 
            $amount<0 ? sprintf('amount-withdraw_amount>%d', -$amount) : '1');
        return $this->oPdoConn->exec($sql);
    }
}

?>