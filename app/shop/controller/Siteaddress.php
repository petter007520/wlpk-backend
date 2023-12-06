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

namespace app\shop\controller;

use app\model\system\Address as AddressModel;
use app\model\shop\SiteAddress as SiteAddressModel;

/**
 * 商家地址库
 * Class Siteaddress
 * @package app\shop\controller
 */
class Siteaddress extends BaseShop
{
    /**
     * 商家地址库列表
     * @return mixed
     */
    public function siteAddress()
    {
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_keys = input('search_keys', "");

            $condition = array (
                [ 'site_id', "=", $this->site_id ]
            );
            if (!empty($search_keys)) {
                $condition[] = [ 'contact_name|full_address', 'like', '%' . $search_keys . '%' ];
            }

            $site_address_model = new SiteAddressModel();
            $list = $site_address_model->getAddressPageList($condition, $page, $page_size, 'id desc');
            return $list;
        } else {
            $this->forthMenu();
            return $this->fetch("siteaddress/site_address_list");
        }
    }

    /**
     * 添加商家地址库
     * @return mixed
     */
    public function addSiteAddress()
    {
        if (request()->isAjax()) {
            $contact_name = input('contact_name', '');//联系人
            $mobile = input('mobile', '');//手机号码
            $postcode = input('postcode', '');//邮编
            $province_id = input('province_id', '');//省id
            $city_id = input('city_id', '');//市id
            $district_id = input('district_id', '');//区id
            $community_id = input('community_id', '');//乡镇id
            $address = input('address', '');//详细地址
            $full_address = input('full_address', '');//完整地址
            $is_return = input('is_return', 0);//是否退货地址
            $is_return_default = input('is_return_default', 0);//是否是默认退货地址
            $is_delivery = input('is_delivery', 0);//是否发货地址

            $site_address_model = new SiteAddressModel();
            $data = array (
                "site_id" => $this->site_id,
                "contact_name" => $contact_name,
                "mobile" => $mobile,
                "postcode" => $postcode,
                "province_id" => $province_id,
                "city_id" => $city_id,
                "district_id" => $district_id,
                "community_id" => $community_id,
                "address" => $address,
                "full_address" => $full_address,
                "is_return" => $is_return,
                "is_return_default" => $is_return_default,
                "is_delivery" => $is_delivery
            );
            $result = $site_address_model->addAddress($data);
            return $result;
        } else {
            //查询省级数据列表
            $address_model = new AddressModel();
            $list = $address_model->getAreaList([ [ "pid", "=", 0 ], [ "level", "=", 1 ] ]);
            $this->assign("province_list", $list[ "data" ]);
            return $this->fetch('siteaddress/add_site_address');
        }
    }

    /**
     * 编辑商家地址库
     * @return mixed
     */
    public function editSiteAddress()
    {
        $site_address_model = new SiteAddressModel();
        $id = input('id', 0);//地址库id
        if (request()->isAjax()) {
            $contact_name = input('contact_name', '');//联系人
            $mobile = input('mobile', '');//手机号码
            $postcode = input('postcode', '');//邮编
            $province_id = input('province_id', '');//省id
            $city_id = input('city_id', '');//市id
            $district_id = input('district_id', '');//区id
            $community_id = input('community_id', '');//乡镇id
            $address = input('address', '');//详细地址
            $full_address = input('full_address', '');//完整地址
            $is_return = input('is_return', 0);//是否退货地址
            $is_return_default = input('is_return_default', 0);//是否是默认退货地址
            $is_delivery = input('is_delivery', 0);//是否发货地址

            $data = array (
                "contact_name" => $contact_name,
                "mobile" => $mobile,
                "postcode" => $postcode,
                "province_id" => $province_id,
                "city_id" => $city_id,
                "district_id" => $district_id,
                "community_id" => $community_id,
                "address" => $address,
                "full_address" => $full_address,
                "is_return" => $is_return,
                "is_return_default" => $is_return_default,
                "is_delivery" => $is_delivery
            );

            $condition = array (
                [ "id", "=", $id ],
                [ "site_id", "=", $this->site_id ],
            );
            $result = $site_address_model->editAddress($data, $condition);
            return $result;
        } else {
            //查询省级数据列表
            $address_model = new AddressModel();
            $list = $address_model->getAreaList([ [ "pid", "=", 0 ], [ "level", "=", 1 ] ]);
            $this->assign("province_list", $list[ "data" ]);
            $condition = array (
                [ "id", "=", $id ],
                [ "site_id", "=", $this->site_id ]
            );
            $site_address_info = $site_address_model->getAddressInfo($condition);
            $this->assign("site_address_info", $site_address_info[ 'data' ]);
            return $this->fetch('siteaddress/edit_site_address');
        }
    }

    /**
     * 删除商家地址库
     */
    public function deleteSiteAddress()
    {
        if (request()->isAjax()) {
            $id = input('id', '');
            $condition = array (
                [ "id", "=", $id ],
                [ "site_id", "=", $this->site_id ],
            );
            $site_address_model = new SiteAddressModel();
            $result = $site_address_model->deleteAddress($condition);
            return $result;
        }
    }

    /**
     * 退货地址
     * @return array
     */
    public function getSiteAddressList()
    {
        if (request()->isAjax()) {
            $is_return = input('is_refund', 0);
            $condition = array (
                [ 'site_id', '=', $this->site_id ]
            );
            if ($is_return) {
                $condition[] = [ 'is_return', '=', $is_return ];
            }
            //商家地址列表
            $site_address_model = new SiteAddressModel();
            $res = $site_address_model->getAddressList($condition, '*', 'id desc');
            return $res;
        }
    }

}