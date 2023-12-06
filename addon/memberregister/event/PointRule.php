<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\memberregister\event;

use addon\memberregister\model\Register;

/**
 * 积分规则
 */
class PointRule
{

    public function handle($data)
    {
        $config = new Register();
        $info   = $config->getConfig($data['site_id'])['data'];

        $data = [
            'title' => '会员注册奖励',
            'content' => !$info['is_use'] || !$info['value']['point'] ? '-' : "会员注册，赠送" . $info['value']['point'] . "积分",
            'url' => 'memberregister://shop/config/index',
            'update_time' => $info['modify_time']
        ];
        return $data;
    }
}