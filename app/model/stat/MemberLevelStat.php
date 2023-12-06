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
 * 统计
 * @author Administrator
 *
 */
class MemberLevelStat extends BaseModel
{
    /**
     * 用于订单(同与订单支付后调用)
     * @param $params
     */
    public function addMemberLevelOrderStat($params){
        $order_id = $params['order_id'];
        $site_id = $params['site_id'] ?? 0;
        $order_condition = array(
            ['order_id', '=', $order_id],
            ['site_id', '=', $site_id]
        );
        $order_info = model('member_level_order')->getInfo($order_condition);
        if(empty($order_info))
            return $this->error();

        $order_money = $order_info['order_money'];
        $stat_data = array(
            'site_id' => $site_id,
            'member_level_count' => 1,
            'member_level_total_money' => $order_money
        );

        $stat_model = new Stat();

        $result = $stat_model->addShopStat($stat_data);
        return $result;
    }

}