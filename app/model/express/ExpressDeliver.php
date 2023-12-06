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


/**
 * 配送员信息
 */
class ExpressDeliver extends BaseModel
{

    /**
     * 获取配送员分页列表
     * @param $condition
     * @param $field
     * @param $order
     * @param $page
     * @param $page_size
     * @return array
     */
    public function getDeliverPageLists($condition, $field, $order, $page, $page_size)
    {
        $list = model('express_deliver')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取配送员列表
     * @param $condition
     * @param string $field
     */
    public function getDeliverLists($condition, $field = '*')
    {
        $list = model('express_deliver')->getList($condition, $field);
        return $this->success($list);
    }

    /**
     * 添加配送员
     * @param $data
     * @param string
     */
    public function addDeliver($data)
    {
        if (empty($data[ 'deliver_name' ])) {
            return $this->error('', '配送员姓名不能为空!');
        }

        if (empty($data[ 'deliver_mobile' ])) {
            return $this->error('', '配送员手机号不能为空!');
        }
        $data[ 'create_time' ] = time();
        $result = model('express_deliver')->add($data);
        return $this->success($result);
    }

    /**
     * 编辑配送员
     * @param $data
     * @param string
     */
    public function editDeliver($data, $deliver_id)
    {
        if (empty($data[ 'deliver_name' ])) {
            return $this->error('', '配送员姓名不能为空!');
        }

        if (empty($data[ 'deliver_mobile' ])) {
            return $this->error('', '配送员手机号不能为空!');
        }
        $data[ 'modify_time' ] = time();
        $condition = [
            [ 'deliver_id', '=', $deliver_id ],
            [ 'site_id', '=', $data[ 'site_id' ] ]
        ];
        if(isset($data['store_id'])){
            $condition[] = ['store_id', '=', $data['store_id']];
        }
        $result = model('express_deliver')->update($data, $condition);
        return $this->success($result);
    }

    /**
     * 删除配送员
     * @param $data
     * @param string
     */
    public function deleteDeliver($deliver_id, $site_id, $store_id = 0)
    {
        $condition = [
            [ 'deliver_id', 'in', $deliver_id ],
            [ 'site_id', '=', $site_id ]
        ];
        if($store_id > 0){
            $condition[] = ['store_id', '=', $store_id];
        }
        $result = model('express_deliver')->delete($condition);
        return $this->success($result);
    }

    /**
     * 配送员信息
     * @param $data
     * @param string
     */
    public function getDeliverInfo($deliver_id, $site_id, $store_id = 0)
    {
        $condition = [
            [ 'deliver_id', '=', $deliver_id ],
            [ 'site_id', '=', $site_id ]
        ];
        if($store_id > 0){
            $condition[] = ['store_id', '=', $store_id];
        }
        $info = model('express_deliver')->getInfo($condition, 'deliver_name,deliver_mobile,create_time,modify_time,deliver_id');
        return $this->success($info);
    }

}