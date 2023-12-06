<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 * 4.0.1升级测试
 */

namespace addon\memberregister\event;

use addon\memberregister\model\Register as RegisterModel;
use app\model\member\MemberAccount as MemberAccountModel;
use addon\coupon\model\Coupon;

/**
 * 会员注册奖励
 */
class MemberRegister
{
    /**
     * @param $param
     * @return array|\multitype
     */
    public function handle($param)
    {
        $register_model       = new RegisterModel();
        $member_account_model = new MemberAccountModel();

        $register_config = $register_model->getConfig($param['site_id'])['data'];

        $res = [];
        if ($register_config['is_use']) {
            $register_config = $register_config['value'];
            unset($register_config['coupon_list']);
            foreach ($register_config as $k => $v) {
                if($k == 'coupon'){
                    $coupon_list = explode(',', $v);
                    $coupon = new Coupon();
                    $coupon_list = array_map(function ($value){
                        return ['coupon_type_id' => $value, 'num' => 1];
                    }, $coupon_list);
                    $coupon->giveCoupon($coupon_list, $param['site_id'], $param['member_id'], 6);
                }else if(!empty($v)) {
                    $adjust_num   = $v;
                    $account_type = $k;
                    $remark       = '注册奖励';
                    $res          = $member_account_model->addMemberAccount($param['site_id'], $param['member_id'], $account_type, $adjust_num, 'register', 0, $remark);
                }
            }
        }

        return $res;

    }

}