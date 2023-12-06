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

use addon\electronicsheet\model\ElectronicsheetDelivery;
use app\model\express\ExpressDelivery;
use app\model\express\ExpressPackage;
use app\model\goods\GoodsStock;
use app\model\member\Member;
use app\model\message\Message;
use app\model\shop\Shop;
use app\model\system\Cron;
use app\model\system\User;

/**
 * 普通（快递）订单
 *
 * @author Administrator
 *
 */
class OrderLog extends OrderCommon
{

    public function addLog($params){
        $action = $params['action'];//操作类型
        $is_auto = $params['is_auto'] ?? false;//是否是自动任务,如果是自动任务的话调用的时候就需要传递此值
        $site_id = $params['site_id'];//站点id
        $order_id = $params['order_id'];//订单id
        $member_id = $params['member_id'] ?? 0;//操作会员id
        $member_model = new Member();

        $scene = $params['scene'] ?? '';//场景值  shop 店铺管理员  store 门店管理员  member 会员操作   cron 自动任务
        if ($is_auto) {
            $actioner_mode = '3';
            $actioner_id = 0;
            $actioner_name = '系统任务';
        } else {
            //todo  散客日志
            $actioner_mode = 1;
            $actioner_id = 0;
            $actioner_name = '';
            if ($member_id > 0) {

                $member_condition = array(
                    ['member_id', '=', $member_id],
                );
                $member_info = $member_model->getMemberInfo($member_condition, 'nickname,headimg')['data'] ?? [];
                $actioner_mode = '1';
                $actioner_id = $member_id;
                $actioner_name = $member_info['nickname'] ?? '';
            }
            $operater_array = $params['operater'] ?? [];//操作管理员id
            if (!empty($operater_array)) {
                $user_model = new User();
                $operater = $operater_array['uid'];
                $user_condition = array(
                    ['uid', '=', $operater],
                    ['site_id', '=', $site_id]
                );
                $user_info = $user_model->getUserInfo($user_condition, 'username')['data'] ?? [];
                $actioner_mode = 2;
                $actioner_id = $operater;
                $actioner_name = $user_info['username'];
            }
        }
        $order_info = $this->getOrderDetail($order_id)['data'] ?? [];
        if (empty($order_info))
            return $this->error([], '订单不存在!');

//        $balance_money = $order_info['balance_money'];//余额
//        $coupon_money = $order_info['coupon_money'];//优惠券金额
//        $point_money = $order_info['point_money'];//积分抵扣金额
//        $hongbao_money = $order_info['hongbao_money'];//红包金额
        $buyer_id = $order_info['member_id'];//买家
        if(!empty($member_info) && $buyer_id == $member_id){
            $buyer_name = $member_info['nickname'];
        }else{
            $buyer_member_condition = array(
                ['member_id', '=', $member_id],
            );
            $buyer_member_info = $member_model->getMemberInfo($buyer_member_condition, 'nickname,headimg')['data'] ?? [];
            $buyer_name = $buyer_member_info['nickname'] ?? '';
        }

//        $shipping_money = $order_info['delivery_money'];//运费
//        $order_money = $order_info['order_money'];//订单金额
//        $pay_money = $order_info['pay_money'];//支付金额
//        $adjust_money = $order_info['adjust_money'];//调整金额
        $close_time = time_to_date($order_info['close_time']);
//        $trade_time = time_to_date($order_info['trade_time']);
//        $send_time = time_to_date($order_info['send_time']);
        $complete_time = time_to_date($order_info['finish_time']);
//        $pay_type_name = $order_info['pay_type_name'];
        $remark = $order_info['remark'];
//        $trade_type = $order_info['trade_type'];
        $close_cause = $order_info['close_cause'] ?? '';
        $order_scene = $order_info['order_scene'];

//        $full_address = $order_info['full_address'];//详细地址

        $order_info['action'] = $action;

        $order_log = event('OrderLog', $order_info, true);
        if($buyer_id > 0){
            if(!empty($buyer_name)){
                $buyer_name = '买家'.'【' . $buyer_name  . '】';
            }else{
                $buyer_name = '买家';
            }
        }else{
            $buyer_name = '散客';
        }
        if (empty($order_log)) {
            switch ($action) {
                case 'create'://订单创建

                    $content = $buyer_name.'下单了';
                    break;
                case 'close'://订单关闭
                    $content = "订单被关闭";
                    if(!empty($close_cause)){
                        $content .= '关闭原因';
                    }
                    break;
//                case 'editaddress'://修改地址
//                    $content = "订单修改收货地址,新收货地址为:{$full_address}";
//                    break;
//                case 'adjust'://订单调价
//                    $content = "卖家操作订单调价,调整金额:{$adjust_money},当前订单总额为:{$order_money}";
//                    break;
                case 'pay'://订单支付
                    if (isset($params['operater']) && !empty($params['operater'])) {
                        $content = '收银员【' . $actioner_name . '】收款，订单支付成功';
                    } else {
                        $content =  $buyer_name.'已支付订单';
                    }
                    break;
//                case 'delivery'://发货
//                    $content = '卖家已发货,发货时间:' . $send_time;
//                    break;
//                case 'receive'://收货
//                    $content = '买家已收货,收货时间:' . $trade_time;
//                    break;
//                case 'local'://配送
//                    $content = '订单已经开始配送';
//                    break;
//                case 'takelocal'://接收配送
//                    $content = '买家已接收配送';
//                    break;
//                case 'store'://提货
//                    $content = '已提货';
//                    break;
                case 'complete'://完成
                    $content = '订单已完成';
                    break;
                case 'remark'://卖家备注
                    $content = '卖家备注:' . $remark;
                    break;
                case 'refund':
                    $content = $params['content'] ?? '';
                    break;
            }
        } else {
            if ($order_log['code'] < 0) {
                return $order_log;
            }
            $content = $order_log['data']['content'];
        }
        $order_status_name = $order_info['order_status_name'];
        $order_status = $order_info['order_status'];
        $data = [
            'order_id' => $order_id,
            'order_status' => $order_status,
            'order_status_name' => $order_status_name,
            'action_way' => $actioner_mode,
            'uid' => $actioner_id,
            'nick_name' => $actioner_name,
            'action_time' => time(),
            'action' => $content,
        ];
        $res = model('order_log')->add($data);
        return $this->success();
    }
}
