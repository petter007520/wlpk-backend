<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\memberconsume\model;

use app\model\member\MemberAccount as MemberAccountModel;
use app\model\order\OrderCommon as OrderCommonModel;
use app\model\order\OrderRefund;
use app\model\system\Config as ConfigModel;
use app\model\BaseModel;
use addon\coupon\model\CouponType;
use addon\coupon\model\Coupon;
use think\facade\Log;

/**
 * 会员消费
 */
class Consume extends BaseModel
{
    /**
     * 会员消费设置
     * array $data
     */
    public function setConfig($data, $is_use, $site_id)
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '会员消费设置', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'MEMBER_CONSUME_CONFIG' ] ]);
        return $res;
    }

    /**
     * 会员消费设置
     */
    public function getConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'MEMBER_CONSUME_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'is_return_point' => 0,
                'return_point_status' => 'complete',
                'return_point_rate' => 0,
                'return_growth_rate' => 0,
                'is_return_coupon' => 0,
                'return_coupon' => '',
            ];
        }
        if (!isset($res[ 'data' ][ 'value' ][ 'is_return_coupon' ])) $res[ 'data' ][ 'value' ][ 'is_return_coupon' ] = 0;
        $coupon_list = [];
        if ($res[ 'data' ][ 'value' ][ 'is_return_coupon' ] != 0 && $res[ 'data' ][ 'value' ][ 'return_coupon' ] != '') {
            $coupon = new CouponType();
            $coupon_list = $coupon->getCouponTypeList([ [ 'site_id', '=', $site_id ], [ 'status', '=', 1 ], [ 'coupon_type_id', 'in', $res[ 'data' ][ 'value' ][ 'return_coupon' ] ] ])[ 'data' ];
        }
        $res[ 'data' ][ 'value' ][ 'coupon_list' ] = $coupon_list;
        $res[ 'data' ][ 'value' ][ 'is_recovery_reward' ] = $res[ 'data' ][ 'value' ][ 'is_recovery_reward' ] ?? 0;

        return $res;
    }

    public function getOrderMoney($out_trade_no)
    {
        $pay_info = model('pay')->getInfo([ [ 'out_trade_no', '=', $out_trade_no ], [ 'pay_status', '=', 2 ] ]);
        if (empty($pay_info)) return $this->error('', '支付信息未找到');

        $order_money = 0;
        switch ( $pay_info[ 'event' ] ) {
            case 'OrderPayNotify':
                //普通订单
                $order = new OrderCommonModel();
                $order_info = $order->getOrderInfo([ [ 'out_trade_no', '=', $out_trade_no ] ], 'order_money');
                $order_money = $order_info[ 'data' ][ 'order_money' ] ?? '0.00';
                break;
            case 'GiftCardOrderPayNotify':
                //礼品卡
                $order_info = model("giftcard_order")->getInfo([ [ 'out_trade_no', '=', $out_trade_no ] ], 'order_money');
                $order_money = $order_info[ 'order_money' ] ?? '0.00';
                break;
            case 'BlindboxGoodsOrderPayNotify':
                //盲盒
                $order_info = model("blindbox_order")->getInfo([ [ 'out_trade_no', '=', $out_trade_no ], 'price' ]);
                $order_money = $order_info[ 'price' ] ?? '0.00';
                break;
            case 'CashierOrderPayNotify':
                //收银订单
                $order = new OrderCommonModel();
                $order_info = $order->getOrderInfo([ [ 'out_trade_no', '=', $out_trade_no ], [ 'member_id', '>', 0 ] ], 'order_money')[ 'data' ] ?? [];
                $order_money = $order_info[ 'order_money' ] ?? '0.00';
                break;
        }

        return $this->success($order_money);
    }

    /**
     * memberConsume 消费记录发放
     * @return array
     */
    public function memberConsume($param)
    {
        Log::write('memberConsume' . json_encode($param));
        $member_account_model = new MemberAccountModel();
        $out_trade_no = $param[ 'out_trade_no' ];
        $pay_info = model('pay')->getInfo([ [ 'out_trade_no', '=', $param[ 'out_trade_no' ] ], [ 'pay_status', '=', 2 ] ]);
        if (empty($pay_info)) return $this->error('', '支付信息未找到');

        $order_info = [];
        switch ( $pay_info[ 'event' ] ) {
            case 'OrderPayNotify':
                //普通订单
                $order = new OrderCommonModel();
                $order_info = $order->getOrderInfo([ [ 'out_trade_no', '=', $out_trade_no ] ], 'order_id, order_money, site_id, member_id, order_type')[ 'data' ];
                break;
            case 'GiftCardOrderPayNotify':
                //礼品卡
                $order_info = model("giftcard_order")->getInfo([ [ 'out_trade_no', '=', $out_trade_no ] ], 'order_id,order_money, site_id, member_id');
                break;
            case 'BlindboxGoodsOrderPayNotify':
                //盲盒
                $order_info = model("blindbox_order")->getInfo([ [ 'out_trade_no', '=', $out_trade_no ] ], 'order_id,price as order_money, site_id, member_id');
                break;
            case 'CashierOrderPayNotify':
                //普通订单
                $order = new OrderCommonModel();
                $order_info = $order->getOrderInfo([ [ 'out_trade_no', '=', $out_trade_no ], [ 'member_id', '>', 0 ], [ 'cashier_order_type', 'in', [ 'goods', 'card' ] ] ], 'order_id, order_money, site_id, member_id, order_type, cashier_order_type, order_scene')[ 'data' ];
                break;
        }
        if (empty($order_info)) return $this->success();

        //是否发放过
        $count = model('promotion_consume_record')->getCount([ [ 'out_trade_no', '=', $param[ 'out_trade_no' ] ] ]);
        if (!empty($count)) {
            return $this->success();
        }

        $consume_config = $this->getConfig($order_info[ 'site_id' ])[ 'data' ];

        if ($consume_config[ 'is_use' ] &&
            (
                ( ( $pay_info[ 'event' ] == 'OrderPayNotify' || $pay_info[ 'event' ] == 'CashierOrderPayNotify' ) &&
                    (
                        ( ( $order_info[ 'order_type' ] == 4 || ( $pay_info[ 'event' ] == 'CashierOrderPayNotify' && $order_info[ 'order_scene' ] == 'cashier' ) ) && $consume_config[ 'value' ][ 'return_point_status' ] == 'receive' && $param[ 'status' ] == 'complete' ) ||
                        $consume_config[ 'value' ][ 'return_point_status' ] == $param[ 'status' ]
                    )
                ) ||
                $pay_info[ 'event' ] == 'BlindboxGoodsOrderPayNotify' ||
                $pay_info[ 'event' ] == 'GiftCardOrderPayNotify'
            )
        ) {
            // 判断是否开启了奖励回收

            if ($consume_config[ 'value' ][ 'is_recovery_reward' ] && ( $pay_info[ 'event' ] == 'OrderPayNotify' || $pay_info[ 'event' ] == 'CashierOrderPayNotify' )) {
                $refunded_count = model('order_goods')->getCount([ [ 'order_id', '=', $order_info[ 'order_id' ] ], [ 'refund_status', '=', OrderRefund::REFUND_COMPLETE ] ]);
                if ($refunded_count > 0) return $this->success();
            }

            $consume_data = [];

            $consume_config = $consume_config[ 'value' ];
            if (!empty($consume_config[ 'return_point_rate' ])) {
                $adjust_num = intval($consume_config[ 'return_point_rate' ] / 100 * $order_info[ 'order_money' ]);
                if ($adjust_num > 0) {
                    $remark = '活动奖励发放';
                    $member_account_model->addMemberAccount($order_info[ 'site_id' ], $order_info[ 'member_id' ], 'point', $adjust_num, 'memberconsume', $order_info[ 'order_id' ], $remark);
                    $consume_data[] = [
                        'site_id' => $order_info[ 'site_id' ],
                        'type' => 'point',
                        'value' => $adjust_num,
                        'order_id' => $order_info[ 'order_id' ],
                        'member_id' => $order_info[ 'member_id' ],
                        'out_trade_no' => $pay_info[ 'out_trade_no' ],
                        'remark' => $remark,
                        'config' => json_encode($consume_config),
                        'create_time' => time()
                    ];
                }
            }
            if (!empty($consume_config[ 'return_growth_rate' ])) {
                $adjust_num = intval($consume_config[ 'return_growth_rate' ] / 100 * $order_info[ 'order_money' ]);
                if ($adjust_num > 0) {
                    $remark = '活动奖励发放';
                    $member_account_model->addMemberAccount($order_info[ 'site_id' ], $order_info[ 'member_id' ], 'growth', $adjust_num, 'memberconsume', $order_info[ 'order_id' ], $remark);
                    $consume_data[] = [
                        'site_id' => $order_info[ 'site_id' ],
                        'type' => 'growth',
                        'value' => $adjust_num,
                        'order_id' => $order_info[ 'order_id' ],
                        'member_id' => $order_info[ 'member_id' ],
                        'out_trade_no' => $pay_info[ 'out_trade_no' ],
                        'remark' => $remark,
                        'config' => json_encode($consume_config),
                        'create_time' => time()
                    ];
                }
            }

            if (!empty($consume_config[ 'is_return_coupon' ]) && !empty($consume_config[ 'return_coupon' ])) {
                $coupon_type = new CouponType();
                $coupon_list = $coupon_type->getCouponTypeList([ [ 'site_id', '=', $order_info[ 'site_id' ] ], [ 'status', '=', 1 ], [ 'coupon_type_id', 'in', $consume_config[ 'return_coupon' ] ] ])[ 'data' ];
                $coupon = new Coupon();
                foreach ($coupon_list as $k => $v) {
                    $coupon->giveCoupon([ [ 'coupon_type_id' => $v[ 'coupon_type_id' ], 'num' => 1 ] ], $order_info[ 'site_id' ], $order_info[ 'member_id' ], 6);
                    if ($v[ 'at_least' ] > 0) {
                        $remark = '满' . $v[ 'at_least' ] . ( $v[ 'type' ] == 'discount' ? '打' . $v[ 'discount' ] : '减' . $v[ 'money' ] );
                    } else {
                        $remark = '无门槛' . ( $v[ 'type' ] == 'discount' ? '打' . $v[ 'discount' ] : '减' . $v[ 'money' ] );
                    }

                    $consume_data[] = [
                        'site_id' => $order_info[ 'site_id' ],
                        'type' => 'coupon',
                        'value' => $v[ 'coupon_type_id' ],
                        'order_id' => $order_info[ 'order_id' ],
                        'member_id' => $order_info[ 'member_id' ],
                        'out_trade_no' => $pay_info[ 'out_trade_no' ],
                        'remark' => $remark,
                        'config' => json_encode($consume_config),
                        'create_time' => time()
                    ];
                }
            }

            if ($consume_data) {
                model('promotion_consume_record')->addList($consume_data);
            }

        }
        return $this->success();
    }

    private function returnStatusToZh($status)
    {
        $status_zh = [
            'pay' => '付款',
            'receive' => '收货',
            'complete' => '完成'
        ];
        return $status_zh[ $status ];
    }

    /**
     * 奖励记录分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getConsumeRecordPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*', $alias = 'a', $join = [])
    {
        $list = model('promotion_consume_record')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 奖励回收
     * @param $out_trade_no
     */
    public function rewardRecovery($out_trade_no)
    {
        if (empty($out_trade_no)) return $this->error();

        $list = model('promotion_consume_record')->getList([ [ 'type', 'in', [ 'point', 'coupon' ] ], [ 'out_trade_no', '=', $out_trade_no ], [ 'is_recycled', '=', 0 ] ]);
        if (!empty($list)) {
            $site_id = $list[ 0 ][ 'site_id' ];
            $member_id = $list[ 0 ][ 'member_id' ];

            $consume_config = $this->getConfig($site_id)[ 'data' ];
            // 回收权益
            if ($consume_config[ 'value' ][ 'is_recovery_reward' ]) {
                $member_account = new MemberAccountModel();
                foreach ($list as $item) {
                    // 扣除积分
                    if ($item[ 'type' ] == 'point') {
                        $member_info = model('member')->getInfo([ [ 'member_id', '=', $member_id ] ], 'point');
                        $point = $item[ 'value' ] > $member_info[ 'point' ] ? $member_info[ 'point' ] : $item[ 'value' ];
                        $res = $member_account->addMemberAccount($site_id, $member_id, 'point', -( $point ), 'memberconsume', $item[ 'order_id' ], "订单退款奖励回收");
                        if ($res[ 'code' ] == 0) {
                            model('promotion_consume_record')->update([ 'is_recycled' => 1 ], [ [ 'id', '=', $item[ 'id' ] ] ]);
                        }
                    }
                    // 删除未使用的优惠券
                    if ($item[ 'type' ] == 'coupon') {
                        $coupon = model('promotion_coupon')->getFirstData([ [ 'member_id', '=', $member_id ], [ 'coupon_type_id', '=', $item[ 'value' ] ], [ 'state', '=', 1 ] ], 'coupon_id');
                        if (!empty($coupon)) {
                            $delete_num = model('promotion_coupon')->delete([ [ 'coupon_id', '=', $coupon[ 'coupon_id' ] ] ]);
                            if ($delete_num) {
                                model('promotion_consume_record')->update([ 'is_recycled' => 1 ], [ [ 'id', '=', $item[ 'id' ] ] ]);
                            }
                        }
                    }
                }
            }
        }
    }

}