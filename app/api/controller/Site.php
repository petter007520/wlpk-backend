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

use app\model\system\Site as SiteModel;
use app\model\shop\Shop as ShopModel;

/**
 * 店铺
 * @author Administrator
 *
 */
class Site extends BaseApi
{

    /**
     * 基础信息
     */
    public function info()
    {
        $field = 'site_id,site_domain,site_name,logo,seo_title,seo_keywords,seo_description,site_tel,logo_square';
        $website_model = new SiteModel();
        $info = $website_model->getSiteInfo([ [ 'site_id', '=', $this->site_id ] ], $field);

        //店铺状态
        $shop_model = new ShopModel();
        $shop_status_result = $shop_model->getShopStatus($this->site_id, $this->app_module);
        $shop_status = $shop_status_result[ 'data' ][ 'value' ];

        $info[ 'data' ][ 'shop_status' ] = $shop_status[ 'shop_pc_status' ];
        return $this->response($info);
    }

    /**
     * 手机端二维码
     * @return false|string
     */
    public function wapQrcode()
    {
        $shop_model = new ShopModel();
        $res = $shop_model->qrcode($this->site_id);
        return $this->response($res);
    }

    /**
     * 是否显示店铺相关功能，用于审核小程序
     */
    public function isShow()
    {
        $res = 1;// 0 隐藏，1 显示
        return $this->response($this->success($res));
    }

    /**
     * 店铺状态
     * @return false|string
     */
    public function status()
    {
        return $this->response($this->success());
    }

    /**
     * 店铺联系方式
     * @return false|string
     */
    public function shopContact()
    {
        $data = ( new ShopModel() )->getShopInfo([ [ 'site_id', '=', $this->site_id ] ], 'mobile');
        return $this->response($data);
    }

}