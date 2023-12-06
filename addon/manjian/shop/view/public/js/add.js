var goods_list = [], selectedGoodsId = [], goods_id=[], coupon_id = [], addCoupon, currIndex;

var laytpl, table, form, laydate, repeat_flag = false, //防重复标识
	currentDate = new Date(),  //当前时间
	minDate = "";

layui.use(['form', 'laydate', 'laytpl','util'], function() {
	form = layui.form,
	laydate = layui.laydate;
	laytpl = layui.laytpl;
	util = layui.util;
	
	form.render();
	renderTable(goods_list); // 初始化表格

	currentDate.setDate(currentDate.getDate() + 30);   //当前时间+30之后的时间戳
	
	// 开始时间
	laydate.render({
	  	elem: '#start_time' ,//指定元素
	  	type: 'datetime',
		value: new Date(),
		done: function(value){
			minDate = value;
			reRender();
		}
	});
	
	//结束时间
	laydate.render({
	  	elem: '#end_time' ,//指定元素
	  	type: 'datetime',
		value: new Date(currentDate)
	});

	/**
	 * 重新渲染结束时间
	 * */
	function reRender(){
		$("#end_time").remove();
		$(".end-time").html('<input type="text" id="end_time" name="end_time" placeholder="请输入结束时间" lay-verify="required|time" class="layui-input len-mid" autocomplete="off"><i class=" iconrili iconfont calendar"></i>');
		laydate.render({
			elem: '#end_time',
			type: 'datetime',
			min: minDate
		});
	}

    //监听活动商品类型
    form.on('radio(manjian_type)', function(data){
        var value = data.value;

        if(value == 1){
			$(".goods_list").hide();
        }
        if(value == 2){
            $(".goods_list").show();
        }
    });

    form.on('radio(type)', function(data){
        var value = data.value;
        $('.level-item .type-' + value).removeClass('layui-hide').siblings('div').addClass('layui-hide');
    });

	/**
	 * 表单验证
	 */
	form.verify({
		len: function(value) {
			if (value.length > 25) {
				return "活动名称最多为25个字符!";
			}
		},
		time: function(value) {
			var now_time = (new Date()).getTime();
			var start_time = (new Date($("#start_time").val())).getTime();
			var end_time = (new Date(value)).getTime();
			if (now_time > end_time) {
				return '结束时间不能小于当前时间!'
			}
			if (start_time > end_time) {
				return '结束时间不能小于开始时间!';
			}
		},
		num: function(value) {
			if (value == '') {
				return;
			}
			if (value < 0) {
				return '数字不能小于0!';
			}
			if (value * 100 % 1 != 0) {
				return '数字最多保留两位小数!';
			}
		},
		manjian_money: function(value){
			var type = $('[name="type"]:checked').val();
			if (type == 0) {
				if (!/[\S]+/.test(value)) {
					return '请输入金额';
				}
				if (value < 0) {
					return '金额不能小于0!';
				}
			}
		},
		manjian_num: function(value){
			var type = $('[name="type"]:checked').val();
			if (type == 1) {
				if (!/[\S]+/.test(value)) {
					return '请输入数量';
				}
				if (value < 0) {
					return '数量不能小于0!';
				}
			}
		},
		coupon_num: function(value){
			if (!/[\S]+/.test(value)) {
				return '请输入优惠券赠送数量';
			}
			if (value < 1) {
				return '优惠券赠送数量不能少于一张';
			}
		},
		goods_num:function (value) {
			var manjian_type = $('[name="manjian_type"]:checked').val();
			if(manjian_type == 2){
				if(value == ''){
					return '请选择活动商品!';
				}
			}
		}
	});
	
	/**
	 * 监听提交
	 */
	form.on('submit(save)', function(data) {
		var rule = {},
			verify = true,
			type = $('[name="type"]:checked').val();
		$('.manjian-rule .level-item').each(function(i, e) {
			var limit = parseFloat($(this).find('.type-'+ type +' .layui-input').val()).toFixed(2);
			if (i > 0) {
				var prevLimit = $('.manjian-rule .level-item:eq('+ (i - 1) +') .type-'+ type +' .layui-input').val();
				if (parseFloat(prevLimit) >= parseFloat(limit)) {
					showErrMsg('优惠门槛需大于上一级优惠门槛', $(this).find('.type-'+ type +' .layui-input'));
					verify = false;
					return false;
				}
			} 
			rule[limit] = {
				limit: limit
			};
			if ($(this).find('[value="discount_money"]').is(':checked')) {
				var discount_money = $(this).find('.discount-item.discount-money .layui-input').val();
				if (!/[\S]+/.test(discount_money)) {
					showErrMsg('请输入优惠金额', $(this).find('.discount-item.discount-money .layui-input'));
					verify = false;
					return false;
				}
				discount_money = parseFloat(discount_money);
				if (discount_money <= 0) {
					showErrMsg('优惠金额不能小于等于0', $(this).find('.discount-item.discount-money .layui-input'));
					verify = false;
					return false;
				}

				if (type == 0 && discount_money > parseFloat(limit)) {
					showErrMsg('优惠金额请勿超出优惠门槛', $(this).find('.discount-item.discount-money .layui-input'));
					verify = false;
					return false;
				}
				rule[limit].discount_money = discount_money;
			}

			if ($(this).find('[value="free_shipping"]').is(':checked')) {
				rule[limit].free_shipping = 1;
			}

			if ($(this).find('[value="point"]').is(':checked')) {
				var point = $(this).find('.discount-item.point .layui-input').val();
				rule[limit].point = point;
				if (!/[\S]+/.test(point)) {
					showErrMsg('请输入赠送积分数', $(this).find('.discount-item.point .layui-input'));
					verify = false;
					return false;	
				}
				if (point <= 0) {
					showErrMsg('赠送积分数不能小于等于0', $(this).find('.discount-item.point .layui-input'));
					verify = false;
					return false;
				}
			}
			var coupon = [];
			var coupon_num = [];
			if ($(this).find('[value="coupon"]').is(':checked')) {

				$(this).find('tr[data-coupon]').each(function (i, e) {
					coupon.push($(e).attr('data-coupon'));
				});
				$(this).find(".coupon input[name='number']").each(function(j,item){
					coupon_num.push(item.value);
				});
				if (!coupon.length) {
					showErrMsg('请选择要赠送的优惠券');
					verify = false;
					return false;
				}
				rule[limit].coupon = coupon.toString();
				rule[limit].coupon_num = coupon_num.toString();
			}

			// if ($(this).find('[value="coupon"]').is(':checked')) {
			// 	var coupon = $(this).find('.coupon tr[data-coupon]').each(function (i, e) {coupon.push($(e).attr('data-coupon'));})
			//
			// 	if (coupon == undefined) {
			// 		showErrMsg('请选择要赠送的优惠券');
			// 		verify = false;
			// 		return false;
			// 	}
			// 	rule[limit].coupon = coupon;
			// }

			if (rule[limit].discount_money == undefined && rule[limit].free_shipping == undefined && rule[limit].point == undefined && rule[limit].coupon == undefined) {
				showErrMsg('请选择活动层级'+ (i + 1) +'的优惠内容');
				verify = false;
				return false;
			}
		});
		
		if (!verify) return;

		data.field.rule_json = JSON.stringify(rule);

		if(data.field.manjian_type != 1){
            if(data.field.goods_ids == ''){
                layer.msg("请选择商品");
                return;
            }
        }

     	if (repeat_flag) return;
		repeat_flag = true;
		$.ajax({
			type: 'POST',
			url: ns.url("manjian://shop/manjian/add"),
			data: data.field,
			dataType: 'JSON',
			success: function (res) {
				repeat_flag = false;
				
				if (res.code == 0) {
					layer.confirm('添加成功', {
						title:'操作提示',
						btn: ['返回列表', '继续添加'],
						closeBtn: 0,
						yes: function(){
							location.href = ns.url("manjian://shop/manjian/lists")
						},
						btn2: function() {
							location.href = ns.url("manjian://shop/manjian/add")
						}
					});
				} else if (res.code == -10077){
					var key = res.data.key;
					layer.confirm('在同一活动时间内，部分商品已参加其他的满减活动', {
						title:'活动冲突提醒',
						btn: ['取消', '查看详情'],
						closeBtn: 0,
						btn1: function(){
							location.href = ns.url("manjian://shop/manjian/add");
						},
						btn2: function() {
							location.href = ns.url("manjian://shop/manjian/conflict", {"key": key});
						}
					});
				} else {
					layer.msg(res.message);
				}
			}
		});

	});

	function showErrMsg(msg, e){
		layer.msg(msg, { icon: 5, shift: 6 });
		if (e != undefined) {
			$(e).focus();
		}
	}

    form.on('submit(coupon-search)', function(data) {

		data.field.status = 1;
		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			url: ns.url("coupon://shop/coupon/lists"),
			data: data.field,
			success: function(res) {
				repeat_flag = false;
				if (res.code == 0) {
					coupon_list=res.data;
					laytpl($("#couponListsearch").html()).render(coupon_list, function (html) {
						$(".layui-layer-content").html(html);
						if ($("tbody tr input:checked").length == $(".coupon-box tbody tr").length) {
							$("input[lay-filter='selectAll']").prop("checked", true);
						}
						form.render();
					});
				}else{
					layer.msg(res.message);
				}
			}
		})

    })

});

// 表格渲染
function renderTable(goods_list) {
	//展示已知数据
	table = new Table({
		elem: '#selected_goods_list',
		page: false,
		limit: Number.MAX_VALUE,
		cols: [
			[{
				field: 'goods_name',
				title: '商品名称',
				unresize: 'false',
				width: '50%',
				templet: function(data) {
					var html = '';
					html += `
							<div class="goods-title">
								<div class="goods-img">
									<img src="${data.goods_image ? ns.img(data.goods_image.split(",")[0],'small') : ''}" alt="">
								</div>
								<p class="multi-line-hiding goods-name">${data.goods_name}</p>
							</div>
						`;
					return html;
				}
			}, {
				field: 'price',
				title: '商品价格(元)', 
				unresize: 'false',
				align: 'right',
				width: '20%',
				templet: function(data) {
					return '￥' + data.price;
				}
			}, {
				field: 'goods_stock', 
				title: '库存', 
				unresize: 'false',
				align: 'center',
				width: '20%'
			}, {
				title: '操作',
				toolbar: '#operation',
				unresize: 'false',
				align:'right'
			}],
		],
		data: goods_list,
	});
}

// 删除选中商品
function delGoods(id) {
	var i, j;
	$.each(goods_list, function(index, item) {
		var goods_id = item.goods_id;
		
		if (id == Number(goods_id)) {
			i = index;
		}
	});
	goods_list.splice(i, 1);
	renderTable(goods_list);
	
	$.each(selectedGoodsId, function(index, item) {
		if (id == Number(item)) {
			j = index;
		}
	});
	selectedGoodsId.splice(j, 1);
	goods_id = selectedGoodsId;
	$("#goods_num").html(selectedGoodsId.length);
	$("input[name='goods_ids']").val(goods_id.toString());
}

/* 商品 */
function addGoods(){
    goodsSelect(function (res) {
		if (!res.length) return false;
		goods_id = [];
		goods_list = [];
        for(var i=0;i<res.length;i++) {
            goods_id.push(res[i].goods_id);
			goods_list.push(res[i]);
        }
		
		renderTable(goods_list);
        $("input[name='goods_ids']").val(goods_id.toString());
		selectedGoodsId = goods_id;
		$("#goods_num").html(selectedGoodsId.length)
    }, selectedGoodsId, {mode: "spu"});
	goods_list.splice(0,goods_list.length);
}

function back() {
	location.href = ns.url("manjian://shop/manjian/lists");
}

// 添加优惠层级
function addDiscountLevel(){
	var type = $('[name="type"]:checked').val();
		length = $('.discount-level .level-item').length;
		if (length == 5) {
			layer.msg('最多支持五个活动层级');
			return;
		}
	var template = `<div class="level-item">
		<div class="level-head">
			<label class="title">活动层级{{ d.length + 1 }}：</label>
			<a href="javascript:;" class="text-color" onclick="deleteLevel(this)">删除</a>
		</div>
		<div class="wrap">
			<div class="condition">
				<label class="layui-form-label"><span class="required">*</span>优惠门槛：</label>
				<div class="layui-input-block">
					<div class="type-0 {{ d.type != 0 ? 'layui-hide' : '' }}">
						<div class="layui-form-mid">满</div>
						<div class="layui-input-inline len-short">
							<input type="number" name="money" value="" lay-verify="manjian_money" placeholder="" autocomplete="off" class="layui-input len-short">
						</div>
						<div class="layui-form-mid">元</div>
					</div>
					<div class="type-1 {{ d.type != 1 ? 'layui-hide' : '' }}">
						<div class="layui-form-mid">满</div>
						<div class="layui-input-inline len-short">
							<input type="number" name="num" value="" lay-verify="manjian_num" placeholder="" autocomplete="off" class="layui-input len-short">
						</div>
						<div class="layui-form-mid">件</div>
					</div>
				</div>
			</div>
			<div class="content">
				<label class="layui-form-label"><span class="required">*</span>优惠内容：</label>
				<div class="layui-input-block">
					<div class="discount-item discount-money">
						<div>
							<input type="checkbox" name="discount_type" value="discount_money" class="input-checkbox" lay-skin="primary"><span>订单金额优惠</span>
						</div>
						<div class="discount-cont layui-hide">
							<div class="layui-form-mid">减</div>
							<div class="layui-input-inline len-short">
								<input type="number" value="" placeholder="" autocomplete="off" class="layui-input len-short">
							</div>
							<div class="layui-form-mid">元</div>
						</div>
					</div>
					<div class="discount-item">
						<div>
							<input type="checkbox" name="discount_type" value="free_shipping" class="input-checkbox" lay-skin="primary"><span>包邮</span>
						</div>
						<div class="word-aux" style="margin-left: 28px;margin-top: 0">
							<p>仅参与该活动的商品包邮，非整单包邮</p>
						</div>
					</div>
					<div class="discount-item point">
						<div>
							<input type="checkbox" name="discount_type" value="point" class="input-checkbox" lay-skin="primary"><span>送积分</span>
						</div>
						<div class="discount-cont layui-hide">
							<div class="layui-form-mid">送</div>
							<div class="layui-input-inline len-short">
								<input type="number" name="" value="" placeholder="" autocomplete="off" class="layui-input len-short">
							</div>
							<div class="layui-form-mid">积分</div>
						</div>
					</div>
					<div class="discount-item coupon">
						<div>
							<input type="checkbox" name="discount_type" value="coupon" class="input-checkbox" lay-skin="primary"><span>送优惠券</span>
						</div>
						<div class="discount-cont layui-hide">
							<div><a href="javascript:;" class="text-color select-coupon">选择优惠券</a></div>
							<div class="word-aux">
								<p>请确认优惠券数量是否充足，优惠券数量不足将导致赠送失败</p>
							</div>
							<div>
								<table class="layui-table" lay-skin="nob">
								  	<colgroup>
									    <col width="30%">
									    <col width="30%">
									    <col width="20%">
									    <col width="20%">
								  	</colgroup>
							  		<thead>
									    <tr>
									      	<th>优惠券</th>
									      	<th>优惠内容</th>
									      	<th>赠券数</th>
									      	<th style="text-align:center;">操作</th>
									    </tr> 
								  	</thead>
								  	<tbody></tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>`;
	laytpl(template).render({
		length: length,
	    type: type
  	}, function(string){
	    $('.discount-level .level-item:last').after(string);
	    form.render();
  	});
}

// 选择优惠
$('body').on('click', '.discount-item .layui-form-checkbox', function(e){
	if ($(this).prev('[name="discount_type"]').is(':checked')) {
		$(this).parents('.discount-item').find('.discount-cont').removeClass('layui-hide');
	} else {
		$(this).parents('.discount-item').find('.discount-cont').addClass('layui-hide');
	}
});

$('body').on('click', '.discount-item .select-coupon', function(e){
	currIndex = $(this).parents('.level-item').index();
	var data = {};
    data.coupon_id = coupon_id[currIndex] != undefined ? coupon_id[currIndex] : [];

    laytpl($("#couponList").html()).render(data, function(html) {
        coupon_list = layer.open({
            title: '优惠券列表',
            skin: 'layer-tips-class',
            type: 1,
            area: ['850px', '600px'],
            content: html,
        });

        if ($("tbody tr input:checked").length == $(".coupon-box tbody tr").length) {
            $("input[lay-filter='selectAll']").prop("checked", true);
        }

        form.render();
    });

    /**
     * 监听全选按钮
     */
    form.on('checkbox(selectAll)', function(data) {
        if (data.elem.checked) {
            $("tr .check-box input:checkbox").each(function(index) {
                $(this).prop("checked", true);
            });
        } else {
            $("tr .check-box input:checkbox").each(function() {
                $(this).prop("checked", false);
            });
        }
        form.render();
    });

	/**
     * 监听每一行的多选按钮
     */
    var len = $(".coupon-box tbody tr").length;
    for (var i = 0; i < len; i++) {
        form.on('checkbox(select' + i + ')', function(data) {
            if ($("tbody tr input:checked").length == len) {
                $("input[lay-filter='selectAll']").prop("checked", true);
            } else {
                $("input[lay-filter='selectAll']").prop("checked", false);
            }

            form.render();
        });
    }
});

function couponSelected() {
    if (!$("#goods tbody tr input:checked").length) {
    	layer.msg('请选择优惠券');
    	return;
    }

    layer.closeAll('page');

    coupon_id[currIndex] = [];

    $("#coupon_selected tbody").empty();

	var data = [];
    $("#goods tr input:checked").each(function(){
    	var tr = $(this).parents('tr');
    	coupon_id[currIndex].push(tr.find("#coupon_id").val());
		data.push({
			coupon_type_id: tr.find("#coupon_id").val(),
			coupon_name: tr.find(".title-content p").text(),
			at_least: tr.find('[name="at_least"]').val(),
			type: tr.find('[name="type"]').val(),
			discount: tr.find('[name="discount"]').val(),
			money: tr.find('[name="money"]').val()
		})
    });

    laytpl($("#addCoupon").html()).render(data, function(string){
		$('.level-item:eq('+ currIndex +') .discount-cont tbody').html(string);
	    layer.closeAll();
  	});
}

// 删除优惠层级
function deleteLevel(e){
	$(e).parents('.level-item').remove();
}

// 删除优惠券
function deleteCoupon(e,index){
	index = $(e).parents('.level-item').index();
	coupon_id[index].splice(index, 1);
	$(e).parents('tr').remove();
}