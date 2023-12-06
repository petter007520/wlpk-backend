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

namespace app\shopapi\controller;

use app\model\member\Withdraw as MemberWithdrawModel;
use app\model\shop\ShopAccount;
use app\model\shop\Shop as ShopModel;
use app\model\web\Account as AccountModel;

class Shopwithdraw extends BaseApi
{


    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
        $token = $this->checkToken();
        if ($token['code'] < 0) {
            echo $this->response($token);
            exit;
        }
    }

    /**
     * 账户信息
     * @return false|string
     */
    public function info()
    {
        $account_model = new AccountModel();
        //会员余额
        $member_balance_sum = $account_model->getMemberBalanceSum($this->site_id);
        $is_memberwithdraw  = addon_is_exit('memberwithdraw', $this->site_id);
        if ($is_memberwithdraw == 1) {
            $data = $member_balance_sum['data'];
        } else {
            $data =  number_format($member_balance_sum['data']['balance'] + $member_balance_sum['data']['balance_money'], 2, '.', '');
        }
        return $this->response($this->success($data));
    }

    /**
     * 申请提现
     * */
    public function apply()
    {
        $money = isset($this->params['apply_money']) ? $this->params['apply_money'] : '';
        $shop_account_model = new ShopAccount();
        $result = $shop_account_model->applyWithdraw($this->site_id, $money);

        return $this->response($result);
    }

    /**
     * 获取提现记录
     */
    public function lists()
    {
        $withdraw_model = new MemberWithdrawModel();

        $page = isset($this->params['page']) ? $this->params['page'] : 1;
        $page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGE_LIST_ROWS;
        $status = isset($this->params['status']) ? $this->params['status'] : '';
        $start_time = isset($this->params['start_time']) ? $this->params['start_time'] : '';
        $end_time = isset($this->params['end_time']) ? $this->params['end_time'] : '';
        $search_text = isset($this->params['search_text'])?$this->params['search_text']:'';
        $condition[] = ['site_id', '=', $this->site_id];
        if (!empty($status)) {
            if ($status == 3) {//待审核
                $condition[] = ['status', '=', 0];
            } else {
                $condition[] = ['status', '=', $status];
            }
        }
        if(!empty($search_text)){
            $condition[] =['withdraw_no|member_name|realname|mobile|account_number' , "like", "%" . $search_text . "%"];
        }

        if (!empty($start_time) && empty($end_time)) {
            $condition[] = ['apply_time', '>=', $start_time];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = ['apply_time', '<=', $end_time];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = ['apply_time', 'between', [$start_time, $end_time]];
        }

        $order = "id desc";

        $list = $withdraw_model->getMemberWithdrawPageList($condition, $page, $page_size, $order);

        return $this->response($list);
    }

    /**
     * 提现信息
     */
    public function detail()
    {
        $id = isset($this->params['id']) ? $this->params['id'] : 0;
        $withdraw_model = new MemberWithdrawModel();
        $info = $withdraw_model->getMemberWithdrawInfo([["id", "=", $id], ['site_id', '=', $this->site_id]]);
        return $this->response($info);
    }

    /**
     * 同意
     * @return array
     */
    public function agree()
    {
        $id = isset($this->params['id']) ? $this->params['id'] : 0;
        $withdraw_model = new MemberWithdrawModel();
        $condition = array(
            ['site_id', '=', $this->site_id],
            ["id", "=", $id]
        );
        $result = $withdraw_model->agree($condition);
        return $this->response($result);
    }

    /**
     * 拒绝
     * @return array
     */
    public function refuse()
    {
        $id = isset($this->params['id']) ? $this->params['id'] : 0;
        $refuse_reason = isset($this->params['refuse_reason']) ? $this->params['refuse_reason'] : 0;
        $withdraw_model = new MemberWithdrawModel();
        $condition = array(
            ['site_id', '=', $this->site_id],
            ["id", "=", $id]
        );
        $data = array(
            "refuse_reason" => $refuse_reason
        );
        $result = $withdraw_model->refuse($condition, $data);
        return $this->response($result);
    }

}