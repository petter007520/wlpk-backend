<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\memberregister\api\controller;

use app\api\controller\BaseApi;
use app\model\member\Member;

/**
 * 判断后台添加的会员能不能领取新人礼
 */
class Receivegift extends BaseApi
{
    /**
     * 信息
     */
    public function info()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $member_model = new Member();
        $info = $member_model->getMemberInfo([['member_id','=',$this->member_id],['site_id','=',$this->site_id]],'member_id,can_receive_registergift');
        return $this->response($info);
    }

    /**
     * 更新信息
     */
    public function updateInfo()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $member_model = new Member();
        $info = $member_model->editMember(['can_receive_registergift'=>0],[['member_id','=',$this->member_id],['site_id','=',$this->site_id]]);
        return $this->response($info);
    }

}