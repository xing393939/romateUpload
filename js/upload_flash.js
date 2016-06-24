(function () {

    var doc = document;

    var filterElemByClass = UTIL.dom.filterElemByClass;
    var addDomEvent = UTIL.addDomEvent;
    var removeDomEvent = UTIL.removeDomEvent;
    var ensureAfterDomRenderer = UTIL.ensureAfterDomRenderer;
    var removeClass = UTIL.dom.removeClass;

    var UPLOAD_URL = 'http://ppyun.ugc.upload.pptv.com/';

    var sendPost = function (url, FormData, callback) {
        var XHR = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();

        XHR.open('post', url);
        XHR.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        XHR.send(FormData);

        XHR.onreadystatechange = function() {
            if (XHR.readyState == 4) {
                if (XHR.status == 200) {
                    callback(XHR.responseText);
                }
            }
        }
    };

    var uploadFile = function (file, index, ppFeature, fid) {
        //var blob = file.fileInput.files[file.fileIndex];
        var fd = 'fid=' + fid + '&ppfeature=' + ppFeature;
        sendPost(UPLOAD_URL + 'php/api_uploading.php', fd, function (str) {
            eval('arr=' + str);
            console.log('fid:' + fid, arr.data);
            //file.loaded.innerHTML = file.changeSize;
            //file.indicator.style.width = '50%';
            if (arr.data.fileSize == arr.data.finished || !arr.data.ranges) {
                file.excutionQueen.reset();
                return;
            }

            var params = '?fid=' + fid;
            params += '&start=' + arr.data.ranges.start;
            params += '&end=' + arr.data.ranges.end;
            params += '&range_md5=xxxx';
            params += '&ppfeature=' + ppFeature;

            document.getElementById('message').innerHTML += params + '<br />';

            upLoadURL(UPLOAD_URL + 'php/api_uploaded.php' + params, arr.data.ranges.start, arr.data.ranges.end);
            currentFile = file;
        })
    };

    var currentFile = null;
    var uploadFinished = function(str) {
        console.log('uploadFinished', str);
        document.getElementById('message').innerHTML += str + '<br />';

        currentFile.excutionQueen.reset();
    };

    var makeUploadQueens = function (file, fid, ppFeature) {
        if (!file) return;
        var up = this;

        var index = 0;
        var excutionQueen = new ExcutionQueen();
        excutionQueen.addLastCallBack(function () {
            var fd = 'fid=' + fid + '&ppfeature=' + ppFeature;
            sendPost(UPLOAD_URL + 'php/api_uploading.php', fd, function (str) {
                console.log(str);
            })
            file.isFinished = true;
        });
        file.excutionQueen = excutionQueen;
        // calculate the number of slices
        while (index < 6) {
            index++;
            excutionQueen.add(file, index, ppFeature, fid, uploadFile, file);
        }
    };

    var getPpFeature = function (ppFeature, size, channelName) {
        ppFeature = size + '_' + ppFeature;
        console.log(ppFeature, size, channelName);

        var up = this;
        var f = {
            name: channelName,
            size: size,
            changeSize: 0,
            isUpload: false,
            isFinished: false,
            index: 0,
            chuckIndex: -1,
            fileInput: null,
            fileIndex: 0,
            currentChuck: -1,
            totalChucks: 0,
            excutionQueen: null,
            viewNode: null
        };

        //请求队列完成之后的处理
        queenRetrieveData.addLastCallBack(function(){
        },this);
        var visitData = new Data({
            params: {
                PPKey: encodeURIComponent('hN3i3oup4BmWrUYH5QNyfbgTXxnvCMqGz++dZ381W8EPyU9WN2kKiKEZxLii/F9CYyq7+0Q2rhJoxK2NYenS+scGcOfGhK3h'),
                userName: 'wengjiawei9',
                epg_category_id: 923,
                channelname: channelName,
                channeltype: 1,
                ppfeature: ppFeature,
                series_id: 182,
                //series_id: 265,
                size: size,
                summary: 'test_summary'
            },
            requestProxy: queenRetrieveData,
            requestOption: {
                apiUrl: 'http://api.pptvyun.ppqa.com/pgc/api/video/list/save-channel',
                param: '',
                callback: function (data) {
                    document.getElementById('message').innerHTML += data.data.fid + '<br />';

                    makeUploadQueens.call(up, f, data.data.fid, ppFeature);
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
    };

    var initComps = function () {

    };

    var initEvent = function () {
        this.uploadLists = filterElemByClass(this.body, 'ul', 'uploadList')[0];

        var up = this;
        var event = new EventService({
            parent: up.wrapper,
            actionMap: {}

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

        this.isAutoUpload = config.isAutoUpload || false;

    };

    Upload.prototype = {

        init: function () {
            initComps.call(this);
            ensureAfterDomRenderer(initEvent, this);
        }

    };

    window.getPpFeature = getPpFeature;
    window.uploadFinished = uploadFinished;

    window.PDIKit = window.PDIKit || {};
    window.PDIKit.Upload = window.PDIKit.Upload || Upload;
}());
