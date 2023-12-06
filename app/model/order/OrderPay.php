<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\order;



use app\model\system\Pay;

/**
 * 订单支付相关
 *
 * @author Administrator
 *
 */
class OrderPay extends OrderCommon
{

    /**
     * 改变订单的交易流水号
     * @param $params
     */
    public function reset0rderTradeNo($params){
        $out_trade_no = $params['out_trade_no'];
        $order_condition = array(
            ['pay_status', '=', 0]
        );
        $order_condition[] = ['out_trade_no', '=', $out_trade_no];
        $order_list = model('order')->getList($order_condition, 'pay_money,order_name,out_trade_no,order_id,pay_status,site_id,member_id,member_card_order');
        //判断订单数是否匹配
        if (empty($order_list))
            return $this->error([], '没有可支付订单!');

        $order_name = '';
        $pay_money = 0;
        foreach($order_list as $v){
            $site_id = $v['site_id'];
            $order_name = string_split($order_name, ',', $v['order_name']);
            $pay_money += $v['pay_money'];
        }
        $pay_model = new Pay();
//        $pay_info = $pay_model->getPayInfo($out_trade_no)['data'] ?? [];
//        if(empty($pay_info))
//            return $this->error([], '找不到可支付的单据!');
        $result = $pay_model->deletePay($out_trade_no);//关闭旧支付单据
        if($result['code'] < 0){
            return $this->error([], '当前单据已支付!');
        }
        $member_id = $order_list[0]['member_id'];
        $new_out_trade_no = $pay_model->createOutTradeNo($member_id ?? 0);
        $update_data = array(
            'out_trade_no' => $new_out_trade_no
        );
        model('order')->update($update_data, [['out_trade_no', '=', $out_trade_no], ['pay_status', '=', 0]]);
        model('member_level_order')->update($update_data, [['out_trade_no', '=', $out_trade_no], ['pay_status', '=', 0]]);

        $result = $pay_model->addPay($site_id, $new_out_trade_no, '', $order_name, $order_name, $pay_money, '', 'OrderPayNotify', '');
        return $this->success($new_out_trade_no);
    }
}
