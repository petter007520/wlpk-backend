<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;


use app\model\express\ExpressDeliver;
use app\model\express\Local as LocalModel;
use app\model\shop\Shop as ShopModel;
use app\model\system\Address as AddressModel;
use app\model\web\Config as WebConfig;
use app\model\store\Store;

/**
 * 配送
 * Class Express
 * @package app\shop\controller
 */
class Local extends BaseShop
{

    /**
     *  本地配送设置
     */
    public function local()
    {
        $shop_model = new ShopModel();
        $shop_info_result = $shop_model->getShopInfo([ [ 'site_id', '=', $this->site_id ] ]);
        $shop_info = $shop_info_result[ 'data' ];
        $local_model = new LocalModel();
        $store = new Store();
        $default_store = $store->getStoreInfo([ ['site_id', '=', $this->site_id], ['is_default', '=', 1] ], 'store_id')['data'] ?? [];
        $store_id = $default_store['store_id'] ?? 0;
        if (request()->isAjax()) {
            if (empty($shop_info)) {
                return $local_model->error([], '店铺地址尚为配置');
            }

            $data = [
                'type' => input('type', 'default'),//配送方式  default 商家自配送  other 第三方配送
                'area_type' => input('area_type', 1),//配送区域
                'local_area_json' => input('local_area_json', ''),//区域及业务集合json
                'time_is_open' => input('time_is_open', 0),
                'time_type' => input('time_type', 0),//时间选取类型 0 全天  1 自定义
                'time_week' => input('time_week', ''),
                'start_time' => input('start_time', 0),
                'end_time' => input('end_time', 0),
                'update_time' => time(),
                'is_open_step' => input('is_open_step', 0),
                'start_distance' => input('start_distance', 0),
                'start_delivery_money' => input('start_delivery_money', 0),
                'continued_distance' => input('continued_distance', 0),
                'continued_delivery_money' => input('continued_delivery_money', 0),
                'start_money' => input('start_money', 0),
                'delivery_money' => input('delivery_money', 0),
                'area_array' => input('area_array', ''),//地域集合
                'man_money' => input('man_money', ''),
                'man_type' => input('man_type', ''),
                'man_discount' => input('man_discount', ''),
                'time_interval' => input('time_interval', 30),
                'delivery_time' => input('delivery_time', ''),
                'advance_day' => input('advance_day', 0),
                'most_day' => input('most_day', 7)
            ];

            $condition = array (
                [ 'site_id', '=', $this->site_id ],
                [ 'store_id', '=', $store_id ]
            );
            return $local_model->editLocal($data, $condition);
        } else {
            if (empty($shop_info)) {
                $this->error('店铺地址尚为配置');
            }
            $this->assign('shop_detail', $shop_info);

            $local_result = $local_model->getLocalInfo([ [ 'site_id', '=', $this->site_id ], ['store_id', '=', $store_id] ]);

            $district_list = [];
            if ($shop_info[ 'province' ] > 0 && $shop_info[ 'city' ] > 0) {
                //查询省级数据列表
                $address_model = new AddressModel();
                $list = $address_model->getAreaList([ [ "pid", "=", $shop_info[ 'city' ] ], [ "level", "=", 3 ] ]);
                $district_list = $list[ "data" ];
            }
            $this->assign('district_list', $district_list);
            $this->assign('local_info', $local_result['data']);

            $config_model = new WebConfig();
            $mp_config = $config_model->getMapConfig($this->site_id);
            $this->assign('tencent_map_key', $mp_config['data']['value']['tencent_map_key']);
            //效验腾讯地图KEY
            $check_map_key = $config_model->checkQqMapKey($mp_config[ 'data' ][ 'value' ][ 'tencent_map_key' ]);
            $this->assign('check_map_key', $check_map_key);

            return $this->fetch('local/local');
        }
    }

    /**
     *  配送员列表
     */
    public function deliverLists()
    {
        $deliver_model = new ExpressDeliver();
        if (request()->isAjax()) {
            $page = input('page', '1');
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $condition = [
                [
                    'site_id', '=', $this->site_id
                ]
            ];
            $search_text = input('search_text', '');
            if (!empty($search_text)) {
                $condition[] = [ 'deliver_name', 'like', '%' . $search_text . '%' ];
            }
            $deliver_lists = $deliver_model->getDeliverPageLists($condition, '*', 'create_time desc', $page, $page_size);
            return $deliver_lists;
        } else {
            return $this->fetch('local/deliverlists');
        }
    }

    /**
     *  添加配送员
     */
    public function addDeliver()
    {
        $deliver_model = new ExpressDeliver();
        if (request()->isAjax()) {
            $data = [
                'deliver_name' => input('deliver_name', ''),
                'deliver_mobile' => input('deliver_mobile', ''),
                'store_id' => input('store_id', 0),
                'site_id' => $this->site_id,
            ];
            $result = $deliver_model->addDeliver($data);
            return $result;
        } else {
            return $this->fetch('local/adddeliver');
        }
    }

    /**
     *  编辑配送员
     */
    public function editDeliver()
    {
        $deliver_model = new ExpressDeliver();
        $deliver_id = input('deliver_id', 0);
        $site_id = $this->site_id;
        if (request()->isAjax()) {
            $data = [
                'deliver_name' => input('deliver_name', ''),
                'deliver_mobile' => input('deliver_mobile', ''),
                'site_id' => $site_id,
            ];
            $result = $deliver_model->editDeliver($data, $deliver_id);
            return $result;
        } else {
            $this->assign('deliver_id', $deliver_id);
            $deliver_info = $deliver_model->getDeliverInfo($deliver_id, $site_id);
            $this->assign('deliver_info', $deliver_info[ 'data' ]);
            return $this->fetch('local/editdeliver');
        }
    }

    /**
     *  删除配送员
     */
    public function deleteDeliver()
    {
        $deliver_model = new ExpressDeliver();
        if (request()->isAjax()) {
            $deliver_ids = input('deliver_ids', 0);
            $site_id = $this->site_id;
            $result = $deliver_model->deleteDeliver($deliver_ids, $site_id);
            return $result;
        }
    }

    /**
     *  获取配送员
     */
    public function getDeliverList()
    {
        $deliver_model = new ExpressDeliver();
        $list = $deliver_model->getDeliverLists([ [ 'site_id', '=', $this->site_id ] ]);
        return $list;
    }
}