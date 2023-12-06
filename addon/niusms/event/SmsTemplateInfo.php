<?php
/**
 * NiuShop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 */

namespace addon\niusms\event;

use addon\niusms\model\Config;

/**
 * 获取短信模板数据
 */
class SmsTemplateInfo
{
    /**
     * 获取短信模板数据
     */
    public function handle($param)
    {
        $config_model = new Config();
        $sms_config = $config_model->getSmsConfig($param['site_id'], 'shop')[ 'data' ];
        if ($sms_config['is_use']) {
            $template_info = model('sms_template')->getInfo([ ['keywords', '=', $param['keywords'] ]]);
            return $template_info;
        }
    }
}