

export default class RestFetch{
	
	constructor(opts){
		this.opts = {
			url : '',
			accept:'application/json',
			mode:''	
		};
		this.setOpt(opts);
		this.authFilter = null;
	}

	//setOpt("url", "/index")
	//setOpt({url:"/index"})
	setOpt(key, value){
		this.opts = Object.assign(this.opts, 1 == arguments.length ? key : {key : value});
		console.log(this.opts);
		if(!this.opts.url){
			throw new Error("The url is required but not specified");
		}
		if(!this.opts.mode){
			var a = new URL(this.opts.url);
			if(a.protocol != document.location.protocol 
				|| a.hostname != document.location.hostname
				|| a.port != document.location.port)
			{
				this.opts.mode = "cors";
			}
		}

	}

	setAuthFetch(filter){
		this.authFetch = filter;
	}

	_fetch(body, headers, method, url){
		return new Promise((resolve, reject) => {
			fetch(url || this.opts.url, {
				method : method || "GET",
				mode : this.opts.mode,
				headers : Object.assign({"Accept" : this.opts.accept}, headers || {}),
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

	authFetch(body, headers, method, url){
		return new Promise((resolve, reject) => {
			this._fetch(body, headers, method, url).then(rsp=>rsp)
			.catch(rsp=>{
				if(! rsp instanceof Response || rsp.status != 401){
					console.error(rsp);
					reject(rsp);
				}
				else{
					if(this.authFetch)
						this.authFetch(rsp, user=>{
							this._fetch(body, headers, method, url).then(rsp=>resolve(rsp));// retry on auth success
						});
					else{
						console.error('Auth required.');
						reject('Auth required.');
					}
				}
			});
		});
	}

	create(params, headers, url){
		return new Promise((resolve, reject) => {
			this.authFetch(params, headers, "POST", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

	select(params, headers, url){
		return new Promise((resolve, reject) => {
			this.authFetch(params, headers, "GET", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

	update(params, headers, url){
		return new Promise((resolve, reject) => {
			this.authFetch(params, headers, "PUT", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

	delete(params, headers, url){
		return new Promise((resolve, reject) => {
			this.authFetch(params, headers, "DELETE", url)
				.then(rsp=>resolve(rsp))
				.catch(rsp=>reject(rsp));
		});
	}

}