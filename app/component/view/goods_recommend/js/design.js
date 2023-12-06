var goodsListHtml = '<div style="display:none;"></div>';

Vue.component("goods-recommend-sources", {
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
			styleList: {
				"style-1": {
					goodsNameSupport: true, // 是否支持商品名称
					saleSupport: true, // 是否支持商品销量
					lineSupport: false, // 是否支持划线价
					bgUrl: '',
					componentBgColor: '',
					elementBgColor: '#FFFFFF',
					topStyle: {
						support: true,
						icon: {
							value: "icondiy icon-system-tuijian",
							color: "#FF3D3D",
							bgColor: "",
						},
						color: "#303133",
						subColor: "#999CA7"
					},
				},
				"style-2": {
					goodsNameSupport: true, // 是否支持商品名称
					saleSupport: true, // 是否支持商品销量
					lineSupport: false, // 是否支持划线价
					bgUrl: goodsRecommendRelativePath + '/bg.png',
					componentBgColor: '#1278FE',
					elementBgColor: '#FFFFFF',
					topStyle: {
						support: true,
						icon: {
							value: "icondiy icon-system-tuijian",
							color: "#1278FE",
							bgColor: "#FFFFFF",
						},
						color: "#FFFFFF",
						subColor: "#FFFFFF"
					},
				},
				"style-3": {
					goodsNameSupport: false, // 是否支持商品名称
					saleSupport: false, // 是否支持商品销量
					lineSupport: false, // 是否支持划线价
					bgUrl: goodsRecommendRelativePath + '/img/style3_bg.png',
					componentBgColor: '',
					elementBgColor: '',
					labelStyle: {
						support: true,
						bgColor: "#FF504D",
						title: "新人专享"
					}
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
			nameLineModeList: [
				{
					text: "单行",
					value: "single"
				},
				{
					text: "多行",
					value: "multiple"
				}
			]
		}
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['textColor']; //加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		var previewList = {};
		for (var i = 1; i < 4; i++) {
			previewList["goods_id_" + ns.gen_non_duplicate(i)] = {
				goods_name: "商品名称",
				discount_price: (Math.random() * 10 * i + 10).toFixed(0), // 随机价格
				line_price: (Math.random() * 10 * i + 10 + 10).toFixed(0), // 随机价格
				sale_num: Math.floor((Math.random() * 10 * i + 10 + 10))
			};
		}

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			goodsSources: this.goodsSources,
			sortWayList: this.sortWayList,
			styleList: this.styleList,
			ornamentList: this.ornamentList,
			nameLineModeList: this.nameLineModeList,
			previewList: previewList,
			methods: {
				addGoods: this.addGoods,
				selectCategory: this.selectCategory,
				selectTemplate: this.selectTemplate,
				selectStyle: this.selectStyle
			},
		};

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
			this.$parent.data.goodsNameStyle.support = this.styleList[this.$parent.data.style].goodsNameSupport;
			this.$parent.data.goodsNameStyle.control = this.styleList[this.$parent.data.style].goodsNameSupport;

			this.$parent.data.saleStyle.support = this.styleList[this.$parent.data.style].saleSupport;
			this.$parent.data.saleStyle.control = this.styleList[this.$parent.data.style].saleSupport;

			this.$parent.data.priceStyle.lineSupport = this.styleList[this.$parent.data.style].lineSupport;
			this.$parent.data.priceStyle.lineControl = this.styleList[this.$parent.data.style].lineSupport;

			this.$parent.data.bgUrl = this.styleList[this.$parent.data.style].bgUrl;
			this.$parent.data.componentBgColor = this.styleList[this.$parent.data.style].componentBgColor;
			this.$parent.data.elementBgColor = this.styleList[this.$parent.data.style].elementBgColor;

			// 顶部标题样式
			if(this.styleList[this.$parent.data.style].topStyle) {
				this.$parent.data.topStyle.support = this.styleList[this.$parent.data.style].topStyle.support;
				this.$parent.data.topStyle.color = this.styleList[this.$parent.data.style].topStyle.color;
				this.$parent.data.topStyle.subColor = this.styleList[this.$parent.data.style].topStyle.subColor;
				this.$parent.data.topStyle.icon.value = this.styleList[this.$parent.data.style].topStyle.icon.value;
				this.$parent.data.topStyle.icon.color = this.styleList[this.$parent.data.style].topStyle.icon.color;
				this.$parent.data.topStyle.icon.bgColor = this.styleList[this.$parent.data.style].topStyle.icon.bgColor;
			}else{
				this.$parent.data.topStyle.support = false;
			}

			if(this.styleList[this.$parent.data.style].labelStyle){
				this.$parent.data.labelStyle.support = this.styleList[this.$parent.data.style].labelStyle.support;
				this.$parent.data.labelStyle.bgColor = this.styleList[this.$parent.data.style].labelStyle.bgColor;
				this.$parent.data.labelStyle.title = this.styleList[this.$parent.data.style].labelStyle.title;
			}else{
				this.$parent.data.labelStyle.support = false;
			}

		},
		selectStyle: function () {
			var self = this;
			layer.open({
				type: 1,
				title: '风格选择',
				area: ['920px', '400px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .style-list-box-goods-recommend").html(),
				success: function (layero, index) {
					$(".layui-layer-content input[name='style']").val(self.data.style);
					$(".layui-layer-content input[name='style_name']").val(self.data.styleName);
					$("body").on("click", ".layui-layer-content .style-list-con-goods-recommend .style-li-goods-recommend", function () {
						$(this).addClass("selected border-color").siblings().removeClass("selected border-color bg-color-after");
						$(".layui-layer-content input[name='style']").val($(this).attr("data_key"));
						$(".layui-layer-content input[name='style_name']").val($(this).find("span").text());
					});
				},
				yes: function (index, layero) {
					self.data.style = $(".layui-layer-content input[name='style']").val();
					self.data.styleName = $(".layui-layer-content input[name='style_name']").val();
					self.selectTemplate();
					layer.closeAll()
				}
			});
		}
	}
});