/**
 * 底部导航·组件
 */
var bottomMenuHtml = '<div class="bottom-menu-config">';
		bottomMenuHtml += '<div class="template-edit-title">';
			bottomMenuHtml += '<h3>导航样式设置</h3>';
			bottomMenuHtml += '<div class="layui-form-item icon-radio">';
				bottomMenuHtml += '<label class="layui-form-label sm">导航类型</label>';
				bottomMenuHtml += '<div class="layui-input-block">';
					bottomMenuHtml += '<template v-for="(item, index) in typeList">';
						bottomMenuHtml += '<span :class="[item.value == data.type ? \'\' : \'layui-hide\']">{{item.label}}</span>';
					bottomMenuHtml += '</template>';
					bottomMenuHtml += '<ul class="icon-wrap">';
						bottomMenuHtml += '<li v-for="(item, index) in typeList" :class="{\'text-color border-color\':data.type==item.value}" @click="data.type=item.value">';
							bottomMenuHtml += '<i :class="[\'iconfont\',item.src,{\'text-color\':data.type==item.value}]"></i>';
						bottomMenuHtml += '</li>';
					bottomMenuHtml += '</ul>';
				bottomMenuHtml += '</div>';
			bottomMenuHtml += '</div>';

			bottomMenuHtml += '<div class="layui-form-item">';
				bottomMenuHtml += '<label class="layui-form-label sm">色调</label>';
				bottomMenuHtml += '<div class="layui-input-block">';
					bottomMenuHtml += '<div @click="switchTheme(\'default\')" :class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : ($parent.data.theme == \'default\') }">';
						bottomMenuHtml += '<i class="layui-anim layui-icon">{{ $parent.data.theme == \'default\' ? \'&#xe643;\' : \'&#xe63f;\' }}</i>';
						bottomMenuHtml += '<div>跟随主题风格</div>';
					bottomMenuHtml += '</div>';
					bottomMenuHtml += '<div @click="switchTheme(\'diy\')" :class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : ($parent.data.theme == \'diy\') }">';
						bottomMenuHtml += '<i class="layui-anim layui-icon">{{ $parent.data.theme == \'diy\' ? \'&#xe643;\' : \'&#xe63f;\' }}</i>';
						bottomMenuHtml += '<div>自定义</div>';
					bottomMenuHtml += '</div>';
				bottomMenuHtml += '</div>';
			bottomMenuHtml += '</div>';

			bottomMenuHtml += '<div v-show="$parent.data.theme==\'diy\'">';
				bottomMenuHtml += '<color :data="{ field: \'backgroundColor\', label: \'背景颜色\' }"></color>';
				bottomMenuHtml += '<color v-show="$parent.data.type == 1 || $parent.data.type == 3"></color>';
				bottomMenuHtml += '<color :data="{ field: \'textHoverColor\', label: \'文字选中\' }" v-show="$parent.data.type == 1 || $parent.data.type == 3"></color>';
			bottomMenuHtml += '</div>';
		bottomMenuHtml += '</div>';

		bottomMenuHtml += '<div class="template-edit-title">';
			bottomMenuHtml += '<h3>导航内容设置</h3>';
		bottomMenuHtml += '</div>';

		bottomMenuHtml += '<ul class="bottom-menu-set">';
			bottomMenuHtml += '<li v-for="(item,index) in menuList" :key="item.id">';

				bottomMenuHtml += '<div class="content-block">';

					bottomMenuHtml += '<div class="layui-form-item" v-show="$parent.data.type != 3">';
						bottomMenuHtml += '<label class="layui-form-label sm">图标</label>';
						bottomMenuHtml += '<div class="layui-input-block">';
							bottomMenuHtml += '<div class="image-block">';
								bottomMenuHtml += '<icon-upload :data="{ data : item, field : \'iconPath\'}" ></icon-upload>';
								bottomMenuHtml += '<icon-upload :data="{ data : item, field : \'selectedIconPath\'}" ></icon-upload>';
							bottomMenuHtml += '</div>';
						bottomMenuHtml += '</div>';
					bottomMenuHtml += '</div>';

					bottomMenuHtml += '<div class="layui-form-item" v-show="$parent.data.type == 1 || $parent.data.type == 3">';
						bottomMenuHtml += '<label class="layui-form-label sm">标题</label>';
						bottomMenuHtml += '<div class="layui-input-block">';
							bottomMenuHtml += '<input type="text" name=\'text\' v-model="item.text" maxlength="5" @keyup="listenText(index,item.text)" class="layui-input" />';
						bottomMenuHtml += '</div>';
					bottomMenuHtml += '</div>';

					bottomMenuHtml += '<nc-link :data="{ field : $parent.data.list[index].link, label:\'链接\' }"></nc-link>';
				bottomMenuHtml += '</div>';

				bottomMenuHtml += '<i class="del" @click="menuList.splice(index,1)" data-disabled="1">x</i>';

				bottomMenuHtml += '<div class="error-msg"></div>';
				bottomMenuHtml += '<div class="iconfont icontuodong"></div>';
			bottomMenuHtml += '</li>';

		bottomMenuHtml += '</ul>';

		bottomMenuHtml += '<div class="add-item text-color" v-if="showAddItem" @click="addMemu">';
			bottomMenuHtml += '<i>+</i>';
			bottomMenuHtml += '<span>添加一个图文导航</span>';
		bottomMenuHtml += '</div>';

		bottomMenuHtml += '<p class="hint">建议上传比例相同的图片，最多添加 {{maxTip}} 个底部导航</p>';

	bottomMenuHtml += '</div>';

Vue.component("bottom-menu", {
	
	template: bottomMenuHtml,
	data: function () {
		
		return {
			data: this.$parent.data,
			typeList: [
				{
					label: "图文",
					value: 1,
					src: "icontuwendaohang1",
				},
				{
					label: "图片",
					value: 2,
					src: "icontuwendaohang",
				},
				{
					label: "文字",
					value: 3,
					src: "iconchunwenzidaohang",
				},
			],
			menuList: this.$parent.data.list,
			showAddItem: true,
			maxTip: 5,
		};
		
	},
	created: function () {
		this.changeShowAddItem();

		this.menuList.forEach(function(e, i){
			if(!e.id ) e.id = ns.gen_non_duplicate(6);
		});
		this.$parent.data.list = this.menuList;

		var moveBeforeIndex = 0;
		var _this = this;

		setTimeout(function(){
			$( '.edit-attribute .bottom-menu-set' ).DDSort({
			    // target: 'li',
			    floatStyle: {
			        'border': '1px solid #ccc',
			        'background-color': '#fff'
			    },
			    down: function(index){
			    	moveBeforeIndex = index;
			    },
				move: function (){
				},
			    up: function(){
					var index = $(this).index();
			    	var temp = _this.menuList[moveBeforeIndex];
			    	_this.menuList.splice(moveBeforeIndex, 1);
			    	_this.menuList.splice(index, 0, temp);
		    		_this.$parent.data.list = _this.menuList;
					_this.$forceUpdate();
			    }
			});
		}, 500)
	},
	
	methods: {
		switchTheme: function (theme) {
			this.$parent.data.theme = theme;
			this.$parent.data.backgroundColor = "#ffffff";
			this.$parent.data.textColor = "#333333";
			this.$forceUpdate();
		},
		listenText: function (index, text) {
			if (text.length > 6) {
				this.data.list[index].text = this.data.list[index].text.substr(0, 5);
				layer.msg("字数不能超出5位");
			}
		},
		
		//改变图文导航按钮的显示隐藏
		changeShowAddItem: function () {
			if (this.menuList.length >= this.maxTip) this.showAddItem = false;
			else this.showAddItem = true;
		},
		addMemu(){
			this.menuList.push({
				iconPath: '',
				selectedIconPath: '',
				text: '菜单',
				link: {},
				iconClass : '',
				style: null,
				selected_style: null,
				id: ns.gen_non_duplicate(6)
			})
		}
	},
	
	watch: {
		menuList: function () {
			this.changeShowAddItem();
		}
	}
});

var iconHtml = '<div class="icon-block layui-form" :id="id">';

	iconHtml += '<div v-if="data.field == \'iconPath\'">';
		iconHtml += '<template v-if="!myData.data[data.field]">';
			iconHtml += '<div class="icon-box">';
				iconHtml += '<div class="select-icon" v-if="myData.data[data.field] == \'\'" @click="uploadImg(\'icon_type\')">';
				iconHtml += '<span class="add">+</span>';
				iconHtml += '</div>';
			iconHtml += '</div>';
		iconHtml += '</template>';
		iconHtml += '<template v-else>';
			iconHtml += '<template v-if="myData.data.icon_type == \'icon\'">';
				iconHtml += '<div class="icon-box">';
					iconHtml += '<iconfont :icon="myData.data[data.field]" :value="myData.data.style ? myData.data.style : null"></iconfont>';
					iconHtml += '<div class="operation">';
						iconHtml += '<div class="operation-warp">';
							iconHtml += '<i title="图片预览" class="iconfont iconreview js-preview"></i>';
							iconHtml += '<i title="删除图标" class="layui-icon layui-icon-delete" @click="deleteIcon()"></i>';
						iconHtml += '</div>';
						iconHtml += '<div @click="uploadImg(\'icon_type\')" class="js-replace">点击替换</div>';
					iconHtml += '</div>';
				iconHtml += '</div>';
				iconHtml += '<div class="action-box">';
					iconHtml += '<div class="action" @click="iconStyle($event, \'style\')"><i class="iconfont iconpifu"></i></div>';
					iconHtml += '<div class="action" @click="selectColor(\'color-\' + data.field+id, \'style\')" :id="\'color-\' + data.field+id"><i class="iconfont iconyanse"></i></div>';
				iconHtml += '</div>';
			iconHtml += '</template>';

			iconHtml += '<template v-if="myData.data.icon_type == \'img\'">';
				iconHtml += '<div class="upload-box">';
					iconHtml += '<img :layer-src="img(myData.data[data.field])" :src="img(myData.data[data.field])" class="img_prev"/>';
					iconHtml += '<div class="operation">';
						iconHtml += '<div class="operation-warp">';
							iconHtml += '<i title="图片预览" class="iconfont iconreview js-preview" @click="previewImg()"></i>';
							iconHtml += '<i title="删除图片" class="layui-icon layui-icon-delete js-delete" @click="deleteImg()"></i>';
						iconHtml += '</div>';
						iconHtml += '<div @click="uploadImg(\'icon_type\')" class="js-replace">点击替换</div>';
					iconHtml += '</div>';
				iconHtml += '</div>';
			iconHtml += '</template>';
		iconHtml += '</template>';
	iconHtml += '</div>';

	iconHtml += '<div v-if="data.field == \'selectedIconPath\'">';
		iconHtml += '<template v-if="!myData.data[data.field]">';
			iconHtml += '<div class="icon-box">';
				iconHtml += '<div class="select-icon" v-if="myData.data[data.field] == \'\'" @click="uploadImg(\'selected_icon_type\')">';
				iconHtml += '<span class="add">+</span>';
				iconHtml += '</div>';
			iconHtml += '</div>';
		iconHtml += '</template>';
		iconHtml += '<template v-else>';
			iconHtml += '<template v-if="myData.data.selected_icon_type == \'icon\'">';
				iconHtml += '<div class="icon-box">';
					iconHtml += '<template>';
						iconHtml += '<iconfont :icon="myData.data[data.field]" :value="myData.data.selected_style ? myData.data.selected_style : null"></iconfont>';
						iconHtml += '<div class="operation">';
							iconHtml += '<div class="operation-warp">';
							iconHtml += '<i title="图片预览" class="iconfont iconreview js-preview"></i>';
							iconHtml += '<i title="删除图标" class="layui-icon layui-icon-delete" @click="deleteIcon()"></i>';
							iconHtml += '</div>';
							iconHtml += '<div @click="uploadImg(\'selected_icon_type\',\'icon\')" class="js-replace">点击替换</div>';
						iconHtml += '</div>';
					iconHtml += '</template>';
				iconHtml += '</div>';
				iconHtml += '<div class="action-box">';
					iconHtml += '<div class="action" @click="iconStyle($event, \'selected_style\')"><i class="iconfont iconpifu"></i></div>';
					iconHtml += '<div class="action" @click="selectColor(\'color-\' + data.field+id, \'selected_style\')" :id="\'color-\' + data.field+id"><i class="iconfont iconyanse"></i></div>';
				iconHtml += '</div>';
			iconHtml += '</template>';

			iconHtml += '<template v-if="myData.data.selected_icon_type == \'img\'">';
				iconHtml += '<div class="upload-box">';
					iconHtml += '<img :layer-src="img(myData.data[data.field])" :src="img(myData.data[data.field])" class="img_prev"/>';
					iconHtml += '<div class="operation">';
						iconHtml += '<div class="operation-warp">';
							iconHtml += '<i title="图片预览" class="iconfont iconreview js-preview" @click="previewImg()"></i>';
							iconHtml += '<i title="删除图片" class="layui-icon layui-icon-delete js-delete" @click="deleteImg()"></i>';
						iconHtml += '</div>';
						iconHtml += '<div @click="uploadImg(\'selected_icon_type\',\'img\')" class="js-replace">点击替换</div>';
					iconHtml += '</div>';
				iconHtml += '</div>';
			iconHtml += '</template>';
		iconHtml += '</template>';
	iconHtml += '</div>';

	iconHtml += '<div class="icon-text" v-if="data.field == \'iconPath\'">未选中</div>';
	iconHtml += '<div class="icon-text" v-if="data.field == \'selectedIconPath\'">已选中</div>';
iconHtml += '</div>';

/**
 * 选择Icon组件：
 * 参数说明：data：链接对象，callback：回调
 */
Vue.component("icon-upload", {
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: null,// icon对象
					label: "图标",// 文本
				};
			}
		}
	},
	template: iconHtml,
	data: function () {
		return {
			id: ns.gen_non_duplicate(10),
			myData: this.data, // 此处用数组的目的是触发变异方法，进行视图更新
			parent: (Object.keys(this.$parent.data).length) ? this.$parent.data : this.$parent.global
		};
	},
	created: function () {
		this.myData.label = this.myData.label || "图标";
		// 找寻父级
		if (this.myData.parent !== undefined) this.parent = this.$parent.data[this.myData.parent];

		this.id = ns.gen_non_duplicate(10);

	},
	methods: {
		selectedIcon: function () {
			iconSelect((data) => {
				this.myData.data[this.data.field] = data;
			})
		},
		uploadImg(field, display_type) {
			display_type = display_type || 'img,icon';
			var self = this;
			openAlbum(function (obj) {
				if (typeof obj == 'object') {
					self.myData.data[self.data.field] = obj[0].pic_path;
					self.myData.data[field] = 'img';
				} else {
					self.myData.data[self.data.field] = obj;
					self.myData.data[field] = 'icon';
					var defaultStyle = {
						fontSize: 100,
						iconBgColor: [],
						iconBgColorDeg: 0,
						iconBgImg: '',
						bgRadius: 0,
						iconColor: ['#000'],
						iconColorDeg: 0,
					};
					if (!self.myData.data.selected_style) self.$set(self.myData.data, 'selected_style', JSON.parse(JSON.stringify(defaultStyle)))
					if (!self.myData.data.style) self.$set(self.myData.data, 'style', JSON.parse(JSON.stringify(defaultStyle)))
				}
				self.$forceUpdate();
			}, 1, 0, 'img', display_type);
		},
		previewImg() {
			$('#' + this.id).find('.upload-box>img').click();
		},
		deleteImg() {
			this.myData.data[this.data.field] = '';
			this.$forceUpdate();
		},
		deleteIcon(index) {
			this.myData.data[this.data.field] = '';
			this.$forceUpdate();
		},
		img(path) {
			return ns.img(path)
		},
		selectColor(id, style_field) {
			let self = this;
			colorRender(id, '', function (elem, color) {
				if (self.myData.data[style_field].iconBgImg || self.myData.data[style_field]['iconBgColor'].length) {
					self.myData.data[style_field]['iconBgColor'] = [color];
				} else {
					self.myData.data[style_field]['iconColor'] = [color];
				}
				self.$forceUpdate();
			})
		},
		/**
		 * 选择图标风格
		 * @param event
		 * @param style_field
		 */
		iconStyle(event, style_field) {
			var self = this;
			selectIconStyle({
				elem: event,
				icon: self.myData.data[self.data.field],
				callback: function (data) {
					if (data) {
						self.myData.data[style_field] = data;
						self.$forceUpdate();
					} else {
						iconStyleSet({
							query: {
								icon: self.myData.data[self.data.field]
							},
						}, function (style) {
							self.myData.data[style_field] = style;
							self.$forceUpdate();
						})
					}
				}
			})
		},

	}
});

/**
 * 渲染颜色组件
 * @param id
 * @param color
 * @param callback
 */
var _colorPicker = {};
function colorRender(id, color, callback){
	if (_colorPicker[id]) return;
	setTimeout(function () {
		_colorPicker[id] = Colorpicker.create({
			el: id,
			color: color,
			change: function (elem, hex) {
				callback(elem, hex)
			}
		});
		$('#'+id).click();
	},10)
}
function selectIconStyle(option) {
	var _w = option.width ? option.width : 340,
		_h = option.height ? option.height : 200,
		_x = option.elem.x - _w,
		_y = option.elem.y;

	option.pagex -= _w;

	window.onmessage = function(e) {
		if (e.data.event && e.data.event == 'selectIconStyle') {
			$('.select-icon-style').remove();
			typeof option.callback == 'function' && option.callback(e.data.data);
		}
	};

	var h = `
            <div class="select-icon-style">
                <div class="icon-style-wrap" style="width: `+ _w +`px;height: `+ _h +`px;left:`+ _x +`px;top:`+ _y +`px">
                    <iframe src="`+ ns.url('shop/diy/selecticonstyle', {icon: option.icon}) +`" frameborder="0"></iframe>
                </div>
            </div>
        `;
	$('body').append(h);
	// 点击任意位置关闭弹窗
	$('.select-icon-style').click(function () {
		$(this).remove();
	})
}

/**
 * 底部导航Vue对象
 */
var vue = new Vue({
	
	el: "#bottomNav",
	
	data: {
		
		data: {
			type: 1,
			theme:'default',
			// fontSize: 14,
			textColor: "#333333",
			textHoverColor: "#ff0036",
			iconColor: "#333333",
			iconHoverColor: "#ff0036",
			backgroundColor: "#ffffff",
			bulge : true,
			list: [
				{iconPath: '', selectedIconPath: '', text: '菜单', link: {}, icon_type:'icon', selected_icon_type:'icon', style:'', selected_style:''},
				{iconPath: '', selectedIconPath: '', text: '菜单', link: {}, icon_type:'icon', selected_icon_type:'icon', style:'', selected_style:''},
				{iconPath: '', selectedIconPath: '', text: '菜单', link: {}, icon_type:'icon', selected_icon_type:'icon', style:'', selected_style:''},
				{iconPath: '', selectedIconPath: '', text: '菜单', link: {}, icon_type:'icon', selected_icon_type:'icon', style:'', selected_style:''},
			],
		},
		selected: -1,
	},
	created: function () {
		if (bottomNavInfo) this.data = bottomNavInfo;
	},
	methods: {
		
		mouseOver: function (index) {
			this.selected = index;
		},
		mouseOut: function () {
			this.selected = -1;
		},
		
		//转换图片路径
		changeImgUrl: function (url) {
			if (url == null || url === "") return '';
			if (url.indexOf("static/img/") > -1) return ns.img(ns_url.staticImg + "/" + url.replace("public/static/img/", ""));
			else return ns.img(url);
		},
		
	}
});

$('.edit-attribute').height($(window).height()-150+'px');

var repeat_flag = false;//防重复标识
$("button.save").click(function () {
	
	// 验证
	var verify = {
		flag : false,
		message : ""
	};
	for (var i=0;i<vue.data.list.length;i++) {
		var item = vue.data.list[i];
		if (vue.data.type == 1) {
			// 图文
			if (item.text == '') {
				verify.flag = true;
				verify.message = "请输入第[" + (i + 1) + "]个标题";
				break;
			}
			if (item.iconPath == '' ) {
				verify.flag = true;
				verify.message = "请上传第[" + (i + 1) + "]个图标";
				break;
			}
			if (item.selectedIconPath == '') {
				verify.flag = true;
				verify.message = "请上传第[" + (i + 1) + "]个选中图标";
				break;
			}

		} else if (vue.data.type == 2) {
			// 图片
			if (item.iconPath == '') {
				verify.flag = true;
				verify.message = "请上传第[" + (i + 1) + "]个图片";
				break;
			}
			if (item.selectedIconPath == '') {
				verify.flag = true;
				verify.message = "请上传第[" + (i + 1) + "]个选中图片";
				break;
			}

		} else if (vue.data.type == 3) {
			// 文字
			if (item.text == '') {
				verify.flag = true;
				verify.message = "请输入第[" + (i + 1) + "]个标题";
				break;
			}
		}
		if ($.isEmptyObject(item.link)) {
			verify.flag = true;
			verify.message = "请选择链接地址";
			break;
		}
	}
	
	if(verify.flag){
		layer.msg(verify.message);
		return;
	}
	
	if (repeat_flag) return;
	repeat_flag = true;

	$.ajax({
		type: "post",
		url: ns.url("shop/diy/bottomNavDesign"),
		data: {value: JSON.stringify(vue.data)},
		dataType: "JSON",
		success: function (res) {
			layer.msg(res.message);
			if (res.code == 0) {
				location.reload();
			} else {
				repeat_flag = false;
			}
		}
	});
});