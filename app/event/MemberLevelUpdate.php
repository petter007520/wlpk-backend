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

use app\model\system\Cron;
use think\facade\Log;

/**
 * 会员等级更新
 */
class MemberLevelUpdate
{
    // 行为扩展的执行入口必须是run
    public function handle($data)
    {
        $member_id = $data[ "relate_id" ];
        $member_list = model('member')->getList([ ['member_id', '>', $member_id] ], 'member_id, site_id', 'member_id asc', '', '', '', 200);
        foreach ($member_list as $k => $v){
            $res = event("AddMemberAccount", [
                'account_type' => 'growth',
                'member_id' => $v['member_id'],
                'site_id' => $v['site_id']
            ]);
        }
        if(count($member_list) > 0){
            $last_member_id = $member_list[count($member_list)-1]['member_id'];
            $count = model('member')->getCount([ ['member_id', '>', $member_id] ], 'member_id');
            if($count > 0){
                $cron = new Cron();
                $cron->addCron(1, 0, "会员等级更新", "MemberLevelUpdate", time(), $last_member_id);
                Log::write('MemberLevelUpdate--总数量'.$count.',下次开始member_id='.$last_member_id);
            }
        }
    }

}