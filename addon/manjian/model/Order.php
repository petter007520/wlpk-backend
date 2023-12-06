<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\manjian\model;

use addon\coupon\model\Coupon;
use app\model\BaseModel;
use app\model\member\MemberAccount;
use app\model\order\Order as BaseOrder;

class Order extends BaseModel
{
    /**
     * 订单完成发放满减送所送积分
     * @param $order_id
     */
    public function orderComplete($order_id)
    {
        $order_info = model('order')->getInfo([ [ 'order_id', '=', $order_id ], [ 'order_status', '=', BaseOrder::ORDER_COMPLETE ] ], 'order_id,member_id');
        if (!empty($order_info)) {
            $member_id = $order_info[ 'member_id' ];
            //存在散客的情况
            if ($member_id > 0) {
                $mansong_record = model('promotion_mansong_record')->getList([ [ 'order_id', '=', $order_id ], [ 'status', '=', 0 ] ]);
                if (!empty($mansong_record)) {
                    model('promotion_mansong_record')->startTrans();
                    foreach ($mansong_record as $item) {
                        try {
                            // 发放积分
                            if (!empty($item[ 'point' ])) {
                                $member_account = new Memberaccount();
                                $member_account->addMemberAccount($item[ 'site_id' ], $item[ 'member_id' ], 'point', $item[ 'point' ], 'manjian', $item[ 'manjian_id' ], "活动奖励发放");
                            }
                            // 发放优惠券
                            if (!empty($item[ 'coupon' ])) {
                                $coupon = new Coupon();
                                $coupon_list = explode(',', $item[ 'coupon' ]);
                                $coupon_num = explode(',', $item[ 'coupon_num' ]);
                                $coupon_data = [];
                                foreach ($coupon_list as $k => $cpupon_item) {
                                    array_push($coupon_data, [
                                        'coupon_type_id' => $cpupon_item,
                                        'num' => $coupon_num[ $k ] ?? 1
                                    ]);
                                }
                                $coupon->giveCoupon($coupon_data, $item[ 'site_id' ], $item[ 'member_id' ], 6);
                            }
                            // 变更发放状态
                            model('promotion_mansong_record')->update([ 'status' => 1 ], [ [ 'id', '=', $item[ 'id' ] ] ]);
                            model('promotion_mansong_record')->commit();
                        } catch (\Exception $e) {
                            model('promotion_mansong_record')->rollback();
                        }
                    }
                }
            }
        }
    }
}