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

use addon\coupon\model\Coupon;
use app\model\system\AddonQuick;
use app\model\system\Promotion as PrmotionModel;
use app\model\member\MemberAccount;
use app\model\order\Order;
use app\model\system\Config as SystemConfig;
use app\model\web\Config as ConfigModel;

/**
 * 营销
 * Class Promotion
 * @package app\shop\controller
 */
class Promotion extends BaseShop
{
    protected $addon_arr = [];

    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
        if (!request()->isAjax()) $this->addon_arr = array_unique(array_column($this->menus, 'addon'));
    }

    /**
     * 营销概况
     */
    public function index()
    {
        $promotion_model = new PrmotionModel();
        $length = input('length', 0);
        $start_time = date('Y-m-01', strtotime($length . ' month'));
        $end_time = date('Y-m-d', strtotime("$start_time +1 month -1 day"));
        $start_time = strtotime($start_time . ' 00:00:00');
        $end_time = strtotime($end_time . ' 23:59:59');
        $summary = $promotion_model->getPromotionSummary($start_time, $end_time, $this->site_id)[ 'data' ];

        if (request()->isAjax()) {
            return success(0, '', [
                'month' => date('Y/m', $start_time),
                'days' => (int) date("t", $start_time),
                'start_time' => $start_time,
                'data' => $summary
            ]);
        }
        $order_num = ( new Order() )->getOrderCount([ [ 'site_id', '=', $this->site_id ], [ 'promotion_type', '<>', '' ], [ 'pay_status', '=', 1 ] ], 'order_id')[ 'data' ];
        $this->assign('summary', $summary);
        $this->assign('month', date('Y/m', $start_time));
        $this->assign('days', date("t", $start_time));
        $this->assign('start_time', $start_time);

        $all_promotion = array_column($promotion_model->getSitePromotions($this->site_id), null, 'name');
        $all_promotion = array_filter(array_map(function($item) {
            if ($item[ 'show_type' ] == 'shop' || $item[ 'show_type' ] == 'member') return $item;
        }, $all_promotion));

        $value = ( new SystemConfig() )->getConfig([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', $this->app_module ], [ 'config_key', '=', 'COMMON_ADDON' ] ])[ 'data' ][ 'value' ];
        $promotion_addon = empty($value) ? [] : array_filter(explode(',', $value[ 'promotion' ] ?? ''));
        foreach ($promotion_addon as $name) {
            if (isset($all_promotion[ $name ])) {
                array_unshift($all_promotion, $all_promotion[ $name ]);
                unset($all_promotion[ $name ]);
            }
        }
        $this->assign('all_promotion', $all_promotion);
        $this->assign('order_num', $order_num);
        return $this->fetch("promotion/index");
    }

    /**
     * 营销活动
     * @return mixed
     */
    public function market()
    {
        $promotion_model = new PrmotionModel();
        $promotions = $promotion_model->getSitePromotions($this->site_id);
        $promotions = $this->filterAddon($promotions);
        $this->assign("promotion", $promotions);
        $user_info = $this->user_info;
        $this->assign('user_info', $user_info);

        $addon_quick_model = new AddonQuick();
        //店铺促销
        $shop_addon = $addon_quick_model->getAddonQuickByAddonType($promotions, 'shop');
        $this->assign('shop_addon', $shop_addon);

        $member_addon = $addon_quick_model->getAddonQuickByAddonType($promotions, 'member');
        $this->assign('member_addon', $member_addon);

        $config = new SystemConfig();
        $value = $config->getConfig([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', $this->app_module ], [ 'config_key', '=', 'COMMON_ADDON' ] ])[ 'data' ][ 'value' ];
        $addon_array = empty($value) ? [] : explode(',', $value[ 'promotion' ] ?? '');
        $this->assign('common_addon', $addon_array);

        return $this->fetch("promotion/market");
    }

    /**
     * 会员营销
     * @return mixed
     */
    public function member()
    {
        $promotion_model = new PrmotionModel();
        $promotions = $promotion_model->getSitePromotions($this->site_id);
        $promotions = $this->filterAddon($promotions);
        $addon_quick_model = new AddonQuick();
        $addon = $addon_quick_model->getAddonQuickByAddonType($promotions, 'member');
        $this->assign('tool_addon', $addon);
        $user_info = $this->user_info;
        $this->assign('user_info', $user_info);
        $this->assign("promotion", $promotions);
        return $this->fetch("promotion/member");
    }

    /**
     * 营销工具
     * @return mixed
     */
    public function tool()
    {
        $promotion_model = new PrmotionModel();
        $promotions = $promotion_model->getPromotions();
        $promotions[ 'shop' ] = $this->filterAddon($promotions[ 'shop' ]);
        $this->assign("promotion", $promotions[ 'shop' ]);

        $addon_quick_model = new AddonQuick();
        $addon = $addon_quick_model->getAddonQuickByAddonType($promotions[ 'shop' ], 'tool');
        $this->assign('tool_addon', $addon);
        $user_info = $this->user_info;
        $this->assign('user_info', $user_info);

        $config = new SystemConfig();
        $value = $config->getConfig([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', $this->app_module ], [ 'config_key', '=', 'COMMON_ADDON' ] ])[ 'data' ][ 'value' ];
        $addon_array = empty($value) ? [] : explode(',', $value[ 'tool' ] ?? '');
        $this->assign('common_addon', $addon_array);

        return $this->fetch("promotion/tool");
    }

    /**
     * @param $data
     */
    protected function filterAddon($data)
    {
        $res = [];
        foreach ($data as $key => $val) {
            if (in_array($val[ 'name' ], $this->addon_arr)) {
                $res[] = $val;
            }
        }
        return $res;
    }

    public function summary()
    {
        if (request()->isAjax()) {
            $coupon_model = new Coupon();
            $order_model = new Order();
            $account_model = new MemberAccount();

            $promotion = event('ShowPromotion', [ 'count' => 1, 'site_id' => $this->site_id ]);
            $promotion = array_map(function($item) {
                if (isset($item[ 'shop' ]) && !empty($item[ 'shop' ]) && isset($item[ 'shop' ][ 0 ][ 'summary' ]) && !empty($item[ 'shop' ][ 0 ][ 'summary' ])) return $item[ 'shop' ][ 0 ][ 'summary' ][ 'count' ];
            }, $promotion);

            $data = [
                'promotion_num' => array_sum($promotion),
                'coupon_total_count' => $coupon_model->getMemberCouponCount([ [ 'site_id', '=', $this->site_id ] ])[ 'data' ],
                'coupon_used_count' => $coupon_model->getMemberCouponCount([ [ 'site_id', '=', $this->site_id ], [ 'state', '=', 2 ] ])[ 'data' ],
                'buyer_num' => $order_model->getOrderCount([ [ 'site_id', '=', $this->site_id ], [ 'promotion_type', '<>', '' ] ], 'order_id', 'a', null, 'member_id')[ 'data' ],
                'deal_num' => $order_model->getOrderCount([ [ 'site_id', '=', $this->site_id ], [ 'promotion_type', '<>', '' ], [ 'pay_status', '=', 1 ] ], 'order_id', 'a', null, 'member_id')[ 'data' ],
                'order_num' => $order_model->getOrderCount([ [ 'site_id', '=', $this->site_id ], [ 'promotion_type', '<>', '' ], [ 'pay_status', '=', 1 ] ], 'order_id')[ 'data' ],
                'order_money' => $order_model->getOrderMoneySum([ [ 'site_id', '=', $this->site_id ], [ 'promotion_type', '<>', '' ], [ 'pay_status', '=', 1 ] ])[ 'data' ],
                'grant_point' => round($account_model->getMemberAccountSum([ [ 'site_id', '=', $this->site_id ], [ 'account_data', '>', 0 ], [ 'from_type', 'not in', [ 'adjust', 'refund', 'pointexchangerefund', 'presale_refund' ] ] ], 'account_data')[ 'data' ])
            ];

            return success(0, '', $data);
        }
    }

    /**
     * 常用功能设置
     */
    public function commonAddonSetting()
    {
        if (request()->isAjax()) {
            $addon = input('addon', '');
            $type = input('type', 'promotion');

            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'app_module', '=', $this->app_module ],
                [ 'config_key', '=', 'COMMON_ADDON' ]
            ];

            $config = new SystemConfig();
            $value = $config->getConfig($condition)[ 'data' ][ 'value' ];
            $addon_array = empty($value) ? [] : explode(',', $value[ $type ] ?? '');

            if (in_array($addon, $addon_array)) {
                $addon_array = array_diff($addon_array, [ $addon ]);
            } else {
                array_push($addon_array, $addon);
            }
            $value[ $type ] = implode(',', $addon_array);
            return $config->setConfig($value, '常用功能设置', 1, $condition);
        }
    }

    /**
     * 活动专区页配置
     * @return mixed
     */
    public function zoneConfig()
    {
        $promotion_model = new PrmotionModel();
        if (request()->isAjax()) {
            $data = [
                'name' => input('name', ''),
                'title' => input('title', ''),
                'bg_color' => input('bg_color', ''), // 背景色
            ];
            $res = $promotion_model->setPromotionZoneConfig($data, $this->site_id, $this->app_module);
            return $res;
        } else {
            $promotion_zone_list = event('PromotionZoneConfig');
            $this->assign('promotion_zone_list', $promotion_zone_list);

            $promotion_config_list = []; // 活动专区页面配置列表
            $config = []; // 第一个活动页面配置

            if (!empty($promotion_zone_list)) {
                foreach ($promotion_zone_list as $k => $v) {
                    $promotion_config_list[ $v[ 'name' ] ] = $promotion_model->getPromotionZoneConfig($v[ 'name' ], $this->site_id, $this->app_module)[ 'data' ][ 'value' ];
                    if ($k == 0) {
                        $config = $promotion_config_list[ $v[ 'name' ] ];
                    }
                }
            }

            $this->assign("config", $config);
            $this->assign("promotion_config_list", $promotion_config_list);

            return $this->fetch("promotion/zone_config");
        }
    }

}