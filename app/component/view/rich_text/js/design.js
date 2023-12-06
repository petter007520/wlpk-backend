var richTextHtml = '<div class="rich-text-list" >';
		richTextHtml += '<div :id="id" style="width:100%;height:450px;padding-left:10px;box-sizing:border-box;"></div>';
	richTextHtml += '</div>';

Vue.component("rich-text", {
	template: richTextHtml,
	data: function () {
		return {
			data : this.$parent.data,
			id: ns.gen_non_duplicate(10),
			editor : null
		}
	},
	created: function () {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['elementBgColor','elementAngle','textColor'];//加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		if(this.$parent.data.margin.top === 0){
			this.$parent.data.margin.top = 10;
			this.$parent.data.margin.bottom = 10;
			this.$parent.data.margin.both = 10;
		}
		
		var self = this;
		setTimeout(function () {
			self.editor = UE.getEditor(self.id,{
				toolbars: [[
					'source', 'undo', 'redo',
					'bold', 'italic', 'underline', '|', 'strikethrough', 'removeformat',  'forecolor', 'backcolor', 'selectall', 'cleardoc',
					'fontsize',
					'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify',
					'horizontal'
				]],
				autoHeightEnabled: false,
				theme:'gray',
			});
			self.editor.ready(function () {
				if(self.$parent.data.html) self.editor.setContent(self.$parent.data.html);
			});
			self.editor.addListener("contentChange",function(){
				self.$parent.data.html = self.editor.getContent();
			});

		}, 100);
	},
	methods:{
		verify : function (index) {
			var res = {code: true, message: ""};
			if (vue.data[index].html === "") {
				res.code = false;
				res.message = "请输入富文本内容";
			}
			return res;
		}
	}
});