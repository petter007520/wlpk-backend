<?php
// +---------------------------------------------------------------------+
// | NiuCloud | [ WE CAN DO IT JUST NiuCloud ]                |
// +---------------------------------------------------------------------+
// | Copy right 2019-2029 www.niucloud.com                          |
// +---------------------------------------------------------------------+
// | Author | NiuCloud <niucloud@outlook.com>                       |
// +---------------------------------------------------------------------+
// | Repository | https://github.com/niucloud/framework.git          |
// +---------------------------------------------------------------------+

namespace addon\wechatpay\event;

use addon\wechatpay\model\Pay as PayModel;
use app\model\system\Pay as PayCommon;

/**
 * 支付回调
 */
class AuthcodePay
{
    /**
     * 支付方式及配置
     */
    public function handle($params)
    {
        $out_trade_no = $params['out_trade_no'] ?? '';
        $auth_code_array = [10, 11, 12, 13, 14, 15];
        if(!empty($out_trade_no)){
            $auth_code = $params['auth_code'];
            $sub_str = substr($auth_code , 0 , 2);
            if(in_array($sub_str, $auth_code_array)){
                $pay      = new PayCommon();
                $pay_info = $pay->getPayInfo($out_trade_no)['data'] ?? [];
                if(!empty($pay_info)){
                    $site_id = $pay_info['site_id'] ?? 0;
                    $pay_model = new PayModel($site_id);
                    $result = $pay_model->micropay(array_merge($params, $pay_info));
                    return $result;
                }

            }

        }

    }
}