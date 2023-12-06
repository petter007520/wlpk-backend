<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\storegoods;

use app\model\BaseModel;
use app\model\store\Store;

/**
 * 商品
 */
class StoreSale extends BaseModel
{

    /**
     * 增加商品销量
     * @param $sku_id
     * @param $num
     */
    public function incStoreGoodsSaleNum($params)
    {
        $sku_id = $params['sku_id'];
        $num = $params['num'];
        $store_id = $params['store_id'] ?? 0;
        if($store_id == 0){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore()['data'] ?? [];
            if(empty($store_info))
                return $this->error();

            $store_id = $store_info['store_id'];
        }
        $goods_id = $params['goods_id'] ?? 0;
        $condition = array (
            [ 'sku_id', '=', $sku_id ],
            ['store_id', '=', $store_id]
        );
        //增加sku销量
        $res = model('store_goods_sku')->setInc($condition, 'sale_num', $num);
        if ($res !== false) {
            if($goods_id == 0){
                $sku_info = model('goods_sku')->getInfo($condition, 'goods_id');
                if(empty($sku_info))
                    return $this->error();
                $goods_id = $sku_info[ 'goods_id' ];
            }

            $res = model('store_goods')->setInc([ [ 'goods_id', '=', $goods_id ] ], 'sale_num', $num);
            return $this->success($res);
        }

        return $this->error($res);
    }

    /**
     * 减少商品销量
     * @param $sku_id
     * @param $num
     */
    public function decStoreGoodsSaleNum($params)
    {
        $sku_id = $params['sku_id'];
        $num = $params['num'];
        $store_id = $params['store_id'] ?? 0;
        if($store_id == 0){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore()['data'] ?? [];
            if(empty($store_info))
                return $this->error();

            $store_id = $store_info['store_id'];
        }
        $goods_id = $params['goods_id'] ?? 0;
        $condition = array (
            [ 'sku_id', '=', $sku_id ],
            ['store_id', '=', $store_id]
        );
        //增加sku销量
        $res = model('store_goods_sku')->setDec($condition, 'sale_num', $num);
        if ($res !== false) {
            if($goods_id == 0){
                $sku_info = model('goods_sku')->getInfo($condition, 'goods_id');
                if(empty($sku_info))
                    return $this->error();
                $goods_id = $sku_info[ 'goods_id' ];
            }

            $res = model('store_goods')->setDec([ [ 'goods_id', '=', $goods_id ] ], 'sale_num', $num);
            return $this->success($res);
        }

        return $this->error($res);
    }

}