<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace app\model\diy;

use app\model\BaseModel;
use app\model\web\DiyView;

/**
 * 模板页面类型、模板组
 */
class Template extends BaseModel
{

    /**
     * 添加自定义模板类型
     * @param $data
     * @return array
     */
    public function addTemplate($data)
    {
        $res = model('diy_template')->add($data);
        if ($res) {
            return $this->success($res);
        } else {
            return $this->error($res);
        }
    }

    /**
     * 添加多个自定义模板类型
     * @param $data
     * @return array
     */
    public function addTemplateList($data)
    {
        $res = model('diy_template')->addList($data);
        if ($res) {
            return $this->success($res);
        } else {
            return $this->error($res);
        }
    }

    /**
     * 修改自定义模板类型
     * @param $data
     * @param $condition
     * @return array
     */
    public function editTemplate($data, $condition)
    {
        $res = model('diy_template')->update($data, $condition);
        if ($res) {
            return $this->success($res);
        } else {
            return $this->error($res);
        }
    }

    /**
     * 删除自定义模板类型
     * @param $condition
     * @return array
     */
    public function deleteTemplate($condition)
    {
        $res = model('diy_template')->delete($condition);
        if ($res) {
            return $this->success($res);
        } else {
            return $this->error($res);
        }
    }

    /**
     * 获取模板页面类型数量
     * @param $condition
     * @return array
     */
    public function getTemplateCount($condition)
    {
        $res = model('diy_template')->getCount($condition);
        return $this->success($res);
    }

    /**
     * 模板类型信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getTemplateInfo($condition = [], $field = '*')
    {
        $info = model('diy_template')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 模板类型列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getTemplateList($condition = [], $field = '*', $order = 'sort asc')
    {
        $list = model('diy_template')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 模板类型分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getTemplatePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = model('diy_template')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 添加模板组
     * @param $data
     * @return array
     */
    public function addTemplateGoods($data)
    {
        $data[ 'create_time' ] = time();
        $res = model('diy_template_goods')->add($data);
        return $this->success($res);
    }

    /**
     * 编辑模板组
     * @param $data
     * @param $condition
     * @return array
     */
    public function editTemplateGoods($data, $condition)
    {
        $data[ 'modify_time' ] = time();
        $res = model('diy_template_goods')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除模板组
     * @param $condition
     * @return array
     */
    public function deleteTemplateGoods($condition)
    {
        $goods_ids = model('diy_template_goods')->getColumn($condition, 'goods_id');
        $res = model('diy_template_goods')->delete($condition);
        if (!empty($goods_ids)) {
            $count = model('site_diy_template')->getCount([ [ 'template_goods_id', 'in', $goods_ids ] ]);
            if ($count) return $this->error('', '模板正在使用中不可删除');

            model('diy_template_goods_item')->delete([ [ 'goods_id', 'in', $goods_ids ] ]);
        }
        return $this->success($res);
    }

    /**
     * 获取模板组数量
     * @param $condition
     * @return array
     */
    public function getTemplateGoodsCount($condition)
    {
        $res = model('diy_template_goods')->getCount($condition);
        return $this->success($res);
    }

    /**
     * 获取模板组
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getTemplateGoodsInfo($condition, $field = '*')
    {
        $info = model('diy_template_goods')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取一条模板组
     * @param $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getFirstTemplateGoods($condition, $field = '*', $order = '')
    {
        $info = model('diy_template_goods')->getFirstData($condition, $field, $order);
        return $this->success($info);
    }

    /**
     * 模板组列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getTemplateGoodsList($condition = [], $field = '*', $order = '')
    {
        $res = model('diy_template_goods')->getList($condition, $field, $order);
        return $this->success($res);
    }

    /**
     * 模板组列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getTemplateGoodsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $field = '*', $order = 'create_time desc', $alias = '', $join = [])
    {

        $res = model('diy_template_goods')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($res);
    }

    /**
     * 添加模板页面
     * @param $data
     * @return array
     */
    public function addTemplateGoodsItem($data)
    {
        $data[ 'create_time' ] = time();
        $res = model('diy_template_goods_item')->add($data);
        return $this->success($res);
    }

    /**
     * 添加多个模板页面
     * @param $data
     * @return array
     */
    public function addTemplateGoodsItemList($data)
    {
        $res = model('diy_template_goods_item')->addList($data);
        return $this->success($res);
    }

    /**
     * 编辑模板页面
     * @param $data
     * @param $condition
     * @return array
     */
    public function editTemplateGoodsItem($data, $condition)
    {
        $data[ 'modify_time' ] = time();
        $res = model('diy_template_goods_item')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除模板页面
     * @param $condition
     * @return array
     */
    public function deleteTemplateGoodsItem($condition)
    {
        $res = model('diy_template_goods_item')->delete($condition);
        return $this->success($res);
    }

    /**
     * 获取模板页面
     * @param $condition
     * @param string $field
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getTemplateGoodsItemInfo($condition, $field = '*', $alias = '', $join = [])
    {
        $info = model('diy_template_goods_item')->getInfo($condition, $field, $alias, $join);
        return $this->success($info);
    }

    /**
     * 模板页面列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getTemplateGoodsItemList($condition = [], $field = '*', $order = 'create_time desc')
    {
        $list = model('diy_template_goods_item')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 模板页面分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getTemplateGoodsItemPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $field = '*', $order = 'create_time desc')
    {
        $list = model('diy_template_goods_item')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 使用模板
     * @param $params
     * @return array
     */
    public function useTemplate($params)
    {
        try {

            model('diy_template_goods')->startTrans();

            $template_goods_info = model('diy_template_goods')->getInfo([ [ 'goods_id', '=', $params[ 'goods_id' ] ] ], 'goods_id,name,addon_name');
            if (empty($template_goods_info)) {
                return $this->error('', '模板组不存在');
            }

            // 添加店铺模板组关联关系
            $count = $this->getSiteDiyTemplateCount([ [ 'site_id', '=', $params[ 'site_id' ] ], [ 'template_goods_id', '=', $params[ 'goods_id' ] ] ])[ 'data' ];
            if ($count == 0) {
                $this->addSiteDiyTemplate([
                    'name' => $template_goods_info[ 'name' ],
                    'site_id' => $params[ 'site_id' ],
                    'template_goods_id' => $template_goods_info[ 'goods_id' ],
                    'addon_name' => $template_goods_info[ 'addon_name' ]
                ]);
            } else {
                $this->modifySiteDiyTemplateIsDefault([
                    'site_id' => $params[ 'site_id' ],
                    'template_goods_id' => $template_goods_info[ 'goods_id' ]
                ]);
            }

            $diy_view_model = new DiyView();

            // 查询模板页面列表，遍历添加到站点页面中
            $item_list = $this->getTemplateGoodsItemList([
                [ 'goods_id', '=', $params[ 'goods_id' ] ]
            ], 'goods_item_id, goods_id, title, name, value, addon_name')[ 'data' ];
            if (!empty($item_list)) {
                foreach ($item_list as $k => $v) {

                    // 查询页面类型
                    $template_info = $this->getTemplateInfo([
                        [ 'name', '=', $v[ 'name' ] ]
                    ], 'name,title')[ 'data' ];

                    $type = 'DIY_PAGE';
                    $type_name = '自定义页面';
                    if (!empty($template_info)) {
                        $type = $template_info[ 'name' ];
                        $type_name = $template_info[ 'title' ];
                    }

                    $site_diy_view_info = $diy_view_model->getSiteDiyViewInfo([
                        [ 'site_id', '=', $params[ 'site_id' ] ],
                        [ 'name', '=', $v[ 'name' ] ],
                        [ 'template_id', '=', $v[ 'goods_id' ] ],
                        [ 'template_item_id', '=', $v[ 'goods_item_id' ] ],
                        [ 'addon_name', '=', $v[ 'addon_name' ] ],
                        [ 'type', '=', $type ],
                    ], 'id')[ 'data' ];

                    $site_diy_data = [
                        'site_id' => $params[ 'site_id' ],
                        'name' => $v[ 'name' ], // 模板标识
                        'title' => $v[ 'title' ], // 模板名称
                        'template_id' => $v[ 'goods_id' ], // 所属模板id
                        'template_item_id' => $v[ 'goods_item_id' ], // 模板页面id
                        'value' => $v[ 'value' ], // 模板数据，json格式
                        'addon_name' => $v[ 'addon_name' ],
                        'type' => $type,
                        'type_name' => $type_name,
                        'is_default' => 1
                    ];

                    // 清除默认页面
                    $diy_view_model->editSiteDiyView([ 'is_default' => 0 ], [
                        [ 'site_id', '=', $params[ 'site_id' ] ],
                        [ 'name', '=', $v[ 'name' ] ]
                    ]);

                    // 检测模板页面是否存在，有则改，无则加
                    if (!empty($site_diy_view_info)) {
                        // 修改相同页面的默认标识
                        $site_diy_data[ 'modify_time' ] = time();
                        $diy_view_model->editSiteDiyView($site_diy_data, [ [ 'id', '=', $site_diy_view_info[ 'id' ] ] ]);
                    } else {
                        $site_diy_data[ 'create_time' ] = time();
                        $diy_view_model->addSiteDiyView($site_diy_data);
                    }

                }
            }

            // 累加模板组使用次数
            model('diy_template_goods')->setInc([ [ 'goods_id', '=', $params[ 'goods_id' ] ] ], 'use_num');
            model('diy_template_goods')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('diy_template_goods')->rollback();
            return $this->error('', 'File：' . $e->getFile() . '，Line：' . $e->getLine() . '，Message：' . $e->getMessage() . ',Code：' . $e->getCode());
        }
    }

    /**
     * 添加店铺关联模板组
     * @param $data
     * @return array
     */
    public function addSiteDiyTemplate($data)
    {
        $data[ 'create_time' ] = time();
        $data[ 'is_default' ] = 1; // 设置店铺默认模板
        model('site_diy_template')->update([ 'is_default' => 0 ], [ [ 'site_id', '=', $data[ 'site_id' ] ] ]);
        $res = model('site_diy_template')->add($data);
        return $this->success($res);
    }

    /**
     * 修改店铺关联模板组
     * @param $data
     * @param $condition
     * @return array
     */
    public function editSiteDiyTemplate($data, $condition)
    {
        $res = model('site_diy_template')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 添加店铺关联模板组
     * @param $data
     * @return array
     */
    public function modifySiteDiyTemplateIsDefault($data)
    {
        model('site_diy_template')->update([ 'is_default' => 0 ], [ [ 'site_id', '=', $data[ 'site_id' ] ] ]);
        $res = model('site_diy_template')->update([ 'is_default' => 1 ], [ [ 'site_id', '=', $data[ 'site_id' ] ], [ 'template_goods_id', '=', $data[ 'template_goods_id' ] ] ]);
        return $this->success($res);
    }

    /**
     * 删除店铺关联模板组
     * @param $condition
     * @return array
     */
    public function deleteSiteDiyTemplate($condition)
    {
        $res = model('site_diy_template')->delete($condition);
        return $this->success($res);
    }

    /**
     * 查询店铺关联模板组数量
     * @param $condition
     * @return array
     */
    public function getSiteDiyTemplateCount($condition)
    {
        $res = model('site_diy_template')->getCount($condition);
        return $this->success($res);
    }

    /**
     * 查询店铺关联模板组信息
     * @param $condition
     * @param $field
     * @return array
     */
    public function getSiteDiyTemplateInfo($condition, $field = 'id,name,template_goods_id,is_default')
    {
        $res = model('site_diy_template')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 店铺关联模板组列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getSiteDiyTemplateList($condition = [], $field = '*', $order = 'create_time desc')
    {
        $list = model('site_diy_template')->getList($condition, $field, $order);
        return $this->success($list);
    }

}