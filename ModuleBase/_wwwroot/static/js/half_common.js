function topManuSearch(form){
	if("function" == typeof($.txtSelected))
		return;
	//inptxt, onkeyup, onchange, onclick
	$.loadFile.setFiles([{type:"js", url:"TAG_URL_JS(, txtSelect.js)", onload:function(){
		form.src = "USER";
		var onkeyup = function(str){
			str = str.trim().htmlspecialchars();
			return '' == str ? [] : ["����Ϊ<span style='color:red;'>"+str+"</span>����", "��ǩΪ<span style='color:red;'>"+str+"</span>����ҳ"];
		},
		onchange = function(str){form.src = "����" == str.substr(0, 2) ? "USER" : "PAGE";},
		check = function(str){
			var txt = form.keyword.value, tm = txt.trim();
			if('' == tm) return false;
			form.keyword.value = tm;
			return true;
		},
		onclick = function(str){if(check(str)) form.submit();};
		form.onsubmit = check;
		$.txtSelected(form.keyword, onkeyup, onchage, onclick);
	}}]);
}