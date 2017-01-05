<?php 

use modbase\common\CCommonCityDictCtr,
    modbase\common\CCommonProvinceDictCtr,
    modbase\common\CDbPool,
    modbase\common\CMemcachedPool;

if(isset($_REQUEST['prov_code'])){
    $city = CCommonCityDictCtr::getInstance($mbs_appenv, 
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    $list = $city->getDB()->getConnection()->query(sprintf('SELECT code, name FROM %s WHERE prov_code=%d',
        $city->getDB()->tbname(), intval($_REQUEST['prov_code'])))->fetchAll(PDO::FETCH_ASSOC);
}else{
    $prov = CCommonProvinceDictCtr::getInstance($mbs_appenv,
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    $list = $prov->getDB()->listAll()->fetchAll(PDO::FETCH_ASSOC);
}

$mbs_appenv->echoex($list);

?>