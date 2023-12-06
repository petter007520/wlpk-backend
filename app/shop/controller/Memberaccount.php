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

use addon\coupon\model\Coupon;
use app\model\account\Point;
use app\model\member\Member as MemberModel;
use app\model\member\MemberAccount as MemberAccountModel;

/**
 * 会员账户管理 控制器
 */
class Memberaccount extends BaseShop
{
    /*
     *  会员积分
     */
    public function point()
    {
        //账户类型和来源类型
        $member_account_model = new MemberAccountModel();
        $from_type = $member_account_model->getFromType();
        $this->assign('from_type', $from_type['point']);

        $total_usable_point = (new MemberModel())->getMemberSum([ ['site_id', '=', $this->site_id], ['is_delete', '=', 0] ], 'point')['data'];
        $this->assign('total_usable_point', round($total_usable_point));

        $grant_point = $member_account_model->getMemberAccountSum([ ['site_id', '=', $this->site_id], ['account_type', '=', 'point'], ['account_data', '>', 0] ], 'account_data')['data'];
        $this->assign('grant_point', round($grant_point));

        $consume_point = $member_account_model->getMemberAccountSum([ ['site_id', '=', $this->site_id], ['account_type', '=', 'point'], ['account_data', '<', 0] ], 'account_data')['data'];
        $this->assign('consume_point', abs(round($consume_point)));

        return $this->fetch('account/point');
    }

    /**
     * 积分规则
     * @return mixed
     */
    public function pointConfig()
    {
        //账户类型和来源类型
        $member_account_model = new MemberAccountModel();

        $total_usable_point = (new MemberModel())->getMemberSum([ ['site_id', '=', $this->site_id], ['is_delete', '=', 0] ], 'point')['data'];
        $this->assign('total_usable_point', round($total_usable_point));

        $grant_point = $member_account_model->getMemberAccountSum([ ['site_id', '=', $this->site_id], ['account_type', '=', 'point'], ['account_data', '>', 0] ], 'account_data')['data'];
        $this->assign('grant_point', round($grant_point));

        $consume_point = $member_account_model->getMemberAccountSum([ ['site_id', '=', $this->site_id], ['account_type', '=', 'point'], ['account_data', '<', 0] ], 'account_data')['data'];
        $this->assign('consume_point', abs(round($consume_point)));

        $rule = event('PointRule', ['site_id' => $this->site_id]);
        $this->assign('rule', $rule);

        return $this->fetch('account/point_config');
    }

    /**
     * 会员余额
     */
    public function balance()
    {
        $member_account_model = new MemberAccountModel();
        $from_type = $member_account_model->getFromType();
        $this->assign('from_type', array_merge($from_type['balance'], $from_type['balance_money']));

        $member_model = new MemberModel();
        $total_balance = $member_model->getMemberSum([ ['site_id', '=', $this->site_id], ['is_delete', '=', 0] ], 'balance')['data'];
        $this->assign('total_balance', sprintf("%.2f", $total_balance));

        $total_balance_money = $member_model->getMemberSum([ ['site_id', '=', $this->site_id], ['is_delete', '=', 0] ], 'balance_money')['data'];
        $this->assign('total_balance_money', sprintf("%.2f", $total_balance_money));

        $total_consume_money = $member_account_model->getMemberAccountSum([ ['site_id', '=', $this->site_id], ['account_type', 'in', ['balance', 'balance_money'] ], ['account_data', '<', 0], ['from_type', '<>', 'adjust'] ], 'account_data')['data'];
        $this->assign('total_consume_money', sprintf("%.2f", abs($total_consume_money)));

        return $this->fetch('account/balance');
    }
    /**
     * 会员成长值
     */
    public function growth()
    {
        $member_account_model = new MemberAccountModel();
        $from_type = $member_account_model->getFromType();
        $this->assign('from_type', $from_type['growth']);

        return $this->fetch('account/growth');
    }

    /**
     * 会员优惠券
     */
    public function coupon()
    {
        $model = new Coupon();
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $start_date = input('start_time', '');
            $end_date = input('end_time', '');
            $search_text = input('search_text', '');
            $get_type = input('get_type', '');
            $state = input('state', '');

            $condition[] = [ 'c.site_id', '=', $this->site_id ];
            if ($start_date != '' && $end_date != '') {
                $condition[] = [ 'c.fetch_time', 'between', [ strtotime($start_date), strtotime($end_date) ] ];
            } else if ($start_date != '' && $end_date == '') {
                $condition[] = [ 'c.fetch_time', '>=', strtotime($start_date) ];
            } else if ($start_date == '' && $end_date != '') {
                $condition[] = [ 'c.fetch_time', '<=', strtotime($end_date) ];
            }
            if ($search_text) {
                $condition[] = [ 'm.nickname|m.mobile', 'like', '%'.$search_text.'%' ];
            }
            if ($get_type) {
                $condition[] = [ 'get_type', '=', $get_type ];
            }
            if ($state) {
                $condition[] = [ 'state', '=', $state ];
            }

            $join = [
                ['member m', 'm.member_id = c.member_id', 'inner']
            ];
            $field = 'c.*,m.nickname,m.headimg,m.mobile';
            return $model->getCouponPageList($condition, $page, $page_size, 'c.fetch_time desc', $field, 'c', $join);
        }
        $this->forthMenu();
        $total_count = $model->getMemberCouponCount([ ['site_id', '=', $this->site_id] ])['data'];
        $this->assign('total_count', $total_count);

        $used_count = $model->getMemberCouponCount([ ['site_id', '=', $this->site_id], ['state', '=', 2] ])['data'];
        $this->assign('used_count', $used_count);

        $not_used_count = $model->getMemberCouponCount([ ['site_id', '=', $this->site_id], ['state', '=', 1] ])['data'];
        $this->assign('not_used_count', $not_used_count);

        $this->assign('get_type', $model->getCouponGetType());
        return $this->fetch('account/coupon');
    }

    /**
     * 账户详情
     */
    public function accountDetail()
    {
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $account_type = input('account_type', '');
            $from_type = input('from_type', '');
            $start_date = input('start_time', '');
            $end_date = input('end_time', '');
            $search_text = input('search_text', '');

            $condition[] = [ 'ma.site_id', '=', $this->site_id ];
            //账户类型
            if ($account_type != '') {
                $condition[] = [ 'ma.account_type', 'in', explode(',', $account_type)  ];
            }
            //来源类型
            if ($from_type != '') {
                $condition[] = [ 'from_type', '=', $from_type ];
            }
            //发生时间
            if ($start_date != '' && $end_date != '') {
                $condition[] = [ 'ma.create_time', 'between', [ strtotime($start_date), strtotime($end_date) ] ];
            } else if ($start_date != '' && $end_date == '') {
                $condition[] = [ 'ma.create_time', '>=', strtotime($start_date) ];
            } else if ($start_date == '' && $end_date != '') {
                $condition[] = [ 'ma.create_time', '<=', strtotime($end_date) ];
            }
            if ($search_text) {
                $condition[] = [ 'm.nickname|m.mobile', 'like', '%'.$search_text.'%' ];
            }

            $field = 'ma.*,m.nickname,m.headimg,m.mobile';
            $join = [
                ['member m', 'm.member_id = ma.member_id', 'left']
            ];
            $member_account_model = new MemberAccountModel();
            $res = $member_account_model->getMemberAccountPageList($condition, $page, $page_size, 'ma.create_time desc', $field, 'ma', $join);
            return $res;
        }
    }


    /**
     * 积分清零
     */
    public function pointClear(){
        $point_model = new Point();
        $result = $point_model->pointClear(['site_id' => $this->site_id, 'remark' => input('remark', '')]);
        return $result;
    }

    /**
     * 积分重置
     */
    public function pointReset(){
        $point_model = new Point();
        $result = $point_model->pointReset(['site_id' => $this->site_id]);
        return $result;
    }

}