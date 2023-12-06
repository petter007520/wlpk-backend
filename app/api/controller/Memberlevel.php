<?php
/**
 * Index.php
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 * @author : niuteam
 * @date : 2022.8.8
 * @version : v5.0.0.1
 */

namespace app\api\controller;

use app\model\member\MemberLevel as MemberLevelModel;

class Memberlevel extends BaseApi
{
    /**
     * 列表信息
     */
    public function lists()
    {
        $member_level_model = new MemberLevelModel();

        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'level_type', '=', 0 ],
            [ 'status', '=', 1 ],
        ];
        $field = 'level_id,level_name,growth,remark,consume_discount,is_free_shipping,point_feedback,send_point,send_balance,send_coupon,charge_rule,charge_type,bg_color,is_default,level_text_color,level_picture';
        $member_level_list = $member_level_model->getMemberLevelList($condition, $field, 'growth asc,level_id desc');
        return $this->response($member_level_list);
    }
}