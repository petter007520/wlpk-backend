<?php

/**
 * Index.php
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @author : niuteam
 * @date : 2022.8.8
 * @version : v5.0.0.1
 */

namespace app\api\controller;

use app\model\goods\Goods as GoodsModel;
use app\model\goods\GoodsCategory as GoodsCategoryModel;
use addon\pc\model\Pc as PcModel;
use app\model\web\Config as ConfigModel;

/**
 * Pc端接口
 * @author Administrator
 *
 */
class Pc extends BaseApi
{
    /**
     * 获取首页浮层
     */
    public function floatLayer()
    {
        $pc_model = new PcModel();
        $info = $pc_model->getFloatLayer($this->site_id);
        return $this->response($this->success($info[ 'data' ][ 'value' ]));
    }

    /**
     * 楼层列表
     *
     * @return string
     */
    public function floors()
    {
        $pc_model = new PcModel();
        $condition = [
            [ 'state', '=', 1 ],
            [ 'site_id', '=', $this->site_id ]
        ];
        $list = $pc_model->getFloorList($condition, 'pf.title,pf.value,fb.name as block_name,fb.title as block_title');
        if (!empty($list[ 'data' ])) {
            $config_model = new ConfigModel();
            $sort_config = $config_model->getGoodsSort($this->site_id);
            $sort_config = $sort_config[ 'data' ][ 'value' ];

            $goods_model = new GoodsModel();
            $goods_category_model = new GoodsCategoryModel();
            foreach ($list[ 'data' ] as $k => $v) {
                $value = $v[ 'value' ];
                if (!empty($value)) {
                    $value = json_decode($value, true);
                    foreach ($value as $ck => $cv) {
                        if (!empty($cv[ 'type' ])) {
                            if ($cv[ 'type' ] == 'goods') {
                                $field = 'gs.sku_id,gs.price,gs.market_price,gs.discount_price,g.goods_stock,(g.sale_num + g.virtual_sale) as sale_num,g.goods_image,g.goods_name,g.introduction';
                                $order = 'g.sort ' . $sort_config[ 'type' ] . ',g.create_time desc';
                                $join = [
                                    [ 'goods g', 'gs.sku_id = g.sku_id', 'inner' ]
                                ];
                                $goods_sku_list = $goods_model->getGoodsSkuPageList([ [ 'gs.goods_id', 'in', $cv[ 'value' ][ 'goods_ids' ] ] ], 1, 0, $order, $field, 'gs', $join);
                                $goods_sku_list = $goods_sku_list[ 'data' ][ 'list' ];
                                $value[ $ck ][ 'value' ][ 'list' ] = $goods_sku_list;
                            } elseif ($cv[ 'type' ] == 'category') {
                                // 商品分类
                                $condition = [
                                    [ 'category_id', 'in', $cv[ 'value' ][ 'category_ids' ] ],
                                    [ 'site_id', '=', $this->site_id ]
                                ];
                                $category_list = $goods_category_model->getCategoryList($condition, 'category_id,category_name,short_name,level,image,image_adv');
                                $category_list = $category_list[ 'data' ];
                                $value[ $ck ][ 'value' ][ 'list' ] = $category_list;
                            }
                        }
                    }
                    $list[ 'data' ][ $k ][ 'value' ] = $value;
                }
            }
        }
        return $this->response($list);
    }

    /**
     * 获取导航
     */
    public function navList()
    {
        $pc_model = new PcModel();
        $data = $pc_model->getNavList([ [ 'is_show', '=', 1 ], [ 'site_id', '=', $this->site_id ] ], 'id,nav_title,nav_url,sort,is_blank,create_time,modify_time,nav_icon,is_show', 'sort asc,create_time desc');
        return $this->response($data);
    }

    /**
     * 获取友情链接
     */
    public function friendlyLink()
    {
        $pc_model = new PcModel();
        $data = $pc_model->getlinkList([ [ 'is_show', '=', 1 ], [ 'site_id', '=', $this->site_id ] ], 'id,link_title,link_url,link_pic,link_sort,is_blank', 'link_sort asc,id desc');
        return $this->response($data);
    }
}
