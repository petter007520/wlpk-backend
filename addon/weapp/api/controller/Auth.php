<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\weapp\api\controller;

use addon\weapp\model\Weapp;
use app\Controller;
use think\facade\Log;

class Auth extends Controller
{

    public $wechat;

    public function __construct()
    {
        parent::__construct();
        $site_id      = request()->siteid();
        $this->wechat = new Weapp($site_id);
    }

    /**
     * 小程序消息推送
     */
    public function relateWeixin()
    {
        Log::write('微信小程序消息');
        $this->wechat->relateWeixin();
    }


}