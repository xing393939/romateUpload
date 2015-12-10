(function(){
	var id = +(new Date());
	var isIE =UTIL.isIE;
	var head = document.head ? document.head : document.getElementsByTagName('head')[0];
	var JSONPRequest = function(option){
        if(window.MessageBox && !option.isHideLoading){
            MessageBox.showMsg();
        };
        var script = document.createElement('script'),
            responseData,
			callBack = option.callBack,
			callbackName = 'PPyunfront' + (++id),
			removeScript = function(){
				if(script.onload)
					script.onload = null;
				if(script.onreadystatechange)
					script.onreadystatechange = null;
				if(script.onerror) script.onerror = null;
				if(responseData && callBack){
					callBack(responseData[0]);
				};
				if(!responseData){
					errorCallback();
				}
				head.removeChild(script);
				callBack = responseData = null;
                if(window.MessageBox){
                    MessageBox.hideMsg();
                };
            },
			errorCallback = option.errorCallback || function(){
                if(window.MessageBox){
                    MessageBox.hideMsg();
                };
				alert('数据加载失败');
			};
		script.type= 'text/javascript';
		if (isIE && UTIL.isIEVersion <= 8 && UTIL.isIEVersion >= 5.5){//IE
			script.onreadystatechange = function(){
				if (script.readyState == "loaded" || script.readyState == "complete"){
					removeScript();
				}
			};
		} else {//Others
			script.onload = removeScript;
		}
		
		window[callbackName] = function(){
		  responseData = arguments;
		};
		
		if(!isIE || UTIL.isIEVersion >=9){
			script.onerror = removeScript;
		}
		
		script.src = option.url + '&cb=' + callbackName;
		head.appendChild(script);
	};
	window.JSONPRequest = window.JSONPRequest || JSONPRequest;
})();