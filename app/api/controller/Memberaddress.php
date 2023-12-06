<?php
/**
 * Index.php
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 * @author : niuteam
 * @date : 2022.8.8
 * @version : v5.0.0.1
 */

namespace app\api\controller;

use app\model\member\MemberAddress as MemberAddressModel;
use app\model\system\Address;
use app\model\express\Local;

class Memberaddress extends BaseApi
{
    /**
     * 添加信息
     */
    public function add()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $this->params[ 'name' ] = preg_replace("/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u", "", $this->params[ 'name' ]);

        $this->params[ 'name' ] = preg_replace_callback('/./u',
            function(array $match) {
                return strlen($match[ 0 ]) >= 4 ? '' : $match[ 0 ];
            },
            $this->params[ 'name' ]);
        $data = [
            'site_id' => $this->site_id,
            'member_id' => $token[ 'data' ][ 'member_id' ],
            'name' => $this->params[ 'name' ],
            'mobile' => $this->params[ 'mobile' ],
            'telephone' => $this->params[ 'telephone' ],
            'province_id' => $this->params[ 'province_id' ],
            'city_id' => $this->params[ 'city_id' ],
            'district_id' => $this->params[ 'district_id' ],
            'community_id' => $this->params[ 'community_id' ],
            'address' => $this->params[ 'address' ],
            'full_address' => $this->params[ 'full_address' ],
            'longitude' => $this->params[ 'longitude' ],
            'latitude' => $this->params[ 'latitude' ],
            'is_default' => $this->params[ 'is_default' ]
        ];
        $member_address = new MemberAddressModel();
        $res = $member_address->addMemberAddress($data);
        return $this->response($res);
    }

    /**
     * 编辑信息
     */
    public function edit()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $data = [
            'site_id' => $this->site_id,
            'id' => $this->params[ 'id' ],
            'member_id' => $token[ 'data' ][ 'member_id' ],
            'name' => $this->params[ 'name' ],
            'mobile' => $this->params[ 'mobile' ],
            'telephone' => $this->params[ 'telephone' ],
            'province_id' => $this->params[ 'province_id' ],
            'city_id' => $this->params[ 'city_id' ] != 'undefined' ? $this->params[ 'city_id' ] : '',
            'district_id' => $this->params[ 'district_id' ] != 'undefined' ? $this->params[ 'district_id' ] : '',
            'community_id' => $this->params[ 'community_id' ],
            'address' => $this->params[ 'address' ],
            'full_address' => $this->params[ 'full_address' ],
            'longitude' => $this->params[ 'longitude' ],
            'latitude' => $this->params[ 'latitude' ],
            'is_default' => $this->params[ 'is_default' ]
        ];
        $member_address = new MemberAddressModel();
        $res = $member_address->editMemberAddress($data);
        return $this->response($res);
    }

    /**
     * 设置默认地址
     * @return string
     */
    public function setdefault()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $id = isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : 0;
        if (empty($id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }
        $member_address = new MemberAddressModel();
        $res = $member_address->setMemberDefaultAddress($id, $token[ 'data' ][ 'member_id' ]);
        return $this->response($res);
    }

    /**
     * 删除信息
     */
    public function delete()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $id = isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : 0;
        if (empty($id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }

        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'id', '=', $id ],
            [ 'member_id', '=', $token[ 'data' ][ 'member_id' ] ]
        ];
        $member_address = new MemberAddressModel();
        $res = $member_address->deleteMemberAddress($condition);
        return $this->response($res);
    }

    /**
     * 基础信息
     */
    public function info()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $id = isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : 0;
        if (empty($id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }

        $default = isset($this->params[ 'default' ]) ? $this->params[ 'default' ] : 0;
        if ($default) {
            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'is_default', '=', 1 ],
                [ 'member_id', '=', $token[ 'data' ][ 'member_id' ] ],
            ];
        } else {
            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'id', '=', $id ],
                [ 'member_id', '=', $token[ 'data' ][ 'member_id' ] ],
            ];
        }

        $member_address = new MemberAddressModel();
        $res = $member_address->getMemberAddressInfo($condition, 'id, member_id, name, mobile, telephone, province_id, district_id, city_id, community_id, address, full_address, longitude, latitude, is_default, type');
        return $this->response($res);
    }

    /**
     * 分页列表信息
     */
    public function page()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $type = isset($this->params[ 'type' ]) ? $this->params[ 'type' ] : '';
        $store_id = $this->params['store_id'] ?? 0;
        $member_address = new MemberAddressModel();
        $condition = [
            [ 'member_id', '=', $token[ 'data' ][ 'member_id' ] ],
            [ 'site_id', '=', $this->site_id ]
        ];
        if (!empty($type)) {
            $condition[] = [ 'type', '=', $type ];
        }
        $field = 'id,member_id, site_id, name, mobile, telephone,province_id,city_id,district_id,community_id,address,full_address,longitude,latitude,is_default,type';
        $list = $member_address->getMemberAddressPageList($condition, $page, $page_size, 'is_default desc,id desc', $field);
        //同城配送验证是否可用
        if ($type == 2) {
            $local = new Local();
            foreach ($list[ 'data' ][ 'list' ] as $k => $v) {
                $v['store_id'] = $store_id;
                $local_res = $local->isSupportDelivery($v);
                if ($local_res[ 'code' ] < 0) {
                    $list[ 'data' ][ 'list' ][ $k ][ 'local_data' ] = $local_res[ 'message' ];
                }
            }
        }

        return $this->response($list);
    }

    /**
     * 添加第三方收货地址
     */
    public function addThreeParties()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $address = new Address();
        //省查询
        $province_info = $address->getAreasInfo([
            [ 'level', '=', 1 ],
            [ 'name', 'like', '%' . $this->params[ 'province' ] . '%' ],
        ], 'id');
        if ($province_info[ 'code' ] < 0) return $this->response(error('', '地址库中未获取到' . $this->params[ 'province' ] . '的信息'));
        //市查询
        $city_info = $address->getAreasInfo([
            [ 'level', '=', 2 ],
            [ 'pid', '=', $province_info[ 'data' ][ 'id' ] ],
            [ 'name', 'like', '%' . $this->params[ 'city' ] . '%' ],
        ], 'id');
        if ($city_info[ 'code' ] < 0) return $this->response(error('', '地址库中未获取到' . $this->params[ 'city' ] . '的信息'));
        //区县查询
        $district_info = $address->getAreasInfo([
            [ 'level', '=', 3 ],
            [ 'pid', '=', $city_info[ 'data' ][ 'id' ] ],
            [ 'name', 'like', '%' . $this->params[ 'district' ] . '%' ],
        ], 'id');
        if ($district_info[ 'code' ] < 0) return $this->response(error('', '地址库中未获取到' . $this->params[ 'district' ] . '的信息'));

        $data = [
            'site_id' => $this->site_id,
            'member_id' => $token[ 'data' ][ 'member_id' ],
            'name' => $this->params[ 'name' ],
            'mobile' => $this->params[ 'mobile' ],
            'telephone' => $this->params[ 'telephone' ] ?? '',
            'province_id' => $province_info[ 'data' ][ 'id' ],
            'city_id' => $city_info[ 'data' ][ 'id' ],
            'district_id' => $district_info[ 'data' ][ 'id' ],
            'community_id' => $this->params[ 'community_id' ] ?? 0,
            'address' => $this->params[ 'address' ],
            'full_address' => $this->params[ 'full_address' ],
            'longitude' => $this->params[ 'longitude' ] ?? '',
            'latitude' => $this->params[ 'latitude' ] ?? '',
            'is_default' => $this->params[ 'is_default' ] ?? 0
        ];
        $member_address = new MemberAddressModel();
        $res = $member_address->addMemberAddress($data);
        return $this->response($res);
    }

    /**
     * 转化省市区地址形式为实际的省市区id
     */
    public function tranAddressInfo()
    {
        $latlng = $this->params[ 'latlng' ] ?? '';

        $address_model = new Address();
        $address_result = $address_model->getAddressByLatlng([ 'latlng' => $latlng ]);
        if ($address_result[ 'code' ] < 0)
            return $this->response($address_result);

        $address_data = $address_result[ 'data' ];
//        $province = $address_data['province'] ?? '';
        $province = "";
        if ($address_data[ 'province' ]) {
            $province = str_replace("省", "", $address_data[ 'province' ]);
            $province = str_replace("市", "", $province);
        }
        $city = $address_data[ 'city' ] ?? '';
        $district = $address_data[ 'district' ] ?? '';
        $province_id = $address_model->getAreasInfo([ [ 'name', 'like', '%' . $province . '%' ], [ 'level', '=', 1 ] ], 'id')[ 'data' ][ 'id' ] ?? 0;

        if ($province_id > 0)
            $city_id = $address_model->getAreasInfo([ [ 'name', 'like', '%' . $city . '%' ], [ 'level', '=', 2 ], [ 'pid', '=', $province_id ] ], 'id')[ 'data' ][ 'id' ] ?? 0;


        if ($city_id > 0 && $province_id > 0)
            $district_id = $address_model->getAreasInfo([ [ 'name', 'like', '%' . $district . '%' ], [ 'level', '=', 3 ], [ 'pid', '=', $city_id ] ], 'id')[ 'data' ][ 'id' ] ?? 0;

        $data = array (
            'province_id' => $province_id ?? 0,
            'city_id' => $city_id ?? 0,
            'district_id' => $district_id ?? 0,
            'province' => $province,
            'city' => $city,
            'district' => $district,
        );
        return $this->response($this->success($data));

    }

}