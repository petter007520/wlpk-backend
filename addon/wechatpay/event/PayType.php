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

use addon\wechatpay\model\Config;

/**
 * 支付方式  (前后台调用)
 */
class PayType
{
    /**
     * 支付方式及配置
     */
    public function handle($params)
    {
        $app_type = isset($params['app_type']) ? $params['app_type'] : '';
        if (!empty($app_type)) {
            $config_model   = new Config();
            $app_type_array = ['h5', 'wechat', 'weapp', 'pc'];
            if (!in_array($app_type, $app_type_array)) {
                return '';
            }
            $config_result = $config_model->getPayConfig($params['site_id']);
            $config        = $config_result["data"]["value"] ?? [];
            $pay_status    = $config["pay_status"] ?? 0;
            if ($pay_status == 0) {
                return '';
            }
        }
        $info = array(
            "pay_type"      => "wechatpay",
            "pay_type_name" => "YiPay",
            "edit_url"      => "wechatpay://shop/pay/config",
            "shop_url"      => "wechatpay://shop/pay/config",
            "logo"          => "addon/wechatpay/sanpay.png",
            "desc"          => "YiPay"
        );
        return $info;

    }
}