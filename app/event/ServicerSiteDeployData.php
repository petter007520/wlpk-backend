<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\event;

use app\shop\controller\Config;

/**
 * 客服网站部署配置
 */
class ServicerSiteDeployData
{
    public function handle($data)
    {
        $servicer_model = new Config();
        $data = $servicer_model->servicer();
        return [
            'type' => 'servicer',
            'html' => $data,
        ];
    }

}