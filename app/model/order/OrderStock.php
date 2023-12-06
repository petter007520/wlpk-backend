<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\order;

use app\model\BaseModel;
use app\model\goods\Goods;
use app\model\stock\GoodsStock;
use app\model\stock\SaleStock;
use app\model\store\Store;
use app\model\storegoods\StoreGoods;
use think\facade\Cache;
use addon\stock\model\stock\Stock as StockAddonModel;

/**
 * 商品库存
 */
class OrderStock extends BaseModel
{

    /**
     * 设置库存原子
     * @param $sku_id
     * @return array
     */
    public function setGoodsSkuStock($sku_id, $store_id = 0)
    {
//        $goods_model = new Goods();
//        $goods_sku_condition = array(
//            ['sku_id', '=', $sku_id]
//        );
//        $goods_sku_info = $goods_model->getGoodsSkuInfo($goods_sku_condition, 'stock')['data'] ?? [];

        if($store_id == 0){
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore()['data'] ?? [];
            $store_id = $store_info['store_id'];
        }
        $store_goods_model = new StoreGoods();
        $store_goods_condition = array(
            ['sku_id', '=', $sku_id],
            ['store_id', '=', $store_id]
        );
        $store_sku_info = $store_goods_model->getStoreGoodsSkuInfo($store_goods_condition)['data'] ?? [];
        if(empty($store_sku_info))
            return $this->error();
        $stock = $store_sku_info['stock'];
        //设定商品数量
        $key = "goods_sku_stock".$sku_id;
        //创建连接redis对象
        $redis = Cache::store('redis')->handler();
        $surplus_stock = $redis->llen($key);
        $count = $stock - $surplus_stock;
        for ($i = 1; $i <= $count; $i++) {
            //将商品id push到列表中
            $redis->rPush($key, 1);
        }
        Cache::set('order_goods_sku_stock'.$sku_id, 1);
        return $this->success();

    }

    /**
     * 检测库存原子
     * @param $params
     * @return array
     */
    public function checkOrderSkuStock($params){
        $sku_id = $params['sku_id'];
        $goods_stock_cache = Cache::get('order_goods_sku_stock'.$sku_id);

        if(empty($goods_stock_cache)){
            $this->setGoodsSkuStock($sku_id);
        }
        return $this->success();
    }

    /**
     * 扣除订单库存
     * @param $sku_id
     * @param $num
     * @return array
     */
    public function decOrderSaleStock($sku_id, $num, $store_id = 0){
        $cache_driver = config('cache')['default'];
        if($cache_driver == 'redis'){//todo  应该会有特定的开关
            $this->checkOrderSkuStock(['sku_id' => $sku_id]);
            $redis = Cache::store('redis')->handler();
            $key = 'goods_sku_stock'.$sku_id;
            $start_num = 0;
            while($start_num < $num){
                $start_num++;
                $item = $redis->lPop($key);
                if (!$item) {
                    return $this->error();
                }
            }
        }
        $sale_stock_model = new SaleStock();
        if($store_id > 0){
            $store_model = new Store();
            $store_condition = array(
                ['store_id', '=', $store_id]
            );
            $store_info = $store_model->getStoreInfo($store_condition)['data'] ?? [];
            $stock_type = $store_info['stock_type'];
            if($stock_type == 'all'){//如果总部统一库存的话就扣除总店的
                $store_id = 0;
            }
        }
        $sale_stock_result = $sale_stock_model->decGoodsStock(['sku_id' => $sku_id, 'stock' => $num, 'store_id' => $store_id]);
        if ($sale_stock_result['code'] < 0) {
            return $sale_stock_result;
        }
        return $this->success();

    }


    /**
     * 扣除订单库存
     * @param $sku_id
     * @param $num
     * @return array
     */
    public function incOrderSaleStock($sku_id, $num, $store_id = 0){
        $cache_driver = config('cache')['default'];
        //todo 返回库存的话还得把原子加上
        if($cache_driver == 'redis'){//todo  应该会有特定的开关
            $this->checkOrderSkuStock(['sku_id' => $sku_id]);
            $redis = Cache::store('redis')->handler();
            $key = 'goods_sku_stock'.$sku_id;
            $start_num = 0;
            while($start_num < $num){
                $start_num++;
               //增加原子
                $redis->rPush($key, 1);
            }
        }
        $sale_stock_model = new SaleStock();
        if($store_id > 0){
            $store_model = new Store();
            $store_condition = array(
                ['store_id', '=', $store_id]
            );
            $store_info = $store_model->getStoreInfo($store_condition)['data'] ?? [];
            $stock_type = $store_info['stock_type'];
            if($stock_type == 'all'){//如果总部统一库存的话就扣除总店的
                $store_id = 0;
            }
        }
        $sale_stock_result = $sale_stock_model->incGoodsStock(['sku_id' => $sku_id, 'stock' => $num, 'store_id' => $store_id]);
        if ($sale_stock_result['code'] < 0) {
            return $sale_stock_result;
        }
        return $this->success();

    }

    /**
     * 阻塞式锁
     */
    public function lock()
    {

        $redis = Cache::store('redis')->handler();
        $store = 1000;//库存()
//        $redis = new \Redis();
//        $result = $redis->connect('127.0.0.1', 6379);
        $res = $redis->llen('goods_store');
        echo $res;
        $count = $store - $res;
        for ($i = 0; $i < $count; $i++) {
            $redis->lpush('goods_store', 1);
        }
        echo $redis->llen('goods_store');
    }


    /**
     * 扣除库存(用于订单)
     * @param $params
     * @return array
     */
    public function decOrderStock($params){
        $params['is_out_stock'] = 0;//不再改变销售库存
        $store_id = $params['store_id'] ?? 0;
        if($store_id > 0){
            $store_model = new Store();
            $store_condition = array(
                ['store_id', '=', $store_id]
            );
            $store_info = $store_model->getStoreInfo($store_condition)['data'] ?? [];
            $stock_type = $store_info['stock_type'];
            if($stock_type == 'all'){//如果总部统一库存的话就扣除总店的
                $params['store_id'] = 0;
            }
        }
        $is_exist = addon_is_exit('stock');
        if($is_exist){
            $stock_model = new StockAddonModel();
        }
        $goods_sku_list = $params['goods_sku_list'] ?? [];
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
                $params['key'] = 'SEAILCK';
                $result = $stock_model->changeStock($params);
                if($result['code'] < 0){
                    return $result;
                }
            }
            if (!empty($goods_sku_list_2)) {
                $params['goods_sku_list'] = $goods_sku_list_2;
                $goods_stock_model = new GoodsStock();
                $result = $goods_stock_model->decGoodsStock($params);
                if($result['code'] < 0){
                    return $result;
                }
            }
        }else{
            $goods_class = $params['goods_class'];
            $params['stock'] = $params['num'] ?? $params['stock'];
            if($is_exist && $goods_class == 1){
                $params['key'] = 'SEAILCK';
                $result = $stock_model->changeStock($params);
            }else{//没有的话直接生成支付单据
                $goods_stock_model = new GoodsStock();
                $result = $goods_stock_model->decGoodsStock($params);
            }
        }

        return $result ?? $this->success();

    }


    /**
     * 返还库存(用于订单)
     * @param $params
     * @return array
     */
    public function incOrderStock($params){
        $store_id = $params['store_id'] ?? 0;
        if($store_id > 0){
            $store_model = new Store();
            $store_condition = array(
                ['store_id', '=', $store_id]
            );
            $store_info = $store_model->getStoreInfo($store_condition)['data'] ?? [];
            $stock_type = $store_info['stock_type'];
            if($stock_type == 'all'){//如果总部统一库存的话就返还总店的
                $params['store_id'] = 0;
            }
        }
        $is_exist = addon_is_exit('stock');
        if($is_exist){
            $stock_model = new StockAddonModel();
        }
        $goods_sku_list = $params['goods_sku_list'] ?? [];
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
                $params['key'] = 'REFUND';
                $result = $stock_model->changeStock($params);
                if($result['code'] < 0){
                    return $result;
                }
            }
            if (!empty($goods_sku_list_2)) {
                $params['goods_sku_list'] = $goods_sku_list_2;
                $goods_stock_model = new GoodsStock();
                $result = $goods_stock_model->incGoodsStock($params);
                if($result['code'] < 0){
                    return $result;
                }
            }
        }else{
            $goods_class = $params['goods_class'];
            $params['stock'] = $params['num'] ?? $params['stock'];
            if($is_exist && $goods_class == 1){
                $params['key'] = 'REFUND';
                $result = $stock_model->changeStock($params);
            }else{//没有的话直接生成支付单据
                $goods_stock_model = new GoodsStock();
                $result = $goods_stock_model->incGoodsStock($params);
            }
        }
        return $result ?? $this->success();

    }

}