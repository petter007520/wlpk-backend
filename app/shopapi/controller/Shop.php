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

namespace app\shopapi\controller;

use app\model\shop\Shop as ShopModel;
use app\model\system\Site;

/**
 * 店铺
 * Class Shop
 * @package app\shop\controller
 */
class Shop extends BaseApi
{

    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            echo $this->response($token);
            exit;
        }
    }

    /**
     * 获取店铺信息
     * @return false|string
     */
    public function shopInfo()
    {
        $condition = array (
            [ "site_id", "=", $this->site_id ]
        );
        $shop_info_result = (new ShopModel())->getShopInfo($condition);
        $site_info = (new Site())->getSiteInfo($condition);

        $shop_info = array_merge($shop_info_result['data'], $site_info['data']);

        $user_info = [
            'group_id' => $this->user_info[ 'group_id' ],
            'group_name' => $this->user_info[ 'group_name' ],
            'is_admin' => $this->user_info[ 'is_admin' ],
            'status' => $this->user_info[ 'status' ],
            'uid' => $this->user_info[ 'uid' ],
            'username' => $this->user_info[ 'username' ],
        ];
        $res = [
            'shop_info' => $shop_info,
            'user_info' => $user_info
        ];
        return $this->response($this->success($res));
    }


    /**
     * 店铺设置
     * @return mixed
     */
    public function config()
    {
        $shop_model = new Site();
        $logo = isset($this->params[ 'logo' ]) ? $this->params[ 'logo' ] : '';//店铺logo
        $avatar = isset($this->params[ 'avatar' ]) ? $this->params[ 'avatar' ] : '';//店铺头像（大图）
        $banner = isset($this->params[ 'banner' ]) ? $this->params[ 'banner' ] : '';//店铺条幅
        $sit_name = isset($this->params['site_name']) ? $this->params['site_name'] : '';
        $seo_keywords = isset($this->params[ 'seo_keywords' ]) ? $this->params[ 'seo_keywords' ] : '';
        $seo_description = isset($this->params[ 'seo_description' ]) ? $this->params[ 'seo_description' ] : '';//店铺简介
        $data = array (
            "logo" => $logo,
            "avatar" => $avatar,
            "site_name"=> $sit_name,
            "banner" => $banner,
            "seo_keywords" => $seo_keywords,
            "seo_description" => $seo_description,
        );
        $res = $shop_model->editSite($data, [ [ 'site_id', '=', $this->site_id ] ]);
        return $this->response($res);
    }

    /**
     * 联系方式
     * @return mixed
     */
    public function contact()
    {
        $shop_model = new ShopModel();

        $province = isset($this->params[ 'province' ]) ? $this->params[ 'province' ] : '';//省级地址
        $province_name = isset($this->params[ 'province_name' ]) ? $this->params[ 'province_name' ] : '';//省级地址
        $city = isset($this->params[ 'city' ]) ? $this->params[ 'city' ] : '';//市级地址
        $city_name = isset($this->params[ 'city_name' ]) ? $this->params[ 'city_name' ] : '';//市级地址
        $district = isset($this->params[ 'district' ]) ? $this->params[ 'district' ] : '';//县级地址
        $district_name = isset($this->params[ 'district_name' ]) ? $this->params[ 'district_name' ] : '';//县级地址
        $community = isset($this->params[ 'community' ]) ? $this->params[ 'community' ] : '';//乡镇地址
        $community_name = isset($this->params[ 'community_name' ]) ? $this->params[ 'community_name' ] : '';//乡镇地址
        $address = isset($this->params[ 'address' ]) ? $this->params[ 'address' ] : '';//详细地址
        $full_address = isset($this->params[ 'full_address' ]) ? $this->params[ 'full_address' ] : '';//完整地址
        $longitude = isset($this->params[ 'longitude' ]) ? $this->params[ 'longitude' ] : '';//经度
        $latitude = isset($this->params[ 'latitude' ]) ? $this->params[ 'latitude' ] : '';//纬度

        $qq = isset($this->params[ 'qq' ]) ? $this->params[ 'qq' ] : '';//qq
        $ww = isset($this->params[ 'ww' ]) ? $this->params[ 'ww' ] : '';//ww
        $email = isset($this->params[ 'email' ]) ? $this->params[ 'email' ] : '';//邮箱
        $telephone = isset($this->params[ 'telephone' ]) ? $this->params[ 'telephone' ] : '';//联系电话
        $name = isset($this->params[ 'name' ]) ? $this->params[ 'name' ] : '';//联系人姓名
        $mobile = isset($this->params[ 'mobile' ]) ? $this->params[ 'mobile' ] : '';//联系人手机号
        $work_week = isset($this->params[ 'work_week' ]) ? $this->params[ 'work_week' ] : '';//工作日  例如 : 1,2,3,4,5,6,7
        $start_time = isset($this->params[ 'start_time' ]) ? $this->params[ 'start_time' ] : '';//开始时间
        $end_time = isset($this->params[ 'end_time' ]) ? $this->params[ 'end_time' ] : '';//结束时间
        $data = array (
            "province" => $province,
            "province_name" => $province_name,
            "city" => $city,
            "city_name" => $city_name,
            "district" => $district,
            "district_name" => $district_name,
            "community" => $community,
            "community_name" => $community_name,
            "address" => $address,
            "full_address" => $full_address,
            "longitude" => $longitude,
            "latitude" => $latitude,
            "qq" => $qq,
            "ww" => $ww,
            "email" => $email,
            "telephone" => $telephone,
            "work_week" => $work_week,
            "start_time" => $start_time,
            "end_time" => $end_time,
            "name" => $name,
            "mobile" => $mobile
        );
        $res = $shop_model->editShop($data, [ [ 'site_id', '=', $this->site_id ] ]);
        return $this->response($res);
    }

}