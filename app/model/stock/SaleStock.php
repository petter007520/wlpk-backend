<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\stock;


use addon\o2o\model\store\Goods;
use app\model\store\Store;


/**
 * 库存model  (公共的库存相关改动和查询)
 *
 * @author Administrator
 *
 */
class SaleStock extends GoodsStock
{


    /**
     * 减少库存(存在已经)
     * @param $params
     */
    public function decGoodsStock($params)
    {
        $goods_id = $params[ 'goods_id' ] ?? 0;
        $sku_id = $params[ 'sku_id' ];
        $site_id = $params[ 'site_id' ] ?? 1;
        $store_id = $params[ 'store_id' ] ?? 0;
        $is_default = 0;

        if($store_id == 0 || !addon_is_exit('store')){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore($site_id)[ 'data' ] ?? [];
            $store_id = $store_info[ 'store_id' ];
            $is_default = 0;
        }

        $temp_stock = $params[ 'stock' ];
        if ($goods_id == 0) {
            $sku_info = model('goods_sku')->getInfo([ [ 'sku_id', '=', $sku_id ] ], 'goods_id');
            if (empty($sku_info)) {
                return $this->error([], '找不到商品');
            }
            $goods_id = $sku_info[ 'goods_id' ];
        }
        $return_info = $this->isNotExistCreateStoreStock([ 'store_id' => $store_id, 'sku_id' => $sku_id, 'goods_id' => $goods_id ])[ 'data' ] ?? [];
        $sku_info = $return_info[ 'sku_info' ];
        $stock = $sku_info[ 'stock' ] ?? 0;
        if ($stock < $temp_stock)
            return $this->error([], '库存不足');
        $sku_condition = array (
            [ 'sku_id', '=', $sku_id ],
            [ 'store_id', '=', $store_id ]
        );
        model('store_goods_sku')->setDec($sku_condition, 'stock', $temp_stock);

        $goods_condition = array (
            [ 'goods_id', '=', $goods_id ],
            [ 'store_id', '=', $store_id ]
        );
        model('store_goods')->setDec($goods_condition, 'stock', $temp_stock);

        if (!$is_default) {
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore($site_id)[ 'data' ] ?? [];
            $default_store_id = $store_info[ 'store_id' ];
            if ($store_id == $default_store_id) {
                $is_default = 1;
            }
        }
        if ($is_default) {
            model('goods_sku')->setDec([ [ 'sku_id', '=', $sku_id ] ], 'stock', $temp_stock);
            model('goods')->setDec([ [ 'goods_id', '=', $goods_id ] ], 'goods_stock', $temp_stock);
        }

        //todo  维护公共的销售库存
        return $this->success();
    }

    /**
     * 增加库存
     * @param $params
     * @return array
     */
    public function incGoodsStock($params)
    {
        $goods_id = $params[ 'goods_id' ] ?? 0;
        $sku_id = $params[ 'sku_id' ];
        $site_id = $params[ 'site_id' ] ?? 1;
        $store_id = $params[ 'store_id' ] ?? 0;
        $is_default = 0;
        if($store_id == 0 || !addon_is_exit('store')){

            $store_model = new Store();
            $store_info = $store_model->getDefaultStore($site_id)[ 'data' ] ?? [];
            $store_id = $store_info[ 'store_id' ];
            $is_default = 1;
        }
        $temp_stock = $params[ 'stock' ];
        if ($goods_id == 0) {
            $sku_info = model('goods_sku')->getInfo([ [ 'sku_id', '=', $sku_id ] ], 'goods_id');
            if (empty($sku_info)) {
                return $this->error([], '找不到商品');
            }
            $goods_id = $sku_info[ 'goods_id' ];
        }
        $return_info = $this->isNotExistCreateStoreStock([ 'store_id' => $store_id, 'sku_id' => $sku_id, 'goods_id' => $goods_id ])[ 'data' ] ?? [];
        $sku_condition = array (
            [ 'sku_id', '=', $sku_id ],
            [ 'store_id', '=', $store_id ]
        );

        model('store_goods_sku')->setInc($sku_condition, 'stock', $temp_stock);

        $goods_condition = array (
            [ 'goods_id', '=', $goods_id ],
            [ 'store_id', '=', $store_id ]
        );
        model('store_goods')->setInc($goods_condition, 'stock', $temp_stock);
        //todo  维护公共的销售库存

        if (!$is_default) {
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore($site_id)[ 'data' ] ?? [];
            $default_store_id = $store_info[ 'store_id' ];
            if ($store_id == $default_store_id) {
                $is_default = 1;
            }
        }
        if ($is_default) {
            model('goods_sku')->setInc([ [ 'sku_id', '=', $sku_id ] ], 'stock', $temp_stock);
            model('goods')->setInc([ [ 'goods_id', '=', $goods_id ] ], 'goods_stock', $temp_stock);
        }

        return $this->success();
    }

}
