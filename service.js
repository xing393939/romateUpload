(function(){
	
	var addDomEvent = UTIL.addDomEvent;
	var fixedEvent = UTIL.fixedEvent; 
	var filterElemByAttrVal = UTIL.dom.filterElemByAttrVal;
	
	var initComps = function(){
		var actionMap = this.actionMap;
		var parent = this.parent;
		var map = [];
		var tElem = null;
		for(var p in actionMap){
			map = p.split('.');
			tElem = filterElemByAttrVal(parent, map[0], map[1], map[2]);
			l = tElem.length;
			if(l < 1) return;
			for(var i = 0;i < l;i++){
				tElem[i].setAttribute('data-action', p);
			}
		}
	};
	
	var initEvent = function(){
		var actionMap = this.actionMap;
		var actiontype = this.actiontype;
		if(this.parent)
			addDomEvent(this.parent, actiontype, function(e){
				fixedEvent(e);
				var target = e.target;
				var dc = target.getAttribute('data-action');
				if(dc){
					actionMap[dc] && actionMap[dc].call(target, target, e);
				}
				
			});
	};
	
	var EventService = function(opt){
		this.parent = opt.parent || null;
		this.actiontype = opt.actiontype || 'click'; 
		this.actionMap = opt.actionMap || {};
		this.init();
	};
	
	EventService.prototype = {
			
		init: function(){
			initEvent.call(this);
			initComps.call(this);
		}
			
	};
	
	window.EventService = window.EventService || EventService;
	
})();