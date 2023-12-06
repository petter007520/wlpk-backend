<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\weapp\event;

use addon\weapp\model\Weapp;

/**
 * 开放数据解密
 */
class DecryptData
{
    /**
     * 执行安装
     */
    public function handle($param = [])
    {
        if ($param['app_type'] == 'weapp') {
            $weapp = new Weapp($param['site_id']);
            return $weapp->decryptData($param);
        }
    }
}