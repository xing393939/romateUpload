(function(){
	
	var doc = document;
	var BYTES_PER_CHUNK = 1024*1024*2;
	
	var filterElemByClass = UTIL.dom.filterElemByClass;
	var addDomEvent = UTIL.addDomEvent;
	var removeDomEvent = UTIL.removeDomEvent;
	var ensureAfterDomRenderer = UTIL.ensureAfterDomRenderer;
	var removeClass = UTIL.dom.removeClass;
	
	var domainURL = 'http://ppyun.ugc.upload.pptv.com/';
	
	var changeBytes = function(m){
		var r = m;
		var b = ['', 'K', 'M', 'G', 'T', 'P'];
		var flag = 0;
		while(r >= 1024){
			r = r / 1024; 
			flag++;
		}
		return (r.toFixed(2) + b[flag] + 'B');
	};
	
	var str2ab_blobreader = function(str, callback) {
	    var blob;
	    BlobBuilder = window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder;
	    if (typeof(BlobBuilder) !== 'undefined') {
	      var bb = new BlobBuilder();
	      bb.append(str);
	      blob = bb.getBlob();
	    } else {
	      blob = new Blob([str]);
	    }
	    var f = new FileReader();
	    f.onload = function(e) {
	        callback(e.target.result)
	    }
	    f.readAsArrayBuffer(blob);
	};
	
	var mergeFile = function(name, size) {
	    var xhr;
	    var fd;

	    xhr = new XMLHttpRequest();

	    fd = new FormData();
	    fd.append("name", encodeURI(name));
	    fd.append("index", size);

	    xhr.open("POST", domainURL + "/php/merge.php", true);
	    xhr.send(fd);
	}
	
	var uploadFile = function(file, index, start, end){
		    var xhr;
			var chunk;
			var self = this;
			var blob = file.fileInput.files[file.fileIndex];
			var indicator = file.indicator;
			var loaded = file.loaded;
			var size = file.size;
            
			console.log('index -->' + index + ', start--> ' + start);
			
			xhr = new XMLHttpRequest();

			xhr.onreadystatechange = function(){
				/*
					to do
				*/
			};
			if (blob.webkitSlice) {
				chunk = blob.webkitSlice(start, end);
			} else if (blob.mozSlice) {
				chunk = blob.mozSlice(start, end);
			} else {
				chunk = blob.slice(start, end); 
			}

			xhr.addEventListener("load",  function (evt) {
				self.chuckIndex = index;
				self.excutionQueen.reset();
			}, false);

			xhr.upload.addEventListener('progress', function (evt) {
				var per = index/file.totalChucks;
				indicator.style.width = Math.round(per* 100) + '%';
				loaded.innerHTML = changeBytes(size * Math.round(per* 100) /100);
			}, false);

			xhr.open('post', domainURL + 'php/upload.php', true);
			xhr.setRequestHeader("X-File", encodeURI(file.name));  // custom header with filename and full size
			xhr.setRequestHeader('X-File-Size', file.size);
			xhr.setRequestHeader('X-Index', index);
			xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');
			// part identifier
			
			if (blob.webkitSlice) {                                     // android default browser in version 4.0.4 has webkitSlice instead of slice()
				var buffer = str2ab_blobreader(chunk, function(buf) {   // we cannot send a blob, because body payload will be empty
					xhr.send(buf);                                      // thats why we send an ArrayBuffer
				});	
			} else {
				xhr.send(chunk);                                        // but if we support slice() everything should be ok
			}
	};
	
	var _callNextUploadQueens = function(index){
		var files = this.files;
		var l = files.length;
		while(index < l){
			if(files[index] && !files[index].isUpload){
				break;
			}
			index++;
		}
		
		if(l > 1 && index < l)
			_autoUpload.call(this, index);
	};
	
	var makeUploadQueens = function(file){
		if(!file) return;
		var up = this;
		var start = file.currentChuck > -1 ? file.currentChuck : 0;
		var end;
		var index = 0;
		var excutionQueen = new ExcutionQueen();
		excutionQueen.addLastCallBack(function(){
			file.isFinished = true;
			file.loaded.innerHTML = file.changeSize;
			file.indicator.style.width = '100%';
			removeClass(file.finished, 'hide');
			mergeFile(file.name, file.totalChucks);
			_callNextUploadQueens.call(up, file.index);
		});
		
		file.excutionQueen = excutionQueen;
		file.totalChucks = Math.ceil(file.size / BYTES_PER_CHUNK);
		
		// calculate the number of slices
		while(start < file.size){
			end = start + BYTES_PER_CHUNK;
			if(end > file.size){
				end = file.size;
			}
			excutionQueen.add(file, index, start, end, uploadFile, file);
			start = end;
			index++;
		}
	};
	
	var _createUploadInput = function(up){
		var fileSelector = document.createElement('input');
		fileSelector.setAttribute('type', 'file');
		if(up.multiple) fileSelector.setAttribute('multiple', 'multiple');
		return fileSelector;
	};
	
	var onChooseFile = function(){
		var up = this;
		var fileChoose = _createUploadInput(up);
		this.fileChoose = fileChoose;
		addDomEvent(fileChoose, 'change', function(){
			onChangeFiles.call(up);
		});
		if(fileChoose) fileChoose.click();
	};
	
	var isInArray = function(name,prop, arr){
		for(var i = 0, l = arr.length;i < l;i++){
			if(arr[i] && name == arr[i][prop]) return i;
		}
		return -1;
	};
	
	var onChangeFiles = function(){
		var fileChoose = this.fileChoose;
		var up = this;
		if(fileChoose){
			var files = fileChoose.files;
			for(var i = 0, l = files.length;i < l;i++){
				var file = files[i];
				var name = file.name;
				var size = file.size;
				var index = isInArray(name, 'name', this.files);
				var flag = this.files.length;
				if(index === -1){
					var tpl = this.tpl;
					var str = tpl.body.tpl.item;
					str = str.replace(/{title}/, name);
					str = str.replace(/{loaded}/, '0.00');
					str = str.replace(/{size}/, changeBytes(size));
					var li = doc.createElement('li');
					li.setAttribute('data-fileIndex', flag);
					li.className = 'uploadList-item';
					li.innerHTML = str;
					
					this.files.push({
						name: name,
						size: size,
						changeSize: changeBytes(size),
						isUpload: false,
						isFinished: false,
						index: flag,
						chuckIndex: -1,
						fileInput: up.fileChoose,
						fileIndex: i,
						currentChuck: -1,
						totalChucks: 0,
						excutionQueen: null,
						viewNode: li
					});
					this.uploadLists.appendChild(li);
					if(this.isAutoUpload) _autoUpload.call(this, flag);
				}
			}
		}
	}; 
	
	var _autoUpload = function(index){
		var files = this.files;
		var i = 0;
		var flag = true;
		while(i < index){
			if(files[i] && !files[i].isFinished){
				flag = false;
				break;
			}
			i++;
		}
		if(flag) onStartUpload.call(this, index, files[index].viewNode);
	};
	
	var onStartUpload = function(index, node){
		var f = this.files[index];
		if(!f.progress) f.progress = filterElemByClass(node, 'span', 'progress')[0];
		if(!f.indicator) f.indicator = filterElemByClass(node, 'span', 'indicator')[0];
		if(!f.loaded) f.loaded = filterElemByClass(node, 'span', 'loaded')[0]; 
		if(!f.finished) f.finished = filterElemByClass(node, 'span', 'finished')[0]; 
		if(!f.isFinished){
			if(!f.excutionQueen){
				makeUploadQueens.call(this, f);
			}
			f.excutionQueen.reInvoke();
			f.isUpload = true;
		}
	};
	
	var onPauseUpload = function(index){
		var f = this.files[index];
		if(!f.isFinished){
			if(f.excutionQueen){
				f.excutionQueen.setPause(true);
			}
		}
	};
	
	var onStopUpload = function(index, node){
		var f = this.files[index];
		if(f.excutionQueen){
			f.excutionQueen.setPause(true);
			f.excutionQueen.destory();
		}
		if(f.fileInput){
			this.files[index] = null;
			removeDomEvent(f.fileInput, 'change', function(){});
			f.fileInput = null;
		}
		this.uploadLists.removeChild(node);
	};
	
	var initComps = function(){
		var tpl = this.tpl || {};
		
		if(tpl.header){
			this.header.appendChild(tpl.header);
			this.wrapper.appendChild(this.header);
		}
		if(tpl.body && tpl.body.tpl){
			var btpl = tpl.body.tpl;
			var bstr = '';
			if(btpl.file){
				bstr = btpl.file.replace(/{name}/, this.domName).replace(/{id}/, this.domId);
			}
			
			if(btpl.body){
				bstr += btpl.body;
			}
			this.body.innerHTML = bstr;
			this.wrapper.appendChild(this.body);
		}
		if(tpl.footer){
			this.footer.appendChild(btpl.footer);
			this.wrapper.appendChild(this.footer);
		}
		
		this.parentNode.appendChild(this.wrapper);
	};
	
	var initEvent = function(){
		this.uploadLists = filterElemByClass(this.body, 'ul', 'uploadList')[0];
		
		var up = this;
		var event = new EventService({
			parent: up.wrapper,
			actionMap: {
				'a.className.choose-icon': function(target, e){
					onChooseFile.call(up);
				},
				'a.className.start': function(target, e){
					var pd = target.parentNode;
					var index = pd.getAttribute('data-fileIndex');
					onStartUpload.call(up, index, pd);
				},
				'a.className.pause': function(target, e){
					var pd = target.parentNode;
					var index = pd.getAttribute('data-fileIndex');
					onPauseUpload.call(up, index);
				},
				'a.className.stop': function(target, e){
					var pd = target.parentNode;
					var index = pd.getAttribute('data-fileIndex');
					onStopUpload.call(up, index, pd);
				}
			}
			
		});
	};
	
	var Upload = function(config){
		this.totalChucks = 0;
		this.currentChuck = -1;
		this.uploadFileIndex = -1;
		this.files = [];
		this.uploadQueens = [];
		this.isUploadQueensDone = true;
		
		this.fileChoose = null;
		this.uploadLists = null;
		
		config = config || {};
		
		this.parentNode = config.parentNode || doc.body;
		this.wrapper = config.wrapper || doc.createElement('div');
		this.header = config.header || doc.createElement('div');
		this.body = config.body || doc.createElement('div');
		this.footer = config.footer || doc.createElement('div');
		this.domName = config.domName || 'uploadFile';
		this.domId = config.domId || 'uploadFile';
		this.multiple = config.multiple || false;
		
		this.tpl = config.tpl || UPLOADTPL;
		
		this.isAutoUpload = config.isAutoUpload || false;
		
		
	};
	
	Upload.prototype = {
		
		init: function(){
			initComps.call(this);
			ensureAfterDomRenderer(initEvent, this);
		}
		
	};
	
	window.PDIKit = window.PDIKit ||{};
    window.PDIKit.Upload = window.PDIKit.Upload || Upload;
}());
