<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace app\shopapi\controller;

use app\model\order\OrderCommon as OrderCommonModel;
use app\model\order\StoreOrder as StoreOrderModel;

/**
 * 订单
 * Class Order
 * @package app\shop\controller
 */
class Storeorder extends BaseApi
{

    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            echo $this->response($token);
            exit;
        }
    }

    /**
     * 门店提货
     * @return false|string
     */
    public function storeOrderTakeDelivery()
    {
        $order_id = isset($this->params[ 'order_id' ]) ? $this->params[ 'order_id' ] : 0;

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

        return $this->response($result);
    }

}