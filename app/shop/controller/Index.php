<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;

use addon\fenxiao\model\FenxiaoApply;
use addon\fenxiao\model\FenxiaoWithdraw;
use addon\niusms\model\Config as NiuSmsConfig;
use addon\niusms\model\Sms as NiuSms;
use addon\weapp\model\Config as WeappConfigModel;
use app\model\goods\Goods as GoodsModel;
use app\model\member\Member;
use app\model\member\Member as MemberModel;
use app\model\order\OrderCommon;
use app\model\shop\Shop as ShopModel;
use app\model\system\Config as SystemConfig;
use app\model\system\Promotion as PrmotionModel;
use app\model\system\Stat;
use app\model\web\Config as WebConfigModel;
use Carbon\Carbon;
use app\model\order\OrderRefund as OrderRefundModel;
use think\facade\Cache;
use addon\wechat\model\Config as WechatConfig;
use addon\weapp\model\Config as WeappConfig;
use addon\alipay\model\Config as AlipayConfig;
use addon\wechatpay\model\Config as WechatpayConfig;
use app\model\order\Order;

class Index extends BaseShop
{
    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        Cache::tag("cache_tablestat_shop")->clear();
        Cache::tag("cache_tablemember")->clear();
        Cache::tag("cache_tablegoods")->clear();
        Cache::tag("cache_tableorder")->clear();
        Cache::tag("cache_tablefenxiao_apply")->clear();
        Cache::tag("cache_tablefenxiao_withdraw")->clear();
        $shop_info = $this->shop_info;
        $time = time();
        $this->assign('shop_status', 1);

        $this->handlePromotion();

        //分销插件是否存在
        $is_fenxiao = addon_is_exit('fenxiao', $this->site_id);
        $this->assign('is_fenxiao', $is_fenxiao);

        //基础统计信息
        $today = Carbon::now();
        $this->assign("today", $today);

        // 牛云短信余额查询
        if (addon_is_exit('niusms', $this->site_id)) {
            $sms_config = ( new NiuSmsConfig() )->getSmsConfig($this->site_id)[ 'data' ];
            if ($sms_config[ 'is_use' ]) {
                $account_info = ( new NiuSms() )->getChildAccountInfo([
                    'username' => $sms_config[ 'value' ][ 'username' ],
                ]);
                $this->assign('sms_num', $account_info[ 'data' ][ 'balance' ] ?? 0);
            }
        }
        $this->assign('guide_close', cookie('guideClose'));
        if (!cookie('guideClose')) {
            $goods = ( new GoodsModel() )->getGoodsInfo([ [ 'site_id', '=', $this->site_id ] ], 'goods_id')[ 'data' ];
            $this->assign('goods_complete', !empty($goods));

            $wechat_config = ( new WechatConfig() )->getWechatConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('wechat_complete', !empty($wechat_config));
            $weapp_config = ( new WeappConfig() )->getWeappConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('weapp_complete', !empty($weapp_config));

            $alipay_config = addon_is_exit('alipay', $this->site_id) ? ( new AlipayConfig() )->getPayConfig($this->site_id)[ 'data' ][ 'value' ] : [];
            $wechatpay_config = ( new WechatpayConfig() )->getPayConfig($this->site_id)[ 'data' ][ 'value' ];
            unset($wechatpay_config[ 'transfer_type' ]);
            $this->assign('pay_complete', ( !( empty($alipay_config) ) || !( empty($wechatpay_config) ) ));

            $this->assign('site_complete', !empty($this->shop_info[ 'logo' ]));
        }
        $this->init();
        $this->assign('img_extension_error', config('upload.driver') == 'imagick' && !extension_loaded('imagick'));
        return $this->fetch("index/index");
    }

    private function init()
    {
        $is_new_version = 0; // 检查小程序是否有新版本
        if (addon_is_exit("weapp")) {
            $weapp_config_model = new WeappConfigModel();
            // 获取站点小程序版本信息
            $version_info = $weapp_config_model->getWeappVersion($this->site_id);
            $version_info = $version_info[ 'data' ][ 'value' ];
            $currrent_version_info = config('info');
            if (!isset($version_info[ 'version' ]) || ( isset($version_info[ 'version' ]) && $version_info[ 'version' ] != $currrent_version_info[ 'version_no' ] )) {
                $is_new_version = 1;
            }
        }
        $this->assign('is_new_version', $is_new_version);

        $is_new_domain = 0; // 检查域名是否发生变化

        $web_config_model = new WebConfigModel();
        $shop_domain_config = $web_config_model->getShopDomainConfig()[ 'data' ][ 'value' ];
        if ($shop_domain_config[ 'domain_name' ] != __ROOT__) {
            $is_new_domain = 1;
        }
        $this->assign('is_new_domain', $is_new_domain);


        //商城状态
        $shop_model = new ShopModel();
        $shop_status_result = $shop_model->getShopStatus($this->site_id, $this->app_module);

        $shop_status = $shop_status_result[ 'data' ][ 'value' ];
        $this->assign('shop_status', $shop_status);
    }

    /**
     * 获取营销活动 添加快捷菜单的优先排序
     */
    public function handlePromotion()
    {
        $promotion_model = new PrmotionModel();
        $promotions = $promotion_model->getSitePromotions($this->site_id);

        $promotion = array_values(array_filter(array_map(function($item) { if ($item[ 'show_type' ] == 'shop' || $item[ 'show_type' ] == 'member') return $item; }, $promotions)));
        $tool = array_values(array_filter(array_map(function($item) { if ($item[ 'show_type' ] == 'tool') return $item; }, $promotions)));
        $promotion = array_column($promotion, null, 'name');
        $tool = array_column($tool, null, 'name');

        $value = ( new SystemConfig() )->getConfig([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', $this->app_module ], [ 'config_key', '=', 'COMMON_ADDON' ] ])[ 'data' ][ 'value' ];
        $promotion_addon = empty($value) ? [] : array_filter(explode(',', $value[ 'promotion' ] ?? ''));
        $tool_addon = empty($value) ? [] : array_filter(explode(',', $value[ 'tool' ] ?? ''));

        if (!empty($promotion_addon)) {
            foreach ($promotion_addon as $name) {
                if (isset($promotion[ $name ])) {
                    array_unshift($promotion, $promotion[ $name ]);
                    unset($promotion[ $name ]);
                }
            }
        }
        if (!empty($tool_addon)) {
            foreach ($tool_addon as $name) {
                if (isset($tool[ $name ])) {
                    array_unshift($tool, $tool[ $name ]);
                    unset($tool[ $name ]);
                }
            }
        }
        $this->assign("promotion", $promotion);
        $this->assign("tool", $tool);
    }

    /**
     * 今日昨日统计
     * @return array
     */
    public function dayCount()
    {
        if (request()->isAjax()) {
            //基础统计信息
            $stat_shop_model = new Stat();
            $today = Carbon::now();
            $yesterday = Carbon::yesterday();
            $stat_today = $stat_shop_model->getShopStatSum($this->site_id, $today->startOfDay()->timestamp, $today->endOfDay()->timestamp);
            $stat_yesterday = $stat_shop_model->getShopStatSum($this->site_id, $yesterday->startOfDay()->timestamp, $yesterday->endOfDay()->timestamp);
            $order = new Order();
            //获取总数
            $shop_stat_sum = $stat_shop_model->getShopStatSum($this->site_id);
            $goods_model = new GoodsModel();
            $goods_sum = $goods_model->getGoodsTotalCount([ [ 'site_id', '=', $this->site_id ], [ 'is_delete', '=', 0 ] ]);
            $shop_stat_sum[ 'data' ][ 'goods_count' ] = $goods_sum[ 'data' ];
            $shop_stat_sum[ 'data' ]['member_count'] = (new Member())->getMemberCount([ [ 'site_id', '=', $this->site_id ], [ 'is_delete', '=', 0 ] ])[ 'data' ];
            $shop_stat_sum[ 'data' ][ 'order_pay_count' ] = $order->getOrderCount([ [ 'site_id', '=', $this->site_id ], [ 'is_delete', '=', 0 ], [ 'pay_status', '=', 1 ] ])['data'];
            $shop_stat_sum[ 'data' ][ 'earnings_total_money' ] = $order->getOrderMoneySum([ [ 'site_id', '=', $this->site_id ], [ 'is_delete', '=', 0 ], [ 'pay_status', '=', 1 ] ], 'pay_money')['data'];

                //日同比
            $day_rate[ 'order_pay_count' ] = diff_rate($stat_today[ 'data' ][ 'order_pay_count' ], $stat_yesterday[ 'data' ][ 'order_pay_count' ]);
            $day_rate[ 'order_total' ] = diff_rate($stat_today[ 'data' ][ 'order_total' ], $stat_yesterday[ 'data' ][ 'order_total' ]);
            $day_rate[ 'earnings_total_money' ] = diff_rate($stat_today[ 'data' ][ 'earnings_total_money' ], $stat_yesterday[ 'data' ][ 'earnings_total_money' ]);
            $day_rate[ 'collect_goods' ] = diff_rate($stat_today[ 'data' ][ 'collect_goods' ], $stat_yesterday[ 'data' ][ 'collect_goods' ]);
            $day_rate[ 'visit_count' ] = diff_rate($stat_today[ 'data' ][ 'visit_count' ], $stat_yesterday[ 'data' ][ 'visit_count' ]);
            $day_rate[ 'member_count' ] = diff_rate($stat_today[ 'data' ][ 'member_count' ], $stat_yesterday[ 'data' ][ 'member_count' ]);

            //会员总数
            $member_model = new MemberModel();
            $member_count = $member_model->getMemberCount([ [ 'site_id', '=', $this->site_id ] ]);

            $res = [
                'stat_day' => $stat_today[ 'data' ],
                'stat_yesterday' => $stat_yesterday[ 'data' ],
                'today' => $today,
                'shop_stat_sum' => $shop_stat_sum[ 'data' ],
                'day_rate' => $day_rate,
                'member_count' => $member_count[ 'data' ]
            ];
        }
        return $res;
    }

    /**
     * 综合统计
     * @return array
     */
    public function sumCount()
    {
        if (request()->isAjax()) {
            $goods_model = new GoodsModel();
            $order = new OrderCommon();
            $waitpay = $order->getOrderCount([ [ 'order_status', '=', 0 ], [ 'site_id', '=', $this->site_id ], [ 'is_delete', '=', 0 ], [ 'order_scene', '=', 'online' ] ]);
            $waitsend = $order->getOrderCount([ [ 'order_status', '=', 1 ], [ 'site_id', '=', $this->site_id ], [ 'is_delete', '=', 0 ] ]);
            $order_refund_model = new OrderRefundModel();
            $refund_num = $order_refund_model->getRefundOrderGoodsCount([
                [ "site_id", "=", $this->site_id ],
                [ "refund_status", "not in", [ 0, 3 ] ]
            ]);
            $goods_stock_alarm = $goods_model->getGoodsStockAlarm($this->site_id);
            $goods_total = $goods_model->getGoodsTotalCount([ [ 'goods_state', '=', 1 ], [ 'site_id', '=', $this->site_id ], [ 'is_delete', '=', 0 ] ]);
            $warehouse_goods = $goods_model->getGoodsTotalCount([ [ 'goods_state', '=', 0 ], [ 'site_id', '=', $this->site_id ], [ 'is_delete', '=', 0 ] ]);

            $num_data = [
                'waitpay' => $waitpay[ 'data' ],
                'waitsend' => $waitsend[ 'data' ],
                'refund' => $refund_num[ 'data' ],
                'goods_stock_alarm' => count($goods_stock_alarm[ 'data' ]),
                'goods_total' => $goods_total[ 'data' ],
                'warehouse_goods' => $warehouse_goods[ 'data' ]
            ];

            //分销插件是否存在
            $is_fenxiao = addon_is_exit('fenxiao', $this->site_id);
            $this->assign('is_fenxiao', $is_fenxiao);
            if ($is_fenxiao) {
                //提现待审核
                $fenxiao_model = new FenxiaoWithdraw();
                $withdraw_count = $fenxiao_model->getFenxiaoWithdrawCount([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ], 'id');
                $num_data[ 'withdraw_count' ] = $withdraw_count[ 'data' ];

                //分销商申请
                $fenxiao_apply_model = new FenxiaoApply();
                $fenxiao_count = $fenxiao_apply_model->getFenxiaoApplyCount([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ], 'apply_id');
                $num_data[ 'apply_count' ] = $fenxiao_count[ 'data' ];
            } else {
                $waitconfirm = $order->getOrderCount([ [ 'order_status', "=", 3 ], [ 'site_id', '=', $this->site_id ], [ 'is_delete', '=', 0 ] ]);
                $complete = $order->getOrderCount([ [ 'order_status', "=", 10 ], [ 'site_id', '=', $this->site_id ], [ 'is_delete', '=', 0 ] ]);
                $num_data[ 'waitconfirm' ] = $waitconfirm[ 'data' ];
                $num_data[ 'complete' ] = $complete[ 'data' ];
            }
        }
        return $num_data;
    }

    /**
     * 图形统计
     *
     * @return void
     */
    public function chartCount()
    {
        if (request()->isAjax()) {
            //近十天的订单数以及销售金额
            $stat_shop_model = new Stat();
            $date_day = getweeks();
            $order_total = '';
            $order_pay_count = '';
            foreach ($date_day as $k => $day) {
                $dayarr = explode('-', $day);
                $stat_day[ $k ] = $stat_shop_model->getStatShop($this->site_id, $dayarr[ 0 ], $dayarr[ 1 ], $dayarr[ 2 ]);
                $order_total .= $stat_day[ $k ][ 'data' ][ 'order_total' ] . ',';
                $order_pay_count .= $stat_day[ $k ][ 'data' ][ 'order_pay_count' ] . ',';
            }
            $ten_day[ 'order_total' ] = explode(',', substr($order_total, 0, strlen($order_total) - 1));
            $ten_day[ 'order_pay_count' ] = explode(',', substr($order_pay_count, 0, strlen($order_pay_count) - 1));
            return $ten_day;
        }
    }

    /**
     * 营销插件
     * @return array
     */
    public function marketingPlug()
    {
        if (request()->isAjax()) {
            //营销活动
            $promotion_model = new PrmotionModel();
            $promotions = $promotion_model->getSitePromotions($this->site_id);
            $toolcount = 0;
            $shopcount = 0;
            //营销插件数量
            foreach ($promotions as $k => $v) {
                if ($v[ "show_type" ] == 'tool') {
                    $toolcount += 1;
                }
                if ($v[ "show_type" ] == 'member' || $v[ "show_type" ] == 'shop') {
                    $shopcount += 1;
                }
            }
            $count = [
                'toolcount' => $toolcount,
                'shopcount' => $shopcount
            ];
            $res = [
                'count' => $count,
                'promotions' => $promotions
            ];
            return $res;
        }
    }
}
