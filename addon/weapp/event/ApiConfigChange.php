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

use addon\weapp\model\Config;

/**
 * api
 */
class ApiConfigChange
{
    /**
     * api配置变更
     */
    public function handle($param = [])
    {
        $config = new Config();
        $config->clearWeappVersion();
    }
}