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

use app\model\BaseModel;
use app\model\order\OrderCommon;
use app\model\system\Cron;
use app\model\system\Stat;
use addon\discount\model\Discount;
use app\model\message\Message;

/**
 * 虚拟商品
 */
class VirtualGoods extends BaseModel
{
    private $goods_class = array ( 'id' => 2, 'name' => '虚拟商品' );

    private $goods_state = array (
        1 => '销售中',
        0 => '仓库中'
    );

    public function getGoodsState()
    {
        return $this->goods_state;
    }

    public function getGoodsClass(){
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
            if ($data['verify_validity_type'] == 1) {
                if(empty($data['virtual_indate'])){
                    return $this->error('','有效期不能为空');
                }
            } else if ($data['verify_validity_type'] == 2) {
                if(empty($data['virtual_indate'])){
                    return $this->error('','有效期不能为空');
                }
            }

            //获取标签名称
            $label_name = '';
            if($data[ 'label_id' ]){
                $label_info = model('goods_label')->getInfo([['id', '=', $data[ 'label_id' ]]], 'label_name');
                $label_name = $label_info['label_name'] ?? '';
            }
            $brand_name = '';
            if($data[ 'brand_id' ]){
                $brand_info = model('goods_brand')->getInfo([['brand_id', '=', $data[ 'brand_id' ]]], 'brand_name');
                $brand_name = $brand_info['brand_name'] ?? '';
            }

            $goods_data = array (
                'goods_image' => $goods_image,
//                'goods_stock' => $data[ 'goods_stock' ],
                'price' => !empty($data[ 'goods_sku_data' ][ 0 ][ 'price' ])?$data[ 'goods_sku_data' ][ 0 ][ 'price' ]:'',
                'market_price' => !empty($data[ 'goods_sku_data' ][ 0 ][ 'market_price' ])?$data[ 'goods_sku_data' ][ 0 ][ 'market_price' ]:'',
                'cost_price' => !empty($data[ 'goods_sku_data' ][ 0 ][ 'cost_price' ])?$data[ 'goods_sku_data' ][ 0 ][ 'cost_price' ]:'',
                'goods_spec_format' => $data[ 'goods_spec_format' ],
                'category_id' => $data[ 'category_id' ],
                'category_json' => $data[ 'category_json' ],
                'label_id' => $data[ 'label_id' ],
                'label_name' => $label_name,
                'timer_on' => $data[ 'timer_on' ],
                'timer_off' => $data[ 'timer_off' ],
                'is_consume_discount' => $data['is_consume_discount'],
                'sale_show' => $data['sale_show'] ?? 1,
                'stock_show' => $data['stock_show'] ?? 1,
                'market_price_show' => $data['market_price_show'] ?? 1,
                'barrage_show' => $data['barrage_show'] ?? 1,
                'virtual_deliver_type' => $data['virtual_deliver_type'] ?? '',
                'virtual_receive_type' => $data['virtual_receive_type'] ?? '',
            );

            $common_data = array (
                'goods_name' => $data[ 'goods_name' ],
                'goods_class' => $this->goods_class[ 'id' ],
                'goods_class_name' => $this->goods_class[ 'name' ],
                'goods_attr_class' => $data[ 'goods_attr_class' ],
                'goods_attr_name' => $data[ 'goods_attr_name' ],
                'is_limit' => isset($data[ 'is_limit' ]) ? $data[ 'is_limit' ] : 0,
                'limit_type' => isset($data[ 'limit_type' ]) ? $data[ 'limit_type' ]:1,
                'site_id' => $data[ 'site_id' ],
                'goods_content' => $data[ 'goods_content' ],
                'goods_state' => $data[ 'goods_state' ],
                'goods_stock_alarm' => $data[ 'goods_stock_alarm' ],
                'is_virtual' => 1,
                'verify_validity_type' => $data['verify_validity_type'] ?? 0,
                'is_need_verify' => $data['is_need_verify'],
                'virtual_indate' => $data[ 'virtual_indate' ],
                'goods_attr_format' => $data[ 'goods_attr_format' ],
                'introduction' => $data[ 'introduction' ],
                'keywords' => $data[ 'keywords' ],
                'unit' => $data[ 'unit' ],
                'brand_id' => $data[ 'brand_id' ],//品牌id
                'brand_name' => $brand_name,//品牌名称
                'video_url' => $data[ 'video_url' ],
                'sort' => $data[ 'sort' ],
                'goods_service_ids' => $data[ 'goods_service_ids' ],
                'create_time' => time(),
                'virtual_sale' => $data[ 'virtual_sale' ],
                'max_buy' => $data[ 'max_buy' ],
                'min_buy' => $data[ 'min_buy' ],
                'recommend_way' => $data[ 'recommend_way' ],
                'qr_id' => isset($data[ 'qr_id' ]) ? $data['qr_id']:0,
                'template_id' => isset($data[ 'template_id' ]) ? $data['template_id']:0,
                'form_id' => $data[ 'form_id' ] ?? 0,
                'sale_channel' => $data[ 'sale_channel' ] ?? 'all',
                'sale_store' => $data[ 'sale_store' ] ?? 'all',
            );
            $goods_id = model('goods')->add(array_merge($goods_data, $common_data));

            $goods_stock = 0;

            $sku_arr = array ();
            $sku_stock_list = [];
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
//                    'stock' => $item[ 'stock' ],
                    'stock_alarm' => $item[ 'stock_alarm' ],
                    'sku_image' => !empty($item[ 'sku_image' ]) ? $item[ 'sku_image' ] : $first_image,
                    'sku_images' => $item[ 'sku_images' ],
                    'goods_id' => $goods_id,
                    'is_default' => $item[ 'is_default' ] ?? 0,
                    'is_consume_discount' => $data['is_consume_discount'],
                    'site_id' => $data['site_id'],
                    'verify_num' => $item['verify_num'] ?? 1
                );
                $sku_stock_list[] = ['stock' => $item[ 'stock' ], 'site_id' => $data['site_id'], 'goods_class' => $common_data['goods_class']];
                $sku_arr[] = array_merge($sku_data, $common_data);
            }


            model('goods_sku')->addList($sku_arr);

            // 赋值第一个商品sku_id
            $first_info = model('goods_sku')->getFirstData([ 'goods_id' => $goods_id ], 'sku_id', 'is_default desc,sku_id asc');
            model('goods')->update([ 'sku_id' => $first_info[ 'sku_id' ]], [ [ 'goods_id', '=', $goods_id ] ]);

            if (!empty($data[ 'goods_spec_format' ])) {
                // 刷新SKU商品规格项 / 规格值JSON字符串
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

            //添加统计
            $stat = new Stat();
            $stat->switchStat(['type' => 'add_goods', 'data' => [ 'add_goods_count' => 1, 'site_id' => $data[ 'site_id' ] ]]);

            // 商品设置库存
            $goods_stock_model = new \app\model\stock\GoodsStock();
            $sku_list = model('goods_sku')->getList([ 'goods_id' => $goods_id ], 'sku_id');
            foreach($sku_stock_list as $k => $v){
                $v['sku_id'] = $sku_list[$k]['sku_id'];
                $goods_stock_model->changeGoodsStock($v);
            }

            model('goods')->commit();
            return $this->success($goods_id);
        } catch (\Exception $e) {
            model('goods')->rollback();
            return $this->error($e->getMessage());
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

            //获取标签名称
            $label_name = '';
            if($data[ 'label_id' ]){
                $label_info = model('goods_label')->getInfo([['id', '=', $data[ 'label_id' ]]], 'label_name');
                $label_name = $label_info['label_name'] ?? '';
            }
            $brand_name = '';
            if($data[ 'brand_id' ]){
                $brand_info = model('goods_brand')->getInfo([['brand_id', '=', $data[ 'brand_id' ]]], 'brand_name');
                $brand_name = $brand_info['brand_name'] ?? '';
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
                'is_consume_discount' => $data['is_consume_discount'],
                'verify_validity_type' => $data['verify_validity_type'],
                'is_need_verify' => $data['is_need_verify'],
                'sale_show' => $data['sale_show'],
                'stock_show' => $data['stock_show'],
                'market_price_show' => $data['market_price_show'],
                'barrage_show' => $data['barrage_show'],

                'virtual_deliver_type' => $data['virtual_deliver_type'],
                'virtual_receive_type' => $data['virtual_receive_type']
            );

            $common_data = array (
                'goods_name' => $data[ 'goods_name' ],
                'goods_class' => $this->goods_class[ 'id' ],
                'goods_class_name' => $this->goods_class[ 'name' ],
                'goods_attr_class' => $data[ 'goods_attr_class' ],
                'goods_attr_name' => $data[ 'goods_attr_name' ],
                'is_limit' => isset($data[ 'is_limit' ]) ? $data[ 'is_limit' ] : 0,
                'limit_type' => isset($data[ 'limit_type' ]) ? $data[ 'limit_type' ]:1,
                'site_id' => $data[ 'site_id' ],
                'goods_content' => $data[ 'goods_content' ],
                'goods_state' => $data[ 'goods_state' ],
                'goods_stock_alarm' => $data[ 'goods_stock_alarm' ],
                'is_virtual' => 1,
                'virtual_indate' => $data[ 'virtual_indate' ],
                'goods_attr_format' => $data[ 'goods_attr_format' ],
                'introduction' => $data[ 'introduction' ],
                'keywords' => $data[ 'keywords' ],
                'unit' => $data[ 'unit' ],
                'video_url' => $data[ 'video_url' ],
                'brand_id' => $data[ 'brand_id' ],//品牌id
                'brand_name' => $brand_name,//品牌名称
                'sort' => $data[ 'sort' ],
                'goods_service_ids' => $data[ 'goods_service_ids' ],
                'modify_time' => time(),
                'virtual_sale' => $data[ 'virtual_sale' ],
                'max_buy' => $data[ 'max_buy' ],
                'min_buy' => $data[ 'min_buy' ],
                'recommend_way' => $data[ 'recommend_way' ],
                'qr_id' => isset($data[ 'qr_id' ]) ? $data['qr_id']:0,
                'template_id' => isset($data[ 'template_id' ]) ? $data['template_id']:0,
                'form_id' => $data[ 'form_id' ] ?? 0,
                'sale_channel' => $data[ 'sale_channel' ] ?? 'all',
                'sale_store' => $data[ 'sale_store' ] ?? 'all',
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
                            'sku_image' => !empty($item[ 'sku_image' ]) ? $item[ 'sku_image' ] : $first_image,
                            'sku_images' => $item[ 'sku_images' ],
                            'goods_id' => $goods_id,
                            'is_default' => $item[ 'is_default' ] ?? 0,
                            'is_consume_discount' => $data['is_consume_discount'],
                            'verify_num' => $item['verify_num']
                        );
                        $sku_arr[] = array_merge($sku_data, $common_data);
                        $sku_stock_list[] = ['stock' => $item[ 'stock' ], 'site_id' => $data['site_id'], 'goods_class' => $common_data['goods_class']];
                    }

                    model('goods_sku')->addList($sku_arr);
                    $sku_list = model('goods_sku')->getList([ 'goods_id' => $goods_id ], 'sku_id');
                    foreach($sku_stock_list as $k => $v){
                        $sku_stock_list[$k]['sku_id'] = $sku_list[$k]['sku_id'];
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
                            'sku_image' => !empty($item[ 'sku_image' ]) ? $item[ 'sku_image' ] : $first_image,
                            'sku_images' => $item[ 'sku_images' ],
                            'goods_id' => $goods_id,
                            'is_default' => $item[ 'is_default' ] ?? 0,
                            'verify_num' => $item['verify_num'],
                            'is_consume_discount' => $data['is_consume_discount']
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
                        $sku_stock_list[] = ['stock' => $item[ 'stock' ],'sku_id' => $item[ 'sku_id' ], 'site_id' => $data['site_id'], 'goods_class' => $common_data['goods_class']];
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
                        'sku_image' => !empty($item[ 'sku_image' ]) ? $item[ 'sku_image' ] : $first_image,
                        'sku_images' => $item[ 'sku_images' ],
                        'goods_id' => $goods_id,
                        'is_default' => $item[ 'is_default' ] ?? 0,
                        'verify_num' => $item['verify_num'],
                        'is_consume_discount' => $data['is_consume_discount']
                    );
                    $sku_arr[] = array_merge($sku_data, $common_data);
                    $sku_stock_list[] = ['stock' => $item[ 'stock' ], 'site_id' => $data['site_id'], 'goods_class' => $common_data['goods_class']];
                }

                model('goods_sku')->addList($sku_arr);

                $sku_list = model('goods_sku')->getList([ 'goods_id' => $goods_id ], 'sku_id');
                foreach($sku_stock_list as $k => $v){
                    $sku_stock_list[$k]['sku_id'] = $sku_list[$k]['sku_id'];
                }
            }

            // 赋值第一个商品sku_id
            $first_info = model('goods_sku')->getFirstData([ 'goods_id' => $goods_id ], 'sku_id', 'is_default desc,sku_id asc');
            model('goods')->update([ 'sku_id' => $first_info[ 'sku_id' ] ], [ [ 'goods_id', '=', $goods_id ] ]);

            if (!empty($data[ 'goods_spec_format' ])) {
                // 刷新SKU商品规格项 / 规格值JSON字符串
                $this->dealGoodsSkuSpecFormat($goods_id, $data[ 'goods_spec_format' ]);
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
            // 商品设置库存
            //核验和校准改变的sku
            $goods_stock_model->checkExistGoodsSku(['goods_id' => $goods_id]);
            foreach($sku_stock_list as $k => $v){
                $goods_stock_model->changeGoodsStock($v);
            }

            model('goods')->commit();
            return $this->success($goods_id);
        } catch (\Exception $e) {
            model('goods')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取商品信息
     * @param array $condition
     * @param string $field
     */
    public function getGoodsInfo($condition, $field = 'goods_id,goods_name,goods_class,goods_class_name,goods_attr_class,goods_attr_name,,goods_image,goods_content,goods_state,price,market_price,cost_price,goods_stock,goods_stock_alarm,goods_spec_format,goods_attr_format,introduction,keywords,unit,sort,video_url,evaluate,virtual_indate')
    {
        $info = model('goods')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取商品列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getGoodsList($condition = [], $field = 'goods_id,goods_class,goods_class_name,goods_attr_name,goods_name,site_id,,sort,goods_image,goods_content,goods_state,price,market_price,cost_price,goods_stock,goods_stock_alarm,is_virtual,goods_spec_format,goods_attr_format,create_time', $order = 'create_time desc', $limit = null)
    {
        $list = model('goods')->getList($condition, $field, $order, '', '', '', $limit);
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
    public function getGoodsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'create_time desc', $field = 'goods_id,goods_name,site_id,goods_image,goods_state,price,goods_stock,create_time,sale_num')
    {
        $list = model('goods')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取商品sku列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getGoodsSkuList($condition = [], $field = 'sku_id,sku_name,price,stock,sale_num,sku_image,goods_id,goods_name,site_id,spec_name', $order = 'create_time desc', $limit = null)
    {
        $list = model('goods_sku')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 刷新SKU商品规格项/规格值JSON字符串
     * @param int $goods_id 商品id
     * @param string $goods_spec_format 商品完整规格项/规格值json
     */
    private function dealGoodsSkuSpecFormat($goods_id, $goods_spec_format)
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

    /************************************************************************* 购买的虚拟产品 start *******************************************************************/

    /**
     * 生成购买的虚拟产品
     * @param $params
     * @return array
     */
    public function addGoodsVirtual($params)
    {
        $data = array (
            'site_id' => $params['site_id'],
            'order_id' => $params['order_id'],
            'order_no' => $params['order_no'],
            'sku_id' => $params['sku_id'],
            'sku_name' => $params['sku_name'],
            'code' => $params['code'] ?? '',
            'member_id' => $params['member_id'],
            'sku_image' => $params['sku_image'],
            'expire_time' => $params['expire_time'] ?? '',
            'card_info' => $params['card_info'] ?? '',
            'sold_time' => $params['sold_time'] ?? time(),
            'goods_id' => $params['goods_id'],
            'verify_total_count' => $params['verify_total_count'] ?? 0
        );
        $res = model("goods_virtual")->add($data);
        return $this->success($res);
    }

    /**
     * 删除
     * @param $condition
     */
    public function deleteGoodsVirtual($condition)
    {
        $res = model("goods_virtual")->delete($condition);
        if ($res === false)
            return $this->error();

        return $this->success();
    }

    /**
     * 核销虚拟商品
     * @param $code
     */
    public function verify($code)
    {
        $goods_virtual_info = model("goods_virtual")->getInfo([ [ "code", '=', $code ], [ "is_veirfy", "=", 0 ] ]);
        if (empty($goods_virtual_info))
            return $this->error([], '虚拟商品不存在或已失效');//虚拟商品不存在或已核销

        $order_common_model = new OrderCommon();
        $local_result = $order_common_model->verifyOrderLock($goods_virtual_info[ 'order_id' ]);
        if ($local_result[ "code" ] < 0)
            return $local_result;
        //核销发送通知
        $message_model = new Message();
        $message_model->sendMessage(['keywords' => "VERIFY", 'order_id' => $goods_virtual_info['order_id'], 'site_id' => $goods_virtual_info['site_id']]);
        model('goods_virtual')->startTrans();
        try{
            $verify_total_count = $goods_virtual_info['verify_total_count'];//总核销次数
            $verify_use_num = $goods_virtual_info['verify_use_num'];//已核销次数
            $now_verify_use_num = $verify_use_num + 1;
            $data = array (
                "verify_time" => time(),
                'verify_use_num' => $now_verify_use_num
            );
            $is_veirfy = 0;
            if($now_verify_use_num >= $verify_total_count){
                $data['is_veirfy'] = 1;
                $is_veirfy = 1;
            }

            $res = model("goods_virtual")->update($data, [ [ "code", '=', $code ], [ "is_veirfy", "=", 0 ] ]);

            //判断是否全部核销完毕
//            $count = model('goods_virtual')->getCount([['order_id','=',$goods_virtual_info[ 'order_id' ]],['is_veirfy','=',0]]);
//            if($count == 0){//全部核销完毕

            if($is_veirfy == 1){//全部核销完毕
                //订单执行自动完成
                $result = event('CronOrderTakeDelivery',['relate_id' => $goods_virtual_info[ 'order_id' ]],true);
                if($result['code'] < 0){
                    model('goods_virtual')->rollback();
                    return $result;
                }
            }

            model('goods_virtual')->commit();
            return $this->success($res);
        }catch(\Exception $e){

            model('goods_virtual')->rollback();
            return $this->error('',$e->getMessage());
        }
    }


    /**
     * 虚拟商品详情
     * @param $condition
     */
    public function getVirtualGoodsInfo($condition, $field = "*")
    {
        $info = model('goods_virtual')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取虚拟商品分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getVirtualGoodsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'id desc', $field = '*', $alias = '', $join = [])
    {
        $list = model('goods_virtual')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }
    /************************************************************************* 购买的虚拟产品 end *******************************************************************/

    /**
     * 商品导入
     * @param $goods_data
     * @param $site_id
     * @return array
     */
    public function importGoods($goods_data, $site_id){
        try {
            if (empty($goods_data['goods_name'])) return $this->error('', '商品名称不能为空');
            if (empty($goods_data['goods_image'])) return $this->error('', '商品主图不能为空');
            if (empty($goods_data['category_1']) && empty($goods_data['category_2']) && empty($goods_data['category_3'])) return $this->error('', '商品分类不能为空');

            // 处理商品分类
            $category_id = '';
            $category_json = [];
            if (!empty($goods_data['category_3'])) {
                $category_info = model('goods_category')->getInfo([ ['level', '=', 3],['site_id', '=', $site_id ], ['category_full_name', '=', "{$goods_data['category_1']}/{$goods_data['category_2']}/{$goods_data['category_3']}"] ], 'category_id_1,category_id_2,category_id_3');
                if (!empty($category_info)) {
                    $category_id = "{$category_info['category_id_1']},{$category_info['category_id_2']},{$category_info['category_id_3']}";
                }
            }
            if (!empty($goods_data['category_2']) && empty($category_id)) {
                $category_info = model('goods_category')->getInfo([ ['level', '=', 2],['site_id', '=', $site_id ], ['category_full_name', '=', "{$goods_data['category_1']}/{$goods_data['category_2']}"] ], 'category_id_1,category_id_2');
                if (!empty($category_info)) {
                    $category_id = "{$category_info['category_id_1']},{$category_info['category_id_2']}";
                }
            }
            if (!empty($goods_data['category_1']) && empty($category_id)) {
                $category_info = model('goods_category')->getInfo([ ['level', '=', 1],['site_id', '=', $site_id ], ['category_name', '=', "{$goods_data['category_1']}"] ], 'category_id_1');
                if (!empty($category_info)) {
                    $category_id = "{$category_info['category_id_1']}";
                }
            }
            if (empty($category_id)) return $this->error('', '未找到所填商品分类');
            $category_json = [$category_id];

            $sku_data = [];
            $goods_spec_format = [];
            $tag = 0;
            // 处理sku数据
            if (isset($goods_data['sku'])) {
                foreach ($goods_data['sku'] as $sku_item) {
                    if (empty($sku_item['sku_data'])) return $this->error('', '规格数据不能为空');

                    $spec_name = '';
                    $spec_data = explode(';', $sku_item['sku_data']);

                    $sku_spec_format = [];
                    foreach ($spec_data as $item) {
                        $spec_item = explode(':', $item);
                        $spec_name .= ' ' . $spec_item[1];

                        // 规格项
                        $spec_index = array_search($spec_item[0], array_column($goods_spec_format, 'spec_name'));
                        if (empty($goods_spec_format) || $spec_index === false) {
                            $spec = [
                                'spec_id' => -($tag + getMillisecond()),
                                'spec_name' => $spec_item[0],
                                'value' => []
                            ];
                            array_push($goods_spec_format, $spec);
                            $tag++;
                        } else {
                            $spec = $goods_spec_format[$spec_index];
                        }
                        // 规格值
                        $spec_index = array_search($spec_item[0], array_column($goods_spec_format, 'spec_name'));
                        $spec_value_index = array_search($spec_item[1], array_column($spec['value'], 'spec_value_name'));
                        if (empty($spec['value']) || $spec_value_index === false) {
                            $spec_value = [
                                'spec_id' => $spec['spec_id'],
                                'spec_name' => $spec['spec_name'],
                                'spec_value_id' => -($tag + getMillisecond()),
                                'spec_value_name' => $spec_item[1],
                                'image' => '',
                            ];
                            array_push($goods_spec_format[$spec_index]['value'], $spec_value);
                            $tag++;
                        } else {
                            $spec_value = $spec['value'][$spec_value_index];
                        }

                        array_push($sku_spec_format, [
                            'spec_id' => $spec['spec_id'],
                            'spec_name' => $spec['spec_name'],
                            'spec_value_id' => $spec_value['spec_value_id'],
                            'spec_value_name' => $spec_value['spec_value_name'],
                            'image' => '',
                        ]);
                    }

                    $sku_images_arr = explode(',', $sku_item['sku_image']);

                    $sku_temp = [
                        'spec_name' => trim($spec_name),
                        'sku_no' => $sku_item['sku_code'],
                        'sku_spec_format' => $sku_spec_format,
                        'price' => $sku_item['price'],
                        'market_price' => $sku_item['market_price'],
                        'cost_price' => $sku_item['cost_price'],
                        'stock' => $sku_item['stock'],
                        'stock_alarm' => $sku_item['stock_alarm'],
                        'verify_num' => empty($sku_item['verify_num']) ? 1 : $sku_item['verify_num'],
                        'sku_image' => empty($sku_item['sku_image']) ? '' : $sku_images_arr[0],
                        'sku_images' => empty($sku_item['sku_image']) ? '' : $sku_item['sku_image'],
                        'sku_images_arr' => empty($sku_item['sku_image']) ? [] : $sku_images_arr,
                        'is_default' => 0
                    ];

                    array_push($sku_data, $sku_temp);
                }
            } else {
                $goods_img = explode(',', $goods_data['goods_image']);
                $sku_data = [
                    [
                        'sku_id' => 0,
                        'sku_name' => $goods_data['goods_name'],
                        'spec_name' => '',
                        'sku_spec_format' => '',
                        'price' => empty($goods_data['price']) ? 0 : $goods_data['price'],
                        'market_price' => empty($goods_data['market_price']) ? 0 : $goods_data['market_price'],
                        'cost_price' => empty($goods_data['cost_price']) ? 0 : $goods_data['cost_price'],
                        'sku_no' => $goods_data['goods_code'],
                        'verify_num' => empty($goods_data['verify_num']) ? 1 : $goods_data['verify_num'],
                        'stock' => empty($goods_data['stock']) ? 0 : $goods_data['stock'],
                        'stock_alarm' => empty($goods_data['stock_alarm']) ? 0 : $goods_data['stock_alarm'],
                        'sku_image' => $goods_img[0],
                        'sku_images' => $goods_data['goods_image']
                    ]
                ];
            }

            if (count($goods_spec_format) > 4) return $this->error('', '最多支持四种规格项');

            $data = [
                'goods_name' => $goods_data['goods_name'],// 商品名称,
                'goods_attr_class' => '',// 商品类型id,
                'goods_attr_name' => '',// 商品类型名称,
                'site_id' => $site_id,
                'category_id' => ',' . $category_id . ',',
                'category_json' => json_encode($category_json),
                'goods_image' => $goods_data['goods_image'],// 商品主图路径
                'goods_content' => '',// 商品详情
                'goods_state' => 0, //$goods_data['goods_state'] == 1 || $goods_data['goods_state'] == '是' ? 1 : 0,// 商品状态（1.正常0下架）
                'price' => empty($goods_data['price']) ? 0 : $goods_data['price'],// 商品价格（取第一个sku）
                'market_price' => empty($goods_data['market_price']) ? 0 : $goods_data['market_price'],// 市场价格（取第一个sku）
                'cost_price' => empty($goods_data['cost_price']) ? 0 : $goods_data['cost_price'],// 成本价（取第一个sku）
                'sku_no' => $goods_data['goods_code'],// 商品sku编码
                'goods_stock' => empty($goods_data['goods_stock']) ? 0 : $goods_data['goods_stock'],// 商品库存（总和）
                'goods_stock_alarm' => empty($goods_data['goods_stock_alarm']) ? 0 : $goods_data['goods_stock_alarm'],// 库存预警
                'goods_spec_format' => empty($goods_spec_format) ? '' : json_encode($goods_spec_format, JSON_UNESCAPED_UNICODE),// 商品规格格式
                'goods_attr_format' => '',// 商品参数格式
                'introduction' => $goods_data['introduction'],// 促销语
                'keywords' => $goods_data['keywords'],// 关键词
                'unit' => $goods_data['unit'],// 单位
                'sort' => '',// 排序,
                'qr_id' => empty($goods_data['qr_id']) ? 0 : $goods_data['qr_id'],// 社群二维码id
                'template_id' => empty($goods_data['template_id']) ? 0 : $goods_data['template_id'],// 海报id
                'is_limit' => empty($goods_data['is_limit']) ? 0 : $goods_data['is_limit'],// 是否限购
                'limit_type' => empty($goods_data['limit_type']) ? 0 : $goods_data['limit_type'],// 限购类型
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
                'is_consume_discount' => $goods_data['is_consume_discount'] == 1 || $goods_data['is_consume_discount'] == '是' ? 1 : 0, //是否参与会员折扣
                'is_need_verify' => $goods_data['is_need_verify'] == 1 || $goods_data['is_need_verify'] == '是' ? 1 : 0,
                'verify_validity_type' => empty($goods_data['verify_validity_type']) ? 0 : $goods_data['verify_validity_type'], // 核销有效期类型
                'virtual_indate' => 0,// 虚拟商品有效期
                'brand_id' => 0,
                'virtual_deliver_type' => 'auto_deliver',
                'virtual_receive_type' => 'auto_receive'
            ];
            if ($goods_data['verify_validity_type'] == 1) {
                $data['virtual_date'] = $goods_data['virtual_indate'];
            } elseif ($goods_data['verify_validity_type'] == 2) {
                $t1 = intval(($goods_data['virtual_indate']- 25569) * 3600 * 24); //转换成1970年以来的秒数
                $createtime = gmdate('Y-m-d H:i:s',$t1);//格式化时间
                $data['virtual_time'] = $createtime;
            }
            $res = $this->addGoods($data);
            return $res;
        } catch (\Exception $e) {
            return $this->error('', $e->getMessage().$e->getFile().$e->getLine());
        }
    }
}