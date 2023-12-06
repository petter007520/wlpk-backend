<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechat\model;

use think\facade\Cache;
use app\model\BaseModel;

/**
 * 微信素材管理
 */
class Material extends BaseModel
{

    /**
     * 添加微信素材
     * @param array $data
     */
    public function addMaterial($data)
    {
        $res = model('wechat_media')->add($data);
        return $this->success($res);
    }

    /**
     * 修改微信素材
     * @param array $data
     * @param array $condition
     * @return multitype:string mixed
     */
    public function editMaterial($data, $condition)
    {
        $res = model('wechat_media')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除微信素材
     * @param array $condition
     * @return multitype:string mixed
     */
    public function deleteMaterial($condition)
    {
        $res = model('wechat_media')->delete($condition);
        return $this->success($res);
    }

    /**
     * 获取微信素材信息
     * @param array $condition
     * @param string $field
     * @return multitype:string mixed
     */
    public function getMaterialInfo($condition, $field = '*')
    {

        $res = model('wechat_media')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取微信素材列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getMaterialList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $res = model('wechat_media')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($res);
    }

    /**
     * 获取微信素材分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     * @return multitype:string mixed
     */
    public function getMaterialPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'update_time desc', $field = '*')
    {
        $list = model('wechat_media')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

}