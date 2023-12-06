<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shopapi\controller;

use app\model\system\Stat as StatModel;
use Carbon\Carbon;

class Statistics extends BaseApi
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
     * 店铺统计
     * @return mixed
     */
    public function shop()
    {
        $date_type = isset($this->params[ 'date_type' ]) ? $this->params[ 'date_type' ] : 0;
        if ($date_type == 0) {
            $start_time = strtotime("today");
            $time_range = date('Y-m-d', $start_time);
        } else if ($date_type == 1) {
            $start_time = strtotime(date('Y-m-d', strtotime("-6 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
        } else if ($date_type == 2) {
            $start_time = strtotime(date('Y-m-d', strtotime("-29 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
        }

        $stat_model = new StatModel();

        $shop_stat_sum = $stat_model->getShopStatSum($this->site_id, $start_time);

        $shop_stat_sum[ 'data' ][ 'time_range' ] = $time_range;

        return $this->response($shop_stat_sum);
    }

    /**
     * 店铺统计报表
     * */
    public function getShopStatList()
    {
        $date_type = isset($this->params[ 'date_type' ]) ? $this->params[ 'date_type' ] : 1;
        if ($date_type == 1) {
            $start_time = strtotime(date('Y-m-d', strtotime("-6 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
            $day = 6;
        } else if ($date_type == 2) {
            $start_time = strtotime(date('Y-m-d', strtotime("-29 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
            $day = 29;
        }

        $stat_model = new StatModel();

        $stat_list = $stat_model->getShopStatList($this->site_id, $start_time, time());

        //将时间戳作为列表的主键
        $shop_stat_list = array_column($stat_list[ 'data' ], null, 'day_time');

        $data = array ();

        for ($i = 0; $i <= $day; $i++) {
            $time = strtotime(date('Y-m-d', strtotime("-" . ( $day - $i ) . " day")));
            $data[ 'time' ][ $i ] = date('Y-m-d', $time);
            if (array_key_exists($time, $shop_stat_list)) {
                $data[ 'order_total' ][ $i ] = $shop_stat_list[ $time ][ 'order_total' ];
                $data[ 'shipping_total' ][ $i ] = $shop_stat_list[ $time ][ 'shipping_total' ];
                $data[ 'refund_total' ][ $i ] = $shop_stat_list[ $time ][ 'refund_total' ];
                $data[ 'order_pay_count' ][ $i ] = $shop_stat_list[ $time ][ 'order_pay_count' ];
                $data[ 'goods_pay_count' ][ $i ] = $shop_stat_list[ $time ][ 'goods_pay_count' ];
                $data[ 'shop_money' ][ $i ] = $shop_stat_list[ $time ][ 'shop_money' ];
                $data[ 'platform_money' ][ $i ] = $shop_stat_list[ $time ][ 'platform_money' ];
                $data[ 'collect_shop' ][ $i ] = $shop_stat_list[ $time ][ 'collect_shop' ];
                $data[ 'collect_goods' ][ $i ] = $shop_stat_list[ $time ][ 'collect_goods' ];
                $data[ 'visit_count' ][ $i ] = $shop_stat_list[ $time ][ 'visit_count' ];
                $data[ 'order_count' ][ $i ] = $shop_stat_list[ $time ][ 'order_count' ];
                $data[ 'goods_count' ][ $i ] = $shop_stat_list[ $time ][ 'goods_count' ];
                $data[ 'add_goods_count' ][ $i ] = $shop_stat_list[ $time ][ 'add_goods_count' ];
                $data[ 'member_count' ][ $i ] = $shop_stat_list[ $time ][ 'member_count' ];
            } else {
                $data[ 'order_total' ][ $i ] = 0.00;
                $data[ 'shipping_total' ][ $i ] = 0.00;
                $data[ 'refund_total' ][ $i ] = 0.00;
                $data[ 'order_pay_count' ][ $i ] = 0;
                $data[ 'goods_pay_count' ][ $i ] = 0;
                $data[ 'shop_money' ][ $i ] = 0.00;
                $data[ 'platform_money' ][ $i ] = 0.00;
                $data[ 'collect_shop' ][ $i ] = 0;
                $data[ 'collect_goods' ][ $i ] = 0;
                $data[ 'visit_count' ][ $i ] = 0;
                $data[ 'order_count' ][ $i ] = 0;
                $data[ 'goods_count' ][ $i ] = 0;
                $data[ 'add_goods_count' ][ $i ] = 0;
                $data[ 'member_count' ][ $i ] = 0;
            }
        }

        $data[ 'time_range' ] = $time_range;

        return $this->response($this->success($data));
    }

    /**
     * 商品统计
     * @return mixed
     */
    public function goods()
    {
        $date_type = isset($this->params[ 'date_type' ]) ? $this->params[ 'date_type' ] : 0;
        if ($date_type == 0) {
            $start_time = strtotime("today");
            $time_range = date('Y-m-d', $start_time);
        } else if ($date_type == 1) {
            $start_time = strtotime("-6 day");
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
        } else if ($date_type == 2) {
            $start_time = strtotime("-29 day");
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
        }

        $stat_model = new StatModel();

        $shop_stat_sum = $stat_model->getShopStatSum($this->site_id, $start_time);

        $shop_stat_sum[ 'data' ][ 'time_range' ] = $time_range;

        return $this->response($shop_stat_sum);

    }

    /**
     * 商品统计报表
     * */
    public function getGoodsStatList()
    {
        $date_type = isset($this->params[ 'date_type' ]) ? $this->params[ 'date_type' ] : 1;
        if ($date_type == 1) {
            $start_time = strtotime("-6 day");
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
            $day = 6;
        } else if ($date_type == 2) {
            $start_time = strtotime("-29 day");
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
            $day = 29;
        }

        $stat_model = new StatModel();
        $stat_list = $stat_model->getShopStatList($this->site_id, $start_time, time());
        //将时间戳作为列表的主键
        $shop_stat_list = array_column($stat_list[ 'data' ], null, 'day_time');

        $data = array ();
        for ($i = 0; $i <= $day; $i++) {
            $time = strtotime(date('Y-m-d', strtotime("-" . ( $day - $i ) . " day")));
            $data[ 'time' ][ $i ] = date('Y-m-d', $time);
            if (array_key_exists($time, $shop_stat_list)) {
                $data[ 'order_total' ][ $i ] = $shop_stat_list[ $time ][ 'order_total' ];
                $data[ 'shipping_total' ][ $i ] = $shop_stat_list[ $time ][ 'shipping_total' ];
                $data[ 'refund_total' ][ $i ] = $shop_stat_list[ $time ][ 'refund_total' ];
                $data[ 'order_pay_count' ][ $i ] = $shop_stat_list[ $time ][ 'order_pay_count' ];
                $data[ 'goods_pay_count' ][ $i ] = $shop_stat_list[ $time ][ 'goods_pay_count' ];
                $data[ 'shop_money' ][ $i ] = $shop_stat_list[ $time ][ 'shop_money' ];
                $data[ 'platform_money' ][ $i ] = $shop_stat_list[ $time ][ 'platform_money' ];
                $data[ 'collect_shop' ][ $i ] = $shop_stat_list[ $time ][ 'collect_shop' ];
                $data[ 'collect_goods' ][ $i ] = $shop_stat_list[ $time ][ 'collect_goods' ];
                $data[ 'visit_count' ][ $i ] = $shop_stat_list[ $time ][ 'visit_count' ];
                $data[ 'order_count' ][ $i ] = $shop_stat_list[ $time ][ 'order_count' ];
                $data[ 'goods_count' ][ $i ] = $shop_stat_list[ $time ][ 'goods_count' ];
                $data[ 'add_goods_count' ][ $i ] = $shop_stat_list[ $time ][ 'add_goods_count' ];
                $data[ 'member_count' ][ $i ] = $shop_stat_list[ $time ][ 'member_count' ];
            } else {
                $data[ 'order_total' ][ $i ] = 0.00;
                $data[ 'shipping_total' ][ $i ] = 0.00;
                $data[ 'refund_total' ][ $i ] = 0.00;
                $data[ 'order_pay_count' ][ $i ] = 0;
                $data[ 'goods_pay_count' ][ $i ] = 0;
                $data[ 'shop_money' ][ $i ] = 0.00;
                $data[ 'platform_money' ][ $i ] = 0.00;
                $data[ 'collect_shop' ][ $i ] = 0;
                $data[ 'collect_goods' ][ $i ] = 0;
                $data[ 'visit_count' ][ $i ] = 0;
                $data[ 'order_count' ][ $i ] = 0;
                $data[ 'goods_count' ][ $i ] = 0;
                $data[ 'add_goods_count' ][ $i ] = 0;
                $data[ 'member_count' ][ $i ] = 0;
            }
        }
        $data[ 'time_range' ] = $time_range;
        return $this->response($this->success($data));
    }


    /**
     * 订单金额及订单数量统计（7、15、30天）
     * @return mixed
     */
    public function orderStatistics()
    {
        $day = isset($this->params[ 'day' ]) ? $this->params[ 'day' ] : 7;

        $stat_shop_model = new StatModel();
        //近十天的订单数以及销售金额
        $date_day = getweeks($day);

        $data = [
            'order_total' => [],
            'order_pay_count' => []
        ];

        for ($i = 1; $i <= $day; $i++) {
            $time             = strtotime(date('Y-m-d', strtotime("-" . ($day - $i) . " day")));
            $date = date('Y-m-d', $time);
            $dayarr = explode('-', $date);

            $stat_data = $stat_shop_model->getStatShop($this->site_id, $dayarr[ 0 ], $dayarr[ 1 ], $dayarr[ 2 ]);
            $stat_data = $stat_data[ 'data' ];

            array_push($data['order_total'], $stat_data[ 'order_total' ]);
            array_push($data['order_pay_count'], $stat_data[ 'order_pay_count' ]);
        }

        return $this->response($this->success($data));
    }

    /**
     * 交易统计
     * @return mixed
     */
    public function order()
    {
        $date_type = isset($this->params[ 'date_type' ]) ? $this->params[ 'date_type' ] : 0;
        if ($date_type == 0) {
            $start_time = strtotime("today");
            $time_range = date('Y-m-d', $start_time);
        } else if ($date_type == 1) {
            $start_time = strtotime(date('Y-m-d', strtotime("-6 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
        } else if ($date_type == 2) {
            $start_time = strtotime(date('Y-m-d', strtotime("-29 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
        }

        $stat_model = new StatModel();

        $shop_stat_sum = $stat_model->getShopStatSum($this->site_id, $start_time);

        $shop_stat_sum[ 'data' ][ 'time_range' ] = $time_range;

        return $this->response($shop_stat_sum);
    }

    /**
     * 交易统计报表
     * */
    public function getOrderStatList()
    {
        $date_type = isset($this->params[ 'date_type' ]) ? $this->params[ 'date_type' ] : 1;
        if ($date_type == 1) {
            $start_time = strtotime(date('Y-m-d', strtotime("-6 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
            $day = 6;
        } else if ($date_type == 2) {
            $start_time = strtotime(date('Y-m-d', strtotime("-29 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
            $day = 29;
        }

        $stat_model = new StatModel();

        $stat_list = $stat_model->getShopStatList($this->site_id, $start_time, time());

        //将时间戳作为列表的主键
        $shop_stat_list = array_column($stat_list[ 'data' ], null, 'day_time');

        $data = array ();

        for ($i = 0; $i <= $day; $i++) {
            $time = strtotime(date('Y-m-d', strtotime("-" . ( $day - $i ) . " day")));
            $data[ 'time' ][ $i ] = date('Y-m-d', $time);
            if (array_key_exists($time, $shop_stat_list)) {
                $data[ 'order_total' ][ $i ] = $shop_stat_list[ $time ][ 'order_total' ];
                $data[ 'shipping_total' ][ $i ] = $shop_stat_list[ $time ][ 'shipping_total' ];
                $data[ 'refund_total' ][ $i ] = $shop_stat_list[ $time ][ 'refund_total' ];
                $data[ 'order_pay_count' ][ $i ] = $shop_stat_list[ $time ][ 'order_pay_count' ];
                $data[ 'goods_pay_count' ][ $i ] = $shop_stat_list[ $time ][ 'goods_pay_count' ];
                $data[ 'shop_money' ][ $i ] = $shop_stat_list[ $time ][ 'shop_money' ];
                $data[ 'platform_money' ][ $i ] = $shop_stat_list[ $time ][ 'platform_money' ];
                $data[ 'collect_shop' ][ $i ] = $shop_stat_list[ $time ][ 'collect_shop' ];
                $data[ 'collect_goods' ][ $i ] = $shop_stat_list[ $time ][ 'collect_goods' ];
                $data[ 'visit_count' ][ $i ] = $shop_stat_list[ $time ][ 'visit_count' ];
                $data[ 'order_count' ][ $i ] = $shop_stat_list[ $time ][ 'order_count' ];
                $data[ 'goods_count' ][ $i ] = $shop_stat_list[ $time ][ 'goods_count' ];
                $data[ 'add_goods_count' ][ $i ] = $shop_stat_list[ $time ][ 'add_goods_count' ];
            } else {
                $data[ 'order_total' ][ $i ] = 0.00;
                $data[ 'shipping_total' ][ $i ] = 0.00;
                $data[ 'refund_total' ][ $i ] = 0.00;
                $data[ 'order_pay_count' ][ $i ] = 0;
                $data[ 'goods_pay_count' ][ $i ] = 0;
                $data[ 'shop_money' ][ $i ] = 0.00;
                $data[ 'platform_money' ][ $i ] = 0.00;
                $data[ 'collect_shop' ][ $i ] = 0;
                $data[ 'collect_goods' ][ $i ] = 0;
                $data[ 'visit_count' ][ $i ] = 0;
                $data[ 'order_count' ][ $i ] = 0;
                $data[ 'goods_count' ][ $i ] = 0;
                $data[ 'add_goods_count' ][ $i ] = 0;
            }
        }

        $data[ 'time_range' ] = $time_range;
        return $this->response($this->success($data));
    }


    /**
     * 访问统计
     * @return mixed
     */
    public function visit()
    {
        $date_type = isset($this->params[ 'date_type' ]) ? $this->params[ 'date_type' ] : 0;

        if ($date_type == 0) {
            $start_time = strtotime("today");
            $time_range = date('Y-m-d', $start_time);
        } else if ($date_type == 1) {
            $start_time = strtotime(date('Y-m-d', strtotime("-6 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
        } else if ($date_type == 2) {
            $start_time = strtotime(date('Y-m-d', strtotime("-29 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
        }

        $stat_model = new StatModel();

        $shop_stat_sum = $stat_model->getShopStatSum($this->site_id, $start_time);

        $shop_stat_sum[ 'data' ][ 'time_range' ] = $time_range;

        return $this->response($shop_stat_sum);

    }

    /**
     * 访问统计报表
     * */
    public function getVisitStatList()
    {
        $date_type = isset($this->params[ 'date_type' ]) ? $this->params[ 'date_type' ] : 1;

        if ($date_type == 1) {
            $start_time = strtotime(date('Y-m-d', strtotime("-6 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
            $day = 6;
        } else if ($date_type == 2) {
            $start_time = strtotime(date('Y-m-d', strtotime("-29 day")));
            $time_range = date('Y-m-d', $start_time) . ' 至 ' . date('Y-m-d', strtotime("today"));
            $day = 29;
        }

        $stat_model = new StatModel();

        $stat_list = $stat_model->getShopStatList($this->site_id, $start_time, time());

        //将时间戳作为列表的主键
        $shop_stat_list = array_column($stat_list[ 'data' ], null, 'day_time');

        $data = array ();

        for ($i = 0; $i <= $day; $i++) {
            $time = strtotime(date('Y-m-d', strtotime("-" . ( $day - $i ) . " day")));
            $data[ 'time' ][ $i ] = date('Y-m-d', $time);
            if (array_key_exists($time, $shop_stat_list)) {
                $data[ 'order_total' ][ $i ] = $shop_stat_list[ $time ][ 'order_total' ];
                $data[ 'shipping_total' ][ $i ] = $shop_stat_list[ $time ][ 'shipping_total' ];
                $data[ 'refund_total' ][ $i ] = $shop_stat_list[ $time ][ 'refund_total' ];
                $data[ 'order_pay_count' ][ $i ] = $shop_stat_list[ $time ][ 'order_pay_count' ];
                $data[ 'goods_pay_count' ][ $i ] = $shop_stat_list[ $time ][ 'goods_pay_count' ];
                $data[ 'shop_money' ][ $i ] = $shop_stat_list[ $time ][ 'shop_money' ];
                $data[ 'platform_money' ][ $i ] = $shop_stat_list[ $time ][ 'platform_money' ];
                $data[ 'collect_shop' ][ $i ] = $shop_stat_list[ $time ][ 'collect_shop' ];
                $data[ 'collect_goods' ][ $i ] = $shop_stat_list[ $time ][ 'collect_goods' ];
                $data[ 'visit_count' ][ $i ] = $shop_stat_list[ $time ][ 'visit_count' ];
                $data[ 'order_count' ][ $i ] = $shop_stat_list[ $time ][ 'order_count' ];
                $data[ 'goods_count' ][ $i ] = $shop_stat_list[ $time ][ 'goods_count' ];
                $data[ 'add_goods_count' ][ $i ] = $shop_stat_list[ $time ][ 'add_goods_count' ];
            } else {
                $data[ 'order_total' ][ $i ] = 0.00;
                $data[ 'shipping_total' ][ $i ] = 0.00;
                $data[ 'refund_total' ][ $i ] = 0.00;
                $data[ 'order_pay_count' ][ $i ] = 0;
                $data[ 'goods_pay_count' ][ $i ] = 0;
                $data[ 'shop_money' ][ $i ] = 0.00;
                $data[ 'platform_money' ][ $i ] = 0.00;
                $data[ 'collect_shop' ][ $i ] = 0;
                $data[ 'collect_goods' ][ $i ] = 0;
                $data[ 'visit_count' ][ $i ] = 0;
                $data[ 'order_count' ][ $i ] = 0;
                $data[ 'goods_count' ][ $i ] = 0;
                $data[ 'add_goods_count' ][ $i ] = 0;
            }
        }
        $data[ 'time_range' ] = $time_range;
        return $this->response($this->success($data));
    }

    /**
     * 获取时间段内统计数据总和
     */
    public function getStatTotal()
    {
        $start_time = $this->params['start_time'] ?? strtotime(date('Y-m-d', time()));
        $end_time = $this->params['end_time'] ?? time();

        if ($start_time > $end_time) {
            $start_time = $this->params['end_time'];
            $end_time = $this->params['start_time'];
        }

        $stat_model = new StatModel();
        $data = $stat_model->getShopStatSum($this->site_id, $start_time, $end_time);
        return $this->response($data);
    }

    /**
     * 获取天统计趋势数据
     */
    public function getStatData()
    {
        $start_time = $this->params['start_time'] ?? strtotime(date('Y-m-d', strtotime('-6 day')));
        $end_time = $this->params['end_time'] ?? time();

        if ($start_time > $end_time) {
            $start_time = $this->params['end_time'];
            $end_time = $this->params['start_time'];
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
        return $this->response($this->success($data));
    }

    /**
     * 获取小时统计趋势数据
     */
    public function getStatHourData()
    {
        $time = $this->params['start_time'] ?? time();
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
                    $value[ $i ] = isset($stat_list[ $i ][ $field ]) && isset($stat_list[ $i ][ $field ]) ? $stat_list[ $i ][ $field ] : 0;
                }
                $data[ $field ] = $value;
            }
        } else {
            foreach ($fields as $field) {
                $data[ $field ] = $empty;
            }
        }
        $data[ 'time' ] = array_map(function($value) { return $value . '时'; }, range(0, 23, 1));
        return $this->response($this->success($data));
    }

    /**
     * 商品排行榜 销量
     * */
    public function countGoodsSale()
    {
        $start_time = $this->params['start_time'] ?? '';
        $end_time = $this->params['end_time'] ?? '';

        if ($start_time > $end_time) {
            $start_time = $this->params['end_time'];
            $end_time = $this->params['start_time'];
        }

        $stat_model = new StatModel();
        $res = $stat_model->getGoodsSaleNumRankingList($this->site_id, $start_time, $end_time, 1, 5);
        return $this->response($res);
    }

    /**
     * 商品排行榜 销售额
     * */
    public function countGoodsSaleMoney()
    {
        $start_time = $this->params['start_time'] ?? '';
        $end_time = $this->params['end_time'] ?? '';

        if ($start_time > $end_time) {
            $start_time = $this->params['end_time'];
            $end_time = $this->params['start_time'];
        }

        $stat_model = new StatModel();
        $res = $stat_model->getGoodsSaleMoneyRankingList($this->site_id, $start_time, $end_time, 1, 5);
        return $this->response($res);
    }
}