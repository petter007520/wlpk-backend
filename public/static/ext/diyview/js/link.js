var form, laytpl;
var goodsLink = ['ALL_GOODS', 'PINTUAN_GOODS', 'PINFAN_GOODS', 'GROUPBUY_GOODS', 'DISTRIBUTION_GOODS', 'BARGAIN_GOODS', 'PRESALE_GOODS'];
layui.use(['form', 'laytpl'], function () {
	form = layui.form;
	laytpl = layui.laytpl;
	setTimeout(function () {
		if (selectLink.name) {
			// 编辑赋值
			$('.link-box .link-left dd.text-color').click();
		} else if (selectLink.parent && goodsLink.indexOf(selectLink.parent) !== -1) {
			// 如果选择了商品
			$('.link-box .link-left dd[data-name="' + selectLink.parent + '"]').click();
		} else {
			// 默认选中第一个
			$('.link-box .link-left dd:eq(0)').click();
		}
	}, 100);
});

/**
 * 查询子级链接
 * @param name
 */
function getLinkInfo(name) {
	try {
		$('.link-box .link-right.js-system dl').hide();
		var linkList = $('.link-box .link-right.js-system dl[data-parent="' + name + '"]');
		linkList.show();
		if (linkList.length === 0) childLinkCallback(name); // 触发选择子级链接回调
	} catch (e) {
		console.log('childLinkCallback error', e);
	}
}

// 展开收缩自定义链接
$(".link-box .link-left dt").click(function () {
	if ($(this).hasClass("active")) {
		$(this).removeClass("active");
		$(this).parent("dl").find("dd").removeClass("layui-hide");
	} else {
		$(this).addClass("active");
		$(this).parent("dl").find("dd").addClass("layui-hide");
	}

	if ($(this).parent("dl").find("dd").length === 0) {
		$(".link-box .link-left dd,.link-box .link-left dt").removeClass("text-color");
		$(this).addClass("text-color");
	}
});

// 选择左侧父级链接
$('.link-box .link-left dd').click(function () {
	$('.link-box .link-left dd').removeClass("text-color");
	$(this).addClass("text-color");
	var name = $(this).attr('data-name');
	switch (name) {
		case 'CUSTOM_LINK':
			// 自定义链接，支持外链
			var data = JSON.parse(JSON.stringify(selectLink));
			if(data.parent !=='CUSTOM_LINK') {
				data.title = '';
				data.wap_url = '';
			}
			laytpl($('#customHtml').html()).render(data, function (html) {
				$(".link-right.js-system").hide();
				$(".link-right.js-extend").html(html).show();
			});
			break;
		case 'OTHER_APPLET':
			// 跳转小程序
			laytpl($('#appletHtml').html()).render(selectLink, function (html) {
				$(".link-right.js-system").hide();
				$(".link-right.js-extend").html(html).show();
			});
			break;
		case 'MOBILE':
			// 拨打手机号
			laytpl($('#mobileHtml').html()).render(selectLink, function (html) {
				$(".link-right.js-system").hide();
				$(".link-right.js-extend").html(html).show();
			});
			break;
		default:
			$(".link-right.js-extend").hide();
			$(".link-right.js-system").show();
			getLinkInfo(name);
			break;
	}

});

$("body").on("click", ".link-box .link-right dd", function () {
	$(".link-box .link-right dd").removeClass("border-color text-color");
	$(this).addClass("border-color text-color");
});

//清空
$(".link-btn .link-eliminate").click(function () {
	window.linkData = {};
	layer.close(window.linkIndex);
});

// 取消
$(".link-btn .link-cancel").click(function () {
	layer.close(window.linkIndex);
});

// 保存
$(".link-box .link-save").click(function () {
	var value = {};
	var dd = $(".link-box .link-right dd.border-color"); // 子级链接
	var parentLink = $('.link-box .link-left dd.text-color'); // 父级链接

	// 标准链接
	if (dd.length) {
		value = {
			name: dd.attr('data-name'),
			title: dd.text(),
			wap_url: dd.attr('data-wap-url')
		};
	}

	// 自定义链接
	if (parentLink.attr('data-name') === 'CUSTOM_LINK') {
		var title = $(".custom-link input[name='title']").val();
		var wap_url = $(".custom-link input[name='wap_url']").val();
		if (!title) {
			layer.msg("链接名称不能为空");
			return;
		}

		if (!wap_url) {
			layer.msg("跳转路径不能为空");
			return;
		}

		value = {
			name: parentLink.attr('data-name'),
			title: title,
			wap_url: wap_url
		};
	}

	// 跳转小程序
	if (parentLink.attr('data-name') === 'OTHER_APPLET') {
		var appid = $(".other-applet input[name='appid']").val();
		var page = $(".other-applet input[name='page']").val();
		if (!appid) {
			layer.msg("跳转小程序的appid不能为空");
			return;
		}
		if (!page) {
			layer.msg("小程序路径不能为空");
			return;
		}
		value = {
			name: parentLink.attr('data-name'),
			title: '小程序-' + appid,
			appid: appid,
			page: page
		};
	}

	if (parentLink.attr('data-name') === 'MOBILE') {
		var mobile = $(".call-mobile input[name='mobile']").val();
		if (!mobile) {
			layer.msg("电话号码不能为空");
			return;
		}
		value = {
			name: parentLink.attr('data-name'),
			title: '拨打电话：' + mobile,
			mobile: mobile
		};
	}

	try {
		value = beforeSaveCallback(value); // 保存前处理数据的回调
	} catch (e) {
		console.log('saveCallback error', e);
	}
	if (Object.keys(value).length) {
		value.parent = parentLink.attr('data-name');
		window.linkData = value;
	}
	layer.close(window.linkIndex);
});

/**
 * 触发选择子级链接回调
 * @param name
 */
function childLinkCallback(name) {
	if (name === 'GOODS_CATEGORY') {
		// 商品分类
		var html = `<div id="goods_category_list"></div>`;
		$(".link-right.js-extend").html(html).show();
		$(".link-right.js-system").hide();
		getGoodsCategory();
	} else if (goodsLink.indexOf(name) !== -1) {
		var html = "<div class='search'>";
		html += `<input name='search_text' class='layui-input search-input layui-input-inline len-mid' placeholder='请输入商品名称' onkeyup="if(event.keyCode === 13) getGoodsList('${name}') " />`;
		html += `<button onclick="getGoodsList('${name}')" class='layui-btn'>搜索</button>`;
		html += "</div>";
		html += `<table id="goods_list" lay-filter="goods_list"></table>`;
		$(".link-right.js-extend").html(html).show();
		$(".link-right.js-system").hide();
		getGoodsList(name);
	} else if (['CARDS_GAME', 'TURNTABLE_GAME', 'EGG_GAME'].indexOf(name) !== -1) {
		var html = `<table id="game_list" lay-filter="game_list"></table>`;
		$(".link-right.js-extend").html(html).show();
		$(".link-right.js-system").hide();
		getGameList(name);
	} else if (['DIY_FORM'].indexOf(name) !== -1) {
		var html = `<table id="diy_form_list" lay-filter="diy_form_list"></table>`;
		$(".link-right.js-extend").html(html).show();
		$(".link-right.js-system").hide();
		getDiyFormList(name);
	}
}

/**
 * 获取商品分类数据
 */
function getGoodsCategory() {
	laytpl($("#goodsCategoryHtml").html()).render([], function (html) {
		$("#goods_category_list").html(html);

		//展开收齐点击事件
		$(".js-switch").click(function () {
			var category_id = $(this).attr("data-category-id");
			var operation = $(this).attr("data-operation");
			if (operation === 'off') {
				$(".goods-category-list .layui-table tr[data-pid='" + category_id + "']").show();
				$(this).text("-").attr("data-operation", 'on');
			} else {
				$(".goods-category-list .layui-table tr[data-pid='" + category_id + "']").hide();
				$(this).text("+").attr("data-operation", 'off');
			}
		});

		var category = $("input[name='category_id']:checked");
		if (category.length) {
			var pid = category.parent().parent().attr('data-pid');
			if (pid) $(".js-switch[data-category-id='" + pid + "']").click();
		}

		// 勾选分类
		form.on('checkbox(category_id)', function (data) {
			if (data.elem.checked) {
				$("input[name='category_id']:checked").prop("checked", false);
				$(data.elem).prop("checked", true);
				form.render();
			}
		});

		form.render();
	});

}

/**
 * 获取商品列表
 * @param name
 */
function getGoodsList(name) {
	var promotion = '', goodsCols = [];
	if (name === 'ALL_GOODS') {
		promotion = 'all';
		goodsCols = [
			[
				{
					width: '8%',
					templet: function (data) {
						return `<input type="checkbox" name="goods_checkbox" value="${data.goods_id}" data-goods-name="${data.goods_name}" lay-skin="primary" lay-filter="goods_checkbox" ${data.goods_id == selectLink.goods_id ? 'checked' : ''} />`;
					}
				},
				{
					title: '商品',
					width: '52%',
					templet: '#goods_info'
				},
				{
					field: 'price',
					title: '价格',
					width: '15%'
				},
				{
					field: 'goods_stock',
					title: '库存',
					width: '15%'
				}
			]
		];
	} else if (name === "PINTUAN_GOODS") {
		promotion = 'pintuan';
		goodsCols = [
			[{
				unresize: 'false',
				width: '8%',
				templet: function (data) {
					return `<input type="checkbox" name="goods_checkbox" value="${data.pintuan_id}" data-goods-name="${data.goods_name}" lay-skin="primary" lay-filter="goods_checkbox" ${data.pintuan_id == selectLink.pintuan_id ? 'checked' : ''} />`;
				}
			}, {
				title: '拼团商品',
				unresize: 'false',
				width: '52%',
				templet: '#goods_info'
			}, {
				field: 'pintuan_price',
				title: '价格',
				unresize: 'false',
				width: '15%'
			}, {
				field: 'goods_stock',
				title: '库存',
				unresize: 'false',
				width: '15%'
			}]
		];
	} else if (name === "PINFAN_GOODS") {
		promotion = 'pinfan';
		goodsCols = [
			[{
				unresize: 'false',
				width: '8%',
				templet: function (data) {
					return `<input type="checkbox" name="goods_checkbox" value="${data.pintuan_id}" data-goods-name="${data.goods_name}" lay-skin="primary" lay-filter="goods_checkbox" ${data.pintuan_id == selectLink.pinfan_id ? 'checked' : ''} />`;
				}
			}, {
				title: '拼团返利',
				unresize: 'false',
				width: '52%',
				templet: '#goods_info'
			}, {
				field: 'pintuan_price',
				title: '价格',
				unresize: 'false',
				width: '15%'
			}, {
				field: 'goods_stock',
				title: '库存',
				unresize: 'false',
				width: '15%'
			}]
		];
	} else if (name === "GROUPBUY_GOODS") {
		promotion = 'groupbuy';
		goodsCols = [
			[{
				unresize: 'false',
				width: '8%',
				templet: function (data) {
					return `<input type="checkbox" name="goods_checkbox" value="${data.groupbuy_id}" data-goods-name="${data.goods_name}" lay-skin="primary" lay-filter="goods_checkbox" ${data.groupbuy_id == selectLink.groupbuy_id ? 'checked' : ''} />`;
				}
			}, {
				title: '团购商品',
				unresize: 'false',
				width: '52%',
				templet: '#goods_info'
			}, {
				field: 'groupbuy_price',
				title: '价格',
				unresize: 'false',
				width: '15%'
			}, {
				field: 'goods_stock',
				title: '库存',
				unresize: 'false',
				width: '15%'
			}]
		];
	} else if (name === "DISTRIBUTION_GOODS") {
		promotion = 'fenxiao';
		goodsCols = [
			[{
				unresize: 'false',
				width: '8%',
				templet: function (data) {
					return `<input type="checkbox" name="goods_checkbox" value="${data.goods_id}" data-goods-name="${data.goods_name}" lay-skin="primary" lay-filter="goods_checkbox" ${data.goods_id == selectLink.goods_id ? 'checked' : ''} />`;
				}
			}, {
				title: '分销商品',
				unresize: 'false',
				width: '52%',
				templet: '#goods_info'
			}, {
				field: 'price',
				title: '价格',
				unresize: 'false',
				width: '15%'
			}, {
				field: 'goods_stock',
				title: '库存',
				unresize: 'false',
				width: '15%'
			}]
		]
	} else if (name === "BARGAIN_GOODS") {
		promotion = 'bargain';
		goodsCols = [
			[{
				unresize: 'false',
				width: '8%',
				templet: function (data) {
					return `<input type="checkbox" name="goods_checkbox" value="${data.bargain_id}" data-goods-name="${data.goods_name}" lay-skin="primary" lay-filter="goods_checkbox" ${data.bargain_id == selectLink.bargain_id ? 'checked' : ''} />`;
				}
			}, {
				title: '砍价商品',
				unresize: 'false',
				width: '52%',
				templet: '#goods_info'
			}, {
				field: 'price',
				title: '价格',
				unresize: 'false',
				width: '15%'
			}, {
				field: 'goods_stock',
				title: '库存',
				unresize: 'false',
				width: '15%'
			}]
		]
	} else if (name === "PRESALE_GOODS") {
		promotion = 'presale';
		goodsCols = [
			[{
				unresize: 'false',
				width: '8%',
				templet: function (data) {
					return `<input type="checkbox" name="goods_checkbox" value="${data.presale_id}" data-goods-name="${data.goods_name}" lay-skin="primary" lay-filter="goods_checkbox" ${data.presale_id == selectLink.presale_id ? 'checked' : ''} />`;
				}
			}, {
				title: '预售商品',
				unresize: 'false',
				width: '45%',
				templet: '#goods_info'
			}, {
				field: 'presale_name',
				title: '活动名称',
				unresize: 'false',
				width: '30%'
			}, {
				field: 'presale_stock',
				title: '库存',
				unresize: 'false',
				width: '15%'
			}]
		]
	}

	new Table({
		elem: '#goods_list',
		url: ns.url('shop/goods/goodsselect'),
		where: {
			site_id: ns_url.siteId,
			app_module: ns_url.appModule,
			promotion: promotion,
			search_text: $("input[name='search_text']").val()
		},
		cols: goodsCols
	});

	// 选择商品
	form.on('checkbox(goods_checkbox)', function (data) {
		if (data.elem.checked) {
			$("input[name='goods_checkbox']:checked").prop("checked", false);
			$(data.elem).prop("checked", true);
			form.render();
		}
	});
}

/**
 * 获取小游戏
 * @param name
 */
function getGameList(name) {
	var addon_url = '';
	if (name === 'CARDS_GAME') {
		addon_url = ns.url('cards://shop/cards/lists', {
			status: 1,
			app_module: ns_url.appModule,
			site_id: ns_url.siteId
		});
	} else if (name === 'TURNTABLE_GAME') {
		addon_url = ns.url('turntable://shop/turntable/lists', {
			status: 1,
			app_module: ns_url.appModule,
			site_id: ns_url.siteId
		});
	} else if (name === 'EGG_GAME') {
		addon_url = ns.url('egg://shop/egg/lists', {status: 1, app_module: ns_url.appModule, site_id: ns_url.siteId});
	}
	var gameCols = [
		[
			{
				unresize: 'false',
				width: '8%',
				templet: function (data) {
					return `<input type="checkbox" name="game_checkbox" value="${data.game_id}" data-game-name="${data.game_name}" lay-skin="primary" lay-filter="game_checkbox" ${data.game_id == selectLink.game_id ? 'checked' : ''} />`;
				}
			},
			{
				field: 'game_name',
				title: '游戏名称',
				unresize: 'false',
				width: '60%',
			},
			{
				field: 'status',
				title: '游戏状态',
				unresize: 'false',
				width: '30%',
				templet: function (d) {
					var status = '';
					if (d.status == 0) {
						status = '未开始';
					} else if (d.status == 1) {
						status = '进行中';
					} else if (d.status == 2) {
						status = '已结束';
					} else if (d.status == 3) {
						status = '已关闭';
					}
					return status;
				}
			}
		]
	];
	new Table({
		elem: '#game_list',
		url: addon_url,
		cols: gameCols
	});

	// 勾选小游戏
	form.on('checkbox(game_checkbox)', function (data) {
		if (data.elem.checked) {
			$("input[name='game_checkbox']:checked").prop("checked", false);
			$(data.elem).prop("checked", true);
			form.render();
		}
	});

}

/**
 * 获取小游戏
 * @param name
 */
function getDiyFormList(name) {
	var addon_url = ns.url('form://shop/form/lists', {
		form_type: 'custom',
		is_use: 1,
		app_module: ns_url.appModule,
		site_id: ns_url.siteId
	});
	var diyFormCols = [
		[
			{
				unresize: 'false',
				width: '10%',
				templet: function (data) {
					return `<input type="checkbox" name="diy_form_checkbox" value="${data.id}" data-form-name="${data.form_name}" lay-skin="primary" lay-filter="diy_form_checkbox" ${data.id == selectLink.form_id ? 'checked' : ''} />`;
				}
			},
			{
				field: 'form_name',
				title: '表单名称',
				unresize: 'false',
				width: '80%',
			}
		]
	];
	new Table({
		elem: '#diy_form_list',
		url: addon_url,
		cols: diyFormCols
	});

	// 勾选自定义表单
	form.on('checkbox(diy_form_checkbox)', function (data) {
		if (data.elem.checked) {
			$("input[name='diy_form_checkbox']:checked").prop("checked", false);
			$(data.elem).prop("checked", true);
			form.render();
		}
	});

}

/**
 * 保存前处理数据的回调
 * @param oV 原链接
 */
function beforeSaveCallback(oV) {
	var name = $('.link-box .link-left dd.text-color').attr('data-name');
	var value = {};

	// 选择商品分类
	var category = $("input[name='category_id']:checked");
	if (category.length) {
		value.name = name;
		value.wap_url = '/pages/goods/list?category_id=' + category.val();
		value.title = category.attr('data-category-name');
		value.category_id = category.val();
	}

	// 选择商品
	var goods = $("input[name='goods_checkbox']:checked");
	if (goods.length) {
		value.name = name;
		value.title = goods.attr('data-goods-name');
		switch (name) {
			case 'BARGAIN_GOODS':
				// 砍价商品
				value.bargain_id = goods.val();
				value.wap_url = '/pages_promotion/bargain/detail?b_id=';
				break;
			case 'GROUPBUY_GOODS':
				// 团购商品
				value.groupbuy_id = goods.val();
				value.wap_url = '/pages_promotion/groupbuy/detail?groupbuy_id=';
				break;
			case 'PINTUAN_GOODS':
				// 拼团商品
				value.pintuan_id = goods.val();
				value.wap_url = '/pages_promotion/pintuan/detail?pintuan_id=';
				break;
			case 'PINFAN_GOODS':
				// 拼团返利商品
				value.pinfan_id = goods.val();
				value.wap_url = '/pages_promotion/pinfan/detail?pinfan_id=';
				break;
			case 'PRESALE_GOODS':
				// 预售商品
				value.presale_id = goods.val();
				value.wap_url = '/pages_promotion/presale/detail?id=';
				break;
			default:
				// 全部商品、分销商品
				value.goods_id = goods.val();
				value.wap_url = '/pages/goods/detail?goods_id=';
				break;
		}
		value.wap_url += goods.val();
	}

	// 选择小游戏
	var game = $("input[name='game_checkbox']:checked");
	if(game.length) {
		value.name = name;
		value.wap_url = '';
		value.title = game.attr('data-game-name');
		value.game_id = game.val();

		switch (name) {
			case 'CARDS_GAME':
				value.wap_url = '/pages_promotion/game/cards?id=';
				break;
			case 'TURNTABLE_GAME':
				value.wap_url = '/pages_promotion/game/turntable?id=';
				break;
			case 'EGG_GAME':
				value.wap_url = '/pages_promotion/game/smash_eggs?id=';
				break;
		}
		value.wap_url += game.val();

	}

	// 选择自定义表单
	var diyForm = $("input[name='diy_form_checkbox']:checked");
	if(diyForm.length) {
		value.name = name;
		value.wap_url = '/pages_tool/form/form?id=';
		value.title = diyForm.attr('data-form-name');
		value.form_id = diyForm.val();
		value.wap_url += diyForm.val();
	}

	// 如果没有选择以上链接，则还原最初链接
	if (Object.keys(value).length === 0) value = oV;

	return value;
}