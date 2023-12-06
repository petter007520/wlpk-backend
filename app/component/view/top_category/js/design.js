/**
 * 顶部分类·组件
 */
var topStyleHtml = '<div>';

	topStyleHtml += '<div class="layui-form-item" >';
		topStyleHtml += '<label class="layui-form-label sm">风格</label>';
		topStyleHtml += '<div class="layui-input-block">';
			topStyleHtml += '<div v-if="styleName" class="input-text text-color selected-style" @click="selectGroupbuyStyle()">{{styleName}} <i class="layui-icon layui-icon-right"></i></div>';
			topStyleHtml += '<div v-else class="input-text selected-style" @click="selectGroupbuyStyle()">选择 <i class="layui-icon layui-icon-right"></i></div>';
		topStyleHtml += '</div>';
	topStyleHtml += '</div>';
	
	topStyleHtml += '</div>';

Vue.component("style-choose",{
	template : topStyleHtml,
	data : function() {
		return {
			data: this.$parent.data,
			styleName: '线条标签'
		};
	},
	created:function() {
		if (this.data.styleType == 'line') this.styleName = '线条标签';
		else this.styleName = '填充标签';

		this.$parent.data.ignore = ['marginBoth', 'marginTop', 'marginBottom', 'textColor', 'elementAngle', 'componentAngle', 'elementBgColor']; //加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载
	},
	methods:{
		selectGroupbuyStyle: function() {
			var self = this;
			layer.open({
				type: 1,
				title: '风格选择',
				area:['500px','300px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .top-category-style").html(),
				success: function(layero, index) {
					$(".layui-layer-content input[name='style']").val(self.data.styleType);
					$(".layui-layer-content input[name='style_name']").val(self.styleName);
					$("body").on("click", ".layui-layer-content .style-list-con-top-category .style-li-top-category", function () {
						$(this).addClass("selected border-color").siblings().removeClass("selected border-color");
						$(".layui-layer-content input[name='style']").val($(this).find("span").attr('data-type'));
						$(".layui-layer-content input[name='style_name']").val($(this).find("span").text());
					});
				},
				yes: function (index, layero) {
					self.data.styleType = $(".layui-layer-content input[name='style']").val();
					self.styleName = $(".layui-layer-content input[name='style_name']").val();
					layer.closeAll()
				}
			});
		},
	}
});