<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\cart;

use addon\coupon\model\Coupon;
use addon\manjian\model\Manjian;
use app\model\goods\Goods;
use app\model\BaseModel;

/**
 * 购物车计算
 *
 * @author Administrator
 *
 */
class CartCalculate extends BaseModel
{

    private $error = 0;  //是否有错误
    private $error_msg = '';  //错误描述


    /**
     * 订单计算
     * @param unknown $data
     */
    public function calculate($data)
    {
        $data = $this->getGoodsList($data);
        $data = $this->manjianPromotion($data);
        $promotion_money = $data[ 'promotion_money' ] ?? 0;
        $order_money = $data[ 'goods_money' ] - $promotion_money;
        $data[ 'order_money' ] = $order_money;

        $data = $this->couponCalculate($data);
        return $data;
    }


    /**
     * 获取购物车商品列表信息
     * @param unknown $cart_ids
     */
    public function getGoodsList($data)
    {
        $site_id = $data[ 'site_id' ];
        $sku_ids = $data[ 'sku_ids' ] ?? [];
        $sku_id_list = array_column($sku_ids, 'sku_id');
        $member_id = $data[ 'member_id' ];
        $sku_num_list = array_column($sku_ids, 'num', 'sku_id');
        $goods_model = new Goods();
        //组装商品列表
        $field = 'ngs.sku_id, ngs.sku_name, ngs.sku_no, ngs.price, ngs.discount_price, ngs.cost_price, ngs.stock, ngs.sku_image, 
            ngs.site_id, ngs.goods_id,ns.site_name,ngs.goods_name';
        $alias = 'ngs';
        $join = [
            [
                'site ns',
                'ngs.site_id = ns.site_id',
                'inner'
            ]
        ];
        $condition = [
            [ 'ngs.sku_id', 'in', $sku_id_list ],
            [ 'ngs.site_id', '=', $site_id ]
        ];

        $goods_list = model('goods_sku')->getList($condition, $field, '', $alias, $join);

        if (!empty($goods_list)) {
            foreach ($goods_list as $k => $v) {

                $item_num = $sku_num_list[ $v[ 'sku_id' ] ];
                $price_result = $goods_model->getGoodsPrice($v[ 'sku_id' ], $member_id);
                $price_info = $price_result[ 'data' ];
                $price = $price_info[ 'price' ];

                $v[ 'price' ] = $price;
                $v[ 'goods_money' ] = $price * $item_num;//todo 这儿要改
                $v[ 'real_goods_money' ] = $v[ 'goods_money' ];
                $v[ 'coupon_money' ] = 0; //优惠券金额
                $v[ 'promotion_money' ] = 0; //优惠金额
                if (!empty($data[ 'goods_list' ])) {
                    $data[ 'goods_list' ][] = $v;
                    $data[ 'goods_num' ] += $item_num;
                    $data[ 'goods_money' ] += $v[ 'goods_money' ];
                } else {
                    $data[ 'site_id' ] = $site_id;
                    $data[ 'site_name' ] = $v[ 'site_name' ];
                    $data[ 'goods_money' ] = $v[ 'goods_money' ];
                    $data[ 'goods_num' ] = $item_num;
                    $data[ 'goods_list' ][] = $v;
                }
            }
        }
        return $data;
    }


    /****************************************************************************** 满减 start *****************************************************************************/
    /**
     * 满减优惠
     * @param $data
     */
    public function manjianPromotion($data)
    {
        $site_id = $data[ 'site_id' ];
        $promotion_money = $data[ 'promotion_money' ] ?? 0;
        //先查询全部商品的满减套餐  进行中
        $manjian_model = new Manjian();
        $all_info = $manjian_model->getManjianInfo([ [ 'manjian_type', '=', 1 ], [ 'site_id', '=', $site_id ], [ 'status', '=', 1 ] ], 'manjian_name,type,goods_ids,rule_json,manjian_id')[ 'data' ] ?? [];
        $goods_list = $data[ 'goods_list' ];
        //存在全场满减(不考虑部分满减情况)
        if (!empty($all_info)) {
            $discount_array = $this->getManjianDiscountMoney($all_info, $data);
            $all_info[ 'discount_array' ] = $discount_array;

            $discount_money = $discount_array[ 'real_discount_money' ];
            $promotion_money += $discount_money;
            if (!empty($discount_array[ 'rule' ])) {
                $goods_list = array_map(function($item) use ($all_info) {
                    $item[ 'promotion' ][ 'manjian' ] = $all_info;
                    return $item;
                }, $goods_list);
            }

        } else {
            $goods_ids = array_unique(array_column($data[ 'goods_list' ], 'goods_id'));

            $manjian_condition = array (
                [ 'goods_id', 'in', $goods_ids ],
                [ 'status', '=', 1 ]
            );
            $manjian_goods_list_result = $manjian_model->getManjianGoodsList($manjian_condition, 'manjian_id');
            $manjian_goods_list = $manjian_goods_list_result[ 'data' ];
            if (!empty($manjian_goods_list)) {
                $discount_money = 0;
                $manjian_goods_list = array_column($manjian_goods_list, 'manjian_id');
                $manjian_goods_list = array_unique($manjian_goods_list); //去重
                sort($manjian_goods_list);
                $manjian_list_result = $manjian_model->getManjianList([ [ 'manjian_id', 'in', $manjian_goods_list ], [ 'status', '=', 1 ] ]);
                $manjian_list = $manjian_list_result[ 'data' ];
                foreach ($manjian_list as $k => $v) {
                    $manjian_goods_ids = explode(',', $v[ 'goods_ids' ]);
                    $item_goods_data = [
                        'goods_money' => 0,
                        'goods_num' => 0
                    ];
                    $item_goods_list = [];
                    $sku_ids = [];
                    foreach ($goods_list as $goods_k => $goods_item) {
                        if (in_array($goods_item[ 'goods_id' ], $manjian_goods_ids)) {
                            $item_goods_data[ 'goods_money' ] += $goods_item[ 'goods_money' ];
                            $item_goods_data[ 'goods_num' ] += $goods_item[ 'num' ];
                            $item_goods_list[] = $goods_item;
                            array_push($sku_ids, $goods_item[ 'sku_id' ]);
                            $goods_list[ $goods_k ] = $v;
                        }
                    }
                    $discount_array = $this->getManjianDiscountMoney($v, $item_goods_data);

                    $manjian_list[ $k ][ 'discount_array' ] = $discount_array;
                    $discount_money += $discount_array[ 'real_discount_money' ];

                    if (!empty($discount_array[ 'rule' ])) {
                        $goods_list = array_map(function($item) use ($sku_ids, $v) {
                            if (in_array($item[ 'sku_id' ], $sku_ids)) {
                                $item[ 'promotion' ][ 'manjian' ] = $v;
                                return $item;
                            }

                        }, $goods_list);
                    }
                }
                $promotion_money += $discount_money;
            }
        }
        $data[ 'goods_list' ] = $goods_list;
        $data[ 'promotion_money' ] = $promotion_money;
        return $data;
    }

    /**
     * 满减优惠金额
     * @param $rule_list
     * @param $goods_money
     */
    public function getManjianDiscountMoney($manjian_info, $data)
    {
        $goods_money = $data[ 'goods_money' ];
        $value = $manjian_info[ 'type' ] == 0 ? $data[ 'goods_money' ] : $data[ 'goods_num' ];

        //阶梯计算优惠
        $rule_item = json_decode($manjian_info[ 'rule_json' ], true);
        $discount_money = 0;
        $money = 0;
        $rule = []; // 符合条件的优惠规则
        array_multisort(array_column($rule_item, 'limit'), SORT_ASC, $rule_item); //排序，根据num 排序
        foreach ($rule_item as $k => $v) {
            if ($value >= $v[ 'limit' ]) {
                $rule = $v;
                if (isset($v[ 'discount_money' ])) {
                    $discount_money = $v[ 'discount_money' ];
                    $money = $v[ 'limit' ];
                }
            }
        }
        $real_discount_money = $discount_money > $goods_money ? $goods_money : $discount_money;
        return [ 'discount_money' => $discount_money, 'money' => $money, 'real_discount_money' => $real_discount_money, 'rule' => $rule ];
    }

    /**
     * 处理商品满减
     */
    public function distributionGoodsDemiscount($goods_list, $goods_money, $discount_money, $is_free_shipping = false, $sku_ids = [])
    {
        $temp_discount_money = $discount_money;
        $last_key = count($goods_list) - 1;
        foreach ($goods_list as $k => $v) {
            if ($last_key != $k) {
                $item_discount_money = round(floor($v[ 'goods_money' ] / $goods_money * $discount_money * 100) / 100, 2);
            } else {
                $item_discount_money = $temp_discount_money;
            }
            $temp_discount_money -= $item_discount_money;
            $goods_list[ $k ][ 'promotion_money' ] = $item_discount_money;
            $real_goods_money = $v[ 'real_goods_money' ] - $item_discount_money;
            $real_goods_money = $real_goods_money < 0 ? 0 : $real_goods_money;
            $goods_list[ $k ][ 'real_goods_money' ] = $real_goods_money; //真实订单项金额
            // 满减送包邮
            if ($is_free_shipping) {
                if (empty($sku_ids) || in_array($v[ 'sku_id' ], $sku_ids)) {
                    $goods_list[ $k ][ 'is_free_shipping' ] = 1;
                }
            }
        }
        return $goods_list;
    }

    /****************************************************************************** 满减 end *****************************************************************************/
    /****************************************************************************** 订单优惠券 start *****************************************************************************/


    /**
     * 查询可用优惠券
     * @param $data
     */
    public function couponCalculate($data)
    {

        $site_id = $data[ 'site_id' ];
        $member_id = $data[ 'member_id' ];
        $goods_money = $data[ 'goods_money' ];//商品总额
        $coupon_list = [];
        $goods_list = $data[ 'goods_list' ];
        //先查询全场通用的优惠券
        $member_coupon_model = new Coupon();
        $all_condition = array (
            [ 'member_id', '=', $member_id ],
            [ 'state', '=', 1 ],
            [ 'site_id', '=', $site_id ],
            [ 'goods_type', '=', 1 ],
            [ 'at_least', '<=', $goods_money ]
        );
        $all_coupon_list = $member_coupon_model->getCouponList($all_condition)[ 'data' ] ?? [];
        foreach ($all_coupon_list as $k => $v) {
            $v[ 'coupon_goods_money' ] = $goods_money;
            $all_coupon_list[ $k ] = $this->getCouponPromotionMoney($v);
        }

        $coupon_list = array_merge($coupon_list, $all_coupon_list);

        $goods_ids = array_column($goods_list, 'goods_id');

        $item_condition = array (
            [ 'member_id', '=', $member_id ],
            [ 'state', '=', 1 ],
            [ 'site_id', '=', $site_id ],
            [ 'goods_type', '=', 2 ],
        );
        $item_like_array = [];
        foreach ($goods_ids as $k => $v) {
            $item_like_array[] = '%,' . $v . ',%';
        }
        $item_condition[] = [ 'goods_ids', 'like', $item_like_array, 'OR' ];
        $item_coupon_list = $member_coupon_model->getCouponList($item_condition)[ 'data' ] ?? [];
        //已领取的优惠券id
        $ed_coupon_ids = array_column(array_merge($item_coupon_list, $all_coupon_list), 'coupon_type_id');
        if (!empty($item_coupon_list)) {
            foreach ($item_coupon_list as $item_k => $item_v) {
                $item_goods_ids = explode(',', $item_v[ 'goods_ids' ]);
                $item_goods_money = 0;
                foreach ($goods_list as $goods_k => $goods_v) {
                    if (in_array($goods_v[ 'goods_id' ], $item_goods_ids)) {
                        $item_goods_money += $goods_v[ 'goods_money' ];
                    }
                }
                if ($item_goods_money >= $item_v[ 'at_least' ]) {
                    $item_v[ 'coupon_goods_money' ] = $item_goods_money;

                    $coupon_list[] = $this->getCouponPromotionMoney($item_v);
                }
            }
        }
        $ing_coupon_condition = array (
            [ 'site_id', '=', $site_id ],
            [ 'status', '=', 1 ],
            [ 'is_show', '=', 1 ]
        );
        if (!empty($ed_coupon_ids)) {
            $ing_coupon_condition[] = [ 'coupon_type_id', 'not in', $ed_coupon_ids ];
        }

        $ing_coupon_list = $member_coupon_model->getCouponTypeList($ing_coupon_condition)[ 'data' ] ?? [];
        if (!empty($ing_coupon_list)) {
            foreach ($ing_coupon_list as $item_k => $item_v) {
                $goods_type = $item_v[ 'goods_type' ];

                if ($goods_type == 1) {//全局支持优惠券
                    $item_goods_money = $goods_money;
                } else {
                    $item_goods_ids = explode(',', $item_v[ 'goods_ids' ]);
                    $item_goods_money = 0;
                    foreach ($goods_list as $goods_k => $goods_v) {
                        if (in_array($goods_v[ 'goods_id' ], $item_goods_ids)) {
                            $item_goods_money += $goods_v[ 'goods_money' ];
                        }
                    }

                }
                if ($item_goods_money >= $item_v[ 'at_least' ]) {
                    $check_result = $member_coupon_model->checkMemberReceiveCoupon([ 'site_id' => $site_id, 'member_id' => $member_id, 'coupon_type_info' => $item_v ]);
                    if ($check_result[ 'code' ] >= 0) {//只有还可领取的优惠券才可以
                        //核验会员是否还可以领取某张优惠券
                        $item_v[ 'receive_type' ] = 'wait';
                        $item_v[ 'coupon_goods_money' ] = $item_goods_money;
                        $coupon_list[] = $this->getCouponPromotionMoney($item_v);
                    }
                }
            }
        }
        //增加查询可以领取的优惠券
        $max_coupon_money = 0;

        foreach ($coupon_list as $k => $v) {
            $item_coupon_money = $v[ 'coupon_money' ] ?? 0;//需修改
            if ($item_coupon_money > $max_coupon_money) {
                $max_coupon_money = $v[ 'coupon_money' ];
                $coupon_info = $v;
            }
        }
        $coupon_money = $coupon_info[ 'coupon_money' ] ?? 0;
        if ($coupon_money > 0) {
            $data[ 'coupon_info' ] = $coupon_info;
            if ($coupon_money > $data[ 'order_money' ]) {
                $coupon_money = $data[ 'order_money' ];
            }
            $data[ 'order_money' ] -= $coupon_money;
            $data[ 'coupon_money' ] = $coupon_money;
        }
        return $data;
    }

    /**
     * 优惠券优惠金额
     * @param $coupon_info
     * @return mixed
     */
    public function getCouponPromotionMoney($coupon_info)
    {
        $coupon_goods_money = $coupon_info[ 'coupon_goods_money' ];//优惠券支持当前商品的总金额
        $coupon_money = 0;
        if ($coupon_info[ 'type' ] == 'reward') { //满减优惠券
            $coupon_money = $coupon_info[ 'money' ] > $coupon_goods_money ? $coupon_goods_money : $coupon_info[ 'money' ];
        } else if ($coupon_info[ 'type' ] == 'divideticket') {   //瓜分优惠券
            $coupon_money = $coupon_info[ 'money' ] > $coupon_goods_money ? $coupon_goods_money : $coupon_info[ 'money' ];
        } else if ($coupon_info[ 'type' ] == 'discount') { //折扣优惠券
            //计算折扣优惠金额
            $coupon_money = $coupon_goods_money * ( 10 - $coupon_info[ 'discount' ] ) / 10;
            $coupon_money = $coupon_money > $coupon_info[ 'discount_limit' ] && $coupon_info[ 'discount_limit' ] != 0 ? $coupon_info[ 'discount_limit' ] : $coupon_money;
            $coupon_money = $coupon_money > $coupon_goods_money ? $coupon_goods_money : $coupon_money;
            $coupon_money = round(floor($coupon_money * 100) / 100, 2);
        }
        $coupon_info[ 'coupon_money' ] = $coupon_money;
        return $coupon_info;

    }

    /****************************************************************************** 订单优惠券 end *****************************************************************************/

}
