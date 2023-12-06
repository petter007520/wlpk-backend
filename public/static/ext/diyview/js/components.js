/**
 * 系统内置属性组件
 */
var resourceHtml = '<div v-show="false"><slot></slot></div>';

//CSS·组件
Vue.component("css", {
	props: ["src"],
	template: resourceHtml,
	created: function () {
		var self = this;

		//内联样式
		if (this.$slots.default) {
			var css = "<style>" + this.$slots.default[0].text + '</style>';

			//防止重复加载资源
			if ($("head").html().indexOf(css) == -1) {
				$("head").append(css);
			}

			//延迟
			setTimeout(function () {
				self.$parent.data.lazyLoadCss = true;
			}, 10);
		}

		//外联样式
		if (this.src) {

			//防止重复加载资源
			if ($("head").html().indexOf(this.src) == -1) {
				var styleNode = createLink(this.src);
				styleOnload(styleNode, function () {
					self.$parent.data.lazyLoadCss = true;
				});
			} else {
				//延迟
				setTimeout(function () {
					self.$parent.data.lazyLoadCss = true;
				}, 10);
			}
		}

	}
});

//JavaScript脚本·组件
Vue.component("js", {
	props: ["src"],
	template: resourceHtml,
	created: function () {
		var self = this;

		//如果JS全部是内部代码，则延迟10毫秒
		//如果JS有内部代码、也有外部JS，则以外部JS加载完成时间为准，同时延迟10毫秒，让外部JS中的组件进行加载
		//如果JS全部是外部代码，则以外部JS加载完成时间为准，同时延迟10毫秒，让外部JS中的组件进行加载

		//内联js
		if (this.$slots.default) {
			var script = "<script>" + this.$slots.default[0].text + "</script>";
			$("body").append(script);
			//如果有外部JS，则以外部JS加载完成时间为准
			if (this.$parent.data.outerCountJs == 0) {
				setTimeout(function () {
					self.$parent.data.lazyLoad = true;
				}, 10);
			}
		}

		//外联js
		if (this.src) {
			$.getScript(this.src, function (res) {
				setTimeout(function () {
					self.$parent.data.lazyLoad = true;
				}, 10);
			});
		}
	}
});

/**
 * 系统内置属性组件
 */
var imageHtml = `<div :style="style"></div>`;

//图片组件
Vue.component("nc-image", {
	props: {
		src: {
			type: String,
			default: ''
		},
		mode: {
			type: String,
			default: 'scaleToFill'
		},
		width: {
			type: String,
			default: '100%'
		},
		height: {
			type: String,
			default: '100%'
		}
	},
	data: function () {
		return {
			style: {
				'background-image': 'url('+ this.src +')',
				'background-repeat': 'no-repeat',
				'width': this.width,
				'height': this.height,
				'background-position': 'center',
				'background-size': ''
			},
			modeList: {
				aspectFit: {'background-size': 'contain'}, // 缩放
				scaleToFill: {'background-size': '100% 100%'}, // 拉伸
				aspectFill: {'background-size': 'cover'}, // 填充
				center: {'background-size': 'auto auto'} // 居中裁剪
			}
		}
	},
	template: imageHtml,
	created: function () {
		if (this.modeList[ this.mode ]) Object.assign(this.style, this.modeList[ this.mode ]);
	},
	watch: {
		mode: function (val) {
			this.$set(this.style, 'background-size', this.modeList[ this.mode ]['background-size'])
		},
		src: function (val) {
			this.$set(this.style, 'background-image', 'url('+ this.src +')')
		}
	}
});

/**
 * 图片显示模式组件
 */
var imageModeHtml = `
	<div class="layui-form-item component-links">
		<label class="layui-form-label sm">缩放模式</label>
		<div class="layui-input-block">
			<span class="sm text-color" @click="selected()">
				<span :title="modeList[ data.imageMode ].name">{{ modeList[ data.imageMode ].name }}</span>
				<i class="layui-icon layui-icon-right"></i>
			</span>
		</div>
	</div>
`;
Vue.component("nc-image-mode", {
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					imageMode: "scaleToFill"
				};
			}
		},
	},
	template: imageModeHtml,
	data: function () {
		return {
			modeList:{
				scaleToFill: {mode: 'scaleToFill', name: '拉伸'},
				aspectFit: {mode: 'aspectFit', name: '缩放'},
				aspectFill: {mode: 'aspectFill', name: '填充'},
				// center: {mode: 'center', name: '中心裁剪'}
			}
		};
	},
	created: function () {
		if (!this.data.imageMode) this.$set(this.data, 'imageMode', 'scaleToFill');
	},
	methods: {
		selected: function () {
			var $self = this;
			var h = `<div class="layui-form"><div class="layui-form-item component-links"><label class="layui-form-label sm">缩放模式：</label><div class="layui-input-block">`;
			Object.keys(this.modeList).forEach(function (key) {
				let item = $self.modeList[key];
				h += '<input type="radio" name="image_mode" value="'+ item.mode +'" title="'+ item.name +'" '+ (key == $self.data.imageMode ? ' checked' : '') +'>';
			})
			h += '</div></div></div>'
			layer.open({
				title: "图片缩放模式设置",
				type: 1,
				area: ['450px', '190px'],
				fixed: false, //不固定
				content: h,
				success: function(){
					layui.use('form', function(){
						layui.form.render();
					})
				},
				btn: ['保存', '取消'],
				yes: function () {
					let mode = $('[name="image_mode"]:checked').val();
					$self.data.imageMode = mode;
					layer.closeAll();
				}
			})
		}
	}
});

//[对齐方式]属性组件
var textAlignHtml = '<div class="layui-form-item">';
		textAlignHtml += '<label class="layui-form-label sm">{{data.label}}</label>';
		textAlignHtml += '<div class="layui-input-block">';
			textAlignHtml += '<template v-for="(item,index) in list">';
				textAlignHtml += '<div @click="parent[data.field]=item.value" :class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : (parent[data.field]==item.value) }"><i class="layui-anim layui-icon">{{ parent[data.field] == item.value ? \'&#xe643;\':\'&#xe63f;\' }}</i><div>{{item.label}}</div></div>';
			textAlignHtml += '</template>';
		textAlignHtml += '</div>';
	textAlignHtml += '</div>';

Vue.component("text-align", {
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: "textAlign",
					label: "对齐方式"
				};
			}
		}
	},
	created: function () {
		if (this.data.label === undefined) this.data.label = "对齐方式";
		if (this.data.field === undefined) this.data.field = "textAlign";
	},
	template: textAlignHtml,
	data: function () {
		return {
			list: [
				{label: "居左", value: "left"},
				{label: "居中", value: "center"},
				{label: "居右", value: "right"}
			],
			parent: this.$parent.data,
		};
	}
});

//[滑块]属性组件
var sliderHtml = '<div class="layui-form-item slide-component">';
		sliderHtml += '<label class="layui-form-label sm">{{compData.label}}</label>';
		sliderHtml += '<div class="layui-input-block">';
			sliderHtml += '<div :id="id" class="side-process"></div>';
			// sliderHtml += '<span class="slide-prompt-text">{{parent[compData.field]}}</span>';
		sliderHtml += '</div>';
	sliderHtml += '</div>';

Vue.component("slide", {
	template: sliderHtml,
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: "height",
					label: "空白高度",
					min: 0,
					max: 100,
					callback: null
				};
			}
		}
	},
	data: function () {
		return {
			id: "slide_" + ns.gen_non_duplicate(10),
			parent: this.$parent.data,
			compData:null
		};
	},
	created: function () {
		this.compData = this.data;// vue组件中不能直接改变props，定义内部变量处理

		if (this.compData.label === undefined) this.compData.label = "空白高度";
		if (this.compData.field === undefined) this.compData.field = "height";
		if (this.compData.min === undefined) this.compData.min = 0;
		if (this.compData.max === undefined) this.compData.max = 100;
		if (this.compData.parent === 'global') this.parent = this.$parent.global;
		else if (this.compData.parent === 'template') this.parent = this.$parent.global.template;
		else if (this.compData.parent !== undefined) this.parent = this.$parent.data[this.compData.parent];

		var fieldArr = this.compData.field.split('.');
		this.compData.field = fieldArr[fieldArr.length - 1];
		for (var i = 0; i < fieldArr.length - 1; i++) {
			this.parent = this.parent[fieldArr[i]];
		}

		var _self = this;
		setTimeout(function () {
			layui.use('slider', function () {
				var slider = layui.slider;
				slider.render({
					elem: '#' + _self.id,
					min: _self.compData.min,
					max: _self.compData.max,
					tips: false,
					input: true,
					value: _self.parent[_self.compData.field],
					change: function (value) {
						_self.parent[_self.compData.field] = value;
						// 如果修改页面属性，那么组件属性也要变化
						if (_self.compData.parent === 'template') {
							for (var i = 0; i < vue.data.length; i++) {
								if (fieldArr.length > 1) {
									for (var j = 0; j < fieldArr.length - 1; j++) {
										vue.data[i][fieldArr[j]][_self.compData.field] = value;
									}
								} else {
									vue.data[i][_self.compData.field] = value;
								}
							}
						}

						if (_self.compData.callback) _self.compData.callback.call(this);
					}
				});

			});
		}, 10);
	}
});

//[链接地址]属性组件
var linkHtml = '<div class="layui-form-item component-links">';
		linkHtml += '<label class="layui-form-label sm">{{myData[0].label}}</label>';
		linkHtml += '<div class="layui-input-block">';
			linkHtml += '<span class="sm" :class="{ \'text-color\': myData[0].field.title}" @click="selected()"><span :title="myData[0].field.title">{{myData[0].field.title ? myData[0].field.title : "请选择链接"}}</span><i class="layui-icon layui-icon-right"></i></span>';
		linkHtml += '</div>';
	linkHtml += '</div>';

/**
 * 链接组件：
 * 参数说明：data：链接对象，callback：回调
 */
Vue.component("nc-link", {
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: null,// 链接对象
					label: "链接地址",// 文本
				};
			}
		},
		callback: null,
	},
	template: linkHtml,
	data: function () {
		return {
			myData: [this.data], // 此处用数组的目的是触发变异方法，进行视图更新
		};
	},
	created: function () {
		if (this.data.label == undefined) this.data.label = "链接地址";
	},
	methods: {
		//设置链接地址
		set: function (link) {
			//由于Vue2.0是单向绑定的：子组件无法修改父组件，但是可以修改单个属性，循环遍历属性赋值

			if (this.myData[0].field) {
				for (var k in this.myData[0].field) delete this.myData[0].field[k];
				for (var k in link) this.myData[0].field[k] = link[k];
			}

			//触发变异方法，进行视图更新
			this.myData.push({});
			this.myData.pop();
		},
		selected: function () {
			var $self = this;
			ns.select_link($self.myData[0].field, function (data) {
				$self.set(data);
				if ($self.callback) $self.callback.call(this, data);
			});
		}
	}
});

//[颜色]属性组件
var colorHtml = '<div class="layui-form-item flex">';
		colorHtml += '<div class="flex_left">';
			colorHtml += '<label class="layui-form-label sm">{{compData.label}}</label>';
			colorHtml += '<slot></slot>';
			colorHtml += '<div class="curr-color">';
				colorHtml += '<span v-for="(item,index) in field">{{ parent[item] ? parent[item].toUpperCase() : "透明" }}</span>';
			colorHtml += '</div>';
			colorHtml += '<slot name="right"></slot>';
		colorHtml += '</div>';
		colorHtml += '<div class="layui-input-block flex_fill">';
			colorHtml += '<div :id="item" v-for="(item,index) in ids" class="picker colorSelector"><div :style="{ background : parent[field[index]] }"></div></div>';
			colorHtml += '<span class="color-selector-reset text-color" @click="reset()">重置</span>';
		colorHtml += '</div>';
	colorHtml += '</div>';

/**
 * 颜色组件：
 * 参数说明：
 * data：{ field : 字段名, value : 值(默认:#303133), 'label' : 文本标签(默认:文字颜色) }
 */
Vue.component("color", {
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: "textColor",
					label: "文字颜色",
					defaultColor: "",
					value: "",
					callback: null
				};
			}
		},
	},
	template: colorHtml,
	data: function () {
		return {
			compData: this.data,
			field: [], // 支持多个颜色
			defaultColor: [], // 支持多个颜色值
			ids: [],
			parent: this.$parent.data ? ((Object.keys(this.$parent.data).length) ? this.$parent.data : this.$parent.global) : this.$parent.global,
		};
	},
	created: function () {
		this.bindColor();
	},
	methods: {
		init: function () {
			this.compData = this.data;
			if (this.compData.field === undefined) this.compData.field = "textColor";
			if (this.compData.label === undefined) this.compData.label = "文字颜色";
			if (this.compData.value === undefined) this.compData.value = "#303133";
			if (this.compData.defaultColor === undefined) this.compData.defaultColor = "";

			// 检查是否有多个颜色
			if (this.compData.field.indexOf(',') !== -1) this.field = this.compData.field.split(',');
			else this.field = [this.compData.field];

			// 检查是否有多个颜色
			if (this.compData.defaultColor.indexOf(',') !== -1) this.defaultColor = this.compData.defaultColor.split(',');
			else this.defaultColor = [this.compData.defaultColor];

			// 找寻父级
			if (this.compData.parent === 'global') this.parent = this.$parent.global;
			else if (this.compData.parent === 'template') this.parent = this.$parent.global.template;
			else if (this.compData.parent !== undefined) {
				if (typeof this.compData.parent == 'string') this.parent = this.$parent.data[this.compData.parent];
				if (typeof this.compData.parent == 'object') this.parent = this.compData.parent;
			}

			//如果当前字段没有值数据，则给予默认值，反之用该字段的值，用于优化调用该组件
			for (var i = 0; i < this.field.length; i++) {
				if (this.parent[this.field[i]] === undefined) this.$set(this.parent, this.field[i], this.compData.value);
				else this.compData.value = this.parent[this.field[i]];
				this.parent[this.field[i]] = this.compData.value;
			}

		},
		reset: function () {
			try {
				for (var i = 0; i < this.field.length; i++) {
					this.parent[this.field[i]] = this.defaultColor[i];
				}
			} catch (e) {
				console.log("color reset() ERROR:", e);
			}
		},
		bindColor() {
			this.init();
			for (var i = 0; i < this.field.length; i++) {
				var name = "colorSelector_" + this.field[i] + '_' + (this.compData.id || ns.gen_non_duplicate(5));
				this.ids.push(name);
				var $self = this;
				setColorPicker(this.parent[this.field[i]], name, function (hex, elem) {
					try {
						// data数据可能不全，所以要用compData，通过对象的引用关系绑定数据
						// 这里要更新$self，不然如果删除了，就会出问题
						var field = elem.attr('id').split('_')[1];
						if (hex) {
							$self.parent[field] = hex;
						} else {
							$self.parent[field] = "";
						}
					} catch (e) {
						console.log("color ERROR:" + e.message);
					}
				});
			}
		}
	}
});

/**
 * 生成颜色选择器
 * @param defaultColor
 * @param name
 * @param callBack
 */
function setColorPicker(defaultColor, name, callBack) {
	setTimeout(function () {
		Colorpicker.create({
			el: name,
			color: defaultColor,
			change: function (elem, hex) {
				$(elem).find("div").css('background', hex);
				if (callBack) callBack(hex,$(elem));
			}
		});
		if (defaultColor) $("#" + name).find("div").css('background', defaultColor);
	}, 500);
}

//[文字大小]属性组件
var fontSizeHtml = '<div class="layui-form-item">';
		fontSizeHtml += '<label class="layui-form-label sm">{{d.label}}</label>';
		fontSizeHtml += '<div class="layui-input-block">';
			fontSizeHtml += '<div :class="{ \'layui-unselect layui-form-select\' : true, \'layui-form-selected\' : isShowFontSize }" @click="isShowFontSize=!isShowFontSize;">';
				fontSizeHtml += '<div class="layui-select-title">';
					fontSizeHtml += '<input type="text" placeholder="请选择" :value="list[selectIndex].text" readonly="readonly" class="layui-input layui-unselect">';
					fontSizeHtml += '<i class="layui-edge"></i>';
				fontSizeHtml += '</div>';
				fontSizeHtml += '<dl class="layui-anim layui-anim-upbit">';
					fontSizeHtml += '<dd v-for="(item,index) in list" :value="item.value" :class="{ \'layui-this\' : (parent[d.field]==item.value) }" @click.stop="parent[d.field]=item.value;isShowFontSize=false;selectIndex=index;">{{item.text}}</dd>';
				fontSizeHtml += '</dl>';
			fontSizeHtml += '</div>';
		fontSizeHtml += '</div>';
	fontSizeHtml += '</div>';

/**
 * 文字大小
 * 参数说明：
 * data：{ field : 字段名, value : 值(默认:14), 'label' : 文本标签(默认:文字大小) }
 */
Vue.component("font-size", {
	template: fontSizeHtml,
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: "fontSize",
					label: "文字大小",
					value: 14
				};
			}
		},
	},
	data: function () {
		return {
			d: this.data,
			isShowFontSize: false,
			selectIndex: 2, //当前选中的下标
			list: [],
			parent: (Object.keys(this.$parent.data).length) ? this.$parent.data : this.$parent.global,
		};
	},
	created: function () {

		if (this.data.field == undefined) this.data.field = "fontSize";

		if (this.data.label == undefined) this.data.label = "文字大小";

		if (this.data.value == undefined) this.data.value = 14;

		if (this.parent[this.data.field] == undefined) this.$set(this.parent, this.data.field, this.data.value);

		this.parent[this.data.field] = this.data.value;

		for (var i = 12; i <= 30; i++) this.list.push({value: i, text: i + "px"});
		for (var i = 0; i < this.list.length; i++) {
			if (this.list[i].value == this.data.value) {
				this.selectIndex = i;
				break;
			}
		}

	},
});

//[图片上传]组件
var imageHtml = '<div v-show="condition" class="img-block layui-form text-color" :id="id" :class="{ \'has-choose-image\' : (myData.data[myData.field]) }">';
		imageHtml += '<div>';
			imageHtml += '<template v-if="myData.data[myData.field]">';
				imageHtml += '<img :layer-src="changeImgUrl(myData.data[myData.field])" :src="changeImgUrl(myData.data[myData.field])" :id="id+\'-img\'"/>';
				imageHtml += '<div class="operation">';
					imageHtml += '<div class="operation-warp">';
					imageHtml += '<i title="图片预览" class="iconfont iconreview js-preview" @click="previewImg(id)"></i>';
					imageHtml += '<i title="删除图片" class="layui-icon layui-icon-delete js-delete" @click="del()"></i>';
					imageHtml += '</div>';
					imageHtml += '<div class="replace_img js-replace" @click="uploadImg()">点击替换</div>';
				imageHtml += '</div>';
			imageHtml += '</template>';

			imageHtml += '<template v-else>';
				imageHtml += '<i class="add" @click="uploadImg()">+</i>';
				imageHtml += '<i class="del" @click.stop="del()" data-disabled="1" v-if="myData.isShow">x</i>';
			imageHtml += '</template>';
		imageHtml += '</div>';
	imageHtml += '</div>';

/**
 * 图片上传
 * 参数说明：
 * data：{ field : 字段名, value : 值(默认:14), 'label' : 文本标签(默认:文字大小) ,isShow: 控制右上角显隐，注意使用场景（一张图片且需要删除时使用，参考拼团、限时折扣等背景）}
 */
Vue.component("img-upload", {
	template: imageHtml,
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					data: {},
					field: "imageUrl",
					callback: null
				};
			}
		},
		condition: {
			type: Boolean,
			default: true
		}
	},
	data: function () {
		return {
			myData: this.data,
			upload: null,
			id: ns.gen_non_duplicate(10),
			// parent: (Object.keys(this.$parent.data).length) ? this.$parent.data : this.data,
		};
	},
	created: function () {
		if (this.myData.field === undefined) this.myData.field = "imageUrl";
		this.id = ns.gen_non_duplicate(10);
	},
	methods: {
		del: function () {
			this.myData.data[this.myData.field] = "";
		},
		//转换图片路径
		changeImgUrl: function (url) {
			if (url == null || url === "") return '';
			if (url.indexOf("static/img/") > -1) return ns.img(ns_url.staticImg + "/" + url.replace("public/static/img/", ""));
			else return ns.img(url);
		},
		uploadImg: function () {
			var self = this;
			openAlbum(function (obj) {
				for (var i = 0; i < obj.length; i++) {
					self.myData.data[self.myData.field] = obj[i].pic_path;
					self.myData.data.imgWidth = obj[i].pic_spec.split("*")[0];
					self.myData.data.imgHeight = obj[i].pic_spec.split("*")[1];
					if (self.myData.callback) self.myData.callback.call(this);
					loadImgMagnify();
				}
			}, 1);
		},
		previewImg(id){
			$('#'+id).find('div>img').click();
		},
	}
});

//[视频上传]组件
var videoHtml = '<div style="position: relative" class="video-add-box" @click="uploadImg()">';
		videoHtml += '<div class="img-block layui-form text-color">';
			videoHtml += '<template v-if="myData.data[myData.field]">';
				videoHtml += '<video :src="changeImgUrl(myData.data[myData.field])" controls/></video>';
				videoHtml += '<span>更换视频</span>';
			videoHtml += '</template>';
			videoHtml += '<template v-else>';
				videoHtml += '<div>';
					videoHtml += '<i class="add add-video">+</i>';
				videoHtml += '</div>';
			videoHtml += '</template>';
		videoHtml += '</div>';
	videoHtml += '</div>';

/**
 * 视频上传
 * 参数说明：
 * data：{ field : 字段名, value : 值(默认:14), 'label' : 文本标签(默认:文字大小) }
 */
Vue.component("video-upload", {
	template: videoHtml,
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					data: {},
					field: "videoUrl",
					callback: null
				};
			}
		}
	},
	data: function () {
		return {
			myData: this.data,
		};
	},
	created: function () {
		if (this.myData.field === undefined) this.myData.field = "videoUrl";
	},
	methods: {
		//转换图片路径
		changeImgUrl: function (url) {
			if (url == null || url === "") return '';
			else return ns.img(url);
		},
		uploadImg: function () {
			var self = this;
			openAlbum(function (obj) {
				self.myData.data[self.myData.field] = obj[0].pic_path;
				if (self.myData.callback) self.myData.callback.call(this);
			}, 1,0,'video');
		}
	}
});

var imgIconHtml = `
	<div>
		<div class="img-upload icon-img-upload" v-if="!myData.data.iconType || myData.data.iconType == 'img'" :id="id">
			<template v-if="myData.data[myData.field]">
				<img :src="changeImgUrl(myData.data[myData.field])" :layer-src="changeImgUrl(myData.data[myData.field])"/>
				<div class="operation">
					<div class="operation-warp">
						<i title="图片预览" class="iconfont iconreview js-preview" @click="previewImg()"></i>
						<i title="删除图片" class="layui-icon layui-icon-delete" @click="del"></i>
					</div>
					<div class="js-replace" @click="uploadImg">点击替换</div>
				</div>
			</template>
			<template v-else>
				<i class="add" @click="uploadImg">+</i>
			</template>
		</div>	
		<div class="icon-upload icon-img-upload icon-box" v-if="myData.data.iconType && myData.data.iconType == 'icon'">
			<template v-if="myData.data[myData.iconField]">
				<iconfont :icon="myData.data[myData.iconField]" :value="myData.data.style ? myData.data.style : null"></iconfont>
				<div class="operation">
					<div class="operation-warp">
						<i title="图标预览" class="iconfont iconreview js-preview"></i>
						<i title="删除图标" class="layui-icon layui-icon-delete" @click="del"></i>
					</div>
					<div class="js-replace" @click="uploadImg">点击替换</div>
				</div>
			</template>
			<template v-else>
				<i class="add" @click="uploadImg">+</i>
			</template>
		</div>
	</div>
`;

/**
 * 图片或图标选择
 * 参数说明：
 * data：{ field : 字段名, value : 值(默认:14), 'label' : 文本标签(默认:文字大小) ,isShow: 控制右上角显隐，注意使用场景（一张图片且需要删除时使用，参考拼团、限时折扣等背景）}
 */
Vue.component("img-icon-upload", {
	template: imgIconHtml,
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					data: {},
					field: "imageUrl",
					iconField: "icon",
					displayType : 'img,icon',
					callback: null
				};
			}
		}
	},
	data: function () {
		return {
			myData: this.data,
			upload: null,
			id: ns.gen_non_duplicate(10),
		};
	},
	created: function () {
		if (this.myData.field === undefined) this.myData.field = "imageUrl";
		if (this.myData.iconField === undefined) this.myData.iconField = "icon";
		this.id = ns.gen_non_duplicate(10);
	},
	methods: {
		del: function () {
			if (this.myData.data.iconType && this.myData.data.iconType == 'icon') this.myData.data[this.myData.iconField] = '';
			else this.myData.data[this.myData.field] = "";
			this.$forceUpdate();
		},
		//转换图片路径
		changeImgUrl: function (url) {
			if (url == null || url === "") return '';
			if (url.indexOf("static/img/") > -1) return ns.img(ns_url.staticImg + "/" + url.replace("public/static/img/", ""));
			else return ns.img(url);
		},
		uploadImg: function () {
			var self = this;
			if(self.data.displayType === undefined) self.data.displayType ='img,icon';
			openAlbum(function (obj) {
				if (typeof obj == 'object') {
					self.myData.data[self.myData.field] = obj[0].pic_path;
					self.myData.data.imgWidth = obj[0].pic_spec.split("*")[0];
					self.myData.data.imgHeight = obj[0].pic_spec.split("*")[1];
					self.myData.data.iconType = 'img';
					loadImgMagnify();
				} else {
					self.myData.data[self.myData.iconField] = obj;
					self.myData.data.iconType = 'icon';
					if (!self.myData.data.style) {
						self.$set(self.myData.data, 'style', {
							fontSize: 60,
							iconBgColor: ["#7b00ff"],
							iconBgColorDeg: 180,
							iconBgImg: 'public/static/ext/diyview/img/icon_bg/bg_05.png',
							bgRadius: 38,
							iconColor: ['#fff'],
							iconColorDeg: 0,
						})
					}
				}
				self.$forceUpdate();
			}, 1, 0, 'img', self.data.displayType);
		},
		previewImg(){
			$('#'+this.id).find('img').click();
		}
	}
});

var commonSetHtml = '<div class="common-set" v-if="ignore.indexOf(\'pageBgColor\') == -1 || ignore.indexOf(\'componentBgColor\') == -1 || (ignore.indexOf(\'marginTop\') == -1 || ignore.indexOf(\'marginBottom\') == -1 || ignore.indexOf(\'marginBoth\') == -1) || ignore.indexOf(\'componentAngle\') == -1">';

	commonSetHtml += '<div class="template-edit-title">';
		commonSetHtml += '<h3>组件样式</h3>';

		commonSetHtml += '<template v-if="ignore.indexOf(\'pageBgColor\') == -1 || ignore.indexOf(\'componentBgColor\') == -1">';
			commonSetHtml += '<color v-if="ignore.indexOf(\'pageBgColor\') == -1" :data="{ field : \'pageBgColor\', \'label\' : \'底部背景\' }"></color>';
			commonSetHtml += '<p class="word-aux">底部背景包含边距和圆角</p>';
			commonSetHtml += '<color v-if="ignore.indexOf(\'componentBgColor\') == -1" :data="{ field : \'componentBgColor\', \'label\' : \'组件背景\' }"></color>';
		commonSetHtml += '</template>';

		commonSetHtml += '<template v-if="ignore.indexOf(\'marginTop\') == -1 || ignore.indexOf(\'marginBottom\') == -1 || ignore.indexOf(\'marginBoth\') == -1">';
			commonSetHtml += '<slide v-if="ignore.indexOf(\'marginTop\') == -1" :data="{ field : \'margin.top\', label : \'上边距\' }"></slide>';
			commonSetHtml += '<slide v-if="ignore.indexOf(\'marginBottom\') == -1" :data="{ field : \'margin.bottom\', label : \'下边距\' }"></slide>';
			commonSetHtml += '<slide v-if="ignore.indexOf(\'marginBoth\') == -1" :data="{ field : \'margin.both\', label : \'左右边距\', max : 20 }"></slide>';
		commonSetHtml += '</template>';

		commonSetHtml += '<template v-if="ignore.indexOf(\'componentAngle\') == -1">';
			commonSetHtml += '<slide v-show="data.componentAngle == \'round\'" :data="{ field : \'topAroundRadius\', label : \'上圆角\', max : 50 }"></slide>';
			commonSetHtml += '<slide v-show="data.componentAngle == \'round\'" :data="{ field : \'bottomAroundRadius\', label : \'下圆角\', max : 50 }"></slide>';
		commonSetHtml += '</template>';

	commonSetHtml += '</div>';

commonSetHtml += '</div>';

/**
 * 公共属性设置
 */
Vue.component("common-set", {
	template: commonSetHtml,
	props: {
		// 忽略属性【textColor（文本颜色）、backgroundColor（背景色）、marginTop（上边距）、marginBottom（下边距）、marginBoth（左右边距）、componentAngle（圆角展示）】
		ignore: {
			type: Array,
			default: function () {
				return [];
			}
		},
		callback: {
			type: Function,
			default: function () {
			}
		}
	},
	data: function () {
		return {
			data: this.$parent.data,
			angleList: [
				{
					text: "直角",
					value: "right",
					src: 'icongl-square'
				},
				{
					text: "圆角",
					value: "round",
					src: 'iconyuanjiao'
				}
			],
		}
	},
	created: function () {
	},
	methods: {
		triggerCallback(){
			if(this.callback) this.callback(this);
		}
	},
});

var popWindowHtml = '<div class="pop-window-wrap" :class="{selected : currentIndex==-98}" :data-sort="-98" v-show="currentIndex==-98">';
		popWindowHtml += '<div class="edit-attribute">';
			popWindowHtml += '<div class="attr-wrap">';
				popWindowHtml += '<div class="restore-wrap">';
					popWindowHtml += '<h2 class="attr-title">弹窗广告</h2>';
					popWindowHtml += '<div class="layui-form-item checkbox-wrap custom-popup">';
						popWindowHtml += '<label class="layui-form-label sm">是否开启</label>';
						popWindowHtml += '<div class="layui-input-block">';
							popWindowHtml += '<span v-if="global.popWindow.count == -1">关闭</span>';
							popWindowHtml += '<span v-else>开启</span>';
							popWindowHtml += '<div v-if="global.popWindow.count != -1" @click="global.popWindow.count = -1" class="layui-unselect layui-form-checkbox layui-form-checked" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
							popWindowHtml += '<div v-else @click="global.popWindow.count = 1" class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
						popWindowHtml += '</div>';
					popWindowHtml += '</div>';

					popWindowHtml += '<template v-if="global.popWindow.count != -1">';
						// popWindowHtml += '<div class="template-edit-title">';
						// 	popWindowHtml += '<h3>弹出形式</h3>';
						// popWindowHtml += '</div>';
						popWindowHtml += '<div class="layui-form-item">';
							popWindowHtml += '<label class="layui-form-label sm"></label>';
							popWindowHtml += '<div class="layui-input-block">';
								popWindowHtml += '<div @click="global.popWindow.count=1" :class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : (global.popWindow.count==1) }">';
									popWindowHtml += '<i class="layui-anim layui-icon">{{ global.popWindow.count == 1 ? \'&#xe643;\':\'&#xe63f;\' }}</i>';
									popWindowHtml += '<div>首次弹出</div>';
								popWindowHtml += '</div>';

								popWindowHtml += '<div @click="global.popWindow.count=0" :class="{ \'layui-unselect layui-form-radio\' : true,\'layui-form-radioed\' : (global.popWindow.count==0) }">';
									popWindowHtml += '<i class="layui-anim layui-icon">{{ global.popWindow.count == 0 ? \'&#xe643;\':\'&#xe63f;\' }}</i>';
									popWindowHtml += '<div>每次弹出</div>';
								popWindowHtml += '</div>';
							popWindowHtml += '</div>';
						popWindowHtml += '</div>';

					popWindowHtml += '</template>';

					popWindowHtml += '<div class="layui-form-item">';
						popWindowHtml += '<label class="layui-form-label sm">广告图</label>';
						popWindowHtml += '<div class="layui-input-block img-upload">';
							popWindowHtml += '<template v-if="globalLazyLoad">';
								popWindowHtml += '<img-upload :data="{ data : global.popWindow, field : \'imageUrl\', text: \'\', isShow:true }"></img-upload>';
							popWindowHtml += '</template>';
						popWindowHtml += '</div>';
						popWindowHtml += '<div class="word-aux diy-word-aux">建议上传图片大小：290px * 410px</div>';
					popWindowHtml += '</div>';

					popWindowHtml += '<template v-if="globalLazyLoad">';
						popWindowHtml += '<nc-link :data="{ field : global.popWindow.link, parent : \'global\' , label : \'广告链接\' }"></nc-link>';
					popWindowHtml += '</template>';

				popWindowHtml += '</div>';
			popWindowHtml += '</div>';
		popWindowHtml += '</div>';
	popWindowHtml += '</div>';

/**
 * 弹出广告组件
 */
Vue.component("pop-window", {
	template: popWindowHtml,
	props: ['currentIndex', 'global','globalLazyLoad'],
	data: function () {
		return {}
	},
	created: function () {
		// console.log('popWindow', this.currentIndex, this.global);
	},
	methods: {}
});

var pageSetHtml = '<div class="edit-attribute">';
		pageSetHtml += '<div class="attr-wrap">';
			pageSetHtml += '<div class="restore-wrap">';
				pageSetHtml += '<div class="attr-title">';
					pageSetHtml += '<span class="title">页面设置</span>';
					pageSetHtml += '<div class="tab-wrap" style="display: flex;">';
						pageSetHtml += '<span class="active bg-color" data-type="content">内容</span>';
						pageSetHtml += '<span data-type="style">样式</span>';
					pageSetHtml += '</div>';
				pageSetHtml += '</div>';

				pageSetHtml += '<div class="edit-content-wrap">';

					pageSetHtml += '<div class="template-edit-title">';
						pageSetHtml += '<h3>页面内容</h3>';
						pageSetHtml += '<div class="layui-form-item">';
							pageSetHtml += '<label class="layui-form-label sm">页面名称</label>';
							pageSetHtml += '<div class="layui-input-block">';
								pageSetHtml += '<input type="text" v-model="global.title" placeholder="请输入页面名称" class="layui-input" maxlength="15">';
							pageSetHtml += '</div>';
						pageSetHtml += '</div>';
						pageSetHtml += '<div class="layui-form-item">';
							pageSetHtml += '<label class="layui-form-label sm">选择风格</label>';
							pageSetHtml += '<div class="layui-input-block">';
								pageSetHtml += '<div v-if="global.navStyle" class="text-color selected-style" @click="selectPageStyle()">风格{{global.navStyle}} <i class="layui-icon layui-icon-right"></i></div>';
								pageSetHtml += '<div v-else class="text-color selected-style" @click="selectPageStyle()">选择 <i class="layui-icon layui-icon-right"></i></div>';
							pageSetHtml += '</div>';
						pageSetHtml += '</div>';

						pageSetHtml += '<div class="layui-form-item" v-if="global.navStyle == 3 || global.navStyle == 2">';
							pageSetHtml += '<label class="layui-form-label sm">图片</label>';
							pageSetHtml += '<div class="layui-input-block img-upload" v-if="globalLazyLoad">';
								pageSetHtml += '<img-upload :data="{ data : global, field : \'topNavImg\', text: \'\',isShow:true }"></img-upload>';
							pageSetHtml += '</div>';
						pageSetHtml += '</div>';
						pageSetHtml += '<div class="word-aux diy-word-aux" v-if="global.navStyle == 2">宽度自适应（最大150px），高度28px</div>';
						pageSetHtml += '<div class="word-aux diy-word-aux" v-if="global.navStyle == 3">宽度自适应（85px），高度30px</div>';

						pageSetHtml += '<template v-if="globalLazyLoad">';
							pageSetHtml += '<nc-link :data="{ field : global.moreLink }"></nc-link>';
						pageSetHtml += '</template>';

						pageSetHtml += '<div class="layui-form-item icon-radio" v-if="global.navStyle == 1">';
							pageSetHtml += '<label class="layui-form-label sm">展示位置</label>';
							pageSetHtml += '<div class="layui-input-block">';
								pageSetHtml += '<template v-for="(item, index) in textImgPositionList">';
									pageSetHtml += '<span :class="{\'layui-hide\':item.value != global.textImgPosLink}">{{item.text}}</span>';
								pageSetHtml += '</template>';
								pageSetHtml += '<ul class="icon-wrap">';
									pageSetHtml += '<li v-for="(item, index) in textImgPositionList" :class="{\'text-color border-color\':item.value == global.textImgPosLink}" @click="global.textImgPosLink = item.value">';
										pageSetHtml += '<i class="iconfont" :class="[item.src]"></i>';
									pageSetHtml += '</li>';
								pageSetHtml += '</ul>';
							pageSetHtml += '</div>';
						pageSetHtml += '</div>';

					pageSetHtml += '<div class="layui-form-item checkbox-wrap">';
						pageSetHtml += '<label class="layui-form-label sm">导航栏</label>';
						pageSetHtml += '<div class="layui-input-block">';
							pageSetHtml += '<span v-if="global.navBarSwitch == true">显示</span>';
							pageSetHtml += '<span v-else>隐藏</span>';
							pageSetHtml += '<div v-if="global.navBarSwitch == true" @click="global.navBarSwitch = false" class="layui-unselect layui-form-checkbox layui-form-checked" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
							pageSetHtml += '<div v-else @click="global.navBarSwitch = true" class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
						pageSetHtml += '</div>';
						pageSetHtml += '<div class="word-aux diy-word-aux">此处控制当前页面导航栏是否显示</div>';
					pageSetHtml += '</div>';

					pageSetHtml += '</div>';

					pageSetHtml += '<div class="template-edit-title">';
						pageSetHtml += '<h3>底部导航</h3>';

						pageSetHtml += '<div class="layui-form-item checkbox-wrap">';
							pageSetHtml += '<label class="layui-form-label sm">底部导航</label>';
							pageSetHtml += '<div class="layui-input-block">';
								pageSetHtml += '<span v-if="global.openBottomNav == true">显示</span>';
								pageSetHtml += '<span v-else>隐藏</span>';
								pageSetHtml += '<div v-if="global.openBottomNav == true" @click="global.openBottomNav = false" class="layui-unselect layui-form-checkbox layui-form-checked" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
								pageSetHtml += '<div v-else @click="global.openBottomNav = true" class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
							pageSetHtml += '</div>';
							pageSetHtml += '<div class="word-aux diy-word-aux">此处控制当前页面底部导航菜单是否显示</div>';
						pageSetHtml += '</div>';

					pageSetHtml += '</div>';

				pageSetHtml += '</div>';

				pageSetHtml += '<div class="edit-style-wrap" style="display: none;">';
					pageSetHtml += '<div class="template-edit-title">';
						pageSetHtml += '<h3>显示样式</h3>';
						pageSetHtml += '<template v-if="globalLazyLoad">';
							pageSetHtml += '<color :data="{ field : \'pageBgColor\', label : \'页面颜色\', value : global.pageBgColor, defaultColor : \'#FFFFFF\', parent: \'global\' }"></color>';
							pageSetHtml += '<color :data="{ field : \'topNavColor\', label : \'顶部颜色\', value : global.topNavColor, defaultColor : \'#FFFFFF\', parent: \'global\' }"></color>';
							pageSetHtml += '<color :data="{ field : \'textNavColor\', label : \'标题颜色\', value : global.textNavColor, defaultColor : \'#303133\', parent: \'global\' }"></color>';
						pageSetHtml += '</template>';

						pageSetHtml += '<div class="layui-form-item checkbox-wrap">';
							pageSetHtml += '<label class="layui-form-label sm">顶部透明</label>';
							pageSetHtml += '<div class="layui-input-block">';
								pageSetHtml += '<span v-if="global.topNavBg == true">是</span>';
								pageSetHtml += '<span v-else>否</span>';
								pageSetHtml += '<div v-if="global.topNavBg == true" @click="global.topNavBg = false" class="layui-unselect layui-form-checkbox layui-form-checked" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
								pageSetHtml += '<div v-else @click="global.topNavBg = true" class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
							pageSetHtml += '</div>';
						pageSetHtml += '</div>';

						pageSetHtml += '<div class="layui-form-item">';
							pageSetHtml += '<label class="layui-form-label sm">背景图片</label>';
							pageSetHtml += '<div class="layui-input-block img-upload" v-if="globalLazyLoad">';
								pageSetHtml += '<img-upload :data="{ data : global, field : \'bgUrl\', text: \'\', isShow:true }"></img-upload>';
							pageSetHtml += '</div>';
						pageSetHtml += '</div>';
					pageSetHtml += '</div>';

					pageSetHtml += '<div class="template-edit-title">';
						pageSetHtml += '<h3>边距设置</h3>';
						pageSetHtml += '<template v-if="globalLazyLoad">';
							pageSetHtml += '<slide :data="{ field : \'margin.both\', label : \'左右边距\', parent: \'template\', max : 20 }"></slide>';
						pageSetHtml += '</template>';
					pageSetHtml += '</div>';
				pageSetHtml += '</div>';

				// pageSetHtml += '<div class="template-edit-title">';
				// 	pageSetHtml += '<h3>小程序收藏</h3>';
				// pageSetHtml += '</div>';
				//
				// pageSetHtml += '<div class="template-edit-wrap">';
				// 	pageSetHtml += '<div class="layui-form-item checkbox-wrap">';
				// 		pageSetHtml += '<label class="layui-form-label sm">显示状态</label>';
				// 		pageSetHtml += '<div class="layui-input-block">';
				// 			pageSetHtml += '<span v-if="global.mpCollect == true">显示</span>';
				// 			pageSetHtml += '<span v-else>隐藏</span>';
				// 			pageSetHtml += '<div v-if="global.mpCollect == true" @click="global.mpCollect = false" class="layui-unselect layui-form-checkbox layui-form-checked" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
				// 			pageSetHtml += '<div v-else @click="global.mpCollect = true" class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
				// 		pageSetHtml += '</div>';
				// 		pageSetHtml += '<div class="word-aux diy-word-aux">首次进入小程序是否显示添加小程序提示</div>';
				// 	pageSetHtml += '</div>';
				// pageSetHtml += '</div>';

			pageSetHtml += '</div>';
		pageSetHtml += '</div>';
	pageSetHtml += '</div>';

/**
 * 页面设置组件
 */
Vue.component("page-set", {
	template: pageSetHtml,
	props: ['currentIndex', 'global','globalLazyLoad'],
	data: function () {
		return {
			data: this.$parent.data,
			appModule: ns.appModule,
			templateType: $("#template_type").val(),
			textImgPositionList: [
				{
					text: "居左",
					value: "left",
					src: "iconzuoduiqi"
				},
				{
					text: "居中",
					value: "center",
					src: "iconjuzhongduiqi"
				}
			],
			angleList: [
				{
					text: "直角",
					value: "right",
					src: 'icongl-square'
				},
				{
					text: "圆角",
					value: "round",
					src: 'iconyuanjiao'
				}
			],
		}
	},
	created: function () {
	},
	methods: {
		//选择页面顶部风格，默认文字、图片+文字、图片+搜索框、定位门店
		selectPageStyle: function () {
			var html = '<div class="nav-style">';
			for (var i = 0; i < 3; i++) {
				html += '<div class="text-title' + ((this.global.navStyle == (i + 1)) ? ' border-color' : '') + '" data-style="' + (i + 1) + '">';
					html += '<img src="' + ns_url.staticExt + '/diyview/img/nav_style/nav_style' + i + '.png"/>';
				html += '</div>';
			}
			// 门店插件存在则显示
			if(storeIsExit == 1) {
				html += '<div class="text-title' + ((this.global.navStyle == 4) ? ' border-color' : '') + '" data-style="4">';
					html += '<img src="' + ns_url.staticExt + '/diyview/img/nav_style/nav_style_store.png"/>';
				html += '</div>';
			}
			html += '</div>';
			layer.open({
				type: 1,
				title: '风格选择',
				area: ['800px', '380px'],
				btn: ['确定', '返回'],
				content: html,
				success: function (layero, index) {
					$('.nav-style .text-title').click(function () {
						$(this).addClass('border-color').siblings().removeClass('border-color');
					});
				},
				yes: function (index, layero) {
					changeStyle($('.nav-style .text-title.border-color').attr('data-style'));
					layer.closeAll();
				}
			});
		}
	}
});

//导航样式切换
function changeStyle(val) {
	$('.text-title:nth-child(' + (val) + ')').addClass('border-color').siblings().removeClass('border-color');
	vue.global.navStyle = val;
	// if (val == 4) vue.global.topNavColor = '#FF6A00';
	// vue.$forceUpdate()
}

var tabbarHtml = '<div :class="[\'nav-tabbar\',\'style-\' + global.navStyle,global.textImgPosLink]">';
	tabbarHtml += '<div v-if="global.navStyle == 1" class="preview-head-div" :style="{ backgroundColor : global.topNavColor,color:global.textNavColor,textAlign:global.textImgPosLink}">';
		tabbarHtml += '<span>{{global.title}}</span>';
	tabbarHtml += '</div>';

	tabbarHtml += '<div v-if="global.navStyle == 2" class="preview-head-div" :style="{ backgroundColor : global.topNavColor,color:global.textNavColor}">';
			tabbarHtml += '<img :src="changeImgUrl(global.topNavImg)">';
			tabbarHtml += '<span>{{global.title}}</span>';
	tabbarHtml += '</div>';

	tabbarHtml += '<div v-if="global.navStyle == 3" class="preview-head-div" :style="{ backgroundColor : global.topNavColor,color:global.textNavColor}">';
		tabbarHtml += '<div class="img-text-search">';
			tabbarHtml += '<img :src="changeImgUrl(global.topNavImg)">';
			tabbarHtml += '<div class="top-search-box">';
				tabbarHtml += '<i class="iconfont iconsousuo"></i><span style="line-height: 1">请输入商品名称</span>';
			tabbarHtml += '</div>';
		tabbarHtml += '</div>';
	tabbarHtml += '</div>';

	tabbarHtml += '<div v-if="global.navStyle == 4" class="preview-head-div" :style="{ backgroundColor : global.topNavColor,color:global.textNavColor}">';
		tabbarHtml += '<i class="iconfont icondingwei"></i>';
		tabbarHtml += '<span>门店名称</span>';
		tabbarHtml += '<i class="iconfont iconyoujiantou"></i>';
	tabbarHtml += '</div>';

tabbarHtml += '</div>';

/**
 * 顶部tabbar组件
 */
Vue.component("tabbar", {
	template: tabbarHtml,
	props: ['global'],
	data: function () {
		return {}
	},
	created: function () {},
	methods: {
		//转换图片路径
		changeImgUrl: function (url) {
			if (url == null || url === "") return '';
			if (url.indexOf("static/img/") > -1) return ns.img(ns_url.staticImg + "/" + url.replace("public/static/img/", ""));
			else return ns.img(url);
		},
	}
});


//[Icon组件]属性组件
var iconHtml = '<div class="layui-form-item component-icon">';
		iconHtml += '<label class="layui-form-label sm">{{data.label || "图标"}}</label>';
		iconHtml += '<div class="layui-input-block">';
			iconHtml += '<span class="sm" @click="selected()"><i :class="[\'text-color\',{\'iconfont iconjia\': !parent[data.field] },{\'js-icon\': parent[data.field]},parent[data.field]]" :style="{\'color\': parent[data.field] && parent[data.color] + \'!important\'}"></i></span>';
		iconHtml += '</div>';
	iconHtml += '</div>';


/**
 * 选择Icon组件：
 * 参数说明：data：链接对象，callback：回调
 */
Vue.component("nc-icon", {
	props: {
		data: {
			type: Object,
			default: function () {
				return {
					field: null,// icon对象
					label: "图标",// 文本
					color: "" // 图标颜色
				};
			}
		}
	},
	template: iconHtml,
	data: function () {
		return {
			parent: (Object.keys(this.$parent.data).length) ? this.$parent.data : this.$parent.global
		};
	},
	created: function () {
		// 找寻父级
		if (typeof this.data.parent == "string") this.parent = this.$parent.data[this.data.parent];
		if (typeof this.data.parent == "object") this.parent = this.data.parent;
	},
	methods: {
		selected: function () {
			iconSelect((data)=>{
				this.parent[this.data.field] = data;
			},{icon: this.parent[this.data.field]})
		}
	}
});