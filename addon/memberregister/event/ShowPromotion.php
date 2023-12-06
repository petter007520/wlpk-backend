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
                    'name' => 'memberregister',
                    //展示分类（根据平台端设置，admin（平台营销），shop：店铺营销，member:会员营销, tool:应用工具）
                    'show_type' => 'member',
                    //展示主题
                    'title' => '新人礼',
                    //展示介绍
                    'description' => '新客注册后发放奖励',
                    //展示图标
                    'icon' => 'addon/memberregister/icon.png',
                    //跳转链接
                    'url' => 'memberregister://shop/config/index',
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
        if (isset($params[ 'count' ]) || isset($params[ 'summary' ])) $config = ( new Register() )->getConfig($params[ 'site_id' ])[ 'data' ];
        //获取活动数量
        if (isset($params[ 'count' ])) {
            return [
                'count' => $config[ 'is_use' ]
            ];
        }
        //获取活动概况,需要获取开始时间与结束时间
        if (isset($params[ 'summary' ])) {
            $content = [];

            $value = $config[ 'value' ];
            if ($value[ 'point' ]) array_push($content, '注册送' . $value[ 'point' ] . '积分');
            if ($value[ 'growth' ]) array_push($content, '注册送' . $value[ 'growth' ] . '成长值');
            if ($value[ 'balance' ]) array_push($content, '注册送' . $value[ 'balance' ] . '元红包');
            if ($value[ 'coupon_list' ]) array_push($content, '注册送优惠券');

            return [
                'unlimited_time' => [
                    'status' => $config[ 'is_use' ],
                    'detail' => empty($content) ? '未配置活动' : implode('、', $content),
                    'switch_type' => empty($content) ? 'jump' : 'switch',
                    'config_key' => 'MEMBER_REGISTER_REWARD_CONFIG'
                ]
            ];
        }
    }
}