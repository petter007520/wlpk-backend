<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;

use app\model\order\OrderCommon as OrderCommonModel;

/**
 * 自提订单
 * Class storeorder
 * @package app\shop\controller
 */
class Storeorder extends BaseShop
{

    /**
     * 订单详情
     * @return mixed
     */
    public function detail()
    {
        $order_id = input("order_id", 0);
        $order_common_model = new OrderCommonModel();
        $order_detail_result = $order_common_model->getOrderDetail($order_id);
        $order_detail = $order_detail_result[ "data" ];
        $this->assign("order_detail", $order_detail);
        $this->assign("http_type", get_http_type());
        return $this->fetch("storeorder/detail");
    }

    /**
     * 订单关闭
     * @return mixed
     */
    public function close()
    {

    }

    /**
     * 订单调价
     * @return mixed
     */
    public function adjustprice()
    {

    }

    /**
     * 直接提货
     */
    public function storeOrderTakedelivery()
    {
        $order_id = input('order_id', 0);

        $order_common_model = new OrderCommonModel();
        $condition = array (
            [ 'site_id', '=', $this->site_id ],
            [ 'order_id', '=', $order_id ]
        );
        $order_info = $order_common_model->getOrderInfo($condition, 'delivery_code')[ 'data' ] ?? [];
        if (empty($order_info))
            return $order_common_model->error('', '订单不存在');

        $verify_code = $order_info[ 'delivery_code' ];
        $info = array (
            "verifier_id" => $this->uid,
            "verifier_name" => $this->user_info[ 'username' ],
            "verify_from" => 'shop',
        );
        $verify_model = new \app\model\verify\Verify();
        $result = $verify_model->verify($info, $verify_code);

        return $result;

    }

}