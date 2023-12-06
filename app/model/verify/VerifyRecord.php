<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\verify;

use app\model\BaseModel;

/**
 * 核销记录管理
 */
class VerifyRecord extends BaseModel
{
    /**
     * 新增核销记录
     * @param $data
     * @return array
     */
    public function addVerifyRecord($data)
    {
//        $data = [
//            'site_id' => $member_verify_info[ 'site_id' ],
//            'verify_code' => $member_verify_info[ 'verify_code' ],
//            'verifier_id' => $verifyer_id,
//            'verifier_name' => $verifyer_name,
//            'create_time' => time()
//        ];
        $res = model('verify_record')->add($data);
        return $this->success($res);
    }

    /**
     * 核销码核销记录
     */
    public function getVerifyRecordsList($condition = [], $field = '*', $order = '', $alias = 'a', $join = [], $limit = null)
    {
        $list = model('verify_record')->getList($condition, $field, $order, $alias, $join, '', $limit);
        return $this->success($list);
    }

    /**
     * 核销码核销记录
     */
    public function getVerifyRecordsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $field = '*', $order = '', $alias = 'a', $join = [], $limit = null)
    {
        $list = model('verify_record')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 核销码核销记录
     */
    public function getVerifyRecordsViewList($condition = [], $field = '*', $order = '', $alias = 'a', $join = [], $limit = null)
    {
        $list = model('verify_record')->getList($condition, $field, $order, $alias, $join, '', $limit);
        return $this->success($list);
    }

    /**
     * 核销码核销记录
     */
    public function getVerifyRecordsViewPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $field = '*', $order = '', $alias = 'a', $join = [], $limit = null)
    {
        $alias = 'vr';
        $join = array (
            [ 'verify v', 'v.verify_code = vr.verify_code', 'left' ],
            [ 'store s', 's.store_id = vr.store_id', 'left' ]
        );
        $order = 'vr.verify_time desc';
        $field = 'v.*,vr.*,s.store_name';

        $list = model('verify_record')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        $verify_model = new Verify();
        foreach ($list[ "list" ] as $k => $v) {
            $temp = json_decode($v[ 'verify_content_json' ], true);
            $list[ "list" ][ $k ][ "item_array" ] = $temp[ "item_array" ];
            $list[ "list" ][ $k ][ "remark_array" ] = $temp[ "remark_array" ];
            if ($v['verify_type'] == 'pickup' || $v['verify_type'] == 'virtualgoods') {
                $list[ "list" ][ $k ][ 'order_no' ] = $temp[ 'remark_array' ][ 1 ][ 'value' ];
                $order_info = model('order')->getInfo([ [ 'order_no', '=', $temp[ 'remark_array' ][ 1 ][ 'value' ] ] ], 'order_id,member_id,name,order_name');
                $list[ 'list' ][ $k ][ 'order_info' ] = $order_info;
                $list[ 'list' ][ $k ][ 'name' ] = model('member')->getValue([ [ 'member_id', '=', $list[ 'list' ][ $k ][ 'order_info' ][ 'member_id' ] ] ], 'username');
                $list[ 'list' ][ $k ][ 'sku_image' ] = "";
                $order_goods_info = model("order_goods")->getInfo([ [ 'order_id', '=', $order_info[ 'order_id' ] ] ], "sku_image");
                $list[ 'list' ][ $k ][ 'sku_image' ] = $order_goods_info[ 'sku_image' ];
            } else {
                $list[ 'list' ][ $k ][ 'sku_image' ] = $temp[ "item_array" ][0]['img'];
                $list[ 'list' ][ $k ][ 'name' ] = $temp[ "item_array" ][0]['name'];
            }
            unset($list[ "list" ][ $k ][ "verify_content_json" ]);
            $list[ 'list' ][ $k ][ 'verifyFrom' ] = $verify_model->verifyFrom[ $v[ 'verify_from' ] ];
        }
        return $this->success($list);
    }

    /**
     * 获取核销记录信息
     */
    public function getVerifyRecordsInfo($condition, $field = '*', $alias = 'a', $join = null, $data = null)
    {
        $info = model('verify_record')->getInfo($condition, $field, $alias, $join, $data);
        return $this->success($info);
    }

}