var videoHtml = '<div class="video-edit">';

		videoHtml += '<div class="template-edit-title">';
			videoHtml += '<h3>视频设置</h3>';

			videoHtml += '<div class="layui-form-item">';
				videoHtml += '<label class="layui-form-label sm">类型</label>';
				videoHtml += '<div class="layui-input-block">';
					videoHtml += '<div @click="data.type=\'upload\'" :class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : (data.type==\'upload\') }">';
						videoHtml += '<i class="layui-anim layui-icon">{{ data.type==\'upload\' ? \'&#xe643;\' : \'&#xe63f;\'  }}</i>';
						videoHtml += '<div>手动上传</div>';
					videoHtml += '</div>';
					videoHtml += '<div @click="data.type=\'link\'" :class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : (data.type==\'link\') }">';
						videoHtml += '<i class="layui-anim layui-icon">{{ data.type==\'link\' ? \'&#xe643;\' : \'&#xe63f;\'  }}</i>';
						videoHtml += '<div>视频链接</div>';
					videoHtml += '</div>';
				videoHtml += '</div>';
			videoHtml += '</div>';

			videoHtml += '<div class="layui-form-item" v-show="data.type == \'link\'">';
				videoHtml += '<label class="layui-form-label sm">视频链接</label>';
				videoHtml += '<div class="layui-input-block">';
					videoHtml += '<input type="text" v-model="data.videoUrl" placeholder="请输入视频链接" class="layui-input">';
				videoHtml += '</div>';
			videoHtml += '</div>';

			videoHtml += '<div class="layui-form-item" v-show="data.type == \'upload\'">';
				videoHtml += '<label class="layui-form-label sm">选择视频</label>';
				videoHtml += '<video-upload :data="{data : data}"></video-upload>';
			videoHtml += '</div>';

			videoHtml += '<div class="layui-form-item">';
				videoHtml += '<label class="layui-form-label sm">封面图</label>';
				videoHtml += '<img-upload :data="{data : data}"></img-upload>';
			videoHtml += '</div>';

		videoHtml += '</div>';

	videoHtml += '</div>';

Vue.component("video-edit",{
	data: function () {
		return {
			data: this.$parent.data,
		};
	},
	created : function(){
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['textColor','componentBgColor','elementBgColor','elementAngle'];//加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载
	},
	methods: {
		verify : function (index) {
			var res = { code : true, message : "" };
			if (vue.data[index].videoUrl === '') {
				res.code = false;
				res.message = "请上传视频";
			}
			if (vue.data[index].imageUrl === '') {
				res.code = false;
				res.message = "请上传视频封面";
			}
			return res;
		}
	},
	template: videoHtml
});