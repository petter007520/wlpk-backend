var link_url_json = $("input[name='link_url']").val();
if(link_url_json) {
	link_url_json = JSON.parse(link_url_json);
	$(".link-url-show").text(link_url_json.title);
}

var laytpl, form, layerIndex;
var categoryFullName = [];//组装名称

var saveData = null;
var totalUploadNum = 0;
var completeUploadNum = 0;

$(function () {
	
	//编辑时赋值组装名称
	if ($("input[name='category_full_name']").length > 0) {
		categoryFullName = $("input[name='category_full_name']").val().split("/").slice(0, $("input[name='category_full_name']").val().split("/").length - 1);
	}

	layui.use(['form', 'laytpl'], function () {
		var repeat_flag = false;//防重复标识
		laytpl = layui.laytpl;
		form = layui.form;
		
		/**
		 * 表单验证
		 */
		form.verify({
			commission_rate: function (value) {
				var reg = /^\d{0,2}(.?\d{0,2})$/;
				if (value.length > 0) {
					if (isNaN(value)) {
						return '佣金比率输入错误';
					}
					if (!reg.test(value) || value < 0 || value > 100) {
						return '佣金比率范围:0~100%';
					}
				}
			},
			num: function (value) {
				if (value == '') {
					return;
				}
				if (value % 1 != 0) {
					return '排序数值必须为整数';
				}
				if (value < 0) {
					return '排序数值必须为大于0';
				}
			}
		});

		var upload = new Upload({
			elem: '#imgUpload',
			size:100,
			auto:false,
			bindAction:'#imageUploadAction',
			callback: function(res) {
				uploadComplete('image', res.data.pic_path);
			}
		});

		var adv_upload = new Upload({
			elem: '#imgUploadAdv',
			auto:false,
			bindAction:'#imageAdvUploadAction',
			callback: function(res) {
				uploadComplete('image_adv', res.data.pic_path);
			}
		});

		function uploadComplete(field, pic_path) {
			saveData.field[field] = pic_path;
			completeUploadNum += 1;
			if(completeUploadNum == totalUploadNum){
				saveFunc();
			}
		}

		form.on('submit(save)', function (data) {

			saveData = data;
			var obj = $("img.img_prev[data-prev='1']");
			totalUploadNum = obj.length;
			if(totalUploadNum > 0){
				obj.each(function(){
					var actionId = $(this).attr('data-action-id');
					$(actionId).click();
				})
			}else{
				saveFunc();
			}

			return false;
		});

		function saveFunc(){

			var data = saveData;
			categoryFullName.push(data.field.category_name);
			data.field.category_full_name = categoryFullName.join("/");
			data.field.attr_class_name = $("select[name='attr_class_id'] option:checked").text();

			// 删除图片
			if(!data.field.image) upload.delete();

			if(!data.field.image_adv) adv_upload.delete();

			if (repeat_flag) return false;
			repeat_flag = true;

			var url = ns.url("shop/goodscategory/addCategory");
			if (data.field.category_id) url = ns.url("shop/goodscategory/editCategory");
			$.ajax({
				url: url,
				data: data.field,
				dataType: 'json',
				type: 'post',
				success: function (data) {
					layer.msg(data.message);
					if (data.code == 0) {
						location.href = ns.url("shop/goodscategory/lists");
					} else {
						repeat_flag = false;
					}
				}
			});
		}
		
		//保存上级分类
		form.on('submit(save_pid)', function (data) {
			
			var option_category_id_1 = $("select[name='category_id_1'] option:checked");
			var option_category_id_2 = $("select[name='category_id_2'] option:checked[value!='0']");
			
			categoryFullName = [];
			var level, category_name, pid;
			if (option_category_id_1.length) {
				level = parseInt(option_category_id_1.attr("data-level"));
				category_name = option_category_id_1.text();
				pid = option_category_id_1.val();//上级分类id
				var category_id_1 = option_category_id_1.val();//一级分类id
				if (category_id_1 > 0) {
					$("input[name='category_id_1']").val(category_id_1);
					categoryFullName.push(category_name);
				}
			}
			
			if($("input[name='category_name_1']").length){
				categoryFullName.push($("input[name='category_name_1']").val());
			}
			
			// 选中了二级商品分类
			if (option_category_id_2.length) {
				level = parseInt(option_category_id_2.attr("data-level"));
				category_name = option_category_id_2.text();
				pid = option_category_id_2.val();
				var category_id_2 = option_category_id_2.val();//二级分类id
				if (category_id_2 > 0) {
					$("input[name='category_id_2']").val(category_id_2);
					categoryFullName.push(category_name);
				}
			}

			$(".js-pid span").text(category_name);
			$("input[name='pid']").val(pid);
			$("input[name='level']").val(level + 1);//当前添加的层级+1
			
			layer.close(layerIndex);
			return false;
			
		});

		setTimeout(()=>{
			form.render();
		},600)
		
	});


	
});

//选择商品分类弹出框
function selectedCategoryPopup() {
	
	if ($("input[name='category_id']").length) {
		
		// 修改
		editSelectedPid();
		
	} else {
		
		//添加
		addSelectedPid();
		
	}
	
}

/**
 * 获取商品分类列表
 * @param data
 * @param callback
 */
function getCategoryList(data, callback) {
	$.ajax({
		url: ns.url("shop/goodscategory/getCategoryList"),
		data: data,
		dataType: 'json',
		type: 'post',
		async: false,
		success: function (res) {
			var data = res.data;
			if (callback) callback(data);
		}
	});
}

/**
 * 添加时，选择上级分类
 */
function addSelectedPid() {
	
	//查询一级商品分类
	getCategoryList({pid: 0}, function (list) {
		
		var html = $("#selectedCategory").html();
		var data = {
			category_id_1: $("input[name='category_id_1']").val(),
			category_list_1: list
		};
		laytpl(html).render(data, function (html) {
			layerIndex = layer.open({
				title: '选择商品分类',
				skin: 'layer-tips-class',
				type: 1,
				area: ['450px'],
				content: html,
				success: function () {
					form.render();
					
					form.on('select(category_id_1)', function (item) {
						
						if (item.value > 0) {
							
							getCategoryList({pid: item.value}, function (list) {
								var h = '<option value="0">请选择</option>';
								for (var i = 0; i < list.length; i++) {
									if ($("input[name='category_id_2']").val() == list[i].category_id) {
										h += '<option value="' + list[i].category_id + '" data-level="' + list[i].level + '" selected>' + list[i].category_name + '</option>';
									} else {
										h += '<option value="' + list[i].category_id + '" data-level="' + list[i].level + '">' + list[i].category_name + '</option>';
									}
									
								}
								
								$("select[name='category_id_2']").html(h);
								form.render("select");
							});
						} else {
							//顶级分类不需要查询
							$("select[name='category_id_2']").html('<option value="0">请选择</option>');
							form.render("select");
						}
						
					});
					
					$("select[name='category_id_1']").siblings("div.layui-form-select").find("dl dd[lay-value='" + $("input[name='category_id_1']").val() + "']").click();
					
				}
			});
		});
	});
}


/**
 * 编辑时，选择上级分类
 */
function editSelectedPid() {
	var html = $("#selectedCategory").html();
	laytpl(html).render({}, function (html) {
		layerIndex = layer.open({
			title: '选择商品分类',
			skin: 'select-category',
			type: 1,
			area: ['650px'],
			content: html,
			btn: ['确定', '取消'],
			success: function () {
				let pid = $('input[name="pid"]').val();
				let level = $('input[name="level"]').val();
				$('.table_div input[name="category_id"][data-category-id="'+pid+'"]').attr('checked', true);
				if(level == 3) $('.table_div div[data-cateid="'+pid+'"]').parents('.table_two_div').show().prev('.table_tr').find('.switch').attr('data-open', 1).html('-');
				form.render();

				$(".js-switch").click(function (event) {
					event.stopPropagation();
					var category_id = $(this).attr("data-category-id");
					var level = $(this).attr("data-level");
					var open = parseInt($(this).attr("data-open").toString());
					if(open){
						$(".goods-category-list .layui-table tr[data-category-id-"+ level+"='" + category_id + "']").hide();
						// $(this).children("img").removeClass('rotate');
						$(this).text("+");
						if(level == 1) $(this).parents('.table_tr').siblings('.table_two_div').hide();
						else if(level == 2) $(this).parents('.table_tr').siblings('.table_three').hide();

					}else{
						$(".goods-category-list .layui-table tr[data-category-id-"+ level+"='" + category_id + "']").show();
						$(this).text("-");
						// $(this).children("img").addClass('rotate');
						if(level == 1) $(this).parents('.table_tr').siblings('.table_two_div').show();
						else if(level == 2) $(this).parents('.table_tr').siblings('.table_three').show();

					}
					$(this).attr("data-open", (open ? 0 : 1));
				});

				form.on('checkbox(category)', function (data) {
					if(data.elem.checked==true){
						$('.table_move').children('div').removeClass('layui-form-checked');
						$(".table_move input").prop("checked",false);
					}
					$(this).parents('.table_move').children('div').addClass('layui-form-checked');
					$(this).parents('.table_move').find('input').prop("checked",true);
					return false;
				});

			},
			yes: function(index, layero){
				let obj = $('.table_div input[name="category_id"]:checked');
				let num = $(obj).length;
				if(num > 1){
					layer.msg('只能选择一个上级');
					return false;
				}

				let parent_level = $(obj).attr('data-level');
				let pid = $(obj).val();
				let parent_name = $(obj).attr('data-name')
				if(num < 1){
					parent_level = 0;
					pid = 0;
					parent_name = '顶级分类';
				}

				$.ajax({
					url: ns.url("shop/goodscategory/checkEditCategory"),
					data: {
						category_id: $('#category_id').val(),
						pid : pid
					},
					dataType: 'json',
					type: 'post',
					async: false,
					success: function (res) {
						if(res.code >= 0){
							$(".js-pid span").text(parent_name);
							$("input[name='pid']").val(pid);
							$("input[name='level']").val(parseInt(parent_level) + 1);//当前添加的层级+1
							layer.close(layerIndex);
						}else{
							layer.msg(res.message)
						}
					}
				});
			}
		});
	});
}

/**
 * 获取商品分类信息
 * @param category_id
 * @param callback
 */
function getCategoryInfo(category_id, callback) {
	$.ajax({
		url: ns.url("shop/goodscategory/getCategoryInfo"),
		data: {category_id: category_id},
		dataType: 'json',
		type: 'post',
		async: false,
		success: function (res) {
			var data = res.data;
			if (callback) callback(data);
		}
	});
}

function back() {
	location.href = ns.url("shop/goodscategory/lists")
}

function selectedLink() {
	if (link_url_json == "") {
		link_url_json = {};
	}
	ns.select_link(link_url_json, function (data) {
		for (var o in data) {
			if (data[o] == null) delete data[o];
		}
		$("input[name='link_url']").val(JSON.stringify(data));
		$(".link-url-show-wrap .layui-input-block").find('.link-url-show').remove();
		$(".link-url-show-wrap .layui-input-block").prepend(`<span class="link-url-show">${data.title}</span>`);
	});
}