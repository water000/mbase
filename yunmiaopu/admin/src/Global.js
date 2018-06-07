
var cfg = {
	domain:{
		api:'http://127.0.0.1:8080',
		img:'http://127.0.0.1:8080',
	}
}

export default class Global{
}

Global.apiUrl=(url)=>{
	return cfg.domain.api + url;
}

Global.imgUrl=(url)=>{
	return cfg.domain.img + url;
}