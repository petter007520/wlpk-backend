<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;

use addon\wechat\model\Wechat;
use app\model\shop\ShopAcceptMessage as ShopAcceptMessageModel;
use think\facade\Cache;

/**
 * 商家接受会员消息管理
 * Class Shopacceptmessage
 * @package app\shop\controller
 */
class Shopacceptmessage extends BaseShop
{

    /**
     * 商家接受会员消息列表
     */
    public function lists()
    {
        if (request()->isAjax()) {

            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);

            $search_text_type = input('search_text_type', 'nickname');
            $search_text = input('search_text', '');

            $condition = [];
            if ($search_text) {
                $condition[] = [ $search_text_type, 'like', '%' . $search_text . '%' ];
            }

            $model = new ShopAcceptMessageModel();
            $list = $model->getShopAcceptMessagePageList($condition, $page, $page_size);

            return $list;
        } else {
            return $this->fetch('shopacceptmessage/lists');
        }
    }

    /**
     * 添加
     */
    public function add()
    {
        if (request()->isAjax()) {

            $model = new ShopAcceptMessageModel();

            $data = [
                'site_id' => $this->site_id,
                'mobile' => input('mobile', ''),
                'wx_openid' => input('wx_openid', ''),
                'nickname' => input('nickname', ''),
            ];
            $res = $model->addShopAcceptMessage($data);
            return $res;
        }
    }

    /**
     * 编辑
     * @return array
     */
    public function edit()
    {
        if (request()->isAjax()) {
            $id = input('id', 0);
            $model = new ShopAcceptMessageModel();
            $data = [
                'mobile' => input('mobile', ''),
                'wx_openid' => input('wx_openid', ''),
                'nickname' => input('nickname', ''),
            ];
            $res = $model->editShopAcceptMessage($data, [ [ 'id', '=', $id ], [ 'site_id', '=', $this->site_id ] ]);
            return $res;
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        if (request()->isAjax()) {
            $model = new ShopAcceptMessageModel();
            $id = input('id', 0);
            $res = $model->deleteShopAcceptMessage([ [ 'id', '=', $id ], [ 'site_id', '=', $this->site_id ] ]);
            return $res;
        }
    }

    /**
     * 创建绑定二维码
     */
    public function createBindQrcode()
    {
        if (request()->isAjax()) {
            $key = 'verify_' . unique_random(6) . $this->site_id;

            $wechat = new Wechat($this->site_id);
            $res = $wechat->getTempQrcode($key, 600);
            if ($res[ 'code' ] != 0) return $res;

            return success(0, '', [ 'key' => $key, 'path' => $res[ 'data' ] ]);
        }
    }

    /**
     * 获取扫码绑定数据
     * @return array
     */
    public function getBindData()
    {
        if (request()->isAjax()) {
            $key = input('key', '');
            $cache = Cache::pull($key);
            if ($cache) {
                return success(0, '', $cache);
            } else {
                return error();
            }
        }
    }

}