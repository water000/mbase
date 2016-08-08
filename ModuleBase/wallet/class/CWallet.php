<?php

class CWallet{
    
    static function transfer($wlt_ctr, $wlt_hty_ctr, $payer_uid, $payee_uid, $amount, $timestamp){

        $conn = $wlt_ctr->getDB()->getConnection();
        $conn->beginTransation();
        
        try {
            $wlt_ctr->setPrimaryKey($payer_uid);
            $afrows = $wlt_ctr->incr(-$amount);
            if( 0 == $afrows ){
                $ret = 'WALLET_BALANCE_NOT_ENOUGH';
            }else{
                $wlt_ctr->setPrimaryKey($payee_uid);
                if(0 == $wlt_ctr->incr($amount)){
                    $ret = 'WALLET_USER_TRANSER_ERR';
                    $conn->rollBack();
                }else{
                    $wlt_hty_ctr->add(array(
                        'a_uid'     => $payer_uid,
                        'b_uid'     => $payee_uid,
                        'amount'    => -$amount,
                        'create_ts' => $timestamp,
                        'type'      => CWalletHistoryCtr::tpconv('TASK_PAY')
                    ));
                    $wlt_hty_ctr->add(array(
                        'a_uid'     => $payee_uid,
                        'b_uid'     => $payer_uid,
                        'amount'    => $amount,
                        'create_ts' => $timestamp,
                        'type'      => CWalletHistoryCtr::tpconv('TASK_PAY')
                    ));
                }
            }
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
        
        $conn->commit();
    }
}

?>