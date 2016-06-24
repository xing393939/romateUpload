(function(){
	var uploadtpl = {
		body: {
			tpl: {
				'file': '<div class="file-choose">' +
							'<a class="choose-icon" href="javascript: void(0);">&xoplus;</a>' +
						'</div>',
				'body': '<div class="uploadList-wrapper">' +
							'<ul class="uploadList"></ul>' +
						'</div>',
				'item': '<span class="title" href="javascript: void(0);">{title}</span>' + 
						'<a class="start" href="javascript: void(0);" data-action="a.className.start">&rtrif;</a>' + 
						'<a class="pause" href="javascript: void(0);" data-action="a.className.pause">&boxV;</a>' +
						'<a class="stop" href="javascript: void(0);" data-action="a.className.stop">&block;</a>' +
						'<span class="progress">' + 
							'<span class="indicator"></span>' + 
						'</span>' + 
						'<span class="loaded">{loaded}</span>' +
						'<span class="total">/{size}</span>' +
						'<span class="finished hide">&check; 视频上传成功，正在审核中</span>'
			}
		},
		emptyTpl: '<div style="text-align: center;">无数据</div>'
	};
	window.UPLOADTPL = window.UPLOADTPL || uploadtpl;
	
})();