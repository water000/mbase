<?php
namespace modbase\common;
     

class CSysMsg{
    static function send($to_uid, $data){
        global $mbs_appenv;
        
        $serv = $mbs_appenv->config('sysmsg_server', 'common');
        list($ip, $port) = explode(':', $serv);
        $sock = fsockopen($ip, $port, $errno, $errmsg, 10);
        if($sock === false){
            trigger_error($errmsg);
            return false;
        }
        fwrite($sock, sprintf("To-Userid: %d\nData: %s\n", $to_uid, $data));
        sleep(1);
        fclose($sock);
        return true;
    }
}

?>