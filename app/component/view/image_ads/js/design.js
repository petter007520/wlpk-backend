/**
 * 图片广告的图片上传
 */
var imageAdsCarouselHtml = '<div style="display:none;"></div>';

Vue.component("image-ads-carouse", {
	data: function () {
		return {
			data: this.$parent.data
		}
	},
	created: function () {
		this.$parent.data.ignore = ['textColor', 'elementBgColor', 'elementAngle'];//加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			swiperHeight: 0,
			carouselIndex: 0
		};

		var imgArr = [];
		for (let i = 0; i < this.data.list.length; i++) {
			let item = this.data.list[i];
			imgArr[i] = (item.imgWidth / item.imgHeight);
		}
		imgArr.sort(function (a, b) {
			return b - a
		});
		var swiperHeight = (370 / imgArr[0]).toFixed(2);
		this.$parent.data.tempData.swiperHeight = swiperHeight;
	},
	template: imageAdsCarouselHtml,
	watch: {
		data: {
			handler: function (val, oldVal) {
				// 计算图片高度
				var imgArr = [];
				for (let i = 0; i < this.data.list.length; i++) {
					let item = this.data.list[i];
					imgArr[i] = (item.imgWidth / item.imgHeight) || 2;
				}
				imgArr.sort(function (a, b) {
					return b - a
				});
				// 屏幕宽度
				var rootWidth = 370 - this.data.margin.both*2;
				var swiperHeight = (rootWidth / imgArr[0]).toFixed(2);
				this.$parent.data.tempData.swiperHeight = swiperHeight;
			},
			deep: true
		}
	},
	methods: {}
});

var imageAdsListHtml = '<div style="display:none;"></div>';

Vue.component("image-ads-list", {
	template: imageAdsListHtml,
	data: function () {
		return {
			data: this.$parent.data,
			list: this.$parent.data.list,
			imgAdsCarousel: [
				{
					text: "圆点",
					value: "circle",
					src: "iconyuandian",
				},
				{
					text: "直线",
					value: "line",
					src: "iconzhishiqi-yuanjiao",
				}
			]
		}
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.list.forEach(function (e, i) {
			if(!e.id ) e.id = ns.gen_non_duplicate(6);
		});
		this.$parent.data.list = this.list;

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			...this.$parent.data.tempData,
			imgAdsCarousel: this.imgAdsCarousel,
			methods: {
				deleteItem: this.deleteItem,
				addItem: this.addItem,
			}
		};

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
		});
	},
	methods: {
		verify: function (index) {
			var res = {code: true, message: ""};
			$(".draggable-element[data-index='" + index + "'] .image-ads .image-ad-list>ul>li").each(function (i) {
				if (vue.data[index].list[i].imageUrl === "") {
					res.code = false;
					res.message = "请添加图片";
					$(this).find(".error-msg").text("请添加图片").show();
					return res;
				} else {
					$(this).find(".error-msg").text("").hide();
				}
			});
			return res;
		},
		addItem: function () {
			this.list.push({
				imageUrl: "",
				imgWidth: 0,
				imgHeight: 0,
				link: {name: ""}
			});
		},
		deleteItem: function (index) {
			this.list.splice(index, 1);
		}
	}
});