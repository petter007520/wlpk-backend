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

use app\model\goods\VirtualGoods;

/**
 * 商品类型
 */
class VirtualGoodsClass
{
    public function handle()
    {
        return [
            'goods_class' => (new VirtualGoods())->getGoodsClass()['id'],
            'goods_class_name' => (new VirtualGoods())->getGoodsClass()['name'],
            'is_virtual' => 1,
            'add_url' => 'shop/virtualgoods/addGoods',
            'edit_url' => 'shop/virtualgoods/editGoods'
        ];
    }

}