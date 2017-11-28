

export default class RestFetch{
	
	construct(opts){
		this.opts = {
			url : '',
			accept:'application/json',
			mode:''	
		};
		this.setOpt(opts);
	}

	//setOpt("url", "/index")
	//setOpt({url:"/index"})
	setOpt(key, value){
		this.opts = Object.assign(this.opts, 1 == arguments.length ? key : {key : value});
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

	_fetch(body, method, url){
		fetch(url || this.opts.url, {
			method : method || "GET",
			mode : this.opts.mode
			headers : {
				"Accept" : this.opts.accept
			},
			body
		})
		.then(rsp =>{
			switch(rsp.status){
				case 200:
					let ct = rsp.headers.get("Content-Type");
					Promise.resolve(ct!=null && ct.toLowerCase().indexOf("/json") != -1 ? rsp.json() : rsp.text());
					break;
				case 401: // login required
					break;
				default:
					Promise.reject(rsp);
					break;
			}
		})
		.catch(err=>{
			console.log(err);
		});
	}

	create(params, url){
		return this._fetch(params, "POST", url);
	}

	select(params, url){
		return this._fetch(params, "GET", url);
	}

	update(params, url){
		return this._fetch(params, "PUT", url);
	}

	delete(params, url){
		return this._fetch(params, "DELETE", url);
	}

}