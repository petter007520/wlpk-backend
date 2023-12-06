<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\discount\shop\controller;

use app\shop\controller\BaseShop;

class BaseController extends BaseShop
{
    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
    }

    public function fetch($template = '', $replace = [], $config = [])
    {
        $config = array_merge($config, [
            'SHOP_ADDON_CSS' => __ROOT__ . '/addon/discount/shop/view/public/css/',
            'SHOP_ADDON_IMG' => __ROOT__ . '/addon/discount/shop/view/public/img/',
            'SHOP_ADDON_JS' => __ROOT__ . '/addon/discount/shop/view/public/js/',
        ]);
        return parent::fetch($template, $replace, $config);
    }
}