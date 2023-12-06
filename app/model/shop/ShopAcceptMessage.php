<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\shop;

use app\model\BaseModel;

/**
 * 商家接受消息设置
 */
class ShopAcceptMessage extends BaseModel
{

    /**
     * 添加商家消息接收会员
     * @param $member_id
     * @param $site_id
     * @return array
     */
    public function addShopAcceptMessage($param)
    {

        $data = [
            'site_id' => $param['site_id'],
            'mobile' => $param['mobile'] ?? '',
            'wx_openid' => $param['wx_openid'] ?? '',
            'nickname' => $param['nickname'],
            'create_time' => time()
        ];

        $res = model('shop_accept_message')->add($data);
        return $this->success($res);
    }

    /**
     * 编辑消息接收人
     * @param $data
     * @param $condition
     * @return array
     */
    public function editShopAcceptMessage($data, $condition)
    {
        $res = model('shop_accept_message')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除商家消息接收会员
     * @param $condition
     * @return array
     */
    public function deleteShopAcceptMessage($condition)
    {
        $res = model('shop_accept_message')->delete($condition);
        return $this->success($res);
    }


    /**
     * 获取商家消息接收会员
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getShopAcceptMessageList($condition = [], $order = '')
    {
        $list = model('shop_accept_message')->getList($condition, '*', $order);
        return $this->success($list);
    }


    /**
     * 获取商家消息接收会员分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getShopAcceptMessagePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'id desc')
    {
        $list = model('shop_accept_message')->pageList($condition, '*', $order, $page, $page_size);
        return $this->success($list);
    }

}