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

use app\model\member\Withdraw;

/**
 * 会员提现失败发送消息
 */
class MessageUserWithdrawalError
{
    /**
     * @param $param
     */
    public function handle($param)
    {
        //发送订单消息
        if ($param["keywords"] == "USER_WITHDRAWAL_ERROR") {
            //发送订单消息
            $model = new Withdraw();
            return $model->messageUserWithdrawalError($param);
        }
    }

}