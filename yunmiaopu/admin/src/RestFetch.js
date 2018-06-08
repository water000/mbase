
export default class RestFetch{
	
	constructor(opts){
		this.opts = {
			path: '',
			domain:'',
			accept:'',
			mode:'',
			catcher:null
		};
		if("string" === typeof opts)
			this.setOpt(Object.assign({domain:RestFetch._DOMAIN,accept:'application/json'}, {path:opts}));
		else
			this.setOpt(Object.assign({domain:RestFetch._DOMAIN,accept:'application/json'}, opts));
		this.callbackPayload = null;
	}

	//setOpt("path", "/index")
	//setOpt({path:"/index"})
	setOpt(key, value){
		let kv = 1 == arguments.length ? key : {key : value};
		this.opts = Object.assign(this.opts, kv);

		let idx = -1;
		if("string" == typeof kv.path && kv.path.length > 0 && (idx=this.opts.path.indexOf("://")) != -1){
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
		if(idx != -1 || kv.domain && kv.domain != '/'){
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
			reject = reject || console.error;

			fetch(url, {
				method,
				mode : this.opts.mode,
				headers : Object.assign({"Accept" : this.opts.accept}, headers || {}),
				credentials:"include",
				body
			})
			.then(rsp =>{
				if(200 == rsp.status)
					return resolve(rsp);
				this.callbackPayload = { body, headers, method, url, resolve, reject};
				(this.opts.catcher||RestFetch._CATCHER).handle(rsp, this, { body, headers, method, url});
			})
			.catch(err=>{
				this.callbackPayload = { body, headers, method, url, resolve, reject};
				(this.opts.catcher||RestFetch._CATCHER).handle(err, this, { body, headers, method, url});
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
		headers = headers || {};
		return new Promise((resolve, reject) => {
			reject = reject || console.error;
			if(params instanceof HTMLFormElement){
				params = new FormData(params);
			}else if(params instanceof FormData){
			}else if("object" == typeof params){
				params = new URLSearchParams(params);
			}
			this._fetch(params, headers, "POST", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

	select(params, headers, url){
		//console.log("select", params, typeof params, this.opts);
		if("object" == typeof params){
			params = new URLSearchParams(params);
			url = this.opts.domain+this.opts.path + '?' + params.toString();
		}
		else{
			url = this.opts.domain+this.opts.path + '/' + params;
		}
		params = null;
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

RestFetch._DOMAIN = "/";
RestFetch._CATCHER = console.log;

RestFetch.setDomain = (d)=>{ RestFetch._DOMAIN = d; }
RestFetch.setCatcher = (c)=>{ RestFetch._CATCHER = c; }
