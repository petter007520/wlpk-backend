var floatBtnListHtml = '<div class="float-btn-list">';
		floatBtnListHtml += '<p class="hint" style="font-size: 12px; margin: 5px 0 8px;">建议上传正方形图片</p>';
		floatBtnListHtml += '<ul>';
			floatBtnListHtml += '<li v-for="(item,index) in list" :key="item.id">';
				floatBtnListHtml += '<img-icon-upload :data="{data : item}"></img-icon-upload>';
				floatBtnListHtml += '<div class="right-wrap">';
					floatBtnListHtml += '<div class="action-box" v-show="item.iconType == \'icon\'">';
						floatBtnListHtml += '<div class="action" @click="iconStyle($event, index)"><i class="iconfont iconpifu"></i></div>';
						floatBtnListHtml += '<div class="action" :id="\'float-btn-color-\' + index"><i class="iconfont iconyanse"></i></div>';
					floatBtnListHtml += '</div>';
					floatBtnListHtml += '<nc-link :data="{field: $parent.data.list[index].link}"></nc-link>';
				floatBtnListHtml += '</div>';
				floatBtnListHtml += '<i class="del" @click="del(index)" data-disabled="1">x</i>';
				floatBtnListHtml += '<div class="error-msg"></div>';
			floatBtnListHtml += '</li>';
		floatBtnListHtml += '</ul>';
		floatBtnListHtml += '<div class="add-item text-color" v-if="showAddItem" @click="add">';
			floatBtnListHtml += '<i>+</i>';
			floatBtnListHtml += '<span>添加一个浮动按钮</span>';
		floatBtnListHtml += '</div>';
	floatBtnListHtml += '</div>';

Vue.component("float-btn-list",{
	data: function () {
		return {
			list: this.$parent.data.list,
			maxTip : 3,//最大上传数量提示
			showAddItem : true,
			screenWidth:0,
			colorPicker:{}
		};
	},
	created : function(){
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['textColor','pageBgColor','componentBgColor','elementBgColor','marginTop','marginBottom','marginBoth','componentAngle','elementAngle'];//加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		getElementPosition(this.$parent);
		window.onresize = () => {
		    return (() => {
		        window.screenWidth = document.body.clientWidth;
		        this.screenWidth = window.screenWidth
		    })()
		};
		this.changeShowAddItem();//获取默认值

		this.list.forEach(function (item) {
			if (!item.id) item.id = ns.gen_non_duplicate(10)
		})
	},
	watch : {
		list : function(){
			this.changeShowAddItem();
			getElementPosition(this.$parent)
		},
		screenWidth(val){
			// 为了避免频繁触发resize函数导致页面卡顿，使用定时器
			getElementPosition(this.$parent);
		},
		"$parent.data.btnBottom": function () {
			getElementPosition(this.$parent);
		}
	},
	mounted(){
		this.fetchAllMenuIconColor();
	},
	methods: {
		verify :function () {
			var res = { code: true, message: "" };
			if(this.list.length >0){
				for(var i=0;i < this.list.length;i++){
					if(this.$parent.data.list[i].imageUrl == "" && this.$parent.data.list[i].icon == ""){
						res.code = false;
						res.message = "请添加图片";
						break;
					}
				}
			}else{
				res.code = false;
				res.message = "请添加一个浮动按钮";
			}
			return res;
		},
		//改变添加浮动按钮
		changeShowAddItem(){
			if(this.list.length >= this.maxTip) this.showAddItem = false;
			else this.showAddItem = true;
		},
		/**
		 * 选择图标风格
		 * @param event
		 * @param index
		 */
		iconStyle(event, index){
			var self = this;
			selectIconStyle({
				elem: event.currentTarget,
				icon: self.list[index].icon,
				callback: function (data) {
					if (data) {
						self.list[index].style = data;
					} else {
						iconStyleSet({
							style: JSON.stringify(self.list[index].style),
							query: {
								icon: self.list[index].icon
							}
						}, function(style){
							self.list[index].style = style;
						})
					}
				}
			})
		},
		/**
		 * 渲染颜色组件
		 * @param id
		 * @param color
		 * @param callback
		 */
		colorRender(id, color, callback){
			var self = this;
			if (this.colorPicker[id]) return;
			setTimeout(function () {
				self.colorPicker[id] = Colorpicker.create({
					el: id,
					color: color,
					change: function (elem, hex) {
						callback(elem, hex)
					}
				});
			})
		},
		/**
		 * 渲染全部菜单颜色选择器
		 */
		fetchAllMenuIconColor(){
			var self = this;
			this.list.forEach(function (item, index) {
				self.colorRender('float-btn-color-' + index, '', function (elem, color) {
					index = $(elem).parents('li').index();
					if (self.list[index].style.iconBgColor.length || self.list[index].style.iconBgImg) {
						self.list[index].style.iconBgColor = [color];
					} else {
						self.list[index].style.iconColor = [color];
					}
					self.$forceUpdate();
				})
			})
		},
		add(){
			var self = this;
			this.list.push({ imageUrl : '', title : '', link : {name: ''}, iconType: 'img', icon: '', style: {fontSize: 60, iconBgColor: [], iconBgColorDeg: 0,iconBgImg: '',bgRadius: 0,iconColor: ['#000'],iconColorDeg: 0}})

			this.colorRender('float-btn-color-' + (this.list.length - 1), '', function (elem, color) {
				var index = $(elem).parents('li').index();
				if (self.list[index].style.iconBgColor.length || self.list[index].style.iconBgImg) {
					self.list[index].style.iconBgColor = [color];
				} else {
					self.list[index].style.iconColor = [color];
				}
				self.$forceUpdate();
			})
		},
		del(index){
			this.list.splice(index, 1);
			delete this.colorPicker['float-btn-color-' + index];
		}
	},
	template: floatBtnListHtml
});

/**
 * 按钮位置
 */
var btnPosition = '<div class="layui-form-item icon-radio">';
	btnPosition += '<label class="layui-form-label sm">{{data.label}}</label>';
	btnPosition +=	 '<div class="layui-input-block">';
	btnPosition += 		 '<template v-for="(item,index) in list">';
	btnPosition += 		 	'<span :class="[parent[data.field] == item.value ? \'\' : \'layui-hide\']">{{item.label}}</span>';
	btnPosition += 		 '</template>';
	btnPosition +=	 	'<ul class="icon-wrap">';
	btnPosition +=		 	'<template v-for="(item,index) in list">';
	btnPosition +=		 		'<li @click="changePosition(item.value)" :class="{\'border-color\':parent[data.field] == item.value}">';
	btnPosition += 					'<i class="iconfont" :class="[item.icon_img,parent[data.field] == item.value ? \'text-color\' : \'\']"></i>';
	btnPosition +=		 		'</li>';
	btnPosition +=		 	'</template>';
	btnPosition +=	 	'</ul>';
	btnPosition +=	 '</div>';
	btnPosition += '</div>';
Vue.component("btn-position", {
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: "bottomPosition",
					label: "按钮位置"
				};
			}
		}
	},
	data: function () {
		return {
			list: [
				{
					label: "左上",
					value: "1",
					icon_img: "iconzuoshangjiao",
				},
				{
					label: "右上",
					value: "2",
					icon_img: "iconyoushangjiao",
				},
				{
					label: "左下",
					value: "3",
					icon_img: "iconzuoxiajiao",
				},
				{
					label: "右下",
					value: "4",
					icon_img: "iconyouxiajiao",
				},
			],
			parent: this.$parent.data,
			imageSize: this.$parent.data.imageSize,
		};
	},
	created: function () {
		$('.float-btn').parent('.draggable-element').css({"border": "none"});// 将边框进行隐藏掉
	},
	watch: {
		"$parent.data.imageSize": function () {
			getElementPosition(this.$parent);
		}
	},
	methods: {
		changePosition:function(val){
			this.parent.bottomPosition = val;
			getElementPosition(this.$parent)
		}
	},
	template: btnPosition
});

function getElementPosition(params) {
	var type = parseInt(params.data.bottomPosition),  //布局类型，1为第一种，2为第二种依次类推
		bottomNumber = parseInt(params.data.btnBottom); //上下偏移的变量

	/**
	 * #diyView .diy-view-wrap .preview-block =》 显示框【定位的参照对象是body】，#diyView =》 外边框【定位的参照对象是body】
	 * 1、弹窗按钮是根据“外边框”进行定位的,但弹窗按钮是需要在“显示框”中展示
	 * 2、弹窗按钮与显示框的上下间距定义为50px,左右为30px，这个是常量
	 * 3、计算弹窗按钮的四个位置，都是根据 top，left进行计算的
	 * */

	var box = document.querySelector("#diyView .diy-view-wrap .preview-block").getBoundingClientRect();
	var box1 = document.querySelector("#diyView").getBoundingClientRect();

	var topVal = 0; //弹窗按钮的top
	var leftVal = 0; //弹窗按钮的left
	var leftOffSet = 30; //弹窗按钮左右的偏移量

	if (type == 1) {

		// topVal = 显示框的top - 外边框的top + 距离显示框下边距的50px + 偏移量
		// leftVal = 显示框的left - 外边框的left + 距离显示框右边距的30px
		topVal = 100 + bottomNumber + "px";
		leftVal = box.left - box1.left + leftOffSet + "px";

	} else if (type == 2) {

		// topVal = 显示框的top - 外边框的top + 距离显示框下边距的50px + 偏移量
		// leftVal = 显示框的left - 外边框的left + 显示框的width（82） - 弹窗按钮的width - 距离显示框右边距的30px
		topVal = 100 + bottomNumber + "px";
		leftVal = box.left - box1.left + box.width - params.data.imageSize - 2 - leftOffSet + "px";

	} else if (type == 3) {

		// topVal = 显示框的top - 外边框的上边距(20) - 防止贴边(20) - 弹出按钮高度 - 弹出按钮的下外边距 - 偏移量
		// leftVal = 显示框的left - 外边框的left + 距离显示框左边距的30px
		// topVal = box.top - box1.top + box.height - 82 - topOff - bottomNumber + "px";
		topVal = $("#diyView .preview-wrap .div-wrap").height() - 20 - 20 - (params.data.list.length * params.data.imageSize) - ((params.data.list.length - 1) * 10) - bottomNumber + 'px';
		leftVal = box.left - box1.left + leftOffSet + "px";

	} else if (type == 4) {

		// topVal = 显示框的top - 外边框的上边距(20) - 防止贴边(20) - 弹出按钮高度 - 弹出按钮的下外边距 - 偏移量
		// leftVal = 显示框的left - 外边框的left + 显示框的width - 弹窗按钮的width - 边框width - 距离显示框右边距的30px
		topVal = $("#diyView .preview-wrap .div-wrap").height() - 20 - 20 - (params.data.list.length * params.data.imageSize) - ((params.data.list.length - 1) * 10) - bottomNumber + 'px';
		leftVal = box.left - box1.left + box.width - params.data.imageSize - 2 - leftOffSet + "px";
	}

	$(".draggable-element .float-btn").css({
		left: leftVal,
		top: topVal,
		'z-index': 999
	});

	$(".draggable-element .float-btn .edit-attribute").css({
		position: 'fixed',
		right: '15px',
		top: Math.abs(box1.top)
	})
}