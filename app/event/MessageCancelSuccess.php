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

use addon\membercancel\model\MemberCancel;

/**
 *  会员注销成功通知
 */
class MessageCancelSuccess
{
    /**
     * @param $param
     * @return array
     */
    public function handle($param)
    {
        //发送订单消息
        if ($param[ "keywords" ] == "USER_CANCEL_SUCCESS") {
            //发送订单消息
            $model = new MemberCancel();
            return $model->memberCancelSuccess($param);
        }
    }

}