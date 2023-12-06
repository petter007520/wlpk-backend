/**
 * 空的验证组件，后续如果增加业务，则更改组件
 */
var couponSetHtml = '<div class="layui-hide"></div>';

Vue.component("coupon-set", {
	template: couponSetHtml,
	data: function () {
		return {
			data: this.$parent.data,
			goodsSources: {
				initial: {
					text: "默认",
					src: "iconshangpinfenlei"
				},
				diy: {
					text: "手动选择",
					src: "iconshoudongxuanze"
				}
			},
			couponList: [
				{
					"ifNeedBg": true,
					"couponBgColor": "",
					"couponBgUrl": "",
					"couponType": "img",
					"isName": false,
					"nameColor": "",
					"moneyColor": "#FFFFFF",
					"limitColor": "#FFFFFF",
					"btnStyle": {
						"textColor": "#FFFFFF",
						"bgColor": "",
						"text": "立即领取",
						"aroundRadius": 0,
						"isBgColor": false,
						"isAroundRadius": false,
						"maxLen": 4
					},
					"bgColor": ""
				},
				{
					"ifNeedBg": true,
					"couponBgColor": "",
					"couponBgUrl": "",
					"couponType": "img",
					"isName": false,
					"nameColor": "",
					"moneyColor": "#FF8143",
					"limitColor": "#FF8143",
					"btnStyle": {
						"textColor": "#FF8143",
						"bgColor": "",
						"text": "领取",
						"aroundRadius": 0,
						"isBgColor": false,
						"isAroundRadius": false,
						"maxLen": 2
					},
					"bgColor": ""
				},
				{
					"ifNeedBg": true,
					"couponBgColor": "#FFFFFF",
					"couponBgUrl": couponRelativePath + "/img/style3-bg-1.png",
					"couponType": "img",
					"isName": false,
					"nameColor": "",
					"moneyColor": "#FF4544",
					"limitColor": "#FF4544",
					"btnStyle": {
						"textColor": "#FFFFFF",
						"bgColor": "#FF4544",
						"text": "立即抢",
						"aroundRadius": 20,
						"isBgColor": true,
						"isAroundRadius": true,
						"maxLen": 4
					},
					"bgColor": ""
				},
				{
					"ifNeedBg": true,
					"couponBgColor": "",
					"couponBgUrl": "",
					"couponType": "img",
					"isName": false,
					"nameColor": "",
					"moneyColor": "#FFFFFF",
					"limitColor": "#FFFFFF",
					"btnStyle": {
						"textColor": "#FFFFFF",
						"bgColor": "",
						"text": "立即领取",
						"aroundRadius": 0,
						"isBgColor": false,
						"isAroundRadius": false,
						"maxLen": 4
					},
					"bgColor": ""
				},
				{
					"ifNeedBg": true,
					"couponBgColor": "",
					"couponBgUrl": "",
					"couponType": "img",
					"isName": true,
					"nameColor": "#303133",
					"moneyColor": "#FF0000",
					"limitColor": "#999999",
					"btnStyle": {
						"textColor": "#FFFFFF",
						"bgColor": "#303133",
						"text": "立即领取",
						"aroundRadius": 5,
						"isBgColor": true,
						"isAroundRadius": true,
						"maxLen": 5
					},
					"bgColor": ""
				},
				{
					"ifNeedBg": true,
					"couponBgColor": "",
					"couponBgUrl": "",
					"couponType": "img",
					"isName": true,
					"nameColor": "#303133",
					"moneyColor": "#FF0000",
					"limitColor": "#303133",
					"btnStyle": {
						"textColor": "#FFFFFF",
						"bgColor": "#303133",
						"text": "领取",
						"aroundRadius": 20,
						"isBgColor": true,
						"isAroundRadius": true,
						"maxLen": 3
					},
					"bgColor": ""
				},
				{
					"ifNeedBg": true,
					"couponBgColor": "",
					"couponBgUrl": "",
					"couponType": "img",
					"isName": true,
					"nameColor": "",
					"moneyColor": "#FD463E",
					"limitColor": "#FD463E",
					"btnStyle": {
						"textColor": "#FF3D3D",
						"bgColor": "",
						"text": "立即领取",
						"aroundRadius": 0,
						"isBgColor": false,
						"isAroundRadius": false,
						"maxLen": 4
					},
					"bgColor": ""
				}
			]
		}
	},
	created:function() {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法
		
		this.$parent.data.ignore = ['textColor', 'elementAngle', 'componentAngle', 'componentBgColor']; //加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载
		this.$parent.data.tempData = {
			goodsSources: this.goodsSources,
			methods:{
				moneyConduct: this.moneyConduct,
				addCoupon: this.addCoupon,
				delCoupon: this.delCoupon,
				selectCouponStyle: this.selectCouponStyle
			}
		}
	},
	methods: {
		// 金额处理
		moneyConduct(value){
			var arr = value.split(".");
			var str = parseInt(arr[1].split("").reverse().join("")) + '';
			str = str.split("").reverse().join("");
			if(str == 0) return arr[0];
			else return arr[0] + '.' + str;
		},
		verify : function (index) {
			var res = { code : true, message : "" };
			if (vue.data[index].sources == 'diy' && vue.data[index].couponIds.length == 0){
				res.code = false;
				res.message = "请选择优惠券";
			}
			return res;
		},
		addCoupon: function(){
			var self = this;
			self.couponSelect(function (res) {
				self.$parent.data.couponIds = [];
				self.$parent.data.previewList = [];
				for (var i=0; i<res.length; i++) {
					self.$parent.data.couponIds.push(res[i].coupon_type_id);
					self.$parent.data.previewList.push(res[i]);
				}
			}, self.$parent.data.couponIds);
		},
		delCoupon: function (index){
			var self = this;
			self.$parent.data.couponIds.splice(index, 1);
			self.$parent.data.previewList.splice(index, 1);
		},
		couponSelect: function(callback, selectId) {
			var self = this;
			layui.use(['layer'], function () {
				var url = ns.url("coupon://shop/coupon/couponselect", {select_id : selectId.toString(),app_module:ns.appModule,site_id:ns.siteId});
				//iframe层-父子操作
				layer.open({
					title: "优惠券选择",
					type: 2,
					area: ['1000px', '600px'],
					fixed: false, //不固定
					btn: ['保存', '返回'],
					content: url,
					yes: function (index, layero) {
						var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：
						iframeWin.selectCoupon(function (obj) {
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
		},
		selectCouponStyle: function() {
			var self = this;
			layer.open({
				type: 1,
				title: '风格选择',
				area:['930px','470px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .coupon-list-style").html(),
				success: function(layero, index) {
					$(".layui-layer-content input[name='style']").val(self.data.style);
					$(".layui-layer-content input[name='style_name']").val(self.data.styleName);
					$("body").on("click", ".layui-layer-content .style-list-con-coupon .style-li-coupon", function () {
						$(this).addClass("selected border-color").siblings().removeClass("selected border-color");
						$(".layui-layer-content input[name='style']").val($(this).index() + 1);
						$(".layui-layer-content input[name='style_name']").val($(this).find("span").text());
					});
				},
				yes: function (index, layero) {
					self.data.style = $(".layui-layer-content input[name='style']").val();
					self.data.styleName = $(".layui-layer-content input[name='style_name']").val();
					self.data.ifNeedBg = self.couponList[self.data.style-1].ifNeedBg;
					self.data.couponBgColor = self.couponList[self.data.style-1].couponBgColor;
					self.data.couponBgUrl = self.couponList[self.data.style-1].couponBgUrl;
					self.data.couponType = self.couponList[self.data.style-1].couponType;
					self.data.isName = self.couponList[self.data.style-1].isName;
					self.data.nameColor = self.couponList[self.data.style-1].nameColor;
					self.data.moneyColor = self.couponList[self.data.style-1].moneyColor;
					self.data.limitColor = self.couponList[self.data.style-1].limitColor;
					self.data.bgColor = self.couponList[self.data.style-1].bgColor;
					self.data.btnStyle.textColor = self.couponList[self.data.style-1].btnStyle.textColor;
					self.data.btnStyle.bgColor = self.couponList[self.data.style-1].btnStyle.bgColor;
					self.data.btnStyle.text = self.couponList[self.data.style-1].btnStyle.text;
					self.data.btnStyle.aroundRadius = self.couponList[self.data.style-1].btnStyle.aroundRadius;
					self.data.btnStyle.isBgColor = self.couponList[self.data.style-1].btnStyle.isBgColor;
					self.data.btnStyle.isAroundRadius = self.couponList[self.data.style-1].btnStyle.isAroundRadius;
					self.data.btnStyle.maxLen = self.couponList[self.data.style-1].btnStyle.maxLen;
					layer.closeAll()
				}
			});
		}
	}
});