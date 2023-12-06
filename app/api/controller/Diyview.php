<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\api\controller;

use app\model\diy\Template;
use app\model\web\DiyView as DiyViewModel;

/**
 * 自定义模板
 * @package app\api\controller
 */
class Diyview extends BaseApi
{

    /**
     * 基础信息
     */
    public function info()
    {
        $id = isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : 0;
        $name = isset($this->params[ 'name' ]) ? $this->params[ 'name' ] : '';

        if (empty($id) && empty($name)) {
            return $this->response($this->error('', 'REQUEST_DIY_ID_NAME'));
        }

        $this->initStoreData();

        // 如果是连锁运营模式，则进入门店页面
        if ($name == 'DIY_VIEW_INDEX' && $this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $name = 'DIY_STORE';
        }

        $diy_view = new DiyViewModel();
        $condition = [
            [ 'site_id', '=', $this->site_id ]
        ];

        if (!empty($id)) {
            $condition[] = [ 'id', '=', $id ];
        } elseif (!empty($name)) {
            $condition[] = [ 'name', '=', $name ];
            $condition[] = [ 'is_default', '=', 1 ];
        }

        $info = $diy_view->getSiteDiyViewDetail($condition);

        if (!empty($info[ 'data' ])) {
            $diy_view->modifyClick([ [ 'id', '=', $info[ 'data' ][ 'id' ] ], [ 'site_id', '=', $this->site_id ] ]);
        }

        // 如果是连锁运营模式，标题显示门店名称
//        if ($name == 'DIY_STORE' && $this->store_data[ 'config' ][ 'store_business' ] == 'store' && $this->store_data[ 'store_info' ]) {
//            $info[ 'data' ][ 'value' ] = json_decode($info[ 'data' ][ 'value' ], true);
//            $info[ 'data' ][ 'value' ][ 'global' ][ 'title' ] = $this->store_data[ 'store_info' ][ 'store_name' ];
//            $info[ 'data' ][ 'value' ] = json_encode($info[ 'data' ][ 'value' ]);
//        }

        return $this->response($info);
    }

    /**
     * 平台端底部导航
     * @return string
     */
    public function bottomNav()
    {
        $site_id = $this->site_id;
        if (empty($site_id)) {
            return $this->response($this->error('', 'REQUEST_SITE_ID'));
        }
        $diy_view = new DiyViewModel();
        $info = $diy_view->getBottomNavConfig($site_id);
        return $this->response($info);
    }

    /**
     * 风格
     */
    public function style()
    {
        $site_id = $this->site_id;
        if (empty($site_id)) {
            return $this->response($this->error('', 'REQUEST_SITE_ID'));
        }
        $diy_view = new DiyViewModel();
        $res = $diy_view->getStyleConfig($this->site_id);
        return $this->response($res);
    }

}