<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */
namespace addon\wechat\shop\controller;

use addon\wechat\model\Menu as MenuModel;
use addon\wechat\model\Wechat as WechatModel;

/**
 * 微信菜单控制器
 */
class Menu extends BaseWechat
{
    /**
     * 微信自定义菜单配置
     */
    public function menu()
    {
        if (request()->isAjax()) {
            $menu_model = new MenuModel();
            $menu_info  = $menu_model->getWechatMenuConfig($this->site_id);
            return $menu_info;
        } else {
            return $this->fetch('menu/menu', [], $this->replace);
        }
    }

    /**
     * 修改微信自定义菜单
     */
    public function edit()
    {
        if (request()->isAjax()) {
            $menu_value = input('value', '');
            $menu_json  = input('json_data', '');
            $menu_model = new MenuModel();
            $data       = json_decode($menu_value, true);
            $res        = $menu_model->setWechatMenuConfig($data, $this->site_id);
            if ($res['code'] != 0) {
                return $res;
            }
            $res = $this->sendWeixinMenu($menu_json);

            return $res;
        }
    }

    /**
     * 公众号同步更新微信菜单
     */
    public function sendWeixinMenu($menu_json)
    {
        $wechat_model = new WechatModel($this->site_id);
        $menu_arr     = json_decode($menu_json, true);
        $res          = $wechat_model->menu($menu_arr['button']);
        return $res;
    }

}