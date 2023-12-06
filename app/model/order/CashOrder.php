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

use app\model\goods\GoodsStock;

/**
 * 门店自提订单
 *
 * @author Administrator
 *
 */
class CashOrder extends OrderCommon
{

    /*****************************************************************************************订单状态***********************************************/
    // 订单创建
    const ORDER_CREATE = 0;

    // 订单已支付
    const ORDER_PAY = 1;

    // 订单待提货
    const ORDER_PENDING_DELIVERY = 2;

    // 订单已发货（配货）
    const ORDER_DELIVERY = 3;

    // 订单已收货
    const ORDER_TAKE_DELIVERY = 4;

    // 订单已结算完成
    const ORDER_COMPLETE = 10;

    // 订单已关闭
    const ORDER_CLOSE = -1;


    /**
     * 订单类型
     *
     * @var int
     */
    public $order_type = 2;


    /**
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
                    'action' => 'orderAdjustMoney',
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
        self::ORDER_COMPLETE => [
            'status' => self::ORDER_COMPLETE,
            'name' => '已完成',
            'is_allow_refund' => 0,
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
     * 订单支付
     * @param unknown $order_info
     */
    public function orderPay($order_info, $pay_type, $log_data = [])
    {
        $order_id = $order_info['order_id'];
        if ($order_info['order_status'] != 0) {
            return $this->error();
        }

        $condition = array(
            ['order_id', '=', $order_id],
            ['order_status', '=', self::ORDER_CREATE],
        );

        $order_goods_list = model('order_goods')->getList([['order_id', '=', $order_id]], 'sku_image,sku_name,price,num,order_goods_id,goods_id,sku_id');
        $item_array = [];
        foreach ($order_goods_list as $k => $v) {
            $item_array[] = [
                'img' => $v['sku_image'],
                'name' => $v['sku_name'],
                'price' => $v['price'],
                'num' => $v['num'],
                'order_goods_id' => $v['order_goods_id'],
                'remark_array' => [

                ]
            ];
            // 增加门店商品销量
            model('store_goods')->setInc([['goods_id', '=', $v['goods_id']], ['store_id', '=', $order_info['delivery_store_id']]], 'sale_num', $v['num']);
            model('store_goods_sku')->setInc([['sku_id', '=', $v['sku_id']], ['store_id', '=', $order_info['delivery_store_id']]], 'sale_num', $v['num']);
        }
        $pay_time = time();
        $pay_type_list = $this->getPayType();
        $data = array(
            'pay_status' => 1,
            'pay_time' => $pay_time,
            'is_enable_refund' => 0,
            'pay_type' => $pay_type,
            'pay_type_name' => $pay_type_list[$pay_type]
        );

        //记录订单日志 start
        $action = '商家对订单进行了线下支付';
        //获取用户信息
        if (empty($log_data)) {
            $member_info = model('member')->getInfo(['member_id' => $order_info['member_id']], 'nickname');
            $log_data = [
                'uid' => $order_info['member_id'],
                'nick_name' => $member_info['nickname'],
                'action_way' => 1
            ];
            $buyer_name = empty($member_info[ 'nickname' ]) ? '' : '【' . $member_info[ 'nickname' ] . '】';
            $action = '买家'.$buyer_name.'支付了订单';
        }

        $log_data = array_merge($log_data, [
            'order_id' => $order_id,
            'action' => $action,
        ]);

        $this->addOrderLog($log_data);
        //记录订单日志 end

        $res = model('order')->update($data, $condition);

        $order_goods_data = array(
            'delivery_status_name' => '已收货'
        );
        $res = model('order_goods')->update($order_goods_data, [['order_id', '=', $order_id]]);

        $order_common_model = new OrderCommon();
        $result = $order_common_model->orderComplete($order_id);
        if ($result['code'] < 0)
            return $result;
        return $this->success($res);
    }


    /**
     * 退款完成操作
     * @param $order_info
     */
    public function refund($order_goods_info)
    {
        //是否入库
        if ($order_goods_info['is_refund_stock'] == 1) {
            $goods_stock_model = new GoodsStock();
            $item_param = array(
                'sku_id' => $order_goods_info['sku_id'],
                'num' => $order_goods_info['num'],
            );
            //返还库存
            $goods_stock_model->incStock($item_param);
        }
    }

    /**
     * 订单详情
     * @param $order_info
     */
    public function orderDetail($order_info)
    {
        return [];
    }


}