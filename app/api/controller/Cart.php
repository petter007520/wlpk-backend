<?php

namespace app\api\controller;

use app\model\goods\Cart as CartModel;
use app\model\goods\Goods;

class Cart extends BaseApi
{
    /**
     * 添加信息
     */
    public function add()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $sku_id = isset($this->params[ 'sku_id' ]) ? $this->params[ 'sku_id' ] : 0;
        $num = isset($this->params[ 'num' ]) ? $this->params[ 'num' ] : 0;
        $form_data = $this->params[ 'form_data' ] ?? '';
        if (empty($sku_id)) {
            return $this->response($this->error('', 'REQUEST_SKU_ID'));
        }
        if (empty($num)) {
            return $this->response($this->error('', 'REQUEST_NUM'));
        }
        $cart = new CartModel();
        $data = [
            'site_id' => $this->site_id,
            'member_id' => $this->member_id,
            'sku_id' => $sku_id,
            'num' => $num,
            'form_data' => $form_data
        ];
        $res = $cart->addCart($data);
        return $this->response($res);
    }

    /**
     * 编辑信息
     */
    public function edit()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $cart_id = isset($this->params[ 'cart_id' ]) ? $this->params[ 'cart_id' ] : 0;
        $num = isset($this->params[ 'num' ]) ? $this->params[ 'num' ] : 0;
        $form_data = $this->params[ 'form_data' ] ?? '';
        if (empty($cart_id)) {
            return $this->response($this->error('', 'REQUEST_CART_ID'));
        }
        if (empty($num)) {
            return $this->response($this->error('', 'REQUEST_NUM'));
        }

        $cart = new CartModel();
        $data = [
            'cart_id' => $cart_id,
            'member_id' => $this->member_id,
            'num' => $num,
            'form_data' => $form_data
        ];
        $res = $cart->editCart($data);
        return $this->response($res);
    }

    /**
     * 删除信息
     */
    public function delete()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $cart_id = isset($this->params[ 'cart_id' ]) ? $this->params[ 'cart_id' ] : 0;
        if (empty($cart_id)) {
            return $this->response($this->error('', 'REQUEST_CART_ID'));
        }
        $cart = new CartModel();
        $data = [
            'cart_id' => $cart_id,
            'member_id' => $this->member_id,
        ];
        $res = $cart->deleteCart($data);
        return $this->response($res);
    }

    /**
     * 清空购物车
     */
    public function clear()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $cart = new CartModel();
        $data = [
            'member_id' => $this->member_id,
        ];
        $res = $cart->clearCart($data);
        return $this->response($res);
    }

    /**
     * 商品购物车列表
     */
    public function goodsLists()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $this->initStoreData();
        $goods = new Goods();

        $condition = [
            ['ngc.site_id', '=', $this->site_id],
            ['ngc.member_id', '=', $this->member_id],
            ['ngs.is_delete', '=', 0 ]
        ];
        $field = 'ngc.cart_id, ngc.site_id, ngc.member_id, ngc.sku_id, ngc.num, ngs.sku_name,ngs.goods_id,
            ngs.sku_no, ngs.sku_spec_format,ngs.price,ngs.market_price, ngs.goods_spec_format,
            ngs.discount_price, ngs.promotion_type, ngs.start_time, ngs.end_time, ngs.stock, 
            ngs.sku_image, ngs.sku_images, ngs.goods_state, ngs.goods_stock_alarm, ngs.is_virtual, ngs.goods_name, ngs.is_consume_discount, ngs.discount_config, ngs.member_price, ngs.discount_method,
            ngs.virtual_indate, ngs.is_free_shipping, ngs.shipping_template, ngs.unit, ngs.introduction,ngs.sku_spec_format, ngs.keywords, ngs.max_buy, ngs.min_buy, ns.site_name, ngs.is_limit, ngs.limit_type';
        $join = [
            ['goods_cart ngc', 'ngc.sku_id = ngs.sku_id', 'inner'],
            ['site ns', 'ns.site_id = ngs.site_id', 'left']
        ];
        // 如果是连锁运营模式
        if ($this->store_data['config']['store_business'] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'ngs.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ];
            $field .= ',IFNULL(sgs.status, 0) as store_goods_status';

            $field = str_replace('ngs.price', 'IFNULL(IF(ngs.is_unify_pirce = 1,ngs.price,sgs.price), ngs.price) as price', $field);
            $field = str_replace('ngs.discount_price', 'IFNULL(IF(ngs.is_unify_pirce = 1,ngs.discount_price,sgs.price), ngs.discount_price) as discount_price', $field);
            if ($this->store_data['store_info']['stock_type'] == 'store' ) {
                $field = str_replace('ngs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
            }
        }

        $list = $goods->getGoodsSkuList($condition, $field, 'ngc.cart_id desc', null, 'ngs', $join);
        if (!empty($list[ 'data' ])) {
            $list[ 'data' ] = $goods->getGoodsListMemberPrice($list[ 'data' ], $this->member_id);
        }
        return $this->response($list);
    }

    /**
     * 获取购物车数量
     * @return string
     */
    public function count()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $this->initStoreData();

        $cart = new CartModel();
        $condition = [
            [ 'gc.member_id', '=', $this->member_id ],
            [ 'gc.site_id', '=', $this->site_id ],
            [ 'gs.goods_state', '=', 1 ],
            [ 'gs.is_delete', '=', 0 ]
        ];
        $join = [
            [ 'goods_sku gs', 'gc.sku_id = gs.sku_id',  'inner' ]
        ];
        if ($this->store_data['config']['store_business'] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'gs.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ];
            $condition[] = ['sgs.status', '=', 1];
        }
        $count = $cart->getCartSum($condition, 'gc.num', 'gc', $join);
        return $this->response($count);
    }

    /**
     * 购物车关联列表
     * @return false|string
     */
    public function lists()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $this->initStoreData();
        $goods = new Goods();

        $condition = [
            ['ngc.site_id', '=', $this->site_id],
            ['ngc.member_id', '=', $this->member_id],
            ['ngs.is_delete', '=', 0 ]
        ];
        $field = 'ngc.cart_id,ngc.sku_id,ngs.goods_id,ngs.discount_price,ngc.num';
        $join = [
            ['goods_cart ngc', 'ngc.sku_id = ngs.sku_id', 'inner']
        ];
        // 如果是连锁运营模式
        if ($this->store_data['config']['store_business'] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'ngs.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ];
            $condition[] = ['sgs.status', '=', 1];
            $field = str_replace('ngs.discount_price', 'IFNULL(IF(ngs.is_unify_pirce = 1,ngs.discount_price,sgs.price), ngs.discount_price) as discount_price', $field);
        }
        $list = $goods->getGoodsSkuList($condition, $field, 'ngc.cart_id desc', null, 'ngs', $join);
        if (!empty($list[ 'data' ])) {
            $goods = new Goods();
            foreach ($list[ 'data' ] as $k => $v) {
                // 是否参与会员等级折扣
                $goods_member_price = $goods->getGoodsPrice($v[ 'sku_id' ], $this->member_id, $this->store_id);
                $goods_member_price = $goods_member_price[ 'data' ];
                if (!empty($goods_member_price[ 'member_price' ]) && $goods_member_price[ 'member_price' ] < $v['discount_price']) {
                    $list[ 'data' ][ $k ][ 'discount_price' ] = $goods_member_price[ 'price' ];
                }
                $list[ 'data' ][ $k ]['total_money'] = $list[ 'data' ][ $k ][ 'discount_price' ] * $v['num'];
            }
        }
        return $this->response($list);
    }

    /**
     * 获取会员购物车中商品数量
     * @return false|string
     */
    public function goodsNum(){
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $goods_id = $this->params['goods_id'] ?? 0;

        $condition = [
            [ 'gc.member_id', '=', $this->member_id ],
            [ 'gc.site_id', '=', $this->site_id ],
            [ 'gs.goods_id', '=', $goods_id ]
        ];

        $join = [
            [
                'goods_sku gs',
                'gc.sku_id = gs.sku_id',
                'left'
            ]
        ];

        $cart = new CartModel();
        $data = $cart->getCartSum($condition, 'gc.num', 'gc', $join);
        return $this->response($data);
    }

    public function editCartSku()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $cart_id = $this->params[ 'cart_id' ] ?? 0;
        $num = $this->params[ 'num' ] ?? 0;
        $sku_id = $this->params[ 'sku_id' ] ?? 0;
        if (empty($cart_id)) return $this->response($this->error('', 'REQUEST_CART_ID'));
        if (empty($num)) return $this->response($this->error('', 'REQUEST_NUM'));
        if (empty($sku_id)) return $this->response($this->error('', 'REQUEST_SKU_ID'));

        $cart = new CartModel();
        $data = [
            'cart_id' => $cart_id,
            'site_id' => $this->site_id,
            'member_id' => $this->member_id,
            'num' => $num,
            'sku_id' => $sku_id
        ];
        $res = $cart->editCartSku($data);
        return $this->response($res);
    }
}