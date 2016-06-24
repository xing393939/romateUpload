(function(){
	var cloneObject = UTIL.cloneObject;
	copyObject = UTIL.copyObject;

	var Data = function(opt){
		this.dataList = [];
		this.cacheData = [];
		this.params = opt.params || {};
		this.requestOption = opt.requestOption || {};
		this.requestProxy = opt.requestProxy || simpleRetrieveData;
		this.isHideLoading = opt.isHideLoading;
	};

	Data.prototype = {
		addData: function(data){
			this.dataList.push(data);
		},
		addParams: function(params){
			UTIL.paramsToURIEncoding(params);
			copyObject(params,this.params);
		},
		nextRequest: function(opt){
			var ropt = cloneObject(this);
			if(opt){
				if(opt.params)
					copyObject(opt.params,ropt.params);
				if(opt.requestOption)
					copyObject(opt.requestOption,ropt.requestOption);
			}
			ropt.requestOption.param = UTIL.paramsToURIComps(ropt.params);
			ropt = ropt.requestOption;
			ropt.isHideLoading = this.isHideLoading;
			this.requestProxy.nextRequest(ropt);
		},
		reset: function(cb){
			this.requestProxy.reset(cb);
		}
	};
	window.Data = window.Data || Data;

})();