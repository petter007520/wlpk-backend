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
namespace addon\memberconsume\api\controller;

use app\api\controller\BaseApi;
use addon\memberconsume\model\Consume;


/**
 * 消费奖励
 * Class MemberCancel
 * @package app\api\controller
 */
class Config extends BaseApi
{

    /**
     * 获取消费奖励
     */
    public function info()
    {
        $token = $this->checkToken();
        if ($token['code'] < 0) return $this->response($token);
        $out_trade_no = isset($this->params['out_trade_no']) ? $this->params['out_trade_no'] : 0;
        $config_model = new Consume();

        $order_money = $config_model->getOrderMoney($out_trade_no);
        if($order_money['code'] < 0) return $this->response($order_money);

        $order_money = $order_money['data'] ?? '0.00';
        //订单返积分设置
        $config_result = $config_model->getConfig($this->site_id);

        $point_num = 0;
        $growth_num = 0;

        if($config_result['data']['is_use']){
            $point_num = intval($config_result['data']['value']['return_point_rate'] / 100 * $order_money);
            $growth_num = intval($config_result['data']['value']['return_growth_rate'] / 100 * $order_money);
        }

        $config_result['data']['value']['point_num'] = $point_num;
        $config_result['data']['value']['growth_num'] = $growth_num;
        $config_result['data']['value']['order_money'] = $order_money;

        return $this->response($config_result);
    }

}