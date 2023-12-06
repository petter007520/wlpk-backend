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

use app\model\member\Member as MemberModel;
use app\model\order\OrderRefund as OrderRefundModel;

class Orderrefund extends BaseApi
{

    /**
     * 售后列表
     */
    public function lists()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_refund_model = new OrderRefundModel();
        $condition = array (
            [ "nop.member_id", "=", $this->member_id ],
        );
        $refund_status = isset($this->params[ 'refund_status' ]) ? $this->params[ 'refund_status' ] : 'all';
        switch ( $refund_status ) {
//            case "waitpay"://处理中
//                $condition[] = [ "refund_status", "=", 1 ];
//                break;
            default :
                $condition[] = [ "nop.refund_status", "<>", 0 ];
                break;
        }

        $page_index = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $res = $order_refund_model->getRefundOrderGoodsPageList($condition, $page_index, $page_size, "refund_action_time desc");
        return $this->response($res);
    }

    /**
     * 退款数据查询
     * @return string
     */
    public function refundData()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_goods_id = isset($this->params[ 'order_goods_id' ]) ? $this->params[ 'order_goods_id' ] : '0';
        $order_refund_model = new OrderRefundModel();
        $order_goods_info_result = $order_refund_model->getRefundDetail($order_goods_id);
        $order_goods_info = $order_goods_info_result[ "data" ];//订单项信息
        $refund_money_array = $order_refund_model->getOrderRefundMoney($order_goods_id);
        if (isset($refund_money_array[ 'code' ]) && $refund_money_array[ 'code' ] != 0) return $this->response($refund_money_array);
        $refund_delivery_money = $refund_money_array[ 'refund_delivery_money' ];//其中的运费
        $refund_money = $refund_money_array[ 'refund_money' ];//总退款
        $refund_type = $order_refund_model->getRefundType($order_goods_info);
        $refund_reason_type = $order_refund_model->refund_reason_type;
        $result = array (
            "order_goods_info" => $order_goods_info,
            "refund_money" => $refund_money,
            "refund_type" => $refund_type,
            "refund_reason_type" => $refund_reason_type,
            "refund_delivery_money" => $refund_delivery_money
        );
        return $this->response($this->success($result));
    }

    /**
     * 多个退款数据查询
     */
    public function refundDataBatch()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_goods_ids = isset($this->params[ 'order_goods_ids' ]) ? $this->params[ 'order_goods_ids' ] : '';
        $order_refund_model = new OrderRefundModel();
        if (empty($order_goods_ids)) return $this->response($this->error('', '未传order_goods_ids'));
        $order_goods_id_arr = explode(',', $order_goods_ids);
        foreach ($order_goods_id_arr as $item) {
            $order_goods_info_result[] = $order_refund_model->getRefundDetail($item)[ 'data' ] ?? [];
        }
        $order_goods_info = $order_goods_info_result;//订单项信息
        $refund_money_array = $order_refund_model->getOrderRefundMoney($order_goods_ids);
        $refund_delivery_money = $refund_money_array[ 'refund_delivery_money' ];//其中的运费
        $refund_money = $refund_money_array[ 'refund_money' ];//总退款
        $refund_type = $order_refund_model->getRefundOrderType($order_goods_info[ 0 ][ 'order_id' ]);
        $refund_reason_type = $order_refund_model->refund_reason_type;
        $result = array (
            "order_goods_info" => $order_goods_info,
            "refund_money" => $refund_money,
            "refund_type" => $refund_type,
            "refund_reason_type" => $refund_reason_type,
            "refund_delivery_money" => $refund_delivery_money
        );
        return $this->response($this->success($result));
    }

    /**
     * 发起退款
     */
    public function refund()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $member_model = new MemberModel();
        $member_info_result = $member_model->getMemberInfo([ [ "member_id", "=", $this->member_id ] ]);
        $member_info = $member_info_result[ "data" ];
        $order_refund_model = new OrderRefundModel();
        $order_goods_ids = $this->params[ 'order_goods_ids' ] ?? '0';
        $refund_type = $this->params[ 'refund_type' ] ?? 1;
        $refund_reason = $this->params[ 'refund_reason' ] ?? '';
        $refund_remark = $this->params[ 'refund_remark' ] ?? '';
        if (empty($order_goods_ids)) return $this->response($this->error('', '未传order_goods_ids'));
        $buyer_name = empty($member_info[ 'nickname' ]) ? '' : '【' . $member_info[ 'nickname' ] . '】';
        $log_data = [
            'uid' => $this->member_id,
            'nick_name' => $member_info[ 'nickname' ],
            'action' => '买家'.$buyer_name.'发起了退款申请',
            'action_way' => 1
        ];

        $order_goods_ids = explode(',', $order_goods_ids);
        foreach ($order_goods_ids as $item) {
            $data = array (
                "order_goods_id" => $item,
                "refund_type" => $refund_type,
                "refund_reason" => $refund_reason,
                "refund_remark" => $refund_remark
            );
            $result = $order_refund_model->orderRefundApply($data, $member_info, $log_data);
        }

        return $this->response($result);
    }

    /**
     * 取消发起的退款申请
     */
    public function cancel()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $member_model = new MemberModel();
        $member_info_result = $member_model->getMemberInfo([ [ "member_id", "=", $this->member_id ] ]);
        $member_info = $member_info_result[ "data" ];
        $order_refund_model = new OrderRefundModel();
        $order_goods_id = isset($this->params[ 'order_goods_id' ]) ? $this->params[ 'order_goods_id' ] : '0';
        $data = array (
            "order_goods_id" => $order_goods_id
        );
        $buyer_name = empty($member_info[ 'nickname' ]) ? '' : '【' . $member_info[ 'nickname' ] . '】';
        $log_data = [
            'uid' => $this->member_id,
            'nick_name' => $member_info[ 'nickname' ],
            'action' => '买家'.$buyer_name.'撤销了维权',
            'action_way' => 1
        ];
        $res = $order_refund_model->memberCancelRefund($data, $member_info, $log_data);
        return $this->response($res);
    }

    /**
     * 买家退货
     * @return string
     */
    public function delivery()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $member_model = new MemberModel();
        $member_info_result = $member_model->getMemberInfo([ [ "member_id", "=", $this->member_id ] ]);
        $member_info = $member_info_result[ "data" ];
        $order_refund_model = new OrderRefundModel();
        $order_goods_id = isset($this->params[ 'order_goods_id' ]) ? $this->params[ 'order_goods_id' ] : '0';
        $refund_delivery_name = isset($this->params[ 'refund_delivery_name' ]) ? $this->params[ 'refund_delivery_name' ] : '';//物流公司名称
        $refund_delivery_no = isset($this->params[ 'refund_delivery_no' ]) ? $this->params[ 'refund_delivery_no' ] : '';//物流编号
        $refund_delivery_remark = isset($this->params[ 'refund_delivery_remark' ]) ? $this->params[ 'refund_delivery_remark' ] : '';//买家发货说明
        $data = array (
            "order_goods_id" => $order_goods_id,
            "refund_delivery_name" => $refund_delivery_name,
            "refund_delivery_no" => $refund_delivery_no,
            "refund_delivery_remark" => $refund_delivery_remark
        );
        $res = $order_refund_model->orderRefundDelivery($data, $member_info);
        return $this->response($res);
    }

    /**
     * 维权详情
     * @return string
     */
    public function detail()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_refund_model = new OrderRefundModel();
        $order_goods_id = isset($this->params[ 'order_goods_id' ]) ? $this->params[ 'order_goods_id' ] : '0';
        $order_goods_info_result = $order_refund_model->getMemberRefundDetail($order_goods_id, $this->member_id);
        $order_goods_info = $order_goods_info_result[ "data" ] ?? [];
        if ($order_goods_info) {
            //查询店铺收货地址
            $order_goods_info_result[ "data" ] = array_merge($order_goods_info_result[ "data" ], $order_refund_model->getRefundAddress($this->site_id, $order_goods_info[ 'refund_address_id' ]));
        }

        return $this->response($order_goods_info_result);
    }

}