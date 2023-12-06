<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechatpay\event;

use addon\wechatpay\model\Pay as PayModel;
use app\model\system\Pay as PayCommon;

/**
 * 支付回调
 */
class PayNotify
{
    /**
     * 支付方式及配置
     */
    public function handle($param)
    {
        return (new PayModel())->payNotify();
    }
}