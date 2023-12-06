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

/**
 * 商品海报
 */
class GoodsPoster extends BaseModel
{
    /**
     * 添加商品海报
     * @param array $data
     */
    public function addPoster($data)
    {
        if (!empty($data[ 'poster_id' ])) {
            $poster_id = model('goods_poster')->update($data, [ [ 'poster_id', '=', $data[ 'poster_id' ], [ 'site_id', '=', $data[ 'site_id' ] ] ] ]);
        } else {
            $poster_id = model('goods_poster')->add($data);
        }
        return $this->success($poster_id);

    }

    /**
     * 删除海报
     * @param int $id
     * @param int $member_id
     */
    public function deletePoster($poster_id, $site_id)
    {
        $res = model('goods_poster')->delete([ [ 'poster_id', '=', $poster_id ], [ 'site_id', '=', $site_id ] ]);
        return $this->success($res);
    }

    /**
     * 获取海报信息
     * @param int $id
     * @param int $member_id
     */
    public function getPosterInfo($poster_id, $site_id)
    {
        $res = model('goods_poster')->getInfo([ [ 'poster_id', '=', $poster_id ], [ 'site_id', '=', $site_id ] ]);
        return $this->success($res);
    }


    /**
     * 获取海报分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getPosterPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'poster_id asc', $field = '*', $alias = 'a', $join = [])
    {
        $list = model('goods_poster')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取社群二维码列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getPosterList($condition = [], $field = '*', $order = 'poster_id asc', $limit = null)
    {
        $list = model('goods_poster')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }
}