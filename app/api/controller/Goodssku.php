<?php
/**
 * Goodssku.php
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 * @author : niuteam
 * @date : 2022.8.8
 * @version : v5.0.0.1
 */

namespace app\api\controller;

use addon\bundling\api\controller\Bundling;
use addon\coupon\api\controller\Coupon;
use addon\coupon\model\CouponType;
use addon\manjian\api\controller\Manjian;
use app\model\express\Config as ExpressConfig;
use app\model\goods\Goods;
use app\model\goods\GoodsCategory as GoodsCategoryModel;
use app\model\goods\GoodsService;
use app\model\member\Member as MemberModel;
use app\model\web\Config as ConfigModel;
use think\facade\Db;

/**
 * 商品sku
 * @author Administrator
 *
 */
class Goodssku extends BaseApi
{
    public function __construct()
    {
        parent::__construct();

        $this->initStoreData();
    }

    /**
     * 【废弃】基础信息
     */
    public function info()
    {
        $sku_id = isset($this->params[ 'sku_id' ]) ? $this->params[ 'sku_id' ] : 0;
        if (empty($sku_id)) {
            return $this->response($this->error('', 'REQUEST_SKU_ID'));
        }

        $goods = new Goods();
        $field = 'gs.goods_id,gs.sku_id,g.goods_image,g.goods_name,g.keywords,gs.sku_name,gs.sku_spec_format,gs.price,gs.market_price,gs.discount_price,gs.promotion_type
        ,gs.start_time,gs.end_time,gs.stock,gs.sku_image,gs.sku_images,gs.goods_spec_format,gs.unit,gs.max_buy,gs.min_buy,gs.is_limit,gs.limit_type';

        $info = $goods->getGoodsSkuDetail($sku_id, $this->site_id, $field);

        if (empty($info[ 'data' ])) return $this->response($this->error());

        $token = $this->checkToken();
        if ($token[ 'code' ] >= 0) {
            // 是否参与会员等级折扣
            $goods_member_price = $goods->getGoodsPrice($sku_id, $this->member_id)[ 'data' ];
            if (!empty($goods_member_price[ 'member_price' ])) {
                $info[ 'data' ][ 'member_price' ] = $goods_member_price[ 'member_price' ];
            }
            if ($info[ 'data' ][ 'is_limit' ] && $info[ 'data' ][ 'limit_type' ] == 2 && $info[ 'data' ][ 'max_buy' ] > 0) $res[ 'goods_sku_detail' ][ 'purchased_num' ] = $goods->getGoodsPurchasedNum($info[ 'data' ][ 'goods_id' ], $this->member_id);
        }

        // 查询当前商品参与的营销活动信息
        $goods_promotion = event('GoodsPromotion', [ 'goods_id' => $info[ 'data' ][ 'goods_id' ], 'sku_id' => $info[ 'data' ][ 'sku_id' ] ]);
        $info[ 'data' ][ 'goods_promotion' ] = $goods_promotion;

        //判断是否参与预售
        $is_join_presale = event('IsJoinPresale', [ 'sku_id' => $sku_id ], true);
        if (!empty($is_join_presale) && $is_join_presale[ 'code' ] == 0) {
            $info[ 'data' ] = array_merge($info[ 'data' ], $is_join_presale[ 'data' ]);
        }

        return $this->response($info);
    }

    /**
     * 详情信息
     */
    public function detail()
    {
        $sku_id = isset($this->params[ 'sku_id' ]) ? $this->params[ 'sku_id' ] : 0;
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;

        if (empty($sku_id) && empty($goods_id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }

        $goods = new Goods();
        if (empty($sku_id) && !empty($goods_id)) {
            $sku_id = $goods->getGoodsInfo([ [ 'goods_id', '=', $goods_id ] ], 'sku_id')[ 'data' ][ 'sku_id' ] ?? 0;
        }

        $condition = [
            [ 'gs.sku_id', '=', $sku_id ],
            [ 'gs.site_id', '=', $this->site_id ]
        ];

        $field = 'gs.goods_id,gs.sku_id,gs.qr_id,gs.goods_name,gs.sku_name,gs.sku_spec_format,gs.price,gs.market_price,gs.discount_price,gs.promotion_type,gs.start_time
            ,gs.end_time,gs.stock,gs.click_num,(g.sale_num + g.virtual_sale) as sale_num,gs.collect_num,gs.sku_image,gs.sku_images
            ,gs.goods_content,gs.goods_state,gs.is_free_shipping,gs.goods_spec_format,gs.goods_attr_format,gs.introduction,gs.unit,gs.video_url
            ,gs.is_virtual,gs.goods_service_ids,gs.max_buy,gs.min_buy,gs.is_limit,gs.limit_type,gs.support_trade_type,g.goods_image,g.keywords,g.stock_show,g.sale_show,g.market_price_show,g.barrage_show,g.evaluate,g.goods_class,g.sale_store,g.sale_channel,g.label_name,g.category_id';
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'inner' ]
        ];

        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'gs.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ];

            $field .= ',IFNULL(sgs.status, 0) as store_goods_status';

            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sgs.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.discount_price,sgs.price), gs.discount_price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('gs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
            }
        }

        $goods_sku_detail = $goods->getGoodsSkuInfo($condition, $field, 'gs', $join)[ 'data' ];

        if (empty($goods_sku_detail)) return $this->response($this->error());

        // 处理商品支持的配送方式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $support_trade_type = explode(',', $goods_sku_detail[ 'support_trade_type' ]);
            $support_trade_type = array_map(function($item) {
                if ($item == 'express' && $this->store_data[ 'store_info' ][ 'is_express' ]) return $item;
                elseif ($item == 'local' && $this->store_data[ 'store_info' ][ 'is_o2o' ]) return $item;
                elseif ($item == 'store' && $this->store_data[ 'store_info' ][ 'is_pickup' ]) return $item;
            }, $support_trade_type);
            $goods_sku_detail[ 'support_trade_type' ] = implode(',', array_filter($support_trade_type));
        }

        $goods_sku_detail[ 'purchased_num' ] = 0; // 该商品已购数量
        $res[ 'goods_sku_detail' ] = $goods_sku_detail;

        $token = $this->checkToken();
        if ($token[ 'code' ] >= 0) {

            // 是否参与会员等级折扣
            $goods_member_price = $goods->getGoodsPrice($goods_sku_detail[ 'sku_id' ], $this->member_id, $this->store_id)[ 'data' ];
            if (!empty($goods_member_price[ 'member_price' ])) {
                $res[ 'goods_sku_detail' ][ 'member_price' ] = $goods_member_price[ 'member_price' ];
            }

            if ($goods_sku_detail[ 'is_limit' ] && $goods_sku_detail[ 'limit_type' ] == 2 && $goods_sku_detail[ 'max_buy' ] > 0) {
                $res[ 'goods_sku_detail' ][ 'purchased_num' ] = $goods->getGoodsPurchasedNum($goods_sku_detail[ 'goods_id' ], $this->member_id);
            }

            if (addon_is_exit('supermember')) {
                $member_model = new MemberModel();
                $member_info = $member_model->getMemberInfo([ [ 'member_id', '=', $this->member_id, [ 'site_id', '=', $this->site_id ] ] ], 'member_level_type')[ 'data' ];
                if ($member_info[ 'member_level_type' ] == 0) {
                    $member_card_api = new \addon\supermember\api\controller\Membercard();
                    $res[ 'goods_sku_detail' ][ 'membercard' ] = json_decode($member_card_api->recommendCard($goods_sku_detail[ 'sku_id' ]), true)[ 'data' ];
                }

            }
        }

        // 查询当前商品参与的营销活动信息
        $goods_promotion = event('GoodsPromotion', [ 'goods_id' => $goods_sku_detail[ 'goods_id' ], 'sku_id' => $goods_sku_detail[ 'sku_id' ] ]);
        $res[ 'goods_sku_detail' ][ 'goods_promotion' ] = $goods_promotion;

        // 判断是否参与预售
        $is_join_presale = event('IsJoinPresale', [ 'sku_id' => $goods_sku_detail[ 'sku_id' ] ], true);
        if (!empty($is_join_presale) && $is_join_presale[ 'code' ] == 0) {
            $res[ 'goods_sku_detail' ] = array_merge($res[ 'goods_sku_detail' ], $is_join_presale[ 'data' ]);
        }

        // 卡项商品
        if (addon_is_exit('cardservice') && $goods_sku_detail[ 'goods_class' ] == 5) {
            $card_model = new \addon\cardservice\model\CardGoods();
            $res[ 'goods_sku_detail' ][ 'card_info' ] = $card_model->getGoodsCardDetail($goods_sku_detail[ 'goods_id' ], $this->site_id, $this->store_data)[ 'data' ];
        }

        // 分销佣金详情
        if (addon_is_exit('fenxiao')) {
            $fenxiao_api = new \addon\fenxiao\api\controller\Goods();
            $res[ 'goods_sku_detail' ][ 'fenxiao_detail' ] = json_decode($fenxiao_api->detail($goods_sku_detail[ 'sku_id' ]), true)[ 'data' ];
        }

        // 查询商品可用的优惠券
        if (addon_is_exit('coupon')) {
            $coupon_api = new Coupon();
            $res[ 'goods_sku_detail' ][ 'coupon_list' ] = json_decode($coupon_api->goodsCoupon($goods_sku_detail[ 'goods_id' ]), true)[ 'data' ];
        }

        // 组合套餐，连锁门店没有营销活动
        if (addon_is_exit('bundling') && $this->store_data[ 'config' ][ 'store_business' ] == 'shop') {
            $bundling_api = new Bundling();
            $res[ 'goods_sku_detail' ][ 'bundling_list' ] = json_decode($bundling_api->lists($goods_sku_detail[ 'sku_id' ]), true)[ 'data' ];
        }

        // 满减
        if (addon_is_exit('manjian')) {
            $manjian_api = new Manjian();
            $res[ 'goods_sku_detail' ][ 'manjian' ] = json_decode($manjian_api->info($goods_sku_detail[ 'goods_id' ]), true)[ 'data' ];
        }

        // 处理公共数据
        $this->handleGoodsDetailData($res[ 'goods_sku_detail' ]);

        return $this->response($this->success($res));
    }

    /**
     * 查询商品SKU集合
     * @return false|string
     */
    public function goodsSku()
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;
        if (empty($goods_id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }
        $goods = new Goods();

        $condition = [
            [ 'gs.goods_id', '=', $goods_id ],
            [ 'gs.site_id', '=', $this->site_id ]
        ];
        $field = 'gs.sku_id,g.goods_image,gs.sku_name,gs.sku_spec_format,gs.price,gs.discount_price,gs.promotion_type,gs.end_time,gs.stock,gs.sku_image,gs.sku_images,gs.goods_spec_format,gs.is_limit,gs.limit_type,gs.market_price,g.goods_state';
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'inner' ]
        ];

        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'gs.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ];
            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sgs.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.discount_price,sgs.price), gs.discount_price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('gs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
            }
        }
        $list = $goods->getGoodsSkuList($condition, $field, 'gs.sku_id asc', null, 'gs', $join);

        $token = $this->checkToken();
        foreach ($list[ 'data' ] as $k => $v) {

            if ($token[ 'code' ] >= 0) {
                // 是否参与会员等级折扣
                $goods_member_price = $goods->getGoodsPrice($v[ 'sku_id' ], $this->member_id, $this->store_id)[ 'data' ];
                if (!empty($goods_member_price[ 'member_price' ])) {
                    $list[ 'data' ][ $k ][ 'member_price' ] = $goods_member_price[ 'member_price' ];
                }
            }

            // 查询当前商品参与的营销活动信息
            $goods_promotion = event('GoodsPromotion', [ 'goods_id' => $goods_id, 'sku_id' => $v[ 'sku_id' ] ]);
            $list[ 'data' ][ $k ][ 'goods_promotion' ] = $goods_promotion;

            //判断是否参与预售
            $is_join_presale = event('IsJoinPresale', [ 'sku_id' => $v[ 'sku_id' ] ], true);
            if (!empty($is_join_presale) && $is_join_presale[ 'code' ] == 0) {
                $list[ 'data' ][ $k ] = array_merge($list[ 'data' ][ $k ], $is_join_presale[ 'data' ]);
            }

            // 分销佣金详情
            if (addon_is_exit('fenxiao')) {
                $fenxiao_api = new \addon\fenxiao\api\controller\Goods();
                $list[ 'data' ][ $k ][ 'fenxiao_detail' ] = json_decode($fenxiao_api->detail($v[ 'sku_id' ]), true)[ 'data' ];
            }
        }

        return $this->response($list);

    }


    /**
     * 商品详情，商品分类用
     * @return false|string
     */
    public function getInfoForCategory()
    {

        $sku_id = isset($this->params[ 'sku_id' ]) ? $this->params[ 'sku_id' ] : 0;
        if (empty($sku_id)) {
            return $this->response($this->error('', 'REQUEST_SKU_ID'));
        }

        $goods = new Goods();
        $condition = [
            [ 'gs.sku_id', '=', $sku_id ],
            [ 'gs.site_id', '=', $this->site_id ]
        ];
        $field = 'gs.goods_id,gs.sku_id,gs.goods_name,gs.is_limit,gs.limit_type,gs.sku_name,gs.sku_spec_format,gs.price,gs.discount_price,gs.stock,gs.sku_image,gs.goods_spec_format,gs.unit,gs.max_buy,gs.min_buy,gs.goods_state';
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'inner' ]
        ];
        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'gs.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ];
            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sgs.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.discount_price,sgs.price), gs.discount_price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('gs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
            }
        }
        $goods_sku_detail = $goods->getGoodsSkuInfo($condition, $field, 'gs', $join);

        $token = $this->checkToken();
        if ($token[ 'code' ] >= 0) {
            // 是否参与会员等级折扣
            $goods_member_price = $goods->getGoodsPrice($sku_id, $this->member_id, $this->store_id)[ 'data' ];
            if (!empty($goods_member_price[ 'member_price' ])) {
                $goods_sku_detail[ 'data' ][ 'member_price' ] = $goods_member_price[ 'member_price' ];
            }
            if ($goods_sku_detail[ 'data' ][ 'max_buy' ] > 0) $goods_sku_detail[ 'data' ][ 'purchased_num' ] = $goods->getGoodsPurchasedNum($goods_sku_detail[ 'data' ][ 'goods_id' ], $this->member_id);
        }
        return $this->response($goods_sku_detail);
    }

    /**
     * 查询商品SKU集合，商品分类用
     * @return false|string
     */
    public function goodsSkuByCategory()
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;
        if (empty($goods_id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }

        $goods = new Goods();

        $condition = [
            [ 'gs.goods_id', '=', $goods_id ],
            [ 'gs.site_id', '=', $this->site_id ]
        ];
        $field = 'gs.sku_id,gs.sku_name,gs.sku_spec_format,gs.price,gs.discount_price,gs.promotion_type,gs.end_time,gs.stock,gs.sku_image,gs.goods_spec_format';
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'inner' ]
        ];

        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'gs.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ];
            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sgs.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.discount_price,sgs.price), gs.discount_price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('gs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
            }
        }
        $list = $goods->getGoodsSkuList($condition, $field, 'gs.sku_id asc', null, 'gs', $join);

        $token = $this->checkToken();
        foreach ($list[ 'data' ] as $k => $v) {

            if ($token[ 'code' ] >= 0) {
                // 是否参与会员等级折扣
                $goods_member_price = $goods->getGoodsPrice($v[ 'sku_id' ], $this->member_id, $this->store_id)[ 'data' ];
                if (!empty($goods_member_price[ 'member_price' ])) {
                    $list[ 'data' ][ $k ][ 'member_price' ] = $goods_member_price[ 'member_price' ];
                }
            }
        }

        return $this->response($list);

    }

    /**
     * 列表信息
     */
    public function page()
    {
        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $goods_id_arr = isset($this->params[ 'goods_id_arr' ]) ? $this->params[ 'goods_id_arr' ] : '';//goods_id数组
        $keyword = isset($this->params[ 'keyword' ]) ? trim($this->params[ 'keyword' ]) : '';//关键词
        $category_id = isset($this->params[ 'category_id' ]) ? $this->params[ 'category_id' ] : 0;//分类
        $brand_id = $this->params[ 'brand_id' ] ?? 0; //品牌
        $min_price = isset($this->params[ 'min_price' ]) ? $this->params[ 'min_price' ] : 0;//价格区间，小
        $max_price = isset($this->params[ 'max_price' ]) ? $this->params[ 'max_price' ] : 0;//价格区间，大
        $is_free_shipping = isset($this->params[ 'is_free_shipping' ]) ? $this->params[ 'is_free_shipping' ] : 0;//是否免邮
        $order = isset($this->params[ 'order' ]) ? $this->params[ 'order' ] : "";//排序（综合、销量、价格）
        $sort = isset($this->params[ 'sort' ]) ? $this->params[ 'sort' ] : "";//升序、降序
        $coupon = isset($this->params[ 'coupon' ]) ? $this->params[ 'coupon' ] : 0;//优惠券
        $condition = [];
        $condition[] = [ 'gs.site_id', '=', $this->site_id ];
        $condition[] = [ '', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'online')") ];

        if (!empty($goods_id_arr)) {
            $condition[] = [ 'gs.goods_id', 'in', $goods_id_arr ];
        }

        if (!empty($keyword)) {
            $condition[] = [ 'g.goods_name|gs.sku_name|gs.keywords|g.introduction', 'like', '%' . $keyword . '%' ];
        }

        if (!empty($brand_id)) {
            $condition[] = [ 'g.brand_id', '=', $brand_id ];
        }

        if (!empty($category_id)) {
            $goods_category_model = new GoodsCategoryModel();

            // 查询当前
            $category_list = $goods_category_model->getCategoryList([ [ 'category_id', '=', $category_id ], [ 'site_id', '=', $this->site_id ] ], 'category_id,pid,level')[ 'data' ];

            // 查询子级
            $category_child_list = $goods_category_model->getCategoryList([ [ 'pid', '=', $category_id ], [ 'site_id', '=', $this->site_id ] ], 'category_id,pid,level')[ 'data' ];

            $temp_category_list = [];
            if (!empty($category_list)) {
                $temp_category_list = $category_list;
            } elseif (!empty($category_child_list)) {
                $temp_category_list = $category_child_list;
            }

            if (!empty($temp_category_list)) {
                $category_id_arr = [];
                foreach ($temp_category_list as $k => $v) {
                    // 三级分类，并且都能查询到
                    if ($v[ 'level' ] == 3 && !empty($category_list) && !empty($category_child_list)) {
                        array_push($category_id_arr, $v[ 'pid' ]);
                    } else {
                        array_push($category_id_arr, $v[ 'category_id' ]);
                    }
                }
                $category_id_arr = array_unique($category_id_arr);
                $temp_condition = [];
                foreach ($category_id_arr as $ck => $cv) {
                    $temp_condition[] = '%,' . $cv . ',%';
                }
                $category_condition = $temp_condition;
                $condition[] = [ 'g.category_id', 'like', $category_condition, 'or' ];
            }
        }

        if ($min_price != "" && $max_price != "") {
            $condition[] = [ 'gs.discount_price', 'between', [ $min_price, $max_price ] ];
        } elseif ($min_price != "") {
            $condition[] = [ 'gs.discount_price', '>=', $min_price ];
        } elseif ($max_price != "") {
            $condition[] = [ 'gs.discount_price', '<=', $max_price ];
        }

        if (!empty($is_free_shipping)) {
            $condition[] = [ 'gs.is_free_shipping', '=', $is_free_shipping ];
        }

        // 非法参数进行过滤
        if ($sort != "desc" && $sort != "asc") {
            $sort = "";
        }

        // 非法参数进行过滤
        if ($order != '') {
            if ($order != "sale_num" && $order != "discount_price" && $order != "create_time") {
                $order = 'gs.sort';
            } elseif ($order == "sale_num") {
                $order = 'sale_sort';
            } elseif ($order == "create_time") {
                $order = 'g.create_time';
            } else {
                $order = 'gs.' . $order;
            }
            $order_by = $order . ' ' . $sort;
        } else {

            $config_model = new ConfigModel();
            $sort_config = $config_model->getGoodsSort($this->site_id);
            $sort_config = $sort_config[ 'data' ][ 'value' ];

            $order_by = 'g.sort ' . $sort_config[ 'type' ] . ',g.create_time desc';
        }

        // 优惠券
        if (!empty($coupon)) {
            $coupon_type = new CouponType();
            $coupon_type_info = $coupon_type->getInfo([
                [ 'coupon_type_id', '=', $coupon ],
                [ 'site_id', '=', $this->site_id ],
                [ 'goods_type', '=', 2 ]
            ], 'goods_ids')[ 'data' ];
            if (isset($coupon_type_info[ 'goods_ids' ]) && !empty($coupon_type_info[ 'goods_ids' ])) {
                $condition[] = [ 'g.goods_id', 'in', explode(',', trim($coupon_type_info[ 'goods_ids' ], ',')) ];
            }
        }

        $condition[] = [ 'g.goods_state', '=', 1 ];
        $condition[] = [ 'g.is_delete', '=', 0 ];
        $alias = 'gs';

        $field = 'gs.is_consume_discount,gs.discount_config,gs.discount_method,gs.member_price,gs.goods_id,gs.sort,gs.sku_id,gs.sku_name,gs.price,gs.market_price,gs.discount_price,gs.stock,(g.sale_num + g.virtual_sale) as sale_num,(gs.sale_num + gs.virtual_sale) as sale_sort,gs.sku_image,gs.goods_name,gs.site_id,gs.is_free_shipping,gs.introduction,gs.promotion_type,g.goods_image,g.promotion_addon,gs.is_virtual,g.goods_spec_format,g.recommend_way,gs.max_buy,gs.min_buy,gs.unit,gs.is_limit,gs.limit_type,g.label_name,g.stock_show,g.sale_show,g.market_price_show,g.barrage_show,g.sale_channel,g.sale_store';
        $join = [
            [ 'goods g', 'gs.sku_id = g.sku_id', 'inner' ]
        ];

        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'g.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ];

            $condition[] = [ 'g.sale_store', 'like', [ '%all%', '%,' . $this->store_id . ',%' ], 'or' ];
            $condition[] = [ 'sgs.status', '=', 1 ];

            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sgs.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sgs.price), gs.price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('gs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
            }
        }

        $goods = new Goods();
        $list = $goods->getGoodsSkuPageList($condition, $page, $page_size, $order_by, $field, $alias, $join);

        if (!empty($list[ 'data' ][ 'list' ])) {
            // 商品列表配置
            $config_model = new ConfigModel();
            $goods_list_config = $config_model->getGoodsListConfig($this->site_id, $this->app_module)[ 'data' ][ 'value' ];
            $list[ 'data' ][ 'config' ] = $goods_list_config;
        }

        $token = $this->checkToken();
        if ($token[ 'code' ] >= 0) {
            if (!empty($list[ 'data' ][ 'list' ])) {
                $list[ 'data' ][ 'list' ] = $goods->getGoodsListMemberPrice($list[ 'data' ][ 'list' ], $this->member_id);
            }
        }

        return $this->response($list);
    }

    /**
     * 查询商品列表供组件调用
     */
    public function pageComponents()
    {
        $token = $this->checkToken();

        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $goods_id_arr = isset($this->params[ 'goods_id_arr' ]) ? $this->params[ 'goods_id_arr' ] : '';//goods_id数组
        $category_id = isset($this->params[ 'category_id' ]) ? $this->params[ 'category_id' ] : 0;//分类

        $order = isset($this->params[ 'order' ]) ? $this->params[ 'order' ] : "";//排序（综合、销量、价格）

        $condition = [
            [ 'g.goods_state', '=', 1 ],
            [ 'g.is_delete', '=', 0 ],
            [ 'g.site_id', '=', $this->site_id ],
            [ '', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'online')") ]
        ];
        if (!empty($category_id)) {
            $condition[] = [ 'category_id', 'like', '%,' . $category_id . ',%' ];
        }
        if (!empty($goods_id_arr)) {
            $condition[] = [ 'g.goods_id', 'in', $goods_id_arr ];
        }

        // 非法参数进行过滤
        if ($order != '') {
            if ($order == 'default') {
                // 综合排序
                $order = 'g.sort asc,g.create_time';
                $sort = 'desc';
            } elseif ($order == 'sales') {
                // 销量排序
                $order = 'sale_num';
                $sort = 'desc';
            } else if ($order == 'price') {
                // 价格排序
                $order = 'gs.discount_price';
                $sort = 'asc';
            } else if ($order == 'news') {
                // 上架时间排序
                $order = 'g.create_time';
                $sort = 'desc';
            } else {
                $order = 'g.' . $order;
                $sort = 'asc';
            }

            $order_by = $order . ' ' . $sort;
        } else {
            $config_model = new ConfigModel();
            $sort_config = $config_model->getGoodsSort($this->site_id);
            $sort_config = $sort_config[ 'data' ][ 'value' ];
            $order_by = 'g.sort ' . $sort_config[ 'type' ] . ',g.create_time desc';
        }

        $field = 'gs.goods_id,gs.sku_id,gs.price,gs.market_price,gs.discount_price,g.goods_stock,(g.sale_num + g.virtual_sale) as sale_num,g.goods_name,gs.site_id,gs.is_free_shipping,g.goods_image,gs.is_virtual,g.recommend_way,gs.unit,gs.promotion_type,g.label_name,g.goods_spec_format';
        if ($token[ 'code' ] >= 0) {
            $field .= ',gs.is_consume_discount,gs.discount_config,gs.member_price,gs.discount_method';
        }
        $alias = 'gs';
        $join = [
            [ 'goods g', 'gs.sku_id = g.sku_id', 'inner' ]
        ];

        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods sg', 'g.goods_id=sg.goods_id and sg.store_id=' . $this->store_id, 'left' ];

            $condition[] = [ 'g.sale_store', 'like', [ '%all%', '%,' . $this->store_id . ',%' ], 'or' ];
            $condition[] = [ 'sg.status', '=', 1 ];

            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sg.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sg.price), gs.price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('g.goods_stock', 'IFNULL(sg.stock, 0) as goods_stock', $field);
            }
        }

        $goods = new Goods();
        $list = $goods->getGoodsSkuPageList($condition, $page, $page_size, $order_by, $field, $alias, $join);

        if (!empty($list[ 'data' ][ 'list' ]) && $token[ 'code' ] >= 0) {
            $list[ 'data' ][ 'list' ] = $goods->getGoodsListMemberPrice($list[ 'data' ][ 'list' ], $this->member_id);
        }

        return $this->response($list);
    }

    /**
     * 查询商品列表供组件调用
     */
    public function components()
    {
        $token = $this->checkToken();

        $num = isset($this->params[ 'num' ]) ? $this->params[ 'num' ] : 0;
        $goods_id_arr = isset($this->params[ 'goods_id_arr' ]) ? $this->params[ 'goods_id_arr' ] : '';//goods_id数组
        $category_id = isset($this->params[ 'category_id' ]) ? $this->params[ 'category_id' ] : 0;//分类
        $order = isset($this->params[ 'order' ]) ? $this->params[ 'order' ] : "";//排序（综合、销量、价格）

        $condition = [
            [ 'g.goods_state', '=', 1 ],
            [ 'g.is_delete', '=', 0 ],
            [ 'g.site_id', '=', $this->site_id ],
            [ '', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'online')") ]
        ];
        if (!empty($category_id)) {
            $condition[] = [ 'category_id', 'like', '%,' . $category_id . ',%' ];
        }
        if (!empty($goods_id_arr)) {
            $condition[] = [ 'g.goods_id', 'in', $goods_id_arr ];
        }

        // 非法参数进行过滤
        if ($order != '') {
            if ($order == 'default') {
                // 综合排序
                $order = 'g.sort asc,g.create_time';
                $sort = 'desc';
            } elseif ($order == 'sales') {
                // 销量排序
                $order = 'sale_num';
                $sort = 'desc';
            } else if ($order == 'price') {
                // 价格排序
                $order = 'gs.discount_price';
                $sort = 'desc';
            } else if ($order == 'news') {
                // 上架时间排序
                $order = 'g.create_time';
                $sort = 'desc';
            } else {
                $order = 'g.' . $order;
                $sort = 'asc';
            }

            $order_by = $order . ' ' . $sort;
        } else {
            $config_model = new ConfigModel();
            $sort_config = $config_model->getGoodsSort($this->site_id);
            $sort_config = $sort_config[ 'data' ][ 'value' ];
            $order_by = 'g.sort ' . $sort_config[ 'type' ] . ',g.create_time desc';
        }

        $field = 'gs.goods_id,gs.sku_id,gs.price,gs.market_price,gs.discount_price,gs.stock,(g.sale_num + g.virtual_sale) as sale_num,g.goods_name,gs.site_id,gs.is_free_shipping,g.goods_image,gs.is_virtual,g.recommend_way,gs.unit,gs.promotion_type,g.label_name,g.goods_spec_format';
        if ($token[ 'code' ] >= 0) {
            $field .= ',gs.is_consume_discount,gs.discount_config,gs.member_price,gs.discount_method';
        }
        $alias = 'gs';
        $join = [
            [ 'goods g', 'gs.sku_id = g.sku_id', 'inner' ]
        ];

        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods sg', 'g.goods_id=sg.goods_id and sg.store_id=' . $this->store_id, 'left' ];

            $condition[] = [ 'g.sale_store', 'like', [ '%all%', '%,' . $this->store_id . ',%' ], 'or' ];
            $condition[] = [ 'sg.status', '=', 1 ];

            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sg.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sg.price), gs.price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('gs.stock', 'IFNULL(sg.stock, 0) as stock', $field);
            }
        }

        $goods = new Goods();
        $list = $goods->getGoodsSkuList($condition, $field, $order_by, $num, $alias, $join);

        if (!empty($list[ 'data' ]) && $token[ 'code' ] >= 0) {
            $list[ 'data' ] = $goods->getGoodsListMemberPrice($list[ 'data' ], $this->member_id);
        }

        return $this->response($list);
    }

    /**
     * 商品推荐
     * @return string
     */
    public function recommend()
    {
        $token = $this->checkToken();
        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $route = isset($this->params[ 'route' ]) ? $this->params[ 'route' ] : '';

        // PC端
        if ($this->params[ 'app_type' ] == 'pc') {
            $route = 'goods_detail';
        }

        if (empty($route)) {
            return $this->response($this->error('',));
        }
        $condition[] = [ 'gs.goods_state', '=', 1 ];
        $condition[] = [ 'gs.is_delete', '=', 0 ];
        $condition[] = [ 'gs.site_id', '=', $this->site_id ];
        $condition[] = [ '', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'online')") ];
        $goods = new Goods();

        $alias = 'gs';

        $config_model = new ConfigModel();
        $order_by = '';

        // 根据后台设置推荐商品
        $guess_you_like = $config_model->getGuessYouLike($this->site_id, $this->app_module)[ 'data' ][ 'value' ];
        if (strpos($guess_you_like[ 'support_page' ], $route) === false) {
            return $this->response($this->error('',));
        }

        if ($guess_you_like[ 'sources' ] == 'sort') {
            $sort_config = $config_model->getGoodsSort($this->site_id)[ 'data' ][ 'value' ];
            $order_by = 'gs.sort ' . $sort_config[ 'type' ] . ',gs.create_time desc';
        } else if ($guess_you_like[ 'sources' ] == 'browse') {
            $condition[] = [ 'gb.member_id', '=', $this->member_id ];
            $condition[] = [ 'gb.site_id', '=', $this->site_id ];
            $order_by = 'browse_time desc';
        } else if ($guess_you_like[ 'sources' ] == 'sale') {
            $order_by = 'sale_num desc';
        } else if ($guess_you_like[ 'sources' ] == 'diy') {
            $condition[] = [ 'gs.goods_id', 'in', $guess_you_like[ 'goods_ids' ] ];
        }

        $field = 'gs.is_consume_discount,gs.discount_config,gs.discount_method,gs.member_price,g.market_price_show,g.sale_show,gs.goods_id,gs.sku_id,gs.sku_name,gs.price,gs.market_price,gs.discount_price,gs.stock,(g.sale_num + g.virtual_sale) as sale_num,gs.goods_name,gs.promotion_type,g.goods_image,gs.unit,g.label_name,gs.sku_image';

        if ($guess_you_like[ 'sources' ] == 'browse') {
            $join = [
                [ 'goods g', 'gs.sku_id = g.sku_id', 'inner' ],
                [ 'goods_browse gb', 'gb.sku_id = gs.sku_id', 'inner' ]
            ];
        } else {
            $join = [
                [ 'goods g', 'gs.sku_id = g.sku_id', 'inner' ]
            ];
        }

        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'g.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ];

            $condition[] = [ 'g.sale_store', 'like', [ '%all%', '%,' . $this->store_id . ',%' ], 'or' ];
            $condition[] = [ 'sgs.status', '=', 1 ];

            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sgs.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sgs.price), gs.price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('gs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
            }
        }

        $list = $goods->getGoodsSkuPageList($condition, $page, $page_size, $order_by, $field, $alias, $join);
        if (!empty($list[ 'data' ][ 'list' ])) {
            $list[ 'data' ][ 'list' ] = $goods->getGoodsListMemberPrice($list[ 'data' ][ 'list' ], $this->member_id);
            $list[ 'data' ][ 'config' ] = $guess_you_like;
        }

        return $this->response($list);
    }

    /**
     * 商品二维码
     * return
     */
    public function goodsQrcode()
    {
        $sku_id = isset($this->params[ 'sku_id' ]) ? $this->params[ 'sku_id' ] : 0;
        if (empty($sku_id)) {
            return $this->response($this->error('', 'REQUEST_SKU_ID'));
        }
        $goods_model = new Goods();
        $goods_sku_info = $goods_model->getGoodsSkuInfo([ [ 'sku_id', '=', $sku_id ] ], 'goods_id,sku_id,goods_name')[ 'data' ];
        $res = $goods_model->qrcode($goods_sku_info[ 'goods_id' ], $goods_sku_info[ 'goods_name' ], $this->site_id);
        return $this->response($res);
    }

    /**
     * 处理商品详情公共数据
     * @param $data
     */
    public function handleGoodsDetailData(&$data)
    {
        $goods = new Goods();

        if (!empty($data[ 'sku_images' ])) $data[ 'sku_images_list' ] = $goods->getGoodsImage($data[ 'sku_images' ], $this->site_id)[ 'data' ] ?? [];
        if (!empty($data[ 'sku_image' ])) $data[ 'sku_image_list' ] = $goods->getGoodsImage($data[ 'sku_image' ], $this->site_id)[ 'data' ] ?? [];
        if (!empty($data[ 'goods_image' ])) $data[ 'goods_image_list' ] = $goods->getGoodsImage($data[ 'goods_image' ], $this->site_id)[ 'data' ] ?? [];

        // 商品服务
        $goods_service = new GoodsService();
        $data[ 'goods_service' ] = $goods_service->getServiceList([ [ 'site_id', '=', $this->site_id ], [ 'id', 'in', $data[ 'goods_service_ids' ] ] ], 'service_name,desc,icon')[ 'data' ];

        // 商品详情配置
        $config_model = new ConfigModel();
        $data[ 'config' ] = $config_model->getGoodsDetailConfig($this->site_id)[ 'data' ][ 'value' ];

        if ($data[ 'is_virtual' ] == 0) {
            $data[ 'express_type' ] = ( new ExpressConfig() )->getEnabledExpressType($this->site_id);
        }

        // 获取用户是否关注
        $goods_collect_api = new Goodscollect();
        $data[ 'is_collect' ] = json_decode($goods_collect_api->iscollect($data[ 'goods_id' ]), true)[ 'data' ];

        // 评价查询
        $goods_evaluate_api = new Goodsevaluate();
        $data[ 'evaluate_config' ] = json_decode($goods_evaluate_api->config(), true)[ 'data' ];

        $data[ 'evaluate_list' ] = json_decode($goods_evaluate_api->firstinfo($data[ 'goods_id' ]), true)[ 'data' ];

        $data[ 'evaluate_count' ] = json_decode($goods_evaluate_api->count($data[ 'goods_id' ]), true)[ 'data' ];

    }
}