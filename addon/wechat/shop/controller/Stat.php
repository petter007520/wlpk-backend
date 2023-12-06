<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */
namespace addon\wechat\shop\controller;

use addon\wechat\model\Stat as StatModel;

/**
 * 微信公众号基础功能
 */
class Stat extends BaseWechat
{
    /**
     * 访问统计
     */
    public function stat()
    {
        $stat_model = new StatModel();
        $yesterday  = date('Y-m-d', strtotime('-1 day'));
        //昨天的用户分析数据
        $wechat_fans_result = $stat_model->fans($yesterday, $yesterday);
        $this->assign('yesterday_user_data', $wechat_fans_result['data'][0] ?? []);
        //昨天的接口分析数据
        $wechat_interface_result = $stat_model->interfaceSummary($yesterday, $yesterday);
        $this->assign('yesterday_interface_data', $wechat_interface_result['data'][0] ?? []);
        return $this->fetch('stat/stat', [], $this->replace);
    }

    /**
     * 接口调用统计
     * @return array
     */
    public function interfaceSummaryStatistics()
    {
        $date_type  = input("date_type", "week");
        $stat_model = new StatModel();
        $result     = $stat_model->interfaceSummaryStatistics($date_type);
        return $result;
    }

    /**
     * 用户访问统计
     * @return array
     */
    public function userSummaryStatistics()
    {
        $date_type  = input("date_type", "week");
        $stat_model = new StatModel();
        $result     = $stat_model->userSummaryStatistics($date_type);
        return $result;
    }

}