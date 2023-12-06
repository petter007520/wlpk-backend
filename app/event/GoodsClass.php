<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\event;

use app\model\goods\Goods;

/**
 * 商品类型
 */
class GoodsClass
{
    public function handle()
    {
        return [
            'goods_class' => (new Goods())->getGoodsClass()['id'],
            'goods_class_name' => (new Goods())->getGoodsClass()['name'],
            'is_virtual' => 0,
            'add_url' => 'shop/goods/addGoods',
            'edit_url' => 'shop/goods/editGoods'
        ];
    }

}