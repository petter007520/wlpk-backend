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

use app\shop\controller\H5;

/**
 * 手机端网站部署配置
 */
class H5SiteDeployData
{
    public function handle($data)
    {
        $h5_model = new H5();
        $data = $h5_model->refreshH5();
        return [
            'type' => 'h5',
            'html' => $data,
        ];
    }

}