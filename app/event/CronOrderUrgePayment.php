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

use app\model\message\Message;
use app\model\order\OrderCommon;

/**
 * 订单催付通知
 */
class CronOrderUrgePayment
{
    // 行为扩展的执行入口必须是run
    public function handle($params)
    {
        $order_info = ( new OrderCommon() )->getOrderInfo([ [ 'order_id', '=', $params[ 'relate_id' ] ], [ 'order_status', '=', OrderCommon::ORDER_CREATE ] ], 'site_id')[ 'data' ];
        trace($order_info, '订单催付event');
        if (!empty($order_info)) {
            ( new Message() )->sendMessage([ 'keywords' => 'ORDER_URGE_PAYMENT', 'order_id' => $params[ 'relate_id' ], 'site_id' => $order_info[ 'site_id' ] ]);
        }
        return success();
    }
}