<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\share;

use app\model\system\Site as SiteModel;
use app\model\system\Config as ConfigModel;

/**
 * 分享
 */
class WeappShare extends WeappShareBase
{
    protected $config = [
        [
            'title' => '商城首页',
            'config_key' => 'WEAPP_SHARE_CONFIG_INDEX',
            'path' => ['/pages/index/index'],
            'method_prefix' => 'index',
        ],
    ];

    protected $sort = 1;

    /**
     * 首页分享数据
     * @param $param
     * @return array
     */
    protected function indexShareData($param)
    {
        $site_id = $param['site_id'] ?? 0;

        //站点设置
        $site_model = new SiteModel();
        $site_info = $site_model->getSiteInfo([['site_id', '=', $site_id]])['data'];

        //获取和替换配置数据
        $config_data = $this->indexShareConfig($param);
        $title = str_replace('{site_name}', $site_info['site_name'], $config_data['value']['title']);
        $image_url = $config_data['value']['imageUrl'] ? img($config_data['value']['imageUrl']) : '';
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
    protected function indexShareConfig($param)
    {
        $site_id = $param['site_id'];
        $config = $param['config'];

        $config_model = new ConfigModel();
        $data = $config_model->getConfig([
            ['site_id', '=', $site_id],
            ['app_module', '=', 'shop'],
            ['config_key', '=', $config['config_key']],
        ])['data'];
        if (empty($data['value'])) {
            $data['value'] = [
                'title' => '{site_name}',
                'imageUrl' => '',
            ];
        }
        $variable = [
            ['title' => '店铺名称', 'name' => '{site_name}'],
        ];

        return [
            'value' => $data['value'],
            'variable' => $variable,
        ];
    }
}
