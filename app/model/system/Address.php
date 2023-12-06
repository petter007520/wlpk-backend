<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\system;

use extend\api\HttpClient;
use think\facade\Cache;
use app\model\BaseModel;
use think\facade\Db;

/**
 * 地区表
 */
class Address extends BaseModel
{
    /**
     * 获取地区列表
     * @param unknown $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     * @return multitype:string mixed
     */
    public function getAreaList($condition = [], $field = '*', $order = '', $limit = null)
    {

        $data  = json_encode([$condition, $field, $order, $limit]);
        $cache = Cache::get("area_getAreaList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $area_list = model("area")->getList($condition, $field, $order, $limit);
        Cache::tag("area")->set("area_getAreaList_" . $data, $area_list);
        return $this->success($area_list);
    }

    /**
     * 获取地区详情
     */
    public function getAreaInfo($circle)
    {

        $cache = Cache::get("area_getAreaInfo_" . $circle);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $info = model("area")->getInfo([['id', '=', $circle]]);
        Cache::tag("area")->set("area_getAreaInfo_" . $circle, $info);
        return $this->success($info);
    }

    /**
     * 获取地区数量
     * @param $condition
     * @return array
     */
    public function getAreaCount($condition){
        $count = model("area")->getCount($condition);
        return $this->success($count);
    }

    /**
     * 获取省市子项
     */
    public function getAreas($circle = 0)
    {

        $cache = Cache::get("area_getAreas_" . $circle);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model("area")->getList([['pid', '=', $circle]]);
        Cache::tag("area")->set("area_getAreas_" . $circle, $list);
        return $this->success($list);
    }

    /**
     * 获取整理后的地址
     */
    public function getAddressTree($level = 4)
    {
        $condition      = [['level', '<=', $level]];
        $json_condition = json_encode($condition);
        $cache          = Cache::get("area_getAddressTree" . $json_condition);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $area_list = $this->getAreaList($condition, "id, pid, name, level", "id asc")['data'];
        //组装数据
        $refer_list = [];
        foreach ($area_list as $key => $val) {
            $refer_list[$val['level']][$val['pid']]['child_list'][$val['id']] = $area_list[$key];
            if (isset($refer_list[$val['level']][$val['pid']]['child_num'])) {
                $refer_list[$val['level']][$val['pid']]['child_num'] += 1;
            } else {
                $refer_list[$val['level']][$val['pid']]['child_num'] = 1;
            }
        }
        Cache::tag("area")->set("area_getAddressTree" . $json_condition, $refer_list);
        return $this->success($refer_list);
    }

    /**
     * 获取地址树结构
     * @param $level
     * @return array
     */
    public function getAddressTreeList($level){
        $condition      = [['level', '<=', $level]];
        $json_condition = json_encode($condition);
        $cache          = Cache::get("area_getAddressTreeList" . $json_condition);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $area_list = $this->getAreaList($condition, "id, pid, name", "id asc")['data'];
        $tree = $this->toTree($area_list);
        Cache::tag("area")->set("area_getAddressTreeList" . $json_condition, $tree);
        return $this->success($tree);
    }

    /**
     * 列表转树结构
     * @param $array
     * @param  int  $pid
     * @return array
     */
    public function toTree($array, $pid = 0){
        $tree = array();
        foreach ($array as $key => $value) {
            if ($value['pid'] == $pid) {
                $value['children'] = $this->toTree($array, $value['id']);
                $tree[] = $value;
            }
        }
        return $tree;
    }

    /**
     * 获取地址
     * @param array $condition
     * @param string $field
     * @return multitype:number unknown
     */
    public function getAreasInfo(array $condition, string $field = '*')
    {
        $info = model("area")->getInfo($condition, $field);
        if ($info) return $this->success($info);
        return $this->error();
    }


    /**
     * 通过地址查询
     */
    public function getAddressByLatlng($post_data)
    {
        $post_url = 'https://apis.map.qq.com/ws/geocoder/v1/';
        $config_model = new \app\model\web\Config();
        $config_result = $config_model->getMapConfig()['data'] ?? [];
        $config = $config_result['value'] ?? [];
        $tencent_map_key = $config['tencent_map_key'] ?? '';
        $post_data = array(
            'location' => $post_data['latlng'],
            'key' => $tencent_map_key,
            'get_poi' => 0,//是否返回周边POI列表：1.返回；0不返回(默认)
        );

        $httpClient = new HttpClient();
        $res = $httpClient->post($post_url, $post_data);
        $res = json_decode($res, true);
        if($res['status'] == 0){
            $return_array = $res['result']['address_component'] ?? [];
            $return_data = array(
                'province' => $return_array['province'] ?? '',
                'city' => $return_array['city'] ?? '',
                'district' => $return_array['district'] ?? '',
                'address' => $return_array['street_number'] ?? '',
                'full_address' => $res['result']['address'] ?? ''
            );
            return $this->success($return_data);
        }else{
            return $this->error([], $res['message']);
        }
    }

    /**
     * 通过地址查询
     */
    public function getAddressByName($address)
    {
        $post_url = 'https://apis.map.qq.com/ws/geocoder/v1/';
        $config_model = new \app\model\web\Config();
        $config_result = $config_model->getMapConfig()['data'] ?? [];
        $config = $config_result['value'] ?? [];
        $tencent_map_key = $config['tencent_map_key'] ?? '';
        $post_data = array(
            'address' => $address,
            'key' => $tencent_map_key,
        );

        $httpClient = new HttpClient();
        $res = $httpClient->post($post_url, $post_data);
        $res = json_decode($res, true);
        if($res['status'] == 0){
            $return_array = $res['result']['location'] ?? [];
            $return_data = array(
                'longitude' => $return_array['lng'] ?? '',
                'latitude' => $return_array['lat'] ?? '',
            );
            return $this->success($return_data);
        }else{
            return $this->error([], $res['message']);
        }
    }

    /**
     * 编辑地区
     * @param $data
     * @return array
     */
    public function saveArea($data){
        $count = model('area')->getCount([ ['id', '=', $data['id'] ] ]);
        if ($count) {
            unset($data['id']);
            $res = model('area')->update($data, [ ['id', '=', $data['id'] ] ]);
        } else {
            $res =  model('area')->add($data);
        }
        if ($res) {
            Cache::clear("area");
            return $this->success($res);
        }
        return $this->error();
    }

    /**
     * 删除地区
     * @param $condition
     * @return array
     */
    public function deleteArae($id, $level){
        switch ((int)$level) {
            case 1:
                $child = model('area')->getColumn([ ['pid', '=', $id] ], 'id');
                if (empty($child)) {
                    $condition = [ ['id', '=', $id], ['level', '=', $level] ];
                } else {
                    $child = implode(',', $child);
                    $condition = [ ['', 'exp', Db::raw("(id = $id AND level = $level) OR (id in ($child) AND level = 2) OR (pid in ($child) AND level = 3)") ]];
                }
                break;
            case 2:
                $condition = [ ['', 'exp', Db::raw("(id = $id AND level = 2) OR (pid = $id AND level = 3)") ]];
                break;
            case 3:
                $condition = [ ['id', '=', $id], ['level', '=', $level] ];
                break;
        }
        $res = model('area')->delete($condition);
        if ($res) {
            Cache::clear("area");
            return $this->success($res);
        }
        return $this->error();
    }
}
