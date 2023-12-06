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

use app\model\system\Config as ConfigModel;
use app\model\BaseModel;

/**
 * 接口api配置
 */
class Api extends BaseModel
{


    /***************************************************************接口api 开始********************************************************/
    /**
     * 获取api配置
     */
    public function getApiConfig($site_id = 1, $app_module = 'shop')
    {
        $config = new ConfigModel();
        $res    = $config->getConfig([['site_id', '=', $site_id], ['app_module', '=', $app_module], ['config_key', '=', 'API_CONFIG']]);
        $res['data']['value']['long_time'] = $res['data']['value']['long_time'] ?? 48;
        return $res;
    }

    /**
     * 设置api配置
     * @param $data
     * @return \multitype
     */
    public function setApiConfig($data, $is_use, $site_id = 1, $app_module = 'shop')
    {
        $old_config = $this->getApiConfig($site_id, $app_module)['data'];

        $config = new ConfigModel();
        $res    = $config->setConfig($data, 'api配置', $is_use, [['site_id', '=', $site_id], ['app_module', '=', $app_module], ['config_key', '=', 'API_CONFIG']]);

        if ($old_config['is_use'] != $is_use || json_encode($old_config['value']) != json_encode($data)) {
            $cron = new Cron();
            $cron->addCron(1, 0, 'api配置更新', 'ApiConfigChange', (time() + 10), 0);
        }
        return $res;
    }
    /***************************************************************接口api 结束********************************************************/

}