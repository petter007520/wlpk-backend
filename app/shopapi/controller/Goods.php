<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace app\shopapi\controller;

use app\model\express\ExpressTemplate as ExpressTemplateModel;
use app\model\goods\Goods as GoodsModel;
use app\model\goods\GoodsAttribute as GoodsAttributeModel;
use app\model\goods\GoodsBrowse;
use app\model\goods\GoodsCategory as GoodsCategoryModel;
use app\model\goods\GoodsCollect;
use app\model\goods\GoodsEvaluate as GoodsEvaluateModel;
use app\model\web\Config as ConfigModel;

/**
 * 实物商品
 * Class Goods
 * @package app\shop\controller
 */
class Goods extends BaseApi
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
     *  商品条件
     * @return false|string
     */
    public function condition()
    {
        $data = [];
        // 营销活动
        $goods_promotion_type = event('GoodsPromotionType');
        $data[ 'goods_promotion_type' ] = $goods_promotion_type;

        return $this->response($this->success($data));
    }


    /**
     * 商品列表
     * @return mixed
     */
    public function lists()
    {
        $goods_model = new GoodsModel();

        $page_index = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $search_text = isset($this->params[ 'search_text' ]) ? $this->params[ 'search_text' ] : '';
        $goods_state = isset($this->params[ 'goods_state' ]) ? $this->params[ 'goods_state' ] : '';
        $start_sale = isset($this->params[ 'start_sale' ]) ? $this->params[ 'start_sale' ] : 0;
        $end_sale = isset($this->params[ 'end_sale' ]) ? $this->params[ 'end_sale' ] : 0;
        $start_price = isset($this->params[ 'start_price' ]) ? $this->params[ 'start_price' ] : 0;
        $end_price = isset($this->params[ 'end_price' ]) ? $this->params[ 'end_price' ] : 0;
        $goods_shop_category_ids = isset($this->params[ 'goods_shop_category_ids' ]) ? $this->params[ 'goods_shop_category_ids' ] : '';
        $goods_class = isset($this->params[ 'goods_class' ]) ? $this->params[ 'goods_class' ] : '';
        $order = isset($this->params[ 'order' ]) ? $this->params[ 'order' ] : 'create_time';
        $sort = isset($this->params[ 'sort' ]) ? $this->params[ 'sort' ] : 'desc';
        $promotion_type = isset($this->params[ 'promotion_type' ]) ? $this->params[ 'promotion_type' ] : '';
        $stockalarm = $this->params[ 'stockalarm' ] ?? 0;
        $order_by = $order . ' ' . $sort;

        $condition = [ [ 'is_delete', '=', 0 ], [ 'site_id', '=', $this->site_id ] ];

        if (!empty($search_text)) {
            $condition[] = [ 'goods_name', 'like', '%' . $search_text . '%' ];
        }

        if ($goods_class !== "") {
            $condition[] = [ 'goods_class', '=', $goods_class ];
        }

        // 上架状态
        if ($goods_state !== '') {
            $condition[] = [ 'goods_state', '=', $goods_state ];
        }
        //参与活动
        if (!empty($promotion_type)) {
            $condition[] = [ 'promotion_addon', 'like', "%{$promotion_type}%" ];
        }

        // 查询库存预警的商品
        if ($stockalarm) {
            $stock_alarm = $goods_model->getGoodsStockAlarm($this->site_id);
            if (!empty($stock_alarm[ 'data' ])) $condition[] = [ 'goods_id', 'in', $stock_alarm[ 'data' ] ];
            else return $this->response($this->success([ 'page_count' => 1, 'count' => 0, 'list' => [] ]));
        }

        if (!empty($start_sale)) $condition[] = [ 'sale_num', '>=', $start_sale ];
        if (!empty($end_sale)) $condition[] = [ 'sale_num', '<=', $end_sale ];
        if (!empty($start_price)) $condition[] = [ 'price', '>=', $start_price ];
        if (!empty($end_price)) $condition[] = [ 'price', '<=', $end_price ];
        if (!empty($goods_shop_category_ids)) $condition[] = [ 'goods_shop_category_ids', 'like', [ $goods_shop_category_ids, '%' . $goods_shop_category_ids . ',%', '%' . $goods_shop_category_ids, '%,' . $goods_shop_category_ids . ',%' ], 'or' ];

        $res = $goods_model->getGoodsPageList($condition, $page_index, $page_size, $order_by);

        if (!empty($res[ 'data' ][ 'list' ])) {
            $goods_promotion_type = event('GoodsPromotionType');
            foreach ($res[ 'data' ][ 'list' ] as $k => $v) {
                if (!empty($v[ 'promotion_addon' ])) {
                    $v[ 'promotion_addon' ] = json_decode($v[ 'promotion_addon' ], true);
                    foreach ($v[ 'promotion_addon' ] as $ck => $cv) {
                        foreach ($goods_promotion_type as $gk => $gv) {
                            if ($gv[ 'type' ] == $ck) {
                                $res[ 'data' ][ 'list' ][ $k ][ 'promotion_addon_list' ][] = $gv;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $this->response($res);

    }

    /**
     * 添加商品
     * @return mixed
     */
    public function addGoods()
    {
        $data = [
            'goods_name' => isset($this->params[ 'goods_name' ]) ? $this->params[ 'goods_name' ] : '',// 商品名称,
            'goods_attr_class' => isset($this->params[ 'goods_attr_class' ]) ? $this->params[ 'goods_attr_class' ] : '',// 商品类型id,
            'goods_attr_name' => isset($this->params[ 'goods_attr_name' ]) ? $this->params[ 'goods_attr_name' ] : '',// 商品类型名称,
            'site_id' => $this->site_id,
            'category_id' => $this->params[ 'category_id' ] ?? '',
            'category_json' => $this->params[ 'category_json' ] ?? '',
            'goods_image' => isset($this->params[ 'goods_image' ]) ? $this->params[ 'goods_image' ] : '',// 商品主图路径
            'goods_content' => isset($this->params[ 'goods_content' ]) ? $this->params[ 'goods_content' ] : '',// 商品详情
            'goods_state' => isset($this->params[ 'goods_state' ]) ? $this->params[ 'goods_state' ] : '',// 商品状态（1.正常0下架）
            'price' => isset($this->params[ 'price' ]) ? $this->params[ 'price' ] : 0,// 商品价格（取第一个sku）
            'market_price' => isset($this->params[ 'market_price' ]) ? $this->params[ 'market_price' ] : '',// 市场价格（取第一个sku）
            'cost_price' => isset($this->params[ 'cost_price' ]) ? $this->params[ 'cost_price' ] : 0,// 成本价（取第一个sku）
            'sku_no' => isset($this->params[ 'sku_no' ]) ? $this->params[ 'sku_no' ] : '',// 商品sku编码
            'weight' => isset($this->params[ 'weight' ]) ? $this->params[ 'weight' ] : '',// 重量
            'volume' => isset($this->params[ 'volume' ]) ? $this->params[ 'volume' ] : '',// 体积
            'goods_stock' => isset($this->params[ 'goods_stock' ]) ? $this->params[ 'goods_stock' ] : 0,// 商品库存（总和）

            'goods_stock_alarm' => isset($this->params[ 'goods_stock_alarm' ]) ? $this->params[ 'goods_stock_alarm' ] : 0,// 库存预警
            'is_free_shipping' => isset($this->params[ 'is_free_shipping' ]) ? $this->params[ 'is_free_shipping' ] : 1,// 是否免邮
            'shipping_template' => isset($this->params[ 'shipping_template' ]) ? $this->params[ 'shipping_template' ] : 0,// 指定运费模板
            'goods_spec_format' => isset($this->params[ 'goods_spec_format' ]) ? $this->params[ 'goods_spec_format' ] : '',// 商品规格格式
            'goods_attr_format' => isset($this->params[ 'goods_attr_format' ]) ? $this->params[ 'goods_attr_format' ] : '',// 商品参数格式
            'introduction' => isset($this->params[ 'introduction' ]) ? $this->params[ 'introduction' ] : '',// 促销语
            'keywords' => isset($this->params[ 'keywords' ]) ? $this->params[ 'keywords' ] : '',// 关键词
            'unit' => isset($this->params[ 'unit' ]) ? $this->params[ 'unit' ] : '',// 单位
            'sort' => isset($this->params[ 'sort' ]) ? $this->params[ 'sort' ] : 0,// 排序,
            'video_url' => isset($this->params[ 'video_url' ]) ? $this->params[ 'video_url' ] : '',// 视频
            'goods_sku_data' => isset($this->params[ 'goods_sku_data' ]) ? $this->params[ 'goods_sku_data' ] : '',// SKU商品数据
            'label_id' => isset($this->params[ 'label_id' ]) ? $this->params[ 'label_id' ] : '',// 商品分组id
            'max_buy' => isset($this->params[ 'max_buy' ]) ? $this->params[ 'max_buy' ] : '',// 限购
            'min_buy' => isset($this->params[ 'min_buy' ]) ? $this->params[ 'min_buy' ] : '',// 起售

            'timer_on' => isset($this->params[ 'timer_on' ]) ? strtotime($this->params[ 'timer_on' ]) : 0,//定时上架
            'timer_off' => isset($this->params[ 'timer_off' ]) ? strtotime($this->params[ 'timer_off' ]) : 0,//定时下架

            'site_name' => $this->shop_info[ 'site_name' ],//店铺名
            'virtual_sale' => $this->params[ 'virtual_sale' ] ?? 0,// 虚拟销量
            'is_consume_discount' => $this->params[ 'is_consume_discount' ] ?? 0, //是否参与会员折扣
            'goods_service_ids' => $this->params[ 'goods_service_ids' ] ?? '',// 商品服务id集合
            'recommend_way' => $this->params[ 'recommend_way' ] ?? 0, // 推荐方式，1：新品，2：精品，3；推荐

            'is_limit' => $this->params[ 'is_limit' ] ?? 0,// 商品是否限购,
            'limit_type' => $this->params[ 'limit_type' ] ?? 1, // 商品限购类型,

            'sale_show' => $this->params[ 'sale_show' ] ?? 0,
            'stock_show' => $this->params[ 'stock_show' ] ?? 0,
            'market_price_show' => $this->params[ 'market_price_show' ] ?? 0,
            'barrage_show' => $this->params[ 'barrage_show' ] ?? 0,
            'brand_id' => $this->params[ 'brand_id' ] ?? 0,
            'support_trade_type' => $this->params[ 'support_trade_type' ] ?? '',
            'form_id' => $this->params['goods_form'] ?? 0
        ];

        $goods_model = new GoodsModel();
        $res = $goods_model->addGoods($data);
        return $this->response($res);
    }

    /**
     * 编辑商品
     * @return mixed
     */
    public function editGoods()
    {
        $goods_model = new GoodsModel();
        $data = [
            'goods_id' => isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0,// 商品id
            'goods_name' => isset($this->params[ 'goods_name' ]) ? $this->params[ 'goods_name' ] : '',// 商品名称,
            'goods_attr_class' => isset($this->params[ 'goods_attr_class' ]) ? $this->params[ 'goods_attr_class' ] : '',// 商品类型id,
            'goods_attr_name' => isset($this->params[ 'goods_attr_name' ]) ? $this->params[ 'goods_attr_name' ] : '',// 商品类型名称,
            'site_id' => $this->site_id,
            'category_id' => $this->params[ 'category_id' ] ?? '',
            'category_json' => $this->params[ 'category_json' ] ?? '',
            'goods_image' => isset($this->params[ 'goods_image' ]) ? $this->params[ 'goods_image' ] : '',// 商品主图路径
            'goods_content' => isset($this->params[ 'goods_content' ]) ? $this->params[ 'goods_content' ] : '',// 商品详情
            'goods_state' => isset($this->params[ 'goods_state' ]) ? $this->params[ 'goods_state' ] : '',// 商品状态（1.正常0下架）
            'price' => isset($this->params[ 'price' ]) ? $this->params[ 'price' ] : 0,// 商品价格（取第一个sku）
            'market_price' => isset($this->params[ 'market_price' ]) ? $this->params[ 'market_price' ] : 0,// 市场价格（取第一个sku）
            'cost_price' => isset($this->params[ 'cost_price' ]) ? $this->params[ 'cost_price' ] : 0,// 成本价（取第一个sku）
            'sku_no' => isset($this->params[ 'sku_no' ]) ? $this->params[ 'sku_no' ] : '',// 商品sku编码
            'weight' => isset($this->params[ 'weight' ]) ? $this->params[ 'weight' ] : '',// 重量
            'volume' => isset($this->params[ 'volume' ]) ? $this->params[ 'volume' ] : '',// 体积
            'goods_stock' => isset($this->params[ 'goods_stock' ]) ? $this->params[ 'goods_stock' ] : 0,// 商品库存（总和）
            'goods_stock_alarm' => isset($this->params[ 'goods_stock_alarm' ]) ? $this->params[ 'goods_stock_alarm' ] : 0,// 库存预警
            'is_free_shipping' => isset($this->params[ 'is_free_shipping' ]) ? $this->params[ 'is_free_shipping' ] : 1,// 是否免邮
            'shipping_template' => isset($this->params[ 'shipping_template' ]) ? $this->params[ 'shipping_template' ] : 0,// 指定运费模板
            'goods_spec_format' => isset($this->params[ 'goods_spec_format' ]) ? $this->params[ 'goods_spec_format' ] : '',// 商品规格格式
            'goods_attr_format' => isset($this->params[ 'goods_attr_format' ]) ? $this->params[ 'goods_attr_format' ] : '',// 商品参数格式
            'introduction' => isset($this->params[ 'introduction' ]) ? $this->params[ 'introduction' ] : '',// 促销语
            'keywords' => isset($this->params[ 'keywords' ]) ? $this->params[ 'keywords' ] : '',// 关键词
            'unit' => isset($this->params[ 'unit' ]) ? $this->params[ 'unit' ] : '',// 单位
            'sort' => isset($this->params[ 'sort' ]) ? $this->params[ 'sort' ] : 0,// 排序,
            'video_url' => isset($this->params[ 'video_url' ]) ? $this->params[ 'video_url' ] : '',// 视频
            'goods_sku_data' => isset($this->params[ 'goods_sku_data' ]) ? $this->params[ 'goods_sku_data' ] : '',// SKU商品数据
            'label_id' => isset($this->params[ 'label_id' ]) ? $this->params[ 'label_id' ] : '',// 商品分组id
            'max_buy' => isset($this->params[ 'max_buy' ]) ? $this->params[ 'max_buy' ] : 0,// 限购
            'min_buy' => isset($this->params[ 'min_buy' ]) ? $this->params[ 'min_buy' ] : 0,// 起售
            'timer_on' => isset($this->params[ 'timer_on' ]) ? strtotime($this->params[ 'timer_on' ]) : 0,//定时上架
            'timer_off' => isset($this->params[ 'timer_off' ]) ? strtotime($this->params[ 'timer_off' ]) : 0,//定时下架
            'spec_type_status' => isset($this->params[ 'spec_type_status' ]) ? $this->params[ 'spec_type_status' ] : 0,

            'site_name' => $this->shop_info[ 'site_name' ],
            'virtual_sale' => $this->params[ 'virtual_sale' ] ?? 0,// 虚拟销量
            'is_consume_discount' => $this->params[ 'is_consume_discount' ] ?? 0, //是否参与会员折扣
            'goods_service_ids' => $this->params[ 'goods_service_ids' ] ?? '',// 商品服务id集合
            'recommend_way' => $this->params[ 'recommend_way' ] ?? 0, // 推荐方式，1：新品，2：精品，3；推荐

            'is_limit' => $this->params[ 'is_limit' ] ?? 0,// 商品是否限购,
            'limit_type' => $this->params[ 'limit_type' ] ?? 1, // 商品限购类型,
            'sale_show' => $this->params[ 'sale_show' ] ?? 0,
            'stock_show' => $this->params[ 'stock_show' ] ?? 0,
            'market_price_show' => $this->params[ 'market_price_show' ] ?? 0,
            'barrage_show' => $this->params[ 'barrage_show' ] ?? 0,
            'brand_id' => $this->params[ 'brand_id' ] ?? 0,
            'support_trade_type' => $this->params[ 'support_trade_type' ] ?? '',
            'form_id' => $this->params['goods_form'] ?? 0
        ];

        $res = $goods_model->editGoods($data);
        return $this->response($res);
    }

    /**
     * 获取编辑商品所需数据
     * @return false|string
     */
    public function editGetGoodsInfo()
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;

        $goods_model = new GoodsModel();
        $goods_info = $goods_model->editGetGoodsInfo([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ])[ 'data' ];
        $goods_sku_list = $goods_model->getGoodsSkuList([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ], "sku_id,sku_name,sku_no,sku_spec_format,price,market_price,cost_price,stock,weight,volume,sku_image,sku_images,goods_spec_format,spec_name,stock_alarm,is_default,verify_num", '')[ 'data' ];
        $goods_info[ 'goods_sku_data' ] = $goods_sku_list;

        if (!empty($goods_info[ 'shipping_template' ])) {
            //获取运费模板
            $express_template_model = new ExpressTemplateModel();
            $express_template_list = $express_template_model->getExpressTemplateList([ [ 'site_id', "=", $this->site_id ], [ 'template_id', '=', $goods_info[ 'shipping_template' ] ] ], 'template_name')[ 'data' ];
            if (!empty($express_template_list)) {
                $goods_info[ 'template_name' ] = $express_template_list[ 0 ][ 'template_name' ];
            }
        }
        return $this->response($this->success($goods_info));
    }

    /**
     * 删除商品
     */
    public function deleteGoods()
    {
        $goods_ids = isset($this->params[ 'goods_ids' ]) ? $this->params[ 'goods_ids' ] : 0;
        $goods_model = new GoodsModel();
        $res = $goods_model->modifyIsDelete($goods_ids, 1, $this->site_id);
        return $this->response($res);
    }

    /**
     * 商品回收站
     */
    public function recycle()
    {
        $page_index = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $search_keys = isset($this->params[ 'search_keys' ]) ? $this->params[ 'search_keys' ] : '';

        $condition = [ [ 'is_delete', '=', 1 ], [ 'site_id', "=", $this->site_id ] ];
        if (!empty($search_keys)) {
            $condition[] = [ 'goods_name', 'like', '%' . $search_keys . '%' ];
        }
        $goods_model = new GoodsModel();
        $res = $goods_model->getGoodsPageList($condition, $page_index, $page_size);
        return $this->response($res);
    }

    /**
     * 商品回收站商品删除
     */
    public function deleteRecycleGoods()
    {
        $goods_ids = isset($this->params[ 'goods_ids' ]) ? $this->params[ 'goods_ids' ] : 0;
        $goods_model = new GoodsModel();
        $res = $goods_model->deleteRecycleGoods($goods_ids, $this->site_id);
        return $this->response($res);
    }

    /**
     * 商品回收站商品恢复
     */
    public function recoveryRecycle()
    {
        $goods_ids = isset($this->params[ 'goods_ids' ]) ? $this->params[ 'goods_ids' ] : 0;
        $goods_model = new GoodsModel();
        $res = $goods_model->modifyIsDelete($goods_ids, 0, $this->site_id);
        return $this->response($res);
    }

    /**
     * 商品下架
     */
    public function offGoods()
    {
        $goods_ids = isset($this->params[ 'goods_ids' ]) ? $this->params[ 'goods_ids' ] : 0;
        $goods_state = isset($this->params[ 'goods_state' ]) ? $this->params[ 'goods_state' ] : 0;
        $goods_model = new GoodsModel();
        $res = $goods_model->modifyGoodsState($goods_ids, $goods_state, $this->site_id);
        return $this->response($res);
    }

    /**
     * 商品上架
     */
    public function onGoods()
    {
        $goods_ids = isset($this->params[ 'goods_ids' ]) ? $this->params[ 'goods_ids' ] : 0;
        $goods_state = isset($this->params[ 'goods_state' ]) ? $this->params[ 'goods_state' ] : 0;
        $goods_model = new GoodsModel();
        $res = $goods_model->modifyGoodsState($goods_ids, $goods_state, $this->site_id);
        return $this->response($res);
    }

    /**
     * 编辑商品库存价格
     * @return false|string
     */
    public function editGoodsStockPrice()
    {
        $sku_list = isset($this->params[ 'sku_list' ]) ? $this->params[ 'sku_list' ] : '';
        $model = new GoodsModel;
        $res = $model->editGoodsStockPrice($sku_list);
        return $this->response($res);
    }

    /**
     * 编辑商品库存
     * @return false|string
     */
    public function editGoodsStock()
    {
        $sku_list = isset($this->params[ 'sku_list' ]) ? $this->params[ 'sku_list' ] : '';
        $res = $this->error();
        if (!empty($sku_list)) {
            $sku_list = json_decode($sku_list, true);
            $model = new GoodsModel;
            $res = $model->editGoodsStock($sku_list, $this->site_id);
        }
        return $this->response($res);
    }

    /**
     * 获取商品分类列表
     * @return false|string
     */
    public function getCategoryList()
    {
        $category_id = isset($this->params[ 'category_id' ]) ? $this->params[ 'category_id' ] : 0;
        $goods_category_model = new GoodsCategoryModel();
        $condition = [
            [ 'pid', '=', $category_id ]
        ];
        $goods_category_list = $goods_category_model->getCategoryList($condition, 'category_id,category_name,level,commission_rate');
        return $this->response($goods_category_list);
    }

    /**
     * 获取商品分类列表
     * @return false|string
     */
    public function getCategoryTree()
    {
        $goods_category_model = new GoodsCategoryModel();
        $condition = [
            [ 'site_id', '=', $this->site_id ]
        ];
        $goods_category_list = $goods_category_model->getCategoryTree($condition, 'category_id,category_name,level,commission_rate,sort,pid');
        return $this->response($goods_category_list);
    }

    /**
     * 获取商品规格列表
     */
    public function getSpecList()
    {
        $attr_id = isset($this->params[ 'attr_id' ]) ? $this->params[ 'attr_id' ] : '';//排除已存在的规格项
        $search_text = isset($this->params[ 'search_text' ]) ? $this->params[ 'search_text' ] : '';

        $condition = [ [ 'is_spec', '=', 1 ], [ 'site_id', 'in', ( "0,$this->site_id" ) ] ];
        if (!empty($attr_id)) {
            $condition[] = [ 'attr_id', 'not in', $attr_id ];
        }
        if (!empty($search_text)) {
            $condition[] = [ 'attr_name', 'like', '%' . $search_text . '%' ];
        }
        $goods_attr_model = new GoodsAttributeModel();
        $spec_list = $goods_attr_model->getSpecList($condition, 'attr_id,attr_name,attr_class_name', 'attr_id desc', 50);
        return $this->response($spec_list);
    }

    /**
     * 获取商品规格值列表
     */
    public function getSpecValueList()
    {
        $attr_id = isset($this->params[ 'attr_id' ]) ? $this->params[ 'attr_id' ] : 0;
        $search_text = isset($this->params[ 'search_text' ]) ? $this->params[ 'search_text' ] : '';
        $condition = [];
        if (!empty($attr_id)) {
            $condition[] = [ 'attr_id', '=', $attr_id ];
        }
        if (!empty($search_text)) {
            $condition[] = [ 'attr_value_name', 'like', '%' . $search_text . '%' ];
        }

        $goods_attr_model = new GoodsAttributeModel();
        $spec_list = $goods_attr_model->getSpecValueList($condition, 'attr_value_id,attr_value_name');
        return $this->response($spec_list);
    }

    /**
     * 获取商品参数列表
     */
    public function getAttributeList()
    {
        $goods_attr_model = new GoodsAttributeModel();
        $attr_class_id = isset($this->params[ 'attr_class_id' ]) ? $this->params[ 'attr_class_id' ] : 0;// 商品类型id
        $attribute_list = $goods_attr_model->getAttributeList([ [ 'attr_class_id', '=', $attr_class_id ], [ 'is_spec', '=', 0 ], [ 'site_id', 'in', ( "0,$this->site_id" ) ] ], 'attr_id,attr_name,attr_class_id,attr_class_name,attr_type,attr_value_format');
        if (!empty($attribute_list[ 'data' ])) {
            foreach ($attribute_list[ 'data' ] as $k => $v) {
                if (!empty($v[ 'attr_value_format' ])) {
                    $attribute_list[ 'data' ][ $k ][ 'attr_value_format' ] = json_decode($v[ 'attr_value_format' ], true);
                }
            }
        }
        return $this->response($attribute_list);
    }

    /**
     * 获取SKU商品列表
     */
    public function getGoodsSkuList()
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;
        $goods_model = new GoodsModel();
        $res = $goods_model->getGoodsSkuList([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ], 'sku_id,sku_name,price,market_price,cost_price,stock,weight,volume,sku_no,sale_num,sku_image,spec_name,goods_id');
        return $this->response($res);
    }

    /**
     * 获取SKU商品出入库列表
     */
    public function getOutputList()
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;
        $goods_model = new GoodsModel();
        $res = $goods_model->getGoodsSkuList([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ], 'sku_id,sku_name,price,market_price,cost_price,stock', 'is_default desc,price asc');
        return $this->response($res);
    }

    /***********************************************************商品评价**************************************************/

    /**
     * 商品评价
     */
    public function evaluate()
    {
        $goods_evaluate = new GoodsEvaluateModel();

        $page_index = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $explain_type = isset($this->params[ 'explain_type' ]) ? $this->params[ 'explain_type' ] : ''; //1好评2中评3差评
        $is_show = isset($this->params[ 'is_show' ]) ? $this->params[ 'is_show' ] : ''; //1显示 0隐藏
        $search_text = isset($this->params[ 'search_text' ]) ? $this->params[ 'search_text' ] : ''; //搜索值
        $search_type = isset($this->params[ 'search_type' ]) ? $this->params[ 'search_type' ] : ''; //搜索类型
        $start_time = isset($this->params[ 'start_time' ]) ? $this->params[ 'start_time' ] : '';
        $end_time = isset($this->params[ 'end_time' ]) ? $this->params[ 'end_time' ] : '';

        $is_image = isset($this->params[ 'is_image' ]) ? $this->params[ 'is_image' ] : 0;//是否有图  1 有图  2 仅文字
        $is_reply = isset($this->params[ 'is_reply' ]) ? $this->params[ 'is_reply' ] : 0;//是否回复  1 已回复  2 未回复
        $condition = [
            [ "site_id", "=", $this->site_id ]
        ];
        $condition[] = [ 'is_audit', '=', 1 ];
        //评分类型
        if ($explain_type != "") {
            $condition[] = [ "explain_type", "=", $explain_type ];
        }
        if ($is_show != "") {
            $condition[] = [ "is_show", "=", $is_show ];
        }
        //评论内容
        if ($is_image > 0) {
            if ($is_image == 1) {
                $condition[] = [ "images", "<>", '' ];
            } else if ($is_image == 2) {
                $condition[] = [ "images", "=", '' ];
            }

        }
        //全部回复
        if ($is_reply > 0) {
            if ($is_reply == 1) {
                $condition[] = [ "explain_first", "<>", '' ];
            } else if ($is_reply == 2) {
                $condition[] = [ "explain_first", "=", '' ];
            }
        }

        if (!empty($search_text)) {
            if (!empty($search_type)) {
                $condition[] = [ $search_type, 'like', '%' . $search_text . '%' ];
            } else {
                $condition[] = [ 'sku_name', 'like', '%' . $search_text . '%' ];
            }
        }
        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ "create_time", ">=", date_to_time($start_time) ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ "create_time", "<=", date_to_time($end_time) ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
        }
        $list = $goods_evaluate->getEvaluatePageList($condition, $page_index, $page_size, "create_time desc");
        return $this->response($list);
    }

    /**
     * 商品评价删除
     */
    public function deleteEvaluate()
    {
        $goods_evaluate = new GoodsEvaluateModel();
        $evaluate_id = isset($this->params[ 'evaluate_id' ]) ? $this->params[ 'evaluate_id' ] : 0;
        $res = $goods_evaluate->deleteEvaluate($evaluate_id);
        return $this->response($res);
    }

    /**
     * 商品推广
     * return
     */
    public function goodsUrl()
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : '';

        $goods_model = new GoodsModel();
        $goods_sku_info = $goods_model->getGoodsSkuInfo([ [ 'goods_id', '=', $goods_id ] ], 'sku_id,goods_name')[ 'data' ];
        $res = $goods_model->qrcode($goods_id, $goods_sku_info[ 'goods_name' ], $this->site_id);
        return $this->response($res);
    }

    /**
     * 商品预览
     * return
     */
    public function goodsPreview()
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : '';
        $goods_model = new GoodsModel();
        $goods_sku_info = $goods_model->getGoodsSkuInfo([ [ 'goods_id', '=', $goods_id ] ], 'sku_id,goods_name')[ 'data' ];
        $res = $goods_model->qrcode($goods_sku_info[ 'sku_id' ], $goods_sku_info[ 'goods_name' ], $this->site_id);
        return $this->response($res);
    }

    /**
     * 商品评价回复
     */
    public function evaluateApply()
    {
        $goods_evaluate = new GoodsEvaluateModel();
        $evaluate_id = isset($this->params[ 'evaluate_id' ]) ? $this->params[ 'evaluate_id' ] : 0;
        $explain = isset($this->params[ 'explain' ]) ? $this->params[ 'explain' ] : 0;
        $is_first_explain = isset($this->params[ 'is_first_explain' ]) ? $this->params[ 'is_first_explain' ] : 0;// 是否第一次回复
        $data = [
            'evaluate_id' => $evaluate_id
        ];
        if ($is_first_explain == 0) {
            $data[ 'explain_first' ] = $explain;
        } elseif ($is_first_explain == 1) {
            $data[ 'again_explain' ] = $explain;
        }

        $res = $goods_evaluate->evaluateApply($data);
        return $this->response($res);
    }

    /**
     * 商品评价回复
     */
    public function deleteContent()
    {
        $goods_evaluate = new GoodsEvaluateModel();
        $evaluate_id = isset($this->params[ 'evaluate_id' ]) ? $this->params[ 'evaluate_id' ] : 0;
        $is_first_explain = isset($this->params[ 'is_first' ]) ? $this->params[ 'is_first' ] : 0;// 0 第一次回复，1 追评回复
        $data = [];
        if ($is_first_explain == 0) {
            $data[ 'explain_first' ] = '';
        } elseif ($is_first_explain == 1) {
            $data[ 'again_explain' ] = '';
        }
        $condition = [
            [ 'evaluate_id', '=', $evaluate_id ],
            [ 'site_id', '=', $this->site_id ],
        ];

        $res = $goods_evaluate->editEvaluate($data, $condition);
        return $this->response($res);
    }

    /**
     * 商品复制
     */
    public function copyGoods()
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;
        $goods_model = new GoodsModel();
        $result = $goods_model->copyGoods($goods_id, $this->site_id);
        return $this->response($result);
    }

    /**
     * 会员商品收藏
     */
    public function memberGoodsCollect()
    {
        $goods_collect_model = new GoodsCollect();
        $member_id = isset($this->params[ 'member_id' ]) ? $this->params[ 'member_id' ] : 0;

        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $condition = [];
        $condition[] = [ 'gc.site_id', '=', $this->site_id ];
        $condition[] = [ 'gc.member_id', '=', $member_id ];
        $order = 'gc.create_time desc';
        $field = 'gc.collect_id, gc.member_id, gc.goods_id, gc.sku_id,gc.sku_name, gc.sku_price, gc.sku_image,g.goods_name,g.is_free_shipping,sku.promotion_type,sku.discount_price,g.sale_num,g.price,g.market_price,g.is_virtual,sku.*';
        $res = $goods_collect_model->getCollectPageList($condition, $page, $page_size, $order, $field);
        return $this->response($res);
    }

    /**
     * 会员浏览记录
     */
    public function memberGoodsBrowse()
    {
        $member_id = isset($this->params[ 'member_id' ]) ? $this->params[ 'member_id' ] : 0;
        $goods_browse_model = new GoodsBrowse();

        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $search = isset($this->params[ 'search' ]) ? $this->params[ 'search' ] : '';
        $condition = [];
        $condition[] = [ 'gb.site_id', '=', $this->site_id ];
        $condition[] = [ 'gb.member_id', '=', $member_id ];
        if (!empty($search))
            $condition[] = [ 'gs.sku_name', 'like', '%' . $search . '%' ];

        $order = 'browse_time desc';
        $field = 'gb.*,gs.sku_name,gs.sku_image,gs.price,gs.goods_state,gs.stock,gs.click_num';
        $alias = 'gb';
        $join = [
            [ 'goods_sku gs', 'gs.sku_id = gb.sku_id', 'left' ]
        ];
        $res = $goods_browse_model->getBrowsePageList($condition, $page, $page_size, $order, $field, $alias, $join);
        return $this->response($res);
    }

    /**
     * 商品浏览记录
     */
    public function goodsBrowse()
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : '';
        $goods_browse_model = new GoodsBrowse();

        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $search = isset($this->params[ 'search' ]) ? $this->params[ 'search' ] : '';
        $condition = [];
        $condition[] = [ 'gb.site_id', '=', $this->site_id ];
        if ($goods_id > 0) {
            $condition[] = [ 'gb.goods_id', '=', $goods_id ];
        }
        if (!empty($search))
            $condition[] = [ 'gs.sku_name', 'like', '%' . $search . '%' ];

        $order = 'browse_time desc';
        $field = 'gb.*,gs.sku_name,gs.sku_image,gs.price,gs.goods_state,gs.stock,gs.click_num,m.nickname,m.headimg';
        $alias = 'gb';
        $join = [
            [ 'goods_sku gs', 'gs.sku_id = gb.sku_id', 'left' ],
            [ 'member m', 'm.member_id = gb.member_id', 'left' ]
        ];
        $res = $goods_browse_model->getBrowsePageList($condition, $page, $page_size, $order, $field, $alias, $join);
        return $this->response($res);
    }

    /**
     * 获取商品参数
     * @return false|string
     */
    public function getAttrClassList()
    {
        $goods_attr_model = new GoodsAttributeModel();
        $attr_class_list = $goods_attr_model->getAttrClassList([ [ 'site_id', 'in', ( "0,$this->site_id" ) ] ], 'class_id,class_name');
        return $this->response($attr_class_list);
    }

    /**
     * 获取商品设置参数
     * @return false|string
     */
    public function config()
    {
        $data = [];
        $config_model = new ConfigModel();
        $goods_sort_confog = $config_model->getGoodsSort($this->site_id, $this->app_module);
        $default_search_words = $config_model->getDefaultSearchWords($this->site_id, $this->app_module);
        $hot_search_words = $config_model->getHotSearchWords($this->site_id, $this->app_module);
        $hot_search_words = $hot_search_words[ 'data' ][ 'value' ];
        $words_array = [];
        if (!empty($hot_search_words[ 'words' ])) {
            $words_array = explode(',', $hot_search_words[ 'words' ]);
        }
        $hot_search_words[ 'words_array' ] = $words_array;
        $data[ "hot_words" ] = $hot_search_words;
        $data[ "default_words" ] = $default_search_words[ 'data' ][ 'value' ];
        $data[ "goods_sort_confog" ] = $goods_sort_confog[ 'data' ][ 'value' ];
        return $this->response($this->success($data));
    }

    /**
     * 编辑商品设置
     * @return false|string
     */
    public function setConfig()
    {
        $config_model = new ConfigModel();
        $hot_serch_words = isset($this->params[ 'hot_words' ]) ? $this->params[ 'hot_words' ] : [];
        $default_serch_words = isset($this->params[ 'default_words' ]) ? $this->params[ 'default_words' ] : '';
        $type = isset($this->params[ 'sort_type' ]) ? $this->params[ 'sort_type' ] : 'asc';
        $default_value = isset($this->params[ 'sort_value' ]) ? $this->params[ 'sort_value' ] : '0';
        $config_model->setHotSearchWords([ 'words' => implode(',', explode(',', $hot_serch_words)) ], $this->site_id, $this->app_module);
        $config_model->setDefaultSearchWords([ 'words' => $default_serch_words ], $this->site_id, $this->app_module);
        $config_model->setGoodsSort([ 'type' => trim($type), 'default_value' => trim($default_value) ], $this->site_id, $this->app_module);
        return $this->response($this->success());
    }

    /**
     * 核销码
     */
    public function verify()
    {
        $goods_id = $this->params['goods_id'] ?? 0;
        $virtual_goods_model = new \app\model\goods\VirtualGoods();

        $verify_count = $virtual_goods_model->getVirtualGoodsInfo([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ], 'count(id) as total_count, sum(verify_use_num) as verify_use_num')[ 'data' ] ?? [];
        return $this->response($this->success($verify_count));
    }

    public function virtualGoodsList()
    {
        $virtual_goods_model = new \app\model\goods\VirtualGoods();

        $goods_id = $this->params['goods_id'] ?? 0;
        $search_text = input("search_text", '');

        $field = 'gv.*, m.nickname,m.headimg';

        $page_index = input('page', 1);
        $page_size = input('page_size', PAGE_LIST_ROWS);
        $alias = 'gv';
        $condition = [
            [ "gv.site_id", "=", $this->site_id ],
            [ "gv.goods_id", "=", $goods_id ],
        ];
        if ($search_text) $condition[] = [ 'm.nickname|gv.code', 'like', '%' . $search_text . '%' ];
        $order = "gv.id desc";
        $join = [
            [
                'member m',
                'm.member_id = gv.member_id',
                'left'
            ]
        ];
        $list = $virtual_goods_model->getVirtualGoodsPageList($condition, $page_index, $page_size, $order, $field, $alias, $join);
        return $this->response($list);
    }
}
