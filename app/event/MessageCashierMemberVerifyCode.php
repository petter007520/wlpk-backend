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

use app\model\member\Member;


/**
 * 收银台会员验证验证码
 */
class MessageCashierMemberVerifyCode
{

    public function handle($param)
    {
        //发送订单消息
        if ($param["keywords"] == "CASHIER_MEMBER_VERIFY_CODE") {
            $result      = (new Member())->bindCode($param);
            return $result;
        }
    }
}