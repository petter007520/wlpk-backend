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
use addon\stock\model\stock\Stock;
use app\model\BaseModel;
use app\model\store\Store;
use app\model\storegoods\StoreGoods;

/**
 * 库存model  (公共的库存相关改动和查询)
 *
 * @author Administrator
 *
 */
class GoodsStock extends BaseModel
{


    /**
     * 商品直接设置库存权重最高(只允许商品数据发生变动时调用)
     * @param $params
     */
    public function changeGoodsStock($params){
        $store_id = $params['store_id'] ?? 0;
        $site_id = $params['site_id'];

        $goods_sku_list = $params['goods_sku_list'] ?? [];

        //没有传递仓库id的话,就选取默认
        if($store_id == 0 || !addon_is_exit('store')){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore($site_id)['data'] ?? [];
            $store_id = $store_info['store_id'];
            $params['store_id'] = $store_id;
        }
        $is_exist = addon_is_exit('stock');
        if($is_exist){
            $stock_model = new Stock();
        }

        if(!empty($goods_sku_list)){
            $goods_sku_list_1 = [];
            $goods_sku_list_2 = [];
            foreach ($goods_sku_list as $k => $v) {
                if($is_exist && $v['goods_class'] == 1){
                    $goods_sku_list_1[] = $v;
                }else{
                    if(in_array($v['goods_class'], [1,2,3,4,5])){
                        $goods_sku_list_2[] = $v;
                    }
                }
            }
            if (!empty($goods_sku_list_1)) {
                $params['goods_sku_list'] = $goods_sku_list_1;
                $result = $stock_model->setGoodsStock($params);
                if($result['code'] < 0){
                    return $result;
                }
            }
            if (!empty($goods_sku_list_2)) {
                $params['goods_sku_list'] = $goods_sku_list_2;
                $result = $this->setGoodsStock($params);
                if($result['code'] < 0){
                    return $result;
                }
            }
        }else{
            $goods_class = $params['goods_class'];
            //如果存在进销存的话生成入库单据
            if($is_exist && $goods_class == 1){
                $result = $stock_model->setGoodsStock($params);
            }else{//没有的话直接生成支付单据
                $result = $this->setGoodsStock($params);
            }
        }


        return $result ?? $this->success();
    }

    /**
     * 商品库存设置(主体永远是sku)
     * @param $params
     * @return int
     */
    public function setGoodsStock($params)
    {
        $goods_sku_list = $params['goods_sku_list'] ?? [];

//        $site_id = $params['site_id'];
        $store_id = $params['store_id'] ?? 0;
        if($store_id == 0 || !addon_is_exit('store')){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore()['data'] ?? [];
            $store_id = $store_info['store_id'];
        }

        if(empty($goods_sku_list)){
            $goods_id = $params['goods_id'] ?? 0;
            $sku_id = $params['sku_id'];
            $temp_stock = $params['stock'];
            $goods_sku_list = [
                [
                    'stock' => $temp_stock,
                    'goods_id' => $goods_id,
                    'sku_id' => $sku_id
                ]
            ];
        }
        foreach($goods_sku_list as $k => $v) {
            $goods_id = $v['goods_id'] ?? 0;
            $sku_id = $v['sku_id'];
            $temp_stock = $v['stock'];//设置的新库存
            if ($temp_stock < 0)
                return $this->error([], '库存不能小于0');

            $condition = array(
//            ['goods_id', '=', $goods_id],
                ['store_id', '=', $store_id],
                ['sku_id', '=', $sku_id]
            );
            $data = array(
                'stock' => $temp_stock
            );
            $sku_info = model('goods_sku')->getInfo([['sku_id', '=', $sku_id]], 'sku_id, goods_id');
            if (empty($sku_info)) {
                return $this->error([], '找不到商品');
            }

            if ($goods_id == 0) {
                $goods_id = $sku_info['goods_id'];
            }
            $return_info = $this->isNotExistCreateStoreStock(['store_id' => $store_id, 'sku_id' => $sku_id, 'goods_id' => $goods_id])['data'] ?? [];
            $stock_goods_sku_info = $return_info['sku_info'];
            $before_stock = $stock_goods_sku_info['stock'];
            $before_real_stock = $stock_goods_sku_info['real_stock'];//实际库存比较特殊,只算差值
            $diff_stock = $temp_stock - $before_stock;

            $sku_real_stock = $before_real_stock + $diff_stock;
            $data['real_stock'] = $sku_real_stock > 0 ? $sku_real_stock : 0;
            $res = model('store_goods_sku')->update($data, $condition);
            $goods_condition = array(
                ['goods_id', '=', $goods_id],
                ['store_id', '=', $store_id]
            );
            if ($diff_stock > 0) {
                model('store_goods')->setInc($goods_condition, 'stock', abs($diff_stock));
                model('store_goods')->setInc($goods_condition, 'real_stock', abs($diff_stock));
            } else {
                model('store_goods')->setDec($goods_condition, 'stock', abs($diff_stock));
                $stock_goods_info = $return_info['goods_info'];
                $before_goods_real_stock = $stock_goods_info['real_stock'];
                if (abs($diff_stock) > $before_goods_real_stock) {
                    $diff_stock = $before_goods_real_stock;
                }
                model('store_goods')->setDec($goods_condition, 'real_stock', abs($diff_stock));
            }
            //维护商品和sku表中库存的统计数据
            $this->setCommonGoodsSkuStock(['sku_id' => $sku_id, 'diff' => $diff_stock, 'store_id' => $store_id]);
        }
        return $this->success();
    }

    /**
     * 减少库存(存在已经)
     * @param $params
     */
    public function decGoodsStock($params)
    {
//        $site_id = $params['site_id'];
        $store_id = $params['store_id'] ?? 0;
        $goods_sku_list = $params['goods_sku_list'] ?? [];
        if($store_id == 0 || !addon_is_exit('store')){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore()['data'] ?? [];
            $store_id = $store_info['store_id'];
        }
        $is_out_stock = $params['is_out_stock'] ?? 0;//是否扣除销售库存(销售状态下一般销售库存已经被扣除了)

        if(empty($goods_sku_list)){
            $goods_id = $params['goods_id'] ?? 0;
            $sku_id = $params['sku_id'];
            $temp_stock = $params['stock'] ?? $params['num'];
            $goods_sku_list = [
                [
                    'stock' => $temp_stock,
                    'goods_id' => $goods_id,
                    'sku_id' => $sku_id
                ]
            ];
        }
        foreach($goods_sku_list as $k => $v){
            $goods_id = $v['goods_id'] ?? 0;
            $sku_id = $v['sku_id'];
            $temp_stock = $v['stock'] ?? $v['num'];
            $sku_info = model('goods_sku')->getInfo([['sku_id', '=', $sku_id]], 'goods_id,sku_name');
            if (empty($sku_info)) {
                return $this->error([], '找不到商品');
            }
            if ($goods_id == 0) {
                $goods_id = $sku_info['goods_id'];
            }
            $sku_name = $sku_info['sku_name'] ;
            $return_info = $this->isNotExistCreateStoreStock(['store_id' => $store_id, 'sku_id' => $sku_id, 'goods_id' => $goods_id])['data'] ?? [];
            $sku_info = $return_info['sku_info'];
            $store_sku_stock = $sku_info['real_stock'];

            if($store_sku_stock < $temp_stock){
                return $this->error([], '产品'.$sku_name.'库存不足');
            }
            $before_stock = $sku_info['stock'];
            $before_real_stock = $sku_info['real_stock'];
            $sku_condition = array(
                ['sku_id', '=', $sku_id],
                ['store_id', '=', $store_id]
            );
            model('store_goods_sku')->setDec($sku_condition, 'real_stock', $temp_stock);
            if($is_out_stock){
                model('store_goods_sku')->setDec($sku_condition, 'stock',$temp_stock > $before_stock ? $before_stock : $temp_stock);
            }

            $goods_condition = array(
                ['goods_id', '=', $goods_id],
                ['store_id', '=', $store_id]
            );
            model('store_goods')->setDec($goods_condition, 'real_stock', $temp_stock);
            if($is_out_stock) {
                $goods_info = $return_info['goods_info'];
                $before_goods_stock = $goods_info['stock'];
                model('store_goods')->setDec($goods_condition, 'stock', $temp_stock > $before_goods_stock ? $before_goods_stock : $temp_stock);
            }
            //维护商品和sku表中库存的统计数据
            $this->setCommonGoodsSkuStock(['sku_id' => $sku_id, 'diff' => -$temp_stock, 'store_id' => $store_id]);
        }

        return $this->success();
    }

    /**
     * 增加库存
     * @param $params
     * @return array
     */
    public function incGoodsStock($params)
    {
        $goods_sku_list = $params['goods_sku_list'] ?? [];
        $goods_id = $params['goods_id'] ?? 0;
        $sku_id = $params['sku_id'];
//        $site_id = $params['site_id'];
        $store_id = $params['store_id'] ?? 0;
        if($store_id == 0 || !addon_is_exit('store')){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore()['data'] ?? [];
            $store_id = $store_info['store_id'];
        }
        if(empty($goods_sku_list)){
            $goods_id = $params['goods_id'] ?? 0;
            $sku_id = $params['sku_id'];
            $temp_stock = $params['stock'] ?? $params['num'];
            $goods_sku_list = [
                [
                    'stock' => $temp_stock,
                    'goods_id' => $goods_id,
                    'sku_id' => $sku_id
                ]
            ];
        }
        foreach($goods_sku_list as $k => $v) {
            $goods_id = $v['goods_id'] ?? 0;
            $sku_id = $v['sku_id'];
            $temp_stock = $v['stock'] ?? $v['num'];
            if ($goods_id == 0) {
                $sku_info = model('goods_sku')->getInfo([['sku_id', '=', $sku_id]], 'goods_id');
                if (empty($sku_info)) {
                    return $this->error([], '找不到商品');
                }
                $goods_id = $sku_info['goods_id'];
            }
            $return_info = $this->isNotExistCreateStoreStock(['store_id' => $store_id, 'sku_id' => $sku_id, 'goods_id' => $goods_id])['data'] ?? [];

            $sku_condition = array(
                ['sku_id', '=', $sku_id],
                ['store_id', '=', $store_id]
            );

            model('store_goods_sku')->setInc($sku_condition, 'stock', $temp_stock);
            model('store_goods_sku')->setInc($sku_condition, 'real_stock', $temp_stock);

            $goods_condition = array(
                ['goods_id', '=', $goods_id],
                ['store_id', '=', $store_id]
            );
            model('store_goods')->setInc($goods_condition, 'stock', $temp_stock);
            model('store_goods')->setInc($goods_condition, 'real_stock', $temp_stock);
            //维护商品和sku表中库存的统计数据
            $this->setCommonGoodsSkuStock(['sku_id' => $sku_id, 'diff' => $temp_stock, 'store_id' => $store_id]);
        }

        return $this->success();
    }

    /**
     * 设置总的商品库存(维护商品数据)
     * @param $params
     */
    public function setCommonGoodsSkuStock($params)
    {
        $sku_id = $params['sku_id'];
//        $diff = $params['diff'];
        $site_id = $params['site_id'] ?? 1;
        $store_model = new Store();
        $store_info = $store_model->getDefaultStore($site_id)['data'] ?? [];
        $default_store_id = $store_info['store_id'];
        $store_id = $params['store_id'];
        if($store_id == $default_store_id){
            $condition = array(
                ['sku_id', '=', $sku_id]
            );
            $sku_info = model('goods_sku')->getInfo($condition, 'goods_id');
            if (empty($sku_info))
                return $this->error();
            $goods_id = $sku_info['goods_id'];
            $goods_condition = array(
                ['goods_id', '=', $goods_id]
            );

            $store_good_model = new StoreGoods();
            $store_goods_info = $store_good_model->getStoreGoodsInfo([['goods_id', '=', $goods_id], ['store_id', '=', $store_id]])['data'] ?? [];
            model('goods')->update(['goods_stock' => $store_goods_info['stock'] ?? 0, 'real_stock' => $store_goods_info['real_stock'] ?? 0], $goods_condition);
            $store_sku_goods_info = $store_good_model->getStoreGoodsSkuInfo([['sku_id', '=', $sku_id], ['store_id', '=', $store_id]])['data'] ?? [];
            model('goods_sku')->update(['stock' => $store_sku_goods_info['stock'] ?? 0, 'real_stock' => $store_sku_goods_info['real_stock'] ?? 0], $condition);
        }

        //        $diff_stock = abs($diff);
//        if ($diff > 0) {
//            model('goods_sku')->setInc($condition, 'stock', $diff_stock);
//            model('goods')->setInc($goods_condition, 'goods_stock', $diff_stock);
//        } else if($diff < 0){
//            model('goods_sku')->setDec($condition, 'stock', $diff_stock);
//            model('goods')->setDec($goods_condition, 'goods_stock', $diff_stock);
//        }
        return $this->success();
    }

    /**
     * 如果库存数据不存在则创建
     * @param $params
     */
    public function isNotExistCreateStoreStock($params){
        $sku_id = $params['sku_id'];
        $store_id = $params['store_id'];
        $goods_id = $params['goods_id'];//经过校验的数据,无需再验
        $sku_store_condition = array(
            ['sku_id', '=', $sku_id],
            ['store_id', '=', $store_id]
        );
        $sku_info = model('store_goods_sku')->getInfo($sku_store_condition);
        //不存在就创建
        if(empty($sku_info)){
            $data = array(
                'goods_id' => $goods_id,
                'sku_id' => $sku_id,
                'store_id' => $store_id,
                'create_time' => time()
            );
            model('store_goods_sku')->add($data);
            $sku_info = model('store_goods_sku')->getInfo($sku_store_condition);
        }
        $sku_store_condition = array(
            ['goods_id', '=', $goods_id],
            ['store_id', '=', $store_id]
        );
        $goods_info = model('store_goods')->getInfo($sku_store_condition);
        //不存在就创建
        if(empty($goods_info)){
            $data = array(
                'goods_id' => $goods_id,
                'store_id' => $store_id,
                'create_time' => time()
            );
            model('store_goods')->add($data);
            $goods_info = model('store_goods')->getInfo($sku_store_condition);
        }
        return $this->success(['sku_info' => $sku_info, 'goods_info' => $goods_info]);
    }

    /**
     * 修改商品后调用商品库存变更
     * @param  array  $data
     */
    public function editGoodsChangeStock(array $param)
    {
        $sku_list = model('goods_sku')->getList([ ['goods_id', '=', $param['goods_id'] ], ['site_id', '=', $param['site_id'] ] ], 'goods_id,sku_id,stock,site_id,goods_class');
        if (empty($sku_list)) return $this->error();

        foreach ($sku_list as $sku_item) {
            $sku_item['user_info'] = $param['user_info'] ?? [];
            $this->changeGoodsStock($sku_item);
        }
        return $this->success();
    }

    /**
     * 核验可能不存在的sku门店数据,并校正数据(单个商品解决方案)
     * @param $params
     */
    public function checkExistGoodsSku($params){
        $goods_id = $params['goods_id'];
        $goods_condition = array(
            ['goods_id', '=', $goods_id]
        );
        $sku_ids = model('goods_sku')->getColumn($goods_condition, 'sku_id');
        $store_sku_condition = array(
            ['goods_id', '=', $goods_id],
            ['sku_id', 'not in', $sku_ids]
        );
        //被废弃的门店sku
        $store_sku_list = model('store_goods_sku')->getList($store_sku_condition, 'store_id, sum(stock) as stock, sum(real_stock) as real_stock', '', '', [], 'store_id');
        if(empty($store_sku_list))
            return $this->success();

        $stock = 0;
        $real_stock = 0;
        foreach($store_sku_list as $k => $v){
            $store_id = $v['store_id'];
            $item_stock = $v['stock'];
            $item_real_stock = $v['real_stock'];
            $item_store_goods_condition = $goods_condition;
            $item_store_goods_condition[] = ['store_id', '=', $store_id];
            $item_store_goods_info = model('store_goods')->getInfo($item_store_goods_condition, 'stock, real_stock');
            $new_item_stock = $item_store_goods_info['stock'] - $item_stock;
            $new_item_real_stock = $item_store_goods_info['real_stock'] - $item_real_stock;

            model('store_goods')->update([
                'stock' => $new_item_stock < 0 ? 0 : $new_item_stock,
                'real_stock' => $new_item_real_stock < 0 ? 0 : $new_item_real_stock
            ], $item_store_goods_condition);
            $stock += $item_stock;
            $real_stock += $item_real_stock;
        }
        model('store_goods_sku')->delete($store_sku_condition);

        $goods_info = model('goods')->getInfo($goods_condition, 'goods_stock, real_stock');
        $goods_stock = $goods_info['goods_stock'] - $stock;
        $goods_real_stock = $goods_info['real_stock'] - $real_stock;
        model('goods')->update([
            'stock' => $goods_stock < 0 ? 0 : $goods_stock,
            'real_stock' => $goods_real_stock < 0 ? 0 : $goods_real_stock
        ], $goods_condition);
        return $this->success();
    }
}
