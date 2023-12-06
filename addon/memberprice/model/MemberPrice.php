<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\memberprice\model;

use app\model\BaseModel;

class MemberPrice extends BaseModel
{

    /**
     * @param $condition
     * @param $data
     * @param $member_price
     * @return array
     */
    public function editGoodsMemberPrice($condition, $data, $member_price)
    {

        model('goods')->startTrans();
        try {

            model('goods')->update($data, $condition);
            if ($data[ 'discount_config' ] == 1) {
                foreach ($member_price as $k => $v) {
                    $sku_condition = $condition;
                    $data[ 'member_price' ] = json_encode($v);
                    $sku_condition[] = [ 'sku_id', '=', $k ];
                    model('goods_sku')->update($data, $sku_condition);
                }

            } else {
                $data[ 'member_price' ] = '';
                model('goods_sku')->update($data, $condition);
            }
            model('goods')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('goods')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

}