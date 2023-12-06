<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\memberconsume\shop\controller;

use app\shop\controller\BaseShop;
use addon\memberconsume\model\Consume;

/**
 * 会员消费
 */
class Config extends BaseShop
{
    /**
     * 消费返积分
     */
    public function index()
    {
        if (request()->isAjax()) {

            //订单消费返积分设置数据
            $data = [
                'return_point_status' => input('return_point_status', 'complete'),//返积分事件 pay 订单付款 receive 订单收货 complete 订单完成 单选或下拉
                'return_point_rate'   => input('return_point_rate', 0),//返积分比率 0-100 不取小数
                'return_growth_rate'  => input('return_growth_rate', 0),//成长值返还比例0-100 不取小数
                'return_coupon'       => input('return_coupon', ''),//优惠券
                'is_return_coupon'    => input('is_return_coupon', 0),//是否送优惠券
                'is_recovery_reward'    => input('is_recovery_reward', 0),//退款是否回收奖励
            ];
            $this->addLog("设置会员消费奖励");
            $is_use       = input("is_use", 0);//是否启用
            $config_model = new Consume();
            $res          = $config_model->setConfig($data, $is_use, $this->site_id);
            return $res;
        } else {
            $event_list = array(
                ["name" => "receive", "title" => "订单收货"],
                ["name" => "pay", "title" => "订单付款"],
                ["name" => "complete", "title" => "订单完成"],
            );
            $this->assign("event_list", $event_list);
            $config_model = new Consume();
            //订单返积分设置
            $config_result = $config_model->getConfig($this->site_id);
            $this->assign('config', $config_result['data']);
            $this->forthMenu();
            return $this->fetch('config/index');
        }
    }

    /**
     * 奖励记录
     */
    public function lists()
    {
        if (request()->isAjax()) {
            $page      = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $status    = input('status', '');
            $order_no    = input('order_no', '');
            $username    = input('username', '');

            $condition = [];
            if ($status !== '') {
                $condition[] = ['pcr.type', '=', $status];
            }
            if($order_no){
                $condition[] = ['o.order_no', 'like', '%'.$order_no.'%'];
            }
            if($username){
                $condition[] = ['m.username', 'like', '%'.$username.'%'];
            }
            $condition[] = ['pcr.site_id', '=', $this->site_id];
            $order       = 'pcr.create_time desc';
            $field       = 'pcr.*,m.username,m.nickname,m.mobile,m.headimg,o.order_no';
            $alias = 'pcr';
            $join = [
                ['member m','pcr.member_id = m.member_id','left'],
                ['order o','pcr.order_id = o.order_id','left'],
            ];

            $start_time = input('start_time', '');
            $end_time   = input('end_time', '');

            if ($start_time && !$end_time) {
                $condition[] = ['pcr.create_time', '>=', date_to_time($start_time)];
            } elseif (!$start_time && $end_time) {
                $condition[] = ['pcr.create_time', '<=', date_to_time($end_time)];
            } elseif ($start_time && $end_time) {
                $condition[] = [ 'pcr.create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
            }

            $config_model = new Consume();
            $res            = $config_model->getConsumeRecordPageList($condition, $page, $page_size, $order, $field, $alias, $join);
            return $res;
        } else {
            $event_list = array(
                ["name" => "coupon", "title" => "优惠券"],
                ["name" => "point", "title" => "积分"],
                ["name" => "growth", "title" => "成长值"],
            );
            $this->assign("event_list", $event_list);
            $this->forthMenu();
            return $this->fetch('config/lists');
        }
    }

}