<?php

namespace app\api\controller;

use app\model\goods\Goods as GoodsModel;
use app\model\goods\GoodsService;
use app\model\order\OrderCommon;
use app\model\system\Poster;
use app\model\goods\Config as GoodsConfigModel;
use app\model\web\Config as ConfigModel;

class Goods extends BaseApi
{

    /**
     * 修改商品点击量
     * @return string
     */
    public function modifyclicks()
    {
        $sku_id = isset($this->params[ 'sku_id' ]) ? $this->params[ 'sku_id' ] : 0;

        if (empty($sku_id)) {
            return $this->response($this->error('', 'REQUEST_SKU_ID'));
        }

        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $goods_model = new GoodsModel();
        $res = $goods_model->modifyClick($sku_id, $this->site_id);
        return $this->response($res);
    }

    /**
     * 获取商品海报
     */
    public function poster()
    {
        $this->checkToken();
        $promotion_type = 'null';
        $qrcode_param = json_decode($this->params[ 'qrcode_param' ], true);
        $qrcode_param[ 'source_member' ] = $this->member_id;
        $poster = new Poster();
        $res = $poster->goods($this->params[ 'app_type' ], $this->params[ 'page' ], $qrcode_param, $promotion_type, $this->site_id, $this->store_id);
        return $this->response($res);
    }

    /**
     * 售后保障
     * @return false|string
     */
    public function aftersale()
    {
        $goods_config_model = new GoodsConfigModel();
        $res = $goods_config_model->getAfterSaleConfig($this->site_id);
        return $this->response($res);
    }

    /**
     * 获取热门搜索关键词
     */
    public function hotSearchWords()
    {
        $config_model = new ConfigModel();
        $info = $config_model->getHotSearchWords($this->site_id, $this->app_module);
        return $this->response($this->success($info[ 'data' ][ 'value' ]));
    }

    /**
     * 获取默认搜索关键词
     */
    public function defaultSearchWords()
    {
        $config_model = new ConfigModel();
        $info = $config_model->getDefaultSearchWords($this->site_id, $this->app_module);
        return $this->response($this->success($info[ 'data' ][ 'value' ]));
    }

    /**
     * 商品服务
     * @return false|string
     */
    public function service()
    {
        $goods_service = new GoodsService();
        $data = $goods_service->getServiceList([ [ 'site_id', '=', $this->site_id ] ], 'service_name,desc,icon');
        foreach ($data['data'] as $key => $val){
            $data['data'][$key]['icon'] = json_decode($val['icon'], true);
        }

        return $this->response($data);
    }

    /**
     * 商品弹幕
     * @return false|string
     */
    public function goodsBarrage()
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;
        $order = new OrderCommon();
        $field = 'm.headimg as img, m.nickname as title';
        $join = [
            [
                'member m',
                'm.member_id=og.member_id',
                'left'
            ],
            [
                'order o',
                'o.order_id=og.order_id',
                'left'
            ]
        ];
        $data = $order->getOrderGoodsPageList([ [ 'og.site_id', '=', $this->site_id ], [ 'og.goods_id', '=', $goods_id ], [ 'o.pay_status', '=', 1 ] ], 1, 20, 'og.order_goods_id desc', $field, 'og', $join, 'o.member_id');
        return $this->response($data);
    }

    /**
     * 小程序分享图
     * @return false|string
     */
    public function shareImg()
    {
        $qrcode_param = json_decode($this->params[ 'qrcode_param' ] ?? '{}', true);

        $poster = new Poster();
        $res = $poster->shareImg($this->params[ 'page' ] ?? '', $qrcode_param, $this->site_id, $this->store_id);
        return $this->response($res);
    }
}