<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace app\model\shop;

use think\facade\Cache;
use app\model\BaseModel;

/**
 * 站点地址库
 */
class SiteAddress extends BaseModel
{
    /**
     * 添加店铺地址库
     * @param $data
     * @return array
     */
    public function addAddress($data)
    {
        model('site_address')->startTrans();
        try {

            if (empty($data['site_id']) || empty($data['contact_name']) || empty($data['mobile']) || empty($data['province_id']) || empty($data['city_id']) || empty($data['address']) || empty($data['full_address'])){
                return $this->error('','参数错误');
            }
            $data[ "update_time" ] = time();
            if($data['is_return_default'] == 1){
                model('site_address')->update([ 'is_return_default' => 0 ], [ [ 'site_id', '=', $data['site_id'] ] ]);
            }
            $res = model('site_address')->add($data);
            model('site_address')->commit();
            Cache::tag("site_address")->clear();
            return $this->success($res);
        }catch (\Exception $e){
            model('site_address')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 修改店铺地址库
     * @param $data
     * @param $condition
     * @return array
     */
    public function editAddress($data, $condition)
    {
        model('site_address')->startTrans();
        try {
            if (empty($data['contact_name']) || empty($data['mobile']) || empty($data['province_id']) || empty($data['city_id']) || empty($data['address']) || empty($data['full_address'])){
                return $this->error('','参数错误');
            }
            if($data['is_return_default'] == 1){
                $site_address_info = model('site_address')->getInfo($condition, 'site_id');
                model('site_address')->update([ 'is_return_default' => 0 ], [ [ 'site_id', '=', $site_address_info['site_id'] ] ]);
            }
            $res = model('site_address')->update($data, $condition);
            model('site_address')->commit();
            //修改对应店铺
            Cache::tag("site_address")->clear();
            return $this->success($res);
        }catch (\Exception $e){
            model('site_address')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 删除店铺地址库
     * @param $condition
     * @return array
     */
    public function deleteAddress($condition)
    {
        $res = model('site_address')->delete($condition);
        Cache::tag("site_address")->clear();
        return $this->success($res);
    }

    /**
     * 获取店铺地址库信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getAddressInfo($condition, $field = 'id, site_id, contact_name, mobile, postcode, province_id, city_id, district_id, community_id, address, full_address, is_return, is_return_default, is_delivery, update_time')
    {
        $data = json_encode([ $condition, $field ]);
        $cache = Cache::get("site_address_getAddressInfo_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $res = model('site_address')->getInfo($condition, $field);
        Cache::tag("site_address")->set("site_address_getAddressInfo_" . $data, $res);
        return $this->success($res);
    }

    /**
     * 获取店铺地址库列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getAddressList($condition = [], $field = 'id, site_id, contact_name, mobile, postcode, province_id, city_id, district_id, community_id, address, full_address, is_return, is_return_default, is_delivery, update_time', $order = '', $limit = null)
    {

        $data = json_encode([ $condition, $field, $order, $limit ]);
        $cache = Cache::get("site_address_getAddressList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('site_address')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("site_address")->set("site_address_getAddressList_" . $data, $list);

        return $this->success($list);
    }

    /**
     * 获取店铺地址库分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getAddressPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = 'id, site_id, contact_name, mobile, postcode, province_id, city_id, district_id, community_id, address, full_address, is_return, is_return_default, is_delivery, update_time')
    {
        $data = json_encode([ $condition, $field, $order, $page, $page_size ]);
        $cache = Cache::get("site_address_getAddressPageList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('site_address')->pageList($condition, $field, $order, $page, $page_size);
        Cache::tag("site_address")->set("site_address_getAddressPageList_" . $data, $list);
        return $this->success($list);
    }
}