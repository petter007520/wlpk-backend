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

use addon\fenxiao\model\FenxiaoData;
use app\model\member\Withdraw;
use app\model\order\Order as OrderModel;
use app\model\order\OrderCommon as OrderCommonModel;
use app\model\order\OrderRefund;
use app\model\shop\Shop as ShopModel;
use app\model\shop\ShopAccount;
use app\model\shop\ShopOpenAccount;
use app\model\shop\ShopReopen as ShopReopenModel;
use app\model\shop\ShopSettlement;
use app\model\web\Account as AccountModel;
use Carbon\Carbon;

class Account extends BaseApi
{

    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            echo $this->response($token);
            exit;
        }
    }

    /**
     * 资产概况
     */
    public function index()
    {
        $shop_model = new ShopModel();
        $shop_account_model = new ShopAccount();

        $data = [];
        //获取商家转账设置
        $shop_withdraw_config = $shop_account_model->getShopWithdrawConfig();
        $data[ 'shop_withdraw_config' ] = $shop_withdraw_config[ 'data' ][ 'value' ];//商家转账设置

        //获取店铺的账户信息
        $condition = array (
            [ "site_id", "=", $this->site_id ]
        );
        $shop_info = $shop_model->getShopInfo($condition, 'site_name,logo,account, account_withdraw,account_withdraw_apply,shop_open_fee,shop_baozhrmb')[ 'data' ];
        $data[ 'shop_info' ] = $shop_info;

        //余额
        $account = $shop_info[ 'account' ] - $shop_info[ 'account_withdraw_apply' ];
        $data[ 'account' ] = number_format($account, 2, '.', '');

        //累计收入
        $total = $shop_info[ 'account' ] + $shop_info[ 'account_withdraw' ];
        $data[ 'total' ] = number_format($total, 2, '.', '');

        //已提现
        $data[ 'account_withdraw' ] = number_format($shop_info[ 'account_withdraw' ], 2, '.', '');

        //提现中
        $data[ 'account_withdraw_apply' ] = number_format($shop_info[ 'account_withdraw_apply' ], 2, '.', '');

        //获取店家结算账户信息
        $shop_cert_result = $shop_model->getShopCert($condition, 'bank_type, settlement_bank_account_name, settlement_bank_account_number, settlement_bank_name, settlement_bank_address');
        $data[ 'shop_cert_info' ] = $shop_cert_result[ 'data' ];//店家结算账户信息

        //店铺的待结算金额
        $settlement_model = new ShopSettlement();
        $settlement_info = $settlement_model->getWaitSettlementInfo($this->site_id);
        $order_apply = $settlement_info[ 'shop_money' ] - $settlement_info[ 'refund_shop_money' ] - $settlement_info[ 'commission' ] + $settlement_info[ 'platform_coupon_money' ] - $settlement_info[ 'refund_platform_coupon_money' ];
        $data[ 'order_apply' ] = number_format($order_apply, 2, '.', '');

        return $this->response($this->success($data));
    }

    /**
     * 店铺账户面板
     */
    public function dashboard()
    {
        $start_time = $this->params['start_time'] ?? Carbon::today()->timestamp;
        $end_time = $this->params['end_time'] ?? Carbon::tomorrow()->timestamp;

        $data = [];
        // 收入
        $order_money = (new OrderModel())->getOrderMoneySum([ ['site_id', '=', $this->site_id], ['pay_time', 'between', [$start_time, $end_time] ], ['order_scene', '=', 'online'] ], 'pay_money')['data'];
        $income_data = [
            [
                'title' => '商城订单',
                'value' => $order_money,
                'desc' => '统计时间内，所有付款订单实付金额之和',
                'url' => 'shop/order/lists'
            ]
        ];
        $event = event('IncomeStatistics', ['site_id' => $this->site_id, 'start_time' => $start_time, 'end_time' => $end_time]);
        if (!empty($event)) $income_data = array_merge($income_data, ...$event);
        $data['total_income'] = array_sum(array_column($income_data, 'value'));
        $data['income_data'] = $income_data;
        // 支出
        $disburse_data = [
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
        if (!empty($event)) $disburse_data = array_merge($disburse_data, ...$event);
        $data['total_disburse'] = array_sum(array_column($disburse_data, 'value'));
        $data['disburse_data'] = $disburse_data;

        return $this->response($this->success($data));
    }

    /**
     * 账户交易记录
     */
    public function orderList()
    {
        $order_model = new OrderCommonModel();
        $condition[] = [ 'site_id', '=', $this->site_id ];

        //下单时间
        $start_time = isset($this->params[ 'start_time' ]) ? $this->params[ 'start_time' ] : '';
        $end_time = isset($this->params[ 'end_time' ]) ? $this->params[ 'end_time' ] : '';

        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ "finish_time", ">=", $start_time ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ "finish_time", "<=", $end_time ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'finish_time', 'between', [ $start_time, $end_time ] ];
        }

        //订单状态
        $order_status = isset($this->params[ 'order_status' ]) ? $this->params[ 'order_status' ] : '';
        if ($order_status != "") {
            switch ( $order_status ) {
                case 1://进行中

                    $condition[] = [ "order_status", "not in", [ 0, -1, 10 ] ];
                    $order = 'pay_time desc';
                    break;
                case 2://待结算

                    $condition[] = [ "order_status", "=", 10 ];
                    $condition[] = [ "is_settlement", "=", 0 ];
                    $order = 'finish_time desc';
                    break;
                case 3://已结算

                    $condition[] = [ "order_status", "=", 10 ];
                    $condition[] = [ "settlement_id", ">", 0 ];
                    $order = 'finish_time desc';
                    break;
                case 4://全部
                    $condition[] = [ "order_status", "not in", [ 0, -1 ] ];
                    $order = 'pay_time desc';
                    break;
            }
        } else {
            $condition[] = [ "order_status", "=", 10 ];
            $condition[] = [ "settlement_id", "=", 0 ];
            $order = 'finish_time desc';
        }
        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $field = 'order_id,order_no,order_money,order_status_name,shop_money,platform_money,refund_money,refund_shop_money,refund_platform_money,commission,finish_time,settlement_id';
        $list = $order_model->getOrderPageList($condition, $page, $page_size, $order, $field);

        return $this->response($list);
    }

    /**
     * 订单统计
     * @return false|string
     */
    public function orderStat()
    {
        $data = [];
        //店铺的待结算金额
        $settlement_model = new ShopSettlement();
        $settlement_info = $settlement_model->getWaitSettlementInfo($this->site_id);
        $wait_settlement = $settlement_info[ 'shop_money' ] - $settlement_info[ 'refund_shop_money' ] - $settlement_info[ 'commission' ];
        $data[ 'wait_settlement' ] = number_format($wait_settlement, 2, '.', '');

        //店铺的已结算金额
        $finish_condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'order_status', '=', 10 ],
            [ 'settlement_id', '>', 0 ]
        ];
        $settlement_info = $settlement_model->getShopSettlementData($finish_condition);
        $finish_settlement = $settlement_info[ 'shop_money' ] - $settlement_info[ 'refund_shop_money' ] - $settlement_info[ 'commission' ];
        $data[ 'finish_settlement' ] = number_format($finish_settlement, 2, '.', '');

        //店铺的进行中金额
        $settlement_condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'order_status', "not in", [ 0, -1, 10 ] ]
        ];
        $settlement_info = $settlement_model->getShopSettlementData($settlement_condition);
        $settlement = $settlement_info[ 'shop_money' ] - $settlement_info[ 'refund_shop_money' ] - $settlement_info[ 'commission' ];
        $data[ 'settlement' ] = number_format($settlement, 2, '.', '');

        return $this->response($this->success($data));
    }

}