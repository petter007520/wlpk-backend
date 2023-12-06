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
use app\model\storegoods\StoreGoods;
use think\facade\Db;

/**
 * 商品批量设置
 */
class Batch extends BaseModel
{

    /**
     * 批量设置
     * @param $params
     */
    public function setPrice($params)
    {
        $site_id = $params[ 'site_id' ];
        $type = $params[ 'type' ] ?? '';
        $price = $params[ 'price' ] ?? 0;
        $price_type = $params[ 'price_type' ] ?? '';
        $sign = $params[ 'sign' ] ?? '';
        $goods_ids = $params[ 'goods_ids' ] ?? '';
        $precise = $params[ 'precise' ] ?? '';

        $calculate_price_type = $params[ 'calculate_price_type' ] ?? '';
        $condition = array (
            [ 'site_id', '=', $site_id ],
            [ 'goods_id', 'in', $goods_ids ]
        );

        $price_field_data = array (
            'sale' => 'price',
            'cost' => 'cost_price',
            'market' => 'market_price'
        );
        $price_field = $price_field_data[ $price_type ] ?? '';

        $todo_price = 0;
        switch ( $type ) {
            case 'money'://固定金额
                //批量给商品设置价格
                $todo_price = $price;
                $update_data = array (
                    $price_field => $price
                );
                break;
            case 'calculate'://公式算法
                $calculate_price_field = $price_field_data[ $calculate_price_type ] ?? '';
                switch ( $sign ) {
                    case 'add'://加法
                        $sign_str = '+';
                        break;
                    case 'subtract'://减法
                        $sign_str = '-';
                        break;
                    case 'multiply'://乘法
                        $sign_str = '*';
                        break;
                    case 'division'://除法
                        $sign_str = '/';
                        break;
                }
                $calculate_str = $calculate_price_field . $sign_str . $price;
                switch ( $precise ) {
                    case 1://全部保留
                        $calculate_str = 'FLOOR((' . $calculate_str . ')*100)/100';
                        break;
                    case 2://抹分
                        $calculate_str = 'FLOOR((' . $calculate_str . ')*10)/10';
                        break;
                    case 3://抹角
                        $calculate_str = 'FLOOR(' . $calculate_str . ')';
                        break;
                    case 4://四舍五入到分
                        $calculate_str = 'ROUND(' . $calculate_str . ', 2)';
                        break;
                    case 5://四舍五入到角
                        $calculate_str = 'ROUND(' . $calculate_str . ', 1)';
                        break;
                    case 6://四舍五入到元
                        $calculate_str = 'ROUND(' . $calculate_str . ')';
                        break;
                }
                //todo  mysql的   round函数和floor函数
                $update_data[ $price_field ] = Db::raw($calculate_str);
                $todo_price = $calculate_str;
                break;
        }

        model('goods')->update($update_data, $condition);
        //只有销售价被改动如果存在就忽略设置discount_price才会收到影响
        if ($price_type == 'sale') {
            //鉴于限时折扣活动的复杂性(商品关联限时折扣活动表,如果存在就忽略设置discount_price)
            $sku_update_data[ 'discount_price' ] = Db::raw('if(discount_price = price,' . $todo_price . ',discount_price)');
            $sku_update_data[ $price_field ] = $update_data[ $price_field ];
        }
        model('goods_sku')->update($sku_update_data, $condition);
        //如果销售价被改动,默认门店的价格也都会被改动
        if(in_array($price_type, ['sale', 'cost'])){
            $store_goods_model = new StoreGoods();
            $store_goods_model->syncGoodsData(['update_data' => $update_data, 'condition' => $condition, 'site_id' => $site_id]);
        }
        return $this->success($update_data);
    }
}