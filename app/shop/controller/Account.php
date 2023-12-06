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

use app\model\member\Withdraw;
use app\model\order\Order as OrderModel;
use app\model\order\OrderRefund;
use Carbon\Carbon;

class Account extends BaseShop
{
    public function dashboard()
    {
        $is_memberwithdraw  = addon_is_exit('memberwithdraw', $this->site_id);
        $this->assign('is_memberwithdraw', $is_memberwithdraw);

        //获取分销商账户统计
        $is_addon_fenxiao = addon_is_exit('fenxiao', $this->site_id);
        $this->assign('is_addon_fenxiao', $is_addon_fenxiao);

        $this->income();
        $this->disburse();

        return $this->fetch('account/dashboard');
    }

    /**
     * 收入
     */
    public function income()
    {
        $start_time = input('start_time', Carbon::today()->timestamp);
        $end_time = input('end_time', Carbon::tomorrow()->timestamp);

        $order_money = (new OrderModel())->getOrderMoneySum([ ['site_id', '=', $this->site_id], ['pay_time', 'between', [$start_time, $end_time] ], ['order_scene', '=', 'online'] ], 'pay_money')['data'];

        $data = [
             [
                'title' => '商城订单',
                'value' => $order_money,
                'desc' => '统计时间内，所有付款订单实付金额之和',
                'url' => 'shop/order/lists'
            ]
        ];

        $event = event('IncomeStatistics', ['site_id' => $this->site_id, 'start_time' => $start_time, 'end_time' => $end_time]);
        if (!empty($event)) $data = array_merge($data, ...$event);

        if (request()->isAjax()) return success(0, '', $data);
        $this->assign('total_income', array_sum(array_column($data, 'value')));
        $this->assign('income_data', $data);
    }

    /**
     * 支出
     */
    public function disburse()
    {
        $start_time = input('start_time', Carbon::today()->timestamp);
        $end_time = input('end_time', Carbon::tomorrow()->timestamp);
        $data = [
            [
                'title' => '订单退款',
                'value' => (new OrderRefund())->getRefundSum([ ['site_id', '=', $this->site_id], ['refund_money_type', '=', '1,2'], ['refund_time', 'between', [$start_time, $end_time] ] ], 'refund_pay_money')['data'],
                'desc' => '统计时间内，所有订单退款转账金额之和',
                'url' => 'shop/orderrefund/lists'
            ],
            [
                'title' => '会员提现',
                'value' => (new Withdraw())->getMemberWithdrawSum([ ['site_id', '=', $this->site_id], ['payment_time', 'between', [$start_time, $end_time] ] ], 'apply_money')['data'],
                'desc' => '统计时间内，所有会员提现转账金额之和',
                'url' => 'shop/memberwithdraw/lists'
            ]
        ];
        $event = event('DisburseStatistics', ['site_id' => $this->site_id, 'start_time' => $start_time, 'end_time' => $end_time]);
        if (!empty($event)) $data = array_merge($data, ...$event);

        if (request()->isAjax()) return success(0, '', $data);
        $this->assign('total_disburse', array_sum(array_column($data, 'value')));
        $this->assign('disburse_data', $data);
    }
}
