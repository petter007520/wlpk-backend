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

use app\model\order\OrderCommon;

/**
 * 订单自动关闭
 */
class CronOrderClose
{
    // 行为扩展的执行入口必须是run
    public function handle($data)
    {
        $order = new OrderCommon();
        $order_info_result = $order->getOrderInfo([ [ "order_id", "=", $data[ "relate_id" ] ] ], "order_status");
        if (!empty($order_info_result) && $order_info_result[ "data" ][ "order_status" ] == 0) {
            $result = $order->orderClose($data[ "relate_id" ], [], '长时间未支付,订单自动关闭');//订单自动关闭
            return $result;
        }
    }
}