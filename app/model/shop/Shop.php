<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\shop;

use app\model\system\Config as ConfigModel;
use app\model\BaseModel;
use addon\shopcomponent\model\Weapp;

/**
 * 店铺信息（无缓存）
 */
class Shop extends BaseModel
{

    /**
     * 添加店铺
     * @param $data
     * @return array
     */
    public function addShop($data)
    {
        $res = model('shop')->add($data);
        return $this->success($res);
    }

    /**
     * 修改店铺(不能随意修改组)
     * @param array $data
     */
    public function editShop($data, $condition, $site_id = 1)
    {
        $res = model('shop')->update($data, $condition);

        //更新视频号商家信息
        if (isset($data[ 'name' ]) && isset($data[ 'mobile' ]) && isset($data[ 'full_address' ]) && isset($data[ 'address' ]) && addon_is_exit("shopcomponent")) {
            $weapp_model = new Weapp($site_id);
            $info = $weapp_model->checkRegister();
            if (isset($info[ 'data' ][ 'status' ]) && $info[ 'data' ][ 'status' ] == 2) {
                $params = [
                    "service_agent_path" => "",
                    "service_agent_phone" => $data[ 'mobile' ],
                    "service_agent_type" => [ 0, 2 ],
                    "default_receiving_address" => [
                        "receiver_name" => $data[ 'name' ],
                        "detailed_address" => $data[ 'full_address' ] . $data[ 'address' ],
                        "tel_number" => $data[ 'mobile' ],
                        "country" => "",
                        "province" => $data[ 'province' ],
                        "city" => $data[ 'city' ],
                        "town" => $data[ 'district' ]
                    ]
                ];
                $weapp_model->updateShop($params);
            }
        }

        return $this->success($res);
    }

    /**
     * 获取店铺信息
     * @param array $condition
     * @param string $field
     */
    public function getShopInfo($condition, $field = '*')
    {
        $res = model('shop')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取店铺详情
     * @param int $site_id
     */
    public function getShopDetail($site_id)
    {
        $res = [];
        $shop_info = model('shop')->getInfo([ [ 'site_id', '=', $site_id ] ], 'site_id,expire_time,site_name,username,shop_status,logo,avatar,banner,seo_description,qq,ww,telephone,workingtime,email');

        $res [ 'shop_info' ] = $shop_info;

        return $this->success($res);
    }

    /**
     * 店铺推广二维码
     * @param $site_id
     * @param string $type
     * @return array
     */
    public function qrcode($site_id, $type = "create")
    {
        $data = [
            'site_id' => $site_id,
            'app_type' => "all", // all为全部
            'type' => $type, // 类型 create创建 get获取
            'data' => [],
            'page' => '',
            'qrcode_path' => 'upload/qrcode/shop/',
            'qrcode_name' => "shop_qrcode_" . $site_id,
        ];

        event('Qrcode', $data, true);
        $app_type_list = config('app_type');

        $path = [];

        foreach ($app_type_list as $k => $v) {
            switch ( $k ) {
                case 'h5':
                    $wap_domain = getH5Domain();
                    $path[ $k ][ 'status' ] = 1;
                    $path[ $k ][ 'url' ] = $wap_domain . $data[ 'page' ];
                    $path[ $k ][ 'img' ] = $data[ 'qrcode_path' ] . $data[ 'qrcode_name' ] . '_' . $k . '.png';
                    break;
                case 'weapp' :
                    $config = new ConfigModel();
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信小程序';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }

                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信小程序';
                    }
                    break;

                case 'wechat' :
                    $config = new ConfigModel();
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信公众号';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }
                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信公众号';
                    }
                    break;
            }

        }
        $return = [
            'path' => $path,
        ];
        return $this->success($return);
    }


    /**
     * 设置商城状态
     * @param $data
     * @param $site_id
     * @param $app_module
     * @return array
     */
    public function setShopStatus($data, $site_id, $app_module)
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '商城状态设置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'SHOP_STATUS_CONFIG' ] ]);
        return $res;
    }

    /**
     * 获取商城状态
     * @param $site_id
     * @param $app_module
     * @return array
     */
    public function getShopStatus($site_id, $app_module)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'SHOP_STATUS_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'shop_pc_status' => 1,
                'shop_h5_status' => 1,
                'shop_weapp_status' => 1,
            ];
        }
        return $res;
    }

}