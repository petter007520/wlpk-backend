<?php

namespace app\component\controller;

use app\model\goods\GoodsCategory;

/**
 * 商品列表·组件
 */
class GoodsList extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {
        $site_id = request()->siteid();
        $goods_category_model = new GoodsCategory();
        $category_condition = [
            [ 'site_id', '=', $site_id ]
        ];
        $category_list = $goods_category_model->getCategoryTree($category_condition)[ 'data' ];
        $this->assign("category_list", $category_list);

        return $this->fetch("goods_list/design.html");
    }
}