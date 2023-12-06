var styleHtml = '<div class="layui-form-item">';
		styleHtml += '<label class="layui-form-label sm">风格选择</label>';
		styleHtml += '<div class="layui-input-block">';
			styleHtml += '<div v-if="data.styleName" class="text-color selected-style" @click="selectStyle()">{{data.styleName}} <i class="layui-icon layui-icon-right"></i></div>';
		styleHtml += '</div>';
	styleHtml += '</div>';

Vue.component("text-style", {
	template: styleHtml,
	data: function () {
		return {
			data: this.$parent.data,
			styleList: {
				"style-1": {
					"text": "标题栏",
					"subTitle": {"isElementShow": false},
					"more": {"isElementShow": false}
				},
				"style-2": {
					"text": "标题栏",
					"subTitle": {"isElementShow": false},
					"more": {"isElementShow": false}
				},
				"style-3": {
					"text": "标题栏",
					"subTitle": {"isElementShow": false},
					"more": {"isElementShow": false}
				},
				"style-4": {
					"text": "标题栏",
					"subTitle": {"isElementShow": false},
					"more": {"isElementShow": false}
				},
				"style-5": {
					"text": "标题栏",
					"subTitle": {"isElementShow": false},
					"more": {"isElementShow": false}
				},
				"style-6": {
					"text": "标题栏",
					"subTitle": {"isElementShow": false},
					"more": {"isElementShow": false}
				},
				"style-7": {
					"text": "标题栏",
					"subTitle": {"isElementShow": false},
					"more": {"isElementShow": false}
				},
				"style-8": {
					"text": "标题栏",
					"subTitle": {"isElementShow": false},
					"more": {"isElementShow": false}
				},
				"style-9": {
					"text": "为您推荐",
					"textColor": "#3b2ce7",
					"fontSize": 16,
					"fontWeight": 'bold',
					"subTitle": {
						"fontSize": 14,
						"text": "夏日清爽出行必备",
						"isElementShow": true,
						"color": "#b7bcd2"
					},
					"more": {"isElementShow": false}
				},
				"style-10": {
					"text": "为您推荐",
					"textColor": "#FF95AC",
					"fontSize": 16,
					"fontWeight": 'bold',
					"subTitle": {
						"fontSize": 14,
						"text": "夏日清爽出行必备",
						"isElementShow": true,
						"color": "#B7BCD2"
					},
					"more": {"isElementShow": false}
				},
				"style-11": {
					"text": "为您推荐",
					"textColor": "#FF3B3B",
					"fontSize": 16,
					"fontWeight": 'bold',
					"subTitle": {
						"fontSize": 14,
						"text": "夏日清爽出行必备",
						"isElementShow": true,
						"color": "#FFB2B2"
					},
					"more": {
						"text": "查看更多",
						"link": {"name": ""},
						"isShow": true,
						"isElementShow": true,
						"color": "#999999"
					}
				},
				"style-12": {
					"text": "标题栏",
					"textColor": "#303133",
					"fontSize": 16,
					"fontWeight": 'bold',
					"subTitle": {
						"fontSize": 14,
						"text": "副标题",
						"isElementShow": true,
						"color": "#999999"
					},
					"more": {
						"text": "更多",
						"link": {"name": ""},
						"isShow": true,
						"isElementShow": true,
						"color": "#999999"
					}
				},
				"style-13": {
					"text": "夏日纳凉精选",
					"textColor": "#FFC425",
					"fontSize": 16,
					"fontWeight": 'bold',
					"subTitle": {"isElementShow": false},
					"more": {"isElementShow": false}
				},
				"style-14": {
					"text": "标题",
					"textColor": "#9849FF",
					"fontSize": 16,
					"fontWeight": 'bold',
					"subTitle": {
						"fontSize": 14,
						"text": "TITLE ZONE",
						"isElementShow": true,
						"color": "#9849FF"
					},
					"more": {
						"text": "更多",
						"link": {"name": ""},
						"isShow": true,
						"isElementShow": true,
						"color": "#999999"
					}
				},
				"style-15": {
					"text": "标题专区",
					"textColor": "#9849FF",
					"fontSize": 16,
					"fontWeight": 'bold',
					"subTitle": {
						"fontSize": 14,
						"text": "TITLE ZONE",
						"isElementShow": true,
						"color": "#9849FF"
					},
					"more": {"isElementShow": false}
				},
				"style-16": {
					"text": "标题专区",
					"textColor": "#303133",
					"fontSize": 16,
					"fontWeight": 'bold',
					"subTitle": {
						"fontSize": 14,
						"text": "副标题",
						"isElementShow": true,
						"color": "#FFFFFF",
						"bgColor": "#FC0F85",
						"icon": "icondiy icon-system-bargain-one",
						"fontWeight": 'bold',

					},
					"more": {
						"text": "更多",
						"link": {"name": ""},
						"isShow": true,
						"isElementShow": true,
						"color": "#999999"
					}
				}
			},
			list: {},
		}
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['elementAngle', 'elementBgColor']; //加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		// this.$parent.data.hidden = ['textColor'];// 隐藏公共属性编辑

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			styleList: this.styleList
		};
	},
	methods: {
		verify: function (index) {
			var res = {code: true, message: ""};
			return res;
		},
		selectStyle: function () {
			var self = this;
			layer.open({
				type: 1,
				title: '风格选择',
				area: ['930px', '630px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .style-list-box-text").html(),
				success: function (layero, index) {
					$(".layui-layer-content input[name='style']").val(self.data.style);
					$(".layui-layer-content input[name='style_name']").val(self.data.styleName);
					$("body").on("click", ".layui-layer-content .style-list-con-text .style-li-text", function () {
						$(this).addClass("selected border-color").siblings().removeClass("selected border-color bg-color-after");
						$(".layui-layer-content input[name='style']").val($(this).attr("data_key"));
						$(".layui-layer-content input[name='style_name']").val($(this).find("span").text());
					});
				},
				yes: function (index, layero) {
					self.data.style = $(".layui-layer-content input[name='style']").val();
					Object.assign(self.data, self.styleList[self.data.style]);
					self.data.styleName = $(".layui-layer-content input[name='style_name']").val();
					layer.closeAll()
				}
			});
		}
	}
});

// 主标题文字粗细
var fontWeightHtml = '<div class="layui-form-item icon-radio">';
	fontWeightHtml +=	 '<label class="layui-form-label sm">文字粗细</label>';
	fontWeightHtml +=	 '<div class="layui-input-block">';
	fontWeightHtml +=		 '<template v-for="(item, index) in thicknessList">';
	fontWeightHtml +=			 '<span :class="[item.value == data.fontWeight ? \'\' : \'layui-hide\']">{{item.name}}</span>';
	fontWeightHtml +=		 '</template>';
	fontWeightHtml +=		 '<ul class="icon-wrap">';
	fontWeightHtml +=			 '<li v-for="(item, index) in thicknessList" :class="[item.value == data.fontWeight ? \'text-color border-color\' : \'\']" @click="change(item)">';
	fontWeightHtml +=				 '<i class="iconfont" :class="[{\'text-color\': item.value == data.fontWeight}, item.src]"></i>';
	fontWeightHtml +=			 '</li>';
	fontWeightHtml +=		 '</ul>';
	fontWeightHtml +=	 '</div>';
	fontWeightHtml += '</div>';

Vue.component("text-font-weight", {
	props: {
		weightData: {
			type: Object,
			default: function () {
				return {};
			}
		}
	},
	template: fontWeightHtml,
	data: function () {
		return {
			data: this.$parent.data,
			thicknessList: [
				{name: "加粗", src: "iconbold", value: "bold"},
				{name: "常规", src: "iconbold-copy", value: "normal"}
			]
		};
	},
	created: function () {
		if (this.weightData.parent !== undefined) this.data = this.$parent.data[this.weightData.parent];
	},
	methods: {
		change: function (item) {
			this.data.fontWeight = item.value;
		}
	},
});