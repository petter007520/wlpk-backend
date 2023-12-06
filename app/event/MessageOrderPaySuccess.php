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

use app\model\order\OrderMessage;

/**
 * 订单创建发送消息
 */
class MessageOrderPaySuccess
{
    /**
     * @param $param
     * @return array|mixed|void
     */
    public function handle($param)
    {
        trace($param, '订单创建发送消息event'.json_encode($param));
        //发送订单消息
        if ($param["keywords"] == "ORDER_PAY") {
            $model = new OrderMessage();
            $model->messagePaySuccess($param);
        }
    }
}