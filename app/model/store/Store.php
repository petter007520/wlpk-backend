<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\store;

use app\model\BaseModel;
use app\model\express\Config;
use think\facade\Db;
use app\model\upload\Upload;

/**
 * 门店管理
 */
class Store extends BaseModel
{

    /**
     * 添加门店
     * @param $data
     * @param array $user_data
     * @param int $is_store
     * @return array
     */
    public function addStore($data, $user_data = [], $is_store = 0)
    {

        $site_id = isset($data[ 'site_id' ]) ? $data[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        if (empty($data[ 'longitude' ]) || empty($data[ 'latitude' ])) {
            return $this->error('', '门店经纬度不能为空');
        }

        $data[ 'create_time' ] = time();
        model('store')->startTrans();

        try {
            $store_id = model('store')->add($data);
            if ($is_store == 1 && isset($user_data[ 'uid' ]) && !empty($user_data[ 'uid' ])) {
                // 添加门店管理员
                $group_id = model('cashier_auth_group')->getValue([ [ 'site_id', '=', $site_id ], [ 'keyword', '=', 'admin' ] ], 'group_id');
                model('user_group')->add([
                    'uid' => $user_data[ 'uid' ],
                    'site_id' => $site_id,
                    'store_id' => $store_id,
                    'group_id' => $group_id,
                    'create_time' => time(),
                    'app_module' => 'store'
                ]);
            }
            //执行事件
            event("AddStore", [ 'store_id' => $store_id, 'site_id' => $data[ 'site_id' ] ]);

            model('store')->commit();
            return $this->success($store_id);
        } catch (\Exception $e) {

            model('store')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 修改门店
     * @param $data
     * @param $condition
     * @param array $user_data
     * @param int $is_exit
     * @param int $user_type
     * @return array
     */
    public function editStore($data, $condition, $user_data = [], $is_exit = 0, $user_type = 1)
    {
        if (( isset($data[ 'longitude' ]) && empty($data[ 'longitude' ]) ) || ( isset($data[ 'latitude' ]) && empty($data[ 'latitude' ]) )) {
            return $this->error('', '门店经纬度不能为空');
        }
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        $store_id = isset($check_condition[ 'store_id' ]) ? $check_condition[ 'store_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $data[ "modify_time" ] = time();

        model('store')->startTrans();
        try {
            $store_info = model('store')->getInfo($condition);
            if ($store_info[ 'store_image' ] && !empty($data[ 'store_image' ]) && $store_info[ 'store_image' ] != $data[ 'store_image' ]) {
                $upload_model = new Upload();
                $upload_model->deletePic($store_info[ 'store_image' ], $site_id);
            }

            model('store')->update($data, $condition);

            //可能会关闭门店自提方式
            $this->checkCloseStoreTrade($site_id);
            model('store')->commit();
            return $this->success($store_id);
        } catch (\Exception $e) {
            model('store')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 删除门店
     * @param array $condition
     */
    public function deleteStore($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        $store_id = isset($check_condition[ 'store_id' ]) ? $check_condition[ 'store_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        $store_info = model('store')->getInfo([ [ 'store_id', '=', $store_id ] ], 'uid, store_image');
        if (!empty($store_info[ 'store_image' ])) {
            $upload_model = new Upload();
            $upload_model->deletePic($store_info[ 'store_image' ], $site_id);
        }
        $res = model('store')->delete($condition);
        if ($res) {
            model('store_goods')->delete([ [ 'store_id', '=', $store_id ] ]);
            model('store_goods_sku')->delete([ [ 'store_id', '=', $store_id ] ]);
            model('store_member')->delete([ [ 'store_id', '=', $store_id ] ]);
            model('store_settlement')->delete([ [ 'store_id', '=', $store_id ], [ 'site_id', '=', $site_id ] ]);
            model('user')->delete([ [ 'app_module', '=', 'store' ], [ 'site_id', '=', $site_id ], [ 'uid', '=', $store_info[ 'uid' ] ] ]);
            model('site_diy_view')->delete([ [ 'name', '=', 'DIY_STORE_' . $store_id ], [ 'site_id', '=', $site_id ] ]);
        }
        //可能会关闭门店自提方式
        $this->checkCloseStoreTrade($site_id);
        return $this->success($res);
    }

    /**
     * 获取门店数量
     * @param $where
     * @param $field
     * @return array
     */
    public function getStoreCount($where, $field = 'store_id')
    {
        $res = model('store')->getCount($where, $field);
        return $this->success($res);
    }

    /**
     * 获取门店字段和
     * @param $where
     * @param $field
     * @return array
     */
    public function getStoreSum($where, $field)
    {
        $res = model('store')->getSum($where, $field);
        return $this->success($res);
    }

    /**
     * @param $condition
     * @param $is_frozen
     */
    public function frozenStore($condition, $is_frozen)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $res = model('store')->update([ 'is_frozen' => $is_frozen == 1 ? 0 : 1 ], $condition);
        //可能会关闭门店自提方式
        $this->checkCloseStoreTrade($site_id);
        return $this->success($res);
    }

    /**
     * 重置密码
     * @param string $password
     * @param $condition
     * @return array
     */
    public function resetStorePassword($password = '123456', $condition = [])
    {
        //获取用户id
        $uid = model('store')->getValue($condition, 'uid');
        if ($uid) {
            $res = model('user')->update([
                'password' => data_md5($password)
            ], [ [ 'uid', '=', $uid ] ]);
        } else {
            $res = 1;
        }
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 获取门店信息
     * @param array $condition
     * @param string $field
     */
    public function getStoreInfo($condition, $field = '*')
    {
        $res = model('store')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取门店详情
     * @param $condition
     */
    public function getStoreDetail($condition)
    {
        $res = model('store')->getInfo($condition, '*');
        if (!empty($res)) {
            if (!empty($res[ 'time_week' ])) {
                $res[ 'time_week' ] = explode(',', $res[ 'time_week' ]);
            }
            if (empty($res[ 'delivery_time' ])) {
                $res[ 'delivery_time' ] = [
                    [ 'start_time' => $res[ 'start_time' ], 'end_time' => $res[ 'end_time' ] ]
                ];
            } else {
                $res[ 'delivery_time' ] = json_decode($res[ 'delivery_time' ], true);
            }
        }
        return $this->success($res);
    }

    /**
     * 获取门店列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getStoreList($condition = [], $field = '*', $order = '', $limit = null)
    {

        $list = model('store')->getList($condition, $field, $order, '', '', '', $limit);

        return $this->success($list);
    }

    /**
     * 获取门店分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getStorePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = model('store')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }


    /**
     * 查询门店  带有距离
     * @param $condition
     * @param $lnglat
     */
    public function getLocationStoreList($condition, $field, $lnglat)
    {
        $order = '';
        if (!empty($lnglat[ 'lat' ]) && !empty($lnglat[ 'lng' ])) {
            $field .= ' , ROUND(st_distance ( point ( ' . $lnglat[ 'lng' ] . ', ' . $lnglat[ 'lat' ] . ' ), point ( longitude, latitude ) ) * 111195 / 1000, 2) as distance ';
            $condition[] = [ '', 'exp', Db::raw(' FORMAT(st_distance ( point ( ' . $lnglat[ 'lng' ] . ', ' . $lnglat[ 'lat' ] . ' ), point ( longitude, latitude ) ) * 111195 / 1000, 2) < 10000') ];
            $order = 'distance asc';
        }
        $list = model('store')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 查询门店  带有距离
     * @param $condition
     * @param $lnglat
     */
    public function getLocationStorePageList($condition, $page, $page_size, $field, $lnglat)
    {
        $order = '';
        if (!empty($lnglat[ 'lat' ]) && !empty($lnglat[ 'lng' ])) {
            $field .= ',FORMAT(st_distance ( point ( ' . $lnglat[ 'lng' ] . ', ' . $lnglat[ 'lat' ] . ' ), point ( longitude, latitude ) ) * 111195 / 1000, 2) as distance';
            $condition[] = [ '', 'exp', Db::raw(' FORMAT(st_distance ( point ( ' . $lnglat[ 'lng' ] . ', ' . $lnglat[ 'lat' ] . ' ), point ( longitude, latitude ) ) * 111195 / 1000, 2) < 10000') ];
            $order = Db::raw(' st_distance ( point ( ' . $lnglat[ 'lng' ] . ', ' . $lnglat[ 'lat' ] . ' ), point ( longitude, latitude ) ) * 111195 / 1000 asc');
        }
        $list = model('store')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }


    /**
     * 核验是否可以关闭门店自提
     * @param $site_id
     */
    public function checkCloseStoreTrade($site_id)
    {
        $count = model('store')->getCount([ [ 'site_id', '=', $site_id ], [ 'is_pickup', '=', 1 ], [ 'status', '=', 1 ], [ 'is_frozen', '=', 0 ] ]);
        if ($count == 0) {
            //站点的所有门店都被删除后,门店开关也会被关闭
            $config_model = new Config();
            $config_model->setStoreIsuse(0, $site_id);
        }
        return $this->success();
    }

    /**
     * 核验是否可以开启门店自提
     * @param $site_id
     */
    public function checkIscanStoreTrade($site_id)
    {
        $count = model('store')->getCount([ [ 'site_id', '=', $site_id ], [ 'is_pickup', '=', 1 ], [ 'status', '=', 1 ], [ 'is_frozen', '=', 0 ] ]);
        if ($count == 0) {
            return $this->error('', '需至少存在一个营业中且开启自提业务的门店,才能开启门店自提开关');
        } else {
            return $this->success();
        }
    }

    /**
     * 获取门店名称(鉴于调用场景过多,封装一个只返回门店名称的函数,todo  做缓存)
     * @param $condition
     */
    public function getStoreName($condition)
    {
        $name = model('store')->getValue($condition, 'store_name');
        return $this->success($name);
    }

    /**
     * 获取默认门店
     * @param int $site_id 只有单商户这么写
     * @param string $field
     * @return array
     */
    public function getDefaultStore($site_id = 0, $field = '*')
    {
        $condition = array (
            [ 'is_default', '=', 1 ]
        );
        if ($site_id > 0) {
            $condition[] = [ 'site_id', '=', $site_id ];
        }

        $info = model('store')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 填写店铺默认门店
     * @param $params
     */
    public function addDefaultStore($params)
    {
        $site_id = $params[ 'site_id' ] ?? 1;
        $data = array (
            'site_id' => $site_id,
            'store_name' => '默认门店',
            'is_default' => 1,
            'create_time' => time()
        );
        $res = model('store')->add($data);
        return $this->success();
    }

    /**
     * 获取门店类型
     * @param string $type
     * @return array
     */
    public function getStoreType($type = '')
    {
        $store_type = [
            'directsale' => [
                'type' => 'directsale',
                'name' => '直营店'
            ],
            'franchise' => [
                'type' => 'franchise',
                'name' => '加盟店'
            ]
        ];
        return $type ? $store_type[ $type ] : $store_type;
    }

    /**
     * 获取扣除库存门店
     * @param $params
     * @return array
     */
    public function getStoreStockTypeStoreId($params)
    {
        $store_id = $params[ 'store_id' ];
        $store_condition = array (
            [ 'store_id', '=', $store_id ]
        );
        $store_info = $this->getStoreInfo($store_condition)[ 'data' ] ?? [];
        if (empty($store_info)) {
            return $this->error();
        }
        $stock_type = $store_info[ 'stock_type' ];

        if ($stock_type == 'all') {
            $default_store_info = $this->getDefaultStore()[ 'data' ] ?? [];
            $store_id = $default_store_info[ 'store_id' ];
        }
        return $this->success($store_id);
    }
}