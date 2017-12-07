
let _DOMAIN = "/";
let _AUTH_FILTER = null;

export function RestFetch_setDomain(d){
	_DOMAIN = d;
}
export function RestFetch_setAuthFilter(filter){
	_AUTH_FILTER = filter;
}

export default class RestFetch{
	
	constructor(opts){
		this.opts = {
			url : '',
			path: '',
			domain:_DOMAIN,
			accept:'application/json',
			mode:''	
		};
		this.setOpt(opts);
		this.setAuthFilter(_AUTH_FILTER);
		this.callbackPayload = null;
	}

	//setOpt("path", "/index")
	//setOpt({path:"/index"})
	setOpt(key, value){
		this.opts = Object.assign(this.opts, 1 == arguments.length ? key : {key : value});
		console.log(this.opts);
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

	setAuthFilter(filter){
		this.authFilter = filter;
		this.handle = filter != null ? this.authFetch : this._fetch;
	}

	_fetch(body, headers, method, url){
		console.log(body);
		return new Promise((resolve, reject) => {
			fetch(url || this.opts.domain+this.opts.path, {
				method : method || "GET",
				mode : this.opts.mode,
				headers : Object.assign({"Accept" : this.opts.accept}, headers || {}),
				credentials:"include",
				body
			})
			.then(rsp =>{
				return 200 == rsp.status ? resolve(rsp) : reject(rsp);
			})
			.catch(err=>{
				reject(err);
			});
		});
	}

	onAuthOk(){
		if(this.callbackPayload)
			this._fetch(this.callbackPayload.body, this.callbackPayload.headers, this.callbackPayload.method, this.callbackPayload.url)
				.then(rsp=>this.callbackPayload.resolve(rsp))
				.catch(err=>this.callbackPayload.reject(err));
	}

	authFetch(body, headers, method, url){
		return new Promise((resolve, reject) => {
			this._fetch(body, headers, method, url)
			.then(rsp=>resolve(rsp))
			.catch(rsp=>{
				if(rsp instanceof Response && 401 == rsp.status ){
					this.callbackPayload = { body, headers, method, url, resolve, reject};
					this.authFilter.onAuth(this);
					console.error('Auth required.');
				}
				else{
					console.error(rsp);
					reject(rsp);
				}
			});
		});
	}

	create(params, headers, url){
		return new Promise((resolve, reject) => {
			this.handle(params, headers, "POST", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

	select(params, headers, url){
		return new Promise((resolve, reject) => {
			this.handle(params, headers, "GET", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

	update(params, headers, url){
		return new Promise((resolve, reject) => {
			this.handle(params, headers, "PUT", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

	delete(params, headers, url){
		return new Promise((resolve, reject) => {
			this.handle(params, headers, "DELETE", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}
}
