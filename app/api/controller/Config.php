<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 * @author : niuteam
 */

namespace app\api\controller;

use app\model\express\Config as ExpressConfig;
use app\model\goods\Cart as CartModel;
use app\model\system\Promotion as PrmotionModel;
use app\model\system\Servicer;
use app\model\web\Config as ConfigModel;

class Config extends BaseApi
{

    /**
     * 详情信息
     */
    public function defaultimg()
    {
        $upload_config_model = new ConfigModel();
        $res = $upload_config_model->getDefaultImg($this->site_id, 'shop');
        if (!empty($res[ 'data' ][ 'value' ])) {
            return $this->response($this->success($res[ 'data' ][ 'value' ]));
        } else {
            return $this->response($this->error());
        }
    }

    /**
     * 版权信息
     */
    public function copyright()
    {
        $config_model = new ConfigModel();
        $res = $config_model->getCopyright($this->site_id, 'shop');
        return $this->response($this->success($res[ 'data' ][ 'value' ]));
    }

    /**
     * 获取当前时间戳
     * @return false|string
     */
    public function time()
    {
        $time = time();
        return $this->response($this->success($time));
    }

    /**
     * 获取验证码配置
     */
    public function getCaptchaConfig()
    {

        $config_model = new ConfigModel();
        $info = $config_model->getCaptchaConfig();
        return $this->response($this->success($info));
    }

    /**
     * 客服配置
     */
    public function servicer()
    {
        $servicer_model = new Servicer();
        $result = $servicer_model->getServicerConfig()[ 'data' ] ?? [];
        return $this->response($this->success($result[ 'value' ] ?? []));
    }

    public function init()
    {

        $cart_count = 0;
        $token = $this->checkToken();
        if ($token[ 'code' ] >= 0) {
            // 购物车数量
            $cart = new CartModel();
            $condition = [
                [ 'gc.member_id', '=', $token[ 'data' ][ 'member_id' ] ],
                [ 'gc.site_id', '=', $this->site_id ],
                [ 'gs.goods_state', '=', 1 ],
                [ 'gs.is_delete', '=', 0 ]
            ];
            $list = $cart->getCartList($condition, 'gc.num');
            $list = $list[ 'data' ];
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v[ 'num' ];
            }
        }

        // 商城风格
        $diy_view_api = new Diyview();
        $diy_style = json_decode($diy_view_api->style(), true)[ 'data' ][ 'value' ];

        // 底部导航
        $diy_bottom_nav = json_decode($diy_view_api->bottomNav(), true)[ 'data' ][ 'value' ];

        // 插件存在性
        $addon_api = new Addon();
        $addon_is_exist = json_decode($addon_api->addonIsExit(), true)[ 'data' ];

        // 默认图
        $config_model = new ConfigModel();
        $default_img = $config_model->getDefaultImg($this->site_id, 'shop')[ 'data' ][ 'value' ];

        // 版权信息
        $copyright = $config_model->getCopyright($this->site_id, 'shop')[ 'data' ][ 'value' ];

        $site_api = new Site();
        $site_info = json_decode($site_api->info(), true)[ 'data' ];

        $servicer = json_decode($this->servicer(), true)[ 'data' ];

        $this->initStoreData();

        $res = [
            'cart_count' => $cart_count,
            'style_theme' => $diy_style,
            'diy_bottom_nav' => $diy_bottom_nav,
            'addon_is_exist' => $addon_is_exist,
            'default_img' => $default_img,
            'copyright' => $copyright,
            'site_info' => $site_info,
            'servicer' => $servicer,
            'store_config' => $this->store_data[ 'config' ]
        ];

        if (!empty($this->store_data[ 'store_info' ])) {
            $res[ 'store_info' ] = $this->store_data[ 'store_info' ];
        }

        return $this->response($this->success($res));
    }

    /**
     * 获取pc首页商品分类配置
     * @return false|string
     */
    public function categoryconfig()
    {
        $config_model = new ConfigModel();
        $config_info = $config_model->getCategoryConfig($this->site_id);
        return $this->response($this->success($config_info[ 'data' ][ 'value' ]));
    }

    /**
     *
     * @return false|string
     */
    public function enabledExpressType()
    {
        $express_type = ( new ExpressConfig() )->getEnabledExpressType($this->site_id);
        return $this->response($this->success($express_type));
    }

    /**
     * 获取活动专区页面配置
     * @return false|string
     */
    public function promotionZoneConfig()
    {
        $name = isset($this->params[ 'name' ]) ? $this->params[ 'name' ] : ''; // 活动名称标识

        $promotion_model = new PrmotionModel();
        $res = $promotion_model->getPromotionZoneConfig($name, $this->site_id)[ 'data' ][ 'value' ];
        return $this->response($this->success($res));
    }

}