<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\manjian\event;

use think\facade\Db;

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
                    'name' => 'manjian',
                    //店铺端展示分类  shop:营销活动   member:互动营销
                    'show_type' => 'shop',
                    //展示主题
                    'title' => '满减活动',
                    //展示介绍
                    'description' => '购满指定金额享受优惠',
                    //展示图标
                    'icon' => 'addon/manjian/icon.png',
                    //跳转链接
                    'url' => 'manjian://shop/manjian/lists',
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
        //获取活动数量
        if (isset($params[ 'count' ])) {
            $count = model("promotion_manjian")->getCount([ [ 'site_id', '=', $params[ 'site_id' ] ] ]);
            return [
                'count' => $count
            ];
        }
        //获取活动概况,需要获取开始时间与结束时间
        if (isset($params[ 'summary' ])) {
            $list = model("promotion_manjian")->getList([
                [ '', 'exp', Db::raw('not ( (`start_time` >= ' . $params[ 'end_time' ] . ')  or (`end_time` <= ' . $params[ 'start_time' ] . '))') ],
                [ 'site_id', '=', $params[ 'site_id' ] ],
                [ 'status', '<>', -1 ]
            ], 'manjian_name as promotion_name,manjian_id as promotion_id,start_time,end_time');
            return !empty($list) ? [
                'time_limit' => [
                    'count' => count($list),
                    'detail' => $list,
                    'color' => '#FFA666'
                ]
            ] : [];
        }
    }
}