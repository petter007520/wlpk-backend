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
 * 转账方式  (前后台调用)
 */
class TransferType
{
    public function handle($params)
    {
        $app_type = isset($params['app_type']) ? $params['app_type'] : '';
        if (!empty($app_type)) {
            $config_model   = new Config();
            $app_type_array = $config_model->app_type;
            if (!in_array($app_type, $app_type_array)) {
                return '';
            }
            $config_result   = $config_model->getPayConfig($params['site_id']);
            $config          = $config_result["data"]["value"] ?? [];
            $transfer_status = $config["transfer_status"] ?? 0;
            if ($transfer_status == 0) {
                return '';
            }
        }
        $info = array(
            "type"      => "wechatpay",
            "type_name" => "微信零钱",
        );
        return $info;

    }
}