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

use addon\cardservice\model\MemberCard;
use addon\coupon\model\Coupon;
use app\model\BaseModel;
use app\model\goods\Goods;
use app\model\member\Member;
use app\model\member\MemberAccount;
use app\model\message\Message;
use app\model\system\Cron;
use app\model\system\Pay;
use app\model\verify\Verify;
use think\facade\Cache;

/**
 * 常规订单操作
 *
 * @author Administrator
 *
 */
class OrderCommon extends BaseModel
{
    /*****************************************************************************************订单基础状态（其他使用）********************************/
    // 订单待付款
    const ORDER_CREATE = 0;

    // 订单已支付
    const ORDER_PAY = 1;

    // 订单已发货（配货）
    const ORDER_DELIVERY = 3;

    // 订单已收货
    const ORDER_TAKE_DELIVERY = 4;

    // 订单已结算完成
    const ORDER_COMPLETE = 10;
    // 订单已关闭
    const ORDER_CLOSE = -1;

    /*********************************************************************************订单支付状态****************************************************/
    // 待支付
    const PAY_WAIT = 0;

    // 支付中
    const PAY_DOING = 1;

    // 已支付
    const PAY_FINISH = 2;

    /**************************************************************************支付方式************************************************************/
    const OFFLINEPAY = 10;



    // 订单待使用
    const ORDER_WAIT_VERIFY = 11;
    //订单已使用
    const ORDER_VERIFYED = 12;
    /**
     * 基础订单状态(不同类型的订单可以不使用这些状态，但是不能冲突)
     * @var unknown
     */
    public $order_status = [
        self::ORDER_CREATE => [
            'status' => self::ORDER_CREATE,
            'name' => '待支付',
            'is_allow_refund' => 0,
            'icon' => 'public/uniapp/order/order-icon.png',
            'action' => [
                [
                    'action' => 'orderClose',
                    'title' => '关闭订单',
                    'color' => ''
                ],
                [
                    'action' => 'orderAddressUpdate',
                    'title' => '修改地址',
                    'color' => ''
                ],
                [
                    'action' => 'orderAdjustMoney',
                    'title' => '调整价格',
                    'color' => ''
                ],
                [
                    'action' => 'offlinePay',
                    'title' => '调整价格',
                    'color' => ''
                ],
            ],
            'member_action' => [
                [
                    'action' => 'orderClose',
                    'title' => '关闭订单',
                    'color' => ''
                ],
                [
                    'action' => 'orderPay',
                    'title' => '支付',
                    'color' => ''
                ],
            ],
            'color' => ''
        ],
        self::ORDER_PAY => [
            'status' => self::ORDER_PAY,
            'name' => '待发货',
            'is_allow_refund' => 0,
            'icon' => 'public/uniapp/order/order-icon-send.png',
            'action' => [

            ],
            'member_action' => [

            ],
            'color' => ''
        ],
        self::ORDER_DELIVERY => [
            'status' => self::ORDER_DELIVERY,
            'name' => '已发货',
            'is_allow_refund' => 1,
            'icon' => 'public/uniapp/order/order-icon-receive.png',
            'action' => [
                [
                    'action' => 'takeDelivery',
                    'title' => '确认收货',
                    'color' => ''
                ],
            ],
            'member_action' => [
            ],
            'color' => ''
        ],
        self::ORDER_TAKE_DELIVERY => [
            'status' => self::ORDER_TAKE_DELIVERY,
            'name' => '已收货',
            'is_allow_refund' => 1,
            'icon' => 'public/uniapp/order/order-icon-received.png',
            'action' => [
            ],
            'member_action' => [
            ],
            'color' => ''
        ],


        //todo 新增虚拟 订单状态
        self::ORDER_WAIT_VERIFY => [
            'status' => self::ORDER_WAIT_VERIFY,
            'name' => '待使用',
            'is_allow_refund' => 1,
            'icon' => 'public/uniapp/order/order-icon-close.png',
            'action' => [

            ],
            'member_action' => [

            ],
            'color' => ''
        ],
        self::ORDER_VERIFYED => [
            'status' => self::ORDER_VERIFYED,
            'name' => '已使用',
            'is_allow_refund' => 1,
            'icon' => 'public/uniapp/order/order-icon-close.png',
            'action' => [

            ],
            'member_action' => [

            ],
            'color' => ''
        ],
        self::ORDER_COMPLETE => [
            'status' => self::ORDER_COMPLETE,
            'name' => '已完成',
            'is_allow_refund' => 1,
            'icon' => 'public/uniapp/order/order-icon-received.png',
            'action' => [
            ],
            'member_action' => [

            ],
            'color' => ''
        ],
        self::ORDER_CLOSE => [
            'status' => self::ORDER_CLOSE,
            'name' => '已关闭',
            'is_allow_refund' => 0,
            'icon' => 'public/uniapp/order/order-icon-close.png',
            'action' => [

            ],
            'member_action' => [

            ],
            'color' => ''
        ],

    ];
    /**
     * 基础支付方式(不考虑实际在线支付方式或者货到付款方式)
     * @var unknown
     */
    public $pay_type = [
        'ONLINE_PAY' => '在线支付',
        'BALANCE' => '余额支付',
        'OFFLINE_PAY' => '线下支付',
        'POINT' => '积分兑换'
    ];

    /**
     * 线上收款方式
     * @var array
     */
    public static $online_pay_type = [ 'wechatpay', 'alipay' ];

    /**
     * 订单类型
     *
     * @var int
     */
    public $order_type = [
        1 => '物流订单',
        2 => '自提订单',
        3 => '外卖订单',
        4 => '虚拟订单',
        5 => '收银订单'
    ];

    /**
     * 获取支付方式
     */
    public function getPayType($params = [])
    {
        $order_type = $params[ 'order_type' ] ?? '';
        //获取订单基础的其他支付方式
        if (!empty($order_type)) {
            $order_model = $this->getOrderModel([ 'order_type' => $order_type ]);
            $pay_type = $order_model->pay_type;
        } else {
            $pay_type = $this->pay_type;
        }
        //获取当前所有在线支付方式
        $onlinepay = event('PayType', []);
        if (!empty($onlinepay)) {
            foreach ($onlinepay as $k => $v) {
                $pay_type[ $v[ 'pay_type' ] ] = $v[ 'pay_type_name' ];
            }
        }
        return $pay_type;
    }

    /**
     * 订单来源
     */
    public function getOrderFromList($params = [])
    {
        $order_from_list = config('app_type');
        $from_event_list = event('OrderFromList', $params);//没有的话就别返回值了(未来插件订单场景扩展)
        foreach ($from_event_list as $k => $v) {
            $order_from_list = array_merge($order_from_list, $v);
        }
        return $order_from_list;

    }

    /**
     * 订单类型(根据物流配送来区分)
     */
    public function getOrderTypeStatusList()
    {
        $list = [];
        $all_order_list = array_column($this->order_status, 'name', 'status');
        $all_order_list[ 'refunding' ] = '退款中';
        $list[ 'all' ] = array (
            'name' => '全部',
            'type' => 'all',
            'status' => $all_order_list
        );
        if (!addon_is_exit('cashier')) {
            unset($this->order_type[ 5 ]);
        }
        foreach ($this->order_type as $k => $v) {
//            switch ( $k ) {
//                case 1:
//                    $order_model = new Order();
//                    break;
//                case 2:
//                    $order_model = new StoreOrder();
//                    break;
//                case 3:
//                    $order_model = new LocalOrder();
//                    break;
//                case 4:
//                    $order_model = new VirtualOrder();
//                    break;
//                case 5:
//                    $order_model = new CashOrder();
//                    break;
//            }
            $order_model = $this->getOrderModel([ 'order_type' => $k ]);
            $temp_order_list = array_column($order_model->order_status, 'name', 'status');
            $temp_order_list[ 'refunding' ] = '退款中';

            $item = array (
                'name' => $v,
                'type' => $k,
                'status' => $temp_order_list
            );
            $list[ $k ] = $item;
        }
        return $list;
    }

    /**
     * 生成订单编号
     *
     * @param unknown $site_id
     */
    public function createOrderNo($site_id)
    {
        $time_str = date('YmdHi');
        $max_no = Cache::get($site_id . '_' . $time_str);
        if (!isset($max_no) || empty($max_no)) {
            $max_no = 1;
        } else {
            $max_no = $max_no + 1;
        }
        $order_no = $time_str . sprintf('%04d', $max_no);
        Cache::set($site_id . '_' . $time_str, $max_no);
        return $order_no;
    }
    /**********************************************************************************订单操作基础方法（订单关闭，订单完成，订单调价）开始********/

    /**
     * 订单删除
     * @param $condition
     * @return array
     */
    public function deleteOrder($condition)
    {
        $res = model('order')->update([ 'is_delete' => 1 ], $condition);
        if ($res === false) {
            return $this->error();
        } else {
            return $this->success($res);
        }
    }

    /**
     * 订单完成
     *
     * @param int $order_id
     */
    public function orderComplete($order_id)
    {
        $cache = Cache::get('order_complete_execute_' . $order_id);
        if (empty($cache)) {
            Cache::set('order_complete_execute_' . $order_id, 1);
        } else {
            return $this->success();
        }

        $lock_result = $this->verifyOrderLock($order_id);
        if ($lock_result[ 'code' ] < 0)
            return $lock_result;

        $order_info = model('order')->getInfo([ [ 'order_id', '=', $order_id ] ], '*');
        $site_id = $order_info[ 'site_id' ];
        if ($order_info[ 'order_status' ] == self::ORDER_COMPLETE) {
            return $this->success();
        }

        $order_action_array = $this->getOrderCommonAction($order_info, self::ORDER_COMPLETE);
        $order_data = array (
            'order_status' => $order_action_array[ 'order_status' ],
            'order_status_name' => $order_action_array[ 'order_status_name' ],
            'order_status_action' => $order_action_array[ 'order_status_action' ],
            'finish_time' => time()
        );

        $config = new Config();
        $order_event = $config->getOrderEventTimeConfig($order_info[ 'site_id' ]);
        $after_sales_time = $order_event[ 'data' ][ 'value' ][ 'after_sales_time' ] ?? 0;
        if ($after_sales_time > 0) {
            $cron = new Cron();
            $execute_time = strtotime("+ {$after_sales_time} day");
            $cron->addCron(1, 0, '订单售后自动关闭', 'CronOrderAfterSaleClose', $execute_time, $order_id);
        } else {
            $order_data[ 'is_enable_refund' ] = 0;
        }

        $res = model('order')->update($order_data, [ [ 'order_id', '=', $order_id ] ]);
        Cache::set('order_complete_execute_' . $order_id, '');
        //修改用户表order_complete_money和order_complete_num

        model('member')->setInc([ [ 'member_id', '=', $order_info[ 'member_id' ] ] ], 'order_complete_money', $order_info[ 'order_money' ] - $order_info[ 'refund_money' ]);
        model('member')->setInc([ [ 'member_id', '=', $order_info[ 'member_id' ] ] ], 'order_complete_num');
        event('OrderComplete', [ 'order_id' => $order_id ]);
        $order_refund_model = new OrderRefund();
        //订单项移除可退款操作
        $order_refund_model->removeOrderGoodsRefundAction([ [ 'order_id', '=', $order_id ] ]);
        //订单完成
        $message_model = new Message();
        $message_model->sendMessage([ 'keywords' => 'ORDER_COMPLETE', 'order_id' => $order_id, 'site_id' => $order_info[ 'site_id' ] ]);
        // 买家订单完成通知商家
        $message_model->sendMessage([ 'keywords' => 'BUYER_ORDER_COMPLETE', 'order_id' => $order_id, 'site_id' => $order_info[ 'site_id' ] ]);


        $log_data = array (
            'order_id' => $order_id,
            'action' => 'complete',
            'site_id' => $site_id,
            'is_auto' => 1,//todo 当前业务默认是系统任务完成订单
        );
        ( new OrderLog() )->addLog($log_data);
        return $this->success($res);
    }

    /**
     * 订单关闭
     * @param int $order_id
     */
    public function orderClose($order_id, $log_data = [], $close_cause = '')
    {

        model('order')->startTrans();
        try {
            $order_info = model('order')->getInfo([ [ 'order_id', '=', $order_id ] ], '*');
            if ($order_info[ 'order_status' ] == -1) {
                model('order')->commit();
                return $this->success();
            }
            $local_result = $this->verifyOrderLock($order_info);
            if ($local_result[ 'code' ] < 0) {
                model('order')->rollback();
                return $local_result;
            }

            // 如果订单未支付关闭 扣除冻结中的余额
            if ($order_info[ 'order_status' ] == self::ORDER_CREATE) {
                $pay_info = model('pay')->getInfo([ [ 'out_trade_no', '=', $order_info[ 'out_trade_no' ] ] ], 'member_id,balance,balance_money');
                if (!empty($pay_info) && $pay_info[ 'member_id' ]) {
                    if ($pay_info[ 'balance' ]) model('member')->setDec([ [ 'site_id', '=', $order_info[ 'site_id' ] ], [ 'member_id', '=', $pay_info[ 'member_id' ] ] ], 'balance_lock', $pay_info[ 'balance' ]);
                    if ($pay_info[ 'balance_money' ]) model('member')->setDec([ [ 'site_id', '=', $order_info[ 'site_id' ] ], [ 'member_id', '=', $pay_info[ 'member_id' ] ] ], 'balance_money_lock', $pay_info[ 'balance_money' ]);
                }
            }

            $order_data = array (
                'order_status' => self::ORDER_CLOSE,
                'order_status_name' => $this->order_status[ self::ORDER_CLOSE ][ 'name' ],
                'order_status_action' => json_encode($this->order_status[ self::ORDER_CLOSE ], JSON_UNESCAPED_UNICODE),
                'close_time' => time(),
                'is_enable_refund' => 0,
                'is_evaluate' => 0,
                'close_cause' => $close_cause
            );

            $res = model('order')->update($order_data, [ [ 'order_id', '=', $order_id ] ]);

            //更改接龙订单状态
            model('promotion_jielong_order')->update([
                'order_status' => self::ORDER_CLOSE,
                'order_status_name' => $this->order_status[ self::ORDER_CLOSE ][ 'name' ],
                'order_status_action' => json_encode($this->order_status[ self::ORDER_CLOSE ], JSON_UNESCAPED_UNICODE),
                'close_time' => time(),
            ], [ [ 'relate_order_id', '=', $order_id ] ]);

            //库存处理
            $condition = array (
                [ 'order_id', '=', $order_id ]
            );
            //循环订单项 依次返还库存 并修改状态
            $order_goods_list = model('order_goods')->getList($condition, 'order_goods_id,sku_id,num,refund_status,use_point,card_item_id');
            $order_refund_model = new OrderRefund();
            $goods_model = new Goods();

            $is_exist_refund = false;//是否存在退款
            $refund_goods_card = [];
            $refund_point = 0;
            $order_stock = new OrderStock();
            foreach ($order_goods_list as $k => $v) {
                //如果是已维权完毕的订单项, 库存不必再次返还
                if ($v[ 'refund_status' ] != $order_refund_model::REFUND_COMPLETE) {
                    $item_param = array (
                        'sku_id' => $v[ 'sku_id' ],
                        'num' => $v[ 'num' ],
                    );
                    //返还销售库存
                    $order_stock->incOrderSaleStock($v[ 'sku_id' ], $v[ 'num' ], $order_info[ 'store_id' ]);
//                    $goods_stock_model->incStock($item_param);

                    $refund_point += $v[ 'use_point' ];

                    // 是否有使用次卡
                    if ($v[ 'card_item_id' ]) {
                        $refund_goods_card[] = [ 'type' => 'order', 'relation_id' => $v[ 'order_goods_id' ] ];
                    }
                }

                if ($v[ 'refund_status' ] == $order_refund_model::REFUND_COMPLETE) {
                    $is_exist_refund = true;
                }

                //减少商品销量(必须支付过)
                if ($order_info[ 'pay_status' ] > 0) {
                    $goods_model->decGoodsSaleNum($v[ 'sku_id' ], $v[ 'num' ], $order_info[ 'store_id' ]);
                }
            }

            //订单项移除可退款操作
            $order_refund_model->removeOrderGoodsRefundAction([ [ 'order_id', '=', $order_id ] ]);

            //返还店铺优惠券
            $coupon_id = $order_info[ 'coupon_id' ];
            if ($coupon_id > 0) {
                $coupon_model = new Coupon();
                $coupon_model->refundCoupon($coupon_id, $order_info[ 'member_id' ]);
            }
            //平台优惠券

            //平台余额  退还余额
            if (!$is_exist_refund) {//因为订单完成后  只有全部退款完毕订单才会关闭
                if ($order_info[ 'balance_money' ] > 0) {
                    $member_account_model = new MemberAccount();
                    $result = $member_account_model->addMemberAccount($order_info[ 'site_id' ], $order_info[ 'member_id' ], 'balance', $order_info[ 'balance_money' ], 'refund', $order_id, '订单关闭返还');
                }
                // 订单关闭返还积分
                if ($refund_point > 0) {
                    $member_account_model = new MemberAccount();
                    $result = $member_account_model->addMemberAccount($order_info[ 'site_id' ], $order_info[ 'member_id' ], 'point', $refund_point, 'refund', $order_id, '订单关闭返还');
                }
            }

            // 退还次卡
            if (!empty($refund_goods_card) && addon_is_exit('cardservice', $order_info[ 'site_id' ])) {
                ( new MemberCard() )->refund($refund_goods_card);
            }

            //订单关闭后操作
            $close_result = event('OrderClose', $order_info);

            if (empty($close_result)) {
                foreach ($close_result as $k => $v) {
                    if (!empty($v) && $v[ 'code' ] < 0) {
                        model('order')->rollback();
                        return $v;
                    }
                }
            }

            //订单关闭消息
            $message_model = new Message();
            $res = $message_model->sendMessage([ 'keywords' => 'ORDER_CLOSE', 'order_id' => $order_id, 'site_id' => $order_info[ 'site_id' ] ]);

            //记录订单日志 start
            if (!empty($log_data)) {

                if ($log_data[ 'action_way' ] == 1) {
                    $member_info = model('member')->getInfo([ 'member_id' => $log_data[ 'uid' ] ], 'nickname');
                    $buyer_name = empty($member_info[ 'nickname' ]) ? '' : '【' . $member_info[ 'nickname' ] . '】';
                    $log_data[ 'nick_name' ] = $buyer_name;
                    $action = '买家'.$buyer_name.'关闭了订单';
                } else {
                    $action = '商家【' . $log_data[ 'nick_name' ] . '】关闭了订单';
                }
                $log_data = array_merge($log_data, [
                    'order_id' => $order_id,
                    'action' => $action,
                    'order_status' => self::ORDER_CLOSE,
                    'order_status_name' => $this->order_status[ self::ORDER_CLOSE ][ 'name' ]
                ]);
                $this->addOrderLog($log_data);
            } else {
                $action = !empty($close_cause) ? $close_cause : '系统自动关闭了订单(长时间未支付)';
                $log_data = [
                    'uid' => 0,
                    'nick_name' => '系统',
                    'action_way' => 2
                ];

                $log_data = array_merge($log_data, [
                    'order_id' => $order_id,
                    'action' => $action,
                    'order_status' => self::ORDER_CLOSE,
                    'order_status_name' => $this->order_status[ self::ORDER_CLOSE ][ 'name' ]
                ]);
                $this->addOrderLog($log_data);
            }
            //记录订单日志 end

            model('order')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('order')->rollback();

            return $this->error('', $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * 订单线上支付
     * @param unknown $out_trade_no
     */
    public function orderOnlinePay($data)
    {
        model('order')->startTrans();
        try {
            $out_trade_no = $data[ 'out_trade_no' ];
            $member_account_model = new MemberAccount();
            $order_list = model('order')->getList([ [ 'out_trade_no', '=', $out_trade_no ] ], '*');
            $total_order_money = model('order')->getSum([ [ 'out_trade_no', '=', $out_trade_no ] ], 'order_money');
            $pay_info = model('pay')->getInfo([ [ 'out_trade_no', '=', $out_trade_no ] ]);
            $message_list = [];
            //订单支付消息
            foreach ($order_list as $k => $order) {
                if ($order[ 'order_status' ] == -1) {
                    continue;
                }
                if ($order[ 'pay_status' ] == 1) {
                    continue;
                }
//                switch ( $order[ 'order_type' ] ) {
//                    case 1:
//                        $order_model = new Order();
//                        break;
//                    case 2:
//                        $order_model = new StoreOrder();
//                        break;
//                    case 3:
//                        $order_model = new LocalOrder();
//                        break;
//                    case 4:
//                        $order_model = new VirtualOrder();
//                        break;
//                    case 5:
//                        $order_model = new CashOrder();
//                        break;
//                }
                $order_model = $this->getOrderModel($order);
                if (isset($data[ 'log_data' ])) {
                    $pay_result = $order_model->orderPay($order, $data[ 'pay_type' ], $data[ 'log_data' ]);
                } else {
                    $pay_result = $order_model->orderPay($order, $data[ 'pay_type' ]);
                }
                //失败的话主动退款
                if (!empty($pay_result) && $pay_result[ 'code' ] < 0) {
                    //主动退款
                    $order_refund_model = new OrderRefund();
                    $refund_result = $order_refund_model->activeRefund($order[ 'order_id' ], $pay_result[ 'message' ], '订单退款');

                    if ($refund_result[ 'code' ] < 0) {
                        model('order')->rollback();
                        return $refund_result;
                    }
                    model('order')->commit();
                    return $this->success();
                }
                //同时将用户表的order_money和order_num更新
                model('member')->setInc([ [ 'member_id', '=', $order[ 'member_id' ] ] ], 'order_money', $order[ 'order_money' ]);
                model('member')->setInc([ [ 'member_id', '=', $order[ 'member_id' ] ] ], 'order_num');
                //更新最后消费时间
                Member::modifyLastConsumTime($order[ 'member_id' ]);

                // 订单绑定余额
                if (( $pay_info[ 'balance' ] || $pay_info[ 'balance_money' ] ) && $order[ 'order_money' ] && $total_order_money) {
                    // 如果是最后一个订单
                    $total_balance = 0;
                    $balance = 0;
                    $balance_money = 0;
                    if ($k == ( count($order_list) - 1 )) {
                        $balance = $pay_info[ 'surplus_balance' ] ?? $pay_info[ 'balance' ];
                        $balance_money = $pay_info[ 'surplus_balance_money' ] ?? $pay_info[ 'balance_money' ];
                    } else {
                        $ratio = $order[ 'order_money' ] / $total_order_money;
                        if ($pay_info[ 'balance' ]) {
                            $balance = round($pay_info[ 'balance' ] * $ratio, 2);
                            $pay_info[ 'surplus_balance' ] = $pay_info[ 'balance' ] - $balance;
                        }
                        if ($pay_info[ 'balance_money' ]) {
                            $balance_money = round($pay_info[ 'balance_money' ] * $ratio, 2);
                            $pay_info[ 'surplus_balance_money' ] = $pay_info[ 'balance_money' ] - $balance_money;
                        }
                    }
                    if ($balance > 0) {
                        $use_res = $member_account_model->addMemberAccount($order[ 'site_id' ], $order[ 'member_id' ], 'balance', -$balance, 'order', $order[ 'order_id' ], '订单消费扣除');
                        if ($use_res[ 'code' ] != 0) {
                            model('order')->rollback();
                            return $use_res;
                        }
                    }
                    if ($balance_money > 0) {
                        $use_res = $member_account_model->addMemberAccount($order[ 'site_id' ], $order[ 'member_id' ], 'balance_money', -$balance_money, 'order', $order[ 'order_id' ], '订单消费扣除');
                        if ($use_res[ 'code' ] != 0) {
                            model('order')->rollback();
                            return $use_res;
                        }
                    }
                    $total_balance = $balance + $balance_money;
                    if ($total_balance) model('order')->update([ 'balance_money' => $total_balance, 'pay_money' => ( $order[ 'pay_money' ] - $total_balance ) ], [ [ 'order_id', '=', $order[ 'order_id' ] ] ]);
                }

                //支付后商品增加销量
                $order_goods_list = model('order_goods')->getList([ [ 'order_id', '=', $order[ 'order_id' ] ] ], 'sku_id,num,goods_class');
                $goods_model = new Goods();
                foreach ($order_goods_list as $ck => $v) {
                    $goods_model->incGoodsSaleNum($v[ 'sku_id' ], $v[ 'num' ]);
                }

                //订单项增加可退款操作
                $order_refund_model = new OrderRefund();
                $order_refund_model->initOrderGoodsRefundAction([ [ 'order_id', '=', $order[ 'order_id' ] ] ]);

                $message_list[] = $order;

                $order = model('order')->getInfo([ [ 'order_id', '=', $order[ 'order_id' ] ] ], '*');
                $res = event('OrderPay', $order);

                if ($pay_info[ 'balance' ]) model('member')->setDec([ [ 'site_id', '=', $pay_info[ 'site_id' ] ], [ 'member_id', '=', $pay_info[ 'member_id' ] ] ], 'balance_lock', $pay_info[ 'balance' ]);
                if ($pay_info[ 'balance_money' ]) model('member')->setDec([ [ 'site_id', '=', $pay_info[ 'site_id' ] ], [ 'member_id', '=', $pay_info[ 'member_id' ] ] ], 'balance_money_lock', $pay_info[ 'balance_money' ]);

                $message_model = new Message();
                // 发送消息
                $param = [ 'keywords' => 'ORDER_PAY' ];
                $param = array_merge($param, $order);
                $message_model->sendMessage($param);

                //商家消息
                $param = [ 'keywords' => 'BUYER_PAY' ];
                $param = array_merge($param, $order);
                $message_model->sendMessage($param);
            }

            model('order')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('order')->rollback();
            return $this->error('', $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * 订单线下支付
     * @param unknown $order_id
     * @return unknown
     */
    public function orderOfflinePay($order_id, $log_data = [])
    {
        model('order')->startTrans();
        try {
            $split_result = $this->splitOrderPay($order_id);
            if ($split_result[ 'code' ] < 0)
                return $split_result;

            $out_trade_no = $split_result[ 'data' ];
            $pay_model = new Pay();
            $result = $pay_model->onlinePay($out_trade_no, 'OFFLINE_PAY', '', '', $log_data);
            if ($result[ 'code' ] < 0) {
                model('order')->rollback();
                return $result;
            }
            model('order')->commit();
            return $result;
        } catch (\Exception $e) {
            model('order')->rollback();
            return $this->error('', $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * 拆分订单
     * @param $order_ids
     * @return \multitype
     */
    public function splitOrderPay($order_ids, $out_trade_no = '')
    {
        $order_ids = empty($order_ids) ? [] : explode(',', $order_ids);
        $order_condition = array (
            [ 'pay_status', '=', 0 ]
        );
        if (empty($order_ids)) {
            $order_condition[] = [ 'out_trade_no', '=', $out_trade_no ];
        } else {
            $order_condition[] = [ 'order_id', 'in', $order_ids ];
        }
        $order_list = model('order')->getList($order_condition, 'pay_money,order_name,out_trade_no,order_id,pay_status,site_id,member_id,member_card_order');
        $order_count = count($order_list);
        //判断订单数是否匹配
        if (count($order_ids) > $order_count)
            return $this->error([], '选中订单中包含已支付数据!');

        $rewrite_order_ids = [];//受影响的id组
        $close_out_trade_no_array = [];
        $pay_money = 0;
        $pay_model = new Pay();
        $order_name_array = [];
        $site_id = 0;
        $member_card_order = [];


        foreach ($order_list as $order_k => $item) {
            $member_id = $item[ 'member_id' ];
            $pay_money += $item[ 'pay_money' ];//累加金额
            $order_name_array[] = $item[ 'order_name' ];
            if (!in_array($item[ 'out_trade_no' ], $close_out_trade_no_array)) {
                $close_out_trade_no_array[] = $item[ 'out_trade_no' ];
            }
            $site_id = $item[ 'site_id' ];
            if (!empty($item[ 'member_card_order' ])) {
                array_push($member_card_order, $item[ 'member_card_order' ]);
            }
//            $field_list = model('order')->getColumn([['out_trade_no', '=', $item['out_trade_no']]], 'order_id');
        }

        //现有的支付单据完全匹配
        if (count($close_out_trade_no_array) == 1) {
            $out_trade_no = $close_out_trade_no_array[ 0 ];
            //必须是有效的支付单据
            $pay_info = $pay_model->getPayInfo($out_trade_no)[ 'data' ];
            if (!empty($pay_info)) {
                $temp_order_count = model('order')->getCount([ [ 'out_trade_no', '=', $out_trade_no ], [ 'order_id', 'not in', $order_ids ] ], 'order_id');
                if ($temp_order_count == 0 && $pay_info[ 'balance' ] == 0 && $pay_info[ 'balance_money' ] == 0) {
                    return $this->success($out_trade_no);
                }
            }
        }
        //循环管理订单支付单据
        foreach ($close_out_trade_no_array as $close_k => $close_v) {
            $result = $pay_model->deletePay($close_v);//关闭旧支付单据
            if ($result[ 'code' ] < 0) {
                return $this->error([], '选中订单中包含已支付数据!');
            }
        }
        $order_name = implode(',', $order_name_array);
        //生成新的支付单据
        $out_trade_no = $pay_model->createOutTradeNo($member_id ?? 0);
        //修改交易流水号为新生成的
        model('order')->update([ 'out_trade_no' => $out_trade_no ], [ [ 'order_id', 'in', $order_ids ], [ 'pay_status', '=', 0 ] ]);
        if (!empty($member_card_order)) model('member_level_order')->update([ 'out_trade_no' => $out_trade_no ], [ [ 'order_id', 'in', $member_card_order ], [ 'pay_status', '=', 0 ] ]);
        $result = $pay_model->addPay($site_id, $out_trade_no, '', $order_name, $order_name, $pay_money, '', 'OrderPayNotify', '');

        return $this->success($out_trade_no);
    }
    /************************************************************************ 订单调价 start **************************************************************************/
    /**
     * 订单金额调整 按整单调整
     * @param string $order_product_adjust_list order_product_id:adjust_money,order_product_id:adjust_money
     * @param string $shipping_money
     */
    public function orderAdjustMoney($order_id, $adjust_money, $delivery_money)
    {
        model('order')->startTrans();
        try {
            //查询订单
            $order_info = model('order')->getInfo([ 'order_id' => $order_id ], 'site_id, out_trade_no,delivery_money, adjust_money, pay_money, order_money, promotion_money, coupon_money, goods_money, invoice_money, invoice_delivery_money, promotion_money, coupon_money, invoice_rate, invoice_delivery_money, balance_money, point_money, member_card_money');
            if (empty($order_info))
                return $this->error('', '找不到订单');

            if ($delivery_money < 0)
                return $this->error('', '配送费用不能小于0!');

            $real_goods_money = $order_info[ 'goods_money' ] - $order_info[ 'promotion_money' ] - $order_info[ 'coupon_money' ] - $order_info[ 'point_money' ];//计算出订单真实商品金额
            $new_goods_money = $real_goods_money + $adjust_money;

            if ($new_goods_money < 0)
                return $this->error('', '真实商品金额不能小于0!');

            $invoice_money = round($new_goods_money * $order_info[ 'invoice_rate' ] / 100, 2);
            $new_order_money = $invoice_money + $new_goods_money + $delivery_money + $order_info[ 'invoice_delivery_money' ] + $order_info[ 'member_card_money' ];

            if ($new_order_money < 0)
                return $this->error('', '订单金额不能小于0!');

            $pay_money = $new_order_money - $order_info[ 'balance_money' ];
            if ($pay_money < 0)
                return $this->error('', '实际支付不能小于0!');

            $data_order = array (
                'delivery_money' => $delivery_money,
                'pay_money' => $pay_money,
                'adjust_money' => $adjust_money,
                'order_money' => $new_order_money,
                'invoice_money' => $invoice_money
            );
            model('order')->update($data_order, [ [ 'order_id', '=', $order_id ] ]);

            $order_goods_list = model('order_goods')->getList([ [ 'order_id', '=', $order_id ] ], 'order_goods_id,goods_money,adjust_money,coupon_money,promotion_money,point_money');
            //将调价摊派到所有订单项
            $real_goods_money = $order_info[ 'goods_money' ] - $order_info[ 'promotion_money' ] - $order_info[ 'coupon_money' ] - $order_info[ 'point_money' ];
            $this->distributionGoodsAdjustMoney($order_goods_list, $real_goods_money, $adjust_money);

            //关闭原支付  生成新支付
            $pay_model = new Pay();
            $pay_result = $pay_model->deletePay($order_info[ 'out_trade_no' ]);//关闭旧支付单据
            if ($pay_result[ 'code' ] < 0) {
                model('order')->rollback();
                return $pay_result;
            }
            $out_trade_no = $pay_result[ 'data' ];
            // 调价之后支付金额为0
            if ($pay_money == 0) {
                $this->orderOfflinePay($order_id);
            }

            model('order')->commit();

            return $this->success();
        } catch (\Exception $e) {
            model('order')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 按比例摊派订单调价
     */
    public function distributionGoodsAdjustMoney($goods_list, $goods_money, $adjust_money)
    {
        $temp_adjust_money = $adjust_money;
        $last_key = count($goods_list) - 1;
        foreach ($goods_list as $k => $v) {
            $item_goods_money = $v[ 'goods_money' ] - $v[ 'promotion_money' ] - $v[ 'coupon_money' ] - $v[ 'point_money' ];
            if ($last_key != $k) {
                $item_adjust_money = round($item_goods_money / $goods_money * $adjust_money, 2);
            } else {
                $item_adjust_money = $temp_adjust_money;
            }
            $temp_adjust_money -= $item_adjust_money;
            $real_goods_money = $item_goods_money + $item_adjust_money;
            $real_goods_money = $real_goods_money < 0 ? 0 : $real_goods_money;
            $order_goods_data = array (
                'adjust_money' => $item_adjust_money,
                'real_goods_money' => $real_goods_money,
            );
            model('order_goods')->update($order_goods_data, [ [ 'order_goods_id', '=', $v[ 'order_goods_id' ] ] ]);
        }
        return $this->success();
    }


    /**
     * 订单删除
     * @param int $order_id
     */
    public function orderDelete($order_id, $user_info = [])
    {

        model('order')->startTrans();
        try {
            $order_info = model('order')->getInfo([ [ 'order_id', '=', $order_id ] ], 'order_status,site_id,order_status_name');
            if ($order_info[ 'order_status' ] != -1) {
                return $this->error([], '只有已经关闭的订单才能删除');
            }
            $order_data = array (
                'is_delete' => 1
            );

            //记录订单日志 start
            if ($user_info) {
                $log_data = [
                    'order_id' => $order_id,
                    'uid' => $user_info[ 'uid' ],
                    'nick_name' => $user_info[ 'username' ],
                    'action' => '商家删除了订单',
                    'action_way' => 2,
                    'order_status' => $order_info[ 'order_status' ],
                    'order_status_name' => $order_info[ 'order_status_name' ]
                ];
                $this->addOrderLog($log_data);
            }
            //记录订单日志 end
            $res = model('order')->update($order_data, [ [ 'order_id', '=', $order_id ] ]);
            model('order')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('order')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /************************************************************************ 订单调价 end **************************************************************************/
    /**
     * 订单编辑
     * @param $data
     * @param $condition
     */
    public function orderUpdate($data, $condition, $log_data = [])
    {
        $order_model = model('order');
        $res = $order_model->update($data, $condition);
        if ($res === false) {
            return $this->error();
        } else {
            //记录订单日志 start
            if ($log_data) {
                $order_info = model('order')->getInfo([ 'order_id' => $log_data[ 'order_id' ] ], 'order_status,order_status_name');
                $log_data = array_merge($log_data, [
                    'order_status' => $order_info[ 'order_status' ],
                    'order_status_name' => $order_info[ 'order_status_name' ]
                ]);
                $this->addOrderLog($log_data);
            }
            //记录订单日志 end
            return $this->success($res);
        }
    }

    /**
     * 订单发货
     * @param $order_id
     * @return array
     */
    public function orderCommonDelivery($order_id, $log_data = [])
    {
        $order_common_model = new OrderCommon();
        $local_result = $order_common_model->verifyOrderLock($order_id);
        if ($local_result[ 'code' ] < 0)
            return $local_result;

        $order_info = model('order')->getInfo([ [ 'order_id', '=', $order_id ] ], 'order_type,site_id');
        $order_model = $this->getOrderModel($order_info);
//        switch ( $order_info[ 'order_type' ] ) {
//            case 1:
//                $order_model = new Order();
//                break;
//            case 2:
//                $order_model = new StoreOrder();
//                break;
//            case 3:
//                $order_model = new LocalOrder();
//                break;
//            case 4:
//                $order_model = new VirtualOrder();
//                break;
//        }
        $result = $order_model->orderDelivery($order_id, $log_data);
        return $result;
    }

    /**
     * 订单收货
     *
     * @param int $order_id
     */
    public function orderCommonTakeDelivery($order_id, $log_data = [])
    {
        $order_info = model('order')->getInfo([ [ 'order_id', '=', $order_id ] ], '*');
        if (empty($order_info))
            return $this->error([], 'ORDER_EMPTY');

        $local_result = $this->verifyOrderLock($order_id);
        if ($local_result[ 'code' ] < 0)
            return $local_result;

        $order_status =  $order_info[ 'order_status' ];
        $virtual_order_model = new VirtualOrder();
        if ($order_status == self::ORDER_TAKE_DELIVERY || $order_status == $virtual_order_model::ORDER_VERIFYED) {
            return $this->error('', '该订单已收货');
        }

//        switch ( $order_info[ 'order_type' ] ) {
//            case 1:
//                $order_model = new Order();
//                break;
//            case 2:
//                $order_model = new StoreOrder();
//                break;
//            case 3:
//                $order_model = new LocalOrder();
//                break;
//            case 4:
//                $order_model = new VirtualOrder();
//                break;
//        }

        $order_model = $this->getOrderModel($order_info);
        model('order')->startTrans();
        try {
            $res = $order_model->orderTakeDelivery($order_id);
            //改变订单状态

            //todo  如果是虚拟商品并且有虚拟码的话, 订单状态应该为已使用
            if($order_status == $virtual_order_model::ORDER_WAIT_VERIFY){
                $order_action_array = $this->getOrderCommonAction($order_model, $virtual_order_model::ORDER_VERIFYED);
            }else{
                $order_action_array = $this->getOrderCommonAction($order_model, $order_model::ORDER_TAKE_DELIVERY);
            }

            $order_data = array (
                'order_status' => $order_action_array[ 'order_status' ],
                'order_status_name' => $order_action_array[ 'order_status_name' ],
                'order_status_action' => $order_action_array[ 'order_status_action' ],
                'is_evaluate' => 1,
                'evaluate_status' => 0,
                'evaluate_status_name' => '待评价',
                'sign_time' => time()
            );
            $res = model('order')->update($order_data, [ [ 'order_id', '=', $order_id ] ]);
            $this->addCronOrderComplete($order_id, $order_info[ 'site_id' ]);

            //视频号接口返回信息
            $result = event('OrderTakeDelivery', [ 'order_id' => $order_id ]);
            if (!empty($reflt[ 0 ]) && $result[ 0 ][ 'code' ] < 0) {
                model('order')->rollback();
                return $this->error($result);
            }

//            event('OrderComplete', ['order_id' => $order_id]);
            model('order')->commit();

            //记录订单日志 start
            if ($log_data) {
                $action = '商家对订单进行了确认收货';
                if ($log_data[ 'action_way' ] == 1) {
                    $member_info = model('member')->getInfo([ 'member_id' => $log_data[ 'uid' ] ], 'nickname');
                    $buyer_name = empty($member_info[ 'nickname' ]) ? '' : '【' . $member_info[ 'nickname' ] . '】';
                    $log_data[ 'nick_name' ] = $buyer_name;

                    $action = '买家'.$buyer_name.'确认收到货物';
                }
                $log_data = array_merge($log_data, [
                    'order_id' => $order_id,
                    'action' => $action,
                    'order_status' => $order_action_array[ 'order_status' ],
                    'order_status_name' => $order_action_array[ 'order_status_name' ],
                ]);
                $this->addOrderLog($log_data);
            }
            //记录订单日志 end

            return $this->success();
        } catch (\Exception $e) {
            model('order')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 添加订单自动完成事件
     * @param $order_id
     * @param $site_id
     */
    public function addCronOrderComplete($order_id, $site_id)
    {
        //获取订单自动完成时间
        $config_model = new Config();
        $event_time_config_result = $config_model->getOrderEventTimeConfig($site_id);
        $event_time_config = $event_time_config_result[ 'data' ];
        $now_time = time();
        if (!empty($event_time_config)) {
            $execute_time = $now_time + $event_time_config[ 'value' ][ 'auto_complete' ] * 86400;//自动完成时间
        } else {
            $execute_time = $now_time + 86400;//尚未配置  默认一天
        }
        //设置订单自动完成事件
        $cron_model = new Cron();
        $result = $cron_model->addCron(1, 0, '订单自动完成', 'CronOrderComplete', $execute_time, $order_id);
        return $this->success($result);
    }

    /**
     * 订单解除锁定
     * @param $order_id
     */
    public function orderUnlock($order_id)
    {
        $data = array (
            'is_lock' => 0
        );
        $res = model('order')->update($data, [ [ 'order_id', '=', $order_id ] ]);
        return $res;
    }

    /**
     * 订单锁定
     * @param $order_id
     * @return mixed
     */
    public function orderLock($order_id)
    {
        $data = array (
            'is_lock' => 1
        );
        $res = model('order')->update($data, [ [ 'order_id', '=', $order_id ] ]);
        return $res;
    }

    /**
     * 验证订单锁定状态
     * @param $order_id
     */
    public function verifyOrderLock($param)
    {
        if (!is_array($param)) {
            $order_info = model('order')->getInfo([ [ 'order_id', '=', $param ] ], 'is_lock');
        } else {
            $order_info = $param;
        }
        if ($order_info[ 'is_lock' ] == 1) {//判断订单锁定状态
            return $this->error('', 'ORDER_LOCK');
        } else {
            return $this->success();
        }
    }



    /**********************************************************************************订单操作基础方法（订单关闭，订单完成，订单调价）结束********/

    /****************************************************************************订单数据查询（开始）*************************************/

    /**
     * 获取订单详情
     * @param $order_id
     * @return array
     */
    public function getOrderDetail($order_id)
    {
        $order_info = model('order')->getInfo([ [ 'o.order_id', '=', $order_id ] ], 'o.*,s.store_name', 'o', [ ['store s', 'o.store_id = s.store_id', 'left'] ]);
        if (empty($order_info))
            return $this->error('');

        $member_info = model('member')->getInfo([ [ 'member_id', '=', $order_info[ 'member_id' ] ] ], 'nickname');

        $order_info[ 'nickname' ] = $member_info[ 'nickname' ] ?? '';

        $order_info[ 'verifier_name' ] = model('verify')->getValue([ [ 'verify_code', '=', $order_info[ 'delivery_code' ] ] ], 'verifier_name');

        $order_goods_list = model('order_goods')->getList([ [ 'order_id', '=', $order_id ] ]);
        foreach ($order_goods_list as $k => $v) {
            $form_info = model('form_data')->getInfo([ [ 'relation_id', '=', $v[ 'order_goods_id' ] ], [ 'scene', '=', 'goods' ] ], 'form_data');
            if (!empty($form_info)) $order_goods_list[ $k ][ 'form' ] = json_decode($form_info[ 'form_data' ], true);
        }
        $order_info[ 'order_goods' ] = $order_goods_list;
//        switch ( $order_info[ 'order_type' ] ) {
//            case 1:
//                $order_model = new Order();
//                break;
//            case 2:
//                $order_model = new StoreOrder();
//                break;
//            case 3:
//                $order_model = new LocalOrder();
//                break;
//            case 4:
//                $order_model = new VirtualOrder();
//                break;
//            case 5:
//                $order_model = new CashOrder();
//                break;
//        }
        $order_model = $this->getOrderModel($order_info);
        $temp_info = $order_model->orderDetail($order_info);
        $order_info = array_merge($order_info, $temp_info);

        $form_info = model('form_data')->getInfo([ [ 'relation_id', '=', $order_id ], [ 'scene', '=', 'order' ] ], 'form_data');
        if (!empty($form_info)) {
            $order_info[ 'form' ] = json_decode($form_info[ 'form_data' ], true);
        }

        if ($order_info[ 'store_id' ]) $order_info[ 'store_name' ] = model('store')->getValue([ [ 'store_id', '=', $order_info[ 'store_id' ] ] ], 'store_name');

        $order_info[ 'order_log' ] = model('order_log')->getList([ [ 'order_id', '=', $order_id ] ], '*', 'action_time desc,id desc');
        return $this->success($order_info);
    }


    /**
     * 获取订单详情(为退款的订单项)
     * @param array $order_id
     */
    public function getUnRefundOrderDetail($order_id)
    {
        $order_info = model('order')->getInfo([ [ 'order_id', '=', $order_id ] ]);

        if (empty($order_info))
            return $this->error('');

        $member_info = model('member')->getInfo([ [ 'member_id', '=', $order_info[ 'member_id' ] ] ], 'nickname');

        $order_info[ 'nickname' ] = $member_info[ 'nickname' ];

        $order_goods_list = model('order_goods')->getList([ [ 'order_id', '=', $order_id ], [ 'refund_status', '=', 0 ] ]);
        $order_info[ 'order_goods' ] = $order_goods_list;
//        switch ( $order_info[ 'order_type' ] ) {
//            case 1:
//                $order_model = new Order();
//                break;
//            case 2:
//                $order_model = new StoreOrder();
//                break;
//            case 3:
//                $order_model = new LocalOrder();
//                break;
//            case 4:
//                $order_model = new VirtualOrder();
//                break;
//        }
        $order_model = $this->getOrderModel($order_info);
        $temp_info = $order_model->orderDetail($order_info);
        $order_info = array_merge($order_info, $temp_info);

        return $this->success($order_info);
    }


    /**
     * 得到订单基础信息
     * @param $condition
     * @param string $field
     */
    public function getOrderInfo($condition, $field = '*')
    {
        $res = model('order')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 得到订单数量
     * @param $condition
     * @param string $field
     */
    public function getOrderCount($condition, $field = '*', $alias = 'a', $join = null, $group = null)
    {
        $res = model('order')->getCount($condition, $field, $alias, $join, $group);
        return $this->success($res);
    }

    /**
     * 获取订单列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getOrderList($condition = [], $field = '*', $order = '', $limit = null, $group = '', $alias = '', $join = '')
    {
        $list = model('order')->getList($condition, $field, $order, $alias, $join, $group, $limit);
        return $this->success($list);
    }

    /**
     * 获取订单分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getOrderPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*', $alias = 'a', $join = [])
    {
        $order_list = model('order')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        $check_condition = array_column($condition, 2, 0);

        if (!empty($order_list[ 'list' ])) {
            foreach ($order_list[ 'list' ] as $k => $v) {
                if (!empty($check_condition[ 'a.order_status' ])) {
                    $order_list[ 'list' ][ $k ][ 'order_data_status' ] = $check_condition[ 'a.order_status' ];
                }
                $order_goods_list = model('order_goods')->getList([
                    'order_id' => $v[ 'order_id' ]
                ]);
                $order_list[ 'list' ][ $k ][ 'order_goods' ] = $order_goods_list;
                if (isset($v[ 'member_id' ])) {
                    //购买人信息
                    $member_info = model('member')->getInfo([ 'member_id' => $v[ 'member_id' ] ], 'nickname');
                    $order_list[ 'list' ][ $k ][ 'nickname' ] = $member_info[ 'nickname' ] ?? '';
                }
            }
        }
        return $this->success($order_list);
    }

    /**
     * 订单列表（已商品为主）
     * @param array $condition
     * @return array
     */
    public function getOrderGoodsDetailList($condition = [])
    {
        $alias = 'og';
        $join = [
            [
                'order o',
                'o.order_id = og.order_id',
                'left'
            ],
            [
                'member m',
                'm.member_id = og.member_id',
                'left'
            ]
        ];
        $order_field = 'o.order_no,o.site_name,o.order_name,o.order_from_name,o.order_type_name,o.order_promotion_name,o.out_trade_no,o.out_trade_no_2,o.delivery_code,o.order_status_name,o.pay_status,o.delivery_status,o.refund_status,o.pay_type_name,o.delivery_type_name,o.name,o.mobile,o.telephone,o.full_address,o.buyer_ip,o.buyer_ask_delivery_time,o.buyer_message,o.goods_money,o.delivery_money,o.promotion_money,o.coupon_money,o.order_money,o.adjust_money,o.balance_money,o.pay_money,o.refund_money,o.pay_time,o.delivery_time,o.sign_time,o.finish_time,o.remark,o.goods_num,o.delivery_status_name,o.is_settlement,o.delivery_store_name,o.promotion_type_name,concat(full_address,"-",address),m.nickname,o.full_address,';

        $order_goods_field = 'og.refund_status_name,og.sku_name,og.sku_no,og.is_virtual,og.goods_class_name,og.price,og.cost_price,og.num,og.goods_money,og.cost_money,og.delivery_no,og.refund_no,og.refund_type,og.refund_apply_money,og.refund_reason,og.refund_real_money,og.refund_delivery_name,og.refund_delivery_no,og.refund_time,og.refund_refuse_reason,og.refund_action_time,og.real_goods_money,og.refund_remark,og.refund_delivery_remark,og.refund_address,og.is_refund_stock';

        $list = model('order_goods')->getList($condition, $order_field . $order_goods_field, 'og.order_goods_id desc', $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取订单项详情
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getOrderGoodsInfo($condition = [], $field = '*')
    {
        $info = model('order_goods')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取订单列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getOrderGoodsList($condition = [], $field = '*', $order = '', $limit = null, $group = '', $alias = '', $join = '')
    {
        $list = model('order_goods')->getList($condition, $field, $order, $alias, $join, $group, $limit);
        return $this->success($list);
    }
    /****************************************************************************订单数据查询结束*************************************/

    /****************************************************************************会员订单订单数据查询开始*************************************/

    /**
     * 会员订单详情
     * @param $order_id
     * @param $member_id
     */
    public function getMemberOrderDetail($order_id, $member_id, $site_id)
    {
        $order_info = model('order')->getInfo([ [ 'order_id', '=', $order_id ], [ 'member_id', '=', $member_id ], [ 'site_id', '=', $site_id ] ]);
        if (empty($order_info))
            return $this->error([], '当前订单不是本账号的订单!');

        $action = empty($order_info[ 'order_status_action' ]) ? [] : json_decode($order_info[ 'order_status_action' ], true);
        $member_action = $action[ 'member_action' ] ?? [];
        //判断是否增加批量退款操作
        if (!in_array($order_info[ 'order_status' ], [ self::ORDER_CREATE, self::ORDER_COMPLETE, self::ORDER_CLOSE ]) && $order_info[ 'is_enable_refund' ]) {
            $not_apply_refund_count = model('order_goods')->getCount([
                [ 'order_id', '=', $order_id ],
                [ 'refund_status', '=', OrderRefund::REFUND_NOT_APPLY ],
            ], 'order_goods_id');
            if ($not_apply_refund_count > 1) {
                array_push($member_action, [
                    'action' => 'memberBatchRefund',
                    'title' => '批量退款',
                    'color' => '',
                ]);
            }
        }
        $order_info[ 'action' ] = $member_action;
        $order_goods_list = model('order_goods')->getList([ [ 'order_id', '=', $order_id ], [ 'member_id', '=', $member_id ] ]);

        foreach ($order_goods_list as $k => $v) {
            $refund_action = empty($v[ 'refund_status_action' ]) ? [] : json_decode($v[ 'refund_status_action' ], true);
            $refund_action = $refund_action[ 'member_action' ] ?? [];
            $order_goods_list[ $k ][ 'refund_action' ] = $refund_action;
            $form_info = model('form_data')->getInfo([ [ 'relation_id', '=', $v[ 'order_goods_id' ] ], [ 'scene', '=', 'goods' ] ], 'form_data');
            if (!empty($form_info)) $order_goods_list[ $k ][ 'form' ] = json_decode($form_info[ 'form_data' ], true);
        }
        $order_info[ 'order_goods' ] = $order_goods_list;

//        switch ( $order_info[ 'order_type' ] ) {
//            case 1:
//                $order_model = new Order();
//                break;
//            case 2:
//                $order_model = new StoreOrder();
//                break;
//            case 3:
//                $order_model = new LocalOrder();
//                break;
//            case 4:
//                $order_model = new VirtualOrder();
//                break;
//            case 5:
//                $order_model = new StoreOrder();
//                break;
//        }
        $order_model = $this->getOrderModel($order_info);
        $temp_info = $order_model->orderDetail($order_info);
        $order_info = array_merge($order_info, $temp_info);

        $code_result = $this->orderQrcode($order_info);
        $order_info = array_merge($order_info, $code_result);
        $order_info[ 'code_info' ] = $code_result;

        $form_info = model('form_data')->getInfo([ [ 'relation_id', '=', $order_id ], [ 'scene', '=', 'order' ] ], 'form_data');
        if (!empty($form_info)) $order_info[ 'form' ] = json_decode($form_info[ 'form_data' ], true);

        return $this->success($order_info);
    }

    /**
     * 会员订单分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return \multitype
     */
    public function getMemberOrderPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*', $alias = 'a', $join = [])
    {
        $order_list = model('order')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        if (!empty($order_list[ 'list' ])) {
            foreach ($order_list[ 'list' ] as $k => $v) {
                $order_goods_list = model('order_goods')->getList([
                    'order_id' => $v[ 'order_id' ]
                ]);
                $order_list[ 'list' ][ $k ][ 'order_goods' ] = $order_goods_list;
                $action = empty($v[ 'order_status_action' ]) ? [] : json_decode($v[ 'order_status_action' ], true);
                $member_action = $action[ 'member_action' ] ?? [];
                $order_list[ 'list' ][ $k ][ 'action' ] = $member_action;
            }
        }
        return $this->success($order_list);
    }

    /**
     * 订单生成码
     * @param $order_info
     * @param $is_create
     */
    public function orderQrcode($order_info)
    {

        $app_type = 'h5';
        switch ( $order_info[ 'order_type' ] ) {
            case 2:
                $code = $order_info[ 'delivery_code' ];
                $verify_type = 'pickup';
                break;
            case 4:
                $code = $order_info[ 'virtual_code' ];
                $verify_type = 'virtualgoods';
                break;
            default:
                return [];
        }
        $verify_model = new Verify();
        $result = $verify_model->qrcode($code, $app_type, $verify_type, $order_info[ 'site_id' ], 'get');

        // 生成条形码
        $txm = getBarcode($code, 'upload/qrcode/' . $verify_type);
        $data = [];
        if (!empty($result) && $result[ 'code' ] >= 0) {
            $data[ $verify_type ] = $result[ 'data' ][ $app_type ][ 'path' ] ?? '';
        }
        $data[ $verify_type . '_barcode' ] = $txm;
        return $data;
    }

    /****************************************************************************会员订单订单数据查询结束*************************************/


    /***************************************************************** 交易记录 *****************************************************************/

    /**
     * 获取交易记录分页列表
     *
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getTradePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = model('order')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /***************************************************************** 交易记录 *****************************************************************/


    public function orderAfterSaleClose($order_id)
    {
        $res = model('order')->update([ 'is_enable_refund' => 0 ], [ [ 'order_id', '=', $order_id ] ]);
        return $this->success($res);
    }

    /************************************************************************* 订单日志 start ********************************************************************/
    /**
     * 添加订单日志
     * @param array $data
     * @return array
     */
    public function addOrderLog($data)
    {
        $data[ 'action_time' ] = time();//操作时间
        $res = model('order_log')->add($data);
        return $this->success($res);
    }

    /**
     * 获取订单日志
     * @param $condition
     * @param string $field
     */
    public function getOrderLog($condition, $field = '*')
    {
        $res = model('order_log')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取订单日志列表
     * @param $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getOrderLogList($condition, $field = '*', $order = 'action_time desc')
    {
        $res = model('order_log')->getList($condition, $field, $order);
        return $this->success($res);
    }

    /**
     * 获取订单日志数量
     * @param $condition
     * @param string $field
     */
    public function getOrderLogCount($condition)
    {
        $res = model('order_log')->getCount($condition);
        return $this->success($res);
    }

    /**
     * 删除订单日志
     * @param $condition
     * @return array
     */
    public function deleteOrderLog($condition)
    {
        $res = model('order_log')->delete($condition);
        return $this->success($res);
    }


    /**
     * 获取订单项分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getOrderGoodsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*', $alias = '', $join = [], $group = '')
    {
        $list = model('order_goods')->pageList($condition, $field, $order, $page, $page_size, $alias, $join, $group);
        return $this->success($list);
    }


    public function getOrderModel($order_info)
    {

        $order_model = event('GetOrderModel', $order_info, true);
        if (empty($order_model)) {
            //调用各种订单
            switch ( $order_info[ 'order_type' ] ) {
                case 1:
                    $order_model = new Order();
                    break;
                case 2:
                    $order_model = new StoreOrder();
                    break;
                case 3:
                    $order_model = new LocalOrder();
                    break;
                case 4:
                    $order_model = new VirtualOrder();
                    break;
            }
        }


        return $order_model;
    }


    /**
     * 鉴于耦合度太高,封装一些公共操作2
     * @param $order_info
     * @param $action
     * @return array
     */
    public function getOrderCommonAction($temp, $action)
    {
        if (is_object($temp)) {
            $order_model = $temp;
        } else {
            $order_model = $this->getOrderModel($temp);
        }
        $data = array (
            'order_status' => $action,
            'order_status_name' => $order_model->order_status[ $action ][ 'name' ] ?? '',
            'order_status_action' => json_encode($order_model->order_status[ $action ], JSON_UNESCAPED_UNICODE),
        );
        return $data;
    }
}