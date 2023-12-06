<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;

use app\model\member\MemberLevel as MemberLevelModel;
use addon\coupon\model\CouponType;
use app\model\member\Member as MemberModel;
use app\model\member\Config;

/**
 * 会员等级管理 控制器
 */
class Memberlevel extends BaseShop
{
    /**
     * 会员等级列表
     */
    public function levelList()
    {
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $level_type = input('level_type', 0);

            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'level_type', '=', $level_type ]
            ];
            if (!empty($search_text)) $condition[] = [ 'level_name', 'like', "%" . $search_text . "%" ];
            $order = 'growth asc';
            $field = '*';

            $member_level_model = new MemberLevelModel();
            $list = $member_level_model->getMemberLevelPageList($condition, 1, MEMBER_LEVEL, $order, $field);
            $member_count = count($list[ 'data' ][ 'list' ]);
            $list_count = MEMBER_LEVEL - $member_count;
            $member_level = array ();
            for ($i = 1; $i <= $list_count; $i++) {
                $member_level[ $i ][ 'level_vip' ] = "VIP" . ( $i + count($list[ 'data' ][ 'list' ]) );
            }

            $list[ 'data' ][ 'list' ] = array_merge($list[ 'data' ][ 'list' ], $member_level);
            $member_status = 0;
            if (!empty($list[ 'data' ][ 'list' ])) {
                $member_model = new MemberModel();
                foreach ($list[ 'data' ][ 'list' ] as $k => $item) {
                    $list[ 'data' ][ 'list' ][ $k ][ 'member_num' ] = 0;
                    if (isset($item[ 'level_id' ])) {
                        $count = $member_model->getMemberCount([ [ 'member_level', '=', $item[ 'level_id' ] ], [ 'is_delete', '=', 0 ] ]);
                        $list[ 'data' ][ 'list' ][ $k ][ 'member_num' ] = $count[ 'data' ];
                    }
                    $list[ 'data' ][ 'list' ][ $k ][ 'level_vip' ] = 'VIP' . ( $k + 1 );
                    $list[ 'data' ][ 'list' ][ $k ][ 'is_show' ] = 0;
                    if ($k > 1 && $k == $member_count && $k < MEMBER_LEVEL) {
                        if ($list[ 'data' ][ 'list' ][ $k - 1 ][ 'status' ] == 1) $list[ 'data' ][ 'list' ][ $k ][ 'is_add' ] = 1;
                    }

                    if ($k > 0 && $k < $member_count && $member_status == 0) {
                        $list[ 'data' ][ 'list' ][ $k ][ 'is_one' ] = 0;
                        if ($item[ 'status' ] == 0) {
                            $list[ 'data' ][ 'list' ][ $k ][ 'is_show' ] = 1;
                            $list[ 'data' ][ 'list' ][ $k - 1 ][ 'is_show' ] = 1;
                            $member_status = 1;
                        }
                        if ($k == $member_count - 1 && $list[ 'data' ][ 'list' ][ $member_count - 1 ] [ 'status' ] == 1) {
                            $list[ 'data' ][ 'list' ][ $k ][ 'is_show' ] = 1;
                        }
                    }
                }
                if ($member_count == 1) {
                    $list[ 'data' ][ 'list' ][ $member_count ][ 'is_add' ] = 1;
                }
                $list[ 'data' ][ 'list' ][ 0 ][ 'is_show' ] = 0;
            }
            return $list;
        } else {

            $config = ( new Config )->getMemberConfig($this->site_id, $this->app_module)[ 'data' ] ?? [];
            $this->assign('is_update', $config[ 'value' ][ 'is_update' ] ?? 1);
            return $this->fetch('memberlevel/level_list');
        }
    }

    /**
     * 会员等级添加
     */
    public function addLevel()
    {
        $member_level_model = new MemberLevelModel();
        if (request()->isAjax()) {
            $data = [
                'site_id' => $this->site_id,
                'level_name' => input('level_name', ''),
                'growth' => input('growth', 0),
                'remark' => input('remark', ''),
                'is_free_shipping' => input('is_free_shipping', 0),
                'consume_discount' => input('consume_discount', 100),
                'point_feedback' => input('point_feedback', 0),
                'send_point' => input('send_point', 0),
                'send_balance' => input('send_balance', 0),
                'send_coupon' => input('send_coupon', ''),
                'level_type' => 0,
                'charge_rule' => '',
                'charge_type' => 0,
                'bg_color' => input('bg_color', '#333333'),
                'level_text_color' => input('level_text_color', '#ffffff'),
                'level_picture' => input('level_picture', ''),
            ];
            $this->addLog("会员等级添加:" . $data[ 'level_name' ]);
            $res = $member_level_model->addMemberLevel($data);
            ( new Config )->setMemberConfig([ 'is_update' => 1 ], $this->site_id, $this->app_module)[ 'data' ] ?? [];
            return $res;

        } else {

            //获取优惠券列表
            $coupon_model = new CouponType();
            $condition = [
                [ 'status', '=', 1 ],
                [ 'site_id', '=', $this->site_id ],
            ];
            //优惠券字段
            $coupon_field = 'coupon_type_id,type,coupon_name,image,money,discount,validity_type,fixed_term,status,is_limit,at_least,count,lead_count,end_time,goods_type,max_fetch';
            $coupon_list = $coupon_model->getCouponTypeList($condition, $coupon_field);
            $this->assign('coupon_list', $coupon_list);

            $this->assign('level_time', $member_level_model->level_time);

            $growth_up = $member_level_model->getFirstMemberLevel([ [ 'site_id', '=', $this->site_id ], [ 'level_type', '=', '0' ] ], 'growth', 'growth desc')[ 'data' ][ 'growth' ] ?? 0;
            $this->assign('growth_up', $growth_up);

            return $this->fetch('memberlevel/add_level');
        }
    }

    /**
     * 会员等级修改
     */
    public function editLevel()
    {
        $member_level_model = new MemberLevelModel();
        if (request()->isAjax()) {
            $data = [
                'level_name' => input('level_name', ''),
                'growth' => input('growth', 0.00),
                'remark' => input('remark', ''),
                'is_free_shipping' => input('is_free_shipping', 0),
                'consume_discount' => input('consume_discount', 100),
                'point_feedback' => input('point_feedback', 0),
                'send_point' => input('send_point', 0),
                'send_balance' => input('send_balance', 0),
                'send_coupon' => input('send_coupon', ''),
                'charge_rule' => '',
                'bg_color' => input('bg_color', '#333333'),
                'level_text_color' => input('level_text_color', '#ffffff'),
                'level_picture' => input('level_picture', ''),
            ];

            $level_id = input('level_id', 0);

            $this->addLog("会员等级修改:" . $data[ 'level_name' ]);
            ( new Config )->setMemberConfig([ 'is_update' => 1 ], $this->site_id, $this->app_module)[ 'data' ] ?? [];
            return $member_level_model->editMemberLevel($data, [ [ 'level_id', '=', $level_id ], [ 'site_id', '=', $this->site_id ] ]);
        } else {

            $level_id = input('get.level_id', 0);
            $level_info = $member_level_model->getMemberLevelInfo([ [ 'level_id', '=', $level_id ], [ 'site_id', '=', $this->site_id ] ]);

            if (empty($level_info[ 'data' ])) $this->error('未获取到等级数据', addon_url('shop/memberlevel/levellist'));

            $this->assign('level_info', $level_info[ 'data' ]);

            $this->assign('level_time', $member_level_model->level_time);

            //获取优惠券列表
            $coupon_model = new CouponType();
            $condition = [
                [ 'status', '=', 1 ],
                [ 'site_id', '=', $this->site_id ],
            ];
            //优惠券字段
            $coupon_field = 'coupon_type_id,type,coupon_name,image,money,discount,validity_type,fixed_term,status,is_limit,at_least,count,lead_count,end_time,goods_type,max_fetch';
            $coupon_list = $coupon_model->getCouponTypeList($condition, $coupon_field);
            $this->assign('coupon_list', $coupon_list);

            $growth_up = $member_level_model->getFirstMemberLevel([ [ 'growth', '<', $level_info[ 'data' ][ 'growth' ] ], [ 'site_id', '=', $this->site_id ], [ 'level_type', '=', '0' ] ], 'growth', 'growth desc')[ 'data' ];
            //下级
            $growth_down = $member_level_model->getFirstMemberLevel([ [ 'growth', '>', $level_info[ 'data' ][ 'growth' ] ], [ 'site_id', '=', $this->site_id ], [ 'level_type', '=', '0' ] ], 'growth', 'growth asc')[ 'data' ];

            $this->assign('growth_up', $growth_up ? $growth_up['growth'] : 0);
            $this->assign('growth_down', $growth_down ? $growth_down['growth'] : 0);

            return $this->fetch('memberlevel/edit_level');
        }
    }

    /**
     * 会员等级删除
     */
    public function deleteLevel()
    {
        $level_id = input('level_id', '');
        $member_level_model = new MemberLevelModel();
        $this->addLog("会员等级删除id:" . $level_id);
        return $member_level_model->deleteMemberLevel($level_id, $this->site_id);
    }

    /**
     * 会员等级状态
     */
    public function statusLevel()
    {
        $level_id = input('level_id', '');
        $status = input('status', '');
        $member_level_model = new MemberLevelModel();
        $this->addLog("会员等级修改id:" . $level_id);
        return $member_level_model->editMemberLevel([ 'status' => $status ], [ [ 'level_id', '=', $level_id ], [ 'site_id', '=', $this->site_id ] ]);
    }


    /**
     * 更新会员等级状态
     */
    public function startlevel()
    {
        $member_level_model = new MemberLevelModel();
        ( new Config )->setMemberConfig([ 'is_update' => 0 ], $this->site_id, $this->app_module)[ 'data' ] ?? [];
        return $member_level_model->startlevel($this->site_id);
    }
}