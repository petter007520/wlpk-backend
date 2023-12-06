var goodsListHtml = '<div style="display:none;"></div>';

Vue.component("goods-list-sources", {
	template: goodsListHtml,
	data: function () {
		return {
			data: this.$parent.data,
			goodsSources: {
				initial: {
					text: "默认",
					icon: "iconmofang"
				},
				category: {
					text: "商品分类",
					icon: "iconshangpinfenlei"
				},
				diy: {
					text: "手动选择",
					icon: "iconshoudongxuanze"
				},
			},
			sortWayList: [
				{
					text: "综合",
					value: "default"
				},
				{
					text: "新品",
					value: "news"
				},
				{
					text: "热销",
					value: "sales"
				},
				{
					text: "价格",
					value: "price"
				}
			],
			templateList: {
				"row1-of1": {
					text: "单列",
					icon: "iconiPhone86",
					styleList: [
						{
							text: "样式1",
							value: "style-1",
							cartSupport: true, // 是否支持购物车按钮
							saleSupport: true, // 是否支持商品销量
							lineSupport: true, // 是否支持划线价
						},
					],
				},
				"row1-of2": {
					text: "两列",
					icon: "iconyihanglianglie",
					styleList: [
						{
							text: "样式1",
							value: "style-1",
							cartSupport: false, // 是否支持购物车按钮
							saleSupport: true, // 是否支持商品销量
							lineSupport: true, // 是否支持划线价
						},
						{
							text: "样式2",
							value: "style-2",
							cartSupport: true, // 是否支持购物车按钮
							saleSupport: true, // 是否支持商品销量
							lineSupport: true, // 是否支持划线价
						},
						{
							text: "样式3",
							value: "style-3",
							cartSupport: false, // 是否支持购物车按钮
							saleSupport: true, // 是否支持商品销量
							lineSupport: false, // 是否支持划线价
						},
					],
				},
				"row1-of3": {
					text: "三列",
					icon: "iconyihangsanlie",
					styleList: [
						{
							text: "样式1",
							value: "style-1",
							cartSupport: false, // 是否支持购物车按钮
							saleSupport: false, // 是否支持商品销量
							lineSupport: true, // 是否支持划线价
						},
						{
							text: "样式2",
							value: "style-2",
							cartSupport: true, // 是否支持购物车按钮
							saleSupport: false, // 是否支持商品销量
							lineSupport: true, // 是否支持划线价
						},
					],
				},
				"horizontal-slide": {
					text: "横向滑动",
					icon: "iconshangpinliebiaohengxianghuadong",
					styleList: [
						{
							text: "样式1",
							value: "style-1",
							cartSupport: false, // 是否支持购物车按钮
							saleSupport: false, // 是否支持商品销量
							lineSupport: true, // 是否支持划线价
						},
					],
				},
				"large-mode": {
					text: "大图",
					icon: "icondanlieshangpin",
					styleList: [
						{
							text: "样式1",
							value: "style-1",
							cartSupport: true, // 是否支持购物车按钮
							saleSupport: true, // 是否支持商品销量
							lineSupport: true, // 是否支持划线价
						},
					],
				},
			},
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
			tagList: [
				{
					text: "商品标签",
					value: "label"
				},
				{
					text: "自定义",
					value: "diy"
				},
				// {
				// 	text: "隐藏",
				// 	value: "hidden"
				// },
			],
			nameLineModeList: [
				{
					text: "单行",
					value: "single"
				},
				{
					text: "多行",
					value: "multiple"
				}
			],
			goodsDuplicateId: ''
		}
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['textColor']; //加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		var previewList = {};
		for (var i = 1; i < 7; i++) {
			previewList["goods_id_" + ns.gen_non_duplicate(i)] = {
				goods_name: "商品名称",
				discount_price: (Math.random() * 10 * i + 10).toFixed(0), // 随机价格
				line_price: (Math.random() * 10 * i + 10 + 10).toFixed(0), // 随机价格
				sale_num: Math.floor((Math.random() * 10 * i + 10 + 10))
			};
		}

		this.goodsDuplicateId = ns.gen_non_duplicate(10);

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			goodsSources: this.goodsSources,
			sortWayList: this.sortWayList,
			templateList: this.templateList,
			ornamentList: this.ornamentList,
			tagList: this.tagList,
			nameLineModeList: this.nameLineModeList,
			goodsDuplicateId: this.goodsDuplicateId,
			previewList: previewList,
			methods: {
				addGoods: this.addGoods,
				selectCategory: this.selectCategory,
				selectTemplate: this.selectTemplate,
				selectTag: this.selectTag,
				iconStyle: this.iconStyle
			},
		};
		this.fetchIconColor();
	},
	methods: {
		verify: function (index) {
			var res = {code: true, message: ""};
			if (vue.data[index].sources === 'category' && vue.data[index].categoryId === 0) {
				res.code = false;
				res.message = "请选择商品分类";
			}
			return res;
		},
		addGoods: function () {
			var self = this;
			goodsSelect(function (res) {
				self.$parent.data.goodsId = res;
			}, self.$parent.data.goodsId, {mode: "spu", disabled: 0, promotion: "module", post: ns.appModule});
		},
		selectCategory() {
			var self = this;
			layui.use(['form'], function () {
				var form = layui.form;
				layer.open({
					type: 1,
					title: '选择分类',
					area: ['630px', '430px'],
					btn: ['确定', '返回'],
					content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .goods-category-layer").html(),
					success: function (layero, index) {
						$(".js-switch").click(function () {
							var category_id = $(this).attr("data-category-id");
							var level = $(this).attr("data-level");
							var open = parseInt($(this).attr("data-open").toString());

							if (open) {
								$(".goods-category-list .layui-table tr[data-category-id-" + level + "='" + category_id + "']").hide();
								$(this).text("+");
							} else {
								$(".goods-category-list .layui-table tr[data-category-id-" + level + "='" + category_id + "']").show();
								$(this).text("-");
							}
							$(this).attr("data-open", (open ? 0 : 1));
						});

						// 勾选分类
						form.on('checkbox(category_select_id)', function (data) {
							if (data.elem.checked) {
								$("input[name='category_select_id']:checked").prop("checked", false);
								$(data.elem).prop("checked", true);
								form.render();
							}
						});

						$("input[name='category_select_id']:checked").prop("checked", false);
						if (self.data.categoryId) {
							$('.layui-layer-content [data-category_select_id="' + self.data.categoryId + '"]').prop("checked", true);
						}
						form.render();
					},
					yes: function (index, layero) {
						var selected = $(".layui-layer-content input[name='category_select_id']:checked");
						if (selected.length === 0) {
							layer.msg('请选择商品分类');
							return;
						}
						self.data.categoryName = selected.parents('tr').find('.category-name').text();
						self.data.categoryId = selected.attr('data-category_select_id');
						layer.closeAll()
					}
				});
			});
		},
		selectTemplate(template, item) {
			if (template) {
				this.$parent.data.template = template;
				item = this.templateList[template].styleList[0];
			}
			this.$parent.data.style = item.value;
			this.$parent.data.btnStyle.support = item.cartSupport;
			this.$parent.data.btnStyle.control = item.cartSupport;
			this.$parent.data.saleStyle.support = item.saleSupport;
			this.$parent.data.saleStyle.control = item.saleSupport;
			this.$parent.data.priceStyle.lineSupport = item.lineSupport;
			this.$parent.data.priceStyle.lineControl = item.lineSupport;
		},
		selectTag() {
			if (this.$parent.data.tag.value === 'hidden') {
				this.$parent.data.tag.text = '商品标签';
				this.$parent.data.tag.value = 'label';
			} else {
				this.$parent.data.tag.text = '隐藏';
				this.$parent.data.tag.value = 'hidden';
			}
		},
		/**
		 * 选择图标风格
		 * @param event
		 */
		iconStyle(event) {
			var self = this;
			selectIconStyle({
				elem: event.currentTarget,
				icon: self.data.btnStyle.iconDiy.icon,
				callback: function (data) {
					if (data) {
						self.data.btnStyle.iconDiy.style = data;
					} else {
						iconStyleSet({
							style: JSON.stringify(self.data.btnStyle.iconDiy.style),
							query: {
								icon: self.data.btnStyle.iconDiy.icon
							}
						}, function (style) {
							self.data.btnStyle.iconDiy.style = style;
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
			self.colorRender('goods-list-color-' + this.goodsDuplicateId, '', function (elem, color) {
				if (self.data.btnStyle.iconDiy.style.iconBgColor.length || self.data.btnStyle.iconDiy.style.iconBgImg) {
					self.data.btnStyle.iconDiy.style.iconBgColor = [color];
				} else {
					self.data.btnStyle.iconDiy.style.iconColor = [color];
				}
				self.$forceUpdate();
			});
		},
	}
});