<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\express;

use app\model\store\Store;
use app\model\system\Config as ConfigModel;
use app\model\BaseModel;

/**
 * 配送配置
 */
class Config extends BaseModel
{

    /*********************************************************************** 物流配送 start ***********************************************************************/
    /**
     * 物流配送配置
     * @param $site_id
     * @return \multitype
     */
    public function getExpressConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'EXPRESS_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'express_name' => '快递发货'
            ];
        }
        return $res;
    }

    /**
     * 设置物流配送配置
     * @param $data
     * @param $is_use
     * @param $site_id
     * @return array
     */
    public function setExpressConfig($data, $is_use, $site_id)
    {
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        $config = new ConfigModel();
        $res = $config->setConfig($data, '物流配送设置', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'EXPRESS_CONFIG' ] ]);
        return $res;
    }
    /*********************************************************************** 物流配送 end ***********************************************************************/
    /*********************************************************************** 门店自提 start ***********************************************************************/
    /**
     * 门店自提配置
     * @param $site_id
     * @return \multitype
     */
    public function getStoreConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'EXPRESS_STORE_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'store_name' => '门店自提'
            ];
        }
        return $res;
    }

    /**
     * 设置门店自提配置
     * @param $data
     * @param $is_use
     * @param $site_id
     * @return array
     */
    public function setStoreConfig($data, $is_use, $site_id)
    {
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        if ($is_use == 1) {
            $store_model = new Store();
            $check_result = $store_model->checkIscanStoreTrade($site_id);
            if($check_result['code'] < 0)
                return $check_result;
        }
        $config = new ConfigModel();
        $res = $config->setConfig($data, '门店自提配置设置', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'EXPRESS_STORE_CONFIG' ] ]);
        return $res;
    }

    /**
     * 设置门店自提开关
     * @param $is_use
     * @param $site_id
     * @return array
     */
    public function setStoreIsuse($is_use, $site_id, $app_module = 'shop')
    {
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        if ($is_use == 1) {
            $store_model = new Store();
            $check_result = $store_model->checkIscanStoreTrade($site_id);
            if($check_result['code'] < 0)
                return $check_result;
        }

        $config = new ConfigModel();
        $res    = $config->modifyConfigIsUse($is_use, [['site_id', '=', $site_id], ['app_module', '=', $app_module], ['config_key', '=', 'EXPRESS_STORE_CONFIG']]);
        return $res;
    }

    /*********************************************************************** 门店自提 end ***********************************************************************/
    /*********************************************************************** 外卖配送 start ***********************************************************************/

    /**
     * 外卖配送配置
     * @param $site_id
     * @return array
     */
    public function getLocalDeliveryConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'EXPRESS_LOCAL_DELIVERY_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'local_name' => '同城配送'
            ];
        }
        return $res;
    }

    /**
     * 设置外卖配送配置
     * @param $data
     * @param $is_use
     * @param $site_id
     * @return array
     */
    public function setLocalDeliveryConfig($data, $is_use, $site_id)
    {
        if ($site_id === '') {
            return $this->error('', '缺少必须参数站点id');
        }
        if($is_use == 1){
            $local_model = new Local();
            $check_result = $local_model->checkIscanTradeLocal($site_id);
            if($check_result['code'])
                return $check_result;
        }
        $config = new ConfigModel();
        $res = $config->setConfig($data, '同城配送配置设置', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'EXPRESS_LOCAL_DELIVERY_CONFIG' ] ]);
        return $res;
    }
    /*********************************************************************** 外卖配送 end ***********************************************************************/

    /**
     * 外卖配送配置
     * @param $site_id
     * @return array
     */
    public function getDeliverTypeSort($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'DELIVERY_SORT_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'deliver_type' => 'local,express,store'
            ];
        }
        return $res;
    }

    public function setDeliverTypeSort($data, $site_id)
    {
        if ($site_id === '') {
            return $this->error('', '缺少必须参数站点id');
        }
        $config = new ConfigModel();
        $res = $config->setConfig($data, '配置方式排序设置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'DELIVERY_SORT_CONFIG' ] ]);
        return $res;
    }

    /**
     * 配送方式列表
     */
    public function getExpressTypeList($site_id)
    {
        //外卖配送
        $local = $this->getLocalDeliveryConfig($site_id)[ 'data' ][ 'value' ] ?? [];
        $store = $this->getStoreConfig($site_id)[ 'data' ][ 'value' ] ?? [];
        $express = $this->getExpressConfig($site_id)[ 'data' ][ 'value' ] ?? [];

        return [ 'express' => $express[ 'express_name' ], 'store' => $store[ 'store_name' ], 'local' => $local[ 'local_name' ] ];

    }

    /**
     * 获取已启用的配送方式
     * @param $site_id
     */
    public function getEnabledExpressType($site_id){
        $local = $this->getLocalDeliveryConfig($site_id)[ 'data' ];
        $store = $this->getStoreConfig($site_id)[ 'data' ];
        $express = $this->getExpressConfig($site_id)[ 'data' ];

        $express_type = [];
        if ($express['is_use']) {
            $express_type['express'] = [
                'name' => $express['value'][ 'express_name' ],
                'icon' => 'iconwuliu',
                'desc' => '支持'.$express['value'][ 'express_name' ].'的商品在购买后将会通过快递的方式进行配送，可在订单中查看物流信息'
            ];
        }
        if ($store['is_use']) {
            $express_type['store'] = [
                'name' => $store['value'][ 'store_name' ],
                'icon' => 'icondianpu',
                'desc' => '支持'.$store['value'][ 'store_name' ].'的商品在购买后用户可自行到下单时所选择的自提点进行提货'
            ];
        }
        if ($local['is_use']) {
            $express_type['local'] = [
                'name' => $local['value'][ 'local_name' ],
                'icon' => 'iconwaimaifuwu',
                'desc' => '支持'.$local['value'][ 'local_name' ].'的商品在购买后平台将安排配送人员配送到用户指定的收货地点'
            ];
        }
        return $express_type;
    }
}