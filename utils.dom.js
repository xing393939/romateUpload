var UTIL = UTIL || {};
/**
 * <h2>工具类(对dom节点进行操作)</h2>
 * @class UTIL.dom
 * @static
 */
UTIL.dom = UTIL.dom || {
    /**
     * 获取dom节点的CSS属性值
     * @method
     * @param {Object}domNode
     *     需要获取的元素节点
     * @returns {Object}result
     *      CSS属性对象
     */
    genCurrentStyle: function(domNode) {
        var computedStyle = null;
        if (document.defaultView && document.defaultView.getComputedStyle) return document.defaultView.getComputedStyle(domNode, null);
        else if (domNode && domNode.currentStyle) return domNode.currentStyle;
        return computedStyle;
    },
    /**
     * 通过className获取元素节点
     * @method
     * @param {Object}p
     *     需要查询的父节点
     * @param {String}tagName
     *      标签类型
     * @param {String}clsName
     *      className
     * @returns {Array}result
     */
    filterElemByClass: function(p, tagName, clsName) {
        var cons = p.getElementsByTagName(tagName) || [];
        var result = [];
        var t = {};
        for (var i = 0, l = cons.length; i < l; i++) {
            t = cons[i];
            if (t && t.className && t.className.indexOf(clsName) > -1) {
                result.push(t);
            }
        }
        return result;
    },
    /**
     * 通过元素属性获取元素
     * @method
     * @param {Object}p
     *      需要查询的父节点
     * @param {String}tagName
     *      标签类型
     * @param {String}atttName
     *      属性名称
     * @param {String}atttVal
     *      属性值
     * @returns {Array} result
     */
    filterElemByAttrVal: function(p, tagName, atttName, atttVal) {
        var cons = p.getElementsByTagName(tagName) || [];
        var result = [];
        var t = {};
        for (var i = 0, l = cons.length; i < l; i++) {
            t = cons[i];
            if (t && ((t[atttName] && t[atttName].indexOf(atttVal) > -1) || t.getAttribute && t.getAttribute(atttName) && t.getAttribute(atttName).indexOf(atttVal) > -1)) {
                result.push(t);
            }
        }
        return result;
    },
    /**
     * 给元素添加class
     * @method
     * @param {Object}item
     *     目标元素
     * @param {String}cls
     *      className
     */
    addClass: function(item, cls) {
        if (UTIL.dom.hasClass(item, cls)) return;
        var tcls = item.className;
        tcls = tcls ? (tcls + ' ' + cls) : cls;
        item.className = tcls;
    },
    /**
     * 移除元素的class
     * @method
     * @param {Object}item
     *      目标元素
     * @param {String}cls
     *      需要移除的className
     */
    removeClass: function(item, cls) {
        var rgex = new RegExp(cls, 'gi');
        item.className = item.className.replace(rgex, '');
    },
    /**
     * 判断一个元素是否拥有某个className
     * @method
     * @param {Object}item
     *      目标元素
     * @param {String}cls
     *      className
     * @returns {Boolean}result
     */
    hasClass: function(item, cls) {
        var rgex = new RegExp(cls, 'gi');
        var tcls = item.className;
        return rgex.test(tcls);
    },
    /**
     * 获取元素的偏移量
     * @method
     * @param {Object}elem
     *      目标元素
     * @returns {Object}result
     */
    getElemCoordinate: function(elem) {
        var t = elem.offsetTop;
        var l = elem.offsetLeft;
        var w = elem.offsetWidth;
        var h = elem.offsetHeight;
        while (elem = elem.offsetParent) {
            t += elem.offsetTop;
            l += elem.offsetLeft;
        }
        return {
            width: w,
            height: h,
            top: t,
            left: l,
            bottom: t + h,
            right: l + w
        };
    },
    /**
     * 获取文本应占的像素宽度
     * @method
     * @param {Object}str
     *      文本
     * @param {Object}str
     *      字号
     * @returns {int}result
     */
    getDomWidthByText: function(str, fontSize) {
        var span = document.getElementById("__getwidth");
        if (span == null) {
            span = document.createElement("span");
            span.id = "__getwidth";
            document.body.appendChild(span);
            span.style.visibility = "hidden";
            span.style.whiteSpace = "nowrap";
            span.style.position = "absolute";
        }
        if (UTIL.isFirefox) {
            span.textContent = str;
        } else {
            span.innerText = str;
        }
        span.style.fontSize = fontSize;
        return span.offsetWidth;
    }
};