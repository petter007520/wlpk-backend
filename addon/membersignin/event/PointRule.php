<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\membersignin\event;

use addon\membersignin\model\Signin;

/**
 * 积分规则
 */
class PointRule
{

    public function handle($data)
    {
        $config = new Signin();
        $info = $config->getConfig($data[ 'site_id' ])[ 'data' ];

        $data = [
            'title' => '会员签到奖励',
            'content' => '-',
            'url' => 'memberconsume://shop/config/index',
            'update_time' => $info[ 'modify_time' ]
        ];

        if ($info[ 'is_use' ]) {
            $detail = [];
            foreach ($info[ 'value' ][ 'reward' ] as $item) {
                $title = $item[ 'day' ] == 1 ? '每日签到' : "连续签到{$item['day']}天";
                if ($item[ 'point' ]) array_push($detail, $title . '，奖励' . $item[ 'point' ] . '积分');
            }
            if (!empty($detail)) $data[ 'content' ] = implode('、', $detail);
        }

        return $data;
    }
}