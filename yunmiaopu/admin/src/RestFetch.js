
let _DOMAIN = "/";
let _AUTH_FILTER = null;
let _CATCHER = console.error;

export function RestFetch_setDomain(d){
	_DOMAIN = d;
}
export function RestFetch_setCatcher(c){
	_CATCHER = c;
}

export default class RestFetch{
	
	constructor(opts){
		this.opts = {
			path: '',
			domain:_DOMAIN,
			accept:'application/json',
			mode:''	
		};
		if("string" === typeof opts)
			this.setOpt({path:opts});
		else
			this.setOpt(opts);
		this.callbackPayload = null;
	}

	//setOpt("path", "/index")
	//setOpt({path:"/index"})
	setOpt(key, value){
		this.opts = Object.assign(this.opts, 1 == arguments.length ? key : {key : value});
		let idx;
		if((idx=this.opts.path.indexOf("://")) != -1){
			let pathidx = this.opts.path.indexOf("/", idx+3);
			this.opts.domain = pathidx > 0 ? this.opts.path.substr(0, pathidx) : this.opts.path;
			if(pathidx > 0){
				this.opts.domain = this.opts.path.substr(0, pathidx);
				this.opts.path   = this.opts.path.substr(pathidx);
			}else{
				this.opts.domain = this.opts.path;
				this.opts.path   = "/";
			}
		}
		if(this.opts.domain != '' && this.opts.domain != '/'){
			var a = new URL(this.opts.domain);
			if(a.protocol != document.location.protocol 
				|| a.hostname != document.location.hostname
				|| a.port != document.location.port)
			{
				this.opts.mode = "cors";
			}
		}
	}

	_fetch(body, headers, method, url){
		return new Promise((resolve, reject) => {
			url = url || this.opts.domain+this.opts.path;
			headers = headers || {};
			method = method || "GET";

			if(null == body || body instanceof String){}
			else if(body instanceof HTMLFormElement){
				body = new FormData(body);
				headers["Content-Type"] = "multipart/form-data; boundary="+(new Date().getMilliseconds());
			}else {
				body = new URLSearchParams(body);
				if("POST" == method.toUpperCase()){
					headers["Content-Type"] = "application/x-www-form-urlencoded;charset=UTF-8";
				}else{
					url += '?'+body.toString();
					body = null; // GET can not have body
				}
			}

			fetch(url, {
				method,
				mode : this.opts.mode,
				headers : Object.assign({"Accept" : this.opts.accept}, headers || {}),
				credentials:"include",
				body
			})
			.then(rsp =>{
				return 200 == rsp.status ? resolve(rsp) : _CATCHER.handle(rsp, this, { body, headers, method, url});
			})
			.catch(err=>{
				this.callbackPayload = { body, headers, method, url, resolve, reject};
				_CATCHER.handle(err, this, { body, headers, method, url});
			});
		});
	}

	retry(){
		if(this.callbackPayload){
			this._fetch(this.callbackPayload.body, this.callbackPayload.headers, this.callbackPayload.method, this.callbackPayload.url)
				.then(rsp=>{this.callbackPayload.resolve(rsp);this.callbackPayload = null;})
				.catch(err=>{this.callbackPayload.reject(err);this.callbackPayload = null;});
		}
	}

	create(params, headers, url){
		return new Promise((resolve, reject) => {
			reject = reject || console.error;
			this._fetch(params, headers, "POST", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

	select(params, headers, url){
		return new Promise((resolve, reject) => {
			reject = reject || console.error;
			this._fetch(params, headers, "GET", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

	update(params, headers, url){
		return new Promise((resolve, reject) => {
			reject = reject || console.error;
			this._fetch(params, headers, "PUT", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

	delete(params, headers, url){
		return new Promise((resolve, reject) => {
			reject = reject || console.error;
			this._fetch(params, headers, "DELETE", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}
}
