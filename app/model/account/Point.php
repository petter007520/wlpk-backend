<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\account;

use app\model\BaseModel;
use app\model\member\MemberAccount;
use app\model\message\Message;
use app\model\message\Sms;
use addon\wechat\model\Message as WechatMessage;
use app\model\member\Member as MemberModel;
use addon\weapp\model\Message as WeappMessage;
use think\facade\Db;

/**
 * 积分管理
 */
class Point extends BaseModel
{

    /**
     * 积分清零
     * @param $params
     * @return array
     */
    public function pointClear($params){
        $site_id = $params['site_id'] ?? 0;
        try {
            set_time_limit(0);
            $condition = array(
                ['point', '>', 0]
            );
            if($site_id > 0){
                $condition[] = ['site_id', '=', $site_id];
            }
            $list  = model('member')->getList($condition, 'member_id,site_id, point');
            if(empty($list)){
                return $this->success();
            }
            $member_account_model = new MemberAccount();
            $remark = empty($params['remark']) ? '积分清零' : $params['remark'];
            foreach($list as $k => $val){
                $member_account_model->addMemberAccount($val['site_id'], $val['member_id'], "point", -$val['point'], 'point_set_zero', 0, $remark);
            }
            return $this->success();
        } catch (\Exception $e) {

            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 积分重置
     * @param $params
     */
    public function pointReset($params){
        $site_id = $params['site_id'];
        //会员积分清零
        $condition = array(
            ['point', '<>', 0]
        );
        $common_condition = [];
        if($site_id > 0){
            $common_condition[] = ['site_id', '=', $site_id];
        }
        $member_data = array(
            'point' => 0
        );
        model('member')->update($member_data, array_merge($condition, $common_condition));
        //会员积分记录清空删除
        $member_account_condition = array(
            ['account_type', '=', 'point']
        );
        model('member_account')->delete(array_merge($member_account_condition, $common_condition));
        return $this->success();
    }
}