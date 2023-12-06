<?php

namespace app\api\controller;

use app\model\goods\GoodsCollect as GoodsCollectModel;
use app\model\goods\Goods as GoodsModel;
use app\model\web\Config as ConfigModel;

/**
 * 商品收藏
 * @author Administrator
 *
 */
class Goodscollect extends BaseApi
{
    /**
     * 添加信息
     */
    public function add()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;
        $sku_id = isset($this->params[ 'sku_id' ]) ? $this->params[ 'sku_id' ] : 0;
        $sku_name = isset($this->params[ 'sku_name' ]) ? $this->params[ 'sku_name' ] : '';
        $sku_price = isset($this->params[ 'sku_price' ]) ? $this->params[ 'sku_price' ] : '';
        $sku_image = isset($this->params[ 'sku_image' ]) ? $this->params[ 'sku_image' ] : '';

        if (empty($goods_id)) {
            return $this->response($this->error('', 'REQUEST_GOODS_ID'));
        }
        if (empty($sku_id)) {
            return $this->response($this->error('', 'REQUEST_SKU_ID'));
        }
        $goods_collect_model = new GoodsCollectModel();
        $data = [
            'member_id' => $token[ 'data' ][ 'member_id' ],
            'goods_id' => $goods_id,
            'sku_id' => $sku_id,
            'sku_name' => $sku_name,
            'sku_price' => $sku_price,
            'sku_image' => $sku_image,
            'site_id' => $this->site_id
        ];
        $res = $goods_collect_model->addCollect($data);
        return $this->response($res);
    }

    /**
     * 删除信息
     */
    public function delete()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;
        if (empty($goods_id)) {
            return $this->response($this->error('', 'REQUEST_GOODS_ID'));
        }
        $goods_collect_model = new GoodsCollectModel();
        $res = $goods_collect_model->deleteCollect($token[ 'data' ][ 'member_id' ], $goods_id);
        return $this->response($res);

    }

    /**
     * 分页列表信息
     */
    public function page()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $this->initStoreData();

        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $goods_collect_model = new GoodsModel();

        $condition = [
            [ 'gc.member_id', '=', $token[ 'data' ][ 'member_id' ] ],
            [ ' g.is_delete', '=', 0 ]
        ];
        $join = [
            [ 'goods_collect gc', 'gc.goods_id = g.goods_id', 'inner' ],
            [ 'goods_sku sku', 'g.sku_id = sku.sku_id', 'inner' ]
        ];
        $field = 'gc.collect_id, gc.member_id, gc.goods_id, gc.sku_id,sku.sku_name, gc.sku_price, gc.sku_image,g.goods_name,g.is_free_shipping,sku.promotion_type,sku.member_price,sku.discount_price,g.sale_num,g.price,g.market_price,g.is_virtual, g.goods_image';
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'g.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ];
            $field = str_replace('sku.price', 'IFNULL(IF(sku.is_unify_pirce = 1,sku.price,sgs.price), sku.price) as price', $field);
            $field = str_replace('sku.discount_price', 'IFNULL(IF(sku.is_unify_pirce = 1,sku.discount_price,sgs.price), sku.discount_price) as discount_price', $field);
        }

        $list = $goods_collect_model->getGoodsPageList($condition, $page, $page_size, 'gc.create_time desc', $field, 'g', $join);
        return $this->response($list);

    }

    /**
     * 检测用户是否收藏商品
     * @param int $id
     * @return false|string
     */
    public function iscollect($id = 0)
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;
        if (!empty($id)) {
            $goods_id = $id;
        }
        if (empty($goods_id)) {
            return $this->response($this->error('', 'REQUEST_GOODS_ID'));
        }

        $goods_collect_model = new GoodsCollectModel();
        $res = $goods_collect_model->getIsCollect($goods_id, $token[ 'data' ][ 'member_id' ]);
        return $this->response($res);
    }

}