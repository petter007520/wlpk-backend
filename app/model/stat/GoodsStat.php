<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\stat;

use app\model\BaseModel;
use app\model\system\Stat;
use Carbon\Carbon;

/**
 * 统计
 * @author Administrator
 *
 */
class GoodsStat extends BaseModel
{
    /**
     * 用于订单(同与订单支付后调用)
     * @param $params
     */
    public function addGoodsStat($params){

        $stat_model = new Stat();

        $result = $stat_model->addShopStat($params);
        return $result;
    }

    /**
     * 商品增加收藏量
     * @param $params
     */
    public function addGoodsCollectStat($params){
        $stat_model = new Stat();

        $result = $stat_model->addShopStat($params);
        return $result;
    }

    /**
     * 增加访问数
     * @param $params
     */
    public function addGoodsVisit($params){
        $member_id = $params['member_id'] ?? 0;
        $goods_id = $params['goods_id'] ?? 0;

        $data = array(
            'site_id' => $params['site_id'],
            'goods_visit_count' => 1
        );
        $stat_model = new Stat();
        $time_region = getDayStartAndEndTime();
        $today_start_time = $time_region['start_time'];
        $today_end_time = $time_region['end_time'];
        if($goods_id > 0){
            $goods_browse_condition = array(
                ['goods_id', '=', $goods_id],
                ['browse_time', 'between', [$today_start_time, $today_end_time]],
            );
            $info = model('goods_browse')->getInfo($goods_browse_condition);
            if(empty($info)){
                $data['goods_visited_type_count'] = 1;
            }
        }

        if($member_id > 0){
            $member_browse_condition = array(
                ['member_id', '=', $member_id],
                ['browse_time', 'between', [$today_start_time, $today_end_time]],
            );
            $info = model('goods_browse')->getInfo($member_browse_condition);
            if(empty($info)){
                $data['goods_visit_member_count'] = 1;
            }
        }
        $result = $stat_model->addShopStat($data);
        return $result;
    }


    /**
     * 商品上架商品统计
     * @param $params
     */
    public function addGoodsOnStat($params){
        $site_id = $params['site_id'];
        //查询当前的已上架商品
        $goods_on_condition = array(
            ['goods_state', '=', 1],
            ['is_delete', '=', 0],
            ['site_id', '=', $site_id]
        );
        $count = model('goods')->getCount($goods_on_condition);
        $carbon = Carbon::now();
        $condition = [
            ['site_id', '=', $site_id],
            ['year', '=', $carbon->year],
            ['month', '=', $carbon->month],
            ['day', '=', $carbon->day],
            ['goods_on_type_count', '>', 0]
        ];

        $info = model('stat_shop')->getInfo($condition, 'goods_on_type_count') ?? [];
        $o_count = $info['goods_on_type_count'] ?? 0;
        $data = [
            'site_id' => $site_id,
        ];
        $diff_count = $count - $o_count;
        if($diff_count != 0){
            $data['goods_on_type_count'] = $diff_count;
            $stat_model = new Stat();
            $result = $stat_model->addShopStat($data);
            return $result;
        }


    }

    /**
     * 销售额统计
     * @param $params
     * @return array
     */
    public function getGoodsOrderMoneyStat($params){
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];

        $condition = array(
            ['o.create_time', 'between', [$start_time, $end_time]],
            ['o.pay_status', '=', 1]
        );
        $site_id = $params['site_id'] ?? 0;
        if($site_id > 0){
            $condition[] = ['o.site_id', '=', $site_id];
        }
        $alias = 'og';
        $join = [
            [
                'order o',
                'o.order_id = og.order_id',
                'inner'
            ],
        ];
        $list =  model('order_goods')->getList($condition, 'sum(og.goods_money) as total_money, og.sku_name, og.goods_id, og.sku_id, og.sku_image', 'total_money desc', $alias, $join, 'og.goods_id', $params['limit']);
        return $this->success($list);
    }

    /**
     * 销售量统计
     * @param $params
     * @return array
     */
    public function getGoodsOrderNumStat($params){
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];

        $condition = array(
            ['o.create_time', 'between', [$start_time, $end_time]],
            ['o.pay_status', '=', 1]
        );
        $site_id = $params['site_id'] ?? 0;
        if($site_id > 0){
            $condition[] = ['o.site_id', '=', $site_id];
        }
        $alias = 'og';
        $join = [
            [
                'order o',
                'o.order_id = og.order_id',
                'inner'
            ],
        ];

        $list =  model('order_goods')->getList($condition, 'sum(og.num) as total_num, og.sku_name, og.goods_id, og.sku_id, og.sku_image', 'total_num desc', $alias, $join, 'og.goods_id', $params['limit']);
        return $this->success($list);
    }
}