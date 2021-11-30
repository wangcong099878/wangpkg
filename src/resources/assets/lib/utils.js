/*
 *  ajax请求封装
 *
 */

function authGet(url, data, callback,token) {
    var Authorization = Cookies.get("Authorization") ? Cookies.get("Authorization") : "";
    if(token) {
        Authorization = token
    }
    var headers = {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Authorization': 'bearer ' + Authorization,
    };
    Get(url, data, callback, headers);
}

function Get(url, data, callback, headers) {
    var headers = headers ? headers : {'Content-Type': 'application/x-www-form-urlencoded'};

    $.ajax({
        type: 'GET',
        url: url,
        data: data,
        timeout: 15000,
        dataType: 'json',
        headers : headers,
        success: function(res){
            callback(res)
        },
        error: function(xhr, type){
            $(document).dialog({
                type : 'notice',
                infoText: '网络错误,请求失败',
                autoClose: 1500,
                position: 'bottom'
            });
        }
    });
}

function authPost(url, data, callback , token) {
    var Authorization = Cookies.get("Authorization") ? Cookies.get("Authorization") : "";
    // 如果是安卓端,则主动传递token进来
    if(token) {
        Authorization = token
    }
    var headers = {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Authorization': 'bearer ' + Authorization,
    };
    POST(url, data, callback, headers);
}

function POST(url, data, callback, headers) {
    var headers = headers ? headers : {'Content-Type': 'application/x-www-form-urlencoded'};

    $.ajax({
        type: 'POST',
        url: url,
        data: data,
        timeout: 15000,
        dataType: 'json',
        headers : headers,
        success: function(res){
            if(res.code==1005){
                try{
                    android.startLogin();
                }catch(e){
                    console.log(e);
                }

            }

            callback(res)
        },
        error: function(xhr, type){
            $(document).dialog({
                type : 'notice',
                infoText: '网络错误,请求失败',
                autoClose: 1500,
                position: 'bottom'
            });
        }
    });
}

/*******************************************/
/*  JavaScript Cookie v2.2.0
/*	操作Cookies基本方法:
/*	1.引入js-cookie.min.js文件
/*	Cookies.set('name', 'value'); //设置cookies
/*	Cookies.set('name', 'value', { expires: 7 }); //设置cookies的过期时间
/*	Cookies.set('name', 'value', { expires: 7, path: '' }); // 设置cookies的过期时间和存储路径,默认根路径
/*	Cookies.get('name'); // => 'value'读取cookie的内容,为空返回undeined
/*	Cookies.get(); // => { name: 'value' } 读取全部cookie内容
/*	Cookies.remove('name'); // 删除cookies kv对
/*	var Cookies2 = Cookies.noConflict();
/*	Cookies2.set('name', 'value')  // 命名空间冲突检测
/*************************************************/

;(function (factory) {
    var registeredInModuleLoader;
    if (typeof define === 'function' && define.amd) {
        define(factory);
        registeredInModuleLoader = true;
    }
    if (typeof exports === 'object') {
        module.exports = factory();
        registeredInModuleLoader = true;
    }
    if (!registeredInModuleLoader) {
        var OldCookies = window.Cookies;
        var api = window.Cookies = factory();
        api.noConflict = function () {
            window.Cookies = OldCookies;
            return api;
        };
    }
}(function () {
    function extend () {
        var i = 0;
        var result = {};
        for (; i < arguments.length; i++) {
            var attributes = arguments[ i ];
            for (var key in attributes) {
                result[key] = attributes[key];
            }
        }
        return result;
    }

    function decode (s) {
        return s.replace(/(%[0-9A-Z]{2})+/g, decodeURIComponent);
    }

    function init (converter) {
        function api() {}

        function set (key, value, attributes) {
            if (typeof document === 'undefined') {
                return;
            }

            attributes = extend({
                path: '/'
            }, api.defaults, attributes);

            if (typeof attributes.expires === 'number') {
                attributes.expires = new Date(new Date() * 1 + attributes.expires * 864e+5);
            }

            // We're using "expires" because "max-age" is not supported by IE
            attributes.expires = attributes.expires ? attributes.expires.toUTCString() : '';

            try {
                var result = JSON.stringify(value);
                if (/^[\{\[]/.test(result)) {
                    value = result;
                }
            } catch (e) {}

            value = converter.write ?
                converter.write(value, key) :
                encodeURIComponent(String(value))
                    .replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);

            key = encodeURIComponent(String(key))
                .replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent)
                .replace(/[\(\)]/g, escape);

            var stringifiedAttributes = '';
            for (var attributeName in attributes) {
                if (!attributes[attributeName]) {
                    continue;
                }
                stringifiedAttributes += '; ' + attributeName;
                if (attributes[attributeName] === true) {
                    continue;
                }

                // Considers RFC 6265 section 5.2:
                // ...
                // 3.  If the remaining unparsed-attributes contains a %x3B (";")
                //     character:
                // Consume the characters of the unparsed-attributes up to,
                // not including, the first %x3B (";") character.
                // ...
                stringifiedAttributes += '=' + attributes[attributeName].split(';')[0];
            }

            return (document.cookie = key + '=' + value + stringifiedAttributes);
        }

        function get (key, json) {
            if (typeof document === 'undefined') {
                return;
            }

            var jar = {};
            // To prevent the for loop in the first place assign an empty array
            // in case there are no cookies at all.
            var cookies = document.cookie ? document.cookie.split('; ') : [];
            var i = 0;

            for (; i < cookies.length; i++) {
                var parts = cookies[i].split('=');
                var cookie = parts.slice(1).join('=');

                if (!json && cookie.charAt(0) === '"') {
                    cookie = cookie.slice(1, -1);
                }

                try {
                    var name = decode(parts[0]);
                    cookie = (converter.read || converter)(cookie, name) ||
                        decode(cookie);

                    if (json) {
                        try {
                            cookie = JSON.parse(cookie);
                        } catch (e) {}
                    }

                    jar[name] = cookie;

                    if (key === name) {
                        break;
                    }
                } catch (e) {}
            }

            return key ? jar[key] : jar;
        }

        api.set = set;
        api.get = function (key) {
            return get(key, false /* read as raw */);
        };
        api.getJSON = function (key) {
            return get(key, true /* read as json */);
        };
        api.remove = function (key, attributes) {
            set(key, '', extend(attributes, {
                expires: -1
            }));
        };

        api.defaults = {};

        api.withConverter = init;

        return api;
    }

    return init(function () {});
}));


/**************************************************************/
/* author: wangcong
/* time: 2019/3/03
/* discription:  模拟安卓IOS弹出层插件
/* version: 1.0.1
/**************************************************************/

;(function($, window, document, undefined) {
    'use strict';

    var Dialog = (function() {
        /**
         * 弹窗构造函数
         * @param {dom obj}   element   调用对象
         * @param {json obj}  options   弹窗配置项
         */
        function Dialog(element, options) {
            this.$element = $(element);
            // console.log($.fn.dialog.defaults);
            // 配置对象
            this.settings = $.extend({}, $.fn.dialog.defaults, options);
        }

        Dialog.prototype = {
            /**
             * 初始化弹窗
             */
            _init: function() {
                var self = this;
                // console.log('初始化弹窗');
                clearTimeout(self.autoCloseTimer);
                self.isHided = false;                   // 是否已经隐藏
                self.tapBug = self._hasTapBug();        // 是否有点透 BUG
                self.platform = mobileUtil.platform;    // 访问设备平台


                // 弹窗风格, 默认自动判断平台; 否则, 为指定平台
                self.dislogStyle = self.settings.style ===' default' ? self.platform : self.settings.style;


                // 创建弹窗显示时, 禁止 body 内容滚动的样式并且添加到 head
                if ($('#dialog-body-no-scroll').length === 0) {
                    var styleContent = '.body-no-scroll { position: absolute; overflow: hidden; width: 100%;}';
                    $('head').append('<style id="dialog-body-no-scroll">'+ styleContent +'</style>');
                }

                self._renderDOM();
                self._bindEvents();
            },

            /**
             * 渲染弹窗 DOM 结构
             */
            _renderDOM: function() {
                var self = this;
                //  显示弹窗前
                self.settings.onBeforeShow();
                //  建立dom节点
                self._createDialogDOM(self.settings.type);
                //  显示弹窗
                self.settings.onShow();
            },

            /**
             * 绑定弹窗相关事件
             */
            _bindEvents: function() {
                var self = this;

                // 确定按钮关闭弹窗
                self.$confirmBtn.on(mobileUtil.tapEvent, function(ev) {
                    var callback = self.settings.onClickConfirmBtn();
                    if (callback || callback === undefined) {
                        self.closeDialog();
                    }
                }).on('touchend', function(ev) {
                    ev.preventDefault();
                });

                // 取消按钮关闭弹窗
                self.$cancelBtn.on(mobileUtil.tapEvent, function(ev) {
                    var callback = self.settings.onClickCancelBtn();
                    if (callback || callback === undefined) {
                        self.closeDialog();
                    }
                }).on('touchend', function(ev) {
                    ev.preventDefault();
                });

                // 关闭按钮关闭弹窗
                self.$closeBtn.on(mobileUtil.tapEvent, function(ev) {
                    var callback = self.settings.onClickCloseBtn();
                    if (callback || callback === undefined) {
                        self.closeDialog();
                    }
                }).on('touchend', function(ev) {
                    ev.preventDefault();
                });

                // 遮罩层关闭弹窗
                if (self.settings.overlayClose) {
                    $(document).on(mobileUtil.tapEvent, '.dialog-overlay', function(ev) {
                        self.closeDialog();
                    });
                }

                // 自动关闭弹窗
                if( self.settings.autoClose > 0 ){
                    // console.log(self.settings.autoClose/1000 +'秒后, 自动关闭弹窗');
                    self._autoClose();
                }

                // 删除弹窗和 tap 点透 BUG 遮罩层, 在隐藏弹窗的动画结束后执行
                $(document).on('webkitAnimationEnd MSAnimationEnd animationend', '.dialog-content', function() {
                    if (self.isHided) {
                        self.removeDialog();
                        if (self.tapBug) {
                            self._removeTapOverlayer();
                        }
                    }
                });

                // 为自定义按钮组绑定回调函数
                if (self.settings.buttons.length) {
                    $.each(self.settings.buttons, function(index, item) {
                        self.$dialogContentFt.children('button').eq(index).on(mobileUtil.tapEvent, function(ev) {
                            ev.preventDefault();
                            var callback = item.callback();
                            if (callback || callback === undefined) {
                                self.closeDialog();
                            }
                        });
                    });
                }

                // 如果弹窗有最大高度设置项, 在窗口大小改变时, 重新设置弹窗最大高度
                $(window).on("onorientationchange" in window ? "orientationchange" : "resize", function() {
                    if (self.settings.contentScroll) {
                        setTimeout(function() {
                            self._resetDialog();
                        }, 200);
                    }
                });


                // 阻止 body 内容滑动
                $(document).on('touchmove', function(e) {
                    if (self.$dialog.find($(e.target)).length){
                        return false;
                    } else {
                        return true;
                    }
                });

                // 弹窗有最大高度设置项, 设置提示内容滑动
                if (self.settings.contentScroll) {
                    self._contentScrollEvent();
                }

                // 安卓风格的点击水波纹
                if (self.dislogStyle === 'android') {
                    $('.dialog-content-ft > .dialog-btn').ripple();
                }

            },

            /**
             * 根据弹窗类型, 创建弹窗 DOM 结构
             * @param {string}  dialogType   弹窗类型
             */
            _createDialogDOM: function(dialogType) {
                var self = this;

                self.$dialog = $('<div class="dialog dialog-open '+ self.settings.dialogClass +'" data-style="'+ self.dislogStyle +'"></div>');
                self.$dialogOverlay = $('<div class="dialog-overlay"></div>');
                self.$dialogContent = $('<div class="dialog-content"></div>');
                self.$dialogTitle = $('<div class="dialog-content-hd"><h3 class="dialog-content-title">'+ self.settings.titleText +'</h3></div>');
                self.$dialogContentFt = $('<div class="dialog-content-ft"></div>');
                self.$dialogContentBd = $('<div class="dialog-content-bd"></div>');
                self.$closeBtn = $('<div class="dialog-btn-close"><span>close</span></div>');
                self.$confirmBtn = $('<button class="dialog-btn dialog-btn-confirm '+ self.settings.buttonClassConfirm +'">'+ self.settings.buttonTextConfirm +'</button>');
                self.$cancelBtn = $('<button class="dialog-btn dialog-btn-cancel '+ self.settings.buttonClassCancel +'">'+ self.settings.buttonTextCancel +'</button>');

                switch(dialogType) {
                    case 'alert':
                        // 添加 alert 类型弹窗标识
                        self.$dialog.addClass('dialog-modal');
                        // 显示遮罩层
                        if (self.settings.overlayShow) {
                            self.$dialog.append(self.$dialogOverlay);
                        }

                        // 显示标题
                        if (self.settings.titleShow) {
                            self.$dialogContent.append(self.$dialogTitle);
                        }

                        // 显示关闭按钮
                        if (self.settings.closeBtnShow) {
                            self.$dialogTitle.append(self.$closeBtn);
                        }
                        // 内容框
                        self.$dialogContentBd.html(self.settings.content);

                        // alert只有确认按钮, 故只用来赋值;
                        self.$dialogContentFt.append(self.$confirmBtn);

                        // 将按钮节点和内容节点添加到dom中去;
                        self.$dialogContent.append(self.$dialogContentBd).append(self.$dialogContentFt);

                        // 内容节点添加到最外层
                        self.$dialog.append(self.$dialogContent);

                        // 添加到body身上
                        $('body').append(self.$dialog);

                        // 测试无效
                        if (self.settings.bodyNoScroll) {
                            $('body').addClass('body-no-scroll');
                        }

                        // 如果配置了内容最大高度true
                        if (self.settings.contentScroll) {
                            self._setDialogContentHeight();
                        }

                        break;
                    case 'confirm':
                        // 添加 confirm 类型弹窗标识
                        self.$dialog.addClass('dialog-modal');

                        // 显示遮罩层
                        if (self.settings.overlayShow) {
                            self.$dialog.append(self.$dialogOverlay);
                        }
                        // 显示标题
                        if (self.settings.titleShow) {
                            self.$dialogContent.append(self.$dialogTitle);
                        }
                        // 显示关闭按钮
                        if (self.settings.closeBtnShow) {
                            self.$dialogTitle.append(self.$closeBtn);
                        }

                        // 按钮: 如果有设置自定义按钮组, 则用自定义按钮组; 否则用默认的"确定"与"取消"按钮
                        if (self.settings.buttons.length) {
                            var buttonGroupHtml = '';
                            $.each(self.settings.buttons, function(index, item) {
                                buttonGroupHtml += '<button class="dialog-btn '+ item.class +'">'+ item.name +'</button>';

                            });
                            self.$dialogContentFt.append(buttonGroupHtml).addClass(self.settings.buttonStyle);
                        } else {
                            self.$dialogContentFt.append(self.$cancelBtn).append(self.$confirmBtn).addClass(self.settings.buttonStyle);
                        }

                        self.$dialogContentBd.html(self.settings.content);
                        self.$dialogContent.append(self.$dialogContentBd).append(self.$dialogContentFt);
                        self.$dialog.append(self.$dialogContent);
                        $('body').append(self.$dialog);

                        // 设置弹窗提示内容最大高度
                        if (self.settings.contentScroll) {
                            self._setDialogContentHeight();
                        }

                        if (self.settings.bodyNoScroll) {
                            $('body').addClass('body-no-scroll');
                        }

                        break;
                    case 'toast':
                        // 添加 toast 类型弹窗标识
                        self.$dialog.addClass('dialog-toast');

                        // 显示遮罩层
                        if (self.settings.overlayShow) {
                            self.$dialog.append(self.$dialogOverlay);
                        }

                        // 弹窗内容 HTML, 默认为 content; 如果设置 icon 与 text, 则覆盖 content 的设置
                        var toastContentHtml = $(self.settings.content);

                        if (self.settings.infoIcon !== '' && self.settings.infoText !== '') {
                            toastContentHtml = $('<img class="info-icon" src="'+ self.settings.infoIcon + '" /><span class="info-text">'+ self.settings.infoText +'</span>');
                        } else if (self.settings.infoIcon === '' && self.settings.infoText !== '') {
                            toastContentHtml = $('<span class="info-text">'+ self.settings.infoText +'</span>');
                        } else if (self.settings.infoIcon !== '' && self.settings.infoText === '') {
                            toastContentHtml = $('<img class="info-icon" src="'+ self.settings.infoIcon + '" />');
                        }

                        self.$dialogContentBd.append(toastContentHtml);
                        self.$dialogContent.append(self.$dialogContentBd);
                        self.$dialog.append(self.$dialogContent);
                        $('body').append(self.$dialog);

                        if (self.settings.bodyNoScroll) {
                            $('body').addClass('body-no-scroll');
                        }

                        break;
                    case 'notice':
                        // 添加 toast 类型弹窗标识
                        self.$dialog.addClass('dialog-notice');

                        // 底部显示的 toast
                        if (self.settings.position==='bottom') {
                            self.$dialog.addClass('dialog-notice-bottom');
                        }

                        // 显示遮罩层
                        if (self.settings.overlayShow) {
                            self.$dialog.append(self.$dialogOverlay);
                        }

                        // 弹窗内容 HTML, 默认为 content; 如果设置 icon 与 text, 则覆盖 content 的设置
                        var noticeContentHtml = $(self.settings.content);

                        if (self.settings.infoIcon !== '' && self.settings.infoText !== '') {
                            noticeContentHtml = $('<img class="info-icon" src="'+ self.settings.infoIcon + '" /><span class="info-text">'+ self.settings.infoText +'</span>');
                        } else if (self.settings.infoIcon === '' && self.settings.infoText !== '') {
                            noticeContentHtml = $('<span class="info-text">'+ self.settings.infoText +'</span>');
                        } else if (self.settings.infoIcon !== '' && self.settings.infoText === '') {
                            noticeContentHtml = $('<img class="info-icon" src="'+ self.settings.infoIcon + '" />');
                        }

                        self.$dialogContentBd.append(noticeContentHtml);
                        self.$dialogContent.append(self.$dialogContentBd);
                        self.$dialog.append(self.$dialogContent);
                        $('body').append(self.$dialog);

                        if (self.settings.bodyNoScroll) {
                            $('body').addClass('body-no-scroll');
                        }

                        break;
                    default:
                        console.log('running default');
                        break;
                }
            },

            /**
             *  设置弹窗内容最大高度
             *  延迟执行, 避免获取相关尺寸不正确
             */

            _setDialogContentHeight: function() {
                var self = this;

                setTimeout(function() {
                    // 获取dialog的默认高度
                    var dialogDefaultContentHeight = self.$dialogContentBd.height();
                    // 获取内容层的最大高度
                    var dialogContentMaxHeight = self._getDialogContentMaxHeight();
                    // css设置最大高度
                    self.$dialogContentBd.css({
                        'max-height': dialogContentMaxHeight,
                    }).addClass('content-scroll');

                    // 提示内容大于最大高度时, 添加底部按钮顶部边框线标识 class; 反之, 删除
                    if (dialogDefaultContentHeight > dialogContentMaxHeight) {
                        self.$dialogContentFt.addClass('dialog-content-ft-border');
                    } else {
                        self.$dialogContentFt.removeClass('dialog-content-ft-border');
                    }

                }, 80);
            },

            /**
             * 获取弹窗内容最大高度
             * @return height
             */
            _getDialogContentMaxHeight: function() {
                var self = this;
                /*
                    winHeight : 屏幕高度
                    dialogContentHdHeight: 标题高度
                    dialogContentFtHeight: 按钮的高度
                    dialogContentBdHeight: 内容层的高度 = 窗口高度 - 内容高度 - 按钮高度 - 60  (补偿高度);

                 */
                var winHeight = $(window).height(),
                    dialogContentHdHeight = self.$dialogTitle.height(),
                    dialogContentFtHeight = self.$dialogContentFt.height(),
                    dialogContentBdHeight = winHeight - dialogContentHdHeight - dialogContentFtHeight - 60;

                 // 最大高度取偶数  校验取偶数
                 // 更改内容高度: dialogContentBdHeight =  dialogContentBdHeight
                dialogContentBdHeight = dialogContentBdHeight%2 === 0 ? dialogContentBdHeight : dialogContentBdHeight - 1;
                return dialogContentBdHeight;
            },

            /**
             *  重置弹窗, 在窗口大小发生变化时触发
             */
            _resetDialog: function() {
                var self = this;
                self._setDialogContentHeight();
            },

            /**
             * 有最大高度弹窗的提示内容滑动
             */
            _contentScrollEvent: function() {
                var self = this;

                var isTouchDown = false;
                // 初始位置
                var position = {
                    x: 0,
                    y: 0,
                    top: 0,
                    left: 0
                };

                // 监听滑动相关事件
                $(document)
                    .on('touchstart mousedown', '.content-scroll', function(ev) {
                        var touch = ev.changedTouches ? ev.changedTouches[0] : ev;

                        isTouchDown = true;
                        position.x = touch.clientX;
                        position.y = touch.clientY;
                        position.top = $(this).scrollTop();
                        position.left = $(this).scrollLeft();
                        return false;
                    })
                    .on('touchmove mousemove', '.content-scroll', function(ev) {
                        var touch = ev.changedTouches ? ev.changedTouches[0] : ev;

                        if (!isTouchDown) {
                            // 未按下
                            return false;
                        } else {
                            // 要滑动的距离 = 已经滑动的距离 - (当前坐标 - 按下坐标)
                            var moveTop  = position.top - (touch.clientY - position.y);
                            var moveLeft = position.left - (touch.clientX - position.x);

                            $(this).scrollTop(moveTop).scrollLeft(moveLeft);
                        }
                    })
                    .on('touchend mouseup', '.content-scroll', function(ev) {
                        ev.preventDefault();
                        isTouchDown = false;
                    });

            },

            /**
             * 自动关闭弹窗
             */
            _autoClose: function() {
                var self = this;
                self.autoCloseTimer = setTimeout(function(){
                    self.closeDialog();
                }, self.settings.autoClose);
            },

            /**
             * 关闭弹窗
             */
            closeDialog: function() {
                var self = this;
                self.isHided = true;
                self.settings.onBeforeClosed();
                self.$dialog.addClass('dialog-close').removeClass('dialog-open');

                if (self.tapBug) {
                    self._appendTapOverlayer();
                }
            },

            /**
             * 删除弹窗
             * @public method
             */
            removeDialog: function() {
                var self = this;
                self.$dialog.remove();
                self.isHided = false;
                self.settings.onClosed();
                // 重新初始化默认配置
                self.settings = $.fn.dialog.defaults;

                if (self.settings.bodyNoScroll) {
                    $('body').removeClass('body-no-scroll');
                }
            },

            /**
             * 更改 toast 和 notice 类型弹窗内容
             * @public method
             * @param {string}  content          弹窗内容, 可以是HTML
             * @param {string}  infoIcon         弹窗提示图标
             * @param {string}  infoText         弹窗提示文字
             * @param {int}     autoClose        自动关闭的延迟时间
             * @param {fn}      onBeforeClosed   关闭前回调函数
             * @param {fn}      onClosed         关闭后回调函数
             */
            update: function (settings) {
                var self = this;

                clearTimeout(self.autoCloseTimer);

                // 设置默认值，并且指向给对象的默认值
                self.settings = $.extend({}, $.fn.dialog.defaults, settings);

                // 通过 content 更改弹窗内容
                if (self.settings.content !== '') {
                    self.$dialogContentBd.html(self.settings.content);
                }

                // 通过设置 infoIcon 与 infoText 更改弹窗内容, 会覆盖 content 的设置
                var $infoIcon = self.$dialogContentBd.find('.info-icon');
                var $infoText = self.$dialogContentBd.find('.info-text');
                $infoIcon.attr({'src': self.settings.infoIcon});
                $infoText.html(self.settings.infoText);

                // 重新为更改后的 DOM 元素绑定事件
                self._bindEvents();
            },

            /**
             * 是否有点透 BUG
             * 条件: 安卓手机并且版本号小于4.4
             * @return Boolean
             */
            _hasTapBug: function() {
                return mobileUtil.isAndroid && (mobileUtil.version < 4.4);
            },

            /**
             * 添加点透遮罩层, 解决点透 BUG
             */
            _appendTapOverlayer: function() {
                var self = this;

                self.$tapBugOverlayer = $('.solve-tap-bug');

                if (!self.$tapBugOverlayer.length) {
                    self.$tapBugOverlayer = $('<div class="solve-tap-bug" style="margin:0;padding:0;border:0;background:rgba(0,0,0,0);-webkit-tap-highlight-color:rgba(0,0,0,0);width:100%;height:100%;position:fixed;top:0;left:0;"></div>');
                    $('body').append(self.$tapBugOverlayer);
                }
            },

            /**
             * 删除点透遮罩层, 延迟执行的时间大于移动端的 click 触发时间
             */
            _removeTapOverlayer: function() {
                var self = this;

                setTimeout(function() {
                    self.$tapBugOverlayer.remove();
                }, 350);
            }
        };

        return Dialog;
    })();

    /**----------------------------
     *  私有方法
     ----------------------------*/
    /**
     * 移动端相关数据 =>> mobileUtil 对象
     * 是否是安卓  : isAndroid
     * 是否是IOS   : isIOS
     * 是否是移动端: isMobile
     * 设备平台    : platform [ ios 或 android ]
     * 事件类型    : tapEvent [ tapEvent 或 click ]
     * 系统版本号  : version [ 如: ios 9.1 或 andriod 6.0 ]
     * 是否支持 touch 事件: isSupportTouch
     */
    var mobileUtil = (function(window) {
        var UA = window.navigator.userAgent,
            isAndroid = /android|adr/gi.test(UA),
            isIOS = /iphone|ipod|ipad/gi.test(UA) && !isAndroid,
            isMobile = isAndroid || isIOS,
            platform = isIOS ? 'ios' : (isAndroid ? 'android' : 'default'),
            isSupportTouch = "ontouchend" in document ? true : false;

        var reg = isIOS ? (/os [\d._]*/gi):(/android [\d._]*/gi),
            verinfo = UA.match(reg),
            version = (verinfo+"").replace(/[^0-9|_.]/ig,"").replace(/_/ig,".");

        return {
            isIOS: isIOS,
            isAndroid: isAndroid,
            isMobile: isMobile,
            platform: platform,
            version: parseFloat(version),
            isSupportTouch: isSupportTouch,
            tapEvent: isMobile && isSupportTouch ? 'tapEvent' : 'click'
        };
    })(window);


    // 绑定dialog到zepto的prototype上

    $.fn.dialog = function(options) {
        // document对象
        var self = this;

        // //遍历匹配的元素，此处的this表示为document
        return this.each(function(){
            var $this = $(this);
            // undefined
            var instance = window.jQuery ? $this.data('dialog') : $.fn.dialog.lookup[ $this.data('dialog') ];

            if (!instance) {
                // 初始化一个dialog对象  this document,  option 用户配置项
                var obj = new Dialog(this, options);
                obj._init();

                if (window.jQuery) {
                    $this.data('dialog', obj);
                } else {
                    // 实例化对象
                    $.fn.dialog.lookup[ ++$.fn.dialog.lookup.i ] = obj;
                    $this.data('dialog', $.fn.dialog.lookup.i);
                    // 实例化个数
                    instance = $.fn.dialog.lookup[$this.data('dialog')];
                }
            } else {
                var obj = new Dialog(this, options);
                obj._init();
            }

            if (typeof options === 'string') {
                instance[options]();
            }


            // 提供外部调用公共方法
            self.close = function(){
                obj.closeDialog();
            };

            self.update = function(settings){
                obj.update(settings);
            };
        });
    };

    if (!window.jQuery) {
        $.fn.dialog.lookup = {
            i: 0
        };
    }

    /**
     * 插件默认值
     */
    $.fn.dialog.defaults = {
        type         : 'alert',   // 弹窗的类型 [ alert: 确定; confirm: 确定/取消; toast: 状态提示; notice: 提示信息 ]
        style        : 'default', // alert 与 confirm 弹窗的风格 [ default: 根据访问设备平台; ios: ios 风格; android: MD design 风格 ]
        titleShow    : true,      // 是否显示标题
        titleText    : '提示',    // 标题文字
        bodyNoScroll : false,     // body内容不可以滚动
        closeBtnShow : false,     // 是否显示关闭按钮
        content      : '',        // 弹窗提示内容, 值可以是 HTML 内容
        contentScroll: true,      // alert 与 confirm 弹窗提示内容是否限制最大高度, 使其可以滚动
        dialogClass  : '',        // 弹窗自定义 class
        autoClose    : 0,         // 弹窗自动关闭的延迟时间(毫秒)。0: 不自动关闭; 大于0: 自动关闭弹窗的延迟时间
        overlayShow  : true,      // 是否显示遮罩层
        overlayClose : false,     // 是否可以点击遮罩层关闭弹窗

        buttonStyle       : 'side',   // 按钮排版样式 [ side: 并排; stacked: 堆叠 ]
        buttonTextConfirm : '确定',   // 确定按钮文字
        buttonTextCancel  : '取消',   // 取消按钮文字
        buttonClassConfirm: '',       // 确定按钮自定义 class
        buttonClassCancel : '',       // 取消按钮自定义 class
        buttons           : [],       // confirm 弹窗自定义按钮组, 会覆盖"确定"与"取消"按钮; 单个 button 对象可设置 name [ 名称 ]、class [ 自定义class ]、callback [ 点击执行的函数 ]

        infoIcon: '',        // toast 与 notice 弹窗的提示图标, 值为图标的路径。不设置=不显示
        infoText: '',        // toast 与 notice 弹窗的提示文字, 会覆盖 content 的设置
        position: 'center',  // notice 弹窗的位置, [ center: 居中; bottom: 底部 ]

        onClickConfirmBtn: function(){},  // “确定”按钮的回调函数
        onClickCancelBtn : function(){},  // “取消”按钮的回调函数
        onClickCloseBtn  : function(){},  // “关闭”按钮的回调函数
        onBeforeShow     : function(){},  // 弹窗显示前的回调函数
        onShow           : function(){},  // 弹窗显示后的回调函数
        onBeforeClosed   : function(){},  // 弹窗关闭前的回调函数
        onClosed         : function(){}   // 弹窗关闭后的回调函数
    };

})(window.jQuery || window.Zepto, window, document);



// 注册快捷事件
;(function($, window, document, undefined) {
    'use strict';

    $(document).ready(function() {
        var startX,       // 开始横坐标
            startY,       // 开始纵坐标
            endX,         // 结束横坐标
            endY,         // 结束纵坐标
            startTime,    // 按下的开始时间
            element;      // 触发事件元素

        $(document).on('touchstart', function(e) {
                var e = e.originalEvent || e;
                var touch = e.changedTouches[0];
                element = $('tagName' in touch.target ? touch.target : touch.target.parentNode);
                startTime = new Date();
                startX = touch.clientX;
                startY = touch.clientY;
                endX = touch.clientX;
                endY = touch.clientY;
            })
            .on('touchmove',function(e) {
                var e = e.originalEvent || e;
                var touch = e.changedTouches[0];

                endX = touch.clientX;
                endY = touch.clientY;
            })
            .on('touchend',function(e) {
                var e = e.originalEvent || e;
                var touch = e.changedTouches[0];
                var endTime = new Date();

                // 结束时间 - 开始时间 < 300毫秒, 并且移动距离(开始坐标-结束左边)<20, 则触发事件 tapEvent
                if (endTime-startTime < 300) {
                    if (Math.abs(endX-startX) + Math.abs(endY-startY) < 30) {
                        element.trigger('tapEvent');
                    }
                }

                startTime = 0;
                startX = 0;
                startY = 0;
                endX = 0;
                endY = 0;
            });
    });

    // 注册快捷事件 tapEvent, 调用: $element.tapEvent(fn);
    ;['tapEvent'].forEach(function(eventName) {
        $.fn[eventName] = function(callback) {
            return this.on(eventName, callback);
        };
    });
})(window.jQuery || window.Zepto, window, document);


// 水特特效;
;(function($, window, document, undefined) {
    'use strict';

    var Ripple = (function() {

        function Ripple(element, options) {
            var self = this;
            self.$element = $(element);
            self.settings = $.extend({}, $.fn.ripple.defaults, options);
            self.target = null;  // 目标元素
            self.positionX = 0;  // 点击位置的横坐标
            self.positionY = 0;  // 点击位置的纵坐标

            self.init();
        }

        Ripple.prototype = {
            /**
             * 初始化
             */
            init: function() {
                var self = this;
                self.bindEvents();
            },

            /**
             * 绑定事件
             */
            bindEvents: function() {
                var self = this;
                // 点击时, 获取点击的目标元素以及其点击位置, 并创建水波纹 DOM
                self.$element.on(event.downEvent, function(ev) {
                    var touch = ev.changedTouches ? ev.changedTouches[0] : ev;
                    self.target = $(touch.target);
                    self.positionX = touch.pageX;
                    self.positionY = touch.pageY;
                    self.creatRipple();
                });

                // 运动结束后, 删除水波纹 DOM
                $(document).on('webkitAnimationEnd MSAnimationEnd animationend', ('.'+ self.settings.className), function() {
                    var $removeElement = this;
                    self.removeRipple($removeElement);
                });
            },

            /**
             * 创建水波纹
             */
            creatRipple: function() {
                var self = this;
                var rect = getRect(self.target[0]);
                var size = Math.max(rect.width, rect.height);    // 目标元素相对窗口宽、高的最大值
                var elementLeft = self.target.offset().left;     // 目标元素相对窗口的横坐标
                var elementTop  = self.target.offset().top;      // 目标元素相对窗口的纵坐标

                // 创建水波纹DOM
                self.$rippleElement = $('<'+ self.settings.tagName +'></'+ self.settings.tagName +'>');

                // 设置水波纹DOM的位置与大小, 并添加到目标元素内
                self.$rippleElement
                    .addClass(self.settings.className)
                    .css({
                        left: self.positionX - elementLeft - size/2,
                        top: self.positionY - elementTop - size/2,
                        width: size,
                        height: size
                    });
                self.target.append(self.$rippleElement);
            },

            /**
             * 删除水波纹
             * @param {jQuery obj}  $element   jQuery 对象
             */
            removeRipple: function($element) {
                var self = this;
                $element.remove();
            }
        };

        return Ripple;
    })();



    /**
     * 获取元素的左、右、上、下相对浏览器视窗的位置
     * @param {DOM obj}  element   DOM 对象
     */
    function getRect(element) {
        try {
            return element.getBoundingClientRect();
        } catch(error) {
            console.log('No support getBoundingClientRect', error.message);
        }
    }

    /**
     * 按下的事件类型: event.downEvent [ touchstart 或 mousedown ]
     */
    var event = (function(window) {
        var UA = window.navigator.userAgent,
            isAndroid = /android|adr/gi.test(UA),
            isIOS = /iphone|ipod|ipad/gi.test(UA) && !isAndroid,
            isMobile = isAndroid || isIOS,
            isSupportTouch = "ontouchend" in document ? true : false;
        return {
            downEvent: isMobile && isSupportTouch ? 'touchstart' : 'mousedown'
        };
    })(window);


    $.fn.ripple = function(options) {
        var self = this;
        var list = [];
        this.each(function(i, self){
            list.push(new Ripple(self, options));
        });
        return list;
    };

    $.fn.ripple.defaults = {
        tagName: 'span',
        className: 'ripple'
    };


    // 通过 data-ripple 的方式实例化插件
    $(function() {
        return new Ripple($('[data-ripple]'));
    });
})(window.jQuery || window.Zepto, window, document);


/*
    阻止冒泡
 */
function stopProp (e) {
    e =window.event||e;
    //只有ie识别
    if(document.all){
        e.cancelBubble = true;
    }else{
        e.stopPropagation();
    }
}

/*
 * 解析请求url配置参数
 *
 */
;function parse_url(url) {
    var a = document.createElement('a');
    a.href = url;
    return {
        source: url,
        protocol: a.protocol.replace(':',''),
        host: a.hostname,
        port: a.port,
        query: a.search,
        params: (function(){
            var ret = {},
                seg = a.search.replace(/^\?/,'').split('&'),
                len = seg.length, i = 0, s;
            for (;i<len;i++) {
                if (!seg[i]) { continue; }
                s = seg[i].split('=');
                ret[s[0]] = s[1];
            }
            return ret;
        })(),
        file: (a.pathname.match(/\/([^\/?#]+)$/i) || [,''])[1],
        hash: a.hash.replace('#',''),
        path: a.pathname.replace(/^([^\/])/,'/$1'),
        relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [,''])[1],
        segments: a.pathname.replace(/^\//,'').split('/')
    };
}



/*
 *  封装一个css方法,执行完毕自动删除类 需要ES6打包处理
 */
var animateCss = ( element, animation, prefix='animate__' , delayTimer , isInfinite) => {
    // 一个promise
    new Promise((resolve, reject)=> {
        const animationName = `${prefix}${animation}`;
        const node = document.querySelector(element);
        node.classList.add(`${prefix}animated`, animationName);

        // 动画结束清除类,返回promise resolve
        function handleAnimationEnd() {
            node.classList.remove(`${prefix}animated`, animationName);
            node.removeEventListener( 'animationend', handleAnimationEnd );
            resolve('Animation ended');
        }

        // 监听结束后执行函数
        node.addEventListener('animationend', handleAnimationEnd);

    });
}

/*
 *  判断元素是否包含一个类
 */
function hasClass(ele, cls) {
    cls = cls || '';
    if (cls.replace(/\s/g, '').length == 0) return false;  //当cls没有参数时，返回false
    return new RegExp(' ' + cls + ' ').test(' ' + ele.className + ' ');
}

function addClass(ele, cls) {
    if (!hasClass(ele, cls)) {
        ele.className = ele.className == '' ? cls : ele.className + ' ' + cls;
    }
}

function removeClass(ele, cls) {
    if (hasClass(ele, cls)) {
        var newClass = ' ' + ele.className.replace(/[\t\r\n]/g, '') + ' ';
        while (newClass.indexOf(' ' + cls + ' ') >= 0) {
            newClass = newClass.replace(' ' + cls + ' ', ' ');
        }
        ele.className = newClass.replace(/^\s+|\s+$/g, '');
    }
}

/*
   *  fn :  带执行函数
   *  wait: 等待时间
   */
function debounce( fn , wait) {
    var timer = null;
    return function() {
        var context = this;
        // console.log(context);

        var args = arguments;
        // 定时器存在 自动清空
        if(timer) {
            clearTimeout(timer);
            timer = null;
        }
        // 首次一个延时器
        timer = setTimeout(function() {
            fn.apply(context,args);
        } , wait);

    }
}
// function debounce(func, wait, immediate) {
//     var timeout;
//     return function() {
//         var context = this, args = arguments;
//         var later = function() {
//             timeout = null;
//             if (!immediate) func.apply(context, args);
//         };
//         var callNow = immediate && !timeout;
//         clearTimeout(timeout);
//         timeout = setTimeout(later, wait);
//         if (callNow) func.apply(context, args);
//     };
// };

/*
 *   函数的节流: throttle
 *
 */
function throttle(fn, gapTime) {
    let _lastTime =  null;
    return function() {
        let _nowTime = new Date();
        if( _nowTime - _lastTime > gapTime || !_lastTime ) {
            fn();
            _lastTime = _nowTime
        }
    }
}


/*
    换算时间差:
    params: dateTimeStamp 时间戳
 */

function getDateDiff(dateTimeStamp,serverTime){

    var minute = 1000 * 60;
    var hour = minute * 60;
    var day = hour * 24;
    var halfamonth = day * 15;
    var month = day * 30;
    var diffValue;

    if(serverTime == undefined) {
        diffValue = new Date().getTime() - new Date(dateTimeStamp).getTime();
    } else {
        diffValue  = serverTime - dateTimeStamp;
    }

    if(diffValue < 0){return;}
    var monthC =diffValue/month;
    var weekC =diffValue/(7*day);
    var dayC =diffValue/day;
    var hourC =diffValue/hour;
    var minC =diffValue/minute;
    if(monthC>=1){
        result="" + parseInt(monthC) + "月前";
    }
    else if(weekC>=1){
        result="" + parseInt(weekC) + "周前";
    }
    else if(dayC>=1){
        result=""+ parseInt(dayC) +"天前";
    }
    else if(hourC>=1){
        result=""+ parseInt(hourC) +"小时前";
    }
    else if(minC>=1){
        result=""+ parseInt(minC) +"分钟前";
    }else
        result="刚刚";
    return result;
}

