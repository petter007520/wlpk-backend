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

use addon\system\Wechat\common\model\Wechat;

class WechatMsg
{
    public function handle($param = [])
    {
        $wechat_config = new Wechat();
        $res           = $wechat_config->sendTemplateMsg(request()->siteid(), $param);
        return $res;
    }
}