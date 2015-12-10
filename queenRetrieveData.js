(function(){//��ȡ�������˵����
		var isRequestDone = false; //�ж���һ�ε�jsonp�Ļص������Ƿ�ִ�����
		var queens = [];
		var cleverTimeCnt = null;
		
		var nextRequest = function(options){
			return function(){
				var url = options.apiUrl + options.param || '';
				JSONPRequest({
					'type': 'get', 
					'url': url, 
					'async': true,
					'callBack': options.callback,
					'errorCallback': options.errorCallback,
					'isHideLoading':options.isHideLoading
				});
			};
		};
		
		var nextMessage = function(options){
			options && add(nextRequest(options));
			if(!isRequestDone){
				isRequestDone = true;
				var exCxt = queens.shift();
				if(exCxt){
					exCxt();
				}else{
					clearTimeout(cleverTimeCnt);
					isRequestDone = false;
					if(lastCallBack) lastCallBack();
				}
			}
		};
		
		var reset = function(cb){
			cleverTimeCnt = setTimeout(function(){
				isRequestDone = false;
				nextMessage();
			}, 10);
		};
		var add = function(msg){
			queens.push(msg);
		};
		var lastCallBack = null;
		var addLastCallBack = function(cb, cxt){
			lastCallBack = function(){
				cb.call(cxt);
			};
		};
		
		window.queenRetrieveData = window.queenRetrieveData || {
			nextRequest: nextMessage,
			reset: reset,
			add: add,
			addLastCallBack: addLastCallBack
		};
	})();	