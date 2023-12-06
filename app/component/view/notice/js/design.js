/**
 * 公告·组件
 */
var noticeConHtml = '<div style="display:none;"></div>';

Vue.component("notice-sources", {
	template: noticeConHtml,
	data: function () {
		return {
			data: this.$parent.data,
			list: this.$parent.data.list,
			noticeSources: {
				initial: {
					text: "默认",
					icon: "iconmofang"
				},
				diy: {
					text: "手动选择",
					icon: "iconshoudongxuanze"
				},
			},
			iconList: {
				initial: {
					text: "系统图标",
					type: 'img',
					src: "iconshangpinfenlei",
					icon: [(noticeRelativePath + "/img/notice_01.png"), (noticeRelativePath + "/img/notice_02.png"), (noticeRelativePath + "/img/notice_03.png")]
				},
				diy: {
					type: 'icon',
					text: "自定义",
					src: "iconshoudongxuanze",
				}
			},
			thicknessList: [
				{name: "加粗", src: "iconbold", value: "bold"},
				{name: "常规", src: "iconbold-copy", value: "normal"}
			]
		}
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		// 赋值初始化图片
		if (!this.data.imageUrl) this.data.imageUrl = this.iconList.initial.icon[0];

		this.$parent.data.ignore = ['elementBgColor', 'elementAngle'];//加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			noticeSources: this.noticeSources,
			iconList: this.iconList,
			thicknessList: this.thicknessList,
			methods: {
				selectNotice: this.selectNotice,
				addNotice: this.addNotice,
				iconStyle: this.iconStyle
			}
		};

		this.list.forEach(function (e, i) {
			if(!e.id ) e.id = ns.gen_non_duplicate(6);
		});
		console.log('this.list',this.list)
		this.$parent.data.list = this.list;

		var moveBeforeIndex = 0;
		var _this = this;
		setTimeout(function () {
			var componentIndex = _this.data.index;
			$('[data-index="' + componentIndex + '"] .notice-config ul').DDSort({
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
	mounted() {
		this.fetchIconColor();
	},
	watch: {
		'$parent.data.sources':function (oVal,nVal) {
			if(oVal == 'initial') return;

			var moveBeforeIndex = 0;
			var _this = this;
			setTimeout(function () {
				var componentIndex = _this.data.index;
				$('[data-index="' + componentIndex + '"] .notice-config ul').DDSort({
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
		}
	},
	methods: {
		verify: function (index) {
			var res = {code: true, message: ""};
			if (vue.data[index].list.length > 0) {
				for (var i = 0; i < vue.data[index].list.length; i++) {
					if (vue.data[index].list[i].title === "") {
						res.code = false;
						res.message = "公告内容不能为空";
						break;
					}
				}
			} else {
				res.code = false;
				res.message = "请添加一条公告";
			}
			return res;
		},
		selectNotice: function () {
			var self = this;
			self.noticeSelect(function (res) {
				self.$parent.data.noticeIds = [];
				self.$parent.data.list = [];

				for (var i = 0; i < res.length; i++) {
					self.$parent.data.noticeIds.push(res[i].id);
					self.$parent.data.list[i] = {
						title: res[i].title,
						link: {},
						id: res[i].id
					};
				}
				self.list = self.$parent.data.list;
			}, self.$parent.data.noticeIds);
		},
		addNotice:function () {
			this.list.push({
				id: ns.gen_non_duplicate(6),
				title: '公告',
				link: {name: ''}
			})
		},
		/**
		 * 选择图标风格
		 * @param event
		 */
		iconStyle(event) {
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
						}, function (style) {
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
		colorRender(id, color, callback) {
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
		fetchIconColor() {
			var self = this;
			self.colorRender('notice-color-' + self.data.index, '', function (elem, color) {
				if (self.data.style.iconBgColor.length || self.data.style.iconBgImg) {
					self.data.style.iconBgColor = [color];
				} else {
					self.data.style.iconColor = [color];
				}
				self.$forceUpdate();
			});
		},
		noticeSelect: function (callback, selectId) {
			var self = this;
			layui.use(['layer'], function () {
				var url = ns.url("shop/notice/noticeselect", {select_id: selectId.toString()});
				//iframe层-父子操作
				layer.open({
					title: "公告选择",
					type: 2,
					area: ['1000px', '600px'],
					fixed: false, //不固定
					btn: ['保存', '返回'],
					content: url,
					yes: function (index, layero) {
						var iframeWin = window[layero.find('iframe')[0]['name']];//得到iframe页的窗口对象，执行iframe页的方法：
						iframeWin.selectNotice(function (obj) {
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
	}
});