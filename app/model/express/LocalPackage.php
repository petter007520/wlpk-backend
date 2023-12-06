<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\express;

use app\model\BaseModel;
use app\model\member\Member;

/**
 * 外卖配送
 */
class LocalPackage extends BaseModel
{

    /**
     * 获取外卖配送包裹列表
     * @param $condition
     * @param string $field
     */
    public function getLocalDeliveryPackageList($condition, $field = '*')
    {
        $list = model('local_delivery_package')->getList($condition, $field);
        return $this->success($list);
    }

    /**
     * 获取包裹信息
     * @param $condition
     */
    public function package($condition)
    {
        $info = model('local_delivery_package')->getInfo($condition, '*');
        return $this->success($info);
    }


    /**
     * 外卖配送
     */
    public function delivery($param)
    {
        $order_id             = $param['order_id'] ?? 0;//订单id
        $order_goods_id_array = $param['order_goods_id_array'];
        $goods_id_array       = $param['goods_id_array'];
        $delivery_type        = $param['delivery_type'];//物流方式  1 物流配送  0 无需物流
        $delivery_no          = $param['delivery_no'] ?? '';//物流单号
        $member_id            = $param['member_id'];
        $site_id              = $param['site_id'];

        $member_model       = new Member();
        $member_info_result = $member_model->getMemberInfo([['member_id', '=', $member_id]], 'nickname');
        $member_info        = $member_info_result['data'] ?? [];
        //查询物流单号是否已存在,如果存在就合并入已存在的数据
        $condition = array(
            ['site_id', '=', $site_id],
            ['delivery_no', '=', $delivery_no],
            ['order_id', '=', $order_id],
            ['delivery_type', '=', $delivery_type],
            ['member_id', '=', $member_id]
        );
        $info      = model('local_delivery_package')->getInfo($condition, '*');
        if (empty($info)) {
            if ($delivery_type > 0) {
                $count        = model('local_delivery_package')->getCount([['site_id', '=', $site_id], ['order_id', '=', $order_id], ['delivery_type', '=', $delivery_type]]);
                $num          = $count + 1;
                $package_name = '包裹' . $num;
            } else {
                $package_name = '商家自配送';
            }
            $data   = array(
                'order_id'             => $order_id,
                'order_goods_id_array' => implode(',', $order_goods_id_array),
                'goods_id_array'       => implode(',', $goods_id_array),
                'delivery_no'          => $delivery_no,
                'site_id'              => $site_id,
                'member_id'            => $member_id,
                'member_name'          => $member_info['nickname'] ?? '',
                'delivery_type'        => $delivery_type,
                'package_name'         => $package_name,
                'delivery_time'        => time(),
                'deliverer'            => $param['deliverer'],
                'deliverer_mobile'     => $param['deliverer_mobile'],
            );
            $result = model('local_delivery_package')->add($data);
        } else {
            $temp_order_goods_id_arr = explode(',', $info['order_goods_id_array']);
            $temp_goods_id_arr       = explode(',', $info['goods_id_array']);

            $order_goods_id_array = implode(',', array_unique(array_merge($temp_order_goods_id_arr, $order_goods_id_array)));
            $goods_id_array       = implode(',', array_merge($temp_goods_id_arr, $goods_id_array));
            $data                 = array(
                'order_goods_id_array' => $order_goods_id_array,
                'goods_id_array'       => $goods_id_array,
            );
            $result               = model('local_delivery_package')->update($data, $condition);
        }
        return $this->success($result);
    }
}