/**
 * [图片导航的图片]·组件
 */
var graphicNavListHtml = '<div style="display: none;"></div>';
Vue.component("graphic-nav-list", {
	template: graphicNavListHtml,
	data: function () {
		return {
			data: this.$parent.data,
			list: this.$parent.data.list,
			modeList: [
				{
					name: "图文导航",
					value: "graphic",
					src: "icontuwendaohang3"
				},
				{
					name: "图片导航",
					value: "img",
					src: "icontudaohang"
				},
				{
					name: "文字导航",
					value: "text",
					src: "iconwendaohang"
				}
			],
			showStyleList: [
				{
					name: "固定显示",
					value: "fixed",
					src: "icongudingzhanshi"
				},
				{
					name: "单行滑动",
					value: "singleSlide",
					src: "icondanhanghuadong"
				},
				{
					name: "分页滑动",
					value: "pageSlide",
					src: "iconfenyehuadong"
				}
			],
			ornamentList: [
				{
					type: 'default',
					text: '默认',
				},
				{
					type: 'shadow',
					text: '投影',
				},
				{
					type: 'stroke',
					text: '描边',
				},
			],
			carouselList: [
				{
					name: "圆点",
					value: "circle",
					src: "iconshenglvehao"
				},
				{
					name: "直线",
					value: "straightLine",
					src: "iconshixian"
				},
				{
					name: "隐藏",
					value: "hide",
					src: "iconyincang"
				}
			],
			rowCountList: [
				{
					name: "3个",
					value: 3,
					src: "iconyihangsange"
				},
				{
					name: "4个",
					value: 4,
					src: "iconyihangsige"
				},
				{
					name: "5个",
					value: 5,
					src: "iconyihang5ge"
				}
			],
			pageCountList: [
				{
					name: "1行",
					value: 1,
					src: "iconfuzhushuxian"
				},
				{
					name: "2行",
					value: 2,
					src: "iconyihangliangge"
				}
			],
			thicknessList: [
				{name: "加粗", src: "iconbold", value: "bold"},
				{name: "常规", src: "iconbold-copy", value: "normal"}
			]
		};
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['textColor', 'elementBgColor', 'elementAngle'];//加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			...this.$parent.data.tempData,
			modeList: this.modeList,
			showStyleList: this.showStyleList,
			ornamentList: this.ornamentList,
			carouselList: this.carouselList,
			rowCountList: this.rowCountList,
			pageCountList: this.pageCountList,
			thicknessList:this.thicknessList,
			carouselIndex: 0,
			methods: {
				addNav: this.addNav
			}
		};

		this.list.forEach(function (e, i) {
			if(!e.id ) e.id = ns.gen_non_duplicate(6);
		});
		this.$parent.data.list = this.list;

		var moveBeforeIndex = 0;
		var _this = this;
		setTimeout(function () {
			var componentIndex = _this.data.index;
			$('[data-index="' + componentIndex + '"] .navigation-set-list').DDSort({
				target: 'li',
				floatStyle: {
					'border': '1px solid #ccc',
					'background-color': '#fff'
				},
				//设置可拖拽区域
				draggableArea: "icontuodong",
				down: function (index) {
					moveBeforeIndex = index;
				},
				up: function () {
					var index = $(this).index();
					var temp = _this.list[moveBeforeIndex];
					_this.list.splice(moveBeforeIndex, 1);
					_this.list.splice(index, 0, temp);
					_this.$parent.data.list = _this.list;
				}
			});
		})
	},
	watch: {
		"data.pageCount"() {
			if (this.data.showStyle == 'pageSlide')
				this.data.tempData.carouselIndex = 0
		},
		"data.rowCount"() {
			if (this.data.showStyle == 'pageSlide')
				this.data.tempData.carouselIndex = 0
		}
	},
	methods: {
		addNav() {
			this.list.push({
				"title": "",
				"icon": "",
				"imageUrl": "",
				"iconType": "img",
				"style": {
					"fontSize": "60",
					"iconBgColor": [],
					"iconBgColorDeg": 0,
					"iconBgImg": "",
					"bgRadius": 0,
					"iconColor": [
						"#000000"
					],
					"iconColorDeg": 0
				},
				"link": {
					"name": ""
				},
				"label": {
					"control": false,
					"text": "热门",
					"textColor": "#FFFFFF",
					"bgColorStart": "#F83287",
					"bgColorEnd": "#FE3423"
				},
				id: ns.gen_non_duplicate(6)
			});
		},
		verify: function (index) {
			var res = {code: true, message: ""};
			$(".draggable-element[data-index='" + index + "'] .graphic-navigation .graphic-nav-list .navigation-set-list>li").each(function (i) {
				if (vue.data[index].mode === "img") {
					$(this).find("input[name='title']").removeAttr("style");//清空输入框的样式
					//检测是否有未上传的图片
					if (vue.data[index].list[i].imageUrl === "") {
						res.code = false;
						res.message = "请选择一张图片";
						$(this).find(".error-msg").text("请选择一张图片").show();
						return res;
					} else {
						$(this).find(".error-msg").text("").hide();
					}
				} else {
					if (vue.data[index].list[i].title === "") {
						res.code = false;
						res.message = "请输入标题";
						$(this).find("input[name='title']").attr("style", "border-color:red !important;").focus();
						$(this).find(".error-msg").text("请输入标题").show();
						return res;
					} else {
						$(this).find("input[name='title']").removeAttr("style");
						$(this).find(".error-msg").text("").hide();
					}
				}
			});

			return res;
		}
	}
});

var uploadImgHtml = '<div class="img-icon-box">';
		uploadImgHtml += '<img-icon-upload :data="{data:myData.data}"></img-icon-upload>';
		uploadImgHtml += '<div class="action-box">';
			uploadImgHtml += '<div class="action" v-if="myData.data.iconType == \'icon\'" title="风格" @click="iconStyle($event)"><i class="iconfont iconpifu"></i></div>';
			uploadImgHtml += '<div class="action" v-if="myData.data.iconType == \'icon\'" title="背景" @click="selectColor(\'graphic-nav-color-\' +id)" :id="\'graphic-nav-color-\' +id"><i class="iconfont iconyanse"></i></div>';
			uploadImgHtml += '<div class="action" title="标签" @click="selectLabel()"><i class="iconfont iconbiaoqian1"></i></div>';
		uploadImgHtml += '</div>';
	uploadImgHtml += '</div>';

Vue.component("image-upload", {
	template: uploadImgHtml,
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					data: {},
					field: "",
					callback: null
				};
			}
		}
	},
	data: function () {
		return {
			myData: this.data,
			list: [],
			parent: this.$parent.data,
			id: ns.gen_non_duplicate(10),
			colorPicker:{}
		};
	},
	created: function () {
		if (this.myData.field === undefined) this.myData.field = "imageUrl";
		this.id = ns.gen_non_duplicate(10);
	},
	methods: {
		// 选择图标风格
		iconStyle(event) {
			var self = this;
			selectIconStyle({
				elem: event.currentTarget,
				icon: self.myData.data.icon,
				callback: function (data) {
					if (data) {
						self.myData.data.style = data;
					} else {
						iconStyleSet({
							style: JSON.stringify(self.myData.data.style),
							query: {
								icon: self.myData.data.icon
							}
						}, function (style) {
							self.myData.data.style = style;
						})
					}
				}
			})
		},
		/**
		 * 渲染颜色组件
		 * @param id
		 * @param color
		 * @param callback
		 */
		colorRender(id, color, callback) {
			var self = this;
			if (this.colorPicker[id]) return;
			setTimeout(function () {
				self.colorPicker[id] = Colorpicker.create({
					el: id,
					color: color,
					change: function (elem, hex) {
						callback(elem, hex)
					}
				});
				$('#' + id).click();
			}, 10)
		},
		selectColor(id) {
			let self = this;
			this.colorRender(id, '', function (elem, color) {
				if (self.myData.data.style['iconBgImg'] || self.myData.data.style['iconBgColor'].length) {
					self.myData.data.style['iconBgColor'] = [color];
				} else {
					self.myData.data.style['iconColor'] = [color];
				}
				self.$forceUpdate();
			})
		},
		/**
		 * 标签设置
		 * @param data
		 * @param callback
		 */
		labelStyle(data, callback) {
			layer.open({
				title: "标签设置",
				type: 2,
				area: ['800px', '420px'],
				fixed: false, //不固定
				btn: ['保存', '取消'],
				content: ns.url("shop/diy/selectlabel", data ? data : {}),
				yes: function (index, layero) {
					var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：
					iframeWin.getData(function (obj) {
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

		},
		selectLabel() {
			let self = this;
			this.labelStyle(self.myData.data.label, function (data) {
				self.myData.data.label = data;
				self.$forceUpdate();
			})
		}
	}
});