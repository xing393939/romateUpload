(function(){
	var slice = Array.prototype.slice;
	
	var nextRequest = function(args){
		var cxt = args.pop();
		var cb = args.pop();
		return function(){
			cb.apply(cxt, args);
		};
	};
	
	var ExcutionQueen = function(){
		this.queens = [];
		this.cleverTimeCnt = null;
		this.lastCallBack = null;
		this.isRequestDone = false;
		this.isPause = false;
	};
	
	ExcutionQueen.prototype = {
		nextMessage: function(){
			var args = slice.call(arguments);
			args.length && this.add(args);
			if(this.isPause) return;
			if(!this.isRequestDone){
				this.isRequestDone = true;
				var exCxt = this.queens.shift();
				if(exCxt){
					exCxt();
				}else{
					clearTimeout(this.cleverTimeCnt);
					this.isRequestDone = false;
					if(this.lastCallBack) this.lastCallBack();
				}
			}
		},
		add: function(){
			var args = arguments[0];
			if(arguments.length > 1)
				args = slice.call(arguments);
			
			this.queens.push(nextRequest(args));
		},
		reset: function(){
			var self = this;
			this.cleverTimeCnt = setTimeout(function(){
				self.isRequestDone = false;
				self.nextMessage();
			}, 10);
		},
		addLastCallBack: function(cb, cxt){
			this.lastCallBack = function(){
				cb.call(cxt);
			};
		},
		setPause: function(isBol){
			this.isPause = isBol;
		},
		reInvoke: function(){
			this.setPause(false);
			this.reset();
		},
		destory: function(){
			var queens = this.queens;
			for(var i = 0, l = queens.length;i < l;i++){
				delete queens[i];
			}
		}
	};
	
	window.ExcutionQueen = window.ExcutionQueen || ExcutionQueen;
})();