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
use app\model\goods\VirtualGoods;
use app\model\system\Cron;
use app\model\verify\Verify as VerifyModel;
use app\model\verify\VerifyRecord;

/**
 * 虚拟订单
 *
 * @author Administrator
 *
 */
class VirtualOrder extends OrderCommon
{

    /*****************************************************************************************订单状态***********************************************/
    // 订单创建
    const ORDER_CREATE = 0;

    // 订单已支付
    const ORDER_PAY = 1;

    // 订单待收货
    const ORDER_DELIVERY = 3;
    // 订单已收货
    const ORDER_TAKE_DELIVERY = 4;

    // 订单已结算完成
    const ORDER_COMPLETE = 10;

    // 订单已关闭
    const ORDER_CLOSE = -1;


    // 订单待使用
    const ORDER_WAIT_VERIFY = 11;

    //订单已使用
    const ORDER_VERIFYED = 12;

    /**
     * 订单类型
     *
     * @var int
     */
    public $order_type = 4;

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

        self::ORDER_PAY => [
            'status' => self::ORDER_PAY,
            'name' => '待发货',
            'is_allow_refund' => 0,
            'icon' => 'public/uniapp/order/order-icon-send.png',
            'action' => [
                [
                    'action' => 'orderVirtualDelivery',
                    'title' => '发货',
                    'color' => ''
                ],
            ],
            'member_action' => [],
            'color' => ''
        ],
        self::ORDER_DELIVERY => [
            'status' => self::ORDER_DELIVERY,
            'name' => '待收货',
            'is_allow_refund' => 1,
            'icon' => 'public/uniapp/order/order-icon-received.png',
            'action' => [
            ],
            'member_action' => [
                [
                    'action' => 'memberVirtualTakeDelivery',
                    'title' => '确认使用',
                    'color' => ''
                ],
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
     * 订单支付
     * @param unknown $order_info
     */
    public function orderPay($order_info, $pay_type,$log_data = [])
    {
        $pay_type_list = $this->getPayType();
        $data = array(
            'order_status' => self::ORDER_PAY,
            'order_status_name' => $this->order_status[self::ORDER_PAY]["name"],
            'order_status_action' => json_encode($this->order_status[self::ORDER_PAY], JSON_UNESCAPED_UNICODE),
            "pay_status" => 1,
            "pay_time" => time(),
            "is_enable_refund" => 1,
            "pay_type" => $pay_type,
            "pay_type_name" => $pay_type_list[$pay_type]
        );

        //记录订单日志 start
        $action = '商家对订单进行了线下支付';
        //获取用户信息
        if (empty($log_data)){
            $member_info = model('member')->getInfo(['member_id'=>$order_info['member_id']],'nickname');
            $log_data = [
                'uid'        => $order_info[ 'member_id' ],
                'nick_name'  => $member_info['nickname'],
                'action_way' => 1
            ];
            $buyer_name = empty($member_info[ 'nickname' ]) ? '' : '【' . $member_info[ 'nickname' ] . '】';
            $action = '买家'.$buyer_name.'支付了订单';
        }
        $log_data = array_merge($log_data,[
            'order_id'          => $order_info['order_id'],
            'action'            => $action,
            'order_status'      => self::ORDER_PAY,
            'order_status_name' => $this->order_status[self::ORDER_PAY]["name"],
        ]);

        $this->addOrderLog($log_data);
        //记录订单日志 end

        $res = model('order')->update($data, [['order_id', "=", $order_info['order_id']]]);

        $goods_id = model('order_goods')->getValue([['order_id', '=', $order_info['order_id']]], 'goods_id');
        //判断商品是否需要核销
        $goods_info = model('goods')->getInfo([['goods_id', '=', $goods_id]], 'goods_class,is_need_verify,virtual_deliver_type');

        if ($order_info['is_lock'] == 0) {
            if (in_array($order_info['promotion_type'], ['pintuan', 'pinfan'])) {
            } else {
                if ($goods_info['goods_class'] == 2) {

                    switch ($goods_info['virtual_deliver_type']) {
                        case 'auto_deliver'://自动发货
                            $result = $this->virtualDelivery($order_info);//
                            break;
                        case 'artificial_deliver'://手动发货

                            break;
                        case 'verify'://到店核销
                            //核销次数  is_need_verify
                            $order_status_item = $this->order_status[self::ORDER_WAIT_VERIFY];
//                            $order_status_item['name'] = '待使用';
//                            $order_status_item['action'] = [];
//                            $order_status_item['member_action'] = [];
                            $data = array(
                                'order_status' => self::ORDER_WAIT_VERIFY,
                                'order_status_name' => $order_status_item['name'],
                                'order_status_action' => json_encode($order_status_item, JSON_UNESCAPED_UNICODE),
                            );
                            $res = model('order')->update($data, [['order_id', "=", $order_info['order_id']]]);

                            if ($goods_info['is_need_verify']) {
                                //虚拟产品发货
                                $this->virtualOrderTakeDelivery($order_info['order_id']);
                            } else {
                                //虚拟产品收货
                                $this->orderCommonTakeDelivery($order_info['order_id']);
                            }
                            break;
                    }


                } elseif ($goods_info['goods_class'] == 3) {
                    $order_status_item = $this->order_status[self::ORDER_DELIVERY];
                    $order_status_item['action'] = [];
                    $order_status_item['member_action'] = [];
                    $data = array(
                        'order_status' => self::ORDER_DELIVERY,
                        'order_status_name' => $this->order_status[self::ORDER_DELIVERY]["name"],
                        'order_status_action' => json_encode($order_status_item, JSON_UNESCAPED_UNICODE),
                    );
                    $res = model('order')->update($data, [['order_id', "=", $order_info['order_id']]]);

                    // 卡密商品发货
                    $result = $this->virtualcardTakeDelivery($order_info['order_id']);
                    if ($result['code'] < 0) {
                        return $result;
                    }
                } elseif($goods_info['goods_class'] == 4) {
                    $order_status_item = $this->order_status[self::ORDER_DELIVERY];
                    $order_status_item['action'] = [];
                    $order_status_item['member_action'] = [];
                    $data = array(
                        'order_status' => self::ORDER_DELIVERY,
                        'order_status_name' => $this->order_status[self::ORDER_DELIVERY]["name"],
                        'order_status_action' => json_encode($order_status_item, JSON_UNESCAPED_UNICODE),
                    );
                    $res = model('order')->update($data, [['order_id', "=", $order_info['order_id']]]);
                    $this->serviceGoodsTakeDelivery($order_info['order_id']);
                }elseif($goods_info['goods_class'] == 5) {
                    $order_status_item = $this->order_status[self::ORDER_DELIVERY];
                    $order_status_item['action'] = [];
                    $order_status_item['member_action'] = [];
                    $data = array(
                        'order_status' => self::ORDER_DELIVERY,
                        'order_status_name' => $this->order_status[self::ORDER_DELIVERY]["name"],
                        'order_status_action' => json_encode($order_status_item, JSON_UNESCAPED_UNICODE),
                    );
                    $res = model('order')->update($data, [['order_id', "=", $order_info['order_id']]]);
                    $this->cardServiceTakeDelivery($order_info['order_id']);
                }
            }
        }
        return $this->success($res);
    }
    /**
     * 订单自动发货
     * @param unknown $order_id
     */
    public function virtualOrderTakeDelivery($order_id)
    {
        $order_info = model('order')->getInfo([['order_id', '=', $order_id]], 'order_no,site_id,site_name,member_id,order_type,
        sign_time,order_status,delivery_code,create_time,name,pay_time,pay_money,mobile,is_lock,order_money');
        if (empty($order_info))
            return $this->error([], "ORDER_EMPTY");

        model('order')->startTrans();
        try {
            //订单项变为已发货
            model('order_goods')->update(['delivery_status' => 1, 'delivery_status_name' => '待使用'], [['order_id', '=', $order_id]]);
            $order_goods_info = model('order_goods')->getInfo([['order_id', '=', $order_id]], 'sku_id,sku_name,sku_image,price,num,goods_id,order_goods_id');//订单项详情

            //判断商品是否需要核销
            $goods_info = model('goods_sku')->getInfo([ ['gs.sku_id', '=', $order_goods_info['sku_id'] ] ], 'g.is_need_verify,g.verify_validity_type,g.virtual_indate,gs.verify_num', 'gs', [ ['goods g', 'g.goods_id = gs.goods_id', 'left'] ]);
            if ($goods_info['is_need_verify']) {

                switch ($goods_info['verify_validity_type']) {
                    case 0:
                        $expire_time = 0;
                        break;
                    case 1:
                        $expire_time = strtotime('+' . $goods_info['virtual_indate'] . 'days');
                        break;
                    case 2:
                        $expire_time = $goods_info['virtual_indate'];
                        break;
                }
                // dump($expire_time);
                //添加自动收货事件
                if($expire_time > 0){

                    $cron = new Cron();
                    $cron->addCron(1, 0, "订单自动收货", "CronOrderTakeDelivery", $expire_time, $order_id);

                    // 核销码临近到期时间（小时）
                    $config_model = new Config();
                    $verify_config = $config_model->getOrderVerifyConfig($order_info['site_id'], 'shop')['data']['value'];

                    $order_verify_out_time = $verify_config['order_verify_time_out'] ?? 24;
                    $time_strtime = $order_verify_out_time * 3600;
                    //核销商品临期提醒
                    $cron->addCron(1,0,"核销商品临期提醒","VerifyOrderOutTime",$expire_time-$time_strtime,$order_id);
                    //核销码过期提醒
                    $cron->addCron(1,0,"核销码过期提醒","CronVerifyCodeExpire",$expire_time,$order_id);
                }

                $count = $goods_info['verify_num'] * $order_goods_info['num'];
//                for ($i = 1; $i <= $count; $i++) {
                //创建待核销记录
                $verify_model = new VerifyModel();
                $item_array = array(
                    [
                        "img" => $order_goods_info["sku_image"],
                        "name" => $order_goods_info["sku_name"],
                        "price" => $order_goods_info["price"],
                        "num" => 1,
                        "order_goods_id" => $order_goods_info["order_goods_id"],
                        "remark_array" => [
                            ["title" => "联系人", "value" => $order_info['name'] . $order_info['mobile']]
                        ]
                    ],
                );
                $remark_array = array(
                    ["title" => '订单金额', "value" => $order_info["order_money"]],
                    ["title" => '订单编号', "value" => $order_info["order_no"]],
                    ["title" => '创建时间', "value" => time_to_date($order_info["create_time"])],
                    ["title" => '付款时间', "value" => time_to_date($order_info["pay_time"])],
                );
                $verify_content_json = $verify_model->getVerifyJson($item_array, $remark_array);
                $code_result = $verify_model->addVerify("virtualgoods", $order_info['site_id'], $order_info['site_name'], $verify_content_json, $expire_time, $count, 0, $order_info['member_id']);
                $code = $code_result["data"]["verify_code"];
                $result = $verify_model->qrcode($code, "all", "virtualgoods", $order_info['site_id'], "create");//生成二维码

                //自动收发货
                $order_data = array(
                    'virtual_code' => $code,
                );
                $res = model('order')->update($order_data, [['order_id', '=', $order_id]]);

                //生成所购买的产品
                $virtual_goods_model = new VirtualGoods();

                $goods_virtual_data = [
                    'site_id' => $order_info['site_id'],
                    'order_id' => $order_id,
                    'order_no' => $order_info['order_no'],
                    'sku_id' => $order_goods_info['sku_id'],
                    'sku_name' => $order_goods_info['sku_name'],
                    'code' => $code,
                    'member_id' => $order_info['member_id'],
                    'sku_image' => $order_goods_info['sku_image'],
                    'expire_time' => $expire_time,
                    'sold_time' => time(),
                    'goods_id' => $order_goods_info['goods_id'],
                    'verify_total_count' => $count
                ];
                $virtual_goods_model->addGoodsVirtual($goods_virtual_data);

//                }
            }

            model('order')->commit();
            return $this->success($res);

        } catch (\Exception $e) {
            model('order')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 订单自动发货
     * @param unknown $order_id
     */
    public function serviceGoodsTakeDelivery($order_id)
    {
        $order_info = model("order")->getInfo([['order_id', '=', $order_id]], 'order_no,site_id,site_name,member_id,order_type,
        sign_time,order_status,delivery_code,create_time,name,pay_time,pay_money,mobile,is_lock,order_money');
        if (empty($order_info))
            return $this->error([], "ORDER_EMPTY");

        model('order')->startTrans();
        try {
            //订单项变为已发货
            model('order_goods')->update(['delivery_status' => 1, "delivery_status_name" => "已发货"], [['order_id', "=", $order_id]]);
            $order_goods_info = model('order_goods')->getInfo([['order_id', '=', $order_id]], 'sku_id,sku_name,sku_image,price,num,goods_id,order_goods_id');//订单项详情

            //判断商品是否需要核销
            $goods_info = model('goods_sku')->getInfo([ ['gs.sku_id', '=', $order_goods_info['sku_id'] ] ], 'g.is_need_verify,g.verify_validity_type,g.virtual_indate,gs.verify_num', 'gs', [ ['goods g', 'g.goods_id = gs.goods_id', 'left'] ]);


            switch ($goods_info['verify_validity_type']) {
                case 0:
                    $expire_time = 0;
                    break;
                case 1:
                    $expire_time = strtotime('+' . $goods_info['virtual_indate'] . 'days');
                    break;
                case 2:
                    $expire_time = $goods_info['virtual_indate'];
                    break;
            }
            // dump($expire_time);
            //添加自动收货事件
            if($expire_time > 0){

                $cron = new Cron();
                $cron->addCron(1, 0, "订单自动收货", "CronOrderTakeDelivery", $expire_time, $order_id);

                // 核销码临近到期时间（小时）
                $config_model = new Config();
                $verify_config = $config_model->getOrderVerifyConfig($order_info['site_id'], 'shop')['data']['value'];

                $order_verify_out_time = $verify_config['order_verify_time_out'] ?? 24;
                $time_strtime = $order_verify_out_time * 3600;
                //核销商品临期提醒
                $cron->addCron(1,0,"核销商品临期提醒","VerifyOrderOutTime",$expire_time-$time_strtime,$order_id);
                //核销码过期提醒
                $cron->addCron(1,0,"核销码过期提醒","CronVerifyCodeExpire",$expire_time,$order_id);
            }

            $count = $order_goods_info['num'];
            //创建待核销记录
            $verify_model = new VerifyModel();
            $item_array = array(
                [
                    "img" => $order_goods_info["sku_image"],
                    "name" => $order_goods_info["sku_name"],
                    "price" => $order_goods_info["price"],
                    "num" => 1,
                    "order_goods_id" => $order_goods_info["order_goods_id"],
                    "remark_array" => [
                        ["title" => "联系人", "value" => $order_info['name'] . $order_info['mobile']]
                    ]
                ],
            );
            $remark_array = array(
                ["title" => '订单金额', "value" => $order_info["order_money"]],
                ["title" => '订单编号', "value" => $order_info["order_no"]],
                ["title" => '创建时间', "value" => time_to_date($order_info["create_time"])],
                ["title" => '付款时间', "value" => time_to_date($order_info["pay_time"])],
            );
            $verify_content_json = $verify_model->getVerifyJson($item_array, $remark_array);
            $code_result = $verify_model->addVerify("virtualgoods", $order_info['site_id'], $order_info['site_name'], $verify_content_json, $expire_time, $count, 0, $order_info['member_id']);
            $code = $code_result["data"]["verify_code"];
            $result = $verify_model->qrcode($code, "all", "virtualgoods", $order_info['site_id'], "create");//生成二维码

            //自动收发货
            $order_data = array(
                'virtual_code' => $code,
            );
            $res = model('order')->update($order_data, [['order_id', '=', $order_id]]);

            //生成所购买的产品
            $virtual_goods_model = new VirtualGoods();

            $goods_virtual_data = [
                'site_id' => $order_info['site_id'],
                'order_id' => $order_id,
                'order_no' => $order_info['order_no'],
                'sku_id' => $order_goods_info['sku_id'],
                'sku_name' => $order_goods_info['sku_name'],
                'code' => $code,
                'member_id' => $order_info['member_id'],
                'sku_image' => $order_goods_info['sku_image'],
                'expire_time' => $expire_time,
                'sold_time' => time(),
                'goods_id' => $order_goods_info['goods_id'],
                'verify_total_count' => $count
            ];
            $virtual_goods_model->addGoodsVirtual($goods_virtual_data);

            model('order')->commit();
            return $this->success($res);

        } catch (\Exception $e) {
            model('order')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 卡密商品自动发货 收货(可以分为两个函数  一个发货一个收货)
     * @param $order_id
     */
    public function virtualcardTakeDelivery($order_id)
    {
        $order_info = model("order")->getInfo([['order_id', '=', $order_id]], 'order_no,site_id,site_name,member_id,order_type,
        sign_time,order_status,delivery_code,create_time,name,pay_time,pay_money,mobile,is_lock,order_money');
        if (empty($order_info))
            return $this->error([], "ORDER_EMPTY");

        model('order')->startTrans();
        try {
            //订单项变为已发货
            model('order_goods')->update(['delivery_status' => 1, "delivery_status_name" => "已发货"], [['order_id', "=", $order_id]]);
            $order_goods_info = model('order_goods')->getInfo([['order_id', '=', $order_id]], '*');//订单项详情
            //库存处理(卡密商品支付后在扣出库存)//todo  可以再商品中设置扣除库存步骤
            $order_stock_model = new OrderStock();
            $item_goods_class = $order_goods_info[ 'goods_class' ];
            if ($item_goods_class == 3) {
                //库存变化
                $stock_result = $order_stock_model->decOrderStock($order_goods_info);
                if ($stock_result[ 'code' ] < 0) {
                    //主动退款
                    $order_refund_model = new OrderRefund();
                    $refund_result = $order_refund_model->activeRefund($order_id, $stock_result[ 'message' ], '订单退款');
                    if ($refund_result[ 'code' ] < 0) {
                        model('order')->rollback();
                        return $refund_result;
                    }
                    model('order')->commit();
                    return $stock_result;
                }
            }

            $goods_virtual_list = model('goods_virtual')->pageList([ ['order_id', '=', 0], ['sku_id', '=', $order_goods_info['sku_id'] ] ], 'id', 'id asc', 1, $order_goods_info['num']);
            if (count($goods_virtual_list['list']) != $order_goods_info['num']) {
                model('order')->rollback();
                return $this->error('','卡密库存不足');
            }
            $ids = array_column($goods_virtual_list['list'], 'id');
            model('goods_virtual')->update([
                'order_id' => $order_id,
                'order_no' => $order_info['order_no'],
                'member_id' => $order_info['member_id'],
                'sold_time' => time()
            ], [ ['id', 'in', $ids ] ]);

            // 订单自动收货
            $this->orderCommonTakeDelivery($order_id);

            model('order')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('order')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 卡项发货设置(需要增加卡项核销)
     * @param $order_id
     */
    public function cardServiceTakeDelivery($order_id)
    {
        $order_info = model("order")->getInfo([['order_id', '=', $order_id]], 'order_no,site_id,site_name,member_id,order_type,
        sign_time,order_status,delivery_code,create_time,name,pay_time,pay_money,mobile,is_lock,order_money');
        if (empty($order_info))
            return $this->error([], "ORDER_EMPTY");

        model('order')->startTrans();
        try {
            //订单项变为已发货
            model('order_goods')->update(['delivery_status' => 1, "delivery_status_name" => "已发货"], [['order_id', "=", $order_id]]);
            $order_goods_info = model('order_goods')->getInfo([['order_id', '=', $order_id]], 'sku_id,sku_name,sku_image,price,num,goods_id,order_goods_id');//订单项详情
            //创建卡项
            $member_card = new MemberCard();
            $card_data = [
                'store_id' => 0,
                'goods_id' => $order_goods_info['goods_id'],
                'num' => $order_goods_info['num'],
                'member_id' => $order_info['member_id'],
                'site_id' => $order_info['site_id'],
                'order_id' => $order_id
            ];
            $res_card = $member_card->create($card_data);
            if($res_card['code'] < 0)
            {
                model('order')->rollback();
                return $this->error('', $res_card['message']);
            }

            // 订单自动收货
            $this->orderCommonTakeDelivery($order_id);
            model('order')->commit();
            return $this->success(1);

        } catch (\Exception $e) {
            model('order')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 自动发货
     * @param $order_id
     */
    public function  orderTakeDelivery($order_id)
    {
        $order_info = model("order")->getInfo([['order_id', '=', $order_id]], 'order_no,site_id,site_name,member_id,order_type,
        sign_time,order_status,delivery_code,create_time,name,pay_time,pay_money,mobile,is_lock,order_money');
        if (empty($order_info))
            return $this->error([], "ORDER_EMPTY");

        $res = model('order_goods')->update(['delivery_status' => 1, "delivery_status_name" => "已发货"], [['order_id', "=", $order_id]]);
        return $this->success($res);
    }

    /**
     * 退款完成操作
     * @param $order_info
     */
    public function refund($order_goods_info)
    {
        $virtual_card = (new \addon\virtualcard\model\VirtualGoods())->getGoodsClass();
        if ($order_goods_info['goods_class'] != $virtual_card['id']) {
            //删除已退款订单项会员虚拟商品, 并退回商品库存
            //无需判断订单项是否需要入库
            $item_param = array(
                "sku_id" => $order_goods_info["sku_id"],
                "num" => $order_goods_info["num"],
            );
            //返还库存
            $order_stock_model = new OrderStock();
            $order_stock_model->incOrderStock($order_goods_info);
            //$goods_stock_model->incStock($item_param);
            //删除用户的这条虚拟商品
            $goods_virtual_model = new VirtualGoods();
            $goods_virtual_model->deleteGoodsVirtual([["order_id", "=", $order_goods_info["order_id"]], ["member_id", "=", $order_goods_info["member_id"]]]);
        }
    }

    /**
     * 订单详情
     * @param $order_info
     */
    public function orderDetail($order_info)
    {
        $verify_record_model = new VerifyRecord();
        $data = [
            'goods_class' => $order_info['order_goods'][0]['goods_class']
        ];
        if ($data['goods_class'] == 2) {
            $virtual_goods = model('goods_virtual')->getFirstData([["order_id", "=", $order_info["order_id"]]], '*', 'is_veirfy asc');
            if (!empty($virtual_goods)) {
                $virtual_goods['total_verify_num'] = model('goods_virtual')->getCount([ ["order_id", "=", $order_info["order_id"] ] ]);
                $virtual_goods['verify_num'] = model('goods_virtual')->getCount([ ["order_id", "=", $order_info["order_id"] ], ['is_veirfy', '=', 1] ]);
                $verify_code = model('goods_virtual')->getColumn([ ["order_id", "=", $order_info["order_id"] ] ], 'code');
                $virtual_goods['verify_record'] = [];
                if (!empty($verify_code)) {
                    $virtual_goods['verify_record'] = $verify_record_model->getVerifyRecordsViewList([ ['verify_code', 'in', $verify_code ] ], '*','verify_time desc')['data'] ?? [];
                }
                $data["virtual_goods"] = $virtual_goods;
                $data["virtual_code"] = $virtual_goods['code'];
            }
        } else if ($data['goods_class'] == 3) {
            $virtual_goods = model('goods_virtual')->getList([["order_id", "=", $order_info["order_id"]]], '*', 'id asc');
            if (!empty($virtual_goods)) {
                foreach ($virtual_goods as $key => $item) {
                    $virtual_goods[$key]['card_info'] = json_decode($item['card_info'], true);
                }
                $data["virtual_goods"] = $virtual_goods;
            }
        }
        if ($data['goods_class'] == 4) {
            $virtual_goods = model('goods_virtual')->getFirstData([["order_id", "=", $order_info["order_id"]]], '*', 'is_veirfy asc');
            if (!empty($virtual_goods)) {
                $virtual_goods['total_verify_num'] = model('goods_virtual')->getCount([ ["order_id", "=", $order_info["order_id"] ] ]);
                $virtual_goods['verify_num'] = model('goods_virtual')->getCount([ ["order_id", "=", $order_info["order_id"] ], ['is_veirfy', '=', 1] ]);
                $verify_code = model('goods_virtual')->getColumn([ ["order_id", "=", $order_info["order_id"] ] ], 'code');
                $virtual_goods['verify_record'] = [];
                if (!empty($verify_code)) {
                    $virtual_goods['verify_record'] = $verify_record_model->getVerifyRecordsViewList([ ['verify_code', 'in', $verify_code ] ], '*','verify_time desc')['data'] ?? [];
                }
                $data["virtual_goods"] = $virtual_goods;
                $data["virtual_code"] = $virtual_goods['code'];
            }
        }
        return $data;
    }

    /**
     * 虚拟发货
     * @param $params
     * @return array
     */
    public function virtualDelivery($params){
        $order_id = $params['order_id'] ?? 0;
        //todo  核验订单是否处于锁定
        $local_result = $this->verifyOrderLock($order_id);
        if ($local_result[ 'code' ] < 0)
            return $local_result;

        $site_id = $params['site_id'] ?? 0;
        $condition = [['order_id', "=", $params['order_id']]];
        if($site_id > 0){
            $condition[] = ['site_id', '=',$site_id ];
        }
        $order_info = $this->getOrderInfo($condition)['data'] ?? [];
        if(empty($order_info))
            return $this->error([], '找不到订单');

        $data = array(
            'order_status' => self::ORDER_DELIVERY,
            'order_status_name' => $this->order_status[self::ORDER_DELIVERY]["name"],
            'order_status_action' => json_encode($this->order_status[self::ORDER_DELIVERY], JSON_UNESCAPED_UNICODE),
        );
        $res = model('order')->update($data, $condition);

        $goods_id = model('order_goods')->getValue([['order_id', '=', $order_id]], 'goods_id');
        //判断商品是否需要核销
        $goods_info = model('goods')->getInfo([['goods_id', '=', $goods_id]], 'virtual_receive_type');
        //增加卡项商品的自动发货

        if(!empty($goods_info)){
            $virtual_receive_type = $goods_info['virtual_receive_type'];
            if($virtual_receive_type == 'auto_receive'){
                $result = $this->virtualTakeDelivery($params);
                if($result['code'] < 0){
                    return $result;
                }
            }
        }
        return $this->success();
    }

    public function virtualTakeDelivery($params){
        $order_id = $params['order_id'] ?? 0;
        $site_id = $params['site_id'] ?? 0;
        $member_id = $params['member_id'] ?? 0;
        $condition = [['order_id', "=", $order_id]];
        if($site_id > 0){
            $condition[] = ['site_id', '=',$site_id ];
        }
        if($member_id > 0){
            $condition[] = ['member_id', '=',$member_id ];
        }

        $order_info = $this->getOrderInfo($condition)['data'] ?? [];
        if(empty($order_info))
            return $this->error([], '找不到订单');
        //虚拟产品收货
        $result = $this->orderCommonTakeDelivery($order_id);
        if($result['code'] < 0){
            return $result;
        }
        return $this->success();
    }


    /**
     * to发送
     * @param $params
     */
    public function toSend($params){
        $order_id = $params['order_id'];
        $goods_id = model('order_goods')->getValue([['order_id', '=', $order_id]], 'goods_id');
        //判断商品是否需要核销
        $goods_info = model('goods')->getInfo([['goods_id', '=', $goods_id]], 'goods_class,is_need_verify,virtual_deliver_type');
        $order_info = model('order')->getInfo([['order_id', '=', $order_id]]);
        if ($goods_info['goods_class'] == 2) {
            switch($goods_info['virtual_deliver_type']){
                case 'auto_deliver'://自动发货
                    $result = $this->virtualDelivery($order_info);//
                    break;
                case 'artificial_deliver'://手动发货

                    break;
                case 'verify'://到店核销

                    $order_status_item = $this->order_status[self::ORDER_WAIT_VERIFY];
                    $data = array(
                        'order_status' => self::ORDER_WAIT_VERIFY,
                        'order_status_name' => $order_status_item["name"],
                        'order_status_action' => json_encode($order_status_item, JSON_UNESCAPED_UNICODE),
                    );
                    $res = model('order')->update($data, [['order_id', "=", $order_info['order_id']]]);

                    if($goods_info['is_need_verify']){
                        //虚拟产品发货
                        $this->virtualOrderTakeDelivery($order_info['order_id']);
                    }else{
                        //虚拟产品收货
                        $this->orderCommonTakeDelivery($order_info['order_id']);
                    }
                    break;
            }
        } elseif ($goods_info['goods_class'] == 3) {
            $order_status_item = $this->order_status[self::ORDER_DELIVERY];
            $order_status_item['action'] = [];
            $order_status_item['member_action'] = [];
            $data = array(
                'order_status' => self::ORDER_DELIVERY,
                'order_status_name' => $this->order_status[self::ORDER_DELIVERY]["name"],
                'order_status_action' => json_encode($order_status_item, JSON_UNESCAPED_UNICODE),
            );
            $res = model('order')->update($data, [['order_id', "=", $order_info['order_id']]]);
            // 卡密商品发货
            $result = $this->virtualcardTakeDelivery($order_info['order_id']);
            if($result['code'] < 0){
                return $result;
            }
        }elseif ($goods_info['goods_class'] == 4) {
            $order_status_item = $this->order_status[self::ORDER_DELIVERY];
            $order_status_item['action'] = [];
            $order_status_item['member_action'] = [];
            $data = array(
                'order_status' => self::ORDER_DELIVERY,
                'order_status_name' => $this->order_status[self::ORDER_DELIVERY]["name"],
                'order_status_action' => json_encode($order_status_item, JSON_UNESCAPED_UNICODE),
            );
            $res = model('order')->update($data, [['order_id', "=", $order_info['order_id']]]);
            $result = $this->serviceGoodsTakeDelivery($order_info['order_id']);
            if($result['code'] < 0){
                return $result;
            }
        }elseif ($goods_info['goods_class'] == 5) {
            $result = $this->cardServiceTakeDelivery($order_info);//
            if($result['code'] < 0){
                return $result;
            }
        }
        return $this->success();
    }
}