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

use addon\form\model\Form;
use app\model\goods\Goods as GoodsModel;
use app\model\goods\GoodsAttribute as GoodsAttributeModel;
use app\model\goods\GoodsBrand as GoodsBrandModel;
use app\model\goods\GoodsCategory as GoodsCategoryModel;
use app\model\goods\GoodsLabel as GoodsLabelModel;
use app\model\goods\GoodsService as GoodsServiceModel;
use app\model\goods\VirtualGoods as VirtualGoodsModel;
use app\model\store\Store as StoreModel;
use app\model\web\Config as ConfigModel;


/**
 * 虚拟商品
 * Class Virtualgoods
 * @package app\shop\controller
 */
class Virtualgoods extends BaseShop
{

    /**
     * 添加商品
     * @return mixed
     */
    public function addGoods()
    {
        if (request()->isAjax()) {

            $category_id = input("category_id", 0);// 分类id
            $category_json = json_encode($category_id);//分类字符串
            $category_id = ',' . implode(',', $category_id) . ',';

            $data = [
                'goods_name' => input("goods_name", ""),// 商品名称,
                'goods_attr_class' => input("goods_attr_class", ""),// 商品类型id,
                'goods_attr_name' => input("goods_attr_name", ""),// 商品类型名称,
                'is_limit' => input("is_limit", "0"),// 商品是否限购,
                'limit_type' => input("limit_type", "1"),// 商品限购类型,
                'site_id' => $this->site_id,
                'category_id' => $category_id,
                'category_json' => $category_json,
                'goods_image' => input("goods_image", ""),// 商品主图路径
                'goods_content' => input("goods_content", ""),// 商品详情
                'goods_state' => input("goods_state", ""),// 商品状态（1.正常0下架）
                'price' => input("price", 0),// 商品价格（取第一个sku）
                'market_price' => input("market_price", 0),// 市场价格（取第一个sku）
                'cost_price' => input("cost_price", 0),// 成本价（取第一个sku）
                'sku_no' => input("sku_no", ""),// 商品sku编码
                'weight' => input("weight", ""),// 重量
                'volume' => input("volume", ""),// 体积
                'goods_stock' => input("goods_stock", 0),// 商品库存（总和）
                'goods_stock_alarm' => input("goods_stock_alarm", 0),// 库存预警
                'goods_spec_format' => input("goods_spec_format", ""),// 商品规格格式
                'goods_attr_format' => input("goods_attr_format", ""),// 商品参数格式
                'introduction' => input("introduction", ""),// 促销语
                'keywords' => input("keywords", ""),// 关键词
                'unit' => input("unit", ""),// 单位
                'sort' => input("sort", 0),// 排序,
                'video_url' => input("video_url", ""),// 视频
                'goods_sku_data' => input("goods_sku_data", ""),// SKU商品数据
                'goods_service_ids' => input("goods_service_ids", ''),// 商品服务id集合
                'label_id' => input("label_id", ''),// 商品分组id
                'brand_id' => input("brand_id", 0),//品牌id
                'virtual_sale' => input("virtual_sale", 0),// 虚拟销量
                'max_buy' => input("max_buy", 0),// 限购
                'min_buy' => input("min_buy", 0),// 起售
                'recommend_way' => input('recommend_way', 0), // 推荐方式，1：新品，2：精品，3；推荐
                'timer_on' => strtotime(input('timer_on', 0)),//定时上架
                'timer_off' => strtotime(input('timer_off', 0)),//定时下架
                'is_consume_discount' => input('is_consume_discount', 0),//是否参与会员折扣
                'is_need_verify' => input("is_need_verify", 0),// 是否需要核销
                'verify_validity_type' => input("verify_validity_type", 0),// 核销有效期类型
                'virtual_indate' => 0,// 虚拟商品有效期
                'qr_id' => input('qr_id', 0),// 社群二维码id
                'template_id' => input('template_id', 0), // 商品海报id
                'sale_show' => input('sale_show', 0),//
                'stock_show' => input('stock_show', 0),//
                'market_price_show' => input('market_price_show', 0),//
                'barrage_show' => input('barrage_show', 0),//
                'virtual_deliver_type' => input('virtual_deliver_type', ''),
                'virtual_receive_type' => input('virtual_receive_type', ''),
                'form_id' => input('form_id', 0),
                'sale_channel' => input('sale_channel', 'all'),
                'sale_store' => input('sale_store', 'all'),
            ];

            if ($data[ 'verify_validity_type' ] == 1) {
                $data[ 'virtual_indate' ] = input("virtual_indate", 0);
            } else if ($data[ 'verify_validity_type' ] == 2) {
                $data[ 'virtual_indate' ] = strtotime(input("virtual_time", ''));
            }

            $virtual_goods_model = new VirtualGoodsModel();
            $res = $virtual_goods_model->addGoods($data);
            return $res;
        } else {

            //获取一级商品分类
            $goods_category_model = new GoodsCategoryModel();
            $condition = [
                [ 'pid', '=', 0 ],
                [ 'site_id', '=', $this->site_id ]
            ];

            $goods_category_list = $goods_category_model->getCategoryList($condition, 'category_id,category_name,level,commission_rate')[ 'data' ];
            $this->assign("goods_category_list", $goods_category_list);

            //获取商品类型
            $goods_attr_model = new GoodsAttributeModel();
            $attr_class_list = $goods_attr_model->getAttrClassList([ [ 'site_id', '=', $this->site_id ] ], 'class_id,class_name')[ 'data' ];
            $this->assign("attr_class_list", $attr_class_list);

            // 商品服务
            $goods_service_model = new GoodsServiceModel();
            $service_list = $goods_service_model->getServiceList([ [ 'site_id', '=', $this->site_id ] ], 'id,service_name,icon')[ 'data' ];
            $this->assign("service_list", $service_list);

            // 商品分组
            $goods_label_model = new GoodsLabelModel();
            $label_list = $goods_label_model->getLabelList([ [ 'site_id', '=', $this->site_id ] ], 'id,label_name', 'sort ASC')[ 'data' ];
            $this->assign("label_list", $label_list);

            // 商品品牌
            $goods_brand_model = new GoodsBrandModel();
            $brand_list = $goods_brand_model->getBrandList([ [ 'site_id', '=', $this->site_id ] ], 'brand_id,brand_name', 'sort asc')[ 'data' ];
            $this->assign("brand_list", $brand_list);

            //商品默认排序值
            $config_model = new ConfigModel();
            $sort_config = $config_model->getGoodsSort($this->site_id);
            $sort_config = $sort_config[ 'data' ][ 'value' ];
            $this->assign("sort_config", $sort_config);

            //获取商品海报
            $poster_list = event('PosterTemplate', [ 'site_id' => $this->site_id ], true);
            if (!empty($poster_list)) {
                $poster_list = $poster_list[ 'data' ];
            }

            $this->assign('poster_list', $poster_list);
            $this->assign('virtualcard_exit', addon_is_exit('virtualcard', $this->site_id));

            $form_is_exit = addon_is_exit('form', $this->site_id);
            if ($form_is_exit) {
                $form_list = (new Form())->getFormList([ [ 'site_id', '=', $this->site_id ], [ 'form_type', '=', 'goods' ], [ 'is_use', '=', 1 ] ], 'id desc', 'id, form_name')['data'];
                $this->assign('form_list', $form_list);
            }
            $this->assign('form_is_exit', $form_is_exit);

            $this->assign('all_goodsclass', event('GoodsClass'));
            $this->assign('goods_class', (new VirtualGoodsModel())->getGoodsClass());

            $this->assign('store_is_exit', addon_is_exit('store', $this->site_id));

            return $this->fetch("virtualgoods/add_goods");
        }
    }

    /**
     * 编辑商品
     * @return mixed
     */
    public function editGoods()
    {
        $virtual_goods_model = new VirtualGoodsModel();
        if (request()->isAjax()) {

            $category_id = input("category_id", 0);// 分类id
            $category_json = json_encode($category_id);//分类字符串
            $category_id = ',' . implode(',', $category_id) . ',';

            $data = [
                'goods_id' => input("goods_id", 0),// 商品id
                'goods_name' => input("goods_name", ""),// 商品名称,
                'goods_attr_class' => input("goods_attr_class", ""),// 商品类型id,
                'goods_attr_name' => input("goods_attr_name", ""),// 商品类型名称,
                'is_limit' => input("is_limit", "0"),// 商品是否限购,
                'limit_type' => input("limit_type", "1"),// 商品限购类型,
                'site_id' => $this->site_id,
                'category_id' => $category_id,
                'category_json' => $category_json,
                'goods_image' => input("goods_image", ""),// 商品主图路径
                'goods_content' => input("goods_content", ""),// 商品详情
                'goods_state' => input("goods_state", ""),// 商品状态（1.正常0下架）
                'price' => input("price", 0),// 商品价格（取第一个sku）
                'market_price' => input("market_price", 0),// 市场价格（取第一个sku）
                'cost_price' => input("cost_price", 0),// 成本价（取第一个sku）
                'sku_no' => input("sku_no", ""),// 商品sku编码
                'weight' => input("weight", ""),// 重量
                'volume' => input("volume", ""),// 体积
                'goods_stock' => input("goods_stock", 0),// 商品库存（总和）
                'goods_stock_alarm' => input("goods_stock_alarm", 0),// 库存预警
                'goods_spec_format' => input("goods_spec_format", ""),// 商品规格格式
                'goods_attr_format' => input("goods_attr_format", ""),// 商品参数格式
                'introduction' => input("introduction", ""),// 促销语
                'keywords' => input("keywords", ""),// 关键词
                'unit' => input("unit", ""),// 单位
                'sort' => input("sort", 0),// 排序,
                'video_url' => input("video_url", ""),// 视频
                'goods_sku_data' => input("goods_sku_data", ""),// SKU商品数据
                'goods_service_ids' => input("goods_service_ids", ''),// 商品服务id集合
                'label_id' => input("label_id", ''),// 商品分组id
                'brand_id' => input("brand_id", 0),//品牌id
                'virtual_sale' => input("virtual_sale", 0),// 虚拟销量
                'max_buy' => input("max_buy", 0),// 限购
                'min_buy' => input("min_buy", 0),// 起售
                'recommend_way' => input('recommend_way', 0), // 推荐方式，1：新品，2：精品，3；推荐
                'timer_on' => strtotime(input('timer_on', 0)),//定时上架
                'timer_off' => strtotime(input('timer_off', 0)),//定时下架
                'spec_type_status' => input('spec_type_status', 0),
                'is_consume_discount' => input('is_consume_discount', 0),//是否参与会员折扣
                'is_need_verify' => input("is_need_verify", 0),// 是否需要核销
                'verify_validity_type' => input("verify_validity_type", 0),// 核销有效期类型
                'virtual_indate' => 0,// 虚拟商品有效期
                'verify_num' => input("verify_num", 1), // 核销次数
                'qr_id' => input('qr_id', 0),// 社群二维码id
                'template_id' => input('template_id', 0), // 商品海报id
                'sale_show' => input('sale_show', 0),//
                'stock_show' => input('stock_show', 0),//
                'market_price_show' => input('market_price_show', 0),//
                'barrage_show' => input('barrage_show', 0),//
                'virtual_deliver_type' => input('virtual_deliver_type', ''),
                'virtual_receive_type' => input('virtual_receive_type', ''),
                'form_id' => input('form_id', 0),
                'sale_channel' => input('sale_channel', 'all'),
                'sale_store' => input('sale_store', 'all'),
            ];

            if ($data[ 'verify_validity_type' ] == 1) {
                $data[ 'virtual_indate' ] = input("virtual_indate", 0);
            } else if ($data[ 'verify_validity_type' ] == 2) {
                $data[ 'virtual_indate' ] = strtotime(input("virtual_time", ''));
            }

            $res = $virtual_goods_model->editGoods($data);
            return $res;
        } else {

            $goods_model = new GoodsModel();
            $goods_id = input("goods_id", 0);
            $goods_info = $goods_model->editGetGoodsInfo([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ])[ 'data' ];

            $goods_sku_list = $virtual_goods_model->getGoodsSkuList([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ], "sku_id,sku_name,sku_no,sku_spec_format,price,market_price,cost_price,stock,virtual_indate,sku_image,sku_images,goods_spec_format,spec_name,stock_alarm,is_default,verify_num", '')[ 'data' ];
            $goods_info[ 'sku_list' ] = $goods_sku_list;
            $this->assign("goods_info", $goods_info);

            //获取一级商品分类
            $goods_category_model = new GoodsCategoryModel();
            $condition = [
                [ 'pid', '=', 0 ],
                [ 'site_id', '=', $this->site_id ]
            ];
            $goods_category_list = $goods_category_model->getCategoryList($condition, 'category_id,category_name,level,commission_rate')[ 'data' ];
            $this->assign("goods_category_list", $goods_category_list);

            //获取商品类型
            $goods_attr_model = new GoodsAttributeModel();
            $attr_class_list = $goods_attr_model->getAttrClassList([ [ 'site_id', '=', $this->site_id ] ], 'class_id,class_name')[ 'data' ];
            $this->assign("attr_class_list", $attr_class_list);

            // 商品服务
            $goods_service_model = new GoodsServiceModel();
            $service_list = $goods_service_model->getServiceList([ [ 'site_id', '=', $this->site_id ] ], 'id,service_name,icon')[ 'data' ];
            $this->assign("service_list", $service_list);

            //获取品牌
            $goods_brand_model = new GoodsBrandModel();
            $brand_list = $goods_brand_model->getBrandList([ [ 'site_id', '=', $this->site_id ] ], "brand_id, brand_name")[ 'data' ];
            $this->assign("brand_list", $brand_list);

            // 商品标签
            $goods_label_model = new GoodsLabelModel();
            $label_list = $goods_label_model->getLabelList([ [ 'site_id', '=', $this->site_id ] ], 'id,label_name', 'sort ASC')[ 'data' ];
            $this->assign("label_list", $label_list);

            //获取商品海报
            $poster_list = event('PosterTemplate', [ 'site_id' => $this->site_id ], true);
            if (!empty($poster_list)) {
                $poster_list = $poster_list[ 'data' ];
            }
            $this->assign('poster_list', $poster_list);

            $form_is_exit = addon_is_exit('form', $this->site_id);
            if ($form_is_exit) {
                $form_list = (new Form())->getFormList([ [ 'site_id', '=', $this->site_id ], [ 'form_type', '=', 'goods' ], [ 'is_use', '=', 1 ] ], 'id desc', 'id, form_name')['data'];
                $this->assign('form_list', $form_list);
            }
            $this->assign('form_is_exit', $form_is_exit);

            $store_is_exit = addon_is_exit('store', $this->site_id);
            if ($store_is_exit && $goods_info['sale_store'] != 'all') {
                $store_list = (new StoreModel())->getStoreList([ ['site_id', '=', $this->site_id ], ['store_id', 'in', $goods_info['sale_store'] ] ], 'store_id,store_name,status,address,full_address,is_frozen');
                $this->assign('store_list', $store_list['data']);
            }
            $this->assign('store_is_exit', $store_is_exit);

            return $this->fetch("virtualgoods/edit_goods");
        }
    }

}