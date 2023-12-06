<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\memberconsume\event;

use addon\memberconsume\model\Consume as ConsumeModel;
use app\model\order\OrderCommon;

/**
 * 订单维权成功
 */
class OrderRefundFinish
{

    public function handle($data)
    {
        $order  = new OrderCommon();
        $order_info = $order->getOrderInfo([ ['order_id','=',$data['order_id']] ],'out_trade_no')['data'];
        $consume_model = new ConsumeModel();
        $res = $consume_model->rewardRecovery($order_info['out_trade_no']);
        return $res;
    }
}