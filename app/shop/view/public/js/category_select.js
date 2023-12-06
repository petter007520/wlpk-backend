var layCascader, goodsCategory = [];
layui.use(['layCascader'], function () {
	layCascader = layui.layCascader;

	$('.goods-category-con-wrap .layui-block').each(function () {
		var category_id = $(this).find('.category_id').val();
		var _this = this;

		fetchCategory({elem: $(this).find('.select-category'), value: category_id ? parseInt(category_id.split(',').splice(-1)) : ''},
			function (value, node) {
				var categoryId = [];
				node.path.forEach(function (item) {
					categoryId.push(item.value)
				});
				$(_this).find('.category_id').val(categoryId.toString())
			}
		)
	})
});

// 刷新商品分类，更新选择商品分类数据
$('body').on('click', '.goods-category-con-wrap .js-refresh-category', function () {
	$.ajax({
		url : ns.url("shop/goodscategory/lists"),
		dataType: 'JSON',
		type: 'POST',
		async: false,
		success: function(res) {
			goodsCategory = res.data;
			var str = '<div class="category-list">';
					str += '<div class="item">';
						str += '<!--后续做搜索-->';
						str += '<ul>';
						if(goodsCategory.length) {
							for (var i=0;i<goodsCategory.length;i++) {
								var item = goodsCategory[i];
								str += `{{# if(d.category_id_1 == '${item.category_id}' ){ }}`;
								str += `<li data-category-id="${item.category_id}" data-commission-rate="${item.commission_rate}" data-level="${item.level}" class="selected">`;
								str += '{{# }else{ }}';
								str += `<li data-category-id="${item.category_id}" data-commission-rate="${item.commission_rate}" data-level="${item.level}">`;
								str += '{{# } }}';
									str += `<span class="category-name">${item.category_name}</span>`;
									str += '<span class="right-arrow"></span>';
								str += '</li>';
							}
						}
						str += '</ul>';
					str += '</div>';
					str += '<div class="item" data-level="2"><!--后续做搜索--><ul></ul></div>';
					str += '<div class="item" data-level="3"><!--后续做搜索--><ul></ul></div>';
				str += '</div>';
				str += '<div class="selected-category-wrap">';
					str += '<label>您当前选择的是：</label>';
					str += '<span class="js-selected-category"></span>';
				str += '</div>';

			$('#selectedCategory').html(str);

			// 刷新商品分类下拉框数据
			$('.goods-category-con-wrap .layui-block').each(function () {
				$(this).find('.el-cascader').remove(); // 清空渲染
				var category_id = $(this).find('.category_id').val();
				var _this = this;

				fetchCategory({elem: $(this).find('.select-category'), value: category_id ? parseInt(category_id.split(',').splice(-1)) : ''},
					function (value, node) {
						var categoryId = [];
						node.path.forEach(function (item) {
							categoryId.push(item.value)
						});
						$(_this).find('.category_id').val(categoryId.toString())
					}
				)
			})

		}
	});
});

$('body').on('click', '.goods-category-wrap-box .js-add-category', function () {
	if ($('.goods-category-con-wrap .layui-block').length >= 10) {
		layer.msg('最多添加十个分类');
		return;
	}
	var h = `<div class="layui-block">
		<div class="layui-input-inline cate-input-defalut">
			<input type="text" readonly lay-verify="required" autocomplete="off" class="layui-input len-mid select-category" />
			<input type="hidden" class="category_id" />
		</div>
		<a href="javascript:;" class="text-color js-delete-category">删除</a>
	</div>`;
	$('.goods-category-con-wrap').append(h);

	fetchCategory({elem: $('.goods-category-con-wrap .layui-block:last-child').find('.select-category')}, function (value, node) {
		var categoryId = [];
		node.path.forEach(function (item) {
			categoryId.push(item.value)
		});
		$('.goods-category-con-wrap .layui-block:last-child').find('.category_id').val(categoryId.toString());
	});
});

$('body').on('click', '.goods-category-con-wrap .js-delete-category', function () {
	$(this).parents('.layui-block').remove();
});

/**
 * 渲染分类选择
 * @param option
 * @param callback
 */
function fetchCategory(option, callback){
	if (!goodsCategory.length) {
		$.ajax({
			url : ns.url("shop/goodscategory/lists"),
			dataType: 'JSON',
			type: 'POST',
			async: false,
			success: function(res) {
				goodsCategory = res.data;
			}
		})
	}
	var _option = {
		options: goodsCategory,
		props: {
			value: 'category_id',
			label: 'category_name',
			children: 'child_list'
		}
	};
	if (option) Object.assign(_option, option);
	var _cascader = layCascader(_option);
	_cascader.changeEvent(function (value, node) {
		typeof callback == 'function' && callback(value, node)
	});
}