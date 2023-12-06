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

use app\model\share\WeappShareBase;
use app\model\system\Config as ConfigModel;

/**
 * 分享
 */
class WeappShare extends WeappShareBase
{
    protected $config = [
        [
            'title' => '领券中心',
            'config_key' => 'WEAPP_SHARE_CONFIG_COUPON_LIST',
            'path' => [ '/pages_tool/goods/coupon' ],
            'method_prefix' => 'couponList',
        ],
    ];

    protected $sort = 2;

    /***************************** 领券中心 ***************************/

    /**
     * 首页分享数据
     * @param $param
     * @return array
     */
    protected function couponListShareData($param)
    {
        //获取和替换配置数据
        $config_data = $this->couponListShareConfig($param);
        $title = $config_data[ 'value' ][ 'title' ];
        $image_url = $config_data[ 'value' ][ 'imageUrl' ] ? img($config_data[ 'value' ][ 'imageUrl' ]) : '';
        $path = $this->getSharePath($param);

        $data = [
            'title' => $title,
            'path' => $path,
            'imageUrl' => $image_url,
        ];
        return [
            'permission' => [
                'onShareAppMessage' => true,
                'onShareTimeline' => true,
            ],
            'data' => $data,//分享内容
        ];
    }

    /**
     * 首页分享配置
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
                'title' => '送你一张优惠券，快来领取吧',
                'imageUrl' => '',
            ];
        }
        $variable = [];

        return [
            'value' => $data[ 'value' ],
            'variable' => $variable,
        ];
    }
}
