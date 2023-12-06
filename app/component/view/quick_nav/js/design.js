/**
 * [图片导航的图片]·组件
 */
var quickNavListHtml = '<div style="display: none;"></div>';
Vue.component("quick-nav-list", {
	template: quickNavListHtml,
	data: function () {
		return {
			data: this.$parent.data,
			list: this.$parent.data.list,
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
			ornamentList: this.ornamentList,
			methods: {
				addNav: this.addNav
			}
		};

		this.list.forEach(function (e, i) {
			if(!e.id) e.id = ns.gen_non_duplicate(6);
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
	methods: {
		addNav() {
			this.list.push({
				"title": "",
				"icon": "",
				"iconType": "img",
				"imageUrl": "",
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
				"bgColorStart": "",
				"bgColorEnd": "",
				"textColor": "#303133",
				id: ns.gen_non_duplicate(6)
			});
		},
		verify: function (index) {
			var res = {code: true, message: ""};
			$(".draggable-element[data-index='" + index + "'] .quick-navigation .quick-nav-list .navigation-set-list>li").each(function (i) {
				if (vue.data[index].list[i].title === "") {
					res.code = false;
					res.message = "请输入标题";
					$(this).find("input[name='title']").attr("style", "border-color:red !important;").focus();
					return res;
				} else {
					$(this).find("input[name='title']").removeAttr("style");
				}
			});

			return res;
		}
	}
});

var quickUploadImgHtml = '<div class="img-icon-box">';
		quickUploadImgHtml += '<img-icon-upload :data="{data:myData.data}"></img-icon-upload>';
		quickUploadImgHtml += '<div class="action-box">';
			quickUploadImgHtml += '<div class="action" v-if="myData.data.iconType == \'icon\'" title="风格" @click="iconStyle($event)"><i class="iconfont iconpifu"></i></div>';
			quickUploadImgHtml += '<div class="action" v-if="myData.data.iconType == \'icon\'" title="背景" @click="selectColor(\'quick-nav-color-\' +id)" :id="\'quick-nav-color-\' +id"><i class="iconfont iconyanse"></i></div>';
		quickUploadImgHtml += '</div>';
	quickUploadImgHtml += '</div>';

Vue.component("quick-image-upload", {
	template: quickUploadImgHtml,
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
			id: '',
			colorPicker:{}
		};
	},
	created: function () {
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
	}
});