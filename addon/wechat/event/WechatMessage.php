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

use liliuwei\think\Jump;
use addon\system\Wechat\common\model\WechatMessage as WechatMessageModel;

class WechatMessage
{
    use Jump;

    /**
     * 微信模板消息
     * @param array $param
     */
    public function handle($param = [])
    {
        $wechat_message = new WechatMessageModel();
        $res            = $wechat_message->sendMessage($param);
        return $res;
    }

}