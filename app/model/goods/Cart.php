<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\goods;


use app\model\BaseModel;
use app\model\system\Stat;

/**
 * 购物车
 */
class Cart extends BaseModel
{

    /**
     * 添加购物车
     * @param unknown $data
     */
    public function addCart($data)
    {
        $cart_info = model("goods_cart")->getInfo([ [ 'sku_id', '=', $data[ 'sku_id' ] ], [ 'member_id', '=', $data[ 'member_id' ] ] ], 'cart_id, num');
        if (!empty($cart_info)) {
            $update = [
                'num' =>  $cart_info[ 'num' ] + $data[ 'num' ]
            ];
            if (isset($data['form_data']) && !empty($data['form_data'])) $update['form_data'] = $data['form_data'];

            $res = model("goods_cart")->update($update, [ [ 'cart_id', '=', $cart_info[ 'cart_id' ] ] ]);

            $stat_model = new Stat();
            $stat_model->switchStat([ 'type' => 'goods_cart', 'data' => [
                'goods_cart_count' =>  $data[ 'num' ],
                'site_id' => $data['site_id']
            ] ]);
        } else {
            $res = model("goods_cart")->add($data);
            $stat_model = new Stat();
            $stat_model->switchStat([ 'type' => 'goods_cart', 'data' => [
                'goods_cart_count' => $data['num'],
                'site_id' => $data['site_id']
            ] ]);
        }
        return $this->success($res);
    }

    /**
     * 更新购物车商品数量
     * @param unknown $data
     */
    public function editCart($data)
    {
        $update = [
            'num' => $data[ 'num' ]
        ];
        if (isset($data['form_data']) && !empty($data['form_data'])) $update['form_data'] = $data['form_data'];
        $condition = [ [ 'cart_id', '=', $data[ 'cart_id' ] ], [ 'member_id', '=', $data[ 'member_id' ] ] ];
        $info = model("goods_cart")->getInfo($condition, 'site_id,num');
        if(empty($info))
            return $this->error();

        $res = model("goods_cart")->update($update, $condition);
        $stat_model = new Stat();
        $stat_model->switchStat([ 'type' => 'goods_cart', 'data' => [
            'goods_cart_count' =>  $data['num'] - $info['num'],
            'site_id' => $info['site_id']
        ] ]);
        return $this->success($res);
    }

    /**
     * 删除购物车商品项(可以多项)
     * @param unknown $data
     */
    public function deleteCart($data)
    {
        $res = model("goods_cart")->delete([ [ 'cart_id', 'in', explode(',', $data[ 'cart_id' ]) ], [ 'member_id', '=', $data[ 'member_id' ] ] ]);
        return $this->success($res);
    }

    /**
     * 清空购物车
     * @param unknown $data
     */
    public function clearCart($data)
    {
        $res = model("goods_cart")->delete([ [ 'member_id', '=', $data[ 'member_id' ] ] ]);
        return $this->success($res);
    }

    /**
     * 获取会员购物车
     * @param unknown $member_id
     * @param unknown $site_id
     */
    public function getCart($member_id, $site_id)
    {
        $field = 'ngc.cart_id, ngc.site_id, ngc.member_id, ngc.sku_id, ngc.num, ngs.sku_name,ngs.goods_id,
            ngs.sku_no, ngs.sku_spec_format,ngs.price,ngs.market_price, ngs.goods_spec_format,
            ngs.discount_price, ngs.promotion_type, ngs.start_time, ngs.end_time, ngs.stock, 
            ngs.sku_image, ngs.sku_images, ngs.goods_state, ngs.goods_stock_alarm, ngs.is_virtual, ngs.goods_name,
            ngs.virtual_indate, ngs.is_free_shipping, ngs.shipping_template, ngs.unit, ngs.introduction,ngs.sku_spec_format, ngs.keywords, ngs.max_buy, ngs.min_buy, ns.site_name, ngs.is_limit, ngs.limit_type';
        $alias = 'ngc';
        $join = [
            [
                'goods_sku ngs',
                'ngc.sku_id = ngs.sku_id',
                'inner'
            ],
            [
                'site ns',
                'ngc.site_id = ns.site_id',
                'inner'
            ],
        ];
        $list = model("goods_cart")->getList([ [ 'ngc.member_id', '=', $member_id ], [ 'ngc.site_id', '=', $site_id ], [ 'ngs.is_delete', '=', 0 ] ], $field, 'ngc.cart_id desc', $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取购物车数量
     * @param unknown $member_id
     */
    public function getCartCount($member_id)
    {
        $list = model("goods_cart")->getCount([ [ 'member_id', '=', $member_id ] ]);
        return $this->success($list);
    }

    /**
     * 获取购物车数量
     * @param $condition
     * @return array
     */
    public function getCartSum($condition, $field, $alias = 'a', $join = null)
    {
        $sum = model("goods_cart")->getSum($condition, $field, $alias, $join);
        return $this->success($sum);
    }

    public function getCartList($condition = [], $field = 'cart_id,site_id,member_id,sku_id,num', $order = 'cart_id desc')
    {
        $alias = 'gc';
        $join = [
            [
                'goods_sku gs',
                'gc.sku_id = gs.sku_id',
                'inner'
            ]
        ];

        $list = model("goods_cart")->getList($condition, $field, $order, $alias, $join);
        return $this->success($list);
    }

    /**
     * 修改购物车sku
     * @param $data
     */
    public function editCartSku($data)
    {
        $info = model("goods_cart")->getInfo([ [ 'cart_id', '=', $data[ 'cart_id' ] ], [ 'site_id', '=', $data[ 'site_id' ] ], [ 'member_id', '=', $data[ 'member_id' ] ] ], 'sku_id');
        if (empty($info)) return $this->error();

        if ($info[ 'sku_id' ] == $data[ 'sku_id' ]) {
            model("goods_cart")->update([ 'num' => $data[ 'num' ] ], [ [ 'cart_id', '=', $data[ 'cart_id' ] ] ]);
        } else {
            $cart_info = model("goods_cart")->getInfo([ [ 'sku_id', '=', $data[ 'sku_id' ] ], [ 'site_id', '=', $data[ 'site_id' ] ], [ 'member_id', '=', $data[ 'member_id' ] ] ], 'cart_id');
            if (!empty($cart_info)) {
                model("goods_cart")->delete([ [ 'cart_id', '=', $cart_info[ 'cart_id' ] ] ]);
            }
            model("goods_cart")->update([ 'num' => $data[ 'num' ], 'sku_id' => $data[ 'sku_id' ] ], [ [ 'cart_id', '=', $data[ 'cart_id' ] ] ]);
        }
        return $this->success();
    }
}