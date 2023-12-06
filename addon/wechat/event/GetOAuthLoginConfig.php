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

/**
 * 获取第三方登录信息
 * @author Administrator
 *
 */
class GetOAuthLoginConfig
{
    public function handle($param = [])
    {
        $config_info                  = [];
        $config_info["info"]          = $this->info;
        $config_info["info"]['title'] = "微信公众号登录";
        $config_model                 = new Wechat();

        $config_result                     = $config_model->getWechatConfigInfo($param['site_id']);
        $config_info['config']             = $config_result["data"];
        $config_info["info"]['icon']       = __ROOT__ . '/addon/system/Wechat/sitehome/view/public/img/wechat.png';
        $config_info["info"]['config_url'] = addon_url('Wechat://sitehome/config/config');
        $config_info["info"]['cn']         = '微信公众号';
        $config_info["info"]['en']         = 'wechat';
        $config_info["info"]["url"]        = addon_url("Wechat://sitehome/config/config");
        return $config_info;

    }
}