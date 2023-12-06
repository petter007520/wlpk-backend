<?php
/**
 * Index.php
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 * @author : niuteam
 * @date : 2022.8.8
 * @version : v5.0.0.1
 */

namespace app\api\controller;

use app\model\express\ExpressPackage;
use app\model\order\Order as OrderModel;
use app\model\order\OrderCommon as OrderCommonModel;
use app\model\order\OrderRefund as OrderRefundModel;
use app\model\order\Config as ConfigModel;
use app\model\order\VirtualOrder;
use think\facade\Db;

class Order extends BaseApi
{

    /**
     * 详情信息
     */
    public function detail()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_common_model = new OrderCommonModel();
        $order_id = isset($this->params[ 'order_id' ]) ? $this->params[ 'order_id' ] : 0;
        $result = $order_common_model->getMemberOrderDetail($order_id, $this->member_id, $this->site_id);

        //获取未付款订单自动关闭时间 字段'auto_close'
        $config_model = new ConfigModel;
        $order_event_time_config = $config_model->getOrderEventTimeConfig($this->site_id, 'shop');
        $auto_close = $order_event_time_config[ 'data' ][ 'value' ][ 'auto_close' ] * 60 ?? [];
        $result[ 'data' ][ 'auto_close' ] = $auto_close;

        return $this->response($result);
    }

    /**
     * 列表信息
     */
    public function lists()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_common_model = new OrderCommonModel();
        $search_text = isset($this->params[ 'searchText' ]) ? $this->params[ 'searchText' ] : "";
        $condition = array (
            [ "o.member_id", "=", $this->member_id ],
            [ "o.site_id", "=", $this->site_id ],
            [ "o.is_delete", '=', 0 ]
        );
        $order_status = isset($this->params[ 'order_status' ]) ? $this->params[ 'order_status' ] : 'all';
        switch ( $order_status ) {
            case "waitpay"://待付款
                $condition[] = [ "o.order_status", "=", 0 ];
                $condition[] = [ 'o.order_scene', '=', 'online'];
                break;
            case "waitsend"://待发货
                $condition[] = [ "o.order_status", "=", 1 ];
                break;
            case "waitconfirm"://待收货
                $condition[] = [ "o.order_status", "in", [ 2, 3 ] ];
                $condition[] = [ "o.order_type", "<>", 4 ];
                break;
            //todo  这儿改了之后要考虑旧数据的问题
            case 'wait_use'://待使用
                $condition[] = [ "o.order_status", "in", [ 3, 11 ] ];
                $condition[] = [ "o.order_type", "=", 4 ];
                break;
            case "waitrate"://待评价
                $condition[] = [ "o.order_status", "in", [ 4, 10 ] ];
                $condition[] = [ "o.is_evaluate", "=", 1 ];
                $condition[] = [ "o.evaluate_status", "=", 0 ];
                break;
            default:
                $condition[] = ['', 'exp', Db::raw("o.order_scene = 'online' OR (o.order_scene = 'cashier' AND o.pay_status = 1)") ];
        }
//		if (c !== "all") {
//			$condition[] = [ "order_status", "=", $order_status ];
//		}

        //获取未付款订单自动关闭时间 字段'auto_close'
        $config_model = new ConfigModel;
        $order_event_time_config = $config_model->getOrderEventTimeConfig($this->site_id, 'shop');

        $page_index = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $order_id = isset($this->params[ 'order_id' ]) ? $this->params[ 'order_id' ] : 0;
        $search_text = isset($this->params[ 'searchText' ]) ? $this->params[ 'searchText' ] : "";
        if ($order_id) {
            $condition[] = [ "o.order_id", "=", $order_id ];
        }
        $join = [];
        $alias = "o";
        if ($search_text) {
            $condition[] = [ 'og.sku_name|o.order_no', 'like', '%' . $search_text . '%' ];
            $join = [
                [ 'order_goods og', 'og.order_id = o.order_id', 'left' ]
            ];
        }

        $res = $order_common_model->getMemberOrderPageList($condition, $page_index, $page_size, "o.create_time desc", "*", $alias, $join);

        $auto_close = $order_event_time_config[ 'data' ][ 'value' ][ 'auto_close' ] * 60 ?? [];
        $res[ 'data' ][ 'auto_close' ] = $auto_close;
        return $this->response($res);
    }

    /**
     * 订单评价基础信息
     */
    public function evluateinfo()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_id = isset($this->params[ 'order_id' ]) ? $this->params[ 'order_id' ] : 0;
        if (empty($order_id)) {
            return $this->response($this->error('', 'REQUEST_ORDER_ID'));
        }

        $order_common_model = new OrderCommonModel();
        $order_info = $order_common_model->getOrderInfo([
            [ 'order_id', '=', $order_id ],
            [ 'member_id', '=', $token[ 'data' ][ 'member_id' ] ],
            [ 'order_status', 'in', ( '4,10' ) ],
            [ 'is_evaluate', '=', 1 ],
        ], 'evaluate_status,evaluate_status_name');

        $res = $order_info[ 'data' ];
        if (!empty($res)) {
            if ($res[ 'evaluate_status' ] == 2) {
                return $this->response($this->error('', '该订单已评价'));
            } else {
                $condition = [
                    [ 'order_id', '=', $order_id ],
                    [ 'member_id', '=', $token[ 'data' ][ 'member_id' ] ],
                    [ 'refund_status', '<>', 3 ],
                ];
                $list = $order_common_model->getOrderGoodsList($condition, 'order_goods_id,order_id,order_no,site_id,member_id,goods_id,sku_id,sku_name,sku_image,price,num');
                $list = $list[ 'data' ];
                $res[ 'list' ] = $list;
                return $this->response($this->success($res));
            }
        } else {
            return $this->response($this->error('', '没有找到该订单'));
        }

    }

    /**
     * 订单收货(收到所有货物)
     */
    public function takeDelivery()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_id = isset($this->params[ 'order_id' ]) ? $this->params[ 'order_id' ] : 0;
        if (empty($order_id)) {
            return $this->response($this->error('', 'REQUEST_ORDER_ID'));
        }
        $order_model = new OrderCommonModel();
        $log_data = [
            'uid' => $this->member_id,
            'action_way' => 1
        ];
        $result = $order_model->orderCommonTakeDelivery($order_id, $log_data);
        return $this->response($result);
    }

    /**
     * 关闭订单
     */
    public function close()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_id = isset($this->params[ 'order_id' ]) ? $this->params[ 'order_id' ] : 0;
        if (empty($order_id)) {
            return $this->response($this->error('', 'REQUEST_ORDER_ID'));
        }

        $order_model = new OrderModel();

        $log_data = [
            'uid' => $this->member_id,
            'action_way' => 1
        ];

        $result = $order_model->orderClose($order_id, $log_data);
        return $this->response($result);
    }

    /**
     * 获取订单数量
     */
    public function num()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        if (empty($this->params[ 'order_status' ])) {
            return $this->response($this->error('', 'REQUEST_ORDER_STATUS'));
        }

        $order_common_model = new OrderCommonModel();
        $order_refund_model = new OrderRefundModel();

        $data = [];
        foreach (explode(',', $this->params[ 'order_status' ]) as $order_status) {
            $condition = array (
                [ "member_id", "=", $this->member_id ],
                [ "order_scene", "=", "online" ]
            );
            switch ( $order_status ) {
                case "waitpay"://待付款
                    $condition[] = [ "order_status", "=", 0 ];
                    break;
                case "waitsend"://待发货
                    $condition[] = [ "order_status", "=", 1 ];
                    break;
                case "waitconfirm"://待收货
                    $condition[] = [ "order_status", "in", [ 2, 3 ] ];
                    $condition[] = [ "order_type", "<>", 4 ];
                    break;
                case 'wait_use'://待使用
//                    $condition[] = [ "order_status", "in", [ 3 ] ];
//                    $condition[] = [ "order_type", "=", 4 ];
                    //todo 待使用状态
                    $condition[] = [ "order_status", "in", [ 3, 11 ] ];
                    $condition[] = [ "order_type", "=", 4 ];
                    break;
                case "waitrate"://待评价
                    $condition[] = [ "order_status", "in", [ 4, 10 ] ];
                    $condition[] = [ "is_evaluate", "=", 1 ];
                    $condition[] = [ "evaluate_status", "=", 0 ];
                    break;
            }
            if ($order_status == 'refunding') {
                $result = $order_refund_model->getRefundOrderGoodsCount([
                    [ "member_id", "=", $this->member_id ],
                    [ "refund_status", "not in", [ 0, 3 ] ]
                ]);
                $data[ $order_status ] = $result[ 'data' ];
            } else {
                $result = $order_common_model->getOrderCount($condition);
                $data[ $order_status ] = $result[ 'data' ];
            }
        }
        return $this->response(success(0, '', $data));
    }

    /**
     * 订单包裹信息
     */
    public function package()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_id = isset($this->params[ 'order_id' ]) ? $this->params[ 'order_id' ] : '';//订单id
        $express_package_model = new ExpressPackage();
        $condition = array (
            [ "member_id", "=", $this->member_id ],
            [ "order_id", "=", $order_id ],
        );

        $order_common_model = new OrderCommonModel();
        //$order_detail             = $order_common_model->getMemberOrderDetail($order_id, $this->member_id, $this->site_id);
        $order_detail = $order_common_model->getOrderInfo([ [ 'member_id', '=', $this->member_id ], [ 'order_id', '=', $order_id ], [ 'site_id', '=', $this->site_id ] ]);

        $result = $express_package_model->package($condition, $order_detail[ 'data' ][ 'mobile' ]);
        if (!empty($result)) {
            foreach ($result as $kk => $vv) {
                if (!empty($vv[ 'trace' ][ 'list' ])) {
                    $result[ $kk ][ 'trace' ][ 'list' ] = array_reverse($vv[ 'trace' ][ 'list' ]);
                }
            }

        }
        if ($result) return $this->response($this->success($result));
        else return $this->response($this->error());
    }

    /**
     * 订单支付
     * @return string
     */
    public function pay()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_ids = isset($this->params[ 'order_ids' ]) ? $this->params[ 'order_ids' ] : '';//订单id
        if (empty($order_ids)) return $this->response($this->error('', "订单数据为空"));
        $order_common_model = new OrderCommonModel();
        $result = $order_common_model->splitOrderPay($order_ids);
        return $this->response($result);
    }

    /**
     * 交易协议
     * @return false|string
     */
    public function transactionAgreement()
    {
        $config_model = new ConfigModel();
        $document_info = $config_model->getTransactionDocument($this->site_id, $this->app_module);
        return $this->response($document_info);
    }

    /**
     * 虚拟订单收货
     */
    public function memberVirtualTakeDelivery()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_id = $this->params[ 'order_id' ] ?? 0;//订单id
        if (empty($order_id)) return $this->response($this->error('', "订单数据为空"));
        $virtual_order_model = new VirtualOrder();
        $params = array (
            'order_id' => $order_id,
            'site_id' => $this->site_id,
            'member_id' => $this->member_id
        );
        $result = $virtual_order_model->virtualTakeDelivery($params);
        return $this->response($result);
    }

}