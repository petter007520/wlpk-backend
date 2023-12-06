/**
 * 渲染订单列表
 */
Order = function () {
};

/**
 * 设置数据集
 */
Order.prototype.setData = function (data) {
	Order.prototype.data = data;

};

/**
 * 列名数据
 */
Order.prototype.cols = [
	{
		type: 'checkbox',
		fixed: 'left',
		width: '3%',
		merge: true,
		template: function (orderitem, order) {
			var json = {}
			json.order_id = order.order_id;
			json.order_no = order.order_no;
			json.full_address = order.full_address;
			if(order.order_type == 4 && order.order_data_status == 3){
				var h = '<div class="sub-selected-checkbox" data-json='+ JSON.stringify(json) +' data-id='+ order.order_id +' disabled>';
				h += '<input type="checkbox" lay-skin="primary" lay-filter="subCheckbox" name="" disabled>';
				h += '</div>';
			}else{
				var h = '<div class="sub-selected-checkbox" data-json='+ JSON.stringify(json) +' data-id='+ order.order_id +' >';
				h += '<input type="checkbox" lay-skin="primary" lay-filter="subCheckbox" name="" >';
				h += '</div>';
			}
			return h;
		}
	},
	{
		title: '<span>商品</span>',
		width: "33%",
		className: "product-info",
		template: function (orderitem, order) {

			var h = '<div class="img-block">';
			h += '<img layer-src="' + ns.img(orderitem.sku_image,'big') + '" src="' + ns.img(orderitem.sku_image,'small') + '">';
			h += '</div>';
			h += '<div class="info">';
			h += '<a href="' + ns.url("shop/order/detail", {order_id: orderitem.order_id}) + '" target="_blank" title="' + orderitem.sku_name + '" class="multi-line-hiding text-color-sub">' + orderitem.sku_name + '</a>';
			if(orderitem.sku_no){
				h += '<span class="text-tile" title="' + orderitem.sku_no + '" >' + orderitem.sku_no + '</span>';
			}
			h += '</div>';
			return h;
		}
	},
	{
		title: "单价(元) / 数量",
		width: "9%",
		align: "right",
		className: "order-price",
		template: function (orderitem, order) {
			var h = '<div style="padding-right: 15px;">';
			h += '<div>';
			h += '<span>' + orderitem.price + '</span>';
			h += '</div>';
			h += '<div>';
			h += '<span>' + orderitem.num + '件</span>';
			h += '</div>';
			h += '</div>';
			return h;
		}
	},
	{
		title: "实付金额(元)",
		width: "9%",
		align: "right",
		className: "order-money",
		merge: true,
		template: function (orderitem, order) {
			var h = '<div style="padding-right: 15px;">';
			h += '<span>' + order.order_money + '</span>';
			h += '</div>';
			return h;
		}
	},
	{
		title: "提货门店",
		width: "15%",
		align: "left",
		className: "order-money",
		merge: true,
		template: function (orderitem, order) {
			var h = '<div>';
			h += '<span>' + order.delivery_store_name + '</span>';
			h += '</div>';
			return h;
		}
	},
	{
		title: "买家/收货人",
		width: "15%",
		align: "left",
		className: "buyers",
		merge: true,
		template: function (orderitem, order) {
			var h = '';
			h += '<p>';
			h += '<a class="text-color" target="_blank" href="' + ns.url("shop/member/editmember?member_id=") + order.member_id + '">' + order.nickname + '</a>';
			h += '</p>';

			h += '<p>';
			h += '<span style="margin-left:22px;">' + order.mobile + '</span>';
			h += '</p>';
			h += '<span class="line-hiding address_box" title="' + order.full_address + ' ' + order.address + '">' + order.full_address + " " + order.address + '</span>';
			h += '<input type="text" class="address_input" id="'+ order.order_id +'_address" value="'+ order.full_address +'-'+ order.address +',收货人：'+ order.name +',手机号：'+ order.mobile +'">'
			h += '<a style="vertical-align: top" href="javascript:ns.copy(\''+ order.order_id +'_address\');" class="iconfont iconfuzhi"></a>'
			return h;
		}
	},
	{
		title: "操作",
		align: "right",
		className: "operation",
		width:"11%",
		merge: true,
		template: function (orderitem, order) {
			var url = "shop/order/detail";
			var html = '';
			var action_json = order.order_status_action;
			var action_arr = JSON.parse(action_json);
			var action = action_arr.action;
			html += '<div class="table-btn operation-type">';
			for (var k = 0; k < action.length; k++) {
				//视频号订单不能改价
				if(order.is_video_number == 1){
					if(action[k].action != "orderAdjustMoney"){
						html += '<a class="layui-btn  text-color" href="javascript:orderAction(\'' + action[k].action + '\', ' + order.order_id + ')">' + action[k].title + '</a>';
					}
				}else{
					html += '<a class="layui-btn  text-color" href="javascript:orderAction(\'' + action[k].action + '\', ' + order.order_id + ')">' + action[k].title + '</a>';
				}

			}
			if(order.order_type == 2 && order.order_status == 2){
				html += '<a class="layui-btn" href="javascript:storeOrderTakedelivery(' + order.order_id + ')">提货</a>';
			}

			html += '</div>';
			return html;

		}
	}
];
/**
 * 渲染表头
 */
Order.prototype.header = function (hasThead) {
	var colgroup = '<colgroup>';
	var thead = '';
	if (hasThead) thead = '<thead><tr>';
	
	for (var i = 0; i < this.cols.length; i++) {
		var align = this.cols[i].align ? "text-align:" + this.cols[i].align : "";
		
		colgroup += '<col width="' + this.cols[i].width + '">';
		if (hasThead) {
			thead += '<th style="' + align + '" class="' + (this.cols[i].className || "") + '">';
			thead += '<div class="layui-table-cell">';
			if(this.cols[i].type){
				thead += '<div class="all-selected-checkbox">';
				thead += '<input type="checkbox" lay-skin="primary" lay-filter="allCheckbox" name="">';
				thead += '</div>';
			}else{
				thead +=  this.cols[i].title;
			}
			thead += '</div>';
			thead += '</th>';
		}
	}
	colgroup += '</colgroup>';
	if (hasThead) thead += '</tr></thead>';
	return colgroup + thead;
};

/**
 * 渲染内容
 */
Order.prototype.tbody = function () {

	var tbody = '<tbody>';
	for (var i = 0; i < this.data.list.length; i++) {

		var order = this.data.list[i];
		var orderitemList = order.order_goods;
		var pay_type_name = order.pay_type_name != '' ? order.pay_type_name : "";
		var buyer_ask_delivery_time = order.buyer_ask_delivery_time != '' ? order.buyer_ask_delivery_time : "";
		var order_type = order.order_type;
		if (i > 0) {
			//分割行
			tbody += '<tr class="separation-row">';
			tbody += '<td colspan="' + this.cols.length + '"></td>';
			tbody += '</tr>';
		}

		//订单项头部
		tbody += '<tr class="header-row">';
		tbody += '<td colspan="6">';
		tbody += '<span class="order-item-header" style="margin-right:10px;">订单号：' + order.order_no + '</span>';
		tbody += '<span class="order-item-header text-color more" style="margin-right:50px;" onclick="showMore(' + order.order_id + ')">更多';
		tbody += '<div class="more-operation" data-order-id="' + order.order_id + '">';
			tbody += '<span>支付流水号：' + order.out_trade_no + '</span>';
		tbody += '</div></span>';

		tbody += '<span class="order-item-header" style="margin-right:50px;">下单时间：' + ns.time_to_date(order.create_time) + '</span>';
		
		tbody += '<span class="order-item-header" style="margin-right:50px;">订单来源：'+ order.order_from_name + (order.is_video_number ? '（视频号）' : '') +'</span>';

		// tbody += '<span class="order-item-header" style="margin-right:50px;">订单类型：' + order.order_type_name + '</span>';
		if (pay_type_name) tbody += '<span class="order-item-header">支付方式：' + pay_type_name +'</span>';

		if (order_type == 2) {
			if (order.buyer_ask_delivery_time != '' && order.buyer_ask_delivery_time != 0) tbody += '<span class="order-item-header" style="margin-left:50px;">要求自提时间：' + (order.buyer_ask_delivery_time.indexOf('-') != -1 ? order.buyer_ask_delivery_time : ns.time_to_date(order.buyer_ask_delivery_time) ) + '</span>';
			else tbody += '<span class="order-item-header" style="margin-left:50px;">要求自提时间：立即自提</span>';
		}
		if (order_type == 3) {
			if (order.buyer_ask_delivery_time != '' && order.buyer_ask_delivery_time != 0) tbody += '<span class="order-item-header" style="margin-left:50px;">要求送达时间：' + (order.buyer_ask_delivery_time.indexOf('-') != -1 ? order.buyer_ask_delivery_time : ns.time_to_date(order.buyer_ask_delivery_time) ) + '</span>';
			else tbody += '<span class="order-item-header" style="margin-left:50px;">要求送达时间：立即送达</span>';
		}
		tbody += '</td>';
		tbody += '<td>';
			tbody += '<div class="table-btn order-list-top-line">';
		if (order.order_type == 1 && (order.order_status == 1 || order.order_status == 3 || order.order_status == 10)) {
			tbody += '<a class="layui-btn" href="javascript:printDeliverOrder(' + order.order_id + ');" >打印发货单</a>';
			// tbody += '<a href="'+ ns.url('shop/order/printOrder',{order_id:order.order_id}) +'" target="_blank" class="layui-btn">打印发货单</a>';
		}

        if (order.order_status == 0) {
            tbody += '<a class="layui-btn" href="javascript:offlinePay(' + order.order_id + ');">线下支付</a> ';
        }
			tbody += '<a class="layui-btn" href="' + ns.url("shop/order/detail", {order_id: order.order_id}) + '" target="_blank">详情</a>';

        if (order.order_status == -1) {
            tbody += '<a class="layui-btn" href="javascript:orderDelete(' + order.order_id + ');" >删除</a>';
        }
       		 tbody += '<a class="layui-btn" href="javascript:orderRemark(' + order.order_id + ');">备注</a> ';


			tbody += '</div>';
		tbody += '</td>';
		tbody += '</tr>';

		// tbody += '<tr class="separation-row"><td colspan="6"><hr /></td></tr>';

		var orderitemHtml = '';
		loadImgMagnify();
		for (var j = 0; j < orderitemList.length; j++) {
			var orderitem = orderitemList[j];
			orderitemHtml += '<tr class="content-row">';
			for (var k = 0; k < this.cols.length; k++) {

				if (j == 0 && this.cols[k].merge && this.cols[k].template) {

					orderitemHtml += '<td class="' + (this.cols[k].className || "") + '" align="' + (this.cols[k].align || "") + '" style="' + (this.cols[k].style || "") + '" rowspan="' + orderitemList.length + '">';
					orderitemHtml += this.cols[k].template(orderitem, order);
					orderitemHtml += '</td>';

				} else if (this.cols[k].template && !this.cols[k].merge) {

					orderitemHtml += '<td class="' + (this.cols[k].className || "") + '" align="' + (this.cols[k].align || "") + '" style="' + (this.cols[k].style || "") + '">';
					orderitemHtml += this.cols[k].template(orderitem, order);
					orderitemHtml += '</td>';

				}
			}
			orderitemHtml += '</tr>';
		}
		tbody += orderitemHtml;

		if(order.buyer_message != '') {
			//订单项底部
			tbody += '<tr class="bottom-row">';
			tbody += '<td colspan="6">';
			tbody += '<span class="order-item-header" style="margin-right:10px;">买家备注：' + order.buyer_message + '</span>';
			tbody += '</td>';
			tbody += '</tr>';
		}

		if (order.remark != '') {
			tbody += '<tr class="remark-row">';
			tbody += '<td colspan="' + this.cols.length + '">卖家备注：' + order.remark + '</td>';
			tbody += '</tr>';
		}

	}

	tbody += '</tbody>';
	return tbody;
};

/**
 * 渲染表格
 */
Order.prototype.fetch = function () {
	if (this.data.list.length > 0) {
		return '<table class="layui-table layui-form">' + this.header(true) + '</table><table class="layui-table order-list-table layui-form">' + this.header(false) + this.tbody() + '</table>';
	} else {
		return '<table class="layui-table order-list-table layui-form">' + this.header(true) + '</table>' + '<div class="order-no-data-block"><ul><li><i class="layui-icon layui-icon-tabs"></i> </li><li>暂无订单</li></ul></div>';
	}
};

function showMore(order_id) {
	$(".more-operation[data-order-id]").hide();
	$(".more-operation[data-order-id='" + order_id + "']").show();
	$("body").click(function (e) {
		if (!$(e.target).closest(".order-item-header.more").length) {
			$(".more-operation[data-order-id='" + order_id + "']").hide();
		}
	});
}
$(".layui-colla-title").on("click", function(){
    if($(".layui-colla-title>i").hasClass("layui-icon-down") === false && $(".layui-colla-title>i").hasClass("layui-icon-up") === false){
        $(".layui-colla-title .put-open").html("展开");
    }else if($(".layui-colla-title>i").hasClass("layui-icon-down") === true){
        $(".layui-colla-title .put-open").html("展开");
    }else if($(".layui-colla-title>i").hasClass("layui-icon-up") === true){
        $(".layui-colla-title .put-open").html("收起");
    }
})