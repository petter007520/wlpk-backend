<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */


namespace addon\weapp\event;

use addon\weapp\shop\controller\Weapp;

/**
 * 微信小程序部署配置
 */
class SiteDeployData
{

    /**
     * 微信小程序部署配置
     */
    public function handle()
    {
        $we_app_controller = new Weapp();
        $pc_site_deploy_data = $we_app_controller->deploy();
        return [
            'type' => 'weapp',
            'html' => $pc_site_deploy_data,
        ];
    }
}