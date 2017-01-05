<?php

$logs = array(
    'php_error',
    'ali_batch_trans',
    'ali_batch_trans_notify',
    //...more
);
$log_idx = 0;
$log_path = ini_get('error_log');
if(empty($log_path)) exit('no such log found');
if(isset($_REQUEST['log'])&&isset($logs[$_REQUEST['log']])){
    $log_path = pathinfo($log_path, PATHINFO_DIRNAME).'/'.$logs[$_REQUEST['log']].'.log';
    $log_idx  = $_REQUEST['log'];
}

$type = isset($_REQUEST['type']) && '1' == $_REQUEST['type'] ? 'head' : 'tail';

define('_LC_MAX_LINES_PER_PAGE', 100);
$line_amt = isset($_REQUEST['line_amt']) ? intval($_REQUEST['line_amt']) : 20;
$line_amt = $line_amt > 1 && $line_amt < 50 ? $line_amt : 20;

$more = isset($_REQUEST['more']) ? intval($_REQUEST['more']) : 1;
$more = $more <= 0 ? 1 : $more;
$dest_lines = $more * $line_amt;
$offset_line = 0;
if($dest_lines > _LC_MAX_LINES_PER_PAGE){
    $offset_line = $dest_lines - _LC_MAX_LINES_PER_PAGE;
    $dest_lines = _LC_MAX_LINES_PER_PAGE;
}
$_REQUEST['more'] = $more + 1;

$error = '';
$lines_data = array();
$fp = fopen($log_path, 'rb');
if(!$fp){
    $error = 'fail to open log using rb mod';
}else{
    $read_lines = 0;
    if('head' == $type)
        $lines_data = _head($fp, $offset_line, $dest_lines, $read_lines) ;
    else 
        $lines_data = _tail($fp, $offset_line, $dest_lines, $read_lines) ;
    fclose($fp);
    
    if($read_lines < $dest_lines){
        $error = 'only '.$read_lines.' lines exsits';
        $_REQUEST['more'] = 0;
    }
}

function _tail($fp, $offset, $lines, &$read_lines){
    $ret = array() ;
    $line = ''; 
    
    fseek($fp, 0, SEEK_END);
    $filesize = ftell($fp);
    $rlen   = 1024;
    $total_seek = 0;
    for( ;$lines>0 && $filesize > $total_seek; ){
        $to_seek = $filesize - $total_seek;
        $to_seek = $to_seek > $rlen ? $rlen : $to_seek;
        if(-1 == fseek($fp, -($to_seek+(0==$total_seek?0:$rlen)), SEEK_CUR)){
            break;
        }   
        $total_seek += $to_seek;
        $str = fread($fp, $to_seek);
        for($i=strlen($str)-1; $i>=0; --$i){
            if("\n" == $str[$i]){
                if(0 == $offset){
                    --$lines;
                    ++$read_lines;
                    array_unshift($ret, $line);
                    $line = ''; 
                    if(0 == $lines) break;
                }else{
                    --$offset;
                }   
            }else if(0 == $offset){
                $line = $str[$i] . $line;
            }   
        }   
    }   
    if(0 == $offset && !empty($line)) array_unshift($ret, $line);
    return $ret;
}

function _head($fp, $offset, $lines, &$read_lines){
    $ret = array() ;
    $line = '';
    for(;$lines>0;){
        $str = fread($fp, 1024);
        for($i=0, $j=strlen($str); $i<$j; ++$i){
            if("\n" == $str[$i]){
                if(0 == $offset){
                    --$lines;
                    ++$read_lines;
                    array_push($ret, $line);
                    $line = '';
                    if(0 == $lines) break;
                }else{
                    --$offset;
                }
            }else if(0 == $offset){
                $line .= $str[$i];
            }
        }
        if(feof($fp)) break;
    }
    if(0 == $offset && !empty($line)) array_unshift($ret, $line);
    return $ret;
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
body{word-spacing:5px;background-color:#eee;margin:0;padding:0;background:#fff1e0 none repeat scroll 0 0;}
h2{text-align:center;margin:0;letter-spacing:normal;background:#444;padding:8px 0;color:#fff;}
h2 span{font-size:12px;}
p{margin:6px 0;}
.line-box{padding:8px 6px;margin:5px 0;border-left:1px solid green;word-break: break-word;}
.line-box p{position:relative;padding-left:32px;}
.line-num{display:inline-block;width:30px;position:absolute;left:0;color: #A25A2E;}
#IDD_WIN{width:95%;margin: 20px auto; color:#333;display:block;}
.more{display:block;padding: 5px 0;margin:10px 0;text-align:right;color:green;text-decoration:underline;font-weight:bold;}
.more:hover{text-decoration:none;}
.line-sep{text-align:center;padding:0;margin:0;color:#08C;}
</style>
</head>
<body>
<h2>Head/Tail Logs</h2>
<div id="IDD_WIN" class="pure-g">
    <?php if(!empty($error)){ ?><div class=error><?php echo $error;?></div><?php } ?>
    <?php if('tail' == $type && $_REQUEST['more'] != 0){?><a class=more href="?<?php echo http_build_query($_REQUEST)?>">More...</a><?php }?>
    <div class=line-box contenteditable=true>
    <?php foreach($lines_data as $n => $line){?>
        <?php if('tail' == $type && $n >0 && 0 == $n%$line_amt){?><p class=line-sep>*</p><p class=line-sep>*</p><p class=line-sep>*</p><?php }?>
        <p><span class=line-num><?php echo 'head'==$type ? $offset_line + $n+1 : -$offset_line-$read_lines+$n?></span>
            <?php echo str_replace(RTM_LOG_TRACE_SEP, '<br/>', htmlspecialchars($line))?>&nbsp;</p>
        <?php if('head' == $type && $n >0 && 0 == $n%$line_amt){?><p class=line-sep>*</p><p class=line-sep>*</p><p class=line-sep>*</p><?php }?>
    <?php }?>
    </div>
    <?php if('head' == $type && $_REQUEST['more'] != 0){?><a class=more href="?<?php echo http_build_query($_REQUEST)?>">More...</a><?php }?>
    <div>
        <form action="" method="post" style="float: right;">
        <select name=type>
            <option value=0 <?php echo 'tail'==$type ? 'selected':''?>>tail</option>
            <option value=1 <?php echo 'head'==$type ? 'selected':''?>>head</option>
        </select>
        <input type=text name=line_amt style="width: 35px;" value=<?php echo $line_amt?> />&nbsp;lines from
        <select name=log>
        <?php foreach($logs as $n => $log){?>
            <option value=<?php echo $n?> <?php echo $n==$log_idx?'selected':''?>><?php echo $log?></option>
        <?php }?>
        </select>
        <input style="margin-left: 3px;" type=submit></form>
    </div>
    <div style="clear: both;"></div>
</div>
</body>
</html>