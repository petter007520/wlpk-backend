<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\order;

use app\model\store\Store;

use app\model\member\Member;
use app\model\member\MemberAddress;
use app\model\shop\Shop;
use think\facade\Cache;

/**
 * 订单创建  可调用的工具类
 *
 * @author Administrator
 *
 */
Trait OrderCreateTool
{

    /**
     * 生成订单编号
     *
     * @param array $site_id
     */
    public function createOrderNo($site_id, $member_id = 0)
    {
        $time_str = date('YmdHi');
        $max_no = Cache::get($site_id . '_' . $member_id . '_' . $time_str);
        if (!isset($max_no) || empty($max_no)) {
            $max_no = 1;
        } else {
            $max_no = $max_no + 1;
        }
        $order_no = $time_str . $member_id . sprintf('%03d', $max_no);
        Cache::set($site_id . '_' . $member_id . '_' . $time_str, $max_no);
        return $order_no;
    }

    public function initStore($data)
    {
        $store_id = $data[ 'store_id' ] ?? 0;
        $store_model = new Store();
        $site_id = $data[ 'site_id' ];
        if ($store_id == 0) {

//            $is_allow_store = false;
//            //是否安装门店插件
//            //todo  只有存在门店插件,并且开启门店连锁模式,才可以所以传递store_id, 否则就只有门店配送和本地配送就可以接收store_id
//            if (addon_is_exit('store')) {
//                //查询门店运营插件
//                $store_config_model = new \addon\store\model\Config();
//                $store_config = $store_config_model->getStoreBusinessConfig($site_id)[ 'data' ][ 'value' ] ?? [];
//                if ($store_config[ 'store_business' ] == 'store') {
//                    $is_allow_store = true;
//                }
//            }
//            if($is_allow_store){
//                $store_info = $store_model->getDefaultStore($site_id)[ 'data' ] ?? [];
//                $data[ 'store_info' ] = $store_info;
//                $data[ 'store_id' ] = $store_info[ 'store_id' ];
//            }

        } else {
            $cashier_type = $data[ 'cashier_type' ] ?? '';
            if ($cashier_type == 'cashier') {

            } else {
                $is_allow_store = false;
                //是否安装门店插件
                //todo  只有存在门店插件,并且开启门店连锁模式,才可以所以传递store_id, 否则就只有门店配送和本地配送就可以接收store_id
                if (addon_is_exit('store')) {
                    //查询门店运营插件
                    $store_config_model = new \addon\store\model\Config();
                    $store_config = $store_config_model->getStoreBusinessConfig($site_id)[ 'data' ][ 'value' ] ?? [];
                    if ($store_config[ 'store_business' ] == 'store') {
                        $is_allow_store = true;
                    }
                }
                if (!$is_allow_store) {
                    $delivery_array = $data[ 'delivery' ] ?? [];
                    $delivery_type = $delivery_array[ 'delivery_type' ] ?? 'express';
                    if (!in_array($delivery_type, [ 'local', 'store' ])) {
                        $store_id = 0;
                    }else{
                        $store_id = $data[ 'delivery' ][ 'store_id' ] ?? 0;
                    }
                }
            }
            $store_info = $store_model->getStoreInfo([ [ 'site_id', '=', $site_id ], [ 'store_id', '=', $store_id ] ])[ 'data' ] ?? [];
            if(empty($store_info)){
                $store_id = 0;
            }
            $data[ 'store_info' ] = $store_info;
            $data[ 'store_id' ] = $store_id;


        }

        return $data;

    }

    /**
     * 初始化站点信息
     * @param $data
     * @return mixed
     */
    public function initSiteData($data)
    {
        $site_id = $data[ 'site_id' ];
        $site_model = new Shop();
        $site_condition = array (
            [ 'site_id', '=', $site_id ]
        );
        $site_info = $site_model->getShopInfo($site_condition)[ 'data' ] ?? [];
        $data[ 'site_info' ] = $site_info;
        return $data;
    }

    /**
     * 初始化会员账户
     * @param $data
     * @return mixed
     */
    public function initMemberAccount($data)
    {
        $member_id = $data[ 'member_id' ] ?? 0;
        $site_id = $data[ 'site_id' ];
        if (empty($member_id))
            return $data;

        $member_model = new Member();
        $member_condition = array (
            [ 'member_id', '=', $member_id ],
        );
        $member_info = $member_model->getMemberInfo($member_condition)[ 'data' ] ?? [];
        if (empty($member_info))
            return $data;

        $data[ 'member_info' ] = $member_info;

        return $data;
    }

    /**
     * 初始化收货地址
     * @param unknown $data
     */
    public function initMemberAddress($data)
    {
        $member_address = new MemberAddress();
        $address = $member_address->getMemberAddressInfo([ [ 'member_id', '=', $data[ 'member_id' ] ], [ 'is_default', '=', 1 ] ]);
        $data[ 'member_address' ] = $address[ 'data' ];
        return $data;
    }


    /**
     * 计算商品的单价
     * @param $goods_info
     * @param $data
     */
    public function getGoodsPrice($goods_info, $data)
    {

        $store_id = $data[ 'store_id' ] ?? 0;
        //判断是否存在限时折扣
        //运营模式为平台, 选择门店或本地配送门店,门店价或门店商品统一价会收到折扣价..影响么
        //todo  计算商品的显示折扣价格  门店统一价该怎么处理和折扣价的关系
        if (addon_is_exit('cashier') && $store_id > 0) {
            $price = $goods_info[ 'price' ];//门店线上暂时没有折扣价格
        } else {
            $discount_price = $goods_info[ 'discount_price' ];
            $price = $discount_price;
        }
        //todo  计算当前会员的会员购买价
        $member_result = $this->getGoodsMemberPrice($goods_info, $data);
        if ($member_result[ 'code' ] >= 0) {
            $member_price = $member_result[ 'data' ];
            if ($member_price < $price) {
                $price = $member_price;
            }
        }
        return $this->success($price);
    }

    /**
     * 获取商品会员价格
     * @param $goods_info
     * @param $data
     */
    public function getGoodsMemberPrice($goods_info, $data)
    {
        $store_id = $data[ 'store_id' ] ?? 0;
        $sku_id = $goods_info[ 'sku_id' ];
        $site_id = $data[ 'site_id' ];
        $member_id = $data[ 'member_id' ];
        if ($member_id > 0) {

            $condition = [
                [ 'sku_id', '=', $sku_id ]
            ];
            $goods_sku_info = model('goods_sku')->getInfo($condition, '*');

            if (!empty($goods_sku_info)) {

                if (addon_is_exit("memberprice")) {
                    if ($goods_sku_info[ 'is_consume_discount' ]) {
                        $price = $goods_info[ 'price' ];
                        $alias = 'm';
                        $join = [
                            [ 'member_level ml', 'ml.level_id = m.member_level', 'inner' ],
                        ];
                        $member_info = model("member")->getInfo([ [ 'member_id', '=', $member_id ] ], 'm.member_level,ml.consume_discount', $alias, $join);
                        if (!empty($member_info)) {
                            if ($goods_sku_info[ 'discount_config' ] == 1) {
                                // 自定义优惠
                                $goods_sku_info[ 'member_price' ] = json_decode($goods_sku_info[ 'member_price' ], true);
                                $value = isset($goods_sku_info[ 'member_price' ][ $goods_sku_info[ 'discount_method' ] ][ $member_info[ 'member_level' ] ]) ? $goods_sku_info[ 'member_price' ][ $goods_sku_info[ 'discount_method' ] ][ $member_info[ 'member_level' ] ] : 0;
                                switch ( $goods_sku_info[ 'discount_method' ] ) {
                                    case "discount":
                                        // 打折
                                        if ($value == 0) {
                                            $member_price = $price;
                                        } else
                                            $member_price = number_format($price * $value / 10, 2, '.', '');
                                        break;
                                    case "manjian":
                                        if ($value == 0) {
                                            $member_price = $price;
                                        } else
                                            // 满减
                                            $member_price = number_format($price - $value, 2, '.', '');
                                        break;
                                    case "fixed_price":
                                        if ($value == 0) {
                                            $member_price = $goods_sku_info[ 'price' ];
                                        } else
                                            // 指定价格
                                            $member_price = number_format($value, 2, '.', '');
                                        break;
                                }
                            } else {
                                // 默认按会员享受折扣计算
                                $member_price = number_format($price * $member_info[ 'consume_discount' ] / 100, 2, '.', '');
                            }
                            return $this->success($member_price);
                        }

                    }
                }
            }
        }
        return $this->error();
    }


    /**
     * 扣除商品库存
     * @param $params
     */
    public function skuDecStock($goods_info, $store_id = 0)
    {
        $goods_class = $goods_info[ 'goods_class' ] ?? 0;
        if (!empty($goods_class)) {
            if (in_array($goods_class, [ 1, 2, 3, 4, 5 ])) {
                $order_stock = new OrderStock();
                $stock_result = $order_stock->decOrderSaleStock($goods_info[ 'sku_id' ], $goods_info[ 'num' ], $store_id);
                if ($stock_result[ 'code' ] < 0) {
                    return $stock_result;
                }
            }
        }
        return $this->success();
    }


    /**
     * 次卡优惠计算
     * @param $goods_info
     */
    public function cardCalculate($goods_info)
    {
        $goods_money = $goods_info[ 'goods_money' ];
        //次卡抵扣优惠
    }


    /**
     * 补齐门店数据
     * @param $data
     */
    public function storeOrderData($shop_goods, $data)
    {
        $temp_data = [];
        $delivery_store_id = $shop_goods[ 'delivery' ][ 'store_id' ] ?? 0; //门店id

        if ($delivery_store_id > 0) {
            $store_model = new Store();
            $condition = array (
                [ 'store_id', '=', $delivery_store_id ],
                [ 'site_id', '=', $shop_goods[ 'site_id' ] ],
                [ 'status', '=', 1 ],
                [ 'is_pickup', '=', 1 ],
            );
            $store_info_result = $store_model->getStoreInfo($condition);
            $store_info = $store_info_result[ 'data' ] ?? [];
            if (empty($store_info)) {
                $this->setError(1, '当前门店不存在或未开启!');
            } else {
                $temp_data[ 'delivery_store_id' ] = $delivery_store_id;
                $delivery_store_name = $store_info_result[ 'data' ][ 'store_name' ];
                $temp_data[ 'delivery_store_name' ] = $delivery_store_name;
                $delivery_store_info = array (
                    'open_date' => $store_info[ 'open_date' ],
                    'full_address' => $store_info[ 'full_address' ] . $store_info[ 'address' ],
                    'longitude' => $store_info[ 'longitude' ],
                    'latitude' => $store_info[ 'latitude' ],
                    'telphone' => $store_info[ 'telphone' ],
                    'store_image' => $store_info[ 'store_image' ],
                    'time_type' => $store_info[ 'time_type' ],
                    'time_week' => $store_info[ 'time_week' ],
                    'start_time' => $store_info[ 'start_time' ],
                    'end_time' => $store_info[ 'end_time' ],
                );
                $temp_data[ 'delivery_store_info' ] = json_encode($delivery_store_info, JSON_UNESCAPED_UNICODE);

                $goods_list = $shop_goods[ 'goods_list' ];

                //核验门店库存  todo  新的库存方式
//                if (addon_is_exit('store', $data['site_id'])) {
//                    foreach ($goods_list as $k => $v) {
//                        //检测门店库存
//                        $store_goods_sklu_model = new StoreGoodsSku();
//                        $item_sku_params = array(
//                            'store_id' => $delivery_store_id,
//                            'goods_id' => $v['goods_id'],
//                            'sku_id' => $v['sku_id'],
//                            'site_id' => $data['site_id'],
//                            'num' => $v['num']
//                        );
//                        $result = $store_goods_sklu_model->checkStoreGoodsSkuStock($item_sku_params);
//                        if ($result['code'] < 0) {
//                            //todo  定义一个高优先级的提示窗口
//                            //error  提示
//                            $this->setError(1, $delivery_store_name.'(门店)'.'商品'.$v['sku_name'].$result['message'], 1);
//                        }
//                    }
//                }
            }
        } else {
            $this->setError(1, '配送门店不可为空!');
        }
        return array_merge($shop_goods, $temp_data);
    }


    /**
     * 设置错误,优先级
     * @param $error
     * @param string $priority 报错优先级  0  创建时提示  1 计算时提示
     */
    public function setError($error, $error_msg, $priority = '0')
    {
        $this->error = $error;
        $this->error_msg = $error_msg;
        if ($priority == 1) {
            $this->error_show = true;
        }
    }
}
