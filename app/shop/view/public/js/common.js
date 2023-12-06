/**
 * 打开相册
 * display_type img-选择图片，icon-选择icon
 */
function openAlbum(callback, imgNum = 9999, is_thumb = 0, type = 'img',display_type = "img") {
	layui.use(['layer'], function () {
		layer.open({
			type: 2,
			title: '素材管理',
			area: ['950px', '600px'],
			fixed: false, //不固定
			btn: ['保存', '返回'],
			content: ns.url("shop/album/album?imgNum=" + imgNum + "&is_thumb=" + is_thumb + '&type=' + type + '&site_id=' + ns_url.siteId + '&app_module=' + ns_url.appModule + '&display_type=' + display_type),
			yes: function (index, layero) {
				var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：

				iframeWin.getCheckItem(function (obj) {
					if (typeof callback == "string") {
						try {
							eval(callback + '(obj)');
							layer.close(index);
						} catch (e) {
							console.error('回调函数' + callback + '未定义');
						}
					} else if (typeof callback == "function") {
						callback(obj);
						layer.close(index);
					}

				});
			}
		});
	});
}

/**
 * 商品选择器
 * @param callback 回调函数
 * @param selectId 已选商品id
 * @param params mode：模式(spu、sku), max_num：最大数量，min_num 最小数量, is_virtual 是否虚拟 0 1, disabled: 开启禁用已选 0 1，promotion：营销活动标识 pintuan、groupbuy、fenxiao （module 表示组件） is_disabled_goods_type: 1表示关闭商品类型筛选 0表示开启商品类型筛选  goods_type: 1 实物商品 2虚拟商品 3电子商品 不传查全部
 */
function goodsSelect(callback, selectId, params={}) {
	layui.use(['layer'], function () {
		if (selectId.length) {
			params.select_id = selectId.toString();
		}

		params.mode = params.mode ? params.mode : 'spu';
		params.disabled = params.disabled == 0 ? 0 : 1;
		params.site_id = ns_url.siteId;
		params.app_module = ns_url.appModule;
		params.is_disabled_goods_type = params.is_disabled_goods_type || 0;
		params.goods_type = params.goods_type || "";

		// if(!params.post) params.post = 'shop';

		// if (params.post == 'store') params.post += '://store';

		var url = ns.url("shop/goods/goodsselect", params);

		//iframe层-父子操作
		layer.open({
			title: "商品选择",
			type: 2,
			area: ['1000px', '720px'],
			fixed: false, //不固定
			btn: ['保存', '返回'],
			content: url,
			yes: function (index, layero) {
				var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：
				
				iframeWin.selectGoods(function (obj) {
					if (typeof callback == "string") {
						try {
							eval(callback + '(obj)');
							layer.close(index);
						} catch (e) {
							console.error('回调函数' + callback + '未定义');
						}
					} else if (typeof callback == "function") {
						callback(obj);
						layer.close(index);
					}
					
				});
			}
		});
	});
}

/**
 * 店铺笔记选择器
 * @param callback 回调函数
 * @param selectId 已选笔记id
 * @param params mode：min_num 最小数量
 */
function notesSelect(callback, selectId, params) {
	layui.use(['layer'], function () {
		if (selectId.length) {
			params.select_id = selectId.toString();
		}
		
		var url = ns.url("notes://shop/notes/notesSelect", params);
		//iframe层-父子操作
		layer.open({
			title: "店铺笔记选择",
			type: 2,
			area: ['1000px', '720px'],
			fixed: false, //不固定
			btn: ['保存', '返回'],
			content: url,
			yes: function (index, layero) {
				var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：
				
				iframeWin.selectNotes(function (obj) {
					if (typeof callback == "string") {
						try {
							eval(callback + '(obj)');
							layer.close(index);
						} catch (e) {
							console.error('回调函数' + callback + '未定义');
						}
					} else if (typeof callback == "function") {
						callback(obj);
						layer.close(index);
					}
					
				});
			}
		});
	});
}

function tipsShow() {
	var prompt_tips_box = 0;
	// 处理鼠标划上提示问题
	setTimeout(function () {
		$('body .js-prompt-top').unbind('mouseover').unbind('mouseout').unbind('mousemove');
		$('body .js-prompt-top').mouseover(function () {
			var prompt_tips_data;
			prompt_tips_data = $(this).data('tips');
			if (!prompt_tips_data) {
				prompt_tips_data = $('.js-prompt-top-' + $(this).data('tipsbox')).html();
			}
			prompt_tips_box = layer.tips(prompt_tips_data, $(this), {
				tips: [1, '#fff'],//还可配置颜色
				time: 0
			});
		}).mouseleave(function () {
			layer.close(prompt_tips_box)
		})
	}, 1000) //延迟执行为解决某些页面渲染问题
}
tipsShow();


/**
 * 图标库选择器
 * @param callback 回调函数
 * @param params icon：选中的icon
 */
function iconSelect(callback, params={}) {
	layui.use(['layer'], function () {

		//iframe层-父子操作
		layer.open({
			title: "图标选择器",
			type: 2,
			area: ['950px', '550px'],
			fixed: false, //不固定
			btn: ['保存', '返回'],
			content: ns.url("shop/diy/iconfont",{icon: params.icon, site_id: ns_url.siteId, app_module: ns_url.appModule}),
			yes: function (index, layero) {
				var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：
				
				iframeWin.selectIcon(function (obj) {
					if (typeof callback == "string") {
						try {
							eval(callback + '(obj)');
							layer.close(index);
						} catch (e) {
							console.error('回调函数' + callback + '未定义');
						}
					} else if (typeof callback == "function") {
						callback(obj);
						layer.close(index);
					}
					
				});
			}
		});
	});
}

/**
 * 图标风格设置
 * @param params
 * @param callback
 */
function iconStyleSet(params, callback) {
	if (params.style != undefined) localStorage.setItem('iconStyle', params.style);
	layer.open({
		title: "图标风格设置",
		type: 2,
		area: ['1000px', '720px'],
		fixed: false, //不固定
		btn: ['保存', '取消'],
		content: ns.url("shop/diy/iconstyleset", params.query ? params.query : {}),
		yes: function (index, layero) {
			var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：
			iframeWin.iconStyle(function (obj) {
				if (typeof callback == "string") {
					try {
						eval(callback + '(obj)');
						layer.close(index);
					} catch (e) {
						console.error('回调函数' + callback + '未定义');
					}
				} else if (typeof callback == "function") {
					callback(obj);
					layer.close(index);
				}
			});
		}
	});
}

// icon预览
$(function () {
	$('body').on('click', '.icon-box .js-preview', function () {
		var h = `<div class="icon-preview">
            <div class="icon-preview-block">
                `+ $(this).parents('.icon-box').html() +`
            </div>
        </div>`;
		$('body').append(h);
		$('.icon-preview-block .operation').remove();
		$('.icon-preview').click(function () {
			$(this).remove();
		})
	})
});

/**
 * 选择图标风格
 * @param option
 */
function selectIconStyle(option) {
	var _w = option.width ? option.width : 340,
		_h = option.height ? option.height : 200,
		_x = $(option.elem).offset().left + $(option.elem).width() - _w,
		_y = $(option.elem).offset().top + $(option.elem).height();

	option.pagex -= _w;

	window.onmessage = function(e) {
		if (e.data.event && e.data.event == 'selectIconStyle') {
			$('.select-icon-style').remove();
			typeof option.callback == 'function' && option.callback(e.data.data);
		}
	};

	var h = `
            <div class="select-icon-style">
                <div class="icon-style-wrap" style="width: `+ _w +`px;height: `+ _h +`px;left:`+ _x +`px;top:`+ _y +`px">
                    <iframe src="`+ ns.url('shop/diy/selecticonstyle', {icon: option.icon}) +`" frameborder="0"></iframe>
                </div>
            </div>
        `;
	$('body').append(h);
	// 点击任意位置关闭弹窗
	$('.select-icon-style').click(function () {
		$(this).remove();
	})
}

/**
 * 商品品牌选择器
 * @param callback 回调函数
 * @param params select_id 已选商品id
 */
function goodsBrandSelect(callback,  params={}) {
	layui.use(['layer'], function () {
		var url = ns.url("shop/goodsbrand/brandselect", params);

		//iframe层-父子操作
		layer.open({
			title: "商品品牌选择",
			type: 2,
			area: ['800px', '600px'],
			fixed: false, //不固定
			btn: ['保存', '返回'],
			content: url,
			yes: function (index, layero) {
				var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：

				iframeWin.selectGoodsBrand(function (obj) {
					if (typeof callback == "string") {
						try {
							eval(callback + '(obj)');
							layer.close(index);
						} catch (e) {
							console.error('回调函数' + callback + '未定义');
						}
					} else if (typeof callback == "function") {
						callback(obj);
						layer.close(index);
					}

				});
			}
		});
	});
}

/**
 * 文章选择器
 * @param callback 回调函数
 * @param params select_id 已选商品id
 */
function articleSelect(callback,  params={}) {
	layui.use(['layer'], function () {
		var url = ns.url("shop/article/articleselect", params);

		//iframe层-父子操作
		layer.open({
			title: "文章选择",
			type: 2,
			area: ['800px', '600px'],
			fixed: false, //不固定
			btn: ['保存', '返回'],
			content: url,
			yes: function (index, layero) {
				var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：

				iframeWin.selectArticle(function (obj) {
					if (typeof callback == "string") {
						try {
							eval(callback + '(obj)');
							layer.close(index);
						} catch (e) {
							console.error('回调函数' + callback + '未定义');
						}
					} else if (typeof callback == "function") {
						callback(obj);
						layer.close(index);
					}

				});
			}
		});
	});
}

function storeSelect(callback, params={}) {
	layui.use(['layer'], function () {

		//iframe层-父子操作
		layer.open({
			title: "选择门店",
			type: 2,
			area: ['950px', '550px'],
			fixed: false, //不固定
			btn: ['保存', '返回'],
			content: ns.url("shop/store/selectstore", params),
			yes: function (index, layero) {
				var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：

				iframeWin.selectStore(function (obj) {
					if (typeof callback == "string") {
						try {
							eval(callback + '(obj)');
							layer.close(index);
						} catch (e) {
							console.error('回调函数' + callback + '未定义');
						}
					} else if (typeof callback == "function") {
						callback(obj);
						layer.close(index);
					}

				});
			}
		});
	});
}

function showNotify(option){
	var node = {};
	if (option.icon && ['success', 'fail', 'info', 'warning'].indexOf(option.icon) != -1) node.icon =  '<div class="icon"><i class="'+ option.icon +'"></i></div>';
	if (option.title) node.title = '<div class="title">'+ option.title +'</div>';
	if (option.content) node.content = '<div class="content">'+ option.content +'</div>';

	var h = `<div class="notify-item">
		`+ (node.icon ? node.icon : '')  +`
		<span class="iconfont iconclose_light"></span>
		<div class="box">
			`+ (node.title ? node.title : '')  +`
			`+ (node.content ? node.content : '')  +`
		</div>
	</div>`;

	if ($('.notify-wrap').length) {
		$('.notify-wrap').append(h);
	} else {
		$('body').append('<div class="notify-wrap">' + h + '</div>');
	}

	let elem = $('.notify-wrap .notify-item:last-child');

	// 手动关闭
	elem.find('.iconclose_light').click(function () {
		$(this).parents('.notify-item').remove();
	})

	// 自动关闭
	let duration = option.duration != undefined ? option.duration : 4500;
	if (duration) {
		setTimeout(function () {
			elem.remove();
		}, duration)
	}
}
