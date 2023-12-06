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

use app\model\stat\GoodsStat;
use app\model\system\Stat as StatModel;
use Carbon\Carbon;

/**
 * 数据统计
 * Class Stat
 * @package app\shop\controller
 */
class Stat extends BaseShop
{
    /**
     * 店铺统计
     * @return mixed
     */
    public function shop()
    {
        $this->assign('today', Carbon::today()->toDateString());
        $this->assign('yesterday', Carbon::yesterday()->toDateString());
        return $this->fetch("stat/shop");
    }

    /**
     * 获取时间段内统计数据总和
     */
    public function getStatTotal()
    {
        if (request()->isAjax()) {
            $start_time = input('start_time', strtotime(date('Y-m-d', time())));
            $end_time = input('end_time', time());

            if ($start_time > $end_time) {
                $start_time = input('end_time');
                $end_time = input('start_time');
            }

            $stat_model = new StatModel();
            $data = $stat_model->getShopStatSum($this->site_id, $start_time, $end_time);
            return $data;
        }
    }

    /**
     * 获取天统计趋势数据
     */
    public function getStatData()
    {
        if (request()->isAjax()) {
            $start_time = input('start_time', strtotime(date('Y-m-d', strtotime('-6 day'))));
            $end_time = input('end_time', time());

            if ($start_time > $end_time) {
                $start_time = input('end_time');
                $end_time = input('start_time');
            }

            $stat_model = new StatModel();
            $fields = $stat_model->getStatField();
            $fields[] = 'cashier_order_pay_money';

            $stat_list = $stat_model->getShopStatList($this->site_id, $start_time, $end_time)[ 'data' ];
            $stat_list = array_map(function($item) {
                $item[ 'day_time' ] = date('Y-m-d', $item[ 'day_time' ]);
                return $item;
            }, $stat_list);
            $stat_list = array_column($stat_list, null, 'day_time');

            $day = ceil(( $end_time - $start_time ) / 86400);

            foreach ($fields as $field) {
                $value = [];
                $time = [];
                for ($i = 0; $i < $day; $i++) {
                    $date = date('Y-m-d', $start_time + $i * 86400);
                    $time[] = $date;
                    $value[] = isset($stat_list[ $date ]) && isset($stat_list[ $date ][ $field ]) ? $stat_list[ $date ][ $field ] : 0;
                }
                $data[ $field ] = $value;
                $data[ 'time' ] = $time;
            }
            return $data;
        }
    }

    /**
     * 获取小时统计趋势数据
     */
    public function getStatHourData()
    {
        if (request()->isAjax()) {
            $time = input('start_time', time());
            $carbon = Carbon::createFromTimestamp($time);

            $stat_model = new StatModel();
            $fields = $stat_model->getStatHourField();
            $fields[] = 'cashier_order_pay_money';

            $stat_list = $stat_model->getShopStatHourList($this->site_id, $carbon->year, $carbon->month, $carbon->day)[ 'data' ];

            $data = [];
            $empty = array_map(function() { return 0; }, range(0, 23, 1));
            if (!empty($stat_list)) {
                $stat_list = array_column($stat_list, null, 'hour');
                foreach ($fields as $field) {
                    $value = [];
                    for ($i = 0; $i < 24; $i++) {
                        $value[ $i ] = isset($stat_list[ $i ]) && isset($stat_list[ $i ][ $field ]) ? $stat_list[ $i ][ $field ] : 0;
                    }
                    $data[ $field ] = $value;
                }
            } else {
                foreach ($fields as $field) {
                    $data[ $field ] = $empty;
                }
            }
            $data[ 'time' ] = array_map(function($value) { return $value . '时'; }, range(0, 23, 1));
            return $data;
        }
    }

    /**
     * 商品统计
     * @return mixed
     */
    public function goods()
    {
        $this->assign('today', Carbon::today()->toDateString());
        $this->assign('yesterday', Carbon::yesterday()->toDateString());
        return $this->fetch("stat/goods");
    }

    /**
     * 交易统计
     * @return mixed
     */
    public function order()
    {
        $this->assign('today', Carbon::today()->toDateString());
        $this->assign('yesterday', Carbon::yesterday()->toDateString());
        return $this->fetch("stat/order");
    }

    /**
     * 访问统计
     * @return mixed
     */
    public function visit()
    {
        $this->assign('today', Carbon::today()->toDateString());
        $this->assign('yesterday', Carbon::yesterday()->toDateString());

        $stat_shop_model = new \app\model\system\Stat();
        $today = Carbon::now();
        $yesterday = Carbon::yesterday();
        $stat_today = $stat_shop_model->getStatShop($this->site_id, $today->year, $today->month, $today->day)[ 'data' ];
        $stat_yesterday = $stat_shop_model->getStatShop($this->site_id, $yesterday->year, $yesterday->month, $yesterday->day)[ 'data' ];
        $stat_today[ 'conversion_ratio' ] = $stat_today[ 'visit_count' ] > 0 ? round($stat_today[ 'order_member_count' ] / $stat_today[ 'visit_count' ], 2) : 0;
        $stat_yesterday[ 'conversion_ratio' ] = $stat_yesterday[ 'visit_count' ] > 0 ? round($stat_yesterday[ 'order_member_count' ] / $stat_yesterday[ 'visit_count' ], 2) : 0;
        $this->assign('stat_today', $stat_today);
        $this->assign('stat_yesterday', $stat_yesterday);

        $day_rate = [];
        $day_rate[ 'order_member_count' ] = diff_rate($stat_today[ 'order_member_count' ], $stat_yesterday[ 'order_member_count' ]);
        $day_rate[ 'visit_count' ] = diff_rate($stat_today[ 'visit_count' ], $stat_yesterday[ 'visit_count' ]);
        $day_rate[ 'member_count' ] = diff_rate($stat_today[ 'member_count' ], $stat_yesterday[ 'member_count' ]);
        $day_rate[ 'conversion_ratio' ] = diff_rate($stat_today[ 'conversion_ratio' ], $stat_yesterday[ 'conversion_ratio' ]);
        $this->assign('day_rate', $day_rate);

        return $this->fetch("stat/visit");
    }

    /**
     * 会员统计
     * @return mixed
     */
    public function member()
    {
        $this->assign('today', Carbon::today()->toDateString());
        $this->assign('yesterday', Carbon::yesterday()->toDateString());
        return $this->fetch("stat/member");
    }

    /**
     * 商品排行榜 销量
     * */
    public function countGoodsSale()
    {
        if (request()->isAjax()) {
            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $stat_model = new StatModel();
            $res = $stat_model->getGoodsSaleNumRankingList($this->site_id, $start_time, $end_time, $page_index, $page_size);
            return $res;
        }
    }

    /**
     * 商品排行榜 销售额
     * */
    public function countGoodsSaleMoney()
    {
        if (request()->isAjax()) {
            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $stat_model = new StatModel();
            $res = $stat_model->getGoodsSaleMoneyRankingList($this->site_id, $start_time, $end_time, $page_index, $page_size);
            return $res;
        }
    }

    /**
     * 销售额排行
     * @return array
     */
    public function getGoodsOrderMoneyStat(){
        $goods_stat_model = new GoodsStat();
        $params = array(
            'site_id' => $this->site_id,
            'start_time' => input('start_time', 0),
            'end_time'=> input('end_time', 0),
            'limit' => input('limit', 5)
        );
        $result = $goods_stat_model->getGoodsOrderMoneyStat($params);
        return $result;
    }

    /**
     * 销售量排行
     * @return array
     */
    public function getGoodsOrderNumStat(){
        $goods_stat_model = new GoodsStat();
        $params = array(
            'site_id' => $this->site_id,
            'start_time' => input('start_time', 0),
            'end_time'=> input('end_time', 0),
            'limit' => input('limit', 5)
        );
        $result = $goods_stat_model->getGoodsOrderNumStat($params);
        return $result;
    }
}