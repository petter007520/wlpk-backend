<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\memberconsume\event;

use addon\memberconsume\model\Consume;

/**
 * 积分规则
 */
class PointRule
{

    public function handle($data)
    {
        $config = new Consume();
        $info = $config->getConfig($data[ 'site_id' ])[ 'data' ];

        $data = [
            'title' => '会员消费奖励',
            'content' => !$info[ 'is_use' ] || !$info[ 'value' ][ 'return_point_rate' ] ? '-' : "会员消费，赠送消费金额" . $info[ 'value' ][ 'return_point_rate' ] . "%的积分",
            'url' => 'memberconsume://shop/config/index',
            'update_time' => $info[ 'modify_time' ]
        ];
        return $data;
    }
}