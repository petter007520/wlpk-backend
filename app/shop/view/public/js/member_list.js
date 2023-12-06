var surplus_coupon = [], // 剩余数据
	selected_coupon = [], // 被选中数据
	selected_coupon_id = [], // 被选中id
	temp_coupon = [], //临时数据
	memberId = '';

function renderCoupon(couponIds, member_id) {

	var couponIds_arr = [];
	if (couponIds) {
		couponIds_arr = couponIds.split(",");
	}
	selected_coupon_id = couponIds_arr;
	memberId = member_id;

	$.ajax({
		type: "POST",
		data: {
			page_size: 0,
			status: 1
		},
		url: ns.url('coupon://shop/coupon/lists'),
		dataType: 'JSON',
		success: function(data) {
			surplus_coupon = get_data_by_ids(data.data.list, 'all', couponIds).all; // 得到剩余的优惠券数据
			selected_coupon = get_data_by_ids(data.data.list, 'selected', couponIds).selected;
			compile_new_data();
		}
	})
}


/**
 * 获取优惠券数据
 */
function get_data_by_ids(data, obj, ids) {
	var id_arr = [], temp = {};
	if (ids) {
		var arr = ids.split(",");

		$.each(arr, function(i, id) {
			id = parseInt(id);
			id_arr.push(id);
		})
	}

	var temp_all = [], temp_selected = [];
	$.each(data, function(index, item) {
		if (id_arr.length > 0) {
			var index = id_arr.indexOf(item.coupon_type_id);
			if (index == -1) {
				temp_all.push(item);
			} else {
				temp_selected.push(item);
			}
		} else {
			temp_all.push(item);
		}

		temp.all = temp_all;
		temp.selected = temp_selected;
	})

	return temp;
}

/**
 * 渲染列表数据
 */
function compile_new_data() {
	var surplus_html = compile_list(surplus_coupon, 'all');
	$(".coupon-modal .all-coupon .box").html(surplus_html);
	var selected_html = compile_list(selected_coupon, 'selected');
	$(".coupon-modal .selected-coupon .box").html(selected_html);
}

/**
 * 渲染数据
 */
function compile_list(temp_list, obj) {
	var html = '<ul>';

	$.each(temp_list, function(index, item) {
		var selected_html = obj == 'selected' ? 'selected' : '';
		html += '<li class="' + selected_html + '" data-selected="' + index + '" data-id="' + item.coupon_type_id + '">';
		html += '<div class="coupon-box">';

		if (item.type == 'reward') {
			html += '<div class="coupon-money">￥' + item.money + '</div>';
		} else {
			html += '<div class="coupon-money">' + item.discount + '折</div>';
		}

		html += '<div class="coupon-name">' + item.coupon_name + '</div>';

		if (item.validity_type == 0) {
			html += '<div class="coupon-time">失效日期：' + ns.time_to_date(item.end_time) + '</div>';
		} else if(item.validity_type == 1) {
			html += '<div class="coupon-time">领取后，' + item.fixed_term + '天有效</div>';
		} else {
			html += '<div class="coupon-time">长期有效</div>';
		}

		if (obj == 'selected') {
			html += `<div class="give-num">
				<span>发放数量</span>
				<input type="number" class="layui-input len-short num" value="1" max="99">
			</div>`
		}

		html += '</div>';

		if (obj == 'selected') {
			html += '<div class="coupon-delete">×</div>'
		}
		html += '</li>';
	})

	html += '</ul>';
	return html;
}

/**
 * 选中与取消数据 先改变数据 再重新渲染
 */
temp_coupon = [];
$("body").on('click', '.layui-layer .coupon-list.all-coupon ul li .coupon-box', function() {
	var li = $(this).parent();

	if ($(this).hasClass("left-selected")) {
		$(this).removeClass("left-selected");

		var index = '';
		$.each(temp_coupon, function(i, item) {
			if (item == li.attr('data-id')) {
				index = i;
			}
		})

		temp_coupon.splice(index, 1)
	} else {
		$(this).addClass("left-selected");
		temp_coupon.push(li.attr('data-id'))
	}
});

// 添加优惠券
$("body").on('click', '.layui-layer .coupon-modal .add', function() {
	var ids = [];
	ids = selected_coupon_id.concat(temp_coupon);
	temp_coupon = [];
	renderCoupon(ids.toString(), memberId);
	// compile_new_data();
});

// 删除优惠券
$("body").on('click', '.layui-layer .coupon-modal .coupon-delete', function() {
	var id = $(this).parents("li").attr("data-id");
	var ind = '';
	$.each(selected_coupon_id, function(index, item) {
		if (id == parseInt(item)) {
			ind = index;
		}
	})
	selected_coupon_id.splice(ind, 1);

	var ids = [];
	ids = selected_coupon_id;
	renderCoupon(ids.toString(), memberId);
});


/**
 * 保存操作
 */
var isRepeat = false;
$("body").on("click", ".modal-operation .save-btn", function () {
	var coupon_data = [], isReturn = false;
	$('.coupon-modal .selected-coupon .selected').each(function () {
		var num = $(this).find('.num').val();
		if (!/[\S]+/.test(num)) {
			layer.msg('请输入发放数量'); isReturn = true;
			return false;
		}
		if (num%1 != 0) {
			layer.msg('发放数量格式错误'); isReturn = true;
			return false;
		}
		if (num <= 0) {
			layer.msg('发放数量不能小于等于0'); isReturn = true;
			return false;
		}
		coupon_data.push({
			coupon_type_id: $(this).attr('data-id'),
			num: num
		})
	})
	if (isReturn || isRepeat) return;
	if (!coupon_data.length) {
		layer.msg('请选择要发放的优惠券');
		return;
	}
	isRepeat = true;
	$.ajax({
		type: "POST",
		data: {
			member_id : memberId,
			coupon_data: JSON.stringify(coupon_data)
		},
		url: ns.url('coupon://shop/coupon/send'),
		dataType: 'JSON',
		success: function(res) {
			isRepeat = false;
			layer.close(layer_coupon);
			layer.msg(res.message);
		}
	})
});
