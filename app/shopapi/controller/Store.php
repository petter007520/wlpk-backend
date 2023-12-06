<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shopapi\controller;

use app\model\store\Store as StoreModel;
use app\model\system\Address as AddressModel;
use app\model\web\Config as ConfigModel;

/**
 * 门店
 * Class Store
 * @package app\shop\controller
 */
class Store extends BaseApi
{

    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
        $token = $this->checkToken();
        if ($token['code'] < 0) {
            echo $this->response($token);
            exit;
        }
    }

    /**
     * 门店列表
     * @return mixed
     */
    public function lists()
    {
        //判断门店插件是否存在
        $store_is_exit = addon_is_exit('store', $this->site_id);
        if ($store_is_exit) {
            $store_model = new StoreModel();
            $page = isset($this->params['page']) ? $this->params['page'] : 1;
            $page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGE_LIST_ROWS;
            $order = isset($this->params['order']) ? $this->params['order'] : "create_time desc";
            $keyword = isset($this->params['keyword']) ? $this->params['keyword'] : '';
            $status = isset($this->params['status']) ? $this->params['status'] : '';
            $type = isset($this->params['type']) ? $this->params['type'] : '';
            $condition = [];
            if ($type == 1) {
                if ($status != null) {
                    $condition[] = ['status', '=', $status];
                    $condition[] = ['is_frozen', '=', 0];
                }
            } else if ($type == 2) {
                $condition[] = ['is_frozen', '=', $status];
            }
            $condition[] = ['site_id', "=", $this->site_id];
            //关键字查询
            if (!empty($keyword)) {
                $condition[] = ["store_name", "like", "%" . $keyword . "%"];
            }
            $list = $store_model->getStorePageList($condition, $page, $page_size, $order);
            return $this->response($list);
        } else {
            return $this->response($this->success('', '请联系管理员安装插件！'));
        }
    }

    public function detail(){
        $store_id = isset($this->params['store_id']) ? $this->params['store_id'] : 0;
        $condition = array(
            ["site_id", "=", $this->site_id],
            ["store_id", "=", $store_id]
        );
        $store_model = new StoreModel();
        $info_result = $store_model->getStoreInfo($condition);//门店信息
        $info = $info_result["data"];
        $data["info"] = $info;
        $is_exit = addon_is_exit("store");
        $config_model = new ConfigModel();
        $mp_config = $config_model->getMapConfig($this->site_id);
        $data["info"] = $info;
        $data["is_exit"] = $is_exit;
        $data["http_type"] = get_http_type();
        $data["tencent_map_key"] = $mp_config['data']['value']['tencent_map_key'];
        return $this->response($this->success($data));
    }


    /**
     * 添加门店
     * @return mixed
     */
    public function addStore()
    {
        $is_store = addon_is_exit('store');
        $store_name = $this->params['store_name'] ? $this->params['store_name'] : '';
        $telphone = $this->params['telphone'] ? $this->params['telphone'] : '';
        $store_image = $this->params['store_image'] ? $this->params['store_image'] : '';
        $status = $this->params['status'] ? $this->params['status'] : 0;
        $province_id = $this->params['province_id'] ? $this->params['province_id'] : 0;
        $city_id = $this->params['city_id'] ? $this->params['city_id'] : 0;
        $district_id = $this->params['district_id'] ? $this->params['district_id'] : 0;
        $community_id = $this->params['community_id'] ? $this->params['community_id'] : 0;
        $address = $this->params['address'] ? $this->params['address'] : '';
        $full_address = $this->params['full_address'] ? $this->params['full_address'] : '';
        $longitude = $this->params['longitude'] ? $this->params['longitude'] : 0;
        $latitude = $this->params['latitude'] ? $this->params['latitude'] : 0;
        $is_pickup = $this->params['is_pickup'] ? $this->params['is_pickup'] : 0;
        $is_o2o = $this->params['is_o2o'] ? $this->params['is_o2o'] : 0;
        $open_date = $this->params['open_date'] ? $this->params['open_date'] : '';
        $data = array(
            "store_name" => $store_name,
            "telphone" => $telphone,
            "store_image" => $store_image,
            "status" => $status,
            "province_id" => $province_id,
            "city_id" => $city_id,
            "district_id" => $district_id,
            "community_id" => $community_id,
            "address" => $address,
            "full_address" => $full_address,
            "longitude" => $longitude,
            "latitude" => $latitude,
            "is_pickup" => $is_pickup,
            "is_o2o" => $is_o2o,
            "open_date" => $open_date,
            "site_id" => $this->site_id
        );
        //判断是否开启多门店
        if ($is_store == 1) {
            $user_data = [
                'username' => isset($this->params['username']) ? $this->params['username'] : '',
                'password' => data_md5(isset($this->params['password']) ? $this->params['password'] : ''),
            ];
        } else {
            $user_data = [];
        }
        $store_model = new StoreModel();
        $result = $store_model->addStore($data, $user_data, $is_store);
        return $this->response($result);
    }

    /**
     * 编辑门店
     * @return mixed
     */
    public function editStore()
    {

        $store_id = isset($this->params['store_id']) ? $this->params['store_id'] : 0;
        $condition = array(
            ["site_id", "=", $this->site_id],
            ["store_id", "=", $store_id]
        );
        $store_model = new StoreModel();
        $store_name = isset($this->params['store_name']) ? $this->params['store_name'] : 0;
        $telphone = isset($this->params['telphone']) ? $this->params['telphone'] : '';
        $store_image = isset($this->params['store_image']) ? $this->params['store_image'] : '';
        $status = isset($this->params['status']) ? $this->params['status'] : 0;
        $province_id = isset($this->params['province_id']) ? $this->params['province_id'] : 0;
        $city_id = isset($this->params['city_id']) ? $this->params['city_id'] : 0;
        $district_id = isset($this->params['district_id']) ? $this->params['district_id'] : 0;
        $community_id = isset($this->params['community_id']) ? $this->params['community_id'] : 0;
        $address = isset($this->params['address']) ? $this->params['address'] : '';
        $full_address = isset($this->params['full_address']) ? $this->params['full_address'] : '';
        $longitude = isset($this->params['longitude']) ? $this->params['longitude'] : 0;
        $latitude = isset($this->params['latitude']) ? $this->params['latitude'] : 0;
        $is_pickup = isset($this->params['is_pickup']) ? $this->params['is_pickup'] : 0;
        $is_o2o = isset($this->params['is_o2o']) ? $this->params['is_o2o'] : 0;
        $open_date = isset($this->params['open_date']) ? $this->params['open_date'] : '';
        $data = array(
            "store_name" => $store_name,
            "telphone" => $telphone,
            "store_image" => $store_image,
            "status" => $status,
            "province_id" => $province_id,
            "city_id" => $city_id,
            "district_id" => $district_id,
            "community_id" => $community_id,
            "address" => $address,
            "full_address" => $full_address,
            "longitude" => $longitude,
            "latitude" => $latitude,
            "is_pickup" => $is_pickup,
            "is_o2o" => $is_o2o,
            "open_date" => $open_date,
        );
        $result = $store_model->editStore($data, $condition);
        return $this->response($result);

    }

    /**
     * 删除门店
     * @return mixed
     */
    public function deleteStore()
    {
        $store_id = isset($this->params["store_id"]) ? $this->params["store_id"] : '0';
        $condition = array(
            ["site_id", "=", $this->site_id],
            ["store_id", "=", $store_id]
        );
        $store_model = new StoreModel();
        $result = $store_model->deleteStore($condition);
        return $this->response($result);
    }

    /**
     * 冻结门店
     * @return array
     */
    public function frozenStore()
    {
        $store_id = isset($this->params['store_id']) ? $this->params['store_id'] : '0';
        $is_frozen = isset($this->params['is_frozen']) ? $this->params['is_frozen'] : '0';
        $condition = [
            ["site_id", "=", $this->site_id],
            ["store_id", "=", $store_id]
        ];
        $store_model = new StoreModel();
        $res = $store_model->frozenStore($condition, $is_frozen);
        return $this->response($res);
    }

    /**
     * 重置密码
     */
    public function modifyPassword()
    {
        $store_id = isset($this->params['store_id']) ? $this->params['store_id'] : '0';
        $password = isset($this->params['password']) ? $this->params['password'] : '123456';
        $store_model = new StoreModel();
        $data = $store_model->resetStorePassword($password, [['store_id', '=', $store_id]]);
        return $this->response($data);
    }
}