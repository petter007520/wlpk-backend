<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\coupon\model;

use app\model\BaseModel;
use app\model\system\Stat;

/**
 * 优惠券统计
 */
class CouponStat extends BaseModel
{

    /**
     * 领取优惠券统计
     * @param $params
     * @return array
     */
    public function addReceiveCouponStat($params)
    {
        $coupon_id = $params[ 'coupon_id' ];
        $site_id = $params[ 'site_id' ] ?? 0;
        $order_condition = array (
            [ 'coupon_id', '=', $coupon_id ],
            [ 'site_id', '=', $site_id ]
        );
        $info = model('promotion_coupon')->getInfo($order_condition);
        if (empty($info))
            return $this->error();

        $stat_data = array (
            'site_id' => $site_id,
            'coupon_count' => 1
        );
        $member_id = $info[ 'member_id' ];
        //如果是第一笔订单才能累加下单会员数

        $time_region = getDayStartAndEndTime();
        $today_start_time = $time_region[ 'start_time' ];
        $today_end_time = $time_region[ 'end_time' ];
        $today_order_condition = array (
            [ 'member_id', '=', $member_id ],
            [ 'fetch_time', 'between', [ $today_start_time, $today_end_time ] ],
            [ 'coupon_id', '<>', $coupon_id ]
        );
        $count = model('promotion_coupon')->getCount($today_order_condition);
        if ($count == 0) {
            $stat_data[ 'coupon_member_count' ] = 1;
        }

        $stat_model = new Stat();

        $result = $stat_model->addShopStat($stat_data);
        return $result;
    }
}