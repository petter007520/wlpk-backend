var styleExtendHtml = '<div style="display:none;"></div>';

Vue.component("text-extend-style", {
	template: styleExtendHtml,
	data: function () {
		return {
			data: this.$parent.data,
			list: {},
		}
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['elementAngle', 'elementBgColor']; //加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

	},
	methods: {
		verify: function (index) {
			var res = {code: true, message: ""};
			return res;
		},
	}
});