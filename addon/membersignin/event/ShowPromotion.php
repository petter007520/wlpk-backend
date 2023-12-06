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
 * 活动展示
 */
class ShowPromotion
{

    /**
     * 活动展示
     * @param array $params
     * @return array
     */
    public function handle($params = [])
    {
        $data = [
            'shop' => [
                [
                    //插件名称
                    'name' => 'membersignin',
                    //展示分类（根据平台端设置，admin（平台营销），shop：店铺营销，member:会员营销, tool:应用工具）
                    'show_type' => 'member',
                    //展示主题
                    'title' => '签到奖励',
                    //展示介绍
                    'description' => '客户每日签到发放奖励',
                    //展示图标
                    'icon' => 'addon/membersignin/icon.png',
                    //跳转链接
                    'url' => 'membersignin://shop/config/index',
                    'summary' => $this->summary($params)
                ]
            ]

        ];
        return $data;
    }

    /**
     * 营销活动概况
     * @param $params
     * @return array
     */
    private function summary($params)
    {
        if (empty($params)) {
            return [];
        }
        if (isset($params[ 'count' ]) || isset($params[ 'summary' ])) $config = ( new Signin() )->getConfig($params[ 'site_id' ])[ 'data' ];
        //获取活动数量
        if (isset($params[ 'count' ])) {
            return [
                'count' => $config[ 'is_use' ]
            ];
        }
        //获取活动概况,需要获取开始时间与结束时间
        if (isset($params[ 'summary' ])) {
            $value = $config[ 'value' ];

            $detail = [];
            foreach ($value[ 'reward' ] as $item) {
                $title = $item[ 'day' ] == 1 ? '每日签到' : "连续签到{$item['day']}天";
                $content = [];
                if ($item[ 'point' ]) array_push($content, '奖励' . $item[ 'point' ] . '积分');
                if ($item[ 'growth' ]) array_push($content, '奖励' . $item[ 'growth' ] . '成长值');
                array_push($detail, $title . '：' . implode('、', $content));
            }

            return [
                'unlimited_time' => [
                    'status' => $config[ 'is_use' ],
                    'detail' => empty($detail) ? '未配置活动' : implode('；', $detail),
                    'switch_type' => empty($detail) ? 'jump' : 'switch',
                    'config_key' => 'MEMBER_SIGNIN_REWARD_CONFIG'
                ]
            ];
        }
    }
}