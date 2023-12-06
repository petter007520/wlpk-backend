<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\system;

use app\model\BaseModel;

/**
 * 系统配置
 */
class SystemConfig extends BaseModel
{

    /**
     * 系统配置
     * @param $site_id
     */
    public function getSystemConfig($site_id = 0){
        return $this->success(['is_open_queue' => 0]);
    }

}