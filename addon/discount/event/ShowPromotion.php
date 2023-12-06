<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\discount\event;

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
                    'name' => 'discount',
                    //店铺端展示分类  shop:营销活动   member:互动营销
                    'show_type' => 'shop',
                    //展示主题
                    'title' => '限时折扣',
                    //展示介绍
                    'description' => '商品限时促销打折',
                    //展示图标
                    'icon' => 'addon/discount/icon.png',
                    //跳转链接
                    'url' => 'discount://shop/discount/lists',
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
            $count = model("promotion_discount")->getCount([ [ 'site_id', '=', $params[ 'site_id' ] ] ]);
            return [
                'count' => $count
            ];
        }
        //获取活动概况,需要获取开始时间与结束时间
        if (isset($params[ 'summary' ])) {
            $join = [
                [ 'goods g', 'd.goods_id = g.goods_id', 'inner' ]
            ];
            $list = model("promotion_discount")->getList([
                [ '', 'exp', Db::raw('not ( (`start_time` >= ' . $params[ 'end_time' ] . ')  or (`end_time` <= ' . $params[ 'start_time' ] . '))') ],
                [ 'd.site_id', '=', $params[ 'site_id' ] ],
                [ 'd.status', '<>', 2 ],
                [ 'd.status', '<>', -1 ],
                [ 'g.goods_state', '=', 1 ],
                [ 'g.is_delete', '=', 0 ]
            ], 'd.discount_name as promotion_name,d.discount_id as promotion_id,d.start_time,d.end_time', 'd.create_time desc', 'd', $join);
            return !empty($list) ? [
                'time_limit' => [
                    'count' => count($list),
                    'detail' => $list,
                    'color' => '#6D66FF'
                ]
            ] : [];
        }
    }
}