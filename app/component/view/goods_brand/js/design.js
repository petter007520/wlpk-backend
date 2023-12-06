var brandHtml = '<div></div>';

Vue.component("goods-brand-sources", {
	template: brandHtml,
	data: function () {
		return {
			data: this.$parent.data,
			goodsSources: {
				initial: {
					text: "默认",
					icon: "iconmofang"
				},
				diy: {
					text: "手动选择",
					icon: "iconshoudongxuanze"
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
		};
	},
	created: function () {
		this.$parent.data.ignore = ['elementBgColor'];//加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		if(Object.keys(this.$parent.data.previewList).length == 0) {
			for (var i = 1; i < 5; i++) {
				this.$parent.data.previewList["brand_id_" + ns.gen_non_duplicate(i)] = {
					image_url: "",
				};
			}
		}

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			goodsSources: this.goodsSources,
			ornamentList: this.ornamentList,
			methods: {
				addBrand: this.addBrand,
			},
		};
	},
	methods: {
		verify: function (index) {
			var res = {code: true, message: ""};
			if (vue.data[index].sources === 'diy' && vue.data[index].brandIds.length === 0) {
				res.code = false;
				res.message = "请选择文章";
			}
			return res;
		},
		addBrand: function () {
			var self = this;
			goodsBrandSelect(function (res) {
				self.$parent.data.brandIds = res.brandIds;
				self.$parent.data.previewList = res.list;
			}, {select_id: self.$parent.data.brandIds.toString()});
		},
	}
});