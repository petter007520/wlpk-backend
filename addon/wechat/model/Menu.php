<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechat\model;

use app\model\system\Config as ConfigModel;
use app\model\BaseModel;

/**
 * 微信公众号配置
 */
class Menu extends BaseModel
{

    /******************************************************************** 微信公众号菜单配置 start ****************************************************************************/
    /**
     * 设置微信公众号配置
     * @return multitype:string mixed
     */
    public function setWechatMenuConfig($data, $site_id = 0)
    {
        $config = new ConfigModel();
        $res    = $config->setConfig($data, '微信公众号设置', 1, [['site_id', '=', $site_id], ['app_module', '=', 'shop'], ['config_key', '=', 'WECHAT_MENU_CONFIG']]);
        return $res;
    }

    /**
     * 微信公众号菜单配置
     */
    public function getWechatMenuConfig($site_id = 0)
    {
        $config = new ConfigModel();
        $res    = $config->getConfig([['site_id', '=', $site_id], ['app_module', '=', 'shop'], ['config_key', '=', 'WECHAT_MENU_CONFIG']]);
        return $res;
    }
    /******************************************************************** 微信公众号菜单配置 end ****************************************************************************/
}