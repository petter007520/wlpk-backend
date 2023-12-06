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

use think\facade\Cache;
use app\model\BaseModel;

/**
 * 运费模板
 */
class ExpressTemplate extends BaseModel
{
    /**
     * 添加运费模板（控制每个站点最多10条）
     * @param $data
     * @param $items
     * @param $shipping_items
     * @return array
     */
    public function addExpressTemplate($data, $items, $shipping_items)
    {
        $count = model('express_template')->getCount([ 'site_id' => $data[ 'site_id' ] ]);
        if ($count >= 10) {
            return $this->error('', 'TEMPLATE_TO_LONG');
        }

        if ($data[ 'is_default' ] == 1) {
            model('express_template')->update([ 'is_default' => 0 ], [ 'site_id' => $data[ 'site_id' ] ]);
        }
        if (empty($data[ 'appoint_free_shipping' ])) {
            $data[ 'shipping_surplus_area_ids' ] = '';
            $shipping_items = [];
        }

        //模板基础信息
        $data[ 'create_time' ] = time();
        $template_id = model('express_template')->add($data);

        //具体模板信息
        foreach ($items as $k => $v) {
            $data_item = $v;
            $data_item[ 'template_id' ] = $template_id;
            $data_item[ 'fee_type' ] = $data[ 'fee_type' ];
            if ($data_item[ 'area_ids' ] && $data_item[ 'area_names' ]) {
                model('express_template_item')->add($data_item);
            }
        }

        foreach ($shipping_items as $k => $v) {
            $data_item = $v;
            $data_item[ 'template_id' ] = $template_id;
            if ($data_item[ 'area_ids' ] && $data_item[ 'area_names' ]) {
                model('express_template_free_shipping')->add($data_item);
            }
        }
        return $this->success($template_id);
    }

    /**
     * 修改系统运费模板
     * @param $data
     * @param $items
     * @param $shipping_items
     * @return array
     */
    public function editExpressTemplate($data, $items, $shipping_items)
    {
        //设置默认
        if ($data[ 'is_default' ] == 1) {
            model('express_template')->update([ 'is_default' => 0 ], [ 'site_id' => $data[ 'site_id' ] ]);
        }

        $data[ 'modify_time' ] = time();
        $res = model('express_template')->update($data, [ [ 'template_id', '=', $data[ 'template_id' ] ] ]);
        if (empty($data[ 'appoint_free_shipping' ])) {
            $data[ 'shipping_surplus_area_ids' ] = '';
            $shipping_items = [];
        }

        //具体模板信息
        model('express_template_item')->delete([ [ 'template_id', '=', $data[ 'template_id' ] ] ]);
        foreach ($items as $k => $v) {
            $data_item = $v;
            $data_item[ 'template_id' ] = $data[ 'template_id' ];
            $data_item[ 'fee_type' ] = $data[ 'fee_type' ];
            if ($data_item[ 'area_ids' ] && $data_item[ 'area_names' ]) {
                model('express_template_item')->add($data_item);
            }
        }

        //具体模板信息
        model('express_template_free_shipping')->delete([ [ 'template_id', '=', $data[ 'template_id' ] ] ]);
        foreach ($shipping_items as $k => $v) {
            $data_item = $v;
            $data_item[ 'template_id' ] = $data[ 'template_id' ];
            if ($data_item[ 'area_ids' ] && $data_item[ 'area_names' ]) {
                model('express_template_free_shipping')->add($data_item);
            }
        }

        return $this->success($res);
    }

    /**
     * 删除系统运费模板
     * @param int $template_id
     */
    public function deleteExpressTemplate($template_id, $site_id)
    {
        $res = model('express_template')->delete([ [ 'template_id', 'in', $template_id ], [ 'site_id', '=', $site_id ] ]);
        if ($res) {
            model('express_template_item')->delete([ [ 'template_id', 'in', $template_id ] ]);
            model('express_template_free_shipping')->delete([ [ 'template_id', 'in', $template_id ] ]);
        }
        return $this->success($res);
    }


    /**
     * 设置默认运费模板
     * @param int $template_id
     */
    public function updateDefaultExpressTemplate($template_id, $is_default, $site_id)
    {
        if ($is_default == 1) {
            model('express_template')->update([ 'is_default' => 0 ], [ 'site_id' => $site_id ]);
        }
        $res = model('express_template')->update([ 'is_default' => 1 ], [ 'template_id' => $template_id ]);
        return $this->success($res);
    }

    /**
     * 获取运费模板信息
     * @param $template_id
     * @param $site_id
     * @return array
     */
    public function getExpressTemplateInfo($template_id, $site_id)
    {

        $res = model('express_template')->getInfo([ [ 'template_id', '=', $template_id ], [ 'site_id', '=', $site_id ] ], 'template_id, site_id, template_name, fee_type, create_time, modify_time, is_default, surplus_area_ids, appoint_free_shipping, shipping_surplus_area_ids');
        if ($res) {
            $res[ 'template_item' ] = model('express_template_item')->getList([ [ 'template_id', '=', $template_id ] ], '*');
            $res[ 'shipping_template_item' ] = model('express_template_free_shipping')->getList([ [ 'template_id', '=', $template_id ] ], '*');
        }
        return $this->success($res);
    }

    /**
     * 获取默认运费模板
     * @param $site_id
     * @return array
     */
    public function getDefaultTemplate($site_id)
    {

        $res = model('express_template')->getInfo([ [ 'is_default', '=', 1 ], [ 'site_id', '=', $site_id ] ], 'template_id, site_id, template_name, fee_type, create_time, modify_time, is_default');
        if ($res) {
            $res[ 'template_item' ] = model('express_template_item')->getList([ [ 'template_id', '=', $res[ 'template_id' ] ] ], '*');
            $res[ 'shipping_template_item' ] = model('express_template_free_shipping')->getList([ [ 'template_id', '=', $res[ 'template_id' ] ] ], '*');
        }
        return $this->success($res);
    }

    /**
     * 获取运费模板列表（主表查询）
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getExpressTemplateList($condition = [], $field = 'template_id, site_id, template_name, fee_type, create_time, modify_time, is_default', $order = '', $limit = null)
    {

        $list = model('express_template')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取运费模板列表（主表查询）
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getExpressTemplatePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = 'template_id, site_id, template_name, fee_type, create_time, modify_time, is_default')
    {
        $list = model('express_template')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取运费模板地域运费列表（主表查询）
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getExpressTemplateItemList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $data = json_encode([ $condition, $field, $order, $limit ]);
        $cache = Cache::get("express_template_getExpressTemplateItemList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('express_template_item')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("express_template_")->set("express_template_getExpressTemplateItemList_" . $data, $list);

        return $this->success($list);
    }

}