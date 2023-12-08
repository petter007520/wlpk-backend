<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechatpay\shop\controller;

use addon\wechatpay\model\Config as ConfigModel;
use app\model\upload\Upload;
use app\shop\controller\BaseShop;
use think\facade\Config;

/**
 * 支付 控制器
 */
class Pay extends BaseShop
{
    public function config()
    {
        $config_model = new ConfigModel();
        if (request()->isAjax()) {
            $api           = input("api", "");//api
            $mch_id          = input("mch_id", "");//商户号
            $app_secrect     = input("app_secrect", "");//应用密钥
            $pay_status     = input("pay_status", "");//支付开关
            $callback_url     = input("callback_url", "");//回调URL
            $pay_code        = input("pay_code", "");//通道
            $pay_type        = input("pay_type", "");//标识
            $data            = array(
                "api"           => $api,
                "mch_id"          => $mch_id,
                "app_secrect"     => $app_secrect,
                "pay_status"      => $pay_status,
                "callback_url"    => $callback_url,
                "pay_code"        => $pay_code,
                "pay_type"        => $pay_type,
            );
            return $config_model->setPayConfig($data, $pay_type,$this->site_id, $this->app_module);
        } else {
            $info_result = $config_model->getPayConfig('WECHAT_PAY_CONFIG',$this->site_id, $this->app_module);
            $info        = $info_result["data"];
            if (!empty($info['value'])) {
                $app_type_arr = [];
                if (!empty($info['value']['app_type'])) {
                    $app_type_arr = explode(',', $info['value']['app_type']);
                }
                $info['value']['app_type_arr'] = $app_type_arr;
            }
            $this->assign("info", $info);
            $this->assign("app_type", Config::get("app_type"));
            return $this->fetch("pay/config");
        }
    }


    /**
     * 上传微信支付证书
     */
    public function uploadWechatCert()
    {
        $upload_model = new Upload();
        $site_id      = request()->siteid();
        $name         = input("name", "");
        $extend_type  = ['pem'];
        $param        = array(
            "name"        => "file",
            "extend_type" => $extend_type
        );

        $site_id = $site_id > 0 ? $site_id : 0;
        $result  = $upload_model->setPath("common/wechat/cert/" . $site_id . "/")->file($param);
        return $result;
    }
}