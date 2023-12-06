<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shopapi\controller;

use app\model\system\Addon as AddonModel;

/**
 * 插件管理
 * @author Administrator
 *
 */
class Addon extends BaseApi
{

    /**
     * 列表信息
     */
    public function lists()
    {
        $addon = new AddonModel();
        $list = $addon->getAddonList();
        return $this->response($list);
    }

    public function addonisexit()
    {
        $addon_api = new \app\api\controller\Addon();
        $res = $addon_api->addonIsExit();
        return $res;
    }

    /**
     * 插件是否存在
     */
    public function isexit()
    {
        $name = $this->params[ 'name' ] ?? '';
        $res = 0;
        if (!empty($name)) $res = addon_is_exit($name, $this->site_id);
        return $this->response($this->success($res));
    }

}