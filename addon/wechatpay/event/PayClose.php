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

/**
 * 关闭支付
 */
class PayClose
{
    /**
     * 关闭支付
     */
    public function handle($params)
    {
        if ($params["pay_type"] == "wechatpay") {
            $pay_model = new PayModel(0, $params['site_id']);
            $result    = $pay_model->close($params);
            return $result;
        }
    }
}