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

/**
 * 活动类型
 */
class GoodsPromotionType
{

    /**
     * 活动类型
     * @return array
     */
    public function handle()
    {
        return [ "name" => "限时折扣", "short" => "折", "type" => "discount", "color" => "#22AFB9", 'url' => 'discount://shop/discount/lists' ];
    }
}