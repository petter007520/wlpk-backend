<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\stat;

use app\model\BaseModel;
use app\model\system\Stat;

/**
 * 储值统计
 * @author Administrator
 *
 */
class RechargeStat extends BaseModel
{
    /**
     * 用于充值订单(同与订单支付后调用)
     * @param $params
     */
    public function addRechargeStat($params){
        $order_id = $params['order_id'];
        $site_id = $params['site_id'] ?? 0;
        $order_condition = array(
            ['order_id', '=', $order_id],
            ['site_id', '=', $site_id]
        );
        $order_info = model('member_recharge_order')->getInfo($order_condition);
        if (empty($order_info))
            return $this->error();

        $order_money = $order_info['price'];
        $member_id = $order_info['member_id'];

        //如果是第一笔订单才能累加下单会员数
        $today_start_time = 0;//当日开始时间
        $today_end_time = 0;//当日结束时间
        $today_order_condition = array(
            ['member_id', '=', $member_id],
            ['pay_time', 'between', [$today_start_time, $today_end_time]],
            ['order_id', '<>', $order_id]
        );
        $stat_data = array(
            'site_id' => $site_id,
            'member_recharge_count' => 1,
            'member_recharge_total_money' => $order_money,

        );
        $count = model('member_recharge_order')->getCount($today_order_condition);
        if ($count == 0) {
            $stat_data['member_recharge_member_count'] = 1;
        }
        //销售量  order_num

        //销售额  order_money
        $stat_model = new Stat();

        $result = $stat_model->addShopStat($stat_data);
        return $result;
    }

}