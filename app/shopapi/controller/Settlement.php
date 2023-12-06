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

use app\model\shop\ShopSettlement;
use app\model\order\OrderCommon as OrderCommonModel;

/**
 * 店铺结算
 * @author Administrator
 *
 */
class Settlement extends BaseApi
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
     * 结算列表
     * @return false|string
     */
    public function lists()
    {
        $model = new ShopSettlement();
        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $condition[] = [ 'site_id', '=', $this->site_id ];

        $start_time = isset($this->params[ 'start_time' ]) ? $this->params[ 'start_time' ] : '';
        $end_time = isset($this->params[ 'end_time' ]) ? $this->params[ 'end_time' ] : '';
        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ 'period_start_time', '>=', $start_time ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'period_end_time', '<=', $end_time ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'period_start_time', '>=', $start_time ];
            $condition[] = [ 'period_end_time', '<=', $end_time ];
        }

        $order = 'id desc';
        $field = 'id,settlement_no,site_id,period_id,site_name,order_money,shop_money,refund_platform_money,platform_money,refund_shop_money,
        refund_money,create_time,period_start_time,period_end_time,commission,platform_coupon_money,refund_platform_coupon_money';
        $list = $model->getShopSettlementPageList($condition, $page, $page_size, $order, $field);

        return $this->response($list);
    }

    /**
     * 结算信息
     * @return false|string
     */
    public function info()
    {
        $model = new ShopSettlement();

        $id = isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : '';
        $field = 'id,settlement_no,site_id,period_id,site_name,order_money,shop_money,refund_platform_money,platform_money,refund_shop_money,
        refund_money,create_time,period_start_time,period_end_time,commission,platform_coupon_money,refund_platform_coupon_money';
        $info = $model->getShopSettlementInfo([ [ 'id', '=', $id ] ], $field);
        $info = $info[ 'data' ];
        //店铺收入
        $shop_money = $info[ 'shop_money' ] - $info[ 'refund_shop_money' ] - $info[ 'commission' ] + $info[ 'platform_coupon_money' ] - $info[ 'refund_platform_coupon_money' ];
        //平台收入
        $money = $info[ 'platform_money' ] - $info[ 'refund_platform_money' ];

        $data = [
            'info' => $info,
            'shop_money' => number_format($shop_money, 2, '.', ''),
            'money' => number_format($money, 2, '.', '')
        ];
        return $this->response($this->success($data));
    }

    /**
     * 结算明细
     * @return false|string
     */
    public function detail()
    {
        $id = isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : '';

        $order_model = new OrderCommonModel();
        $condition[] = [ 'settlement_id', '=', $id ];

        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $field = 'order_id,order_no,order_money,order_status,shop_money,platform_money,refund_money,refund_shop_money,refund_platform_money,commission,finish_time,platform_coupon_money,refund_platform_coupon_money';
        $list = $order_model->getOrderPageList($condition, $page, $page_size, 'finish_time desc', $field);

        return $this->response($list);
    }

}