<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\weapp\model;

use app\model\system\Config as ConfigModel;
use app\model\BaseModel;
use think\facade\Cache;
use app\model\upload\Upload;

/**
 * 微信小程序配置
 */
class Config extends BaseModel
{
    /******************************************************************** 微信小程序配置 start ****************************************************************************/
    /**
     * 设置微信小程序配置
     * @return multitype:string mixed
     */
    public function setWeappConfig($data, $is_use, $site_id = 0)
    {
        $config_info = $this->getWeappConfig($site_id);
        if (!empty($config_info[ 'data' ][ 'value' ][ 'qrcode' ]) && !empty($data[ 'qrcode' ]) && $config_info[ 'data' ][ 'value' ][ 'qrcode' ] != $data[ 'qrcode' ]) {
            $upload_model = new Upload();
            $upload_model->deletePic($config_info[ 'data' ][ 'value' ][ 'qrcode' ], $site_id);
        }

        $config = new ConfigModel();
        $res = $config->setConfig($data, '微信小程序设置', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_CONFIG' ] ]);
        if ($res && $data[ 'qrcode' ]) {
            copy($data[ 'qrcode' ], "public/static/img/default_img/wxewm.png");
        }
        return $res;
    }

    /**
     * 获取微信小程序配置信息
     * @param int $site_id
     * @return array
     */
    public function getWeappConfig($site_id = 0)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_CONFIG' ] ]);
        return $res;
    }
    /******************************************************************** 微信小程序配置 end ****************************************************************************/

    /**
     * 设置小程序版本信息
     * @param $data
     * @param $is_use
     * @param int $site_id
     * @return array
     */
    public function setWeappVersion($data, $is_use, $site_id = 0)
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '小程序版本', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_VERSION' ] ]);
        return $res;
    }

    /**
     * 获取小程序版本信息
     * @param int $site_id
     * @return array
     */
    public function getWeappVersion($site_id = 0)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_VERSION' ] ]);
        return $res;
    }

    /**
     * 清除小程序版本信息
     */
    public function clearWeappVersion()
    {
        model('config')->update([ 'value' => '' ], [ [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_VERSION' ] ]);
        Cache::tag("config")->clear();
    }

    /**
     * 设置小程序分享
     * @param $site_id
     * @param $app_module
     * @param $key
     * @param $value
     */
    public function setShareConfig($site_id, $app_module, $key, $value)
    {
        $config = model('config')->getInfo([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'WEAPP_SHARE' ] ], 'value');
        if (!empty($config) && !empty($config[ 'value' ])) $data = json_decode($config[ 'value' ], true);

        if (!empty($data[ $key ][ 'path' ]) && !empty($value[ 'path' ]) && $data[ $key ][ 'path' ] != $value[ 'path' ]) {
            $upload_model = new Upload();
            $upload_model->deletePic($data[ $key ][ 'path' ], $site_id);
        }

        $data[ $key ] = $value;
        $model = new ConfigModel();
        $res = $model->setConfig($data, '小程序分享', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'WEAPP_SHARE' ] ]);
        return $res;
    }

    /**
     * 获取小程序分享配置
     * @param $site_id
     * @param $app_module\
     */
    public function getShareConfig($site_id, $app_module)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'WEAPP_SHARE' ] ]);
        return $res;
    }
}