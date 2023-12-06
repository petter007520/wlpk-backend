<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\goods;

use addon\discount\model\Discount;
use app\model\BaseModel;
use app\model\order\Order;
use app\model\order\OrderRefund;
use app\model\storegoods\StoreGoods;
use app\model\storegoods\StoreSale;
use app\model\system\Config as ConfigModel;
use app\model\system\Cron;
use app\model\system\Stat;
use think\facade\Db;
use think\facade\Cache;

/**
 * 商品
 */
class Goods extends BaseModel
{

    private $goods_class = array ( 'id' => 1, 'name' => '实物商品' );

    private $goods_state = array (
        1 => '销售中',
        0 => '仓库中'
    );

    public function getGoodsState()
    {
        return $this->goods_state;
    }

    public function getGoodsClass()
    {
        return $this->goods_class;
    }

    /**
     * 商品添加
     * @param $data
     */
    public function addGoods($data)
    {
        model('goods')->startTrans();

        try {
            $site_id = $data[ 'site_id' ];
            if (!empty($data[ 'goods_attr_format' ])) {

                $goods_attr_format = json_decode($data[ 'goods_attr_format' ], true);
                $keys = array_column($goods_attr_format, 'sort');
                if (!empty($keys)) {
                    array_multisort($keys, SORT_ASC, SORT_NUMERIC, $goods_attr_format);
                    $data[ 'goods_attr_format' ] = json_encode($goods_attr_format);
                }
            }

            $goods_image = $data[ 'goods_image' ];
            $first_image = explode(",", $goods_image)[ 0 ];

            //SKU商品数据
            if (!empty($data[ 'goods_sku_data' ])) {
                $data[ 'goods_sku_data' ] = json_decode($data[ 'goods_sku_data' ], true);
//                if (empty($goods_image)) {
//                    $goods_image = $data[ 'goods_sku_data' ][ 0 ][ 'sku_image' ];
//                }
            }
            if (isset($data[ 'support_trade_type' ]) && strpos($data[ 'support_trade_type' ], 'express') !== false && $data[ 'is_free_shipping' ] == 0 && empty($data[ 'shipping_template' ])) {
                return $this->error('', '运费模板不能为空');
            }

            //获取标签名称
            $label_name = '';
            if ($data[ 'label_id' ]) {
                $label_info = model('goods_label')->getInfo([ [ 'id', '=', $data[ 'label_id' ] ] ], 'label_name');
                $label_name = $label_info[ 'label_name' ] ?? '';
            }
            $brand_name = '';
            if ($data[ 'brand_id' ]) {
                $brand_info = model('goods_brand')->getInfo([ [ 'brand_id', '=', $data[ 'brand_id' ] ] ], 'brand_name');
                $brand_name = $brand_info[ 'brand_name' ] ?? '';
            }

            $goods_data = array (
                'goods_image' => $goods_image,
                'price' => !empty($data[ 'goods_sku_data' ][ 0 ][ 'price' ]) ? $data[ 'goods_sku_data' ][ 0 ][ 'price' ] : '',
                'market_price' => !empty($data[ 'goods_sku_data' ][ 0 ][ 'market_price' ]) ? $data[ 'goods_sku_data' ][ 0 ][ 'market_price' ] : '',
                'cost_price' => !empty($data[ 'goods_sku_data' ][ 0 ][ 'cost_price' ]) ? $data[ 'goods_sku_data' ][ 0 ][ 'cost_price' ] : '',
                'goods_spec_format' => $data[ 'goods_spec_format' ],
                'category_id' => $data[ 'category_id' ],
                'category_json' => $data[ 'category_json' ],
                'label_id' => $data[ 'label_id' ],
                'label_name' => $label_name,
                'timer_on' => $data[ 'timer_on' ],
                'timer_off' => $data[ 'timer_off' ],
                'is_consume_discount' => $data[ 'is_consume_discount' ],
                'sale_show' => $data[ 'sale_show' ] ?? 1,
                'stock_show' => $data[ 'stock_show' ] ?? 1,
                'market_price_show' => $data[ 'market_price_show' ] ?? 1,
                'barrage_show' => $data[ 'barrage_show' ] ?? 1,
            );

            $common_data = array (
                'goods_name' => $data[ 'goods_name' ],
                'goods_class' => $this->goods_class[ 'id' ],
                'goods_class_name' => $this->goods_class[ 'name' ],
                'goods_attr_class' => $data[ 'goods_attr_class' ],
                'goods_attr_name' => $data[ 'goods_attr_name' ],
                'is_limit' => isset($data[ 'is_limit' ]) ? $data[ 'is_limit' ] : 0,
                'limit_type' => isset($data[ 'limit_type' ]) ? $data[ 'limit_type' ] : 1,
                'site_id' => $data[ 'site_id' ],
                'goods_content' => $data[ 'goods_content' ],
                'goods_state' => $data[ 'goods_state' ],
                'goods_stock_alarm' => $data[ 'goods_stock_alarm' ],
                'is_free_shipping' => $data[ 'is_free_shipping' ],
                'shipping_template' => $data[ 'shipping_template' ],
                'goods_attr_format' => $data[ 'goods_attr_format' ],
                'introduction' => $data[ 'introduction' ],
                'keywords' => $data[ 'keywords' ],
                'brand_id' => $data[ 'brand_id' ],//品牌id
                'brand_name' => $brand_name,//品牌名称
                'unit' => $data[ 'unit' ],
                'video_url' => $data[ 'video_url' ],
                'sort' => $data[ 'sort' ],
                'goods_service_ids' => $data[ 'goods_service_ids' ],
                'create_time' => time(),
                'virtual_sale' => $data[ 'virtual_sale' ],
                'max_buy' => $data[ 'max_buy' ],
                'min_buy' => $data[ 'min_buy' ],
                'recommend_way' => $data[ 'recommend_way' ],
                'qr_id' => isset($data[ 'qr_id' ]) ? $data[ 'qr_id' ] : 0,
                'template_id' => isset($data[ 'template_id' ]) ? $data[ 'template_id' ] : 0,
                'form_id' => $data[ 'form_id' ] ?? 0,
                'support_trade_type' => $data[ 'support_trade_type' ] ?? '',
                'sale_channel' => $data[ 'sale_channel' ] ?? 'all',
                'sale_store' => $data[ 'sale_store' ] ?? 'all',
                'is_unify_pirce' => $data[ 'is_unify_pirce' ] ?? 1,
            );

            $goods_id = model('goods')->add(array_merge($goods_data, $common_data));

            $goods_stock = 0;

            $sku_arr = array ();
            //添加sku商品
            $sku_stock_list = [];
            foreach ($data[ 'goods_sku_data' ] as $item) {

                $goods_stock += $item[ 'stock' ];

                $sku_data = array (
                    'sku_name' => $data[ 'goods_name' ] . ' ' . $item[ 'spec_name' ],
                    'spec_name' => $item[ 'spec_name' ],
                    'sku_no' => $item[ 'sku_no' ],
                    'sku_spec_format' => !empty($item[ 'sku_spec_format' ]) ? json_encode($item[ 'sku_spec_format' ]) : "",
                    'price' => $item[ 'price' ],
                    'market_price' => $item[ 'market_price' ],
                    'cost_price' => $item[ 'cost_price' ],
                    'discount_price' => $item[ 'price' ],//sku折扣价（默认等于单价）
                    'stock_alarm' => $item[ 'stock_alarm' ],
                    'weight' => $item[ 'weight' ],
                    'volume' => $item[ 'volume' ],
                    'sku_image' => !empty($item[ 'sku_image' ]) ? $item[ 'sku_image' ] : $first_image,
                    'sku_images' => $item[ 'sku_images' ],
                    'goods_id' => $goods_id,
                    'is_default' => $item[ 'is_default' ] ?? 0,
                    'is_consume_discount' => $data[ 'is_consume_discount' ]
                );
                $sku_stock_list[] = [ 'stock' => $item[ 'stock' ], 'site_id' => $site_id, 'goods_class' => $common_data[ 'goods_class' ] ];
                $sku_arr[] = array_merge($sku_data, $common_data);
            }

            model('goods_sku')->addList($sku_arr);


            // 赋值第一个商品sku_id
            $first_info = model('goods_sku')->getFirstData([ 'goods_id' => $goods_id ], 'sku_id', 'is_default desc,sku_id asc');
            model('goods')->update([ 'sku_id' => $first_info[ 'sku_id' ] ], [ [ 'goods_id', '=', $goods_id ] ]);

            //同步默认门店的上下级状态
            if ($data[ 'goods_state' ] == 1) {
                ( new StoreGoods() )->modifyGoodsState($goods_id, $data[ 'goods_state' ], $site_id);
            }


            if (!empty($data[ 'goods_spec_format' ])) {
                // 刷新SKU商品规格项/规格值JSON字符串
                $this->dealGoodsSkuSpecFormat($goods_id, $data[ 'goods_spec_format' ]);
            }

            $cron = new Cron();
            //定时上下架
            if ($goods_data[ 'timer_on' ] > 0) {
                $cron->addCron(1, 0, "商品定时上架", "CronGoodsTimerOn", $goods_data[ 'timer_on' ], $goods_id);
            }
            if ($goods_data[ 'timer_off' ] > 0) {
                $cron->addCron(1, 0, "商品定时下架", "CronGoodsTimerOff", $goods_data[ 'timer_off' ], $goods_id);
            }

            //添加店铺添加统计
            $stat = new Stat();
            $stat->switchStat([ 'type' => 'add_goods', 'data' => [ 'add_goods_count' => 1, 'site_id' => $data[ 'site_id' ] ] ]);
            $stat->switchStat([ 'type' => 'goods_on', 'data' => [ 'site_id' => $data[ 'site_id' ] ] ]);
            $sku_list = model('goods_sku')->getList([ 'goods_id' => $goods_id ], 'sku_id');


            // 商品设置库存
            $goods_stock_model = new \app\model\stock\GoodsStock();
            //同步商品成本价
            ( new StoreGoods() )->setSkuPrice([ 'goods_id' => $goods_id, 'site_id' => $site_id ]);
            foreach ($sku_stock_list as $k => $v) {
                $sku_stock_list[ $k ][ 'sku_id' ] = $sku_list[ $k ][ 'sku_id' ];
            }
            $goods_stock_model->changeGoodsStock([
                'site_id' => $data[ 'site_id' ],
                'goods_class' => $common_data[ 'goods_class' ],
                'goods_sku_list' => $sku_stock_list
            ]);
            model('goods')->commit();

            return $this->success($goods_id);
        } catch (\Exception $e) {
            model('goods')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 商品编辑
     * @param $data
     */
    public function editGoods($data)
    {

        model('goods')->startTrans();

        try {

            $site_id = $data[ 'site_id' ];
            if (!empty($data[ 'goods_attr_format' ])) {

                $goods_attr_format = json_decode($data[ 'goods_attr_format' ], true);
                $keys = array_column($goods_attr_format, 'sort');
                if (!empty($keys)) {
                    array_multisort($keys, SORT_ASC, SORT_NUMERIC, $goods_attr_format);
                    $data[ 'goods_attr_format' ] = json_encode($goods_attr_format);
                }
            }
            $goods_id = $data[ 'goods_id' ];
            $goods_image = $data[ 'goods_image' ];
            $first_image = explode(",", $goods_image)[ 0 ];

            //SKU商品数据
            if (!empty($data[ 'goods_sku_data' ])) {
                $data[ 'goods_sku_data' ] = json_decode($data[ 'goods_sku_data' ], true);
//                if (empty($goods_image)) {
//                    $goods_image = $data[ 'goods_sku_data' ][ 0 ][ 'sku_image' ];
//                }
            }

            if (isset($data[ 'support_trade_type' ]) && strpos($data[ 'support_trade_type' ], 'express') !== false && $data[ 'is_free_shipping' ] == 0 && empty($data[ 'shipping_template' ])) {
                return $this->error('', '运费模板不能为空');
            }

            //获取标签名称
            $label_name = '';
            if ($data[ 'label_id' ]) {
                $label_info = model('goods_label')->getInfo([ [ 'id', '=', $data[ 'label_id' ] ] ], 'label_name');
                $label_name = $label_info[ 'label_name' ] ?? '';
            }
            $brand_name = '';
            if ($data[ 'brand_id' ]) {
                $brand_info = model('goods_brand')->getInfo([ [ 'brand_id', '=', $data[ 'brand_id' ] ] ], 'brand_name');
                $brand_name = $brand_info[ 'brand_name' ] ?? '';
            }
            $goods_data = array (
                'goods_image' => $goods_image,
//                'goods_stock' => $data[ 'goods_stock' ],
                'price' => $data[ 'goods_sku_data' ][ 0 ][ 'price' ],
                'market_price' => $data[ 'goods_sku_data' ][ 0 ][ 'market_price' ],
                'cost_price' => $data[ 'goods_sku_data' ][ 0 ][ 'cost_price' ],
                'goods_spec_format' => $data[ 'goods_spec_format' ],
                'category_id' => $data[ 'category_id' ],
                'category_json' => $data[ 'category_json' ],
                'label_id' => $data[ 'label_id' ],
                'label_name' => $label_name,
                'timer_on' => $data[ 'timer_on' ],
                'timer_off' => $data[ 'timer_off' ],
                'is_consume_discount' => $data[ 'is_consume_discount' ],
                'sale_show' => $data[ 'sale_show' ],
                'stock_show' => $data[ 'stock_show' ],
                'market_price_show' => $data[ 'market_price_show' ],
                'barrage_show' => $data[ 'barrage_show' ],
                'support_trade_type' => $data[ 'support_trade_type' ] ?? '',
            );

            $common_data = array (
                'goods_name' => $data[ 'goods_name' ],
                'goods_class' => $this->goods_class[ 'id' ],
                'goods_class_name' => $this->goods_class[ 'name' ],
                'goods_attr_class' => $data[ 'goods_attr_class' ],
                'goods_attr_name' => $data[ 'goods_attr_name' ],
                'site_id' => $data[ 'site_id' ],
                'goods_content' => $data[ 'goods_content' ],
                'goods_state' => $data[ 'goods_state' ],
                'goods_stock_alarm' => $data[ 'goods_stock_alarm' ],
                'is_free_shipping' => $data[ 'is_free_shipping' ],
                'shipping_template' => $data[ 'shipping_template' ],
                'goods_attr_format' => $data[ 'goods_attr_format' ],
                'introduction' => $data[ 'introduction' ],
                'keywords' => $data[ 'keywords' ],
                'unit' => $data[ 'unit' ],
                'video_url' => $data[ 'video_url' ],
                'sort' => $data[ 'sort' ],
                'goods_service_ids' => $data[ 'goods_service_ids' ],
                'modify_time' => time(),
                'virtual_sale' => $data[ 'virtual_sale' ],
                'max_buy' => $data[ 'max_buy' ],
                'min_buy' => $data[ 'min_buy' ],
                'brand_id' => $data[ 'brand_id' ],//品牌id
                'brand_name' => $brand_name,//品牌名称
                'recommend_way' => $data[ 'recommend_way' ],
                'is_consume_discount' => $data[ 'is_consume_discount' ],
                'is_limit' => $data[ 'is_limit' ],
                'limit_type' => $data[ 'limit_type' ],
                'qr_id' => isset($data[ 'qr_id' ]) ? $data[ 'qr_id' ] : 0,
                'template_id' => isset($data[ 'template_id' ]) ? $data[ 'template_id' ] : 0,
                'form_id' => $data[ 'form_id' ] ?? 0,
                'support_trade_type' => $data[ 'support_trade_type' ] ?? '',
                'sale_channel' => $data[ 'sale_channel' ] ?? 'all',
                'sale_store' => $data[ 'sale_store' ] ?? 'all',
                'is_unify_pirce' => $data[ 'is_unify_pirce' ] ?? 1,
            );
            model('goods')->update(array_merge($goods_data, $common_data), [ [ 'goods_id', '=', $goods_id ], [ 'goods_class', '=', $this->goods_class[ 'id' ] ] ]);

            $goods_stock = 0;
            $goods_stock_model = new \app\model\stock\GoodsStock();
            $sku_stock_list = [];
            // 如果只编辑价格库存就是修改，如果添加规格项/值就需要重新生成
            if (!empty($data[ 'goods_sku_data' ][ 0 ][ 'sku_id' ])) {
                if ($data[ 'spec_type_status' ] == 1) {
                    model('goods_sku')->delete([ [ 'goods_id', '=', $goods_id ] ]);

                    $sku_arr = array ();
                    //添加sku商品
                    foreach ($data[ 'goods_sku_data' ] as $item) {

                        $goods_stock += $item[ 'stock' ];

                        $sku_data = array (
                            'sku_name' => $data[ 'goods_name' ] . ' ' . $item[ 'spec_name' ],
                            'spec_name' => $item[ 'spec_name' ],
                            'sku_no' => $item[ 'sku_no' ],
                            'sku_spec_format' => !empty($item[ 'sku_spec_format' ]) ? json_encode($item[ 'sku_spec_format' ]) : "",
                            'price' => $item[ 'price' ],
                            'market_price' => $item[ 'market_price' ],
                            'cost_price' => $item[ 'cost_price' ],
                            'discount_price' => $item[ 'price' ],//sku折扣价（默认等于单价）
//                            'stock' => $item[ 'stock' ],
                            'stock_alarm' => $item[ 'stock_alarm' ],
                            'weight' => $item[ 'weight' ],
                            'volume' => $item[ 'volume' ],
                            'sku_image' => !empty($item[ 'sku_image' ]) ? $item[ 'sku_image' ] : $first_image,
                            'sku_images' => $item[ 'sku_images' ],
                            'goods_id' => $goods_id,
                            'is_default' => $item[ 'is_default' ] ?? 0,
                            'is_consume_discount' => $data[ 'is_consume_discount' ]
                        );
                        $sku_arr[] = array_merge($sku_data, $common_data);
                        $sku_stock_list[] = [ 'stock' => $item[ 'stock' ], 'site_id' => $site_id, 'goods_class' => $common_data[ 'goods_class' ] ];
                    }
                    model('goods_sku')->addList($sku_arr);
                    $sku_list = model('goods_sku')->getList([ 'goods_id' => $goods_id ], 'sku_id');
                    foreach ($sku_stock_list as $k => $v) {
                        $sku_stock_list[ $k ][ 'sku_id' ] = $sku_list[ $k ][ 'sku_id' ];
                    }
                } else {
                    $discount_model = new Discount();
                    $sku_id_arr = [];
                    foreach ($data[ 'goods_sku_data' ] as $item) {
                        $discount_info = [];
                        if (!empty($item[ 'sku_id' ])) {
                            $discount_info_result = $discount_model->getDiscountGoodsInfo([ [ 'pdg.sku_id', '=', $item[ 'sku_id' ] ], [ 'pd.status', '=', 1 ] ], 'id');
                            $discount_info = $discount_info_result[ 'data' ];
                        }

                        $goods_stock += $item[ 'stock' ];

                        $sku_data = array (
                            'sku_name' => $data[ 'goods_name' ] . ' ' . $item[ 'spec_name' ],
                            'spec_name' => $item[ 'spec_name' ],
                            'sku_no' => $item[ 'sku_no' ],
                            'sku_spec_format' => !empty($item[ 'sku_spec_format' ]) ? json_encode($item[ 'sku_spec_format' ]) : "",
                            'price' => $item[ 'price' ],
                            'market_price' => $item[ 'market_price' ],
                            'cost_price' => $item[ 'cost_price' ],
//                            'stock' => $item[ 'stock' ],
                            'stock_alarm' => $item[ 'stock_alarm' ],
                            'weight' => $item[ 'weight' ],
                            'volume' => $item[ 'volume' ],
                            'sku_image' => !empty($item[ 'sku_image' ]) ? $item[ 'sku_image' ] : $first_image,
                            'sku_images' => $item[ 'sku_images' ],
                            'goods_id' => $goods_id,
                            'is_default' => $item[ 'is_default' ] ?? 0,
                            'is_consume_discount' => $data[ 'is_consume_discount' ]
                        );
                        if (empty($discount_info)) {
                            $sku_data[ 'discount_price' ] = $item[ 'price' ];
                        }
                        if (!empty($item[ 'sku_id' ])) {
                            $sku_id_arr[] = $item[ 'sku_id' ];
                            model('goods_sku')->update(array_merge($sku_data, $common_data), [ [ 'sku_id', '=', $item[ 'sku_id' ] ], [ 'goods_class', '=', $this->goods_class[ 'id' ] ] ]);
                        } else {
                            $sku_id = model('goods_sku')->add(array_merge($sku_data, $common_data));
                            $item[ 'sku_id' ] = $sku_id;
                            $sku_id_arr[] = $sku_id;
                        }
                        $sku_stock_list[] = [ 'stock' => $item[ 'stock' ], 'sku_id' => $item[ 'sku_id' ], 'site_id' => $site_id, 'goods_class' => $common_data[ 'goods_class' ] ];
                    }

                    // 移除不存在的商品SKU
                    $sku_id_list = model('goods_sku')->getList([ [ 'goods_id', '=', $goods_id ] ], 'sku_id');
                    $sku_id_list = array_column($sku_id_list, 'sku_id');
                    foreach ($sku_id_list as $k => $v) {
                        foreach ($sku_id_arr as $ck => $cv) {
                            if ($v == $cv) {
                                unset($sku_id_list[ $k ]);
                            }
                        }
                    }
                    $sku_id_list = array_values($sku_id_list);
                    if (!empty($sku_id_list)) {
                        model('goods_sku')->delete([ [ 'sku_id', 'in', implode(",", $sku_id_list) ] ]);
                    }
                }

            } else {

                model('goods_sku')->delete([ [ 'goods_id', '=', $goods_id ] ]);

                $sku_arr = array ();
                //添加sku商品
                foreach ($data[ 'goods_sku_data' ] as $item) {

                    $goods_stock += $item[ 'stock' ];

                    $sku_data = array (
                        'sku_name' => $data[ 'goods_name' ] . ' ' . $item[ 'spec_name' ],
                        'spec_name' => $item[ 'spec_name' ],
                        'sku_no' => $item[ 'sku_no' ],
                        'sku_spec_format' => !empty($item[ 'sku_spec_format' ]) ? json_encode($item[ 'sku_spec_format' ]) : "",
                        'price' => $item[ 'price' ],
                        'market_price' => $item[ 'market_price' ],
                        'cost_price' => $item[ 'cost_price' ],
                        'discount_price' => $item[ 'price' ],//sku折扣价（默认等于单价）
//                        'stock' => $item[ 'stock' ],
                        'stock_alarm' => $item[ 'stock_alarm' ],
                        'weight' => $item[ 'weight' ],
                        'volume' => $item[ 'volume' ],
                        'sku_image' => !empty($item[ 'sku_image' ]) ? $item[ 'sku_image' ] : $first_image,
                        'sku_images' => $item[ 'sku_images' ],
                        'goods_id' => $goods_id,
                        'is_default' => $item[ 'is_default' ] ?? 0,
                        'is_consume_discount' => $data[ 'is_consume_discount' ]
                    );
                    $sku_stock_list[] = [ 'stock' => $item[ 'stock' ], 'site_id' => $site_id, 'goods_class' => $common_data[ 'goods_class' ] ];
                    $sku_arr[] = array_merge($sku_data, $common_data);
                }

                model('goods_sku')->addList($sku_arr);

                $sku_list = model('goods_sku')->getList([ 'goods_id' => $goods_id ], 'sku_id');
                foreach ($sku_stock_list as $k => $v) {
                    $sku_stock_list[ $k ][ 'sku_id' ] = $sku_list[ $k ][ 'sku_id' ];
                }
            }

            // 赋值第一个商品sku_id
            $first_info = model('goods_sku')->getFirstData([ 'goods_id' => $goods_id ], 'sku_id', 'is_default desc,sku_id asc');
            model('goods')->update([ 'sku_id' => $first_info[ 'sku_id' ] ], [ [ 'goods_id', '=', $goods_id ] ]);

            if (!empty($data[ 'goods_spec_format' ])) {
                //刷新SKU商品规格项/规格值JSON字符串
                $this->dealGoodsSkuSpecFormat($goods_id, $data[ 'goods_spec_format' ]);
            }

            //同步默认门店的上下级状态
            if ($data[ 'goods_state' ] == 1) {
                ( new StoreGoods() )->modifyGoodsState($goods_id, $data[ 'goods_state' ], $site_id);
            }

            $cron = new Cron();
            $cron->deleteCron([ [ 'event', '=', 'CronGoodsTimerOn' ], [ 'relate_id', '=', $goods_id ] ]);
            $cron->deleteCron([ [ 'event', '=', 'CronGoodsTimerOff' ], [ 'relate_id', '=', $goods_id ] ]);
            //定时上下架
            if ($goods_data[ 'timer_on' ] > 0) {
                $cron->addCron(1, 0, "商品定时上架", "CronGoodsTimerOn", $goods_data[ 'timer_on' ], $goods_id);
            }
            if ($goods_data[ 'timer_off' ] > 0) {
                $cron->addCron(1, 0, "商品定时下架", "CronGoodsTimerOff", $goods_data[ 'timer_off' ], $goods_id);
            }

            event('GoodsEdit', [ 'goods_id' => $goods_id, 'site_id' => $data[ 'site_id' ] ]);

            $stat = new Stat();
            $stat->switchStat([ 'type' => 'goods_on', 'data' => [ 'site_id' => $data[ 'site_id' ] ] ]);


            //同步商品成本价
            ( new StoreGoods() )->setSkuPrice([ 'goods_id' => $goods_id, 'site_id' => $site_id ]);
            //核验和校准改变的sku
            $goods_stock_model->checkExistGoodsSku([ 'goods_id' => $goods_id ]);
            // 商品设置库存
            $goods_stock_model->changeGoodsStock([
                'site_id' => $data[ 'site_id' ],
                'goods_class' => $common_data[ 'goods_class' ],
                'goods_sku_list' => $sku_stock_list
            ]);
            model('goods')->commit();
            return $this->success($goods_id);
        } catch (\Exception $e) {
            model('goods')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 商品复制
     * @param $goods_id
     * @return array
     */
    public function copyGoods($goods_id, $site_id)
    {
        $goods_info = model("goods")->getInfo([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $site_id ] ]);
        if (empty($goods_info)) {
            return $this->error('', '商品不存在，无法复制！');
        }

        model('goods')->startTrans();

        try {
            unset($goods_info[ 'goods_id' ]);
            $goods_info[ 'goods_name' ] .= '_副本';
            $goods_info[ 'goods_state' ] = 0;
            $goods_info[ 'goods_stock' ] = 0;
            $goods_info[ 'create_time' ] = time();
            $goods_info[ 'modify_time' ] = 0;
            $goods_info[ 'sale_num' ] = 0;
            $goods_info[ 'evaluate' ] = 0;
            $goods_info[ 'evaluate_shaitu' ] = 0;
            $goods_info[ 'evaluate_shipin' ] = 0;
            $goods_info[ 'evaluate_zhuiping' ] = 0;
            $goods_info[ 'evaluate_haoping' ] = 0;
            $goods_info[ 'evaluate_zhongping' ] = 0;
            $goods_info[ 'evaluate_chaping' ] = 0;
            $goods_info[ 'is_fenxiao' ] = 0;
            $goods_info[ 'fenxiao_type' ] = 1;
            $goods_info[ 'supplier_id' ] = 0;
            $goods_info[ 'is_consume_discount' ] = 0;
            $goods_info[ 'discount_config' ] = 0;
            $goods_info[ 'discount_method' ] = '';
            $goods_info[ 'sku_id' ] = 0;
            $goods_info[ 'promotion_addon' ] = '';
            $goods_info[ 'virtual_sale' ] = 0;

            $new_goods_id = model("goods")->add($goods_info);

            $goods_sku_list = model("goods_sku")->getList([ [ 'goods_id', '=', $goods_id ] ], '*', 'sku_id asc');

            $sku_data = array ();

            foreach ($goods_sku_list as $k => $v) {
                unset($v[ 'sku_id' ]);
                $v[ 'goods_id' ] = $new_goods_id;
                $v[ 'sku_name' ] .= '_副本';
                $v[ 'promotion_type' ] = 0;
                $v[ 'start_time' ] = 0;
                $v[ 'end_time' ] = 0;
                $v[ 'stock' ] = 0;
                $v[ 'click_num' ] = 0;
                $v[ 'sale_num' ] = 0;
                $v[ 'collect_num' ] = 0;
                $v[ 'goods_name' ] .= '_副本';
                $v[ 'goods_state' ] = 0;
                $v[ 'create_time' ] = time();
                $v[ 'modify_time' ] = 0;
                $v[ 'evaluate' ] = 0;
                $v[ 'evaluate_shaitu' ] = 0;
                $v[ 'evaluate_shipin' ] = 0;
                $v[ 'evaluate_zhuiping' ] = 0;
                $v[ 'evaluate_haoping' ] = 0;
                $v[ 'evaluate_zhongping' ] = 0;
                $v[ 'evaluate_chaping' ] = 0;
                $v[ 'supplier_id' ] = 0;
                $v[ 'is_consume_discount' ] = 0;
                $v[ 'discount_config' ] = 0;
                $v[ 'discount_method' ] = '';
                $v[ 'member_price' ] = '';
                $v[ 'fenxiao_price' ] = 0;
                $v[ 'virtual_sale' ] = 0;
                $sku_data[] = $v;
            }

            model('goods_sku')->addList($sku_data);

            // 赋值第一个商品sku_id
            $first_info = model('goods_sku')->getFirstData([ 'goods_id' => $new_goods_id ], 'sku_id', 'sku_id asc');
            model('goods')->update([ 'sku_id' => $first_info[ 'sku_id' ] ], [ [ 'goods_id', '=', $new_goods_id ] ]);

            if (!empty($goods_info[ 'goods_spec_format' ])) {
                // 刷新SKU商品规格项/规格值JSON字符串
                $this->dealGoodsSkuSpecFormat($new_goods_id, $goods_info[ 'goods_spec_format' ]);
            }

            model('goods')->commit();

        } catch (\Exception $e) {
            model('goods')->rollback();
            return $this->error($e->getMessage());
        }
        return $this->success($new_goods_id);
    }

    /**
     * 修改商品状态
     * @param $goods_ids
     * @param $goods_state
     * @param $site_id
     * @return array
     */
    public function modifyGoodsState($goods_ids, $goods_state, $site_id)
    {
        model('goods')->update([ 'goods_state' => $goods_state ], [ [ 'goods_id', 'in', $goods_ids ], [ 'site_id', '=', $site_id ] ]);
        model('goods_sku')->update([ 'goods_state' => $goods_state ], [ [ 'goods_id', 'in', $goods_ids ], [ 'site_id', '=', $site_id ] ]);

        //同步默认门店的上下级状态

        if ($goods_state == 1) {
            ( new StoreGoods() )->modifyGoodsState($goods_ids, $goods_state, $site_id);
        }

        $stat = new Stat();
        $stat->switchStat([ 'type' => 'goods_on', 'data' => [ 'site_id' => $site_id ] ]);
        return $this->success(1);
    }

    /**
     * 事件修改商品状态
     * @param $goods_ids
     * @param $goods_state
     * @param $site_id
     * @return array
     */
    public function cronModifyGoodsState($condition, $goods_state)
    {
        model('goods')->update([ 'goods_state' => $goods_state ], $condition);
        model('goods_sku')->update([ 'goods_state' => $goods_state ], $condition);

        if ($goods_state == 1) {
            $goods_list = model('goods')->getList($condition, 'goods_id,site_id');
            $goods_ids = array_column($goods_list, 'goods');
            //同步默认门店的上下级状态
            ( new StoreGoods() )->modifyGoodsState($goods_ids, $goods_state, $goods_list[ 0 ][ 'site_id' ]);
        }


        return $this->success(1);
    }

    /**
     * 修改排序
     * @param $sort
     * @param $goods_id
     * @param $site_id
     * @return array
     */
    public function modifyGoodsSort($sort, $goods_id, $site_id)
    {
        model('goods')->update([ 'sort' => $sort ], [ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $site_id ] ]);
        model('goods_sku')->update([ 'sort' => $sort ], [ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $site_id ] ]);
        return $this->success(1);
    }

    /**
     * 修改删除状态
     * @param $goods_ids
     * @param $is_delete
     * @param $site_id
     */
    public function modifyIsDelete($goods_ids, $is_delete, $site_id)
    {
        model('goods')->update([ 'is_delete' => $is_delete ], [ [ 'goods_id', 'in', $goods_ids ], [ 'site_id', '=', $site_id ] ]);
        model('goods_sku')->update([ 'is_delete' => $is_delete ], [ [ 'goods_id', 'in', $goods_ids ], [ 'site_id', '=', $site_id ] ]);

        //删除商品
        if ($is_delete == 1) {
            event('DeleteGoods', [ 'goods_id' => $goods_ids, 'site_id' => $site_id ]);
        }
        return $this->success(1);
    }

    /**
     * 修改商品点击量
     * @param $sku_id
     * @param $site_id
     */
    public function modifyClick($sku_id, $site_id)
    {
        model("goods_sku")->setInc([ [ 'sku_id', '=', $sku_id ], [ 'site_id', '=', $site_id ] ], 'click_num', 1);
        return $this->success(1);
    }

    /**
     * 删除回收站商品
     * @param $goods_ids
     * @param $site_id
     */
    public function deleteRecycleGoods($goods_ids, $site_id)
    {
        model('goods')->delete([ [ 'goods_id', 'in', $goods_ids ], [ 'site_id', '=', $site_id ] ]);
        model('goods_sku')->delete([ [ 'goods_id', 'in', $goods_ids ], [ 'site_id', '=', $site_id ] ]);
        return $this->success(1);
    }

    /**
     * 获取商品信息
     * @param array $condition
     * @param string $field
     */
    public function getGoodsInfo($condition, $field = '*', $alias = 'a', $join = [])
    {
        $info = model('goods')->getInfo($condition, $field, $alias, $join);
        return $this->success($info);
    }

    /**
     * 获取商品信息
     * @param array $condition
     * @param string $field
     */
    public function editGetGoodsInfo($condition, $field = '*')
    {
        $info = model('goods')->getInfo($condition, $field);
        if (!empty($info)) {
            $category_json = json_decode($info[ 'category_json' ]);
            $goods_category = [];
            foreach ($category_json as $k => $v) {
                if (!empty($v)) {
                    $category_list = model('goods_category')->getList([ [ 'category_id', 'in', $v ] ], 'category_name', 'level asc');
                    $category_name = array_column($category_list, 'category_name');
                    $category_name = implode('/', $category_name);
                    $goods_category[ $k ] = [
                        'id' => $v,
                        'category_name' => $category_name
                    ];
                }
            }
            $info[ 'goods_category' ] = $goods_category;
            return $this->success($info);
        }
        return $this->error();
    }

    /**
     * 获取商品详情
     * @param $goods_id
     * @return array
     */
    public function getGoodsDetail($goods_id)
    {
        $info = model('goods')->getInfo([ [ 'goods_id', '=', $goods_id ] ], "*");
        $field = 'sku_id, sku_name, sku_no, sku_spec_format, price, market_price, cost_price, discount_price, stock,
                  weight, volume,  sku_image, sku_images, sort,member_price,fenxiao_price';
        $sku_data = model('goods_sku')->getList([ [ 'goods_id', '=', $goods_id ] ], $field);

        if (!empty($sku_data)) {
            foreach ($sku_data as $k => $v) {
                $sku_data[ $k ][ 'member_price' ] = $v[ 'member_price' ] == '' ? '' : json_decode($v[ 'member_price' ], true);
            }
            $info[ 'sku_data' ] = $sku_data;
        }
        return $this->success($info);
    }

    /**
     * 商品sku 基础信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getGoodsSkuInfo($condition, $field = "sku_id,sku_name,sku_spec_format,price,market_price,discount_price,promotion_type,start_time,end_time,stock,click_num,sale_num,collect_num,sku_image,sku_images,goods_id,site_id,goods_content,goods_state,is_virtual,is_free_shipping,goods_spec_format,goods_attr_format,introduction,unit,video_url,sku_no,goods_name,goods_class,goods_class_name,cost_price", $alias = 'a', $join = null)
    {
        $info = model('goods_sku')->getInfo($condition, $field, $alias, $join);
        return $this->success($info);
    }

    /**
     * 商品SKU 详情
     * @param $sku_id
     * @param $site_id
     * @param string $field
     * @return array
     */
    public function getGoodsSkuDetail($sku_id, $site_id, $field = '')
    {
        $condition = [ [ 'gs.sku_id', '=', $sku_id ], [ 'gs.site_id', '=', $site_id ], [ 'gs.is_delete', '=', 0 ] ];

        if (empty($field)) {
            $field = 'gs.goods_id,gs.sku_id,gs.qr_id,gs.goods_name,gs.sku_name,gs.sku_spec_format,gs.price,gs.market_price,gs.discount_price,gs.promotion_type,gs.start_time
            ,gs.end_time,gs.stock,gs.click_num,(g.sale_num + g.virtual_sale) as sale_num,gs.collect_num,gs.sku_image,gs.sku_images
            ,gs.goods_content,gs.goods_state,gs.is_free_shipping,gs.goods_spec_format,gs.goods_attr_format,gs.introduction,gs.unit,gs.video_url
            ,gs.is_virtual,gs.goods_service_ids,gs.max_buy,gs.min_buy,gs.is_limit,gs.limit_type,gs.support_trade_type,g.goods_image,g.keywords,g.stock_show,g.sale_show,g.market_price_show,g.barrage_show,g.evaluate,g.goods_class';
        }
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'inner' ],
        ];

        $info = model('goods_sku')->getInfo($condition, $field, 'gs', $join);
        return $this->success($info);
    }

    /**
     * 获取商品SKU集合
     * @param $goods_id
     * @param $site_id
     * @param string $field
     * @return array
     */
    public function getGoodsSku($goods_id, $site_id, $field = '')
    {
        $condition = [
            [ 'gs.goods_id', '=', $goods_id ],
            [ 'gs.site_id', '=', $site_id ],
            [ 'gs.is_delete', '=', 0 ],
        ];

        if (empty($field)) {
            $field = 'gs.sku_id,g.goods_image,gs.sku_name,gs.sku_spec_format,gs.price,gs.discount_price,gs.promotion_type,gs.end_time,gs.stock,gs.sku_image,gs.sku_images,gs.goods_spec_format,gs.is_limit,gs.limit_type,gs.market_price,g.goods_state';
        }
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'inner' ],
        ];

        $list = model('goods_sku')->getList($condition, $field, 'gs.sku_id asc', 'gs', $join);
        return $this->success($list);
    }

    /**
     * 获取商品列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getGoodsList($condition = [], $field = 'goods_id,goods_class,goods_class_name,goods_attr_name,goods_name,site_id,sort,goods_image,goods_content,goods_state,price,market_price,cost_price,goods_stock,goods_stock_alarm,is_virtual,is_free_shipping,shipping_template,goods_spec_format,goods_attr_format,create_time', $order = 'create_time desc', $limit = null, $alias = '', $join = [])
    {
        $list = model('goods')->getList($condition, $field, $order, $alias, $join, '', $limit);
        return $this->success($list);
    }

    /**
     * 获取商品分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getGoodsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'a.create_time desc', $field = 'a.goods_id,a.goods_name,a.site_id,a.site_name,a.goods_image,a.goods_state,a.price,a.goods_stock,a.goods_stock_alarm,a.create_time,a.sale_num,a.is_virtual,a.goods_class,a.goods_class_name,a.is_fenxiao,a.fenxiao_type,a.promotion_addon,a.sku_id,a.is_consume_discount,a.discount_config,a.discount_method,a.sort,a.label_id,a.is_delete', $alias = 'a', $join = [])
    {
        $list = model('goods')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 编辑商品库存价格等信息
     * @param $goods_sku_array
     * @return array
     */
    public function editGoodsStock($goods_sku_array, $site_id)
    {
        $goods_sku_model = new GoodsStock();
        $store_stock_model = new \app\model\stock\GoodsStock();
        model('goods')->startTrans();
        try {
            $sku_stock_list = [];
            foreach ($goods_sku_array as $k => $v) {
                $sku_info = model("goods_sku")->getInfo([ [ 'site_id', '=', $site_id ], [ 'sku_id', '=', $v[ 'sku_id' ] ] ], "goods_id,stock, goods_class");

                $goods_id = $sku_info[ 'goods_id' ];
                $goods_class = $sku_info[ 'goods_class' ];
                //验证当前规格是否参加的活动
                $discount_model = new Discount();
                $discount_info = [];
                if (!empty($item[ 'sku_id' ])) {
                    $discount_info_result = $discount_model->getDiscountGoodsInfo([ [ 'pdg.sku_id', '=', $v[ 'sku_id' ] ], [ 'pd.status', '=', 1 ] ], 'id');
                    $discount_info = $discount_info_result[ 'data' ];
                }
                if (empty($discount_info)) {
                    $v[ 'discount_price' ] = $v[ 'price' ];
                }

                if ($k == 0) {//修改商品中的价格等信息
                    $goods_data = [
                        'price' => $v[ 'price' ],
                        'market_price' => $v[ 'market_price' ],
                        'cost_price' => $v[ 'cost_price' ]
                    ];
                    model('goods')->update($goods_data, [ [ 'goods_id', '=', $sku_info[ 'goods_id' ] ] ]);
                }
//                if ($v[ 'stock' ] > $sku_info[ 'stock' ]) {
//
//                    $sku_stock_data = [
//                        'sku_id' => $v[ 'sku_id' ],
//                        'num' => $v[ 'stock' ] - $sku_info[ 'stock' ]
//                    ];
//                    $goods_sku_model->incStock($sku_stock_data);
//                }
//                if ($v[ 'stock' ] < $sku_info[ 'stock' ]) {
//                    $sku_stock_data = [
//                        'sku_id' => $v[ 'sku_id' ],
//                        'num' => $sku_info[ 'stock' ] - $v[ 'stock' ]
//                    ];
//                    $goods_sku_model->decStock($sku_stock_data);
//                }
                $stock = $v[ 'stock' ];
                unset($v[ 'stock' ]);
                model('goods_sku')->update($v, [ [ 'sku_id', '=', $v[ 'sku_id' ] ] ]);
                $sku_stock_list[] = [ 'stock' => $stock, 'sku_id' => $v[ 'sku_id' ], 'goods_class' => $goods_class ];

            }
            $store_stock_model->changeGoodsStock([
                'site_id' => $site_id,
                'goods_sku_list' => $sku_stock_list,
                'goods_class' => $goods_class
            ]);
            model('goods')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('goods')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取商品sku列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getGoodsSkuList($condition = [], $field = 'sku_id,sku_name,price,stock,sale_num,sku_image,goods_id,goods_name,site_id,spec_name', $order = 'price asc', $limit = null, $alias = '', $join = [])
    {
        $list = model('goods_sku')->getList($condition, $field, $order, $alias, $join, '', $limit);
        return $this->success($list);
    }

    /**
     * 获取商品sku分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getGoodsSkuPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*', $alias = '', $join = '', $group = null)
    {
        $list = model('goods_sku')->Lists($condition, $field, $order, $page, $page_size, $alias, $join, $group);
        return $this->success($list);
    }

    /**
     * 二维数组根据某个字段排序
     * @param array $array 要排序的数组
     * @param string $keys 要排序的键字段
     * @param string $sort 排序类型  SORT_ASC     SORT_DESC
     * @return array 排序后的数组
     */
    function arraySort($array, $keys, $sort = SORT_DESC)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[ $k ] = $v[ $keys ];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }

    /**
     * 刷新SKU商品规格项/规格值JSON字符串
     * @param int $goods_id 商品id
     * @param string $goods_spec_format 商品完整规格项/规格值json
     */
    public function dealGoodsSkuSpecFormat($goods_id, $goods_spec_format)
    {
        if (empty($goods_spec_format)) return;

        $goods_spec_format = json_decode($goods_spec_format, true);

        //根据goods_id查询sku商品列表，查询：sku_id、sku_spec_format 列
        $sku_list = model('goods_sku')->getList([ [ 'goods_id', '=', $goods_id ], [ 'sku_spec_format', '<>', '' ] ], 'sku_id,sku_spec_format', 'sku_id asc');
        if (!empty($sku_list)) {

//			$temp = 0;//测试性能，勿删

            //循环SKU商品列表
            foreach ($sku_list as $k => $v) {
//				$temp++;

                $sku_format = $goods_spec_format;//最终要存储的值
                $current_format = json_decode($v[ 'sku_spec_format' ], true);//当前SKU商品规格值json

                $selected_data = [];//已选规格/规格值json

                //1、找出已选规格/规格值json

                //循环完整商品规格json
                foreach ($sku_format as $sku_k => $sku_v) {
//					$temp++;

                    //循环当前SKU商品规格json
                    foreach ($current_format as $current_k => $current_v) {
//						$temp++;

                        //匹配规格项
                        if ($current_v[ 'spec_id' ] == $sku_v[ 'spec_id' ]) {

                            //循环规格值
                            foreach ($sku_v[ 'value' ] as $sku_value_k => $sku_value_v) {
//								$temp++;

                                //匹配规格值id
                                if ($current_v[ 'spec_value_id' ] == $sku_value_v[ 'spec_value_id' ]) {
                                    $sku_format[ $sku_k ][ 'value' ][ $sku_value_k ][ 'selected' ] = true;
                                    $sku_format[ $sku_k ][ 'value' ][ $sku_value_k ][ 'sku_id' ] = $v[ 'sku_id' ];
                                    $selected_data[] = $sku_format[ $sku_k ][ 'value' ][ $sku_value_k ];
                                    break;
                                }
                            }

                        }

                    }
                }

                //2、找出未选中的规格/规格值json
                foreach ($sku_format as $sku_k => $sku_v) {
//					$temp++;

                    foreach ($sku_v[ 'value' ] as $sku_value_k => $sku_value_v) {
//						$temp++;

                        if (!isset($sku_value_v[ 'selected' ])) {

                            $refer_data = [];//参考已选中的规格/规格值json
                            $refer_data[] = $sku_value_v;

//							根据已选中的规格值进行参考
                            foreach ($selected_data as $selected_k => $selected_v) {
//								$temp++;
//								排除自身，然后进行参考
                                if ($selected_v[ 'spec_id' ] != $sku_value_v[ 'spec_id' ]) {
                                    $refer_data[] = $selected_v;
                                }
                            }

                            foreach ($sku_list as $again_k => $again_v) {
//								$temp++;

                                //排除当前SKU商品
                                if ($again_v[ 'sku_id' ] != $v[ 'sku_id' ]) {

                                    $current_format_again = json_decode($again_v[ 'sku_spec_format' ], true);
                                    $count = count($current_format_again);//规格总数量
                                    $curr_count = 0;//当前匹配规格数量

                                    //循环当前SKU商品规格json
                                    foreach ($current_format_again as $current_again_k => $current_again_v) {
//										$temp++;

                                        foreach ($refer_data as $fan_k => $fan_v) {
//											$temp++;

                                            if ($current_again_v[ 'spec_value_id' ] == $fan_v[ 'spec_value_id' ]) {
                                                $curr_count++;
                                            }
                                        }

                                    }

//									匹配数量跟规格总数一致表示匹配成功
                                    if ($curr_count == $count) {
                                        $sku_format[ $sku_k ][ 'value' ][ $sku_value_k ][ 'selected' ] = false;
                                        $sku_format[ $sku_k ][ 'value' ][ $sku_value_k ][ 'sku_id' ] = $again_v[ 'sku_id' ];
                                        break;
                                    }
                                }

                            }

                            //没有匹配到规格值，则禁用
                            if (!isset($sku_format[ $sku_k ][ 'value' ][ $sku_value_k ][ 'selected' ])) {
                                $sku_format[ $sku_k ][ 'value' ][ $sku_value_k ][ 'disabled' ] = false;
//                                var_dump(json_encode($sku_format));
//                                var_dump('==========');
                            }

                        }
                    }
                }

//				var_dump($sku_format);
//				var_dump("=========");
                //修改ns_goods_sku表表中的goods_spec_format字段，将$sku_format值传入
                model('goods_sku')->update([ 'goods_spec_format' => json_encode($sku_format) ], [ [ 'sku_id', '=', $v[ 'sku_id' ] ] ]);

            }

//			var_dump("性能：" . $temp);

        }

    }

    /**
     * 商品推广二维码
     * @param $goods_id
     * @param $goods_name
     * @param $site_id
     * @param string $type
     * @return array
     */
    public function qrcode($goods_id, $goods_name, $site_id, $type = "create")
    {
        $data = [
            'site_id' => $site_id,
            'app_type' => "all", // all为全部
            'type' => $type, // 类型 create创建 get获取
            'data' => [
                "goods_id" => $goods_id
            ],
            'page' => '/pages/goods/detail',
            'qrcode_path' => 'upload/qrcode/goods',
            'qrcode_name' => "goods_qrcode_" . $goods_id
        ];

        event('Qrcode', $data, true);
        $app_type_list = config('app_type');
        $path = [];
        foreach ($app_type_list as $k => $v) {
            switch ( $k ) {
                case 'h5':
                    $wap_domain = getH5Domain();
                    $path[ $k ][ 'status' ] = 1;
                    $path[ $k ][ 'url' ] = $wap_domain . $data[ 'page' ] . '?goods_id=' . $goods_id;
                    $path[ $k ][ 'img' ] = "upload/qrcode/goods/goods_qrcode_" . $goods_id . "_" . $k . ".png";
                    break;
                case 'weapp' :
                    $config = new ConfigModel();
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信小程序';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }

                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信小程序';
                    }
                    break;

                case 'wechat' :
                    $config = new ConfigModel();
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信公众号';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }
                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信公众号';
                    }
                    break;
            }

        }

        $return = [
            'path' => $path,
            'goods_name' => $goods_name,
        ];

        return $this->success($return);
    }

    /**
     * 增加商品销量
     * @param $sku_id
     * @param $num
     */
    public function incGoodsSaleNum($sku_id, $num, $store_id = 0)
    {
        $condition = array (
            [ "sku_id", "=", $sku_id ]
        );
        //增加sku销量
        $res = model("goods_sku")->setInc($condition, "sale_num", $num);
        if ($res !== false) {
            $sku_info = model("goods_sku")->getInfo($condition, "goods_id");
            $res = model("goods")->setInc([ [ "goods_id", "=", $sku_info[ "goods_id" ] ] ], "sale_num", $num);
            if ($store_id > 0) {
                $store_sale_model = new StoreSale();
                $store_sale_model->incStoreGoodsSaleNum([ 'sku_id' => $sku_id, 'num' => $num, 'store_id' => $store_id, 'goods_id' => $sku_info[ "goods_id" ] ]);
            }
            return $this->success($res);
        }


        return $this->error($res);
    }

    /**
     * 减少商品销量
     * @param $sku_id
     * @param $num
     */
    public function decGoodsSaleNum($sku_id, $num, $store_id = 0)
    {
        $condition = array (
            [ 'sku_id', '=', $sku_id ]
        );
        //增加sku销量
        $res = model('goods_sku')->setDec($condition, 'sale_num', $num);
        if ($res !== false) {
            $sku_info = model('goods_sku')->getInfo($condition, 'goods_id');
            if (!empty($sku_info)) {
                $res = model('goods')->setDec([ [ 'goods_id', '=', $sku_info[ 'goods_id' ] ] ], 'sale_num', $num);
                if ($store_id > 0) {
                    $store_sale_model = new StoreSale();
                    $store_sale_model->decStoreGoodsSaleNum([ 'sku_id' => $sku_id, 'num' => $num, 'store_id' => $store_id, 'goods_id' => $sku_info[ 'goods_id' ] ]);
                }
            }

            return $this->success($res);
        }
        return $this->error($res);
    }

    /**
     * 修改商品分组
     * @param $label_id
     * @param $site_id
     * @param $goods_ids
     */
    public function modifyGoodsLabel($label_id, $site_id, $goods_ids)
    {
        //获取标签名称
        $label_info = model('goods_label')->getInfo([ [ 'id', '=', $label_id ] ], 'label_name');
        if (empty($label_info)) {
            return $this->error(null, '标签数据有误');
        }

        $result = model('goods')->update([
            'label_id' => $label_id,
            'label_name' => $label_info[ 'label_name' ],
        ], [
            [ 'site_id', '=', $site_id ],
            [ 'goods_id', 'in', $goods_ids ]
        ]);
        return $this->success($result);
    }

    /**
     * 修改商品表单
     * @param $form_id
     * @param $site_id
     * @param $goods_ids
     * @return array
     */
    public function modifyGoodsForm($form_id, $site_id, $goods_ids)
    {
        $result = model('goods')->update([
            'form_id' => $form_id,
        ], [
            [ 'site_id', '=', $site_id ],
            [ 'goods_id', 'in', $goods_ids ]
        ]);
        $result = model('goods_sku')->update([
            'form_id' => $form_id,
        ], [
            [ 'site_id', '=', $site_id ],
            [ 'goods_id', 'in', $goods_ids ]
        ]);
        return $this->success($result);
    }

    /**
     * 修改商品分类Id
     * @param $category_id
     * @param $site_id
     * @param $goods_ids
     * @return array
     */
    public function modifyGoodsCategoryId($category_id, $site_id, $goods_ids)
    {
        $category_json = json_encode($category_id);//分类字符串;
        $category_id = ',' . implode(',', $category_id) . ',';
        model('goods')->update([ 'category_id' => $category_id, 'category_json' => $category_json ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        return $this->success();
    }

    /**
     * 修改商品推荐方式
     * @param $recom_way
     * @param $site_id
     * @param $goods_ids
     * @return array
     */
    public function modifyGoodsShopIntor($recom_way, $site_id, $goods_ids)
    {

        model('goods')->update([ 'recommend_way' => $recom_way ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        model('goods_sku')->update([ 'recommend_way' => $recom_way ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        return $this->success();
    }

    /**
     * 批量设置参与会员优惠
     * @param $is_consume_discount
     * @param $site_id
     * @param $goods_ids
     * @return array
     */
    public function modifyGoodsConsumeDiscount($is_consume_discount, $site_id, $goods_ids)
    {
        model('goods')->update([ 'is_consume_discount' => $is_consume_discount ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        model('goods_sku')->update([ 'is_consume_discount' => $is_consume_discount ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        return $this->success();
    }

    /**
     * 修改商品服务
     * @param $service_ids
     * @param $site_id
     * @param $goods_ids
     * @return array
     */
    public function modifyGoodsService($service_ids, $site_id, $goods_ids)
    {
        model('goods')->update([ 'goods_service_ids' => $service_ids ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        model('goods_sku')->update([ 'goods_service_ids' => $service_ids ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        return $this->success();
    }

    /**
     * 修改商品虚拟销量
     * @param $sale
     * @param $site_id
     * @param $goods_ids
     * @return array
     */
    public function modifyGoodsVirtualSale($sale, $site_id, $goods_ids)
    {
        model('goods')->update([ 'virtual_sale' => $sale ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        model('goods_sku')->update([ 'virtual_sale' => $sale ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        return $this->success();
    }

    /**
     * 修改商品限购
     * @param $max_buy
     * @param $site_id
     * @param $goods_ids
     * @return array
     */
    public function modifyGoodsPurchaseLimit($max_buy, $site_id, $goods_ids)
    {
        model('goods')->update([ 'max_buy' => $max_buy ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        model('goods_sku')->update([ 'max_buy' => $max_buy ], [ [ 'site_id', '=', $site_id ], [ 'goods_id', 'in', $goods_ids ] ]);
        return $this->success();
    }

    /**
     * 设置商品是否包邮
     * @param $is_free_shipping
     * @param $shipping_template
     * @param $site_id
     * @param $goods_ids
     * @return array
     */
    public function modifyGoodsShippingTemplate($is_free_shipping, $shipping_template, $site_id, $goods_ids)
    {
        model('goods')->update([ 'is_free_shipping' => $is_free_shipping, 'shipping_template' => $shipping_template ], [
            [ 'site_id', '=', $site_id ],
            [ 'goods_id', 'in', $goods_ids ],
            [ 'goods_class', '=', 1 ]
        ]);
        model('goods_sku')->update([ 'is_free_shipping' => $is_free_shipping, 'shipping_template' => $shipping_template ], [
            [ 'site_id', '=', $site_id ],
            [ 'goods_id', 'in', $goods_ids ],
            [ 'goods_class', '=', 1 ]
        ]);
        return $this->success();
    }

    /**
     * 获取商品总数
     * @param array $condition
     * @return array
     */
    public function getGoodsTotalCount($condition = [])
    {
        $res = model('goods')->getCount($condition);
        return $this->success($res);
    }

    /**
     * 获取商品会员价
     * @param $sku_id
     * @param $member_id
     * @return array
     */
    public function getGoodsPrice($sku_id, $member_id, $store_id = 0)
    {
        $res = [
            'discount_price' => 0, // 折扣价（默认等于单价）
            'member_price' => 0, // 会员价
            'price' => 0 // 最低价格
        ];
        $condition = [
            [ 'gs.sku_id', '=', $sku_id ]
        ];

        $field = 'gs.is_consume_discount,gs.discount_config,gs.discount_method,gs.price,gs.member_price,gs.discount_price';
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'inner' ],
        ];
        if ($store_id) {
            $join[] = [ 'store_goods_sku sgs', 'gs.sku_id = sgs.sku_id and sgs.store_id=' . $store_id, 'left' ];
            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sgs.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.discount_price,sgs.price), gs.discount_price) as discount_price', $field);
        }
        $goods_sku_info = model("goods_sku")->getInfo($condition, $field, 'gs', $join);

        if (empty($goods_sku_info)) return $this->success($res);

        $res[ 'discount_price' ] = $goods_sku_info[ 'discount_price' ];
        $res[ 'price' ] = $goods_sku_info[ 'discount_price' ];

        if (!addon_is_exit("memberprice")) return $this->success($res);

        if ($goods_sku_info[ 'is_consume_discount' ]) {
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
                                $res[ 'member_price' ] = $goods_sku_info[ 'price' ];
                            } else
                                $res[ 'member_price' ] = number_format($goods_sku_info[ 'price' ] * $value / 10, 2, '.', '');
                            break;
                        case "manjian":
                            if ($value == 0) {
                                $res[ 'member_price' ] = $goods_sku_info[ 'price' ];
                            } else
                                // 满减
                                $res[ 'member_price' ] = number_format($goods_sku_info[ 'price' ] - $value, 2, '.', '');
                            break;
                        case "fixed_price":
                            if ($value == 0) {
                                $res[ 'member_price' ] = $goods_sku_info[ 'price' ];
                            } else
                                // 指定价格
                                $res[ 'member_price' ] = number_format($value, 2, '.', '');
                            break;
                    }
                } else {
                    // 默认按会员享受折扣计算
                    $res[ 'member_price' ] = number_format($goods_sku_info[ 'price' ] * $member_info[ 'consume_discount' ] / 100, 2, '.', '');
                }
                if ($res[ 'member_price' ] < $res[ 'price' ]) {
                    $res[ 'price' ] = $res[ 'member_price' ];
                }
            }

        }
        return $this->success($res);
    }

    /**
     * 获取商品会员价（列表）
     * @param array $goods_list
     * @param $member_id
     * @param $site_id
     */
    public function getGoodsListMemberPrice(array $goods_list, $member_id)
    {
        $alias = 'm';
        $join = [
            [ 'member_level ml', 'ml.level_id = m.member_level', 'inner' ],
        ];
        $member_info = model("member")->getInfo([ [ 'member_id', '=', $member_id ] ], 'm.member_level,ml.consume_discount', $alias, $join);
        if (empty($member_info)) return $goods_list;

        if (!addon_is_exit("memberprice")) return $goods_list;

        foreach ($goods_list as $key => $goods_item) {
            if ($goods_item[ 'is_consume_discount' ]) {
                // 自定义优惠
                if ($goods_item[ 'discount_config' ] == 1) {
                    $member_price_config = json_decode($goods_item[ 'member_price' ], true);
                    $value = $member_price_config[ $goods_item[ 'discount_method' ] ][ $member_info[ 'member_level' ] ] ?? 0;
                    switch ( $goods_item[ 'discount_method' ] ) {
                        case "discount":
                            // 打折
                            if ($value == 0) {
                                $goods_list[ $key ][ 'member_price' ] = $goods_item[ 'price' ];
                            } else
                                $goods_list[ $key ][ 'member_price' ] = number_format($goods_item[ 'price' ] * $value / 10, 2, '.', '');
                            break;
                        case "manjian":
                            if ($value == 0) {
                                $goods_list[ $key ][ 'member_price' ] = $goods_item[ 'price' ];
                            } else
                                // 满减
                                $goods_list[ $key ][ 'member_price' ] = number_format($goods_item[ 'price' ] - $value, 2, '.', '');
                            break;
                        case "fixed_price":
                            if ($value == 0) {
                                $goods_list[ $key ][ 'member_price' ] = $goods_item[ 'price' ];
                            } else
                                // 指定价格
                                $goods_list[ $key ][ 'member_price' ] = number_format($value, 2, '.', '');
                            break;
                    }
                } else {
                    // 默认按会员享受折扣计算
                    $goods_list[ $key ][ 'member_price' ] = number_format($goods_item[ 'price' ] * $member_info[ 'consume_discount' ] / 100, 2, '.', '');
                }
            } else {
                unset($goods_list[ $key ][ 'member_price' ]);
            }
        }
        return $goods_list;
    }

    /**
     * 获取会员卡商品价格
     * @param $sku_id
     * @param $level_id
     * @return array
     */
    public function getMemberCardGoodsPrice($sku_id, $level_id)
    {
        $res = [
            'discount_price' => 0, // 折扣价（默认等于单价）
            'member_price' => 0, // 会员价
            'price' => 0 // 最低价格
        ];

        $goods_sku_info = model("goods_sku")->getInfo([ [ 'sku_id', '=', $sku_id ] ], 'is_consume_discount,discount_config,discount_method,price,member_price,discount_price');
        if (empty($goods_sku_info)) return $this->success($res);

        $res[ 'discount_price' ] = $goods_sku_info[ 'discount_price' ];
        $res[ 'price' ] = $goods_sku_info[ 'discount_price' ];

        $level_info = model("member_level")->getInfo([ [ 'level_id', '=', $level_id ] ], 'consume_discount');

        if (!addon_is_exit("memberprice") || empty($level_info)) return $this->success($res);

        if ($goods_sku_info[ 'is_consume_discount' ]) {
            if ($goods_sku_info[ 'discount_config' ] == 1) {
                // 自定义优惠
                $goods_sku_info[ 'member_price' ] = json_decode($goods_sku_info[ 'member_price' ], true);
                $value = isset($goods_sku_info[ 'member_price' ][ $goods_sku_info[ 'discount_method' ] ][ $level_id ]) ? $goods_sku_info[ 'member_price' ][ $goods_sku_info[ 'discount_method' ] ][ $level_id ] : 0;
                switch ( $goods_sku_info[ 'discount_method' ] ) {
                    case "discount":
                        // 打折
                        if ($value == 0) {
                            $res[ 'member_price' ] = $goods_sku_info[ 'price' ];
                        } else {
                            $res[ 'member_price' ] = number_format($goods_sku_info[ 'price' ] * $value / 10, 2, '.', '');
                        }
                        break;
                    case "manjian":
                        if ($value == 0) {
                            $res[ 'member_price' ] = $goods_sku_info[ 'price' ];
                        } else {
                            // 满减
                            $res[ 'member_price' ] = number_format($goods_sku_info[ 'price' ] - $value, 2, '.', '');
                        }
                        break;
                    case "fixed_price":
                        if ($value == 0) {
                            $res[ 'member_price' ] = $goods_sku_info[ 'price' ];
                        } else {
                            // 指定价格
                            $res[ 'member_price' ] = number_format($value, 2, '.', '');
                        }
                        break;
                }
            } else {
                // 默认按会员享受折扣计算
                $res[ 'member_price' ] = number_format($goods_sku_info[ 'price' ] * $level_info[ 'consume_discount' ] / 100, 2, '.', '');
            }
            if ($res[ 'member_price' ] < $res[ 'price' ]) {
                $res[ 'price' ] = $res[ 'member_price' ];
            }
        }
        return $this->success($res);
    }

    public function getSkuMemberPrice($sku_list, $site_id)
    {
        $member_level_list = model('member_level')->getList([ [ 'site_id', '=', $site_id ] ], 'level_name,level_id,consume_discount', 'level_type asc,growth asc');
        foreach ($sku_list as $k => $sku_item) {
            $member_level = [];
            if ($sku_item[ 'is_consume_discount' ]) {
                foreach ($member_level_list as $level_item) {
                    // 自定义优惠
                    if ($sku_item[ 'discount_config' ] == 1) {
                        $member_price = json_decode($sku_item[ 'member_price' ], true);
                        $value = isset($member_price[ $sku_item[ 'discount_method' ] ][ $level_item[ 'level_id' ] ]) ? $member_price[ $sku_item[ 'discount_method' ] ][ $level_item[ 'level_id' ] ] : 0;
                        switch ( $sku_item[ 'discount_method' ] ) {
                            case "discount":
                                // 打折
                                if ($value == 0) {
                                    $level_item[ 'member_price' ] = $sku_item[ 'price' ];
                                } else
                                    $level_item[ 'member_price' ] = number_format($sku_item[ 'price' ] * $value / 10, 2, '.', '');
                                break;
                            case "manjian":
                                if ($value == 0) {
                                    $level_item[ 'member_price' ] = $sku_item[ 'price' ];
                                } else
                                    // 满减
                                    $level_item[ 'member_price' ] = number_format($sku_item[ 'price' ] - $value, 2, '.', '');
                                break;
                            case "fixed_price":
                                if ($value == 0) {
                                    $level_item[ 'member_price' ] = $sku_item[ 'price' ];
                                } else
                                    // 指定价格
                                    $level_item[ 'member_price' ] = number_format($value, 2, '.', '');
                                break;
                        }
                    } else {
                        $level_item[ 'member_price' ] = number_format($sku_item[ 'price' ] * $level_item[ 'consume_discount' ] / 100, 2, '.', '');
                    }
                    array_push($member_level, $level_item);
                }
            }
            $sku_list[ $k ][ 'member_price_list' ] = $member_level;
        }
        return $sku_list;
    }

    /**
     * 修改当前商品参与的营销活动标识，逗号分隔（限时折扣、团购、拼团、秒杀、专题活动）
     * @param $goods_id
     * @param array $promotion 营销活动标识，【promotion:value】
     * @param bool $is_delete 是否删除
     * @return array
     */
    public function modifyPromotionAddon($goods_id, $promotion = [], $is_delete = false)
    {
        $goods_info = model("goods")->getInfo([ [ 'goods_id', '=', $goods_id ] ], 'promotion_addon');
        $promotion_addon = [];
        if (!empty($goods_info[ 'promotion_addon' ])) {
            $promotion_addon = json_decode($goods_info[ 'promotion_addon' ], true);
        }
        $promotion_addon = array_merge($promotion_addon, $promotion);
        if ($is_delete) {
            foreach ($promotion as $k => $v) {
                unset($promotion_addon[ $k ]);
            }
        }
        if (!empty($promotion_addon)) {
            $promotion_addon = json_encode($promotion_addon);
        } else {
            $promotion_addon = '';
        }
        $res = model("goods")->update([ 'promotion_addon' => $promotion_addon ], [ [ 'goods_id', '=', $goods_id ] ]);
        return $this->success($res);
    }

    /**
     * 获取会员已购该商品数
     * @param $goods_id
     * @param $member_id
     * @return float
     */
    public function getGoodsPurchasedNum($goods_id, $member_id)
    {
        $join = [
            [ 'order o', 'o.order_id = og.order_id', 'left' ]
        ];
        $num = model("order_goods")->getSum([
            [ 'og.member_id', '=', $member_id ],
            [ 'og.goods_id', '=', $goods_id ],
            [ 'o.order_status', '<>', Order::ORDER_CLOSE ],
            [ 'og.refund_status', '<>', OrderRefund::REFUND_COMPLETE ]
        ], 'og.num', 'og', $join);
        return $num;
    }

    /**
     * 判断规格值是否禁用
     * @param $bargain_id
     * @param $site_id
     * @param $goods
     * @return false|string
     */
    public function getGoodsSpecFormat($sku_ids, $goods_spec_format)
    {
        if (!empty($goods_spec_format) && !empty($sku_ids)) {

            $sku_spec_format = model('goods_sku')->getColumn([ [ 'sku_id', 'in', $sku_ids ] ], 'sku_spec_format');

            $sku_spec_format_arr = [];
            foreach ($sku_spec_format as $sku_spec) {
                $format = json_decode($sku_spec, true);
                if (is_array($format)) {
                    foreach ($format as $format_v) {
                        if (empty($sku_spec_format_arr[ $format_v[ 'spec_id' ] ])) {
                            $sku_spec_format_arr[ $format_v[ 'spec_id' ] ] = [];
                        }
                        $sku_spec_format_arr[ $format_v[ 'spec_id' ] ][] = $format_v[ 'spec_value_id' ];
                    }
                }
            }

            $goods_spec_format = json_decode($goods_spec_format, true);
            $count = count($goods_spec_format);
            foreach ($goods_spec_format as $k => $v) {
                foreach ($v[ 'value' ] as $key => $item) {
                    if (!in_array($item[ 'spec_value_id' ], $sku_spec_format_arr[ $item[ 'spec_id' ] ])) {
                        $v[ 'value' ][ $key ][ 'disabled' ] = true;
                    }
                }
                if ($k > 0 || $count == 1) {
                    foreach ($v[ 'value' ] as $key => $item) {
                        if (!in_array($item[ 'sku_id' ] ?? '', $sku_ids)) {
                            $v[ 'value' ][ $key ][ 'disabled' ] = true;
                        }
                    }
                }
                $goods_spec_format[ $k ][ 'value' ] = $v[ 'value' ];
            }
            return $goods_spec_format;
        }
    }


    public function getEmptyGoodsSpecFormat($sku_ids, $sku_id)
    {
        if (!empty($sku_id) && !empty($sku_ids)) {

            $sku_spec_format = model('goods_sku')->getValue([ [ 'sku_id', '=', $sku_id ] ], 'sku_spec_format');
            $sku_spec_format = json_decode($sku_spec_format, true);
            $sku_spec_format = array_column($sku_spec_format, null, 'spec_id');

            $sku_array = [];
            $spec_dict = [];
            foreach ($sku_spec_format as $k => $v) {
                $spec_dict[] = $k;
            }
            $sku_spec_list = model('goods_sku')->getList([ [ 'sku_id', 'in', $sku_ids ] ], 'sku_spec_format,sku_id');
            $sku_spec_list = array_column($sku_spec_list, 'sku_spec_format', 'sku_id');
            foreach ($sku_spec_list as $sku_key => $sku_spec) {
                $format = json_decode($sku_spec, true);
                $format_column = array_column($format, null, 'spec_id');
                $key = 0;
                $num = $this->verifySkuSpec($format_column, $sku_spec_format, $key, $spec_dict);
                if (empty($sku_array)) {
                    $sku_array = array (
                        'sku_id' => $sku_key,
                        'num' => $num
                    );
                } else {
                    if ($num > $sku_array[ 'num' ]) {
                        $sku_array = array (
                            'sku_id' => $sku_key,
                            'num' => $num
                        );
                    }
                }
            }
            $temp_sku_id = $sku_array[ 'sku_id' ] ?? 0;
            return $temp_sku_id;
        }
        return 0;
    }

    public function verifySkuSpec($spec1, $spec2, $key, $spec_dict)
    {
        $real_key = $spec_dict[ $key ];
        $spec1_value = $spec1[ $real_key ];
        $spec2_value = $spec2[ $real_key ];
        $num = 0;
        if ($spec1_value[ 'spec_value_id' ] == $spec2_value[ 'spec_value_id' ]) {
            $num++;
            $key++;
            $num += $this->verifySkuSpec($spec1, $spec2, $key, $spec_dict);
        }
        return $num;

    }

    /**
     * 库存预警数量
     * @param $site_id
     * @return array
     */
    public function getGoodsStockAlarm($site_id)
    {
        $prefix = config('database.connections.mysql.prefix');
        $sql = 'select goods_id from ' . $prefix . 'goods_sku where stock_alarm >= stock and stock_alarm > 0 and is_delete = 0 and goods_state = 1 and site_id = ' . $site_id . ' group by goods_id';
        $data = model('goods')->query($sql);
        if (!empty($data)) {
            $data = array_column($data, 'goods_id');
        }
        return $this->success($data);
    }

    /**
     * 商品导入
     * @param $goods_data
     * @param $img_dir
     * @param $site_id
     * @return array
     */
    public function importGoods($goods_data, $site_id)
    {

        try {
            if (empty($goods_data[ 'goods_name' ])) return $this->error('', '商品名称不能为空');
            if (empty($goods_data[ 'goods_image' ])) return $this->error('', '商品主图不能为空');
            if (empty($goods_data[ 'category_1' ]) && empty($goods_data[ 'category_2' ]) && empty($goods_data[ 'category_3' ])) return $this->error('', '商品分类不能为空');

            // 处理商品分类
            $category_id = '';
            $category_json = [];
            if (!empty($goods_data[ 'category_3' ])) {
                $category_info = model('goods_category')->getInfo([ [ 'level', '=', 3 ], [ 'site_id', '=', $site_id ], [ 'category_full_name', '=', "{$goods_data['category_1']}/{$goods_data['category_2']}/{$goods_data['category_3']}" ] ], 'category_id_1,category_id_2,category_id_3');
                if (!empty($category_info)) {
                    $category_id = "{$category_info['category_id_1']},{$category_info['category_id_2']},{$category_info['category_id_3']}";
                }
            }
            if (!empty($goods_data[ 'category_2' ]) && empty($category_id)) {
                $category_info = model('goods_category')->getInfo([ [ 'level', '=', 2 ], [ 'site_id', '=', $site_id ], [ 'category_full_name', '=', "{$goods_data['category_1']}/{$goods_data['category_2']}" ] ], 'category_id_1,category_id_2');
                if (!empty($category_info)) {
                    $category_id = "{$category_info['category_id_1']},{$category_info['category_id_2']}";
                }
            }
            if (!empty($goods_data[ 'category_1' ]) && empty($category_id)) {
                $category_info = model('goods_category')->getInfo([ [ 'level', '=', 1 ], [ 'site_id', '=', $site_id ], [ 'category_name', '=', "{$goods_data['category_1']}" ] ], 'category_id_1');
                if (!empty($category_info)) {
                    $category_id = "{$category_info['category_id_1']}";
                }
            }
            if (empty($category_id)) return $this->error('', '未找到所填商品分类');
            $category_json = [ $category_id ];

            $sku_data = [];
            $goods_spec_format = [];
            $tag = 0;
            // 处理sku数据
            if (isset($goods_data[ 'sku' ])) {
                foreach ($goods_data[ 'sku' ] as $sku_item) {
                    if (empty($sku_item[ 'sku_data' ])) return $this->error('', '规格数据不能为空');

                    $spec_name = '';
                    $spec_data = explode(';', $sku_item[ 'sku_data' ]);

                    $sku_spec_format = [];
                    foreach ($spec_data as $item) {
                        $spec_item = explode(':', $item);
                        $spec_name .= ' ' . $spec_item[ 1 ];

                        // 规格项
                        $spec_index = array_search($spec_item[ 0 ], array_column($goods_spec_format, 'spec_name'));
                        if (empty($goods_spec_format) || $spec_index === false) {
                            $spec = [
                                'spec_id' => -( $tag + getMillisecond() ),
                                'spec_name' => $spec_item[ 0 ],
                                'value' => []
                            ];
                            array_push($goods_spec_format, $spec);
                            $tag++;
                        } else {
                            $spec = $goods_spec_format[ $spec_index ];
                        }
                        // 规格值
                        $spec_index = array_search($spec_item[ 0 ], array_column($goods_spec_format, 'spec_name'));
                        $spec_value_index = array_search($spec_item[ 1 ], array_column($spec[ 'value' ], 'spec_value_name'));
                        if (empty($spec[ 'value' ]) || $spec_value_index === false) {
                            $spec_value = [
                                'spec_id' => $spec[ 'spec_id' ],
                                'spec_name' => $spec[ 'spec_name' ],
                                'spec_value_id' => -( $tag + getMillisecond() ),
                                'spec_value_name' => $spec_item[ 1 ],
                                'image' => '',
                            ];
                            array_push($goods_spec_format[ $spec_index ][ 'value' ], $spec_value);
                            $tag++;
                        } else {
                            $spec_value = $spec[ 'value' ][ $spec_value_index ];
                        }

                        array_push($sku_spec_format, [
                            'spec_id' => $spec[ 'spec_id' ],
                            'spec_name' => $spec[ 'spec_name' ],
                            'spec_value_id' => $spec_value[ 'spec_value_id' ],
                            'spec_value_name' => $spec_value[ 'spec_value_name' ],
                            'image' => '',
                        ]);
                    }

                    $sku_images_arr = explode(',', $sku_item[ 'sku_image' ]);

                    $sku_temp = [
                        'spec_name' => trim($spec_name),
                        'sku_no' => $sku_item[ 'sku_code' ],
                        'sku_spec_format' => $sku_spec_format,
                        'price' => $sku_item[ 'price' ],
                        'market_price' => $sku_item[ 'market_price' ],
                        'cost_price' => $sku_item[ 'cost_price' ],
                        'stock' => $sku_item[ 'stock' ],
                        'stock_alarm' => $sku_item[ 'stock_alarm' ],
                        'weight' => $sku_item[ 'weight' ],
                        'volume' => $sku_item[ 'volume' ],
                        'sku_image' => empty($sku_item[ 'sku_image' ]) ? '' : $sku_images_arr[ 0 ],
                        'sku_images' => empty($sku_item[ 'sku_image' ]) ? '' : $sku_item[ 'sku_image' ],
                        'sku_images_arr' => empty($sku_item[ 'sku_image' ]) ? [] : $sku_images_arr,
                        'is_default' => 0
                    ];

                    array_push($sku_data, $sku_temp);
                }
            } else {
                $goods_img = explode(',', $goods_data[ 'goods_image' ]);
                $sku_data = [
                    [
                        'sku_id' => 0,
                        'sku_name' => $goods_data[ 'goods_name' ],
                        'spec_name' => '',
                        'sku_spec_format' => '',
                        'price' => empty($goods_data[ 'price' ]) ? 0 : $goods_data[ 'price' ],
                        'market_price' => empty($goods_data[ 'market_price' ]) ? 0 : $goods_data[ 'market_price' ],
                        'cost_price' => empty($goods_data[ 'cost_price' ]) ? 0 : $goods_data[ 'cost_price' ],
                        'sku_no' => $goods_data[ 'goods_code' ],
                        'weight' => empty($goods_data[ 'weight' ]) ? 0 : $goods_data[ 'weight' ],
                        'volume' => empty($goods_data[ 'volume' ]) ? 0 : $goods_data[ 'volume' ],
                        'stock' => empty($goods_data[ 'stock' ]) ? 0 : $goods_data[ 'stock' ],
                        'stock_alarm' => empty($goods_data[ 'stock_alarm' ]) ? 0 : $goods_data[ 'stock_alarm' ],
                        'sku_image' => $goods_img[ 0 ],
                        'sku_images' => $goods_data[ 'goods_image' ]
                    ]
                ];
            }

            if (count($goods_spec_format) > 4) return $this->error('', '最多支持四种规格项');


            $shipping_template = 0;
            $is_free_shipping = $goods_data[ 'is_free_shipping' ] == 1 || $goods_data[ 'is_free_shipping' ] == '是' ? 1 : 0;// 是否免邮
            if ($is_free_shipping == 0 && $goods_data[ 'template_name' ] == "") return $this->error('', '运费模板不能为空');

            if ($is_free_shipping == 0 && $goods_data[ 'template_name' ]) {
                $shipping = model("express_template")->getInfo([ [ 'template_name', '=', $goods_data[ 'template_name' ] ] ]);
                if (empty($shipping)) {
                    return $this->error('', '未找到该运费模板');
                }
                $shipping_template = $shipping[ 'template_id' ];
            }

            $data = [
                'goods_name' => $goods_data[ 'goods_name' ],// 商品名称,
                'goods_attr_class' => '',// 商品类型id,
                'goods_attr_name' => '',// 商品类型名称,
                'site_id' => $site_id,
                'category_id' => ',' . $category_id . ',',
                'category_json' => json_encode($category_json),
                'goods_image' => $goods_data[ 'goods_image' ],// 商品主图路径
                'goods_content' => '',// 商品详情
                'goods_state' => 0, //$goods_data['goods_state'] == 1 || $goods_data['goods_state'] == '是' ? 1 : 0,// 商品状态（1.正常0下架）
                'price' => empty($goods_data[ 'price' ]) ? 0 : $goods_data[ 'price' ],// 商品价格（取第一个sku）
                'market_price' => empty($goods_data[ 'market_price' ]) ? 0 : $goods_data[ 'market_price' ],// 市场价格（取第一个sku）
                'qr_id' => empty($goods_data[ 'qr_id' ]) ? 0 : $goods_data[ 'qr_id' ],// 社群二维码id
                'template_id' => empty($goods_data[ 'template_id' ]) ? 0 : $goods_data[ 'template_id' ],// 海报id
                'is_limit' => empty($goods_data[ 'is_limit' ]) ? 0 : $goods_data[ 'is_limit' ],// 是否限购
                'limit_type' => empty($goods_data[ 'limit_type' ]) ? 0 : $goods_data[ 'limit_type' ],// 限购类型
                'cost_price' => empty($goods_data[ 'cost_price' ]) ? 0 : $goods_data[ 'cost_price' ],// 成本价（取第一个sku）
                'sku_no' => $goods_data[ 'goods_code' ],// 商品sku编码
                'weight' => empty($goods_data[ 'weight' ]) ? 0 : $goods_data[ 'weight' ],// 重量
                'volume' => empty($goods_data[ 'volume' ]) ? 0 : $goods_data[ 'volume' ],// 体积
                'goods_stock' => empty($goods_data[ 'goods_stock' ]) ? 0 : $goods_data[ 'goods_stock' ],// 商品库存（总和）
                'goods_stock_alarm' => empty($goods_data[ 'goods_stock_alarm' ]) ? 0 : $goods_data[ 'goods_stock_alarm' ],// 库存预警
                'is_free_shipping' => $is_free_shipping,
                'shipping_template' => $shipping_template,// 指定运费模板
                'goods_spec_format' => empty($goods_spec_format) ? '' : json_encode($goods_spec_format, JSON_UNESCAPED_UNICODE),// 商品规格格式
                'goods_attr_format' => '',// 商品参数格式
                'introduction' => $goods_data[ 'introduction' ],// 促销语
                'keywords' => $goods_data[ 'keywords' ],// 关键词
                'unit' => $goods_data[ 'unit' ],// 单位
                'sort' => '',// 排序,
                'video_url' => '',// 视频
                'goods_sku_data' => json_encode($sku_data, JSON_UNESCAPED_UNICODE),// SKU商品数据
                'goods_service_ids' => '',// 商品服务id集合
                'label_id' => '',// 商品分组id
                'virtual_sale' => 0,// 虚拟销量
                'max_buy' => 0,// 限购
                'min_buy' => 0,// 起售
                'recommend_way' => 0, // 推荐方式，1：新品，2：精品，3；推荐
                'timer_on' => 0,//定时上架
                'timer_off' => 0,//定时下架
                'brand_id' => 0,
                'is_consume_discount' => $goods_data[ 'is_consume_discount' ] == 1 || $goods_data[ 'is_consume_discount' ] == '是' ? 1 : 0, //是否参与会员折扣
                'support_trade_type' => 'express,store,local'
            ];
            $res = $this->addGoods($data);
            return $res;
        } catch (\Exception $e) {
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 商品用到的分类
     * @param $condition
     * @return array
     */
    public function getGoodsCategoryIds($condition)
    {
        $cache_name = "shop_goods_category_" . md5(json_encode($condition));
        $cache_time = 60;
        $cache_res = Cache::get($cache_name);
        if (empty($cache_res) || time() - $cache_res[ 'time' ] > $cache_time) {
            $list = Db::name('goods')
                ->where($condition)
                ->group('category_id')
                ->column('category_id');
            $category_ids = trim(join('0', $list), ',');
            $category_id_arr = array_unique(explode(',', $category_ids));
            Cache::set($cache_name, [ 'time' => time(), 'data' => $category_id_arr ]);
        } else {
            $category_id_arr = $cache_res[ 'data' ];
        }
        return $this->success($category_id_arr);
    }

    public function urlQrcode($page, $qrcode_param, $promotion_type, $site_id)
    {
        $params = [
            'site_id' => $site_id,
            'data' => $qrcode_param,
            'page' => $page,
            'promotion_type' => $promotion_type,
            'h5_path' => $page . '?goods_id=' . $qrcode_param[ 'goods_id' ],
            'qrcode_path' => 'upload/qrcode/goods',
            'qrcode_name' => [
                'h5_name' => 'goods_qrcode_' . $promotion_type . '_h5_' . $qrcode_param[ 'goods_id' ] . '_' . $site_id,
                'weapp_name' => 'goods_qrcode_' . $promotion_type . '_weapp_' . $qrcode_param[ 'goods_id' ] . '_' . $site_id
            ]
        ];

        $solitaire = event('ExtensionInformation', $params);
        return $this->success($solitaire[ 0 ]);
    }

    /**
     * 批量修改商品库存
     * @param $goods_sku_array
     * @return array
     */
    public function editGoodsSkuStock($goods_sku_array, $stock, $type, $site_id = 0)
    {
        $goods_sku_model = new GoodsStock();
        model('goods')->startTrans();
        try {
            $stock_list = [];
            foreach ($goods_sku_array as $k => $v) {

                if ($type == 'inc') {
                    $item_stock = $v[ 'stock' ] + $stock;
//                    $sku_stock_data = [
//                        'sku_id' => $v[ 'sku_id' ],
//                        'num' => $stock
//                    ];
//                    $goods_sku_model->incStock($sku_stock_data);
                } else {
                    $item_stock = $v[ 'stock' ] - $stock;
                    $item_stock = $item_stock < 0 ? 0 : $item_stock;
//                    $sku_stock_data = [
//                        'sku_id' => $v[ 'sku_id' ],
//                        'num' => $v[ 'stock' ] >= $stock ? $stock : $v[ 'stock' ]
//                    ];
//                    $goods_sku_model->decStock($sku_stock_data);
                }
                $goods_class = $v[ 'goods_class' ];
                $stock_list[] = [ 'sku_id' => $v[ 'sku_id' ], 'stock' => $item_stock, 'goods_class' => $v[ 'goods_class' ] ];
            }

            $goods_stock_model = new \app\model\stock\GoodsStock();
            $result = $goods_stock_model->changeGoodsStock([
                'site_id' => $site_id,
                'goods_sku_list' => $stock_list
            ]);
            if ($result[ 'code' ] < 0) {
                model('goods')->rollback();
                return $result;
            }
            model('goods')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('goods')->rollback();
            return $this->error($e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * 获取商品图片大小
     */
    public function getGoodsImage($goods_images, $site_id)
    {
        $list = model('album_pic')->getList([ [ 'pic_path', 'in', $goods_images ], [ 'site_id', '=', $site_id ] ], 'pic_path,pic_spec');
        return $this->success($list);
    }
}