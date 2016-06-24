(function () {

    var doc = document;

    var filterElemByClass = UTIL.dom.filterElemByClass;
    var addDomEvent = UTIL.addDomEvent;
    var removeDomEvent = UTIL.removeDomEvent;
    var ensureAfterDomRenderer = UTIL.ensureAfterDomRenderer;
    var removeClass = UTIL.dom.removeClass;

    var domainURL = 'http://ppyun.ugc.upload.pptv.com/';
    var blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice;
    var spark = new SparkMD5.ArrayBuffer();
    var fileReader = new FileReader();

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

    var sendFile = function (url, chunk, length, callback) {
        ao = new FormData;
        ao.append("fileToUpload", chunk);
        an = new XMLHttpRequest;
        an.upload.addEventListener("progress", function(){}, !1);
        an.addEventListener("load", function(){callback('{}')}, !1);
        an.addEventListener("error", function(){}, !1);
        an.addEventListener("abort", function(){}, !1);
        an.open("POST", url);
        an.setRequestHeader("Content-Range", 'bytes 0-' + (length - 1) + '/' + length);
        an.send(ao);
    };

    var sendFile2 = function (url, chunk, length, callback) {
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
        xhr.setRequestHeader('Content-Disposition', 'attachment; name="file1"; filename="filename"');
        xhr.setRequestHeader('X-Content-Range', 'bytes 0-' + (length - 1) + '/' + length);
        if (File.prototype.webkitSlice) {
            // android default browser in version 4.0.4 has webkitSlice instead of slice()
            var buffer = str2ab_blobreader(chunk, function (buf) {
                xhr.send(buf);
            });
        } else {
            xhr.send(chunk);
        }
    }

    var uploadFile = function (file, index, ppFeature, fid) {
        var blob = file.fileInput.files[file.fileIndex];
        var fd = new FormData();
        fd.append("fid", fid)
        fd.append("ppfeature", ppFeature)
        sendPost(domainURL + 'php/api_uploading.php', fd, function (str) {
            eval('arr=' + str);
            console.log('fid:' + fid, arr.data);
            file.loaded.innerHTML = file.changeSize;
            file.indicator.style.width = '50%';
            if (arr.data.fileSize == arr.data.finished || !arr.data.ranges) {
                file.excutionQueen.reset();
                return;
            }
            chunk = blobSlice.call(blob, arr.data.ranges.start, arr.data.ranges.end);
            fileReader.onload = function (e) {
                spark.append(e.target.result);
                var params = '?fid=' + fid;
                params += '&start=' + arr.data.ranges.start;
                params += '&end=' + arr.data.ranges.end;
                params += '&range_md5=' + spark.end();
                params += '&ppfeature=' + ppFeature;
                var length = arr.data.ranges.end - arr.data.ranges.start;
                sendFile(domainURL + 'ppyun/upload' + params, chunk, length, function (str) {
                    file.excutionQueen.reset();
                });
            };
            fileReader.readAsArrayBuffer(chunk);
        })
    };

    var _callNextUploadQueens = function (index) {
        var files = this.files;
        var l = files.length;
        while (index < l) {
            if (files[index] && !files[index].isUpload) {
                break;
            }
            index++;
        }

        if (l > 1 && index < l)
            _autoUpload.call(this, index);
    };

    var makeUploadQueens = function (file, fid) {
        if (!file) return;
        var up = this;

        var blob = file.fileInput.files[file.fileIndex];
        var index = 0;
        var excutionQueen = new ExcutionQueen();
        excutionQueen.addLastCallBack(function () {
            var fd = new FormData();
            fd.append("fid", fid)
            fd.append("ppfeature", ppFeature)
            sendPost(domainURL + 'php/api_uploading.php', fd, function (str) {
                console.log(str);
            })
            file.isFinished = true;
            file.loaded.innerHTML = file.changeSize;
            file.indicator.style.width = '100%';
            removeClass(file.finished, 'hide');
            _callNextUploadQueens.call(up, file.index);
        });
        file.excutionQueen = excutionQueen;
        // calculate the number of slices
        while (index < blob.size / 4194303) {
            index++;
            excutionQueen.add(file, index, ppFeature, fid, uploadFile, file);
        }
    };

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
                var blob = f.fileInput.files[f.fileIndex];
                var fd = new FormData();
                fd.append("size", blob.size);
                if (blob.size < 65535) {
                    fd.append("files[]", file);
                } else {
                    start = 0
                    fd.append("files[]", blobSlice.call(blob, start, start + 12288));
                    start = parseInt(1 * blob.size / 5);
                    fd.append("files[]", blobSlice.call(blob, start, start + 12288));
                    start = parseInt(2 * blob.size / 5);
                    fd.append("files[]", blobSlice.call(blob, start, start + 12288));
                    start = parseInt(3 * blob.size / 5);
                    fd.append("files[]", blobSlice.call(blob, start, start + 12288));
                    start = blob.size - 12288;
                    fd.append("files[]", blobSlice.call(blob, start, start + 12288));
                }
                sendPost(domainURL + 'php/api_feature.php', fd, function (str) {
                    eval('ppFeatureArr=' + str);
                    ppFeature = ppFeatureArr.data.ppFeature;

                    //请求队列完成之后的处理
                    queenRetrieveData.addLastCallBack(function(){
                    },this);
                    var visitData = new Data({
                        params: {
                            PPKey: encodeURIComponent('hN3i3oup4BmWrUYH5QNyfbgTXxnvCMqGz++dZ381W8EPyU9WN2kKiKEZxLii/F9CYyq7+0Q2rhJoxK2NYenS+scGcOfGhK3h'),
                            userName: 'wengjiawei9',
                            categoryid: 930,
                            channelname: 'test_upload13',
                            channeltype: 1,
                            ppfeature: ppFeature,
                            series_id: 182,
                            //series_id: 265,
                            size: blob.size,
                            summary: 'test_summary'
                        },
                        requestProxy: queenRetrieveData,
                        requestOption: {
                            apiUrl: 'http://api.pptvyun.ppqa.com/pgc/api/video/list/save-channel',
                            param: '',
                            callback: function (data) {
                                makeUploadQueens.call(up, f, data.data.fid);
                                f.excutionQueen.reInvoke();
                                f.isUpload = true;
                                visitData.reset();
                            },
                            errorCallback: function () {
                                visitData.reset();
                            }
                        }
                    });
                    visitData.nextRequest();
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

        init: function () {
            initComps.call(this);
            ensureAfterDomRenderer(initEvent, this);
        }

    };

    window.PDIKit = window.PDIKit || {};
    window.PDIKit.Upload = window.PDIKit.Upload || Upload;
}());
