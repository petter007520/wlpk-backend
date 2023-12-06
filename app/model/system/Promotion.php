<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\system;

use app\model\BaseModel;
use app\model\system\Config as ConfigModel;

/**
 * 活动整体管理
 */
class Promotion extends BaseModel
{
    /**
     * 获取营销活动展示
     */
    public function getPromotions()
    {
        $show = event("ShowPromotion", []);
        $shop_promotion = [];
        foreach ($show as $k => $v) {
            if (!empty($v[ 'shop' ])) {
                $shop_promotion = array_merge($shop_promotion, $v[ 'shop' ]);
            }
        }
        return [
            'shop' => $shop_promotion
        ];
    }

    /**
     * 获取站点营销活动展示
     * @param $site_id
     */
    public function getSitePromotions($site_id)
    {
        $show = event("ShowPromotion", []);
        $promotion = [];
        foreach ($show as $k => $v) {
            if (!empty($v[ 'shop' ])) {
                $promotion = array_merge($promotion, $v[ 'shop' ]);
            }
        }
        return $promotion;
    }


    /**
     * 获取营销类型
     */
    public function getPromotionType()
    {
        $promotion_type = event("PromotionType");
        $promotion_type[] = [ "type" => "empty", "name" => "无营销活动" ];
        return $promotion_type;
    }

    /**
     * 获取营销活动总数
     */
    public function getPromotionCount($site_id)
    {
        $show = event("ShowPromotion", [ 'count' => 1, 'site_id' => $site_id ]);
        $count = 0;
        foreach ($show as $k => $v) {
            if (!empty($v[ 'shop' ])) {
                $summary = $v[ 'shop' ][ 'summary' ] ?? [];
                if (!empty($summary)) {
                    $count += $summary[ 'count' ];
                }
            }
            if (!empty($v[ 'member' ])) {
                $summary = $v[ 'member' ][ 'summary' ] ?? [];
                if (!empty($summary)) {
                    $count += $summary[ 'count' ];
                }
            }

        }
        return $count;
    }

    /**
     * 输入时间查看活动营销概况
     * @param $start_time
     * @param $end_time
     */
    public function getPromotionSummary($start_time, $end_time, $site_id)
    {
        $summary = event("ShowPromotion", [ 'summary' => 1, 'start_time' => $start_time, 'end_time' => $end_time, 'site_id' => $site_id ]);
        $promotion = [
            'time_limit' => [], // 限时类活动
            'unlimited_time' => [], // 不限时类的活动
            'promotion_num' => 0, // 活动数量
            'in_progress_num' => 0 // 进行中活动数量
        ];
        foreach ($summary as $k => $v) {
            $shop = $v[ 'shop' ][ 0 ] ?? [];
            if (empty($shop)) continue;
            $promotion[ 'promotion_num' ] += 1;

            $summary_v = $shop[ 'summary' ] ?? [];
            if (isset($summary_v[ 'time_limit' ])) {
                unset($shop[ 'summary' ]);
                array_push($promotion[ 'time_limit' ], array_merge($shop, $summary_v[ 'time_limit' ]));
                $promotion[ 'in_progress_num' ] += $summary_v[ 'time_limit' ][ 'count' ];
            }
            if (isset($summary_v[ 'unlimited_time' ])) {
                unset($shop[ 'summary' ]);
                array_push($promotion[ 'unlimited_time' ], array_merge($shop, $summary_v[ 'unlimited_time' ]));
//                if ($summary_v['unlimited_time']['status']) $promotion['in_progress_num'] += 1;
            }
        }
        return $this->success($promotion);
    }

    /**
     * 设置活动专区页面配置
     * @param $data
     * @param $site_id
     * @param $app_module
     * @return array
     */
    public function setPromotionZoneConfig($data, $site_id, $app_module = 'shop')
    {
        $config = new ConfigModel();
        $config_key = strtoupper($data[ 'name' ]) . '_ZONE_CONFIG';
        $res = $config->setConfig($data, $data[ 'title' ] . '活动专区页面配置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', $config_key ] ]);
        return $res;
    }

    /**
     * 获取活动专区页面配置
     * @param $name
     * @param $config_key
     * @param $site_id
     * @param $app_module
     * @return array
     */
    public function getPromotionZoneConfig($name, $site_id, $app_module = 'shop')
    {
        $config = new ConfigModel();
        $config_key = strtoupper($name) . '_ZONE_CONFIG';
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', $config_key ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $promotion_zone_config = event('PromotionZoneConfig', [ 'name' => $name ], true);
            $res[ 'data' ][ 'value' ] = $promotion_zone_config[ 'value' ];
        }
        return $res;
    }

}