<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\goods;

use app\model\BaseModel;
use app\model\system\Document as DocumentModel;
use app\model\system\Config as SystemConfig;

/**
 * 商品设置
 */
class Config extends BaseModel
{

    /**
     * 获取售后保障设置
     */
    public function getAfterSaleConfig($site_id)
    {
        $document = new DocumentModel();
        $info     = $document->getDocument([['site_id', '=', $site_id], ['app_module', '=', 'shop'], ['document_key', '=', "GOODS_AFTER_SALE"]]);
        $config = (new SystemConfig())->getConfig([['site_id', '=', $site_id], ['app_module', '=', 'shop'], ['config_key', '=', 'GOODS_AFTER_SALE']]);
        $info['data']['is_display'] = empty($config['data']['value']) ? 0 : $config['data']['value']['is_display'];
        return $info;
    }

    /**
     * 设置售后保障
     * @param $title
     * @param $content
     * @param $site_id
     * @return \app\model\system\multitype
     */
    public function setAfterSaleConfig($title, $content, $site_id, $is_display = 0)
    {
        $document = new DocumentModel();
        $res      = $document->setDocument($title, $content, [['site_id', '=', $site_id], ['app_module', '=', 'shop'], ['document_key', '=', "GOODS_AFTER_SALE"]]);
        (new SystemConfig())->setConfig(['is_display' => $is_display], '售后保障是否显示', 1, [['site_id', '=', $site_id], ['app_module', '=', 'shop'], ['config_key', '=', 'GOODS_AFTER_SALE']]);
        return $res;
    }

}