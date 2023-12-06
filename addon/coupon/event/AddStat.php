<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */


namespace addon\coupon\event;

use addon\coupon\model\CouponStat;

/**
 * 礼品卡统计
 */
class AddStat
{

    public function handle($params)
    {
        $type = $params['type'];
        if($type == 'receive_coupon'){
            $coupon_stat_model = new CouponStat();
            $res = $coupon_stat_model->addReceiveCouponStat($params['data']);
            return $res;
        }

    }

}