<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\order;

use app\model\BaseModel;

/**
 * 商品
 */
class OrderGoods extends BaseModel
{
    /**
     * 获取商品sku分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getOrderGoodsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*', $alias = '', $join = '')
    {
        $list = model('order_goods')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }


    /**
     * 订单项商品真实支付金额
     * @param $params
     * @return array
     */
    public function getOrderRealPayGoodsMoney($params){
        $order_goods_id = $params['order_goods_id'];
        $order_id = $params['order_id'];
        $condition = array(
            ['order_id', '=', $order_id]
        );
        $order_info = model('order')->getInfo($condition, 'order_money, pay_money, delivery_money, coupon_money, balance_money, invoice_money, point_money');
        $order_money = $order_info['order_money'];
        $pay_money = $order_info['pay_money'];
        $delivery_money = $order_info['delivery_money'];
        $invoice_money = $order_info['invoice_money'];
        $balance_money = $order_info['balance_money'];
        $coupon_money = $order_info['coupon_money'];
        $point_money = $order_info['point_money'];
        $real_pay_goods_money = $pay_money - $delivery_money - $invoice_money ;//总的商品真实支付金额

        $real_goods_money = $order_money - $delivery_money - $invoice_money;
        $item_real_goods_money = $params['real_goods_money'];//订单项真实支付金额
        $item_real_pay_goods_money = $real_goods_money > 0 ? round($item_real_goods_money/$real_goods_money * $real_pay_goods_money, 2) : 0;//四舍五入可能会多
        return $this->success($item_real_pay_goods_money);

    }
}