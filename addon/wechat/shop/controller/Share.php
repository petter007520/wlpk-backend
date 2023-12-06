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

/**
 * 微信菜单控制器
 */
class Share extends BaseWechat
{
    /**
     * 微信自定义菜单配置
     */
    public function share()
    {
        if (request()->isAjax()) {

            return success();
        } else {

            return $this->fetch('share/share', [], $this->replace);
        }
    }

}