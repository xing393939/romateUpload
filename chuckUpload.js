(function(){}(
	var BYTES_PER_CHUNK = 1024*1024;
	var doc = document;
	
	var uploadFile = function(file, index, start, end){
		    var xhr;
			var chunk;

			xhr = new XMLHttpRequest();

			xhr.onreadystatechange = function() {
				if(xhr.readyState == 4) {
					if(xhr.responseText) {
						--slices;
						// if we have finished all slices
						if(slices == 0) {
							mergeFile(file);
						}
					}
				}
			};

			if (blob.webkitSlice) {
				chunk = file.webkitSlice(start, end);
			} else if (blob.mozSlice) {
				chunk = file.mozSlice(start, end);
			} else {
				chunk = file.slice(start, end); 
			}

			xhr.addEventListener("load",  function (evt) {
				queenRetrieveData.reset();
			}, false);

			xhr.upload.addEventListener("progress", function (evt) {
				var percentageDiv = document.getElementById("percent");  
				var progressBar = document.getElementById("progressBar");

				if (evt.lengthComputable) {
					progressBar.max = slicesTotal;  
					progressBar.value = index; 			
					percentageDiv.innerHTML = Math.round(index/slicesTotal * 100) + "%";  
				} 
			}, false);

			xhr.open("post", "upload.php", true);
			xhr.setRequestHeader("X-File-Name", file.name);             // custom header with filename and full size
			xhr.setRequestHeader("X-File-Size", file.size);
			xhr.setRequestHeader("X-Index", index);                     // part identifier
			
			if (blob.webkitSlice) {                                     // android default browser in version 4.0.4 has webkitSlice instead of slice()
				var buffer = str2ab_blobreader(chunk, function(buf) {   // we cannot send a blob, because body payload will be empty
					xhr.send(buf);                                      // thats why we send an ArrayBuffer
				});	
			} else {
				xhr.send(chunk);                                        // but if we support slice() everything should be ok
			}
	};
	
	var makeUploadQueens = function(file){
		if(!file) return;
		var start = 0;
		var end;
		var index = 0;
		// calculate the number of slices
		this.totalChucks = Math.ceil(file.size / BYTES_PER_CHUNK);
		this.currentChuck = index;

		while(start < file.size){
			end = start + BYTES_PER_CHUNK;
			if(end > file.size){
				end = file.size;
			}
			if(this.isAutoUpload){
				queenRetrieveData.nextRequest(file, index, start, end, uploadFile, this);
			}else{
				queenRetrieveData.add(file, index, start, end, uploadFile, this);
			}
			start = end;
			index++;
		}
	};
	
	var initComps = function(){
		
	};
	
	var initEvent = function(){
		
	};
	
	var ChuckUpload = function(config){
		this.totalChucks = 0;
		this.currentChuck = -1;
		this.files = [];
		this.uploadQueens = [];
		this.isUploadQueensDone = true;
		config = config || {};
		
		this.wrapper = config.wrapper || doc.createElement('div');
		this.header = config.header || doc.createElement('div');
		this.body = config.body || doc.createElement('div');
		this.footer = config.footer || doc.createElement('div');
		
		this.isAutoUpload = config.isAutoUpload || false;
		
		
	};
	
	ChuckUpload.prototype = {
		
		init: function(){
			
		}
		
	};
	
	//&rtrif;   start icon
	//&check;   finished icon
	//&block;   stop icon
	//&orarr;   restarted icon
	//&boxV;    pause icon 
	//&xoplus;  add icon
	
	
	
));