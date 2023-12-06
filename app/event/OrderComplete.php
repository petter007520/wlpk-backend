<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\event;

use app\model\member\Member;
use app\model\member\MemberAccount;
use app\model\member\MemberLevel;
use app\model\order\OrderCommon;

/**
 * 订单完成后
 */
class OrderComplete
{

    // 行为扩展的执行入口必须是run
    public function handle($data)
    {
        //订单返还积分
        $order_model       = new OrderCommon();
        $condition         = array(
            ['order_id', '=', $data['order_id']]
        );
        $order_info = $order_model->getOrderInfo($condition, 'order_money,order_status,site_id,member_id')['data'] ?? [];
        //如果缺失已完成
        if ($order_info['order_status'] == 10) {
            //会员等级 计算积分返还比率
            $site_id            = $order_info['site_id'];
            $member_id          = $order_info['member_id'];
            //存在散客的情况
            if($member_id > 0){
                $member_model       = new Member();
                $member_info_result = $member_model->getMemberInfo([['member_id', '=', $member_id], ['site_id', '=', $site_id]], 'member_level');
                $member_info        = $member_info_result['data'];
                if ($member_info['member_level'] > 0) {
                    $member_level_model       = new MemberLevel();
                    $member_level_info_result = $member_level_model->getMemberLevelInfo([['level_id', '=', $member_info['member_level']], ['site_id', '=', $site_id]], "point_feedback");
                    $member_level_info        = $member_level_info_result['data'];
                    if ($member_level_info['point_feedback'] > 0) {
                        //计算返还的积分
                        $point                = round($order_info['order_money'] * $member_level_info['point_feedback']);
                        $member_account_model = new MemberAccount();
                        $result               = $member_account_model->addMemberAccount($site_id, $member_id, 'point', $point, 'order', '会员消费回馈积分', '会员消费奖励发放');
                        if ($result['code'] < 0) {
                            return $result;
                        }
                    }
                }
            }

        }

        return $order_model->success();
    }

}