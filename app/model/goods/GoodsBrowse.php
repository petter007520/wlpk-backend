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

use app\model\member\Member;
use app\model\BaseModel;
use app\model\system\Stat;

/**
 * 商品浏览历史
 */
class GoodsBrowse extends BaseModel
{
    /**
     * 添加商品浏览记录
     * @param array $data
     */
    public function addBrowse($data)
    {
        $res = model('goods_browse')->getInfo([ [ 'member_id', '=', $data[ 'member_id' ] ], [ 'goods_id', '=', $data[ 'goods_id' ] ] ], 'site_id,id');
        $data[ 'browse_time' ] = time();
        $stat = new Stat();
        $stat->switchStat([ 'type' => 'goods_visit', 'data' => [
            'site_id' => $data[ 'site_id' ],
            'goods_id' => $data[ 'goods_id' ],
            'member_id' => $data['member_id']
        ] ]);

        if (!empty($res)) {
            $collect_id = model('goods_browse')->update($data, [ [ 'id', '=', $res[ 'id' ] ] ]);
        } else {
            $collect_id = model('goods_browse')->add($data);
        }
        Member::modifyLastVisitTime($data[ 'member_id' ]);
        //添加浏览统计

        $stat->switchStat([ 'type' => 'visit', 'data' => [
            'visit_count' => 1,
            'site_id' => $data[ 'site_id' ],
            'app_module' => $data[ 'app_module' ] ?? '',
            'goods_id' => $data[ 'goods_id' ],
        ] ]);
        return $this->success($collect_id);

    }

    /**
     * 删除浏览记录
     * @param int $id
     * @param int $member_id
     */
    public function deleteBrowse($id, $member_id)
    {
        $res = model('goods_browse')->delete([ [ 'member_id', '=', $member_id ], [ 'id', 'in', $id ] ]);
        return $this->success($res);
    }

    /**
     * 获取浏览记录分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getBrowsePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'browse_time desc', $field = 'id,member_id,browse_time,goods_id,sku_id', $alias = 'a', $join = [])
    {
        $list = model('goods_browse')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取浏览商品列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getBrowseList($condition = [], $field = '*', $order = 'id asc', $limit = null)
    {
        $list = model('goods_browse')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }
}