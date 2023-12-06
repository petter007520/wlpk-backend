<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\system\Wechat\event;

use addon\system\Wechat\sitehome\controller\Material;
use think\facade\View;

class MaterialMannager
{
    /**
     * 图文消息管理
     */
    public function handle($param = [])
    {
        $material     = new Material();
        $result       = $material->materialMannager();
        $return_array = array_merge($result[1], $param);
        return View::fetch($result[0], $return_array, $result[2]);
    }
}