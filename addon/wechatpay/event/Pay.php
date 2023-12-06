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
 * 生成支付
 */
class Pay
{
    /**
     * 支付
     */
    public function handle($params)
    {
        if ($params["pay_type"] == "wechatpay") {

            $app_type = $params['app_type'];
            $is_weapp = 0;
            switch ($app_type) {
                case 'h5' :
                    $trade_type = "MWEB";
                    break;
                case 'wechat' :
                    $trade_type = "JSAPI";
                    break;
                case 'weapp' :
                    $is_weapp   = 1;
                    $trade_type = "APPLET";
                    break;
                case 'app' :
                    $trade_type = "APP";
                    break;
                case 'pc' :
                    $trade_type = "NATIVE";
                    break;
                case 'cashier':
                    $trade_type = "NATIVE";
                    break;
            }
            $params["trade_type"] = $trade_type;
            $pay_model            = new PayModel($is_weapp, $params['site_id']);
            $result               = $pay_model->pay($params);
            return $result;
        }
    }
}