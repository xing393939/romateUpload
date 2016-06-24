(function () {

    var doc = document;

    var filterElemByClass = UTIL.dom.filterElemByClass;
    var addDomEvent = UTIL.addDomEvent;
    var removeDomEvent = UTIL.removeDomEvent;
    var ensureAfterDomRenderer = UTIL.ensureAfterDomRenderer;
    var removeClass = UTIL.dom.removeClass;

    var domainURL = 'http://ppyun.ugc.upload.pptv.com/';
    var blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice;

    var changeBytes = function (m) {
        var r = m;
        var b = ['', 'K', 'M', 'G', 'T', 'P'];
        var flag = 0;
        while (r >= 1024) {
            r = r / 1024;
            flag++;
        }
        return (r.toFixed(2) + b[flag] + 'B');
    };

    var str2ab_blobreader = function (str, callback) {
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
        f.onload = function (e) {
            callback(e.target.result)
        }
        f.readAsArrayBuffer(blob);
    };

    var sendPost = function (url, FormData, callback) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    callback(xhr.responseText);
                }
            }
        }
        xhr.upload.onprogress = function (evt) {
        };
        xhr.open("post", url);
        xhr.send(FormData);
    };

    var sendFile = function (url, blob, start, length, callback) {
        var chunk = blobSlice.call(blob, start, start + length);
        ao = new FormData;
        ao.append("fileToUpload", chunk);
        an = new XMLHttpRequest;
        an.upload.addEventListener("progress", function(){}, !1);
        an.addEventListener("load", function(){callback('{}')}, !1);
        an.addEventListener("error", function(){}, !1);
        an.addEventListener("abort", function(){}, !1);
        an.open("POST", url);
        an.setRequestHeader("Content-Range", 'bytes ' + start + '-' + (start + length - 1) + '/' + blob.size);
        an.send(ao);
    };

    var sendFile2 = function (url, blob, start, length, callback) {
        var chunk = blobSlice.call(blob, start, start + length);
        xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    callback(xhr.responseText);
                }
            }
        };
        xhr.addEventListener("load", function (evt) {
        }, false);

        xhr.upload.addEventListener('progress', function (evt) {
        }, false);

        xhr.open('post', url, true);
        xhr.setRequestHeader('Content-type', 'application/octet-stream');
        xhr.setRequestHeader('Content-Disposition', 'attachment; name="fileToUpload"; filename="filename"');
        xhr.setRequestHeader('Content-Range', 'bytes ' + start + '-' + (start + length - 1) + '/' + blob.size);
        if (File.prototype.webkitSlice) {
            // android default browser in version 4.0.4 has webkitSlice instead of slice()
            var buffer = str2ab_blobreader(chunk, function (buf) {
                xhr.send(buf);
            });
        } else {
            xhr.send(chunk);
        }
    }

    var _createUploadInput = function (up) {
        var fileSelector = document.createElement('input');
        fileSelector.setAttribute('type', 'file');
        if (up.multiple) fileSelector.setAttribute('multiple', 'multiple');
        return fileSelector;
    };

    var onChooseFile = function () {
        var up = this;
        var fileChoose = _createUploadInput(up);
        this.fileChoose = fileChoose;
        addDomEvent(fileChoose, 'change', function () {
            onChangeFiles.call(up);
        });
        if (fileChoose) fileChoose.click();
    };

    var isInArray = function (name, prop, arr) {
        for (var i = 0, l = arr.length; i < l; i++) {
            if (arr[i] && name == arr[i][prop]) return i;
        }
        return -1;
    };

    var onChangeFiles = function () {
        var fileChoose = this.fileChoose;
        var up = this;
        if (fileChoose) {
            var files = fileChoose.files;
            for (var i = 0, l = files.length; i < l; i++) {
                var file = files[i];
                var name = file.name;
                var size = file.size;
                var index = isInArray(name, 'name', this.files);
                var flag = this.files.length;
                if (index === -1) {
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
                    if (this.isAutoUpload) _autoUpload.call(this, flag);
                }
            }
        }
    };

    var _autoUpload = function (index) {
        var files = this.files;
        var i = 0;
        var flag = true;
        while (i < index) {
            if (files[i] && !files[i].isFinished) {
                flag = false;
                break;
            }
            i++;
        }
        if (flag) onStartUpload.call(this, index, files[index].viewNode);
    };

    var onStartUpload = function (index, node) {
        var f = this.files[index];
        var up = this;
        if (!f.progress) f.progress = filterElemByClass(node, 'span', 'progress')[0];
        if (!f.indicator) f.indicator = filterElemByClass(node, 'span', 'indicator')[0];
        if (!f.loaded) f.loaded = filterElemByClass(node, 'span', 'loaded')[0];
        if (!f.finished) f.finished = filterElemByClass(node, 'span', 'finished')[0];
        if (!f.isFinished) {
            if (!f.excutionQueen) {
                //初始化上传
                //var url = 'http://ugc.upload.pptv.com/html5upload?format=json&filename=2.mp4&from=clt&token=E9r7QktWF7xuq6dAVm5sNejcicaEmdSCNU7shGjp0A0XwFf1M8fkXsSfNWn00vnFrjK6mW9%2B7BbG539Q3Llp1GTbab0Hda5RdryZSXwPstWjpwAKefl07cKm4yQlizp678MJg03B2Za4PMvc2dCEqwF5aYU7kBN9w%2FPd1W1QjqY%3D&cp=1717wanunion&username=xing393939&uploadid=c84cb936d28840c79e4a839c1d5d50d4&size=24390815&type=video%2Fmp4&lastmodifiedtime=1464939706000';
                var url = domainURL + 'ppyun/upload?fid=1&ppfeature=1&start=1&end=1';
                var blob = f.fileInput.files[f.fileIndex];
                sendFile2(url, blob, 0, 10000000, function (str) {
                    sendFile2(url, blob, 10000000, 10000000, function (str) {
                        sendFile2(url, blob, 20000000, blob.size - 20000000, function (str) {
                            console.log('ok');
                        })
                    })
                });
            } else {
                f.excutionQueen.reInvoke();
                f.isUpload = true;
            }
        }
    };

    var onPauseUpload = function (index) {
        var f = this.files[index];
        if (!f.isFinished) {
            if (f.excutionQueen) {
                f.excutionQueen.setPause(true);
            }
        }
    };

    var onStopUpload = function (index, node) {
        var f = this.files[index];
        if (f.excutionQueen) {
            f.excutionQueen.setPause(true);
            f.excutionQueen.destory();
        }
        if (f.fileInput) {
            this.files[index] = null;
            removeDomEvent(f.fileInput, 'change', function () {
            });
            f.fileInput = null;
        }
        this.uploadLists.removeChild(node);
    };

    var initComps = function () {
        var tpl = this.tpl || {};

        if (tpl.header) {
            this.header.appendChild(tpl.header);
            this.wrapper.appendChild(this.header);
        }
        if (tpl.body && tpl.body.tpl) {
            var btpl = tpl.body.tpl;
            var bstr = '';
            if (btpl.file) {
                bstr = btpl.file.replace(/{name}/, this.domName).replace(/{id}/, this.domId);
            }

            if (btpl.body) {
                bstr += btpl.body;
            }
            this.body.innerHTML = bstr;
            this.wrapper.appendChild(this.body);
        }
        if (tpl.footer) {
            this.footer.appendChild(btpl.footer);
            this.wrapper.appendChild(this.footer);
        }

        this.parentNode.appendChild(this.wrapper);
    };

    var initEvent = function () {
        this.uploadLists = filterElemByClass(this.body, 'ul', 'uploadList')[0];

        var up = this;
        var event = new EventService({
            parent: up.wrapper,
            actionMap: {
                'a.className.choose-icon': function (target, e) {
                    onChooseFile.call(up);
                },
                'a.className.start': function (target, e) {
                    var pd = target.parentNode;
                    var index = pd.getAttribute('data-fileIndex');
                    onStartUpload.call(up, index, pd);
                },
                'a.className.pause': function (target, e) {
                    var pd = target.parentNode;
                    var index = pd.getAttribute('data-fileIndex');
                    onPauseUpload.call(up, index);
                },
                'a.className.stop': function (target, e) {
                    var pd = target.parentNode;
                    var index = pd.getAttribute('data-fileIndex');
                    onStopUpload.call(up, index, pd);
                }
            }

        });
    };

    var Upload = function (config) {

        this.files = [];
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

        init: function () {
            initComps.call(this);
            ensureAfterDomRenderer(initEvent, this);
        }

    };

    window.PDIKit = window.PDIKit || {};
    window.PDIKit.Upload = window.PDIKit.Upload || Upload;
}());
