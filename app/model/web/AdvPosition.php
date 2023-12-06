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

namespace app\model\web;


use think\facade\Cache;
use app\model\BaseModel;

/**
 * 广告位管理
 * @author Administrator
 *
 */
class AdvPosition extends BaseModel
{
    /**
     * 添加广告位
     * @param array $data
     */
    public function addAdvPosition($data)
    {
        //查询是否有重复关键字
        $condition = [
            [ 'keyword', '=', $data[ 'keyword' ] ]
        ];
        $result = $this->getAdvPositionInfo($condition);
        if (!empty($result[ 'data' ])) return $this->error('', '广告关键字已存在');
        $ap_id = model('adv_position')->add($data);
        Cache::tag("adv_position")->clear();
        return $this->success($ap_id);
    }

    /**
     * 修改广告位
     * @param array $data
     */
    public function editAdvPosition($data, $condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $ap_id = isset($check_condition[ 'ap_id' ]) ? $check_condition[ 'ap_id' ] : '';
        if ($ap_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        //查询是否有重复关键字
        if (isset($data[ 'keyword' ])) {
            $conditions = [
                [ 'keyword', '=', $data[ 'keyword' ] ],
                [ 'ap_id', '<>', $ap_id ],
            ];
            $result = $this->getAdvPositionInfo($conditions);
            if (!empty($result[ 'data' ])) return $this->error('', '广告关键字已存在');
        }
        $res = model('adv_position')->update($data, $condition);
        Cache::tag("adv_position")->clear();
        return $this->success($res);
    }

    /**
     * 删除广告位
     * @param $condition
     * @param $ap_ids
     * @return array
     */
    public function deleteAdvPosition($condition, $ap_ids)
    {
        $list = model('adv_position')->getList([ [ 'ap_id', 'in', $ap_ids ], [ 'is_system', '=', 1 ] ]);
        if ($list) {
            return $this->error('', '删除的广告位存在系统广告位');
        }
        $res = model('adv_position')->delete($condition);
        Cache::tag("adv_position")->clear();
        return $this->success($res);
    }

    /**
     * 获取广告位基础信息
     * @param $condition
     * @param string $file
     * @return array
     */
    public function getAdvPositionInfo($condition, $file = 'ap_id, keyword , ap_name, ap_intro, ap_height, ap_width, default_content, ap_background_color, type,is_system,state')
    {
        $data = json_encode([ $condition ]);
        $cache = Cache::get("adv_position_getAdvPositionInfo_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $res = model('adv_position')->getInfo($condition, $file);
        Cache::tag("adv_position")->set("adv_position_getAdvPositionInfo_" . $data, $res);
        return $this->success($res);
    }

    /**
     * 获取广告位列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getAdvPositionList($condition = [], $field = 'ap_id, keyword , ap_name, ap_intro, ap_height, ap_width, default_content, ap_background_color, type', $order = '', $limit = null)
    {
        $data = json_encode([ $condition, $field, $order, $limit ]);
        $cache = Cache::get("adv_position_getAdvPositionList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('adv_position')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("adv_position")->set("adv_position_getAdvPositionList_" . $data, $list);

        return $this->success($list);
    }

    /**
     * 获取广告位分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getAdvPositionPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'ap_id desc', $field = 'ap_id, keyword , ap_name, ap_intro, ap_height, ap_width, default_content, ap_background_color, type,is_system,state')
    {
        $data = json_encode([ $condition, $field, $order, $page, $page_size ]);
        $cache = Cache::get("adv_position_getAdvPositionPageList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('adv_position')->pageList($condition, $field, $order, $page, $page_size);
        Cache::tag("adv_position")->set("adv_position_getAdvPositionPageList_" . $data, $list);
        return $this->success($list);
    }

}
