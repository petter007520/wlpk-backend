var ns = window.ns_url;

/**
 * 解析URL
 * @param  {string} url 被解析的URL
 * @return {object}     解析后的数据
 */
ns.parse_url = function (url) {
	var parse = url.match(/^(?:([0-9a-zA-Z]+):\/\/)?([\w-]+(?:\.[\w-]+)+)?(?::(\d+))?([\w-\/]+)?(?:\?((?:\w+=[^#&=\/]*)?(?:&\w+=[^#&=\/]*)*))?(?:#([\w-]+))?$/i);
	parse || $.error("url格式不正确！");
	return {
		"scheme": parse[1],
		"host": parse[2],
		"port": parse[3],
		"path": parse[4],
		"query": parse[5],
		"fragment": parse[6]
	};
};

ns.parse_str = function (str) {
	var value = str.split("&"), vars = {}, param;
	for (var i = 0; i < value.length; i++) {
		param = value[i].split("=");
		vars[param[0]] = param[1];
	}
	return vars;
};

ns.parse_name = function (name, type) {
	if (type) {
		/* 下划线转驼峰 */
		name = name.replace(/_([a-z])/g, function ($0, $1) {
			return $1.toUpperCase();
		});
		/* 首字母大写 */
		name = name.replace(/[a-z]/, function ($0) {
			return $0.toUpperCase();
		});
	} else {
		/* 大写字母转小写 */
		name = name.replace(/[A-Z]/g, function ($0) {
			return "_" + $0.toLowerCase();
		});
		/* 去掉首字符的下划线 */
		if (0 === name.indexOf("_")) {
			name = name.substr(1);
		}
	}
	return name;
};

//scheme://host:port/path?query#fragment
ns.url = function (url, vars, suffix) {
	if (url.indexOf('http://') != -1 || url.indexOf('https://') != -1) {
		return url;
	}

	var info = this.parse_url(url), path = [], param = {}, reg;
	/* 验证info */
	info.path || alert("url格式错误！");
	url = info.path;
	/* 解析URL */
	path = url.split("/");
	path = [path.pop(), path.pop(), path.pop()].reverse();
	path[1] = path[1] || this.route[1];
	path[0] = path[0] || this.route[0];
	param[this.route[1]] = path[0];
	param[this.route[2]] = path[1];
	param[this.route[3]] = path[2].toLowerCase();
	url = param[this.route[1]] + '/' + param[this.route[2]] + '/' + param[this.route[3]];
	/* 解析参数 */
	if (typeof vars === "string") {
		vars = this.parse_str(vars);
	} else if (!$.isPlainObject(vars)) {
		vars = {};
	}
	/* 添加伪静态后缀 */
	if (false !== suffix) {
		suffix = suffix || 'html';
		if (suffix) {
			url += "." + suffix;
		}
	}
	/* 解析URL自带的参数 */
	info.query && $.extend(vars, this.parse_str(info.query));
	var addon = '';
	if (info.scheme != '' && info.scheme != undefined) {
		addon = info.scheme + '/';
	}
	url = addon + url;

	if (vars) {
		var param_str = $.param(vars);
		if ('' !== param_str) {
			url += ((this.baseUrl + url).indexOf('?') !== -1 ? '&' : '?') + param_str;
		}
	}
	// url = url.replace("shop", this.appModule);
	url = this.baseUrl + url;
	return url;
};

/**
 * 验证手机号
 * @param  {string} mobile 被验证的mobile
 * @return {object}   验证后的结果
 **/
ns.parse_mobile = function (mobile) {
	var parse = /^1(3|4|5|6|7|8|9)\d{9}$/.test(mobile);
	return parse;
};

/**
 * 处理图片路径
 * path 路径地址
 * type 类型 big、mid、small
 */
ns.img = function (path, type = '') {

	if (path.indexOf(",") != -1) {
		path = path.split(',')[0];
	}
	var start = path.lastIndexOf('.');
	type = type ? '_' + type.toUpperCase() : '';
	var suffix = path.substring(start);
	path = path.substring(0, start);
	var first = path.split("/");
	path += type + suffix;

	if (path.indexOf("http://") == -1 && path.indexOf("https://") == -1) {

		var base_url = this.baseUrl.replace('/?s=', '');
		var base_url = base_url.replace('/index.php', '');
		if (isNaN(first[0])) {
			var true_path = base_url + path;
		} else {
			var true_path = base_url + 'attachment/' + path;
		}
	} else {
		var true_path = path;
	}
	return true_path;
};

/**
 * 时间戳转时间
 *
 */
var default_time_format = 'YYYY-MM-DD h:m:s';
ns.time_to_date = function (timeStamp, time_format = '') {
	time_format = time_format == '' ? default_time_format : time_format;
	if (timeStamp > 0) {
		var date = new Date();
		date.setTime(timeStamp * 1000);
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		m = m < 10 ? ('0' + m) : m;
		var d = date.getDate();
		d = d < 10 ? ('0' + d) : d;
		var h = date.getHours();
		h = h < 10 ? ('0' + h) : h;
		var minute = date.getMinutes();
		var second = date.getSeconds();
		minute = minute < 10 ? ('0' + minute) : minute;
		second = second < 10 ? ('0' + second) : second;
		var time = '';
		time += time_format.indexOf('Y') > -1 ? y : '';
		time += time_format.indexOf('M') > -1 ? '-' + m : '';
		time += time_format.indexOf('D') > -1 ? '-' + d : '';
		time += time_format.indexOf('h') > -1 ? ' ' + h : '';
		time += time_format.indexOf('m') > -1 ? ':' + minute : '';
		time += time_format.indexOf('s') > -1 ? ':' + second : '';
		return time;
	} else {
		return "";
	}
};

/**
 * 时间戳转时间(毫秒)
 */
ns.millisecond_to_date = function (timeStamp) {
	if (timeStamp > 0) {
		var date = new Date();
		date.setTime(timeStamp);
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		m = m < 10 ? ('0' + m) : m;
		var d = date.getDate();
		d = d < 10 ? ('0' + d) : d;
		var h = date.getHours();
		h = h < 10 ? ('0' + h) : h;
		var minute = date.getMinutes();
		var second = date.getSeconds();
		minute = minute < 10 ? ('0' + minute) : minute;
		second = second < 10 ? ('0' + second) : second;
		return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second;
	} else {
		return "";
	}
};

/**
 * 日期 转换为 Unix时间戳
 * @param <string> 2014-01-01 20:20:20  日期格式
 * @return <int>        unix时间戳(秒)
 */
ns.date_to_time = function (string) {
	var f = string.split(' ', 2);
	var d = (f[0] ? f[0] : '').split('-', 3);
	var t = (f[1] ? f[1] : '').split(':', 3);
	return (new Date(
		parseInt(d[0], 10) || null,
		(parseInt(d[1], 10) || 1) - 1,
		parseInt(d[2], 10) || null,
		parseInt(t[0], 10) || null,
		parseInt(t[1], 10) || null,
		parseInt(t[2], 10) || null
	)).getTime() / 1000;
};

/**
 * url 反转义
 * @param url
 */
ns.urlReplace = function (url) {
	url = decodeURIComponent(url);
	var new_url = url.replace(/%2B/g, "+");//"+"转义
	new_url = new_url.replace(/%26/g, "&");//"&"
	new_url = new_url.replace(/%23/g, "#");//"#"
	new_url = new_url.replace(/%20/g, " ");//" "
	new_url = new_url.replace(/%3F/g, "?");//"#"
	new_url = new_url.replace(/%25/g, "%");//"#"
	new_url = new_url.replace(/&3D/g, "=");//"#"
	new_url = new_url.replace(/%2F/g, "/");//"#"
	return new_url;
};

/**
 * 需要定义APP_KEY,API_URL
 * method 插件名.控制器.方法
 * data  json对象
 * async 是否异步，默认true 异步，false 同步
 */
ns.api = function (method, param, callback, async) {
	// async true为异步请求 false为同步请求
	async = async != undefined ? async : true;
	param = param || {};
	param.port = 'platform';
	$.ajax({
		type: 'get',
		url: ns_url.baseUrl + '' + method + '?site_id=' + ns_url.siteId,
		data: param,
		dataType: "JSON",
		async: async,
		success: function (res) {
			if (callback) callback(eval("(" + res + ")"));
		}
	});
};

/**
 * url 反转义
 * @param url
 */
ns.append_url_params = function (url, params) {
	if (params != undefined) {
		var url_params = '';
		for (var k in params) {
			url_params += "&" + k + "=" + params[k];
		}
		url += url_params;
	}
	return url;
};

/**
 * 生成随机不重复字符串
 * @param len
 * @returns {string}
 */
ns.gen_non_duplicate = function (len) {
	return Number(Math.random().toString().substr(3, len) + Date.now()).toString(36);
};

/**
 * 获取分页参数
 * @param param 参数
 * @returns {{layout: string[]}}
 */
ns.get_page_param = function (param) {
	var obj = {
		layout: ['count', 'limit', 'prev', 'page', 'next']
	};
	if (param != undefined) {
		if (param.limit != undefined) {
			obj.limit = param.limit;
		}
	}
	return obj;
};

/**
 * 弹出框，暂时没有使用
 * @param options 参数，参考layui：https://www.layui.com/doc/modules/layer.html
 */
ns.open = function (options) {
	if (!options) options = {};

	options.type = options.type || 1;

	//宽高，小、中、大
	// options.size
	options.area = options.area || ['500px'];
	layer.open(options);
};

/**
 * 上传
 * @param id
 * @param method
 * @param param
 * @param callback
 * @param async
 */
ns.upload_api = function (id, method, param, callback, async) {
	// async true为异步请求 false为同步请求
	async = async != undefined ? async : true;
	param.app_key = APP_KEY;
	var file = document.getElementById(id).files[0];
	var formData = new FormData();
	formData.append("file", file);
	formData.append("method", method);
	formData.append("param", JSON.stringify(param));
	$.ajax({
		url: API_URL + '?s=/api/index/get/method/' + method + '/version/1.0',
		type: "post",
		data: formData,
		dataType: "JSON",
		contentType: false,
		processData: false,
		async: async,
		mimeType: "multipart/form-data",
		success: function (res) {
			if (callback) callback(eval("(" + res + ")"));
		},
		// error: function (data) {
		//     console.log(data);
		// }
	});
};

/**
 * 复制
 * @param dom
 * @param callback
 */
ns.copy = function JScopy(dom, callback) {
	var url = document.getElementById(dom);
	var o = {
		url: url.value
	};
	
	var inputText = document.createElement('input');
	inputText.value = o.url;
	document.body.appendChild(inputText);
	inputText.select();
	
	document.execCommand("Copy");
	
	if (callback) callback.call(this, o);
	inputText.type = 'hidden';
	layer.msg('复制成功');
};

ns.int_to_float = function (val) {
	return new Number(val).toFixed(2);
};

var show_link_box_flag = true;

/**
 * 弹出框-->选择链接
 * @param link
 * @param callback
 */
ns.select_link = function (link, callback) {
	if (show_link_box_flag) {
		show_link_box_flag = false;
		$.post(ns.url("shop/diy/link"), {
			link: JSON.stringify(link),
			site_id:ns_url.siteId,
			app_module: ns_url.appModule,
		}, function (str) {
			window.linkIndex = layer.open({
				type: 1,
				title: "选择链接",
				content: str,
				btn: [],
				area: ['850px'], //宽高
				maxWidth: 1920,
				cancel: function (index, layero) {
					show_link_box_flag = true;
				},
				end: function () {
					if (window.linkData) {
						if (callback) callback(window.linkData);
						delete window.linkData;// 清空本次选择
					}

					show_link_box_flag = true;

				}
			});
		});
	}
};

/**
 * 打开iframe弹框
 * @param param
 */
ns.open_iframe = function (param) {
	layui.use(['layer'], function () {
		var url = param.url || '';
		var success = param.success || null;
		var title = param.title || '弹框';
		var area = param.area || ['80%', '80%'];
		//iframe层-父子操作
		layer.open({
			title: title,
			type: 2,
			area: area,
			fixed: false, //不固定
			btn: ['保存', '返回'],
			content: url,
			yes: function (index, layero) {
				var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：

				iframeWin['getIframeRes'](function (obj) {
					if (typeof success == "string") {
						try {
							eval(success + '(obj)');
							layer.close(index);
						} catch (e) {
							console.error('回调函数' + success + '未定义');
						}
					} else if (typeof success == "function") {
						success(obj);
						layer.close(index);
					}
				});
			}
		});
	});
}

ns.compare = function (property) {
	return function (a, b) {
		var value1 = a[property];
		var value2 = b[property];
		return value1 - value2;
	}
};

//存储单元单位转换
ns.sizeformat = function (limit) {
	if (limit == null || limit == "") {
		return "0KB"
	}
	var index = 0;
	var limit = limit.toUpperCase();//转换为小写
	if (limit.indexOf('B') == -1) { //如果无单位,加单位递归转换
		limit = limit + "B";
		//unitConver(limit);
	}
	var reCat = /[0-9]*[A-Z]B/;
	if (!reCat.test(limit) && limit.indexOf('B') != -1) { //如果单位是b,转换为kb加单位递归
		limit = limit.substring(0, limit.indexOf('B')); //去除单位,转换为数字格式
		limit = (limit / 1024) + 'KB'; //换算舍入加单位
		//unitConver(limit);
	}
	var array = new Array('KB', 'MB', 'GB', 'TB', 'PT');
	for (var i = 0; i < array.length; i++) { //记录所在的位置
		if (limit.indexOf(array[i]) != -1) {
			index = i;
			break;
		}
	}
	var limit = parseFloat(limit.substring(0, (limit.length - 2))); //得到纯数字

	while (limit >= 1024) {//数字部分1到1024之间
		limit /= 1024;
		index += 1;
	}
	limit = limit.toFixed(2) + array[index];
	return limit;
};

/**
 * 对象深度拷贝
 * @param options
 * @constructor
 */
ns.deepclone = function (obj) {
	const isObject = function (obj) {
		return typeof obj == 'object';
	}

	if (!isObject(obj)) {
		throw new Error('obj 不是一个对象！')
	}
	//判断传进来的是对象还是数组
	let isArray = Array.isArray(obj)
	let cloneObj = isArray ? [] : {}
	//通过for...in来拷贝
	for (let key in obj) {
		cloneObj[key] = isObject(obj[key]) ? ns.deepclone(obj[key]) : obj[key]
	}
	return cloneObj
}

/**
 * 检测输入
 * @param dom
 * @param type
 */
ns.checkInput = function (dom) {
	let new_val = $(dom).val();
	let reg = /^(0|[1-9][0-9]*)(.\d{0,2})?$/;
	let old_val = $(dom).attr('data-value');
	if (new_val === '' || reg.test(new_val)) {
		$(dom).attr('data-value', new_val);
	} else {
		$(dom).val(old_val);
	}
}

/**
 * 数据表格
 * layui官方文档：https://www.layui.com/doc/modules/table.html
 * @param options
 * @constructor
 */
function Table(options) {
	if (!options) return;
	var _self = this;

	var fields = [];
	for (var i = 0; i < options.cols[0].length; i++) {
		if (options.cols[0][i].sort == true) {
			fields.push(options.cols[0][i].field);
		}
	}
	options.parseData = options.parseData || function (data) {
		$.each(data.data.list, function (index, item) {
			$.each(item, function (key, value) {
				if ($.inArray(key, fields) >= 0) {
					data.data.list[index][key] = Number(value);
				}
			});
		});
		return {
			"code": data.code,
			"msg": data.message,
			"count": data.data.count,
			"data": data.data.list
		};
	};

	options.request = options.request || {
		limitName: 'page_size' //每页数据量的参数名，默认：limit
	};

	var post_url = options.url || 0;
	var post_where = options.where;
	var domain = window.location.href + '/s' + (window.ns_url.siteid ? window.ns_url.siteid : 0)+post_url + JSON.stringify(post_where);

	if (options.page == undefined) {
		options.page = {
			layout: ['count', 'limit', 'prev', 'page', 'next'],
			limit: $.cookie('pageSize' + domain) || 10,
			curr: $.cookie('currPage' + domain) || 1
		};
	}

	options.defaultToolbar = options.defaultToolbar || [];//'filter', 'print', 'exports'

	options.toolbar = options.toolbar || "";//头工具栏事件

	options.skin = options.skin || 'line';
	options.size = options.size || 'lg';
	options.async = (options.async != undefined) ? options.async : true;
	options.done = function (res, curr, count) {
		//加载图片放大
		loadImgMagnify();

		$.cookie('currPage' + domain, curr, {path: window.location.pathname});
		$.cookie('pageSize' + domain, $('.layui-laypage-limits select[lay-ignore]').val(), {path: window.location.pathname});

		if (options.callback) options.callback(res, curr, count);
	};

	layui.use('table', function () {
		_self._table = layui.table;
		_self._table.render(options);
	});

	this.filter = options.filter || options.elem.replace(/#/g, "");
	this.elem = options.elem;

	//获取当前选中的数据
	this.checkStatus = function () {
		return this._table.checkStatus(_self.elem.replace(/#/g, ""));
	};
}

/**
 * 监听头工具栏事件
 * @param callback 回调
 */
Table.prototype.toolbar = function (callback) {
	var _self = this;
	var interval = setInterval(function () {
		if (_self._table) {
			_self._table.on('toolbar(' + _self.filter + ')', function (obj) {
				var checkStatus = _self._table.checkStatus(obj.config.id);
				obj.data = checkStatus.data;
				obj.isAll = checkStatus.isAll;
				if (callback) callback.call(this, obj);
			});
			clearInterval(interval);
		}
	}, 50);
};

/**
 * 监听底部工具栏事件
 * @param callback 回调
 */
Table.prototype.bottomToolbar = function (callback) {
	var _self = this;
	var interval = setInterval(function () {
		if (_self._table) {
			_self._table.on('bottomToolbar(' + _self.filter + ')', function (obj) {
				var checkStatus = _self._table.checkStatus(obj.config.id);
				obj.data = checkStatus.data;
				obj.isAll = checkStatus.isAll;
				if (callback) callback.call(this, obj);
			});
			clearInterval(interval);
		}
	}, 50);
};

/**
 * 绑定layui的on事件
 * @param name
 * @param callback
 */
Table.prototype.on = function (name, callback) {
	var _self = this;
	var interval = setInterval(function () {
		if (_self._table) {
			_self._table.on(name + '(' + _self.filter + ')', function (obj) {
				if (callback) callback.call(this, obj);
			});
			clearInterval(interval);
		}
	}, 50);
};

/**
 * 表格重绘事件
 * @param {Object} callback
 */
Table.prototype.resize = function (callback) {
	var _self = this;
	if (_self._table) {
		_self._table.resize(_self.filter);
	}
};

/**
 * //监听行工具事件
 * @param callback 回调
 */
Table.prototype.tool = function (callback) {
	var _self = this;
	var interval = setInterval(function () {
		if (_self._table) {
			_self._table.on('tool(' + _self.filter + ')', function (obj) {
				if (callback) callback.call(this, obj);
			});
			clearInterval(interval);
		}
	}, 50);
};

/**
 * 刷新数据
 * @param options 参数，参考layui数据表格参数
 * @param callback 回调
 */
Table.prototype.reload = function (options, callback) {
	var page = 1;
	if (options && options.page) {
		page = options.page.curr || 1;
	}
	options = options || {
		page: {
			curr: page
		}
	};
	var _self = this;
	var interval = setInterval(function () {
		if (_self._table) {
			_self._table.reload(_self.elem.replace(/#/g, ""), options);
			clearInterval(interval);
		}
	}, 50);
	if (callback) setTimeout(function () {
		callback()
	}, 500)
};

var layedit;


/**
 * 富文本编辑器
 * https://www.layui.com/v1/doc/modules/layedit.html
 * @param id
 * @param options 参数，参考layui
 * @param callback 监听输入回调
 * @constructor
 */
function Editor(id, options, callback) {
	options = options || {};
	this.id = id;
	var _self = this;
	layui.use(['layedit'], function () {
		layedit = layui.layedit;
		layedit.set({
			uploadImage: {
				url: ns.url("file://common/File/image")
			},
			callback: callback
		});
		_self.index = layedit.build(id, options);
	});
}

/**
 * 设置内容
 * @param content 内容
 * @param append 是否追加
 */
Editor.prototype.setContent = function (content, append) {
	var _self = this;
	var time = setInterval(function () {
		layedit.setContent(_self.index, content, append);
		clearInterval(time);
	}, 150);
};

Editor.prototype.getContent = function () {
	return layedit.getContent(this.index);
};

Editor.prototype.getText = function () {
	return layedit.getText(this.index);
};

$(function () {
	loadImgMagnify();

	/* 处理日历图片点击问题 */
	$(function () {
		$(".calendar").parents(".layui-form-item").find("input").css({
			"background": 'transparent',
			'z-index': 2,
			'position': 'relative'
		});
	});
});

//图片最大递归次数
var IMG_MAX_RECURSIVE_COUNT = 3;
var count = 0;

/**
 * 加载图片放大
 */
function loadImgMagnify() {
	setTimeout(function () {
		try {
			if (layer) {
				$("img[src!=''][layer-src]").each(function () {
					var id = getId($(this).parent());
					// console.log("id",id);
					layer.photos({
						photos: "#" + id,
						anim: 5
					});
					count = 0;
				});
			}
		} catch (e) {

		}
	}, 200);
}

function getId(o) {
	count++;
	var id = o.attr("id");
	// console.log("递归次数:", count,id);
	if (id == undefined && count < IMG_MAX_RECURSIVE_COUNT) {
		id = getId(o.parent());
	}
	if (id == undefined) {
		id = ns.gen_non_duplicate(10);
		o.attr("id", id);
	}
	return id;
}

// 返回(关闭弹窗)
function back() {
	layer.closeAll('page');
}

/**
 * 自定义分页
 * @param options
 * @constructor
 */
function Page(options) {

	if (!options) return;
	var _self = this;

	options.elem = options.elem.replace(/#/g, "");// 注意：这里不能加 # 号
	options.count = options.count || 0;// 数据总数。一般通过服务端得到
	options.limit = options.limit || 10;// 每页显示的条数。laypage将会借助 count 和 limit 计算出分页数。
	options.limits = options.limits || [10,20,30,40,50,60,70,80,90];// 每页条数的选择项。如果 layout 参数开启了 limit，则会出现每页条数的select选择框
	options.curr = options.curr || location.hash.replace('#!page=', '');// 起始页。一般用于刷新类型的跳页以及HASH跳页
	// options.hash = options.hash || 'page';// 开启location.hash，并自定义 hash 值。如果开启，在触发分页时，会自动对url追加：#!hash值={curr} 利用这个，可以在页面载入时就定位到指定页
	options.groups = options.groups || 5;// 连续出现的页码个数
	options.prev = options.prev || '<i class="layui-icon layui-icon-left"></i>';// 自定义“上一页”的内容，支持传入普通文本和HTML
	options.next = options.next || '<i class="layui-icon layui-icon-right"></i>';// 自定义“下一页”的内容，同上
	options.first = options.first || 1;// 自定义“首页”的内容，同上

	options.request = options.request || {
		limitName: 'page_size' //每页数据量的参数名，默认：limit
	};

	// 自定义排版。可选值有：count（总条目输区域）、prev（上一页区域）、page（分页区域）、next（下一页区域）、limit（条目选项区域）、refresh（页面刷新区域。注意：layui 2.3.0 新增） 、skip（快捷跳页区域）
	options.layout = options.layout || ['count', 'limit','prev', 'page', 'next'];
	options.jump = function (obj, first) {
		//首次不执行，一定要加此判断，否则初始时会无限刷新
		if (!first) {
			obj.page = obj.curr;
			if (options.callback) options.callback.call(this, obj);
		}
	};

	layui.use('laypage', function () {
		_self._page = layui.laypage;
		_self._page.render(options);
	});

}


/**
 * 表单验证
 * @value options
 * @item
 */
layui.use('form', function () {
	var form = layui.form;

	$(".layui-input").blur(function () {
		var val = $(this).val().trim();
		$(this).val(val);
	})

	$(".layui-textarea").blur(function () {
		var val = $(this).val().trim();
		$(this).val(val);
	})

	form.verify({
		required: function (value, item) {
			var str = $(item).parents(".layui-form-item").find("label").text().split("*").join("");
			str = str.substring(0, str.length - 1);

			if (value == null || value.trim() == "" || value == undefined || value == null) return str + "不能为空";
		}
	});
});


/**
 * 面板折叠
 * @value options
 * @item
 */
layui.use('element', function () {
	var element = layui.element;
	element.on('collapse(selection_panel)', function (data) {
		if (data.show) {
			$(data.title).find("i").removeClass("layui-icon-up").addClass("layui-icon-down");
		} else {
			$(data.title).find("i").removeClass("layui-icon-down").addClass("layui-icon-up");
		}
		$(data.title).find("i").text('');
	});
});

/**
 * 上传
 * layui官方文档：https://www.layui.com/doc/modules/upload.html
 * @param options
 * @constructor
 */
function Upload(options) {
	if (!options) return;
	if (!options.size) {
		options.size = ns.upload_max_filesize;
	}
	var elemChildImg = $(options.elem).children('.preview_img')
	var _self = this;
	var $parent = $(options.elem).parent();
	options.post = options.post || "shop";
	if (options.post == 'store') options.post += '://store';

	this.post = options.post;
	options.url = options.url || ns.url(options.post + "/upload/image");
	options.accept = options.accept || "images";
	options.before = function (obj) {
		// console.log("before", obj)
	};

	//预览
	if (options.auto === false && options.bindAction) {
		options.choose = function (res) {
			var elemChildImg = $(options.elem).children('.preview_img');
			var $parent = $(options.elem).parent();

			res.preview(function (index, file, result) {
				$parent.find("input[type='hidden']").val(result);
				$parent.find(".del").addClass("show");
				$parent.addClass('hover');
				if (elemChildImg.length) {
					var tempId = $(options.elem).children('.preview_img').attr('id');
					var tempHtml = "<div id='" + tempId + "' class='preview_img'><img layer-src title='点击放大图片' src=" + result + "  class='img_prev' data-prev='1' data-action-id='" + options.bindAction + "'></div>";
					$parent.children('.upload-default').html(tempHtml);
				} else {
					var tempId = $(options.elem).attr('id');
					var tempHtml = "<div id='preview_" + tempId + "' class='preview_img'><img layer-src title='点击放大图片' src=" + result + "  class='img_prev' data-prev='1' data-action-id='" + options.bindAction + "'></div>";
					$parent.children('.upload-default').html(tempHtml);
				}
			});
		}
	}

	options.done = function (res, index, upload) {
		try {
			if (res.code >= 0) {
				$parent.find("input[type='hidden']").val(res.data.pic_path);
				$parent.find(".del").addClass("show");
				$parent.addClass('hover');
				if (res.data.pic_info) {
					res.data.pic_path = res.data.pic_info.pic_path;
				}
				if (options.accept == 'images') {
					if (elemChildImg.length) {
						var tempId = $(options.elem).children('.preview_img').attr('id');
						var tempHtml = "<div id='" + tempId + "' class='preview_img'><img layer-src title='点击放大图片' src=" + ns.img(res.data.pic_path) + "  class='img_prev'></div>";
						$parent.children('.upload-default').html(tempHtml);
					} else {
						var tempId = $(options.elem).attr('id');
						var tempHtml = "<div id='preview_" + tempId + "' class='preview_img'><img layer-src title='点击放大图片' src=" + ns.img(res.data.pic_path) + "  class='img_prev'></div>";
						$parent.children('.upload-default').html(tempHtml);
					}
					// var tempHtml = "<div id='imgId' class='preview_img'><img layer-src title='点击放大图片' src=" + ns.img(res.data.pic_path) + "  class='img_prev'></div>";
					// $parent.children('.upload-default').html(tempHtml);
				}

				// $(options.elem).addClass("replace").removeClass("no-replace");
				typeof options.callback == "function" ? options.callback(res) : "";
			}
		} catch (e) {
		} finally {
			//加载图片放大
			if (options.accept == 'images') loadImgMagnify();
			// if (options.callback) options.callback(res, index, upload);
		}
		if (!options.callback) {
			return layer.msg(res.message);
		}
	};

	layui.use('upload', function () {
		_self._upload = layui.upload;
		_self._upload.render(options);
	});
	// this.elem = options.elem;
	this.parent = $parent;

	$parent.find(".js-delete").click(function () {
		var path = $parent.children('.operation').siblings("input[type='hidden']").val();
		if (!path) return;
		_self.path = path;
		$parent.children('.operation').siblings("input[type='hidden']").val("");
		$parent.removeClass("hover");
		$parent.find(".upload-default").html(`
					<div class="upload"><i class="iconfont iconshangchuan"></i>
					<p>点击上传</p></div>
				`);

		if (options.deleteCallback) options.deleteCallback();
		$(options.elem).removeClass("hover");
	});

	// 预览
	$parent.find(".js-preview").click(function (e) {
		var id = $parent.find('.preview_img').attr('id');
		$parent.find('.img_prev').click()
		return false;
	});
	// 替换
	$parent.find(".js-replace").click(function (e) {
		var id = $parent.find('.upload-default').attr('id')
		$parent.find('#' + id).click()
	});
}

// 删除物理文件
Upload.prototype.delete = function () {
	var _self = this;
	$.ajax({
		url: ns.url(_self.post + "/upload/deleteFile"),
		data: {path: _self.path},
		dataType: 'JSON',
		type: 'POST',
		success: function (res) {
			$(_self).removeClass("show");
		}
	});
};


// 关闭组件编辑模块内容
function closeBox(obj) {
	var elem = $(obj).parents(".template-edit-title").next();
	if ($(elem).hasClass("layui-hide")) {
		$(elem).removeClass("layui-hide");
		$(obj).removeClass("closed-right");
	} else {
		$(elem).addClass("layui-hide");
		$(obj).addClass("closed-right");
	}
}

/**
 * 日期时间选择
 * @param options
 * @constructor
 */
function LayDate(options, judge) {

	// judge 判断是默认时间是否是当前~下月当天，还是上月上天~当前{nextmonth 下月 ;beformonth 上月}

	if (!options) return;
	var _self = this;

	options.type = options.type || 'datetime';
	if (options.type == "datetime") {
		options.range = options.range == false ? false : true;
		options.format = options.format || "yyyy-MM-dd HH:mm:ss";

		if (options.range) {
			var myDate = new Date();
			var startData = myDate.getFullYear() + "-" + (myDate.getMonth() + 1) + "-" + myDate.getDate() + " 00:00:00",
				endData = myDate.getFullYear() + "-" + (myDate.getMonth() + 1) + "-" + myDate.getDate() + " 23:59:59",
				time = startData + " - " + endData;
			options.value = options.value || time;
		}
	} else {
		options.format = options.format || "yyyy-MM-dd";
	}

	//获取当前时间 yy-MM-dd HH:mm:ss格式
	var myDate = new Date();
	let currentDate = myDate.toLocaleString('chinese', {hour12: false}).split('/').join('-');
	let nextmonth = '';//获取下月当天时间
	let beformonth = ''; //获取下月当天时间
	let defaultTime = '';//默认时间
	let dateTime = [];
	currentDate.split(' ')[0].split('-').forEach((item, index) => {
		if (item < 10) {
			dateTime.push('0' + item)
		} else {
			dateTime.push(item)
		}
	})

	//当天时间
	currentDate = dateTime.join('-') + ' ' + currentDate.split(' ')[1]
	// 下月当天时间
	nextmonth = nextmonthTime() + ' ' + '23:59:59'
	// 上月当天时间
	beformonth = beformonthTime() + ' ' + '00:00:00'

	if (judge == 'nextmonth') {
		defaultTime = currentDate + ' - ' + nextmonth
	} else if (judge == 'beformonth') {
		defaultTime = beformonth + ' - ' + currentDate
	} else {
		defaultTime = options.value
	}

	options.min = options.min || '1900-1-1';
	options.max = options.max || options.max == 0 ? options.max : '2099-12-31';
	options.trigger = options.trigger || 'focus';
	options.position = options.position || "absolute";
	options.zIndex = options.zIndex || 66666666;
	options.value = defaultTime;

	layui.use('laydate', function () {
		_self._laydate = layui.laydate;
		_self._laydate.render(options);
	});
}

// 获取下月时间
function nextmonthTime() {
	var now = new Date();
	var year = now.getFullYear();
	var month = now.getMonth() + 1;
	var day = now.getDate();
	if (parseInt(month) < 10) {
		month = "0" + month;
	}
	if (parseInt(day) < 10) {
		day = "0" + day;
	}

	now = year + '-' + month + '-' + day;
	// // 下月信息
	let lastMonth = parseInt(month) + 1
	if (parseInt(lastMonth) > 12) {
		lastMonth = '01'
	}
	var lastSize = new Date(year, parseInt(lastMonth), 0).getDate();//下月总天数
	// //十二月份取下年一月份
	if (parseInt(month) == 12) {
		return (parseInt(year) + 1) + '-01-' + day;
	}
	if (parseInt(lastSize) < parseInt(day)) {
		return year + '-' + lastMonth + '-' + lastSize;
	} else {
		return year + '-' + lastMonth + '-' + day;
	}
}

// 获取上月时间
function beformonthTime() {
	var now = new Date();
	var year = now.getFullYear();
	var month = now.getMonth() + 1;
	var day = now.getDate();
	if (parseInt(month) < 10) {
		month = "0" + month;
	}
	if (parseInt(day) < 10) {
		day = "0" + day;
	}
	let preMonth = parseInt(month) - 1
	if (parseInt(preMonth) < 1) {
		preMonth = '12'
	}
	var preSize = new Date(year, parseInt(preMonth), 0).getDate();//上月总天数
	// 1月份取上一年的12月,年份退一年
	if (parseInt(month) == 1) {
		return (parseInt(year) - 1) + '-12-' + day;
	}
	// 获取上月天数
	if (parseInt(preSize) < parseInt(day)) {
		return year + '-' + preMonth + '-' + preSize;
	} else {
		//没有特殊情况的话,就选择个
		return year + '-' + preMonth + '-' + day;
	}
}

/**
 * 金额格式化输入
 * @param money
 */
function moneyFormat(money) {
	if (isNaN(money)) return money;
	return parseFloat(money).toFixed(2);
}

/**
 * 颜色混合
 * @param c1
 * @param c2
 * @param ratio
 * @returns {string}
 */
function colourBlend(c1, c2, ratio) {
	ratio = Math.max(Math.min(Number(ratio), 1), 0)
	let r1 = parseInt(c1.substring(1, 3), 16)
	let g1 = parseInt(c1.substring(3, 5), 16)
	let b1 = parseInt(c1.substring(5, 7), 16)
	let r2 = parseInt(c2.substring(1, 3), 16)
	let g2 = parseInt(c2.substring(3, 5), 16)
	let b2 = parseInt(c2.substring(5, 7), 16)
	let r = Math.round(r1 * (1 - ratio) + r2 * ratio)
	let g = Math.round(g1 * (1 - ratio) + g2 * ratio)
	let b = Math.round(b1 * (1 - ratio) + b2 * ratio)
	r = ('0' + (r || 0).toString(16)).slice(-2)
	g = ('0' + (g || 0).toString(16)).slice(-2)
	b = ('0' + (b || 0).toString(16)).slice(-2)
	return '#' + r + g + b
}

//判空函数
ns.checkIsNotNull = function (param) {
	if (param) {
		if (typeof (param) == 'object') {
			if (Array.isArray(param)) {
				if (param.length > 0) return true
			} else {
				if (JSON.stringify(param) != '{}') return true
			}
		} else {
			return true;
		}
	}
	return false;
}