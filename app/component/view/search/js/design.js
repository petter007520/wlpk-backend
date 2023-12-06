/**
 * 商品搜索·组件
 */
var searchHtml = '<div style="display:none;"></div>';

Vue.component("search-resource", {
	template: searchHtml,
	data: function () {
		return {
			data: this.$parent.data,
			styleList: [
				{
					label: "样式1",
					value: 1,
					icon_img: "iconsousuo11",
				},
				{
					label: "样式2",
					value: 2,
					icon_img: "iconsousuo1",
				},
				{
					label: "样式3",
					value: 3,
					icon_img: "iconsousuo12",
				}
			],
			borderList: [
				{
					label: "方形",
					value: 1,
					icon_img: "icongl-square",
				},
				{
					label: "圆形",
					value: 2,
					icon_img: "iconyuanjiao",
				},
			],
			textAlignList: [
				{
					label: "居左",
					value: "left",
					icon_img: "iconzuoduiqi"
				},
				{
					label: "居中",
					value: "center",
					icon_img: "iconjuzhongduiqi"
				},
			],
			positionWayList: [
				{
					label: "正常显示",
					value: "static",
				},
				{
					label: "滚动至顶部固定",
					value: "fixed",
				}
			],
		};
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['elementAngle', 'componentAngle']; //加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			styleList: this.styleList,
			textAlignList: this.textAlignList,
			positionWayList: this.positionWayList,
			borderList: this.borderList,
			methods: {
				addNotice: this.addNotice,
				iconStyle: this.iconStyle
			}
		};

		this.fetchIconColor();
	},
	methods: {
		verify: function (index) {
			var res = {code: true, message: ""};
			return res;
		},
		/**
		 * 选择图标风格
		 * @param event
		 */
		iconStyle(event){
			var self = this;
			selectIconStyle({
				elem: event.currentTarget,
				icon: self.data.icon,
				callback: function (data) {
					if (data) {
						self.data.style = data;
					} else {
						iconStyleSet({
							style: JSON.stringify(self.data.style),
							query: {
								icon: self.data.icon
							}
						}, function(style){
							self.data.style = style;
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
		colorRender(id, color, callback){
			setTimeout(function () {
				Colorpicker.create({
					el: id,
					color: color,
					change: function (elem, hex) {
						callback(elem, hex)
					}
				});
			})
		},
		/**
		 * 渲染图标颜色选择器
		 */
		fetchIconColor(){
			var self = this;
			self.colorRender('search-color-' + self.data.index, '', function (elem, color) {
				if (self.data.style.iconBgColor.length || self.data.style.iconBgImg) {
					self.data.style.iconBgColor = [color];
				} else {
					self.data.style.iconColor = [color];
				}
				self.$forceUpdate();
			});
		},
	}
});