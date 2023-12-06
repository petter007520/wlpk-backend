<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\system\Wechat\event;

use addon\system\Wechat\common\model\WechatMessage;

class   CronMessageSend
{
    /**
     * 邮箱消息延时发送
     * @param array $param
     */
    public function handle($param = [])
    {
        $wechat_message = new WechatMessage();
        $res            = $wechat_message->cronMessageSend($param);
        return $res;
    }
}