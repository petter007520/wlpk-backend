<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 山西牛酷信息科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\system;


use app\model\BaseModel;
use app\model\system\Config as ConfigModel;

/**
 * 客服配置
 */
class Servicer extends BaseModel
{
    /**
     * 设置客服配置
     * @param $data
     * @return array
     */
    public function setServicerConfig($data)
    {
        $config_model = new ConfigModel();
        $res = $config_model->setConfig($data, '客服配置', 1, [ [ 'site_id', '=', 1 ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'SRRVICER_ROOT_CONFIG' ] ]);
        return $res;
    }

    /**
     * 获取客服配置
     */
    public function getServicerConfig()
    {
        $config_model = new ConfigModel();
        $res = $config_model->getConfig([ [ 'site_id', '=', 1 ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'SRRVICER_ROOT_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'h5' => [
                    'type' => 'none'
                ],
                'weapp' => [
                    'type' => 'none'
                ],
                'pc' => [
                    'type' => 'none'
                ]
            ];
        }
        $res[ 'data' ][ 'value' ][ 'h5' ][ 'type' ] = $res[ 'data' ][ 'value' ][ 'h5' ][ 'type' ] ?? 'none';
        $res[ 'data' ][ 'value' ][ 'weapp' ][ 'type' ] = $res[ 'data' ][ 'value' ][ 'weapp' ][ 'type' ] ?? 'none';
        $res[ 'data' ][ 'value' ][ 'pc' ][ 'type' ] = $res[ 'data' ][ 'value' ][ 'pc' ][ 'type' ] ?? 'none';
        return $res;
    }
}
