<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\coupon\model\share;

use app\model\share\WchatShareBase as BaseModel;
use app\model\system\Config as ConfigModel;
use app\model\system\Site as SiteModel;

/**
 * 分享
 */
class WchatShare extends BaseModel
{
    protected $config = [
        [
            'title' => '领券中心',
            'config_key' => 'WCHAT_SHARE_CONFIG_COUPON_LIST',
            'path' => [ '/pages_tool/goods/coupon' ],
            'method_prefix' => 'couponList',
        ],
    ];

    /**
     * 商品列表分享数据
     * @param $param
     * @return array
     */
    protected function couponListShareData($param)
    {
        $site_id = $param[ 'site_id' ] ?? 0;

        //站点设置
        $site_model = new SiteModel();
        $site_info = $site_model->getSiteInfo([ [ 'site_id', '=', $site_id ] ])[ 'data' ];

        //跳转路径
        $link = $this->getShareLink($param);

        //获取和替换配置数据
        $config_method = preg_replace('/Data$/', 'Config', __FUNCTION__);
        $config_data = $this->$config_method($param);
        $title = $config_data[ 'value' ][ 'title' ];
        $desc = $config_data[ 'value' ][ 'desc' ];
        $image_url = $config_data[ 'value' ][ 'imgUrl' ] ? $config_data[ 'value' ][ 'imgUrl' ] : $site_info[ 'logo_square' ];

        $data = [
            'title' => $title,
            'desc' => $desc,
            'link' => $link,
            'imgUrl' => $image_url,
        ];
        return [
            'permission' => [
                'hideOptionMenu' => false,
                'hideMenuItems' => [],
            ],
            'data' => $data,//分享内容
        ];
    }

    /**
     * 商品列表分享配置
     * @param $param
     * @return array
     */
    protected function couponListShareConfig($param)
    {
        $site_id = $param[ 'site_id' ];
        $config = $param[ 'config' ];

        $config_model = new ConfigModel();
        $data = $config_model->getConfig([
            [ 'site_id', '=', $site_id ],
            [ 'app_module', '=', 'shop' ],
            [ 'config_key', '=', $config[ 'config_key' ] ],
        ])[ 'data' ];
        if (empty($data[ 'value' ])) {
            $data[ 'value' ] = [
                'title' => '送你一张优惠券',
                'desc' => "优惠多多\n好物多多",
                'imgUrl' => '',
            ];
        }
        if (empty($data[ 'value' ][ 'imgUrl' ])) {
            $data[ 'value' ][ 'imgUrl' ] = img('addon/coupon/icon.png');
        }
        $variable = [];

        return [
            'value' => $data[ 'value' ],
            'variable' => $variable,
        ];
    }
}
