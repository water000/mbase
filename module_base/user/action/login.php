<?php

use modbase\common\CStrTools,
    modbase\common\CDbPool,
    modbase\common\CMemcachedPool;

use modbase\user\CUserSession, 
    modbase\user\CUserDeviceCtr, 
    modbase\user\CUserInfoCtr;

define('REDIRECT_AFTER_LOGIN', isset($_REQUEST['redirect'])
    ? urldecode($_REQUEST['redirect']) : $mbs_appenv->toURL('index', 'privilege'));

$error = array();


if(isset($_COOKIE[session_name()])){
    $us = new CUserSession();
    $user_info = $us->get();
    if(!empty($user_info)){
        //had_login
        $mbs_appenv->echoex(array('user'=>$user_info[1], 'token'=>session_id()), '', REDIRECT_AFTER_LOGIN);
        exit(0);
    }else{
	    if($mbs_appenv->item('client_accept') != 'html'){ // expired
	        //$mbs_appenv->echoex('invalid token! clear and retry', 'USER_LOGIN_ERR');
	        //exit(0);
	    }else if(isset($_REQUEST['captcha'])){
	        if(!isset($_SESSION)) 
	            session_start();
	        if(isset($_SESSION['common_img_captcha'])){
    	        if($_SESSION['common_img_captcha'] != strtoupper($_REQUEST['captcha'])){
    	            $error[] = $mbs_appenv->lang('invalid_captcha');
    	        }else{
    	            unset($_SESSION['common_img_captcha']);
    	        }
	        }else{
	            $p = session_get_cookie_params();
	            setcookie(session_name(), '', time() - 3600, $p['path'],
	                $p['domain'], $p['secure'], $p['httponly']);
	            $mbs_appenv->echoex('unknown error', 'USER_LOGIN_ERR');
	            exit(0);
	        }
	    }else{
	        $p = session_get_cookie_params();
	        setcookie(session_name(), '', time() - 3600, $p['path'],
	            $p['domain'], $p['secure'], $p['httponly']);
	        if(isset($_REQUEST['phone'])){
	           $mbs_appenv->echoex('invalid token! clear and <a href="">retry</a>', 'USER_LOGIN_ERR');
	           exit(0);
	        }
	    }
    }
}

if(isset($_REQUEST['phone'])){
	if(isset($_REQUEST['need_testing_cookie'])){
		if(!isset($_COOKIE['is_cookie_available'])){
			$error[] = 'cookie is unavailable on your browser!configured and <a href="">retry</a>';
			define('NEED_TESTING_COOKIE', 1);
		}else{
			setcookie('is_cookie_available', '', time()-1000);
		}
	}
	
	if(!CStrTools::isValidPhone($_REQUEST['phone'])){
		$error[] = $mbs_appenv->lang('invalid_phone');
	}
	if(!CStrTools::isValidPassword($_REQUEST['password'])){
		$error[] = $mbs_appenv->lang('invalid_password');
	}
	
	if(isset($_REQUEST['remember_me'])){
		session_set_cookie_params(time()+15*24*3600);
	}

	$error_code = 'LOGIN_FAILED';
	if(empty($error)){
		$uc = CUserInfoCtr::getInstance($mbs_appenv, CDbPool::getInstance(), 
		    CMemcachedPool::getInstance());
		$rs = null;
		$rs = $uc->search(array('phone'=>$_REQUEST['phone']));
		if(empty($rs) || !($rs = $rs->fetchAll(\PDO::FETCH_ASSOC))){
			$error[] = $mbs_appenv->lang('invalid_phone');
		}
		else if(!CUserInfoCtr::passwordVerify($_REQUEST['password'], $rs[0]['password'])){
		    $error[] = $mbs_appenv->lang('invalid_password');
		}
		else{
		    //header('P3P: CP=CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR');
		    
			$rs = $rs[0];
			$us = isset($us) ? $us : new CUserSession();
			$us->set($rs['id'], $rs);
			$sid = session_id();
			$mbs_appenv->echoex(array('user'=>$rs, 'token'=>$sid), '', REDIRECT_AFTER_LOGIN);
			session_write_close();
		
			$os = 'WINDOWS';
			if(isset($_REQUEST['device_desc'])){
			    switch (strtoupper($_REQUEST['device_desc'])){
			        case 'ANDROID':
			            $os = 'ANDROID';
			        case 'IPHONE':
			            $os = 'IOS';
			    }
			}
			$dev = array(
			    'uid'      => $rs['id'],
			    'sid'      => $sid,
			    'token'    => isset($_REQUEST['device_token']) ? $_REQUEST['device_token'] : '',
			    'ip'       => $mbs_appenv->item('client_ip'),
			    'type'     => CUserDeviceCtr::sttype(isset($_REQUEST['device_desc'])?'PHONE':'PC'),
			    'os'       => CUserDeviceCtr::stos($os) ,
			    'login_ts' => time(),
			);
			$udev = CUserDeviceCtr::getInstance($mbs_appenv,
			    CDbPool::getInstance(), CMemcachedPool::getInstance(), $rs['id']);
			$udev_info = $udev->get();
			if(empty($udev_info)){
			    $udev->add($dev);
			}else{
			    $udev->set($dev);
			    
			    if(!empty($udev_info['sid'])){
    			    ini_set('session.use_cookies', 0);
    			    session_id($udev_info['sid']);
    			    session_start();
    			    session_destroy();
			    }
			}
			exit(0);
		}
	}
	if(!empty($error) && $mbs_appenv->item('client_accept') != 'html'){
		$mbs_appenv->echoex(implode(';', $error), $error_code);
		exit(0);
	}
}
else{
	if(ini_get('session.use_cookies') && empty($_COOKIE)){
		setcookie('is_cookie_available', 'yes', time() + 365*86400); // for checking whether the client supporting cookies
		define('NEED_TESTING_COOKIE', 1);
	}
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('reset.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('style.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('iconfont.css')?>" rel="stylesheet">
</head>
<body style="background: #1b3b6a;">
<div class="login_div">
		<div class="logo"></div>
		<div class="logo_text"></div>
		<form name=myform action="" method="post">
    		<div class="logo_form">
    			<div class="layer">
    				<i class="iconfont">&#xe60f;</i>
    				<input type="text" class="inp" name="phone" 
            	       placeholder="<?php echo $mbs_appenv->lang(array('please_input', 'phone'))?>" />
    			</div>
    			<div class="layer">
    				<i class="iconfont">&#xe603;</i>
    				<input type="password" class="inp" name="password" 
            	       placeholder="<?php echo $mbs_appenv->lang(array('please_input', 'password'))?>" />
    			</div>
    			<?php if((isset($_REQUEST['phone']) && !empty($error) || isset($_SESSION['common_img_captcha']))){?>
                <div class="layer">
                    <i class="iconfont">&#xe603;</i>
                    <input id="captcha" type="text" name="captcha" class="inp" style="width: 135px;" 
                    	placeholder="<?php echo $mbs_appenv->lang(array('please_input', 'captcha'))?>" />
            		<img alt="<?php echo $mbs_appenv->lang('captcha')?>"  src="<?php echo $mbs_appenv->toURL('img_captcha', 'common')?>" 
            		/><a href="#"  style="vertical-align: bottom;font-size:12px;" onclick="this.previousSibling.src='<?php echo $mbs_appenv->toURL('img_captcha', 'common')?>?n='+Math.random();"><?php echo $mbs_appenv->lang('reload_on_unclear')?></a>
        		</div>
        	   <?php } ?>
    			<div class="auto_login">
    				<input type="checkbox" class="top2" name="remember_me" />
    				<?php echo $mbs_appenv->lang('auto_login_in_next')?>
    			</div>
    			<button type="submit" style="display:block;width:100%;"  class="logo_btn">
    			 <span><?php echo $mbs_appenv->lang('login')?></span><i class="iconfont">&#xe60d;</i></button>
    		</div>
    	</form>
	</div>
	<footer><?php echo $mbs_appenv->lang('foot')?></footer>
<script type="text/javascript">
document.myform.elements["phone"].focus();
</script>
</body>
</html>