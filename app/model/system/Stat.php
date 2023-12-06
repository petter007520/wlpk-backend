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
use app\model\order\OrderCommon;
use app\model\order\OrderRefund;
use app\model\stat\GoodsCartStat;
use app\model\stat\GoodsStat;
use app\model\stat\MemberStat;
use app\model\stat\MemberWithdrawStat;
use app\model\stat\OrderStat;
use app\model\stat\RechargeStat;
use app\model\stat\VisitStat;
use Carbon\Carbon;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;

/**
 * 统计
 * @author Administrator
 *
 */
class Stat extends BaseModel
{
    /**
     * 添加店铺统计(按照天统计)
     * @param array $data
     */
    public function addShopStat($data)
    {
        Log::write('addShopStat' . '-'.date('y-m-d H:i:s', time()).'-'.json_encode($data));
        $site_id = $data[ 'site_id' ];
        $carbon = Carbon::now();
        $condition = [
            'site_id' => $site_id,
            'year' => $carbon->year,
            'month' => $carbon->month,
            'day' => $carbon->day
        ];

        $info = model('stat_shop')->getInfo($condition, 'id');

        //在这里会整体处理总支出   总收入  总预计收入

        $stat_data = $this->getStatData($data);
        if (empty($info)) {
            $insert_data = [
                'site_id' => $site_id,
                'year' => $carbon->year,
                'month' => $carbon->month,
                'day' => $carbon->day,
                'day_time' => time(),
                'create_time' => time()
            ];
            $insert_data = array_merge($insert_data, $stat_data);
            $res = model('stat_shop')->add(
                $insert_data
            );

        } else {
            $update_data = array ();
            if (!empty($stat_data)) {
                foreach ($stat_data as $k => $v) {
                    if ($v > 0) {
                        $update_data[ $k ] = Db::raw($k . '+' . $v);
                    } else if ($v < 0) {
                        $update_data[ $k ] = Db::raw($k . '-' . abs($v));
                    }
                }
            }
            if (!empty($update_data)) {
                $res = Db::name('stat_shop')->where($condition)
                    ->update($update_data);
                Log::write('addShopStat' . Db::name('stat_shop')->getLastSql());
                Cache::tag("cache_table" . "stat_shop")->clear();

            }
        }
        //增加当天时统计
        $this->addShopHourStat($data, $carbon);
        return $this->success($res ?? 0);

    }

    /**
     * 增加当日的时统计记录
     * @param $data
     */
    public function addShopHourStat($data, $carbon)
    {
        $site_id = $data[ 'site_id' ];
//        $carbon    = Carbon::now();
        $condition = [
            'site_id' => $site_id,
            'year' => $carbon->year,
            'month' => $carbon->month,
            'day' => $carbon->day,
            'hour' => $carbon->hour
        ];
        $info = model('stat_shop_hour')->getInfo($condition, 'id');

        //在这里会整体处理总支出   总收入  总预计收入

        $stat_data = $this->getStatData($data);

        if (empty($info)) {
            $insert_data = [
                'site_id' => $site_id,
                'year' => $carbon->year,
                'month' => $carbon->month,
                'day' => $carbon->day,
                'day_time' => time(),
                'create_time' => time(),
                'hour' => $carbon->hour
            ];
            $insert_data = array_merge($insert_data, $stat_data);
            $res = model('stat_shop_hour')->add(
                $insert_data
            );

        } else {
            $update_data = array ();
            if (!empty($stat_data)) {
                foreach ($stat_data as $k => $v) {
                    if ($v > 0) {
                        $update_data[ $k ] = Db::raw($k . '+' . $v);
                    } else if ($v < 0) {
                        $update_data[ $k ] = Db::raw($k . '-' . abs($v));
                    }
                }
            }
            if (!empty($update_data)) {
                $res = Db::name('stat_shop_hour')->where($condition)
                    ->update($update_data);
                Cache::tag("cache_table" . "stat_shop_hour")->clear();
            }

        }
        return $this->success($res ?? 0);
    }

    /**
     * 整理数据
     * @param $data
     * @return mixed
     */
    public function getStatData($data)
    {
        unset($data[ 'site_id' ]);
        $order_pay_money = $data[ 'order_pay_money' ] ?? 0;//订单总额
        $member_recharge_total_money = $data[ 'member_recharge_total_money' ] ?? 0;//会员充值总额
        $member_level_total_money = $data[ 'member_level_total_money' ] ?? 0;//超级会员卡销售额
        $member_giftcard_total_money = $data[ 'member_giftcard_total_money' ] ?? 0;//礼品卡订单总额
        $earnings_total_money = $order_pay_money + $member_recharge_total_money + $member_level_total_money + $member_giftcard_total_money;//预计总收入
        $order_refund_total_money = $data[ 'refund_total' ] ?? 0;//订单退款总额
        $member_withdraw_total_money = $data[ 'member_withdraw_total_money' ] ?? 0;//会员提现总额
        $expenditure_total_money = $order_refund_total_money + $member_withdraw_total_money;//总支出
        $expected_earnings_total_money = $earnings_total_money - $expenditure_total_money;

        Log::write('getStatData' . json_encode([ $earnings_total_money, $expenditure_total_money, $expected_earnings_total_money ]));

        $data[ 'earnings_total_money' ] = $earnings_total_money;
        $data[ 'expenditure_total_money' ] = $expenditure_total_money;
        $data[ 'expected_earnings_total_money' ] = $expected_earnings_total_money;
        $data = array_filter($data);
        return $data;
    }

    /**
     * 获取店铺统计（按照天查询）
     * @param unknown $site_id 0表示平台
     * @param unknown $year
     * @param unknown $month
     * @param unknown $day
     */
    public function getStatShop($site_id, $year, $month, $day)
    {
        $condition = [
            'site_id' => $site_id,
            'year' => $year,
            'month' => $month,
            'day' => $day
        ];
        $info = model('stat_shop')->setIsCache(0)->getInfo($condition, '*');

        if (empty($info)) {
            $condition[ 'day_time' ] = strtotime(date("{$year}-{$month}-{$day}"));
            model('stat_shop')->add($condition);
            $info = model('stat_shop')->getInfo($condition, '*');
        }
        return $this->success($info);
    }

    /**
     * 获取店铺统计信息
     * @param unknown $site_id
     * @param unknown $start_time
     */
    public function getShopStatSum($site_id, $start_time = 0, $end_time = 0)
    {
        $condition = [
            [ 'site_id', '=', $site_id ]
        ];
        if (!empty($start_time)) {
            $condition[] = [ 'day_time', '>=', $start_time ];
        }
        if (!empty($end_time)) {
            $condition[] = [ 'day_time', '<=', $end_time ];
        }
        $field = array_map(function ($field){
            switch ($field) {
                case 'earnings_total_money':
                    return "sum(earnings_total_money) + sum(cashier_billing_money) + sum(cashier_buycard_money) as earnings_total_money";
                    break;
                case 'expenditure_total_money':
                    return "sum(expenditure_total_money) + sum(cashier_refund_money) as expenditure_total_money";
                    break;
                case 'cashier_billing_money':
                    return "sum(cashier_billing_money) + sum(cashier_buycard_money) as cashier_order_pay_money";
                    break;
                case 'refund_total':
                    return "sum(refund_total) + sum(cashier_refund_money) as refund_total";
                    break;
                case 'expected_earnings_total_money':
                    return "sum(expected_earnings_total_money) + sum(cashier_billing_money) + sum(cashier_buycard_money) - sum(cashier_refund_money) as expected_earnings_total_money";
                    break;
                case 'order_pay_count':
                    return "sum(order_pay_count) + sum(cashier_billing_count) + sum(cashier_buycard_count) as order_pay_count";
                    break;
                default:
                    return "sum($field) as $field";
            }
        }, $this->getStatField());
        $info = model('stat_shop')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取店铺统计列表
     * @param unknown $site_id
     * @param unknown $start_time
     */
    public function getShopStatList($site_id, $start_time, $end_time)
    {
        $condition = [
            [ 'site_id', '=', $site_id ],
            [ 'day_time', '>=', $start_time ],
            [ 'day_time', '<=', $end_time ],
        ];
        $list = model('stat_shop')->getList($condition, $this->handleStatField());
        return $this->success($list);
    }

    /**
     * 处理查询字段
     */
    private function handleStatField(){
        $fields = Db::name('stat_shop')->getTableFields('');
        foreach ($fields as $k => $field) {
            switch ($field) {
                case 'earnings_total_money':
                    $fields[ $k ] = "earnings_total_money + cashier_billing_money + cashier_buycard_money as earnings_total_money";
                    break;
                case 'expenditure_total_money':
                    $fields[ $k ] = "expenditure_total_money + cashier_refund_money as expenditure_total_money";
                    break;
                case 'cashier_billing_money':
                    $fields[ $k ] = "cashier_billing_money + cashier_buycard_money as cashier_order_pay_money";
                    break;
                case 'refund_total':
                    $fields[ $k ] = "refund_total + cashier_refund_money as refund_total";
                    break;
                case 'expected_earnings_total_money':
                    $fields[ $k ] = "expected_earnings_total_money + cashier_billing_money + cashier_buycard_money - cashier_refund_money as expected_earnings_total_money";
                    break;
                case 'order_pay_count':
                    $fields[ $k ] = "order_pay_count + cashier_billing_count + cashier_buycard_count as order_pay_count";
                    break;
            }
        }
        return implode(',', $fields);
    }

    /**
     * 获取小时统计数据
     * @param $site_id
     * @param $year
     * @param $month
     * @param $day
     * @return array
     */
    public function getShopStatHourList($site_id, $year, $month, $day)
    {
        $condition = [
            [ 'site_id', '=', $site_id ],
            [ 'year', '=', $year ],
            [ 'month', '=', $month ],
            [ 'day', '=', $day ],
        ];
        $list = model('stat_shop_hour')->getList($condition, $this->handleStatHourField(), 'id desc');
        return $this->success($list);
    }

    /**
     * 处理查询字段
     */
    private function handleStatHourField(){
        $fields = Db::name('stat_shop_hour')->getTableFields('');
        foreach ($fields as $k => $field) {
            switch ($field) {
                case 'earnings_total_money':
                    $fields[ $k ] = "earnings_total_money + cashier_billing_money + cashier_buycard_money as earnings_total_money";
                    break;
                case 'expenditure_total_money':
                    $fields[ $k ] = "expenditure_total_money + cashier_refund_money as expenditure_total_money";
                    break;
                case 'refund_total':
                    $fields[ $k ] = "refund_total + cashier_refund_money as refund_total";
                    break;
                case 'expected_earnings_total_money':
                    $fields[ $k ] = "expected_earnings_total_money + cashier_billing_money + cashier_buycard_money - cashier_refund_money as expected_earnings_total_money";
                    break;
                case 'order_pay_count':
                    $fields[ $k ] = "order_pay_count + cashier_billing_count + cashier_buycard_count as order_pay_count";
                    break;
            }
        }
        $fields[] = 'cashier_billing_money + cashier_buycard_money as cashier_order_pay_money';
        return implode(',', $fields);
    }

    /**
     * 获取天统计表统计字段
     * @return array
     */
    public function getStatField()
    {
        $fields = Db::name('stat_shop')->getTableFields('');
        $fields = array_values(array_diff($fields, [ 'id', 'site_id', 'year', 'month', 'day', 'day_time' ]));
        return $fields;
    }

    /**
     * 获取时统计表统计字段
     * @return array
     */
    public function getStatHourField()
    {
        $fields = Db::name('stat_shop_hour')->getTableFields('');
        $fields = array_values(array_diff($fields, [ 'id', 'site_id', 'year', 'month', 'day', 'hour', 'day_time' ]));
        return $fields;
    }

    /**
     * 获取商品销量排行榜
     * @param $site_id
     * @param string $start_time
     * @param string $end_time
     */
    public function getGoodsSaleNumRankingList($site_id, $start_time, $end_time, $page_index, $page_size)
    {
        $condition = [
            [ 'o.site_id', '=', $site_id ],
            [ 'o.pay_status', '=', 1 ],
            [ 'g.is_delete', '=', 0 ],
            [ 'o.order_status', '<>', OrderCommon::ORDER_CLOSE ],
            [ 'og.refund_status', '<>', OrderRefund::REFUND_COMPLETE ]
        ];
        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ 'o.create_time', '>=', date_to_time($start_time) ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'o.create_time', '<=', date_to_time($end_time) ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'o.create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
        }
        $join = [
            [ 'order o', 'og.order_id = o.order_id', 'left' ],
            [ 'goods g', 'og.goods_id = g.goods_id', 'right' ]
        ];
        $list = model('order_goods')->pageList($condition, 'og.goods_id,g.goods_name,g.goods_state,SUM(og.num) AS sale_num', 'sale_num desc', $page_index, $page_size, 'og', $join, 'og.goods_id');
        return $this->success($list);
    }

    /**
     * 获取商品销量排行榜
     * @param $site_id
     * @param string $start_time
     * @param string $end_time
     * @param $page_index
     * @param $page_size
     * @return array
     */
    public function getGoodsSaleMoneyRankingList($site_id, $start_time, $end_time, $page_index, $page_size)
    {
        $condition = [
            [ 'o.site_id', '=', $site_id ],
            [ 'o.pay_status', '=', 1 ],
            [ 'g.is_delete', '=', 0 ],
            [ 'o.order_status', '<>', OrderCommon::ORDER_CLOSE ],
            [ 'og.refund_status', '<>', OrderRefund::REFUND_COMPLETE ]
        ];
        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ 'o.create_time', '>=', date_to_time($start_time) ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'o.create_time', '<=', date_to_time($end_time) ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'o.create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
        }
        $join = [
            [ 'order o', 'og.order_id = o.order_id', 'left' ],
            [ 'goods g', 'og.goods_id = g.goods_id', 'right' ]
        ];
        $list = model('order_goods')->pageList($condition, 'og.goods_id,g.goods_name,g.goods_state,SUM(o.order_money) AS order_money', 'order_money desc', $page_index, $page_size, 'og', $join, 'og.goods_id');
        return $this->success($list);
    }

    public function switchStat($params)
    {
        $type = $params[ 'type' ];
        $temp_params = $params[ 'data' ];
        $result = event('AddStat', $params, true);
        if (empty($result)) {
            switch ( $type ) {
                case 'order_create'://订单支付
                    $order_stat_model = new OrderStat();
                    $result = $order_stat_model->addOrderCreateStat($temp_params);
                    break;
                case 'order_pay'://订单支付
                    $order_stat_model = new OrderStat();
                    $result = $order_stat_model->addOrderPayStat($temp_params);
                    break;
                case 'order_refund'://退款
                    $order_stat_model = new OrderStat();
                    $result = $order_stat_model->addOrderRefundStat($temp_params);
                    break;
                case 'add_goods'://添加商品
                    $goods_stat_model = new GoodsStat();
                    $result = $goods_stat_model->addGoodsStat($temp_params);
                    break;
                case 'collect_goods':
                    $goods_stat_model = new GoodsStat();
                    $result = $goods_stat_model->addGoodsCollectStat($temp_params);
                    break;
                case 'recharge':
                    $recharge_model = new RechargeStat();
                    $result = $recharge_model->addRechargeStat($temp_params);
                    break;
                case 'visit':
                    $visit_model = new VisitStat();
                    $result = $visit_model->addVisitStat($temp_params);
                    break;
                case 'member_withdraw':
                    $withdraw_model = new MemberWithdrawStat();
                    $result = $withdraw_model->addMemberWithdrawStat($temp_params);
                    break;
                case 'add_member':
                    $member_model = new MemberStat();
                    $result = $member_model->addMemberStat($temp_params);
                    break;
                case 'goods_cart'://购物车加购
                    $goods_cart_stat_model = new GoodsCartStat();
                    $result = $goods_cart_stat_model->addGoodsCartStat($temp_params);
                    break;
                case 'goods_visit'://购物车加购
                    $goods_stat_model = new GoodsStat();
                    $result = $goods_stat_model->addGoodsVisit($temp_params);
                    break;
                case 'goods_on'://上下架
                    $goods_stat_model = new GoodsStat();
                    $result = $goods_stat_model->addGoodsOnStat($temp_params);
                    break;
            }
        }
        return $result;
    }
}