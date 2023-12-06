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
 * 订单自动完成
 */
class CronOrderAfterSaleClose
{
    // 行为扩展的执行入口必须是run
    public function handle($data)
    {
        $order = new OrderCommon();
        $res   = $order->orderAfterSaleClose($data["relate_id"]);//订单自动完成
        return $res;
    }
}