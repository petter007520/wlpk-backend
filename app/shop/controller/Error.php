<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;

use app\Controller;

/**
 * 空控制器
 * Class Error
 * @package app\shop\controller
 */
class Error extends Controller
{
    public function __call($method, $args)
    {
        return $this->fetch('error/error');
    }
}
