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
use addon\presale\model\PresaleOrder;
use app\model\goods\GoodsStock;
use app\model\member\Member;
use app\model\member\MemberAccount;
use app\model\shop\Shop;
use app\model\shop\SiteAddress;
use app\model\system\Pay;
use app\model\BaseModel;
use app\model\message\Message;
use addon\coupon\model\Coupon;
use app\model\order\Order as OrderModel;
use app\model\system\Stat;
use app\model\verify\Verify as VerifyModel;
use addon\shopcomponent\model\Weapp;

/**
 * 订单退款
 *
 * @author Administrator
 *
 */
class OrderRefund extends BaseModel
{
    /*********************************************************************************订单退款状态*****************************************************/
    //未申请退款
    const REFUND_NOT_APPLY = 0;

    //已申请退款
    const REFUND_APPLY = 1;

    // 已确认
    const REFUND_CONFIRM = 2;

    //已完成
    const REFUND_COMPLETE = 3;

    //等待买家发货
    const REFUND_WAIT_DELIVERY = 4;

    //等待卖家收货
    const REFUND_WAIT_TAKEDELIVERY = 5;
    //卖家确认收货
    const REFUND_TAKEDELIVERY = 6;

    // 卖家拒绝退款
    const REFUND_DIEAGREE = -1;

    // 卖家关闭退款
    const REFUND_CLOSE = -2;

    //退款方式
    const ONLY_REFUNDS = 1;//仅退款
    const A_REFUND_RETURN = 2;//退款退货
    const SHOP_ACTIVE_REFUND = 3;//店铺主动退款

    /**
     * 订单退款状态
     * @var unknown
     */
    public $order_refund_status = [
        0 => [
            'status' => 0,
            'name' => '',
            'action' => [

            ],
            'member_action' => [
                [
                    'event' => 'orderRefundApply',
                    'title' => '申请维权',
                    'color' => ''
                ],
            ]
        ],
        self::REFUND_APPLY => [
            'status' => self::REFUND_APPLY,
            'name' => '申请维权',
            'action' => [
                [
                    'event' => 'orderRefundAgree',
                    'title' => '同意',
                    'color' => ''
                ],
                [
                    'event' => 'orderRefundRefuse',
                    'title' => '拒绝',
                    'color' => ''
                ],
                [
                    'event' => 'orderRefundClose',
                    'title' => '关闭维权',
                    'color' => ''
                ]
            ],
            'member_action' => [
                [
                    'event' => 'orderRefundCancel',
                    'title' => '撤销维权',
                    'color' => ''
                ],
            ]
        ],
        self::REFUND_CONFIRM => [
            'status' => self::REFUND_CONFIRM,
            'name' => '待转账',
            'action' => [
                [
                    'event' => 'orderRefundTransfer',
                    'title' => '转账',
                    'color' => ''
                ],
                [
                    'event' => 'orderRefundClose',
                    'title' => '关闭维权',
                    'color' => ''
                ]
            ],
            'member_action' => [

            ]
        ],
        self::REFUND_COMPLETE => [
            'status' => self::REFUND_COMPLETE,
            'name' => '维权结束',
            'action' => [

            ],
            'member_action' => [

            ]
        ],
        self::REFUND_WAIT_DELIVERY => [
            'status' => self::REFUND_WAIT_DELIVERY,
            'name' => '买家待退货',
            'action' => [
                [
                    'event' => 'orderRefundClose',
                    'title' => '关闭维权',
                    'color' => ''
                ]
            ],
            'member_action' => [
                [
                    'event' => 'orderRefundDelivery',
                    'title' => '填写发货物流',
                    'color' => ''
                ],
            ]
        ],
        self::REFUND_WAIT_TAKEDELIVERY => [
            'status' => self::REFUND_WAIT_TAKEDELIVERY,
            'name' => '卖家待收货',
            'action' => [
                [
                    'event' => 'orderRefundTakeDelivery',
                    'title' => '收货',
                    'color' => ''
                ],
                [
                    'event' => 'orderRefundRefuse',
                    'title' => '拒绝',
                    'color' => ''
                ],
                [
                    'event' => 'orderRefundClose',
                    'title' => '关闭维权',
                    'color' => ''
                ]
            ],
            'member_action' => [

            ]
        ],
        self::REFUND_TAKEDELIVERY => [
            'status' => self::REFUND_TAKEDELIVERY,
            'name' => '卖家已收货',
            'action' => [
                [
                    'event' => 'orderRefundTransfer',
                    'title' => '转账',
                    'color' => ''
                ],
                [
                    'event' => 'orderRefundClose',
                    'title' => '关闭维权',
                    'color' => ''
                ]
            ],
            'member_action' => [

            ]
        ],
        self::REFUND_DIEAGREE => [
            'status' => self::REFUND_DIEAGREE,
            'name' => '卖家拒绝',
            'action' => [
                [
                    'event' => 'orderRefundClose',
                    'title' => '关闭维权',
                    'color' => ''
                ]
            ],
            'member_action' => [
                [
                    'event' => 'orderRefundCancel',
                    'title' => '撤销维权',
                    'color' => ''
                ],
                [
                    'event' => 'orderRefundAsk',
                    'title' => '修改申请',
                    'color' => ''
                ],
            ]
        ]

    ];

    /**
     * 退款方式
     * @var unknown
     */
    public $refund_type = [
        self::ONLY_REFUNDS => '仅退款',
        self::A_REFUND_RETURN => '退货退款',
    ];

    /**
     * 退款方式
     * @var unknown
     */
    public $refund_reason_type = [
        '未按约定时间发货',
        '拍错/多拍/不喜欢',
        '协商一致退款',
        '其他',
    ];

    /****************************************************************************订单退款相关操作（开始）**********************************/

    /**
     * 获取退款金额
     * @param string $order_goods_ids
     */
    public function getOrderRefundMoney($order_goods_ids)
    {
        //订单商品项
        $order_goods_ids = (string)$order_goods_ids;
        $order_goods_lists = model('order_goods')->getList([[
            'order_goods_id', 'in', is_array($order_goods_ids) ? $order_goods_ids : (string)$order_goods_ids
        ]]);
        if (empty($order_goods_lists)) return $this->error(null, '未查询到订单商品');
        $order_id = $order_goods_lists[0]['order_id'];

        //退款状态检测 只有未申请的可以发起退款
        foreach ($order_goods_lists as $val) {
            if (!in_array($val['refund_status'], [0, self::REFUND_NOT_APPLY, self::REFUND_DIEAGREE])) {
                return $this->error(null, '订单商品退款状态有误');
            }
        }

        //剩余未申请退款的订单商品统计
        $not_apply_count = model('order_goods')->getCount([
            ['order_id', '=', $order_id],
            ['order_goods_id', 'not in', $order_goods_ids],
            ['refund_status', 'in', [0, self::REFUND_NOT_APPLY, self::REFUND_DIEAGREE]],
        ], 'order_goods_id');
        //有退过运费的订单商品统计
        $refund_delivery_count = model('order_goods')->getCount([
            ['order_id', '=', $order_id],
            ['order_goods_id', 'not in', $order_goods_ids],
            ['refund_delivery_money', '>', 0],
        ], 'order_goods_id');

        //如果还有未申请退款的商品就不退运费 发票 和发票运费
        if ($not_apply_count > 0) {
            $delivery_money = 0;
            $invoice_delivery_money = 0;
            $invoice_money = 0;
        } else {
            $order_info = model('order')->getInfo([
                ['order_id', '=', $order_id],
            ], 'delivery_money, invoice_delivery_money, invoice_money');
            if ($refund_delivery_count == 0) {
                $delivery_money = $order_info['delivery_money'];
            } else {
                $delivery_money = 0;
            }
            $invoice_delivery_money = $order_info['invoice_delivery_money'];
            $invoice_money = $order_info['invoice_money'];
        }

        //计算实际退款金额
        $refund_money = 0;
        foreach ($order_goods_lists as $item) {
            $refund_money += $item['real_goods_money'];
        }
        $refund_money += $delivery_money + $invoice_delivery_money + $invoice_money;
        $data = array(
            'refund_money' => round($refund_money, 2),
            'refund_delivery_money' => round($delivery_money, 2)
        );
        return $data;
    }

    /**
     * 订单退回余额
     * @param int $order_goods_id
     */
    public function getOrderRefundBalance($order_goods_id)
    {
        //订单商品项
        $order_goods_info = model('order_goods')->getInfo([
            'order_goods_id' => $order_goods_id
        ], 'order_id, goods_money');
        //订单整体项
        $order_info = model('order')->getInfo([
            'order_id' => $order_goods_info['order_id']
        ], 'goods_money, balance_money');
        if ($order_info['balance_money'] != 0) {
            if ($order_info['goods_money'] != 0) {
                return $this->success($order_info['balance_money'] * $order_goods_info['goods_money'] / $order_info['goods_money']);
            } else {
                return $this->success(0);
            }
        }
        return $this->success(0);
    }

    /**
     * 添加退款操作日志
     * @param int $order_goods_id
     * @param int $refund_status
     * @param string $action
     * @param int $action_way
     * @param int $action_userid
     * @param string $action_username
     */
    public function addOrderRefundLog($order_goods_id, $refund_status, $action, $action_way, $action_userid, $action_username, $desc = '')
    {
        $data = [
            'order_goods_id' => $order_goods_id,
            'refund_status' => $refund_status,
            'action' => $action,
            'action_way' => $action_way,
            'action_userid' => $action_userid,
            'username' => $action_username,
            'action_time' => time(),
            'desc' => $desc
        ];
        $res = model('order_refund_log')->add($data);
        // 维权状态变更
        event('RefundStatusChange', $data);
        return $res;
    }

    /**
     * 会员申请退款
     * @param array $data
     * @param array $member_info
     * @return multitype:string mixed
     */
    public function orderRefundApply($data, $member_info, $log_data = [])
    {
        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $data['order_goods_id']], 'order_id,refund_status,delivery_status,is_virtual,site_id,order_no,order_goods_id');
        if (empty($order_goods_info))
            return $this->error();

        if ($order_goods_info['refund_status'] != 0 && $order_goods_info['refund_status'] != -1)
            return $this->error();

        $refund_type_list = $this->getRefundType($order_goods_info);
        //防止退款方式越权
        if (!in_array($data['refund_type'], $refund_type_list))
            return $this->error();


        $order_info = model('order')->getInfo(['order_id' => $order_goods_info['order_id']]);
        //判断是否允许申请退款
        if ($order_info['is_enable_refund'] == 0) {
            if ($order_info['promotion_type'] == 'pinfan') {
                return $this->error('', '拼团活动正在进行中,待拼成功后可发起退款');
            }
            return $this->error();
        }

        model('order_goods')->startTrans();
        try {

            $data['refund_status'] = self::REFUND_APPLY;
            $data['refund_status_name'] = $this->order_refund_status[self::REFUND_APPLY]['name'];
            $data['refund_status_action'] = json_encode($this->order_refund_status[self::REFUND_APPLY], JSON_UNESCAPED_UNICODE);
            $data['refund_mode'] = $order_info['order_status'] == Order::ORDER_COMPLETE ? 2 : 1;

            $pay_model = new Pay();
            $data['refund_no'] = $pay_model->createRefundNo();
            $data['refund_action_time'] = time();
            $refund_apply_money_array = $this->getOrderRefundMoney($data['order_goods_id']);//可退款金额 通过计算获得
            $refund_apply_money = $refund_apply_money_array['refund_money'];
            $refund_delivery_money = $refund_apply_money_array['refund_delivery_money'];
            $data['refund_apply_money'] = $refund_apply_money;//申请的总退款
            $data['refund_delivery_money'] = $refund_delivery_money;//退的运费

            //生成视频号订单ID
            $data['out_aftersale_id'] = $order_goods_info['order_no'] . time() . $order_goods_info['order_goods_id'];
            $res = model('order_goods')->update($data, ['order_goods_id' => $data['order_goods_id']]);

            //验证订单锁定状态
            $local_result = $this->verifyOrderLock($order_goods_info['order_id']);

            $this->addOrderRefundLog($data['order_goods_id'], self::REFUND_APPLY, '买家申请退款', 1, $member_info['member_id'], $member_info['nickname']);

            event('orderRefundApply', $data);//传入订单类型以及订单项id


            model('order_goods')->commit();

            //订单会员申请退款消息
            $message_model = new Message();
            $message_model->sendMessage(['keywords' => 'BUYER_REFUND', 'order_goods_id' => $data['order_goods_id'], 'site_id' => $order_goods_info['site_id']]);

            // 发起维权 关闭订单评价
            model('order')->update(['is_evaluate' => 0], ['order_id' => $order_goods_info['order_id']]);

            //记录订单日志 start
            if ($log_data) {
                $order_common_model = new OrderCommon();
                $log_data = array_merge($log_data, [
                    'order_id' => $order_goods_info['order_id'],
                    'order_status' => $order_info['order_status'],
                    'order_status_name' => $order_info['order_status_name']
                ]);

                $order_common_model->addOrderLog($log_data);
            }
            //记录订单日志 end

            return $this->success($res);
        } catch ( \Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 用户撤销退款申请
     * @param array $data
     * @param array $member_info
     * @return string[]|mixed[]
     */
    public function memberCancelRefund($data, $member_info, $log_data = [])
    {
        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $data['order_goods_id']]);
        if (empty($order_goods_info)) {
            return $this->error();
        }
        model('order_goods')->startTrans();
        try {
//            $order_info = model('order')->getInfo(['order_id' => $order_goods_info['order_id']]);
            $data['refund_status'] = 0;
            $data['refund_status_name'] = $this->order_refund_status[0]['name'];
            $data['refund_status_action'] = json_encode($this->order_refund_status[0], JSON_UNESCAPED_UNICODE);
            $data['refund_type'] = 0;
            //重置部分字段
            $data['refund_address'] = '';
            $data['refund_delivery_remark'] = '';
            $data['refund_remark'] = '';
            $data['refund_delivery_name'] = '';
            $data['refund_delivery_no'] = '';
            $data['refund_reason'] = '';
            $res = model('order_goods')->update($data, ['order_goods_id' => $data['order_goods_id']]);

            //验证订单锁定状态
            $lock_result = $this->verifyOrderLock($order_goods_info['order_id']);

            // 维权拒绝 评价锁定放开
            model('order')->update(['is_evaluate' => 1], [['order_id', '=', $order_goods_info['order_id']], ['order_status', 'in', [OrderModel::ORDER_TAKE_DELIVERY, OrderModel::ORDER_COMPLETE]]]);

            //记录订单日志 start
            if ($log_data) {
                $order_common_model = new OrderCommon();
                $order_info = model('order')->getInfo(['order_id' => $order_goods_info['order_id']], 'order_status,order_status_name,member_id,site_id,is_video_number');
                $log_data = array_merge($log_data, [
                    'order_id' => $order_goods_info['order_id'],
                    'order_status' => $order_info['order_status'],
                    'order_status_name' => $order_info['order_status_name']
                ]);
                $order_common_model->addOrderLog($log_data);

                if ($order_info['is_video_number'] == 1) {
                    $weapp_model = new Weapp($order_info['site_id']);
                    $member = model('member')->getInfo([['member_id', '=', $order_info['member_id']]]);
                    $res = $weapp_model->cancel([
                        'out_aftersale_id' => $order_goods_info['out_aftersale_id'],
                        'openid' => $member['weapp_openid']
                    ]);
                    if ($res['code'] < 0) {
                        model('order_goods')->rollback();
                        return $this->error('', $res['message']);
                    }
                }
            }
            //记录订单日志 end

            $this->addOrderRefundLog($data['order_goods_id'], 0, '买家撤销退款申请', 1, $member_info['member_id'], $member_info['nickname']);
            event('memberCancelRefund', $data);//传入订单类型以及订单项id
            model('order_goods')->commit();
            return $this->success();
        } catch ( \Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 卖家确认退款
     * @param array $data
     * @param array $user_info
     */
    public function orderRefundConfirm($data, $user_info)
    {
        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $data['order_goods_id']]);
        if (empty($order_goods_info)) {
            return $this->error();
        }
        if ($order_goods_info['refund_status'] != self::REFUND_APPLY) {
            return $this->error();
        }
        model('order_goods')->startTrans();
        try {
            if ($order_goods_info['refund_type'] == 1) {
                $data['refund_status'] = self::REFUND_CONFIRM;  //确认等待转账
            } else {
                $data['refund_status'] = self::REFUND_WAIT_DELIVERY;  //确认等待买家发货
            }
            $data['refund_status_name'] = $this->order_refund_status[$data['refund_status']]['name'];
            $data['refund_status_action'] = json_encode($this->order_refund_status[$data['refund_status']], JSON_UNESCAPED_UNICODE);
            $res = model('order_goods')->update($data, ['order_goods_id' => $data['order_goods_id']]);

            //记录订单日志 start
            $order_common_model = new OrderCommon();
            $order_info = model('order')->getInfo(['order_id' => $order_goods_info['order_id']], 'order_status,order_status_name,is_video_number,site_id');
            $log_data = [
                'uid' => $user_info['uid'],
                'nick_name' => $user_info['username'],
                'action' => '商家同意了退款申请，等待转账',
                'action_way' => 2,
                'order_id' => $order_goods_info['order_id'],
                'order_status' => $order_info['order_status'],
                'order_status_name' => $order_info['order_status_name']
            ];
            $order_common_model->addOrderLog($log_data);
            //记录订单日志 end
            if ($order_goods_info['refund_type'] != 1) {

                if ($order_info['is_video_number'] == 1) {
//                    $shop_info = model('shop')->getInfo([['site_id', '=',$order_info['site_id'] ]]);

                    $refund_address = $this->getRefundAddress($order_goods_info['site_id']);

                    $weapp_model = new Weapp($order_info['site_id']);
                    $res = $weapp_model->aceptreturn([
                        'out_aftersale_id' => $order_goods_info['out_aftersale_id'],
                        'address_info' => [
                            'receiver_name' => $refund_address['shop_contacts'],
                            'detailed_address' => $refund_address['shop_address'],
                            'tel_number' => $refund_address['shop_mobile'],
                            'country' => '',
                            'province' => $refund_address['province_name'] ?? '无',
                            'city' => $refund_address['city_name'] ?? '无',
                            'town' => $refund_address['district_name'] ?? '无',
                        ]
                    ]);
                    if ($res['code'] < 0) {
                        model('order_goods')->rollback();
                        return $this->error('', $res['message']);
                    }
                }
            }

            $this->addOrderRefundLog($data['order_goods_id'], $data['refund_status'], '卖家确认退款', 2, $user_info['uid'], $user_info['username']);
            model('order_goods')->commit();

            //订单退款同意消息
            $message_model = new Message();
            $message_model->sendMessage(['keywords' => 'ORDER_REFUND_AGREE', 'order_id' => $order_goods_info['order_id'], 'order_goods_id' => $data['order_goods_id'], 'site_id' => $order_goods_info['site_id']]);
            return $this->success($res);
        } catch ( \Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 卖家拒绝退款
     * @param array $data
     * @param array $user_info
     */
    public function orderRefundRefuse($data, $user_info, $refund_refuse_reason, $log_data = [])
    {
        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $data['order_goods_id']]);
        if (empty($order_goods_info)) {
            return $this->error();
        }
        if ($order_goods_info['refund_status'] != self::REFUND_APPLY && $order_goods_info['refund_status'] != self::REFUND_WAIT_TAKEDELIVERY) {
            return $this->error();
        }
        model('order_goods')->startTrans();
        try {
            $data['refund_status'] = self::REFUND_DIEAGREE;
            $data['refund_status_name'] = $this->order_refund_status[self::REFUND_DIEAGREE]['name'];
            $data['refund_status_action'] = json_encode($this->order_refund_status[self::REFUND_DIEAGREE], JSON_UNESCAPED_UNICODE);
            $data['refund_refuse_reason'] = $refund_refuse_reason;

            $data['refund_action_time'] = time();
            $res = model('order_goods')->update($data, ['order_goods_id' => $data['order_goods_id']]);
            //验证订单锁定状态
            $lock_result = $this->verifyOrderLock($order_goods_info['order_id']);

            $log_desc = empty($refund_refuse_reason) ? '' : '拒绝原因：' . $refund_refuse_reason;
            $this->addOrderRefundLog($data['order_goods_id'], $data['refund_status'], '卖家拒绝退款', 2, $user_info['uid'], $user_info['username'], $log_desc);
            event('OrderRefundRefuse', $data);

            //记录订单日志 start
            $order_info = model('order')->getInfo(['order_id' => $order_goods_info['order_id']], 'order_status,order_status_name,is_video_number,site_id');
            if ($log_data) {
                $order_common_model = new OrderCommon();
                $log_data = array_merge($log_data, [
                    'order_id' => $order_goods_info['order_id'],
                    'order_status' => $order_info['order_status'],
                    'order_status_name' => $order_info['order_status_name']
                ]);
                $order_common_model->addOrderLog($log_data);
            }

            if ($order_info['is_video_number'] == 1) {
                $weapp_model = new Weapp($order_info['site_id']);
                $res = $weapp_model->orderNoRefund(['out_aftersale_id' => $order_goods_info['out_aftersale_id']]);
                if ($res['code'] < 0) {
                    model('order_goods')->rollback();
                    return $this->error('', $res['message']);
                }
            }
            //记录订单日志 end

            // 维权拒绝 评价锁定放开
            model('order')->update(['is_evaluate' => 1], [['order_id', '=', $order_goods_info['order_id']], ['order_status', 'in', [OrderModel::ORDER_TAKE_DELIVERY, OrderModel::ORDER_COMPLETE]]]);

            //订单退款拒绝消息
            $message_model = new Message();
            $message_model->sendMessage(['keywords' => 'ORDER_REFUND_REFUSE', 'order_id' => $order_goods_info['order_id'], 'order_goods_id' => $data['order_goods_id'], 'site_id' => $order_goods_info['site_id']]);
            model('order_goods')->commit();
            return $this->success();
        } catch ( \Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 买家退货
     * @param array $data 退货信息
     * @param array $member_info 会员信息
     */
    public function orderRefundDelivery($data, $member_info)
    {
        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $data['order_goods_id']]);
        if (empty($order_goods_info)) {
            return $this->error();
        }
        if ($order_goods_info['refund_status'] != self::REFUND_WAIT_DELIVERY) {
            return $this->error();
        }
        model('order_goods')->startTrans();
        try {
            $data['refund_status'] = self::REFUND_WAIT_TAKEDELIVERY;
            $data['refund_status_name'] = $this->order_refund_status[self::REFUND_WAIT_TAKEDELIVERY]['name'];
            $data['refund_status_action'] = json_encode($this->order_refund_status[self::REFUND_WAIT_TAKEDELIVERY], JSON_UNESCAPED_UNICODE);
//            $shop_model = new Shop();
//            $shop_info_result = $shop_model->getShopInfo([ [ 'site_id', '=', $order_goods_info[ 'site_id' ] ] ], 'full_address');
//            $shop_info = $shop_info_result[ 'data' ];

            $refund_address = $this->getRefundAddress($order_goods_info['site_id']);

            $data['refund_address'] = $refund_address['shop_address'];
            $res = model('order_goods')->update($data, ['order_goods_id' => $data['order_goods_id']]);

            $this->addOrderRefundLog($data['order_goods_id'], $data['refund_status'], $data['refund_delivery_name'] . ':' . $data['refund_delivery_no'], 1, $member_info['member_id'], $member_info['nickname']);

            $order_info = model('order')->getInfo(['order_id' => $order_goods_info['order_id']], 'order_status,order_status_name,is_video_number,site_id,member_id');
            if ($order_info['is_video_number'] == 1) {
                $member_info = model('member')->getInfo([['member_id', '=', $order_info['member_id']]]);
                $weapp_model = new Weapp($order_info['site_id']);
                $res = $weapp_model->uploadreturninfo([
                    'out_aftersale_id' => $order_goods_info['out_aftersale_id'],
                    'openid' => $member_info['weapp_openid'],
                    'delivery_id' => '',
                    'waybill_id' => $order_goods_info['refund_delivery_no'],
                    'delivery_name' => $order_goods_info['refund_delivery_name']
                ]);
                if ($res['code'] < 0) {
                    model('order_goods')->rollback();
                    return $this->error('', $res['message']);
                }
            }

            model('order_goods')->commit();

            //买家已退货提醒
            $message_model = new Message();
            $message_model->sendMessage(['keywords' => 'BUYER_DELIVERY_REFUND', 'order_goods_info' => $order_goods_info, 'site_id' => $order_goods_info['site_id']]);

            return $this->success();
        } catch ( \Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 卖家确认收到退货
     * @param array $data 退货信息
     * @param array $member_info 会员信息
     */
    public function orderRefundTakeDelivery($data, $user_info)
    {
        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $data['order_goods_id']]);
        if (empty($order_goods_info)) {
            return $this->error();
        }
        if ($order_goods_info['refund_status'] != self::REFUND_WAIT_TAKEDELIVERY) {
            return $this->error();
        }
        model('order_goods')->startTrans();
        try {
            $data['refund_status'] = self::REFUND_TAKEDELIVERY;
            $data['refund_status_name'] = $this->order_refund_status[self::REFUND_TAKEDELIVERY]['name'];
            $data['refund_status_action'] = json_encode($this->order_refund_status[self::REFUND_TAKEDELIVERY], JSON_UNESCAPED_UNICODE);
            $res = model('order_goods')->update($data, ['order_goods_id' => $data['order_goods_id']]);
            $this->addOrderRefundLog($data['order_goods_id'], $data['refund_status'], '卖家确认收到退货', 2, $user_info['uid'], $user_info['username']);
            model('order_goods')->commit();
            return $this->success();
        } catch ( \Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 退货完成
     * @param array $data
     * @param array $user_info
     * @return multitype:string mixed
     */
    public function orderRefundFinish($data, $user_info, $log_data = [])
    {
        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $data['order_goods_id']]);
        if (empty($order_goods_info))
            return $this->error([], '不存在的退款项');

        $order_id = $order_goods_info['order_id'];
        $order_info = model('order')->getInfo([['order_id', '=', $order_id]]);
        if (empty($order_info))
            return $this->error([], '不存在的退款项');

        $refund_apply_money = $order_goods_info['refund_apply_money'];

        $shop_active_refund = $data['shop_active_refund'] ?? 0;
        $update_data = array(
            'refund_time' => time(),
            'shop_active_refund' => $shop_active_refund,
        );
        if ($shop_active_refund == 1) {//商家主动退款
            //查询发货状态(已发货的不能主动退款)
            if($order_info['order_scene'] == 'online'){//todo  只有非收银订单受限
                if ($order_goods_info['delivery_status'] != OrderModel::DELIVERY_WAIT) {
                    return $this->error([], '已发货的订单不能发起主动退款');
                }
            }

            //获取可退金额
            $do_refund_money = $data['do_refund_money'] ?? 0;
            $refund_apply_money_arr = $this->getOrderRefundMoney($data['order_goods_id']);
//            $refund_apply_money = $refund_apply_money_arr['refund_money'];
            $refund_apply_money = $do_refund_money;
            $refund_delivery_money = $refund_apply_money_arr['refund_delivery_money'];
            $data['refund_real_money'] = $refund_apply_money;
            $data['refund_delivery_money'] = $refund_delivery_money;
            $update_data['refund_no'] = (new Pay())->createRefundNo();
            $update_data['refund_action_time'] = time();
        } else {
            if ($order_goods_info['refund_status'] != self::REFUND_TAKEDELIVERY && $order_goods_info['refund_status'] != self::REFUND_CONFIRM) {
                return $this->error();
            }
        }

        if ($data['refund_real_money'] > $refund_apply_money) return $this->error('', '退款金额超出最大可退金额');
        model('order_goods')->startTrans();
        try {
            $update_data['refund_apply_money'] = $refund_apply_money;
            $update_data['refund_money_type'] = $data['refund_money_type'];
            $update_data['refund_real_money'] = $data['refund_real_money'];
            $update_data['shop_refund_remark'] = $data['shop_refund_remark'];
            $update_data['refund_delivery_money'] = $data['refund_delivery_money'] ?? 0.00;

            $update_data['refund_status'] = self::REFUND_COMPLETE;
            $update_data['refund_status_name'] = $this->order_refund_status[self::REFUND_COMPLETE]['name'];
            $update_data['refund_status_action'] = json_encode($this->order_refund_status[self::REFUND_COMPLETE], JSON_UNESCAPED_UNICODE);
            if (!empty($data['out_aftersale_id'])) {
                $update_data['out_aftersale_id'] = $data['out_aftersale_id'];
            }
            $res = model('order_goods')->update($update_data, [['order_goods_id', '=', $data['order_goods_id']]]);
            $result = $this->finishAction($data['order_goods_id'], $log_data, $data['is_deposit_back'] ?? 0);
            if ($result['code'] < 0) {
                model('order_goods')->rollback();
                return $result;
            }

            //退货日志
            $this->addOrderRefundLog($data['order_goods_id'], self::REFUND_COMPLETE, '维权完成', 2, $user_info['uid'], $user_info['username'], '维权完成，退款金额：¥' . $data['refund_real_money'] ?? 0);

            $order_goods_info = model('order_goods')->getInfo([['order_goods_id', '=', $data['order_goods_id']]]);
            $this->orderGoodsRefund($order_goods_info);
            //同时修改用户的order_money
            model('member')->setDec([['member_id', '=', $order_goods_info['member_id']]], 'order_money', $order_goods_info['refund_real_money']);

//            event('OrderRefundFinish', $order_goods_info);//传入订单类型以及订单项id
            model('order_goods')->commit();
            return $this->success();
        } catch ( \Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }


    /**
     * 退款完成操作
     * @param $order_goods_id
     * @param $refund_money_type
     * @return array
     */
    public function finishAction($order_goods_id, $log_data = [], $is_deposit_back = 1, $is_active_refund = false)
    {

        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $order_goods_id]);
        $order_id = $order_goods_info['order_id'];
        $site_id = $order_goods_info['site_id'];
        model('order_goods')->startTrans();
        try {
            $order_info = model('order')->getInfo(['order_id' => $order_id]);

            //验证订单是否全部退款完毕
            $order_goods_count = model('order_goods')->getCount([['order_id', '=', $order_id]], 'order_goods_id');

            $refund_count = model('order_goods')->getCount([['order_id', '=', $order_id], ['refund_status', '=', self::REFUND_COMPLETE]], 'order_goods_id');

            $refund_total_real_money = model('order_goods')->getSum([['order_id', '=', $order_id], ['refund_status', '=', self::REFUND_COMPLETE]], 'refund_real_money');

            if ($refund_total_real_money > $order_info['order_money']) {
                model('order_goods')->rollback();
                return $this->error([], '退款金额不能大于订单总金额');
            }

            //todo  退还创建订单时使用的次卡
            //实际执行转账 (存在余额支付的话   退款一部分余额  退还一部分实际金额)  //订单退款退回余额积分等操作
            if ($order_info['balance_money'] > 0 && $order_goods_info['refund_real_money'] > 0) {
                $balance_rate = $order_info['balance_money'] / $order_info['order_money'];
                $refund_balance_money = $order_goods_info['refund_real_money'] * $balance_rate;
                $refund_pay_money = $order_goods_info['refund_real_money'] - $refund_balance_money;
            } else {
                $refund_balance_money = 0;
                $refund_pay_money = $order_goods_info['refund_real_money'];
            }

            model('order_goods')->update(['refund_pay_money' => $refund_pay_money], ['order_goods_id' => $order_goods_id]);
            $addon_result = event('AddonOrderRefund', ['order_no' => $order_info['order_no'], 'promotion_type' => $order_info['promotion_type'], 'is_deposit_back' => $is_deposit_back, 'refund_money_type' => $order_goods_info['refund_money_type']], true);

            if (empty($addon_result)) {
                //原路退回的时候退还余额 + 支付金额
                if ($order_goods_info['refund_money_type'] == 1) {

                    //退还直接支付的金额
                    if ($refund_pay_money > 0) {
                        $pay_model = new Pay();

                        if ($order_goods_info['refund_no'] == '') {
                            $refund_no = $pay_model->createRefundNo();
                        } else {
                            $refund_no = $order_goods_info['refund_no'];
                        }

                        $refund_result = $pay_model->refund($refund_no, $refund_pay_money, $order_info['out_trade_no'], '', $order_info['pay_money'], $order_info['site_id'], 1, $order_goods_id, $order_info['is_video_number']);
                        if ($refund_result['code'] < 0) {
                            model('order_goods')->rollback();
                            return $refund_result;
                        }
                        //支付时超卖,主动退款的话,不计入统计数据
                        if (!$is_active_refund) {
                            $stat_model = new Stat();
                            $stat_model->switchStat(['type' => 'order_refund', 'data' => ['order_goods_id' => $order_goods_id, 'refund_pay_money' => $refund_pay_money, 'site_id' => $site_id]]);
                        }

                    }

                    //退款余额
                    if ($refund_balance_money > 0) {
                        $member_account_model = new MemberAccount();
                        // 查询该订单使用的现金余额
                        $order_use_balance_money = abs($member_account_model->getMemberAccountSum([['account_type', '=', 'balance_money'], ['type_tag', '=', $order_id], ['from_type', '=', 'order']], 'account_data')['data']);
                        // 查询该订单已退回的现金余额
                        $refunded_balance_money = $member_account_model->getMemberAccountSum([['account_type', '=', 'balance_money'], ['type_tag', '=', $order_id], ['from_type', '=', 'refund']], 'account_data')['data'];

                        if ($order_use_balance_money > $refunded_balance_money) {
                            $refundable_balance_money = $order_use_balance_money - $refunded_balance_money;
                            $refundable_balance_money = $refundable_balance_money > $refund_balance_money ? $refund_balance_money : $refundable_balance_money;
                            $refund_balance_money -= $refundable_balance_money;
                            $balance_result = $member_account_model->addMemberAccount($order_info['site_id'], $order_info['member_id'], 'balance_money', $refundable_balance_money, 'refund', $order_id, '订单退款返还');
                            if ($balance_result['code'] < 0) {
                                model('order_goods')->rollback();
                                return $balance_result;
                            }
                        }

                        if ($refund_balance_money > 0) {
                            $balance_result = $member_account_model->addMemberAccount($order_info['site_id'], $order_info['member_id'], 'balance', $refund_balance_money, 'refund', $order_id, '订单退款返还');
                            if ($balance_result['code'] < 0) {
                                model('order_goods')->rollback();
                                return $balance_result;
                            }
                        }
                    }
                } else if ($order_goods_info['refund_money_type'] == 3) { //退款到余额
                    $member_account_model = new MemberAccount();
                    $refund_result = $member_account_model->addMemberAccount($order_info['site_id'], $order_info['member_id'], 'balance', $refund_total_real_money, 'refund', $order_id, '订单退款返还');

                    if ($refund_result['code'] < 0) {
                        model('order_goods')->rollback();
                        return $refund_result;
                    }
                } else if ($order_goods_info['refund_money_type'] == 2) {//线下退款
                    //支付时超卖,主动退款的话,不计入统计数据
                    if (!$is_active_refund) {

                        $stat_model = new Stat();
                        $stat_model->switchStat(['type' => 'order_refund', 'data' => ['order_goods_id' => $order_goods_id, 'refund_pay_money' => $refund_pay_money, 'site_id' => $site_id]]);

                    }
                }

            } else {
                if ($addon_result['code'] < 0) {
                    model('order_goods')->rollback();
                    return $addon_result;
                }
            }

            //虚拟商品 退款 修改核销码状态
            if ($order_goods_info['refund_mode'] == 1 && $order_goods_info['goods_class'] == 2) {
                $verify_goods_condition = [
                    ['order_no', '=', $order_info['order_no']],
                    ['site_id', '=', $order_info['site_id']]
                ];
                $virtual_goods_res = model('goods_virtual')->update(['is_veirfy' => 2], $verify_goods_condition);

                $verify_model = new VerifyModel();
                $verify_condition = [
                    ['verify_code', '=', $order_info['virtual_code']],
                    ['site_id', '=', $order_info['site_id']]
                ];
                $verify_res = $verify_model->editVerify(['is_verify' => 2], $verify_condition);
            }

            // 退还积分 只有退款时返还 售后不返还
            if ($order_goods_info['refund_mode'] == 1 && $order_goods_info['use_point'] > 0) {
                $member_account_model = new MemberAccount();
                $point_result = $member_account_model->addMemberAccount($order_info['site_id'], $order_info['member_id'], 'point', $order_goods_info['use_point'], 'refund', $order_id, '订单退款返还');
                if ($point_result['code'] < 0) {
                    model('order_goods')->rollback();
                    return $point_result;
                }
            }

            // 退还次卡
            if (!empty($order_goods_info['card_item_id']) && addon_is_exit('cardservice', $order_goods_info[ 'site_id' ])) {
                (new MemberCard())->refund([ ['type' => 'order', 'relation_id' => $order_goods_info['order_goods_id'] ] ]);
            }

            //验证订单锁定状态
            $lock_result = $this->verifyOrderLock($order_id);

            //验证订单是否全部退款完毕  订单如果全部退款完毕,订单关闭
            if ($order_goods_count == $refund_count) {
                //将订单设置为不可退款
                $order_common_model = new OrderCommon();
                $order_common_model->orderUpdate(['is_enable_refund' => 0], [['order_id', '=', $order_id]]);

                if ($order_info['order_status'] != Order::ORDER_COMPLETE) {
                    $order_common_model = new OrderCommon();
                    //记录订单日志 start
                    if (!empty($log_data)) {
                        $log_data = array_merge($log_data, [
                            'order_id' => $order_id,
                            'order_status' => -1,
                            'order_status_name' => '已关闭'
                        ]);
                        $order_common_model->addOrderLog($log_data);
                    }
                    $close_result = $order_common_model->orderClose($order_id, [], '退款完成,订单关闭');
                    if ($close_result['code'] < 0) {
                        model('order_goods')->rollback();
                        return $close_result;
                    }
                }else{
                    if (!empty($log_data)) {
                        $order_common_model = new OrderCommon();
                        $log_data = array_merge($log_data, [
                            'order_id' => $order_id,
                            'order_status' => $order_info['order_status'],
                            'order_status_name' => $order_info['order_status_name']
                        ]);
                        $order_common_model->addOrderLog($log_data);
                    }
                }

            } else {
                //记录订单日志 start
                if (!empty($log_data)) {
                    $order_common_model = new OrderCommon();
                    $log_data = array_merge($log_data, [
                        'order_id' => $order_id,
                        'order_status' => $order_info['order_status'],
                        'order_status_name' => $order_info['order_status_name']
                    ]);
                    $order_common_model->addOrderLog($log_data);
                }
                //记录订单日志 end
            }

            // 如果售后完成关闭订单评价
            if ($order_goods_count == $refund_count && $order_info['order_status'] == Order::ORDER_COMPLETE) {
                $order_common_model = new OrderCommon();
                $order_common_model->orderUpdate(['is_evaluate' => 0], [['order_id', '=', $order_id]]);
            } else if ($order_info['order_status'] == Order::ORDER_COMPLETE || $order_info['order_status'] == Order::ORDER_TAKE_DELIVERY) {
                $order_common_model = new OrderCommon();
                $order_common_model->orderUpdate(['is_evaluate' => 1], [['order_id', '=', $order_id]]);
            }

            //订单累加 退款
            model('order')->update(['refund_money' => $refund_total_real_money], [['order_id', '=', $order_id]]);
            event('OrderRefundFinish', $order_goods_info);//传入订单类型以及订单项id
            model('order_goods')->commit();
            return $this->success();
        } catch ( \Exception $e ) {

            model('order_goods')->rollback();
            return $this->error('', $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }


    /**
     * 获取订单售后操作列表
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getOrderRefundLogList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $list = model('order_refund_log')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取退款维权订单列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getRefundOrderGoodsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = 'nop.*,no.order_no,no.site_id,no.site_name,no.name,m.nickname')
    {
        $join = [
            [
                'order no',
                'nop.order_id = no.order_id',
                'left'
            ],
            [
                'member m',
                'm.member_id = no.member_id',
                'left'
            ],
        ];
        $list = model('order_goods')->pageList($condition, $field, $order, $page, $page_size, 'nop', $join);
        if (!empty($list['list'])) {
            foreach ($list['list'] as $k => $v) {
                $refund_action = empty($v['refund_status_action']) ? [] : json_decode($v['refund_status_action'], true);
                $refund_member_action = $refund_action['member_action'] ?? [];
                $list['list'][$k]['refund_action'] = $refund_member_action;
            }
        }
        return $this->success($list);
    }

    /**
     * 获取退款维权订单数量
     * @param array $condition
     */
    public function getRefundOrderGoodsCount($condition = [])
    {
        $count = model('order_goods')->getCount($condition);
        return $this->success($count);
    }

    /**
     * 初始化订单项退款操作
     * @param $order_id
     */
    public function initOrderGoodsRefundAction($condition)
    {
        //订单项增加可退款操作
        $data = array(
            'refund_status_action' => json_encode($this->order_refund_status[0], JSON_UNESCAPED_UNICODE)
        );
        $result = model('order_goods')->update($data, $condition);
        return $this->success($result);
    }

    /**
     * 移除订单项退款操作
     * @param $order_id
     */
    public function removeOrderGoodsRefundAction($condition)
    {
        //订单项增加可退款操作
        $data = array(
            'refund_status_action' => ''
        );
        $result = model('order_goods')->update($data, $condition);
        return $this->success($result);
    }

    /**
     * 会员维权详情
     * @param $order_goods_id
     */
    public function getMemberRefundDetail($order_goods_id, $member_id)
    {
        $condition = array(
            ['order_goods_id', '=', $order_goods_id]
        );

        $condition[] = ['member_id', '=', $member_id];

        $info = model('order_goods')->getInfo($condition);
        $refund_action = empty($info['refund_status_action']) ? [] : json_decode($info['refund_status_action'], true);
        $refund_member_action = $refund_action['member_action'] ?? [];
        $info['refund_action'] = $refund_member_action;

        //将售后日志引入
        $refund_log_list = model('order_refund_log')->getList([['order_goods_id', '=', $order_goods_id]], '*', 'action_time desc');
        $info['refund_log_list'] = $refund_log_list;
        return $this->success($info);
    }

    /**
     * 会员维权详情
     * @param $order_goods_id
     */
    public function getRefundDetail($order_goods_id, $site_id = 0, $store_id = 0)
    {
        $condition = array(
            ['order_goods_id', '=', $order_goods_id]
        );
        if ($site_id > 0) {
            $condition[] = ['site_id', '=', $site_id];
        }
        if ($store_id > 0) {
            $condition[] = ['store_id', '=', $store_id];
        }
        $info = model('order_goods')->getInfo($condition);
        if (empty($info)) return $this->error('', '');

        //添加会员昵称
        $member = new Member();
        $member_info = $member->getMemberInfo([ [ "member_id", '=', $info[ 'member_id' ] ] ], 'nickname')['data'] ?? [];
        $info[ 'nickname' ] = $member_info[ 'nickname' ] ?? '';

        if ($info['refund_status'] == 0) {
            $refund_apply_arr = $this->getOrderRefundMoney($order_goods_id);
            $info['refund_apply_money'] = round($refund_apply_arr['refund_money'], 2);
            $info['refund_delivery_money'] = $refund_apply_arr['refund_delivery_money'];
        }
        $refund_action = empty($info['refund_status_action']) ? [] : json_decode($info['refund_status_action'], true);
        $refund_action = $refund_action['action'] ?? [];
        $info['refund_action'] = $refund_action;
        //将售后日志引入
        $refund_log_list = model('order_refund_log')->getList([['order_goods_id', '=', $order_goods_id]], '*', 'action_time desc');
        $info['refund_log_list'] = $refund_log_list;
        return $this->success($info);
    }

    /**
     * 根据配送状态获取退款方式
     * @param $refund_status
     */
    public function getRefundType($order_goods_info)
    {
        if ($order_goods_info['is_virtual'] == 1) {
            return [self::ONLY_REFUNDS];
        } else {
            if ($order_goods_info['delivery_status'] == 0) {
                return [self::ONLY_REFUNDS];
            } else {
                return [self::ONLY_REFUNDS, self::A_REFUND_RETURN];
            }
        }

    }

    /**
     * 根据配送状态获取退款方式
     */
    public function getRefundOrderType($order_id)
    {
        $status = model('order')->getInfo([['order_id', '=', $order_id]], 'delivery_status');
        if ($status['delivery_status'] == 0) {
            return [self::ONLY_REFUNDS];
        } else {
            return [self::ONLY_REFUNDS, self::A_REFUND_RETURN];
        }
    }
    /****************************************************************************订单退款相关操作（结束）**********************************/

    /********************************************************************** 主动退款 ********************************************************************/
    /**
     * 主动完成退款流程
     * @param $order_id
     */
    public function activeRefund($order_id, $remark, $refund_reason)
    {
        $order_info = model('order')->getInfo([['order_id', '=', $order_id]], 'order_money, out_trade_no, site_id, delivery_money');
        if ($order_info['order_money'] > 0) {
            $pay_model = new Pay();
            //遍历订单项
            $order_goods_list = model('order_goods')->getList([['order_id', '=', $order_id]]);
            if (!empty($order_goods_list)) {
                $count = count($order_goods_list);
                foreach ($order_goods_list as $k => $v) {
                    $item_refund_money = $v['real_goods_money'];
                    if ($count == ($k + 1)) {
                        $item_refund_money += $order_info['delivery_money'];
                    }
                    $item_result = $this->activeOrderGoodsRefund($v['order_goods_id'], $item_refund_money, $remark, $refund_reason);
                    if ($item_result['code'] < 0) {
                        return $item_result;
                    }
                }
            }

            //订单整体退款
//            $refund_result = $pay_model->refund($refund_no, $order_info['pay_money'], $order_info['out_trade_no'], '', $order_info['pay_money'], $order_info['site_id'], 1);
            return $this->success();
        } else {
            return $this->success();
        }

    }

    /**
     * 订单项主动退款
     * @param $order_goods_id
     * @param $refund_money
     * @return array|mixed|void
     */
    public function activeOrderGoodsRefund($order_goods_id, $refund_money, $remark = '', $refund_reason = '')
    {
        model('order_goods')->startTrans();
        try {

            //判断是否退款完毕
            $order_goods_info = model('order_goods')->getInfo([['order_goods_id', '=', $order_goods_id]]);
            if ($order_goods_info['refund_status'] == self::REFUND_COMPLETE) {
                model('order_goods')->rollback();
                return $this->error('', '订单不能重复维权');
            }
            $pay_model = new Pay();
            $refund_no = $pay_model->createRefundNo();

            $update_data = array(
                'refund_no' => $refund_no,
                'refund_time' => time(),
                'refund_reason' => $refund_reason,
                'refund_apply_money' => $refund_money,
                'refund_real_money' => $refund_money,
                'refund_action_time' => time()
            );
            $update_data['refund_status'] = self::REFUND_COMPLETE;
            $update_data['refund_status_name'] = $this->order_refund_status[self::REFUND_COMPLETE]['name'];
            $update_data['refund_status_action'] = json_encode($this->order_refund_status[self::REFUND_COMPLETE], JSON_UNESCAPED_UNICODE);
            $res = model('order_goods')->update($update_data, [['order_goods_id', '=', $order_goods_id]]);
            if ($res === false) {
                model('order_goods')->rollback();
                return $this->error();
            }


            $refund_result = $this->finishAction($order_goods_id, [], 1, true);
            if ($refund_result['code'] < 0) {
                model('order_goods')->rollback();
                return $refund_result;
            }
            //退货日志
            $this->addOrderRefundLog($order_goods_id, self::REFUND_COMPLETE, $remark . ',维权完成', 3, 0, '平台');

            model('order_goods')->commit();
            return $this->success();
        } catch ( \Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /********************************************************************** 主动退款 ********************************************************************/
    /**
     * 判断订单的锁定状态
     * @param $order_goods_id
     */
    public function verifyOrderLock($order_id)
    {
        $condition = array(
            ['order_id', '=', $order_id],
            ['refund_status', 'not in', [0, 3]],
        );
        $count = model('order_goods')->getCount($condition, 'order_goods_id');

        $order_common_model = new OrderCommon();

        if ($count > 0) {
            $res = $order_common_model->orderLock($order_id);
        } else {
            $res = $order_common_model->orderUnlock($order_id);

        }

        return $res;
    }

    /**
     * 关闭退款
     * @param $order_goods_id
     * @param $site_id
     */
    public function orderRefundClose($order_goods_id, $site_id, $user_info)
    {
        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $order_goods_id, 'site_id' => $site_id]);
        if (empty($order_goods_info)) {
            return $this->error();
        }
        model('order_goods')->startTrans();
        try {
            $data = [
                'order_goods_id' => $order_goods_id,
                'refund_status' => 0,
                'refund_status_name' => $this->order_refund_status[0]['name'],
                'refund_status_action' => json_encode($this->order_refund_status[0], JSON_UNESCAPED_UNICODE),
                'refund_type' => 0,
                'refund_address' => '',
                'refund_delivery_remark' => '',
                'refund_remark' => '',
                'refund_delivery_name' => '',
                'refund_delivery_no' => '',
                'refund_reason' => ''
            ];
            $res = model('order_goods')->update($data, ['order_goods_id' => $order_goods_id]);

            //验证订单锁定状态
            $lock_result = $this->verifyOrderLock($order_goods_info['order_id']);

            //记录订单日志 start
            $order_common_model = new OrderCommon();
            $order_info = model('order')->getInfo(['order_id' => $order_goods_info['order_id']], 'order_status,order_status_name,is_video_number,site_id,member_id');
            $log_data = [
                'uid' => $user_info['uid'],
                'nick_name' => $user_info['username'],
                'action' => '商家关闭了维权',
                'action_way' => 2,
                'order_id' => $order_goods_info['order_id'],
                'order_status' => $order_info['order_status'],
                'order_status_name' => $order_info['order_status_name']
            ];
            $order_common_model->addOrderLog($log_data);
            //记录订单日志 end

            if ($order_info['is_video_number'] == 1) {
                $weapp_model = new Weapp($order_info['site_id']);
                $member = model('member')->getInfo([['member_id', '=', $order_info['member_id']]]);
                $res = $weapp_model->orderNoRefund([
                    'out_aftersale_id' => $order_goods_info['out_aftersale_id']
                ]);
                if ($res['code'] < 0) {
                    model('order_goods')->rollback();
                    return $this->error('', $res['message']);
                }
            }

            $this->addOrderRefundLog($data['order_goods_id'], 0, '卖家关闭本次维权', 2, $user_info['uid'], $user_info['username']);
            event('memberCancelRefund', $data);//传入订单类型以及订单项id
            model('order_goods')->commit();
            return $this->success();
        } catch ( \Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 获取订单项退款信息
     * @param $order_goods_id
     * @param $site_id
     * @return array
     */
    public function getOrderGoodsRefundInfo($order_goods_id, $site_id = 0, $store_id = 0)
    {
        $order_goods_condition = array(
            ['order_goods_id', '=', $order_goods_id]
        );
        if ($site_id > 0) {
            $order_goods_condition[] = ['site_id', '=', $site_id];
        }
        if ($store_id > 0) {
            $order_goods_condition[] = ['store_id', '=', $store_id];
        }
        $order_goods_info = model('order_goods')->getInfo($order_goods_condition);
        if (empty($order_goods_info)) {
            return $this->error('', '该订单项不存在');
        }
        if ($order_goods_info['refund_status'] == self::REFUND_COMPLETE) {
            return $this->error('该订单项已维权结束');
        }
        if ($order_goods_info['refund_status'] == 0) {
            $refund_apply_arr = $this->getOrderRefundMoney($order_goods_id);
            $order_goods_info['refund_apply_money'] = round($refund_apply_arr['refund_money'], 2);
            $order_goods_info['refund_delivery_money'] = $refund_apply_arr['refund_delivery_money'];
        }

        //获取订单信息
        $order_info = model('order')->getInfo([['order_id', '=', $order_goods_info['order_id']]]);

        $coupon_info = [];
        if ($order_info['coupon_id'] > 0) {
            $order_goods_count = model('order_goods')->getCount([['order_id', '=', $order_goods_info['order_id']]], 'order_goods_id');
            $refund_count = model('order_goods')->getCount([['order_id', '=', $order_goods_info['order_id']], ['refund_status', '=', self::REFUND_COMPLETE]], 'order_goods_id');

            if (($order_goods_count - $refund_count) == 1) {
                //查询优惠劵信息
                $coupon_model = new Coupon();
                $coupon_info = $coupon_model->getCouponInfo([['coupon_id', '=', $order_info['coupon_id']]], 'coupon_id,coupon_name,type,at_least,money,discount,discount_limit');
                $coupon_info = $coupon_info['data'];
            }
        }

        $data = [
            'order_goods_info' => $order_goods_info,
            'order_info' => $order_info,
            'coupon_info' => $coupon_info
        ];

        //预售订单
        if ($order_info['promotion_type'] == 'presale') {
            $presale_order_model = new PresaleOrder();
            $presale_order_info = $presale_order_model->getPresaleOrderInfo([['order_no', '=', $order_info['order_no']]], 'presale_deposit_money,final_money');
            $data['presale_order_info'] = $presale_order_info['data'];
        }

        return $this->success($data);

    }

    public function addshopcomponent($data)
    {

        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $data['order_goods_id']]);
        if (empty($order_goods_info)) {
            return $this->error();
        }
        $refund_apply_money = $order_goods_info['refund_apply_money'];

        $shop_active_refund = $data['shop_active_refund'] ?? 0;
        if ($shop_active_refund == 1) {//商家主动退款
            //查询发货状态(已发货的不能主动退款)
            if ($order_goods_info['delivery_status'] != OrderModel::DELIVERY_WAIT) {
                return $this->error();
            }
            //获取可退金额
            $refund_apply_money_arr = $this->getOrderRefundMoney($data['order_goods_id']);
            $refund_apply_money = $refund_apply_money_arr['refund_money'];
            $refund_delivery_money = $refund_apply_money_arr['refund_delivery_money'];
            $data['refund_real_money'] = $refund_apply_money;
            $data['refund_delivery_money'] = $refund_delivery_money;
        } else {
            if ($order_goods_info['refund_status'] != self::REFUND_TAKEDELIVERY && $order_goods_info['refund_status'] != self::REFUND_CONFIRM) {
                return $this->error();
            }
        }

        if ($data['refund_real_money'] > $refund_apply_money) return $this->error('', '退款金额超出最大可退金额');
        //视频号订单创建订单
        $order_info = model('order')->getInfo([['order_id', '=', $order_goods_info['order_id']]]);
        if ($order_info['is_video_number'] == 1) {
            $member = model('member')->getInfo([['member_id', '=', $order_info['member_id']]]);

            $data = [
                'out_order_id' => (string)$order_info['order_id'],
                'out_aftersale_id' => $data['out_aftersale_id'],
                'openid' => $member['weapp_openid'],
                'type' => 1,
                'product_info' => [
                    'out_product_id' => (string)$order_goods_info['goods_id'],
                    'out_sku_id' => (string)$order_goods_info['sku_id'],
                    'product_cnt' => (int)$order_goods_info['num'],
                ],
                'refund_reason' => '无',
                'refund_reason_type' => 12,
                'orderamt' => round($refund_apply_money * 100)
            ];
            $weapp = new Weapp($order_info['site_id']);
            $res = $weapp->addAftersale($data);
            if ($res['code'] < 0) {
                return $this->error('', $res['message']);
            }
        }
    }


    /******************************************************************************** 主动退款(商家) **********************************************/
    /**
     * 商家主动退款
     * @param $params
     */
    public function doOrderOrderRefund($params)
    {
        $site_id = $params['site_id'] ?? 0;
        $app_module = $params['app_module'];
        $refund_money_type = $params['refund_money_type'];
        $shop_refund_remark = $params['shop_refund_remark'];
        $do_refund_money = $params['do_refund_money'] ?? 0;
        $user_info = $params['user_info'];
        $order_goods_id = $params['order_goods_id'];
        $config_model = new Config();
        $config_info = $config_model->getOrderEventTimeConfig($site_id, $app_module)['data']['value'] ?? [];
        $do_refund = $config_info['do_refund'] ?? 1;

        $order_commnn_model = new OrderCommon();
        $order_goods_info = $order_commnn_model->getOrderGoodsInfo([['order_goods_id', '=', $order_goods_id]]);
        $out_aftersale_id = $order_goods_info['data']['order_no'] . time() . $order_goods_id;

        $data = array(
            'order_goods_id' => $order_goods_id,
            'refund_money_type' => $refund_money_type,
            'shop_refund_remark' => $shop_refund_remark,
            'shop_active_refund' => 1,
            'out_aftersale_id' => $out_aftersale_id,
            'do_refund_money' => $do_refund_money
        );

        $log_data = [
            'uid' => $user_info['uid'],
            'nick_name' => $user_info['username'],
            'action' => '商家对订单进行了主动退款',
            'action_way' => 2
        ];


        if ($do_refund == 1) {//直接完成退款
            //创建视频号售后订单
            $res = $this->addshopcomponent($data);
            if ($res) {
                return $res;
            }
            $result = $this->orderRefundFinish($data, $user_info, $log_data);
        } else {
            //发起申请,需要审核通过转账(仅退款)
            //主动模拟退款申请和退款通过
            $apply_data = array(
                'order_goods_id' => $order_goods_id,
                'refund_type' => self::ONLY_REFUNDS,
                'refund_reason' => '商家对订单进行了主动退款',
                'refund_remark' => '商家对订单进行了主动退款',

            );
            $refund_data = array(
                'data' => $apply_data,
                'user_info' => $user_info,
                'log_data' => $log_data,
                'do_refund_money' => $do_refund_money
            );

            $result = $this->doOrderOrderRefundApply($refund_data);

        }
        return $result;

    }

    /**
     * 店家主动申请退款
     * @param $params
     * @return array
     */
    public function doOrderOrderRefundApply($params)
    {
        $data = $params['data'];
        $user_info = $params['user_info'];
        $log_data = $params['log_data'] ?? [];
        $do_refund_money = $params['do_refund_money'];
        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $data['order_goods_id']], 'order_id,refund_status,delivery_status,is_virtual,site_id,order_no,order_goods_id');
        if (empty($order_goods_info))
            return $this->error();

        if ($order_goods_info['refund_status'] != 0 && $order_goods_info['refund_status'] != -1)
            return $this->error([], '存在进行中的退款');

//        $refund_type_list = $this->getRefundType($order_goods_info);
//        //防止退款方式越权
//        if (!in_array($data[ 'refund_type' ], $refund_type_list))
//            return $this->error();


        $order_info = model('order')->getInfo(['order_id' => $order_goods_info['order_id']]);
        //判断是否允许申请退款
        if ($order_info['is_enable_refund'] == 0) {
            if ($order_info['promotion_type'] == 'pinfan') {
                return $this->error('', '拼团活动正在进行中,待拼成功后可发起退款');
            }
            return $this->error();
        }

        model('order_goods')->startTrans();
        try {

            $data['refund_status'] = self::REFUND_APPLY;
            $data['refund_status_name'] = $this->order_refund_status[self::REFUND_APPLY]['name'];
            $data['refund_status_action'] = json_encode($this->order_refund_status[self::REFUND_APPLY], JSON_UNESCAPED_UNICODE);
            $data['refund_mode'] = $order_info['order_status'] == Order::ORDER_COMPLETE ? 2 : 1;

            $pay_model = new Pay();
            $data['refund_no'] = $pay_model->createRefundNo();
            $data['refund_action_time'] = time();
            $refund_apply_money_array = $this->getOrderRefundMoney($data['order_goods_id']);//可退款金额 通过计算获得

            $refund_apply_money = $refund_apply_money_array['refund_money'];
            if ($do_refund_money > $refund_apply_money) {
                model('order_goods')->rollback();
                return $this->error('', '主动退款金额不能大于可退款总额');
            }
            $refund_apply_money = $do_refund_money;
            $refund_delivery_money = $refund_apply_money_array['refund_delivery_money'];

            $data['refund_apply_money'] = $refund_apply_money;//申请的总退款
            $data['refund_delivery_money'] = $refund_delivery_money;//退的运费

            //生成视频号订单ID
            $data['out_aftersale_id'] = $order_goods_info['order_no'] . time() . $order_goods_info['order_goods_id'];
            $res = model('order_goods')->update($data, ['order_goods_id' => $data['order_goods_id']]);

            //验证订单锁定状态
            $local_result = $this->verifyOrderLock($order_goods_info['order_id']);

            $this->addOrderRefundLog($data['order_goods_id'], self::REFUND_APPLY, '商家主动退款', 2, $user_info['uid'], $user_info['username']);

            event('orderRefundApply', $data);//传入订单类型以及订单项id


            model('order_goods')->commit();

            //订单会员申请退款消息
//            $message_model = new Message();
//            $message_model->sendMessage([ 'keywords' => 'BUYER_REFUND', 'order_goods_id' => $data[ 'order_goods_id' ], 'site_id' => $order_goods_info[ 'site_id' ] ]);

            // 发起维权 关闭订单评价
            model('order')->update(['is_evaluate' => 0], ['order_id' => $order_goods_info['order_id']]);

            //记录订单日志 start
            if ($log_data) {
                $order_common_model = new OrderCommon();
                $log_data = array_merge($log_data, [
                    'order_id' => $order_goods_info['order_id'],
                    'order_status' => $order_info['order_status'],
                    'order_status_name' => $order_info['order_status_name']
                ]);

                $order_common_model->addOrderLog($log_data);
            }
            //记录订单日志 end

            return $this->success($res);
        } catch ( \Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 查询退货地址
     * @param $site_id
     * @return array
     */
    public function getRefundAddress($site_id, $refund_address_id = 0)
    {
        $address = [];
        $site_address_model = new SiteAddress();
        $site_address_condition = array(
            ['site_id', '=', $site_id],
            ['is_return', '=', 1],
        );
        if ($refund_address_id > 0) {
            $site_address_condition[] = ['id', '=', $refund_address_id];
        } else {
            $site_address_condition[] = ['is_return_default', '=', 1];
        }
        $site_address_info = $site_address_model->getAddressInfo($site_address_condition)['data'] ?? [];
        if (empty($site_address_info)) {
            unset($site_address_condition[2]);
            $site_address_info = $site_address_model->getAddressInfo($site_address_condition)['data'] ?? [];
        }
        if (empty($site_address_info)) {
            $shop_model = new Shop();
            $shop_info_result = $shop_model->getShopInfo([['site_id', '=', $site_id]], 'full_address,address,name,mobile');
            $shop_info = $shop_info_result['data'];
            $address['shop_contacts'] = $shop_info['name'];
            $address['shop_mobile'] = $shop_info['mobile'];
            $address['shop_address'] = $shop_info['full_address'] . $shop_info['address'];
        }
        if (!empty($site_address_info)) {
            $address['shop_contacts'] = $site_address_info['contact_name'];
            $address['shop_mobile'] = $site_address_info['mobile'];
            $address['shop_address'] = $site_address_info['full_address'];
        }
        return $address;
    }

    /**
     * 求和
     * @param  array $where
     * @param  string $field
     * @param  string $alias
     * @param  null $join
     * @return array
     */
    public function getRefundSum($where = [], $field = '', $alias = 'a', $join = null)
    {
        $data = model('order_goods')->getSum($where, $field, $alias, $join);
        return $this->success($data);
    }


    /**
     * 订单项退款
     * @param $order_goods_info
     * @return mixed|void
     */
    public function orderGoodsRefund($order_goods_info)
    {
        $order_info = model('order')->getInfo(['order_id' => $order_goods_info['order_id']]);
        $order_goods_info['order_info'] = $order_info;

        $result = event('OrderGoodsRefund', $order_goods_info, true);
        if (empty($result)) {
            $order_common_model = new OrderCommon();
            $order_model = $order_common_model->getOrderModel($order_info);
            $result = $order_model->refund($order_goods_info);
        }

        return $result;
    }
}