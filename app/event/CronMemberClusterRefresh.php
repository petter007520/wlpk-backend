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

use app\model\member\MemberCluster as MemberClusterModel;

/**
 * 刷新会员群体会员信息
 */
class CronMemberClusterRefresh
{
    // 行为扩展的执行入口必须是run
    public function handle()
    {
        $member_cluster_model = new MemberClusterModel();
        $result = $member_cluster_model->refreshMemberCluster();//会员群体定时刷新
        return $result;
    }
}