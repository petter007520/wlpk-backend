<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace app\model\goods;

use app\model\BaseModel;

/**
 * 商品品牌
 */
class GoodsBrand extends BaseModel
{

    /**
     * 添加品牌
     * @param array $data
     */
    public function addBrand($data)
    {
        $data[ 'create_time' ] = time();
        $brand_id = model('goods_brand')->add($data);
        return $this->success($brand_id);
    }

    /**
     * 修改品牌
     * @param array $data
     * @return multitype:string
     */
    public function editBrand($data, $condition)
    {
        $res = model('goods_brand')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除品牌
     * @param array $condition
     */
    public function deleteBrand($condition)
    {
        $res = model('goods_brand')->delete($condition);
        return $this->success($res);
    }

    /**
     * 修改排序
     * @param int $sort
     * @param int $brand_id
     */
    public function modifyBrandSort($sort, $condition)
    {
        $res = model('goods_brand')->update([ 'sort' => $sort ], $condition);
        return $this->success($res);
    }

    /**
     * 获取品牌信息
     * @param array $condition
     * @param string $field
     */
    public function getBrandInfo($condition, $field = 'brand_id, brand_name, brand_initial, image_url, sort, create_time, is_recommend')
    {
        $res = model('goods_brand')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取品牌列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getBrandList($condition = [], $field = 'brand_id, brand_name, brand_initial, image_url, sort, create_time', $order = '', $limit = null)
    {
        $list = model('goods_brand')->getList($condition, $field, $order, '', '', '', $limit);

        return $this->success($list);
    }

    /**
     * 获取品牌分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getBrandPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'ngb.create_time desc', $field = 'ngb.brand_id,ngb.brand_name,ngb.brand_initial,ngb.image_url,ngb.sort,ngb.create_time,ngb.is_recommend,ngb.site_id,ns.site_name,nsp.title as supply_name')
    {
        $list = model('goods_brand')->pageList($condition, $field, $order, $page, $page_size, '', '');
        return $this->success($list);
    }

}