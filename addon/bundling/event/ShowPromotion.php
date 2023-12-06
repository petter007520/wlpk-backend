<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\bundling\event;

/**
 * 店铺活动
 */
class ShowPromotion
{

    /**
     * 活动展示
     * @return array
     */
    public function handle()
    {
        $data = [
            'shop' => [
                [
                    //插件名称
                    'name' => 'bundling',
                    //展示分类（根据平台端设置，admin（平台营销），shop：店铺营销，member:会员营销, tool:应用工具）
                    'show_type' => 'shop',
                    //展示主题
                    'title' => '组合套餐',
                    //展示介绍
                    'description' => '向客户推荐商品搭售',
                    //展示图标
                    'icon' => 'addon/bundling/icon.png',
                    //跳转链接
                    'url' => 'bundling://shop/bundling/lists',
                ]
            ]

        ];
        return $data;
    }
}