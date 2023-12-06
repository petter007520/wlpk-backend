/**
 * 热区
 */
var hotAreaCarouselHtml = '<div style="display:none;"></div>';

Vue.component("hot-area-carouse", {
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
			swiperHeight: 0
		};

		if(this.data.imgWidth && this.data.imgHeight) {
			var radio = this.data.imgWidth / this.data.imgHeight;
			var swiperHeight = (370 / radio).toFixed(2);
			this.$parent.data.tempData.swiperHeight = swiperHeight;
		}
	},
	template: hotAreaCarouselHtml,
	watch: {
		data: {
			handler: function (val, oldVal) {
				// 计算图片高度
				var radio = (this.data.imgWidth / this.data.imgHeight) || 2;
				// 屏幕宽度
				var rootWidth = 370 - this.data.margin.both * 2;
				var swiperHeight = (rootWidth / radio).toFixed(2);
				this.$parent.data.tempData.swiperHeight = swiperHeight;
			},
			deep: true
		}
	},
	methods: {}
});

var hotAreaListHtml = '<div style="display:none;"></div>';

Vue.component("hot-area-list", {
	template: hotAreaListHtml,
	data: function () {
		return {
			data: this.$parent.data,
		}
	},
	created: function () {
		if (!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			...this.$parent.data.tempData,
			methods: {
				setHeatMap:this.setHeatMap
			}
		};
	},
	methods: {
		//热区设置
		setHeatMap :function() {
			let that = this;
			if(!that.$parent.data.imageUrl){
				layer.msg('请先上传图片');
				return;
			}
			sessionStorage.setItem('imageData',JSON.stringify(that.$parent.data));
			ns.open_iframe({
				title: '热区设置',
				url:ns.url("shop/diy/heatmap"),
				area:['100%', '100%'],
				success: function(data){
					that.$parent.data.heatMapData = data.heatMapData;
				}
			})
		},
		verify: function (index) {
			var res = {code: true, message: ""};
			$(".draggable-element[data-index='" + index + "'] .hot-area").each(function (i) {
				if (vue.data[index].imageUrl === "") {
					res.code = false;
					res.message = "请添加图片";
					$(this).find(".error-msg").text("请添加图片").show();
					return res;
				} else {
					$(this).find(".error-msg").text("").hide();
				}
			});
			return res;
		}
	}
});