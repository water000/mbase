<?php
if(defined('G_TOPBAR'))
	return;
define('G_TOPBAR', 
'<div class="dropmenuHeader">
    <div class="size">
    	<div class="menu">
    		<a href="TAG_URL_ACTION(index, aboutme)" %c1>Ҷ������</a>
    		<a href="TAG_URL_ACTION(collection, url_info)&rand=1" %c2>�������</a>
    		<a href="TAG_URL_ACTION(collection,tag_list)" %c3>Ŀ¼���</a>
    	</div>
    	<?php echo CHTMLSegment::getTopbar(CSystem::getUser())?>
    </div>
</div>');

define('G_TOPMANU', 
<<<EOD
<div class="le_tags">
    <div class="x-manu%s0">%ss0</div>
	<div class="x-manu x-manu-w107%s1">%ss1</div>
	<div class="x-manu x-manu-w107%s2">%ss2</div>
	<div class="aside">
	   <div class=mod id=db-sidesrh>
	     <form name="top_search_form" method="post" action="">
	       <input name="keyword" class="search-textbox" type="text" value="�����ǩ���û���" onfocus="if(this.value=='�����ǩ���û���') this.value='';" onblur="if(this.value=='') this.value='�����ǩ���û���';" autocomplete="off" /><input class=bn-srh value="��Ѱ" type="submit" />
         </form>
	   </div>
	</div>
</div>
<a class="logo" href="TAG_URL_ACTION(index, aboutme)"><img src="TAG_URL_IMG(common, logo-mini.gif)" height="44" width="111" alt=""/></a>
<script type="text/javascript">
$(document).ready(function(){
	$.loadFile.setFiles([{type:"js", url:"TAG_URL_JS(, txtSelect.js)", onload:function(){
		var form = document.top_search_form, inp=form.keyword, defVal = inp.value, urlHome="<?=CSystem::getActionURL('index','home')?>", urlList="<?=CSystem::getActionURL('collection','common_list')?>";
		form.action = urlList;
		inp.name = "tag";
		var onkeyup = function(str){
			str = str.trim().htmlspecialchars();
			return '' == str ? [] : ["��ǩΪ<span>"+str+"</span>����ҳ", "����Ϊ<span>"+str+"</span>����"];
		},
		onchange = function(obj, defval){var str = obj == null ? defval : obj.innerHTML;if("����" == str.substr(0, 2)){form.action = urlHome;inp.name="un";}else{form.action = urlList;inp.name="tag";}},
		check = function(str){
			var txt = inp.value, tm = txt.trim();
			if('' == tm) return false;
			if(tm == defVal) return false;
			inp.value = tm;
			return true;
		},
		onclick = function(str){if(check(str)) form.submit();};
		form.onsubmit = check;
		$.txtSelect(inp, onkeyup, onchange, onclick);
	}}]);
});
</script>
EOD
);

define('G_LEFTMANU', 
'<div class="site-menu-user-box ">
   <div class="site-menu-user"><a href="<?php if(isset($isOwner)&&$isOwner){?>TAG_URL_ACTION(user, make_avatar) <?php }else{ ?>TAG_URL_ACTION(index, home)&owner_id=<?php echo OWNER_ID; } ?>"><img src="<?=CUR_USER_AVATAR_URL?>" /></a></div>
   <div class="site-menu-user-info">
      <div class="name-and-icons"><img src="TAG_URL_IMG(collection,ico01.gif)" width="14" height="11" /><span class="description">12</span></div>
   </div>
</div>
<div class="site-menu-nav-box">
  <div%s0><img src="TAG_URL_IMG(collection,collect-ico.gif)" />&nbsp;%ss0</div>
  <div%s1><img src="TAG_URL_IMG(share,share-ico.gif)" />&nbsp;%ss1</div>
  <!-- <div%s2><img src="TAG_URL_IMG(comment,comment-ico.gif)" />&nbsp;%ss2</div> -->
  <div%s3><img src="TAG_URL_IMG(feeling,feeling-ico.gif)" />&nbsp;%ss3</div>
</div>');                     


if(0 == strcasecmp($_SERVER['SERVER_NAME'], 'yee.cn'))
define('G_SITE_STAT', 
<<<EOD
<script type="text/javascript">
var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F22eef2d7ee502afe95da70643233cee7' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript" id="IDS_BD">
$(document).ready(function(){var s1 = document.getElementById("IDS_BD");var iv = setInterval(function(){var n2 = s1.previousSibling;if(n2!=null && n2.tagName=="A"){n2.style.display = "none";clearInterval(iv);}}, 30)});
</script>
EOD
);
else
define('G_SITE_STAT','');


define('G_FOOTER', 
'<div class="g-footer">
  <div class="copy-detail">
    <div class="copy">&copy;2011��yee.cnҶ����Ȩ���С���ICP��11008630��</div>       
    <div class="yee-detail"><a href="TAG_URL_ACTION(site,aboutme)">����Ҷ��</a> | <a href="TAG_URL_ACTION(site,join)">��������</a> | <a href="TAG_URL_ACTION(site,contactus)">��ϵ����</a> | <a href="TAG_URL_ACTION(site,helpcenter)">��������</a> | <a href="TAG_URL_ACTION(site,service)">��������</a> </div>
  </div>
 </div>'.G_SITE_STAT);

define('G_PAGE_RIGHT_URL', 
<<<EOD
<div class="intro%s">
   <div class="img"><a href="%s"><img src="%s" width="95" height="72" /></a></div>
   <div class="from-w"><a href="%s" title="%s" class="name">%s</a></div>
</div>
EOD
);

define('G_COMMON_TITLE_SEG', 'Ҷ��������õ������ղؼ�');
define('G_COMMON_META_DESC', '<meta name="Description" content="Ҷ��������õ������ղؼУ���Ҷ������Է��ֶ����м�ֵ����վ�������ղ����������Դ��������ϲ������վ��������Ȥ����Ϣ" />');
define('G_COMMON_META_KEYS', '<meta name="Keywords" content="�����ղؼ�,������ǩ,qq��ǩ,��վ����,��վ��ȫ" />');
?>