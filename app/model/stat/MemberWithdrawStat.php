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
use think\facade\Log;

/**
 * 会员提现统计
 * @author Administrator
 *
 */
class MemberWithdrawStat extends BaseModel
{
    /**
     * 用于会员提现
     * @param $params
     */
    public function addMemberWithdrawStat($params){
        $id = $params['id'];
        $site_id = $params['site_id'] ?? 0;
        $condition = array(
            ['id', '=', $id],
            ['site_id', '=', $site_id]
        );
        $info = model('member_withdraw')->getInfo($condition);
        if(empty($info))
            return $this->error();

        $member_withdraw_total_money = $info['apply_money'];
        $stat_data = array(
            'site_id' => $site_id,
            'member_withdraw_count' => 1,
            'member_withdraw_total_money' => $member_withdraw_total_money
        );

        $stat_model = new Stat();

        Log::write('addMemberWithdrawStat'.json_encode($stat_data));

        $result = $stat_model->addShopStat($stat_data);
        return $result;
    }

}