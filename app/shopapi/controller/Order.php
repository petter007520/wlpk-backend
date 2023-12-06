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

use app\model\express\ExpressDeliver;
use app\model\express\ExpressPackage;
use app\model\order\Config as ConfigModel;
use app\model\order\Order as OrderModel;
use app\model\order\OrderCommon as OrderCommonModel;
use think\facade\Config;
use think\facade\Db;

/**
 * 订单
 * Class Order
 * @package app\shop\controller
 */
class Order extends BaseApi
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
     * 获取订单查询条件
     */
    public function condition()
    {
        $data = [];

        $order_common_model = new OrderCommonModel();

        $order_type_list = $order_common_model->getOrderTypeStatusList();
        if (array_key_exists('all', $order_type_list)) {
            array_unshift($order_type_list, $order_type_list[ 'all' ]);
            unset($order_type_list[ 'all' ]);
        }
        $data[ 'order_type_list' ] = $order_type_list;
        $data[ 'order_status_list' ] = $order_type_list[ 1 ][ 'status' ];//订单状态

        //订单来源 (支持端口)
        $order_from = Config::get("app_type");
        $data[ 'order_from_list' ] = $order_from;

        $pay_type = $order_common_model->getPayType();
        $data[ 'pay_type_list' ] = $pay_type;

        //营销活动类型
        $order_promotion_type = event('OrderPromotionType');
        $data[ 'promotion_type' ] = $order_promotion_type;
        $data[ 'http_type' ] = get_http_type();

        return $this->response($this->success($data));
    }

    /**
     * 订单列表
     */
    public function lists()
    {
        $order_status = isset($this->params[ 'order_status' ]) ? $this->params[ 'order_status' ] : '';//订单状态
        $order_name = isset($this->params[ 'order_name' ]) ? $this->params[ 'order_name' ] : '';
        $pay_type = isset($this->params[ 'pay_type' ]) ? $this->params[ 'pay_type' ] : '';
        $order_from = isset($this->params[ 'order_from' ]) ? $this->params[ 'order_from' ] : '';
        $start_time = isset($this->params[ 'start_time' ]) ? $this->params[ 'start_time' ] : '';
        $end_time = isset($this->params[ 'end_time' ]) ? $this->params[ 'end_time' ] : '';

        $search_text = isset($this->params[ 'search' ]) ? $this->params[ 'search' ] : '';
        $promotion_type = isset($this->params[ 'promotion_type' ]) ? $this->params[ 'promotion_type' ] : '';//订单类型
        $order_type = isset($this->params[ 'order_type' ]) ? $this->params[ 'order_type' ] : 'all';//营销类型
        $settlement_state = isset($this->params[ 'settlement_state' ]) ? $this->params[ 'settlement_state' ] : '';//结算状态
        $page_index = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $alias = 'o';
        $join = null;
        $condition = [
            [ "o.site_id", "=", $this->site_id ],
            [ 'o.is_delete', '=', 0 ]
        ];
        //订单状态
        if ($order_status != "") {
            if ($order_status != 'refunding') {
                if ($order_status == 0) {
                    $condition[] = [ "o.order_status", "=", $order_status ];
                    $condition[] = [ 'o.order_scene', '=', 'online' ];
                } else {
                    $condition[] = [ "o.order_status", "=", $order_status ];
                }
            } else {
                $join = [
                    [
                        'order_goods og',
                        'og.order_id = o.order_id',
                        'left'
                    ]
                ];
                $condition[] = [ "og.refund_status", "not in", [ 0, 3 ] ];
            }
        } else {
            $condition[] = [ '', 'exp', Db::raw("o.order_scene = 'online' OR (o.order_scene = 'cashier' AND o.pay_status = 1)") ];
        }
        //订单内容 模糊查询
        if ($order_name != "") {
            $condition[] = [ "o.order_name", 'like', "%$order_name%" ];
        }
        //订单来源
        if ($order_from != "") {
            $condition[] = [ "o.order_from", "=", $order_from ];
        }
        //订单支付
        if ($pay_type != "") {
            $condition[] = [ "o.pay_type", "=", $pay_type ];
        }
        //订单类型
        if ($order_type != 'all') {
            $condition[] = [ "o.order_type", "=", $order_type ];
        }
        //结算状态
        if ($settlement_state == 1) {
            $condition[] = [ "o.is_settlement", "=", '1' ];
        } elseif ($settlement_state == 2) {
            $condition[] = [ "o.is_settlement", "=", '0' ];
        }
        //营销类型
        if ($promotion_type != "") {
            if ($promotion_type == 'empty') {
                $condition[] = [ "o.promotion_type", "=", '' ];
            } else {
                $condition[] = [ "o.promotion_type", "=", $promotion_type ];
            }
        }
        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ "o.create_time", ">=", date_to_time($start_time . ' 00:00:00') ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ "o.create_time", "<=", date_to_time($end_time . ' 23:59:59') ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'o.create_time', 'between', [ date_to_time($start_time . ' 00:00:00'), date_to_time($end_time . ' 23:59:59') ] ];
        }

        //订单内容
        if ($search_text != '') {
            $condition[] = [ 'o.order_no|o.out_trade_no|o.order_name|o.name|o.mobile', 'like', '%' . $search_text . '%' ];
        }

        $order_common_model = new OrderCommonModel();
        $field = 'o.order_id, o.order_no, o.order_type, o.order_type_name, o.order_status, o.order_status_name, o.order_status_action, o.pay_type_name, o.name, o.mobile, o.address, o.full_address, o.order_money, o.create_time, o.remark, o.promotion_type_name, o.promotion_status_name, o.buyer_message, o.is_enable_refund, o.promotion_type, o.order_scene,o.buyer_ask_delivery_time';
        $list = $order_common_model->getOrderPageList($condition, $page_index, $page_size, "o.create_time desc", $field, $alias, $join);
        foreach ($list[ 'data' ][ 'list' ] as $k => $v) {
            // 2：自提提货，3：本地配送外卖
            if ($v[ 'order_type' ] == 2 || $v[ 'order_type' ] == 3) {
                if ($list[ 'data' ][ 'list' ][ $k ][ 'buyer_ask_delivery_time' ] == 0) {
                    $list[ 'data' ][ 'list' ][ $k ][ 'buyer_ask_delivery_time_str' ] = '立即送达';
                } elseif (strpos($list[ 'data' ][ 'list' ][ $k ][ 'buyer_ask_delivery_time' ], '-') !== false) {
                    $list[ 'data' ][ 'list' ][ $k ][ 'buyer_ask_delivery_time_str' ] = $list[ 'data' ][ 'list' ][ $k ][ 'buyer_ask_delivery_time' ];
                } else {
                    $list[ 'data' ][ 'list' ][ $k ][ 'buyer_ask_delivery_time_str' ] = date("H:i:s", $list[ 'data' ][ 'list' ][ $k ][ 'buyer_ask_delivery_time' ]);
                }
            }
        }
        return $this->response($list);
    }

    /**
     * 快递订单详情
     */
    public function detail()
    {
        $order_id = isset($this->params[ 'order_id' ]) ? $this->params[ 'order_id' ] : 0;

        $order_common_model = new OrderCommonModel();
        $order_detail_result = $order_common_model->getOrderDetail($order_id);
        if (empty($order_detail_result[ 'data' ])) {
            return $this->response($this->error('查询不到此订单信息!'));
        }
        if ($order_detail_result[ 'data' ][ 'site_id' ] != $this->site_id) {
            return $this->response($this->error('查询不到此订单信息!'));
        }

        $order_log_condition = array (
            [ 'order_id', '=', $order_id ]
        );
        $order_log_count = $order_common_model->getOrderLogCount($order_log_condition);

        if ($order_detail_result[ 'data' ][ 'buyer_ask_delivery_time' ] == 0) {
            $order_detail_result[ 'data' ][ 'buyer_ask_delivery_time_str' ] = '立即送达';
        } elseif (strpos($order_detail_result[ 'data' ][ 'buyer_ask_delivery_time' ], '-') !== false) {
            $order_detail_result[ 'data' ][ 'buyer_ask_delivery_time_str' ] = $order_detail_result[ 'data' ][ 'buyer_ask_delivery_time' ];
        } else {
            $order_detail_result[ 'data' ][ 'buyer_ask_delivery_time_str' ] = date("H:i:s", $order_detail_result[ 'data' ][ 'buyer_ask_delivery_time' ]);
        }
        $order_detail_result[ 'data' ][ 'log_count' ] = $order_log_count[ 'data' ];

        return $this->response($order_detail_result);
    }

    /**
     * 订单日志列表
     * @return false|string
     */
    public function log()
    {
        $order_id = isset($this->params[ 'order_id' ]) ? $this->params[ 'order_id' ] : 0;
        $order_common_model = new OrderCommonModel();

        $order_log_condition = array (
            [ 'order_id', '=', $order_id ]
        );
        $order_log_list = $order_common_model->getOrderLogList($order_log_condition);
        return $this->response($order_log_list);
    }

    /**
     * 订单关闭
     * @return mixed
     */
    public function close()
    {
        $order_id = isset($this->params[ 'order_id' ]) ? $this->params[ 'order_id' ] : 0;
        $order_common_model = new OrderCommonModel();
        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action_way' => 2
        ];
        $result = $order_common_model->orderClose($order_id, $log_data);
        return $this->response($result);
    }

    /**
     * 订单调价
     * @return mixed
     */
    public function adjustPrice()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $adjust_money = isset($this->params[ 'adjust_money' ]) ? $this->params[ 'adjust_money' ] : 0;
        $delivery_money = isset($this->params[ 'delivery_money' ]) ? $this->params[ 'delivery_money' ] : 0;
        $order_common_model = new OrderCommonModel();
        $result = $order_common_model->orderAdjustMoney($order_id, $adjust_money, $delivery_money);
        return $this->response($result);
    }

    /**
     * 订单发货
     * @return mixed
     */
    public function delivery()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $order_goods_ids = isset($this->params[ 'order_goods_ids' ]) ? $this->params[ 'order_goods_ids' ] : '';
        $express_company_id = isset($this->params[ 'express_company_id' ]) ? $this->params[ 'express_company_id' ] : 0;
        $delivery_no = isset($this->params[ 'delivery_no' ]) ? $this->params[ 'delivery_no' ] : '';
        $delivery_type = isset($this->params[ 'delivery_type' ]) ? $this->params[ 'delivery_type' ] : 0;
        $order_model = new OrderModel();
        $data = array (
            "type" => isset($this->params[ 'type' ]) ? $this->params[ 'type' ] : 'manual',//发货方式（手动发货、电子面单）
            "order_goods_ids" => $order_goods_ids,
            "express_company_id" => $express_company_id,
            "delivery_no" => $delivery_no,
            "order_id" => $order_id,
            "delivery_type" => $delivery_type,
            "site_id" => $this->site_id,
            "template_id" => isset($this->params[ 'template_id' ]) ? $this->params[ 'template_id' ] : '0'//电子面单模板id
        );
        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action' => '商家对订单进行了发货',
            'action_way' => 2
        ];
        $result = $order_model->orderGoodsDelivery($data, 1, $log_data);
        return $this->response($result);
    }

    /**
     * 获取订单项列表
     */
    public function getOrderGoodsList()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $delivery_status = $this->params[ 'delivery_status' ] ?? '';

        $order_common_model = new OrderCommonModel();
        $condition = array (
            [ "order_id", "=", $order_id ],
            [ "site_id", "=", $this->site_id ],
            [ "refund_status", "<>", 3 ],
        );
        if ($delivery_status != '') {
            $condition[] = [ "delivery_status", "=", $delivery_status ];
        }
        $field = "order_goods_id, order_id, site_id, sku_name, sku_image, sku_no, is_virtual, price, cost_price, num, goods_money, cost_money, delivery_status, delivery_no, goods_id, delivery_status_name,refund_status,refund_status_name";
        $result = $order_common_model->getOrderGoodsList($condition, $field, '', null, "");
        return $this->response($result);
    }

    /**
     * 订单修改收货地址
     * @return mixed
     */
    public function editAddress()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;

        $order_model = new OrderModel();
        $province_id = isset($this->params[ 'province_id' ]) ? $this->params[ 'province_id' ] : '';
        $city_id = isset($this->params[ 'city_id' ]) ? $this->params[ 'city_id' ] : '';
        $district_id = isset($this->params[ 'district_id' ]) ? $this->params[ 'district_id' ] : '';
        $community_id = isset($this->params[ 'community_id' ]) ? $this->params[ 'community_id' ] : '';
        $address = isset($this->params[ 'address' ]) ? $this->params[ 'address' ] : '';
        $full_address = isset($this->params[ 'full_address' ]) ? $this->params[ 'full_address' ] : '';
        $longitude = isset($this->params[ 'longitude' ]) ? $this->params[ 'longitude' ] : '';
        $latitude = isset($this->params[ 'latitude' ]) ? $this->params[ 'latitude' ] : '';
        $mobile = isset($this->params[ 'mobile' ]) ? $this->params[ 'mobile' ] : '';
        $telephone = isset($this->params[ 'telephone' ]) ? $this->params[ 'telephone' ] : '';
        $name = isset($this->params[ 'name' ]) ? $this->params[ 'name' ] : '';
        $data = array (
            "province_id" => $province_id,
            "city_id" => $city_id,
            "district_id" => $district_id,
            "community_id" => $community_id,
            "address" => $address,
            "full_address" => $full_address,
            "longitude" => $longitude,
            "latitude" => $latitude,
            "mobile" => $mobile,
            "telephone" => $telephone,
            "name" => $name,
        );
        $condition = array (
            [ "order_id", "=", $order_id ],
            [ "site_id", "=", $this->site_id ]
        );
        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action' => '商家修改了收货地址',
            'action_way' => '2',
            'order_id' => $order_id
        ];
        $result = $order_model->orderAddressUpdate($data, $condition, $log_data);
        return $this->response($result);
    }

    /**
     * 获取订单信息
     */
    public function getOrderInfo()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;

        $order_common_model = new OrderCommonModel();
        $condition = array (
            [ "order_id", "=", $order_id ],
            [ "site_id", "=", $this->site_id ],
        );
        $result = $order_common_model->getOrderInfo($condition);
        // 获取配送员信息
        $deliver_model = new ExpressDeliver();
        $deliver_list = $deliver_model->getDeliverLists([ [ 'site_id', '=', $this->site_id ] ], 'deliver_id,deliver_name,deliver_mobile');
        if (!empty($deliver_list[ 'data' ])) {
            $result[ 'data' ][ 'deliver_list' ] = $deliver_list[ 'data' ];
        }
        return $this->response($result);
    }

    /**
     * 获取订单 订单项内容
     */
    public function getOrderDetail()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $order_common_model = new OrderCommonModel();
        $result = $order_common_model->getOrderDetail($order_id);
        return $this->response($result);
    }

    /**
     * 卖家备注
     */
    public function orderRemark()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $remark = $this->params[ 'remark' ] ?? '';

        $order_common_model = new OrderCommonModel();
        $condition = array (
            [ "order_id", "=", $order_id ],
            [ "site_id", "=", $this->site_id ],
        );
        $data = array (
            "remark" => $remark
        );
        $log_data = [
            'action' => '商家备注了订单，备注内容：' . $remark,
            'action_way' => 2,
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'order_id' => $order_id
        ];
        $result = $order_common_model->orderUpdate($data, $condition, $log_data);
        return $this->response($result);
    }

    /**
     * 延长收货时间
     */
    public function extendTakeDelivery()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $condition = array (
            [ 'order_id', '=', $order_id ],
            [ 'site_id', '=', $this->site_id ],
        );
        $order_common_model = new OrderCommonModel();
        $log_data = [
            'uid' => $this->uid,
            'username' => $this->user_info[ 'username' ],
            'module' => 'shop'
        ];
        $result = $order_common_model->extendTakeDelivery($condition, $log_data);
        return $this->response($result);
    }

    /**
     * 线下支付
     */
    public function offlinePay()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $order_common_model = new OrderCommonModel();
        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action_way' => 2
        ];
        $order_detail_result = $order_common_model->orderOfflinePay($order_id, $log_data);
        return $this->response($order_detail_result);
    }


    /**
     * 订单列表（发票）
     */
    public function invoicelist()
    {
        $order_common_model = new OrderCommonModel();
        $page_index = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $invoice_status = input("invoice_status", '');
        $order_type = $this->params[ 'order_type' ] ?? '';//营销类型
        $order_status = $this->params[ 'order_status' ] ?? '';
        $start_time = $this->params[ 'start_time' ] ?? '';
        $end_time = $this->params[ 'end_time' ] ?? '';
        //订单编号
        $order_no = $this->params[ 'search_text' ] ?? '';
        $alias = 'o';
        $join = null;
        $condition = [
            [ "o.site_id", "=", $this->site_id ],
            [ 'o.is_invoice', '=', 1 ]
        ];
        //发票状态
        if ($invoice_status != '') {
            $condition[] = [ 'o.invoice_status', '=', $invoice_status ];
        }
        if ($order_no) {
            $condition[] = [ "o.order_no", "like", "%" . $order_no . "%" ];
        }
        //订单状态
        if ($order_status != "") {
            if ($order_status != 'refunding') {
                $condition[] = [ "o.order_status", "=", $order_status ];
            } else {
                $join = [
                    [
                        'order_goods og',
                        'og.order_id = o.order_id',
                        'left'
                    ]
                ];
                $condition[] = [ "og.refund_status", "not in", [ 0, 3 ] ];
            }
        }

        //订单类型
        if (!empty($order_type)) {
            $condition[] = [ "o.order_type", "=", $order_type ];
        }

        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ "o.create_time", ">=", date_to_time($start_time . ' 00:00:00') ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ "o.create_time", "<=", date_to_time($end_time . ' 23:59:59') ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'o.create_time', 'between', [ date_to_time($start_time . ' 00:00:00'), date_to_time($end_time . ' 23:59:59') ] ];
        }
        $list = $order_common_model->getOrderPageList($condition, $page_index, $page_size, "o.create_time desc", 'o.*', $alias, $join);
        return $this->response($list);
    }

    /**
     * 订单列表（发票编辑）
     */
    public function invoiceEdit()
    {
        //接收数据
        $order_id = $this->params[ 'order_id' ] ?? '';
        $invoice_status = $this->params[ 'invoice_status' ] ?? '';
        $invoice_code = $this->params[ 'invoice_code' ] ?? '';
        $invoice_remark = $this->params[ 'invoice_remark' ] ?? '';
        $condition = [
            [ "order_id", "=", $order_id ]
        ];
        $data[ 'invoice_status' ] = $invoice_status;
        $data[ 'invoice_code' ] = $invoice_code;
        $data[ 'invoice_remark' ] = $invoice_remark;
        $data[ 'invoice_time' ] = time();
        $order_common_model = new OrderCommonModel();
        $res = $order_common_model->orderUpdate($data, $condition);
        return $this->response($res);

    }

    /**
     * 订单包裹信息
     */
    public function package()
    {
        $order_id = $this->params[ 'order_id' ] ?? '';//订单id
        $express_package_model = new ExpressPackage();
        $condition = array (
            [ "order_id", "=", $order_id ],
        );
        $order_model = new OrderCommonModel();
        $order_info = $order_model->getOrderInfo($condition)[ 'data' ] ?? [];
        $condition[] = [ 'site_id', '=', $order_info[ 'site_id' ] ];
        $package = $express_package_model->package($condition, $order_info[ 'mobile' ]);
        if ($package) {
            $result = [
                'package' => $package,
                'order_status' => $order_info[ 'order_status' ]
            ];
            return $this->response($this->success($result));
        } else {
            return $this->response($this->error());
        }
    }

    /**
     * 修改单个订单的物流信息（电子面单的除外）
     */
    public function editOrderDelivery()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;// 订单id
        $package_id = $this->params[ 'package_id' ] ?? 0;// 包裹id
        $delivery_type = $this->params[ 'delivery_type' ] ?? 0;// 是否需要物流
        $express_company_id = $this->params[ 'express_company_id' ] ?? '';// 物流公司
        $delivery_no = $this->params[ 'delivery_no' ] ?? '';// 物流单号

        $delivery_json = array (
            'site_id' => $this->site_id,
            'order_id' => $order_id,
            'package_id' => $package_id,
            'delivery_type' => $delivery_type,
            'express_company_id' => $express_company_id,
            'delivery_no' => $delivery_no
        );
        $express_package_model = new ExpressPackage();
        $res = $express_package_model->editOrderExpressDeliveryPackage($delivery_json);
        return $this->response($res);
    }

    /**
     * 确认收货
     */
    public function takeDelivery()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;// 订单id
        $order_model = new OrderCommonModel();
        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action_way' => 2
        ];
        $result = $order_model->orderCommonTakeDelivery($order_id, $log_data);
        return $this->response($result);
    }

    /**
     * 交易配置
     */
    public function config()
    {
        $config_model = new ConfigModel();
        //订单事件时间设置
        $order_event_time_config = $config_model->getOrderEventTimeConfig($this->site_id, $this->app_module);
        $order_event_time_config[ 'data' ][ 'value' ][ 'invoice_content' ] = explode(',', $order_event_time_config[ 'data' ][ 'value' ][ 'invoice_content' ]);
        $order_event_time_config[ 'data' ][ 'value' ][ 'invoice_type' ] = explode(',', $order_event_time_config[ 'data' ][ 'value' ][ 'invoice_type' ]);
        //订单评价设置
        $order_evaluate_config = $config_model->getOrderEvaluateConfig($this->site_id, $this->app_module);
        //余额支付配置
        $balance_config = $config_model->getBalanceConfig($this->site_id, $this->app_module);
        $data[ 'balance_config' ] = $balance_config[ 'data' ][ 'value' ];
        $data[ 'order_event_time_config' ] = $order_event_time_config[ 'data' ][ 'value' ];
        $data[ 'order_evaluate_config' ] = $order_evaluate_config[ 'data' ][ 'value' ];
        return $this->response($this->success($data));
    }

    public function setConfig()
    {
        $config_model = new ConfigModel();
        //订单事件时间设置数据
        $order_event_time_config_data = [
            'auto_close' => $this->params[ 'order_auto_close_time' ] ?? 0,//订单未付款自动关闭时间 数字 单位(分钟)
            'auto_take_delivery' => $this->params[ 'order_auto_take_delivery_time' ] ?? 0,//订单发货后自动收货时间 数字 单位(天)
            'auto_complete' => $this->params[ 'order_auto_complete_time' ] ?? 0,//订单收货后自动完成时间 数字 单位(天)
            'after_sales_time' => $this->params[ 'after_sales_time' ] ?? 0,//订单完成后可维权时间 数字 单位(天)
            'invoice_status' => $this->params[ 'invoice_status' ] ?? 0,
            'invoice_rate' => $this->params[ 'invoice_rate' ] ?? 0,
            'invoice_content' => implode(',', explode(',', isset($this->params[ 'invoice_content' ]) && !empty($this->params[ 'invoice_content' ]) ? $this->params[ 'invoice_content' ] : '')),
            'invoice_money' => $this->params[ 'invoice_money' ] ?? 0,
            'invoice_type' => implode(',', explode(',', isset($this->params[ 'invoice_type' ]) && !empty($this->params[ 'invoice_type' ]) ? $this->params[ 'invoice_type' ] : ''))
        ];
        //订单评价设置数据
        $order_evaluate_config_data = [
            'evaluate_status' => $this->params[ 'evaluate_status' ] ?? 0,//订单评价状态（0关闭 1开启）
            'evaluate_show' => $this->params[ 'evaluate_show' ] ?? 0,//显示评价（0关闭 1开启）
            'evaluate_audit' => $this->params[ 'evaluate_audit' ] ?? 0,//评价审核状态（0关闭 1开启）
        ];
        $res = $config_model->setOrderEventTimeConfig($order_event_time_config_data, $this->site_id, $this->app_module);
        $config_model->setOrderEvaluateConfig($order_evaluate_config_data, $this->site_id, $this->app_module);
        return $this->response($res);
    }
}