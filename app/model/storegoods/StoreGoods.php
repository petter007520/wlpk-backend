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
class StoreGoods extends BaseModel
{

    /**
     * 门店商品信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getStoreGoodsInfo($condition, $field = '*'){
        $info = model('store_goods')->getInfo($condition, $field);
        return $this->success($info);
    }


    /**
     * 门店sku信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getStoreGoodsSkuInfo($condition, $field = '*'){
        $info = model('store_goods_sku')->getInfo($condition, $field);
        return $this->success($info);
    }

    public function checkStoreGoods($goods_ids, $site_id, $store_id){
        $goods_list = model('goods')->getList([ ['goods_id', 'in', (string)$goods_ids], ['site_id', '=', $site_id] ], 'price, goods_id');

        foreach ($goods_list as $k => $v){
            $goods_condition = array(
                ['goods_id', '=', $v['goods_id']],
                ['store_id', '=', $store_id]
            );
            $goods_info = model('store_goods')->getInfo($goods_condition, 'id');
            //不存在就创建
            if(empty($goods_info)){
                $data = array(
                    'goods_id' => $v['goods_id'],
                    'store_id' => $store_id,
                    'create_time' => time(),
                    'price' => $v['price']
                );
                model('store_goods')->add($data);
            }
        }

        $sku_goods_list = model('goods_sku')->getList([ ['goods_id', 'in', (string)$goods_ids], ['site_id', '=', $site_id] ], 'price, goods_id, sku_id');

        foreach ($sku_goods_list as $k => $v){
            $sku_goods_condition = array(
                ['sku_id', '=', $v['sku_id']],
                ['store_id', '=', $store_id]
            );
            $sku_info = model('store_goods_sku')->getInfo($sku_goods_condition, 'id');
            //不存在就创建
            if(empty($sku_info)){
                $data = array(
                    'goods_id' => $v['goods_id'],
                    'store_id' => $store_id,
                    'sku_id' => $v['sku_id'],
                    'create_time' => time(),
                    'price' => $v['price']
                );
                model('store_goods_sku')->add($data);
            }
        }

        return $this->success(1);

    }

    /**
     * 门店修改商品状态
     * @param $goods_ids
     * @param $goods_state
     * @param $site_id
     * @return array
     */
    public function modifyGoodsState($goods_ids, $goods_state, $site_id, $store_id = 0)
    {
        if($store_id == 0){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore($site_id)['data'] ?? [];
            $store_id = $store_info['store_id'];
        }
        $this->checkStoreGoods($goods_ids, $site_id, $store_id);

        model('store_goods')->update([ 'status' => $goods_state ], [ [ 'goods_id', 'in', (string)$goods_ids ], ['store_id', '=', $store_id] ]);
        model('store_goods_sku')->update([ 'status' => $goods_state ], [ [ 'goods_id', 'in', (string)$goods_ids ], ['store_id', '=', $store_id] ]);
        return $this->success(1);
    }

    /**
     * 修改价格库存
     */
    public function editStoreGoods($goods_sku_array, $site_id, $store_id, $uid)
    {
        $store_stock_model = new \app\model\stock\GoodsStock();
        $store_model = new Store();

        $store_info = $store_model->getStoreInfo([ ['store_id', '=', $store_id] ])['data'] ?? [];

        $default_store_info = $store_model->getDefaultStore($site_id)['data'] ?? [];
        $default_store_id = $default_store_info['store_id'];
        $is_default_store = 0;
        if($default_store_id == $store_id){
            $is_default_store = 1;
        }
        model('goods')->startTrans();
        try {
            foreach ($goods_sku_array as $k => $v) {
                $item_sku_id = $v['sku_id'];

                $sku_info = model("goods_sku")->getInfo([ ['sku.site_id', '=', $site_id], [ 'sku.sku_id', '=', $item_sku_id ] ], "sku.goods_id,sku.stock,sku.price as sku_price,g.is_unify_pirce,g.price, g.goods_class", 'sku',[
                    ['goods g', 'sku.goods_id=g.goods_id','left']
                ]);
                $goods_id = $sku_info['goods_id'];
                $goods_info = model('store_goods')->getInfo([
                    ['goods_id', '=', $goods_id],
                    ['store_id', '=', $store_id]
                ], 'id');
                if(empty($goods_info)){
                    $data = array(
                        'goods_id' => $goods_id,
                        'store_id' => $store_id,
                        'create_time' => time(),
                        'price' => $sku_info['price']
                    );
                    model('store_goods')->add($data);
                }

                $store_sku_info = model('store_goods_sku')->getInfo([
                    ['sku_id', '=', $item_sku_id],
                    ['store_id', '=', $store_id]
                ], 'id');
                if(empty($store_sku_info)){
                    $data = array(
                        'goods_id' => $goods_id,
                        'store_id' => $store_id,
                        'sku_id' => $item_sku_id,
                        'create_time' => time(),
                        'price' => $sku_info['sku_price']
                    );
                    model('store_goods_sku')->add($data);
                }

                $save_data = [];
                if(!$sku_info['is_unify_pirce']){
                    $save_data['price'] = $v['price'];
                }

                if ($k == 0 && !empty($save_data)) {
                    model('store_goods')->update($save_data, [ [ 'goods_id', '=', $goods_id ], ['store_id', '=', $store_id] ]);
                }

                if(!empty($save_data)) model('store_goods_sku')->update($save_data, [ [ 'sku_id', '=', $item_sku_id ], ['store_id', '=', $store_id] ]);

                //统一库存不入库
                if(isset($v['stock']) && $store_info && $store_info['stock_type'] == 'store') {
                    $res = $store_stock_model->changeGoodsStock([
                        'store_id' => $store_id,
                        'site_id' => $site_id,
                        'sku_id' => $item_sku_id,
                        'stock' => $v['stock'],
                        'uid' => $uid,
                        'goods_class' => $sku_info['goods_class']
                    ]);
                }
                //如果是默认门店,也会同步修改平台价
                if(!empty($save_data['price'])){
                    if($is_default_store){
                        if ($k == 0 && !empty($save_data)) {
                            model('goods')->update(['price' => $save_data['price']], [['goods_id', '=', $goods_id]]);
                        }
                        model('goods_sku')->update(['price' => $save_data['price']], [['sku_id', '=', $item_sku_id]]);
                    }
                }


            }
            model('goods')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('goods')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 设置门店商品成本价
     * @param $params
     */
    public function setSkuPrice($params){
        $goods_id = $params['goods_id'];
        $site_id = $params['site_id'];
        $store_id = $params['store_id'] ?? 0;
        if($store_id == 0){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore($site_id)['data'] ?? [];
            $store_id = $store_info['store_id'];
        }

        $result = $this->checkStoreGoods($goods_id, $site_id, $store_id);
        $goods_condition = array(
            ['goods_id', '=', $goods_id]
        );
        $goods_info = model('goods')->getInfo($goods_condition, 'cost_price,price');
        $sku_list = model('goods_sku')->getList($goods_condition, 'sku_id,cost_price,price');
        $store_goods_condition = array(
            ['goods_id', '=', $goods_id],
            ['store_id', '=', $store_id]
        );
        model('store_goods')->update(['cost_price' => $goods_info['cost_price'],'price' => $goods_info['price'] ], $store_goods_condition);
        foreach($sku_list as $k => $v){
            $store_goods_sku_condition = array(
                ['sku_id', '=', $v['sku_id']],
                ['store_id', '=', $store_id]
            );
            $item_data = array(
                'cost_price' => $v['cost_price'],
                'price' => $v['price']
            );
            model('store_goods_sku')->update($item_data, $store_goods_sku_condition);
        }
        return $this->success();
    }


    /**
     * 同步数据
     * @param $params
     */
    public function syncGoodsData($params){
        $update_data = $params['update_data'];
        $condition = $params['condition'];
        $site_id = $params['site_id'];
        $store_id = $params['store_id'] ?? 0;
        if($store_id == 0){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore()['data'] ?? [];
            $store_id = $store_info['store_id'];
        }

        $goods_list = model('goods')->getList($condition, 'goods_id, price, cost_price');
        $goods_ids = array_column($goods_list, 'goods_id');
        //检验商品对否存在
        $result = $this->checkStoreGoods(implode(',', $goods_ids), $site_id, $store_id);

        $store_condition = array(['store_id', '=', $store_id]);
        foreach($goods_list as $k => $v){
            $item_update_data = array(
                'price' => $v['price'],
                'cost_price' => $v['cost_price']
            );
            $item_store_condition = $store_condition;
            $item_store_condition[] = ['goods_id', '=', $v['goods_id']];
            model('store_goods')->update($item_update_data, $item_store_condition);
        }
        $goods_sku_list = model('goods_sku')->getList($condition, 'sku_id, price, cost_price');
        foreach($goods_sku_list as $k => $v){
            $item_update_data = array(
                'price' => $v['price'],
                'cost_price' => $v['cost_price']
            );
            $item_store_condition = $store_condition;
            $item_store_condition[] = ['sku_id', '=', $v['sku_id']];
            model('store_goods_sku')->update($item_update_data, $item_store_condition);
        }
        return $this->success();
    }

}