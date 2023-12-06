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

use app\model\goods\VirtualGoods as VirtualGoodsModel;

/**
 * 虚拟商品
 * Class Virtualgoods
 * @package app\shop\controller
 */
class Virtualgoods extends BaseApi
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
            'is_need_verify' => $this->params[ 'is_need_verify' ] ?? 0, // 是否需要核销
            'verify_validity_type' => $this->params[ 'verify_validity_type' ] ?? 0,// 核销有效期类型
            'virtual_indate' => $this->params[ 'virtual_indate' ] ?? 1,// 虚拟商品有效期
            'verify_num' => $this->params[ 'verify_num' ] ?? 1, // 核销次数
            'is_limit' => $this->params[ 'is_limit' ] ?? 0,// 商品是否限购,
            'limit_type' => $this->params[ 'limit_type' ] ?? 1, // 商品限购类型,
           'sale_show' => $this->params['sale_show'] ?? 0,
           'stock_show' => $this->params['stock_show'] ?? 0,
           'market_price_show' => $this->params['market_price_show'] ?? 0,
           'barrage_show' => $this->params['barrage_show'] ?? 0,
           'brand_id' => $this->params['brand_id'] ?? 0,
           'virtual_deliver_type' => $this->params['virtual_deliver_type'] ?? '',
           'virtual_receive_type' => $this->params['virtual_receive_type'] ?? '',
           'form_id' => $this->params['goods_form'] ?? 0
        ];

        if ($data['verify_validity_type'] == 2) {
            $data['virtual_indate'] = strtotime($data['virtual_indate']);
        }

        $virtual_goods_model = new VirtualGoodsModel();
        $res = $virtual_goods_model->addGoods($data);
        return $this->response($res);
    }

    /**
     * 编辑商品
     * @return mixed
     */
    public function editGoods()
    {
        $virtual_goods_model = new VirtualGoodsModel();

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
            'spec_type_status' => isset($this->params[ 'spec_type_status' ]) ? strtotime($this->params[ 'spec_type_status' ]) : 0,

            'site_name' => $this->shop_info[ 'site_name' ],//店铺名
            'virtual_sale' => $this->params[ 'virtual_sale' ] ?? 0,// 虚拟销量
            'is_consume_discount' => $this->params[ 'is_consume_discount' ] ?? 0, //是否参与会员折扣
            'goods_service_ids' => $this->params[ 'goods_service_ids' ] ?? '',// 商品服务id集合
            'recommend_way' => $this->params[ 'recommend_way' ] ?? 0, // 推荐方式，1：新品，2：精品，3；推荐
            'is_need_verify' => $this->params[ 'is_need_verify' ] ?? 0, // 是否需核销
            'verify_validity_type' => $this->params[ 'verify_validity_type' ] ?? 0,// 核销有效期类型
            'virtual_indate' => $this->params[ 'virtual_indate' ] ?? 1,// 虚拟商品有效期
            'verify_num' => $this->params[ 'verify_num' ] ?? 1, // 核销次数
            'is_limit' => $this->params[ 'is_limit' ] ?? 0,// 商品是否限购,
            'limit_type' => $this->params[ 'limit_type' ] ?? 1, // 商品限购类型,
            'sale_show' => $this->params['sale_show'] ?? 0,
            'stock_show' => $this->params['stock_show'] ?? 0,
            'market_price_show' => $this->params['market_price_show'] ?? 0,
            'barrage_show' => $this->params['barrage_show'] ?? 0,
            'brand_id' => $this->params['brand_id'] ?? 0,
            'virtual_deliver_type' => $this->params['virtual_deliver_type'] ?? '',
            'virtual_receive_type' => $this->params['virtual_receive_type'] ?? '',
            'form_id' => $this->params['goods_form'] ?? 0
        ];

        if ($data['verify_validity_type'] == 2) {
            $data['virtual_indate'] = strtotime($data['virtual_indate']);
        }

        $res = $virtual_goods_model->editGoods($data);
        return $this->response($res);

    }

}