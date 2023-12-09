<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechatpay\model;

use app\model\system\Config as ConfigModel;
use app\model\BaseModel;

/**
 * 微信支付配置
 * 版本 1.0.4
 */
class Config extends BaseModel
{

    /**
     * 设置支付配置
     * array $data
     */
    public function setPayConfig($data, $pay_type,$site_id = 0, $app_module = 'shop')
    {
        $config_key = 'WECHAT_PAY_CONFIG';
        if(in_array($pay_type,['ydWechatH5','ydWechatScan','ydAlipayH5','ydQuickPay','ydWechatPu','ydWechatMini'])){
            $config_key = 'WECHAT_PAY_CONFIG';
        }elseif ($pay_type=='ydpay'){
            $config_key = 'YD_PAY_CONFIG';
        }
        $config = new ConfigModel();
        return $config->setConfig($data, '微信支付配置', 1, [['site_id', '=', $site_id], ['app_module', '=', $app_module], ['config_key', '=', $config_key]]);
    }

    /**
     * 获取支付配置
     */
    public function getPayConfig($pay_type='ydpay',$site_id = 0, $app_module = 'shop')
    {
        $config_key = 'WECHAT_PAY_CONFIG';
        if(in_array($pay_type,['ydWechatH5','ydWechatScan','ydAlipayH5','ydQuickPay','ydWechatPu','ydWechatMini'])){
            $config_key = 'WECHAT_PAY_CONFIG';
        }elseif ($pay_type=='ydpay'){
            $config_key = 'YD_PAY_CONFIG';
        }
        $config = new ConfigModel();
        $res    = $config->getConfig([['site_id', '=', $site_id], ['app_module', '=', $app_module], ['config_key', '=', $config_key]]);
        if (!empty($res['data']['value'])) {
            $res['data']['value']['api_type'] = $res['data']['value']['api_type'] ?? 'v2';
            $res['data']['value']['transfer_type'] = $res['data']['value']['transfer_type'] ?? 'v2';
        }
        return $res;
    }
}