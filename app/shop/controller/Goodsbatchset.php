<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy riht 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;

use app\model\goods\Batch;

/**
 * 实物商品
 * Class Goods
 * @package app\shop\controller
 */
class Goodsbatchset extends BaseShop
{

    /**
     *编辑商品价格(批量)
     */
    public function setPrice()
    {
        $type = input('type', '');// 金额 money 公式计算 calculate
        $price_type = input('price_type', '');//  价格字段  sale 销售价  cost 成本价  market 划线价
        $goods_ids = input('goods_ids', '');
        $calculate_price_type = input('calculate_price_type', '');//计算所用价格字段  sale 销售价  cost 成本价  market 划线价
        $price = input('price', 0);//设置的价格
        $sign = input('sign', '');//运算符号 add 加法subtract减法  multiply  乘法  division 除法
        $precise = input('precise', '');//精度  1 全部保留  2 抹分  3 抹角 4 四舍五入到分 5 四舍五入到角 6 四舍五入到元 7
        $calculate_price = input('calculate_price', 0);
        $params = array(
            'site_id' => $this->site_id,
            'type' => $type,
            'price_type' => $price_type,
            'goods_ids' => $goods_ids,
            'price' => $price,
            'sign' => $sign,
            'precise' => $precise,
            'calculate_price_type' => $calculate_price_type,
            'calculate_price' => $calculate_price
        );
        $batch_model = new Batch();
        $result = $batch_model->setPrice($params);
        return $result;
    }

}