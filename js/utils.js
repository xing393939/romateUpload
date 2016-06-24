/**
 * <h2>工具类</h2>
 * @class UTIL.UTIL
 * @static
 */
var UTIL = UTIL || {
    /**
     * 给dom节点添加事件
     * @method
     * @param {Object}target
     *      需要添加事件的dom节点
     * @param {String}type
     *      需要添加的事件类型
     * @param {Function}callback
     *      事件和处理方法
     * @param {Function}useCapture
     *      当为false时为冒泡获取(由里向外)，true为capture方式(由外向里)
     */
    addDomEvent: function(target, type, callback, useCapture) {
        var eventName = type;
        if (target.addEventListener) {
            target.addEventListener(eventName, callback, useCapture);
        } else if (target.attachEvent) {
            eventName = 'on' + eventName;
            target.attachEvent(eventName, function() {
                callback.call(target, arguments[0]);
            });
        }
    },
    /**
     * 移除dom节点事件
     * @method
     * @param {Object}target
     *      需要移除事件的dom节点
     * @param {String}type
     *      需要移除的事件类型
     * @param {Function}callback
     *      需要移除的操作方法
     */
    removeDomEvent: function(target,type,callback) {
        if (target.removeEventListener) {
            target.removeEventListener(type,callback)
        } else if (target.detachEvent) {
            target.detachEvent("on"+type, callback);
        }
    },
    /**
     * 把对象参数编码成url参数
     * @method
     * @param {Object}config
	 * @param {Boolean}isEncode 
     * @returns {string}
     */
    paramsToURIComps: function(config, isEncode) {
        var result = [];
		var str = '';
        for (var prop in config) {
			str = config[prop];
			isEncode && (str = encodeURIComponent(str)); //需要编码将参数isEncode配置成true
            result.push(prop + '=' + str);
        }
        return ('?' + result.join('&'));
    },
    paramsToURIEncoding: function(params){
        for (var prop in params) {
            params[prop] = encodeURIComponent(params[prop]);
        }
    },
    /**
     * 对象的浅拷贝
     * @method
     * @param {Object}ori
     * @returns {Object}des
     */
    copySobject: function(ori) {
        var result = {};
        for (var prop in config) {
            result[prop] = config[prop];
        }
        return result;
    },
    /**
     * 修复事件的兼容性（主要针对IE）
     * @method
     * @param e
     */
    fixedEvent: function(e) {
        if (!e) e = window.event;
        if (!e.target) e.target = e.srcElement;
        if (!e.stopPropagation) {
            e.stopPropagation = function() {
                e.cancelBubble = true;
            };
        }

        if (!e.preventDefault) {
            e.preventDefault = function() {
                e.returnValue = false;
                return false;
            };
        }
    },
	/**
     * 验证IE的版本
     * @method
     *  @returns {String}result
     */
    isIEVersion: (function() {
        var jscriptMap = {
			"5.5": "5.5",
			"5.6": "6",
			"5.7": "7",
			"5.8": "8",
			"9": "9",
			"10": "10",
            "11": "11"
		};
		var jscriptVersion = new Function("/*@cc_on return @_jscript_version; @*/")();
		if(!jscriptVersion) return -1;
		return jscriptMap[jscriptVersion];
    })(),
    /**
     * 验证是否是IE浏览器
     * @method
     *  @returns {Boolean}result
     */
    isIE: (function() {
        var ua = navigator.userAgent.toLowerCase();
        return (ua.match(/rv:([\d.]+)\) like gecko/) || ua.match(/msie ([\d.]+)/))?true:false;
    })(),
	/**
     * 验证是否是IE8浏览器
     * @method
     *  @returns {Boolean}result
     */
    isIE9: (function() {
        return navigator && navigator.userAgent.match(/msie/i) && navigator.appVersion.match(/MSIE 9.0/i);
    })(),
	/**
     * 验证是否是IE8浏览器
     * @method
     *  @returns {Boolean}result
     */
    isIE8: (function() {
        return navigator && navigator.userAgent.match(/msie/i) && navigator.appVersion.match(/MSIE 8.0/i);
    })(),
    /**
     * 验证是否是IE7浏览器
     * @method
     *  @returns {Boolean}result
     */
    isIE7: (function() {
        return navigator && navigator.userAgent.match(/msie/i) && navigator.appVersion.match(/MSIE 7.0/i);
    })(),
    /**
     * 验证是否是IE6浏览器
     * @method
     *  @returns {Boolean}result
     */
    isIE6: (function() {
        return navigator && navigator.userAgent.match(/msie/i) && navigator.appVersion.match(/MSIE 6.0/i);
    })(),
    /**
     * 验证是否是IE11浏览器
     * @method
     *  @returns {Boolean}result
     */
    isIE11: (function() {
        return Object.hasOwnProperty.call(window, "ActiveXObject") && !window.ActiveXObject;
    })(),
    /**
     * 验证是否是Firefox浏览器
     * @method
     *  @returns {Boolean}result
     */
    isFirefox: (function() {
        return navigator && navigator.userAgent.match(/Firefox/i);
    })(),
    /**
     * 空函数
     * @method
     */
    emptyFunc: function() {},
    /**
     * 等待dom节点渲染完毕后再执行
     * @method
     * @param {Function}cb
     *      回调方法
     * @param {Object}cxt
     *      回调上下文
     * @param {Number}delay
     *      延迟执行时间(毫秒)
     */
    ensureAfterDomRenderer: function(cb, cxt, delay) { //make sure that all the doms' are rendered then invoke 'cb'
        if (cb) {
            setTimeout(function() {
                cb.call(cxt, cxt);
            }, delay || 0);
        }
    },
    /**
     * 对象的深拷贝
     * @method
     * @param {Object}orign
     *     原始对象
     * @returns {Object}dest
     *      对象的副本
     */
    cloneObject: function(orign) {
        var obj = {};
        for (var p in orign) {
            if (typeof orign[p] === 'object') {
                obj[p] = UTIL.cloneObject(orign[p]);
            } else {
                obj[p] = orign[p];
            }
        }
        return obj;
    },
    /**
     * 对象合并
     * @method
     * @param {Object}orig
     *      原始对象
     * @param {Object}desi
     *      目标对象
     */
    copyObject: function(orig, desi) {
        for (var p in orig) {
            desi[p] = orig[p];
        }
    },
    /**
     * 对象合并(深度)
     * @method
     * @param {Object}orig
     *      原始对象
     * @param {Object}desi
     *      目标对象
     */
    copyDeepObject: function(orig, desi) {
        for (var p in orig) {
            if(typeof orig[p] == 'object'){
               UTIL.copyDeepObject(orig[p], desi[p]); 
            }
            else{
               desi[p] = orig[p]; 
            }
        }
    },
    /**
     * 随机生成UUID字符串
     * @method
     * @returns {String}uuid
     */
    uuid: (function() {
        var cnt = 0;
        return function() {
            ++cnt;
            return (+(new Date)) + cnt;
        };
    })(),
    /**
     * 对数组进行过滤
     * @method
     * @param {Array}array
     *  原数组
     * @param {Function}filterFunc
     *  过滤方法
     * @returns {Array}arr
     *  返回的新数组(下标)
     */
    filter: function(array, filterFunc) {
        var result = [];
        for (var i = 0; i < array.length; i++) {
            if (filterFunc(array[i])) {
                result.push(i);
            }
        };
        return result;
    },
    /**
     * 用于对模板进行渲染
     * @method
     * @param {Object}item
     *  数据
     * @param {Function}tpl
     *  模板
     * @param {Function}reFunc
     *    处理方法
     * @param {Object}cxt
     *    上下文
     * @returns {String}result
     */
    render: function(item, tpl, reFunc, cxt) {
        return tpl.replace(/\{[$a-zA-Z0-9_-]*\}/ig, function(w) {
            var key = w.substr(1, w.length - 2);
            var str = (item[key] || item[key] === 0) ? item[key] : '';
            if (reFunc)
                str = reFunc.call(cxt || this, item, key, str);
            return str;
        });
    },
    /**
     * 四舍五入
     * @method
     * @param {float}val
     *  数据
     * @param {int}limitNum
     *  保留小数位
     * @returns {String}result
     */
    roundDecimal: function(val, limitNum) {
        limitNum = Math.pow(10, limitNum);
        return Math.round(parseFloat(val) * limitNum) / limitNum;
    },
    /**
     * 格式化占用空间大小
     * @method
     * @param {int}m
     *  字节数
     * @returns {String}result
     */
    formatDiskSize: function(m){
        var r = m / 1;
        var b = ['', 'K', 'M', 'G', 'T', 'P'];
        var flag = 0;
        while(r >= 1024){
            r = r / 1024; 
            flag++;
        }
        return (r.toFixed(2) + b[flag] + 'B');
    }
};