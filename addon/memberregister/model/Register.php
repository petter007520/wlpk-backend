<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\memberregister\model;

use addon\coupon\model\CouponType;
use app\model\BaseModel;
use app\model\system\Config as ConfigModel;

/**
 * 会员注册
 */
class Register extends BaseModel
{
    /**
     * 会员注册奖励设置
     * array $data
     */
    public function setConfig($data, $is_use, $site_id)
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '会员注册奖励设置', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'MEMBER_REGISTER_REWARD_CONFIG' ] ]);
        return $res;
    }

    /**
     * 会员注册奖励设置
     */
    public function getConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'MEMBER_REGISTER_REWARD_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'point' => 0,
                'balance' => 0,
                'growth' => 0,
                'coupon' => 0
            ];
        }
        $coupon_list = [];
        if ($res[ 'data' ][ 'value' ][ 'coupon' ]) {
            $coupon = new CouponType();
            $condition = [
                [ 'site_id', '=', $site_id ],
                [ 'status', '=', 1 ],
                [ 'coupon_type_id', 'in', $res[ 'data' ][ 'value' ][ 'coupon' ] ],
            ];
            $coupon_list = $coupon->getCouponTypeList($condition);
            $coupon_list = $coupon_list[ 'data' ];
        }
        $res[ 'data' ][ 'value' ][ 'coupon_list' ] = $coupon_list;
        return $res;
    }
}