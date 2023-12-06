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

use app\model\order\OrderCommon;
use app\model\order\OrderRefund as OrderRefundModel;
use app\model\member\Member;

/**
 * 订单维权
 * Class Orderrefund
 * @package app\shop\controller
 */
class Orderrefund extends BaseApi
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
     * 获取订单条件
     * @return false|string
     */
    public function condition()
    {
        $order_refund_model = new OrderRefundModel();
        $refund_status_list = $order_refund_model->order_refund_status;
        $data = [
            'refund_status_list' => $refund_status_list,//退款状态
            'refund_type_list' => $order_refund_model->refund_type//退款方式
        ];

        return $this->response($this->success($data));
    }

    /**
     * 维权订单列表
     * @return mixed
     */
    public function lists()
    {
        $refund_status = isset($this->params[ 'refund_status' ]) ? $this->params[ 'refund_status' ] : '';//退款状态
        $sku_name = isset($this->params[ 'sku_name' ]) ? $this->params[ 'sku_name' ] : '';//商品名称
        $refund_type = isset($this->params[ 'refund_type' ]) ? $this->params[ 'refund_type' ] : '';//退款方式
        $start_time = isset($this->params[ 'start_time' ]) ? $this->params[ 'start_time' ] : '';//开始时间
        $end_time = isset($this->params[ 'end_time' ]) ? $this->params[ 'end_time' ] : '';//结束时间
        $order_no = isset($this->params[ 'order_no' ]) ? $this->params[ 'order_no' ] : '';//订单编号
        $delivery_status = isset($this->params[ 'delivery_status' ]) ? $this->params[ 'delivery_status' ] : '';//物流状态
        $refund_no = isset($this->params[ 'refund_no' ]) ? $this->params[ 'refund_no' ] : '';//退款编号
        $delivery_no = isset($this->params[ 'delivery_no' ]) ? $this->params[ 'delivery_no' ] : '';//物流编号
        $refund_delivery_no = isset($this->params[ 'refund_delivery_no' ]) ? $this->params[ 'refund_delivery_no' ] : '';//退款物流编号
        $order_refund_model = new OrderRefundModel();

        $page_index = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $condition = [
            [ "nop.site_id", "=", $this->site_id ]
        ];
        //退款状态
        if ($refund_status != "") {
            $condition[] = [ "nop.refund_status", "=", $refund_status ];
        } else {
            $condition[] = [ "nop.refund_status", "<>", 0 ];
        }
        //物流状态
        if ($delivery_status != "") {
            $condition[] = [ "nop.delivery_status", "=", $delivery_status ];
        }
        //商品名称
        if ($sku_name != "") {
            $condition[] = [ "nop.sku_name", "like", "%$sku_name%" ];
        }
        //退款方式
        if ($refund_type != "") {
            $condition[] = [ "nop.refund_type", "=", $refund_type ];
        }
        //退款编号
        if ($refund_no != "") {
            $condition[] = [ "nop.refund_no", "like", "%$refund_no%" ];
        }
        //订单编号
        if ($order_no != "") {
            $condition[] = [ "nop.order_no", "like", "%$order_no%" ];
        }
        //物流编号
        if ($delivery_no != "") {
            $condition[] = [ "nop.delivery_no", "like", "%$delivery_no%" ];
        }
        //退款物流编号
        if ($refund_delivery_no != "") {
            $condition[] = [ "nop.refund_delivery_no", "like", "%$refund_delivery_no%" ];
        }

        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ "nop.refund_action_time", ">=", date_to_time($start_time) ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ "nop.refund_action_time", "<=", date_to_time($end_time) ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'nop.refund_action_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
        }
        $list = $order_refund_model->getRefundOrderGoodsPageList($condition, $page_index, $page_size, "nop.refund_action_time desc");
        return $this->response($list);
    }

    /**
     * 维权订单详情
     * @return mixed
     */
    public function detail()
    {
        $order_goods_id = isset($this->params[ 'order_goods_id' ]) ? $this->params[ 'order_goods_id' ] : 0;
        //维权订单项信息
        $order_refund_model = new OrderRefundModel();

        $detail_result = $order_refund_model->getRefundDetail($order_goods_id);
        $detail = $detail_result[ "data" ];

        if (empty($detail)) {
            return $this->response($this->error('查询不到此退款信息!'));
        }
        if ($detail['site_id'] != $this->site_id){
            return $this->response($this->error('查询不到此退款信息!'));
        }

        $order_common_model = new OrderCommon();
        $order_info_result = $order_common_model->getOrderInfo([ [ "order_id", "=", $detail[ "order_id" ] ], [ 'site_id', '=', $this->site_id ] ]);
        $order_info = $order_info_result[ "data" ];

        //添加会员昵称
        $member = new Member();
        $member_info = $member->getMemberInfo([ [ "member_id", '=', $order_info[ 'member_id' ] ] ], 'nickname');
        $order_info[ 'nickname' ] = $member_info[ 'data' ][ 'nickname' ];

        $data = [
            'detail' => $detail,
            'order_info' => $order_info
        ];
        return $this->response($this->success($data));
    }

    /**
     * 维权拒绝
     * @return mixed
     */
    public function refuse()
    {
        $order_goods_id = isset($this->params[ 'order_goods_id' ]) ? $this->params[ 'order_goods_id' ] : 0;
        $refund_refuse_reason = isset($this->params[ 'refund_refuse_reason' ]) ? $this->params[ 'refund_refuse_reason' ] : '';
        $order_refund_model = new OrderRefundModel();
        $data = array (
            "order_goods_id" => $order_goods_id,
            "refund_refuse_reason" => $refund_refuse_reason,
            'site_id' => $this->site_id
        );
        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action' => '商家拒绝了维权',
            'action_way' => 2
        ];
        $res = $order_refund_model->orderRefundRefuse($data, $this->user_info, $refund_refuse_reason, $log_data);
        return $this->response($res);
    }

    /**
     * 维权同意
     * @return mixed
     */
    public function agree()
    {
        $order_goods_id = isset($this->params[ 'order_goods_id' ]) ? $this->params[ 'order_goods_id' ] : 0;
        $order_refund_model = new OrderRefundModel();
        $data = array (
            "order_goods_id" => $order_goods_id,
            'site_id' => $this->site_id
        );
        $res = $order_refund_model->orderRefundConfirm($data, $this->user_info);
        return $this->response($res);
    }

    /**
     * 维权收货
     * @return mixed
     */
    public function receive()
    {
        $order_goods_id = isset($this->params[ 'order_goods_id' ]) ? $this->params[ 'order_goods_id' ] : 0;
        $is_refund_stock = isset($this->params[ 'is_refund_stock' ]) ? $this->params[ 'is_refund_stock' ] : 0;
        $order_refund_model = new OrderRefundModel();
        $data = array (
            "order_goods_id" => $order_goods_id,
            "is_refund_stock" => $is_refund_stock,
            'site_id' => $this->site_id
        );
        $res = $order_refund_model->orderRefundTakeDelivery($data, $this->user_info);
        return $this->response($res);
    }

    /**
     * 维权通过
     * @return mixed
     */
    public function complete()
    {
        $order_goods_id = isset($this->params[ 'order_goods_id' ]) ? $this->params[ 'order_goods_id' ] : 0;
        $refund_money_type = $this->params[ 'refund_money_type' ] ?? 2;
        $shop_refund_remark = $this->params[ 'shop_refund_remark' ] ?? '';
        $refund_real_money = $this->params[ 'refund_real_money' ] ?? 0;
        $order_refund_model = new OrderRefundModel();
        $data = array (
            'order_goods_id' => $order_goods_id,
            'site_id' => $this->site_id,
            'refund_money_type' => $refund_money_type,
            'shop_refund_remark' => $shop_refund_remark,
            'refund_real_money' => $refund_real_money
        );
        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action' => '商家对维权进行了转账，维权结束',
            'action_way' => 2
        ];
        $res = $order_refund_model->orderRefundFinish($data, $this->user_info, $log_data);
        return $this->response($res);
    }

    /**
     * 维权关闭
     * @return false|string
     */
    public function close(){
        $order_goods_id = isset($this->params[ 'order_goods_id' ]) ? $this->params[ 'order_goods_id' ] : 0;
        $order_refund_model = new OrderRefundModel();
        $res = $order_refund_model->orderRefundClose($order_goods_id, $this->site_id, $this->user_info);
        return $this->response($res);
    }

    /**
     * 主动退款
     * @return false|string
     */
    public function activeRefund(){
        $order_goods_id = $this->params['order_goods_id'] ?? 0;
        $refund_money_type = $this->params['refund_money_type'] ?? '';
        $shop_refund_remark = $this->params['shop_refund_remark'] ?? '';
        $do_refund_money = $this->params['do_refund_money'] ?? '';

        $order_refund_model = new OrderRefundModel();
        $params = array (
            'site_id' => $this->site_id,
            'app_module' => $this->app_module,
            'refund_money_type' => $refund_money_type,
            'shop_refund_remark' => $shop_refund_remark,
            'user_info' => $this->user_info,
            'order_goods_id' => $order_goods_id,
            'do_refund_money' => $do_refund_money
        );
        $res = $order_refund_model->doOrderOrderRefund($params);
        return $this->response($res);
    }
}