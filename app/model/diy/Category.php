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
use think\facade\Cache;

/**
 * 自定义模板分类
 */
class Category extends BaseModel
{

    /**
     * 添加自定义模板分类
     * @param $data
     * @return array
     */
    public function addCategory($data)
    {
        $count = model("diy_template_category")->getCount([ [ 'name', '=', $data[ 'name' ] ], [ 'pid', '=', $data[ 'pid' ] ] ]);
        if ($count > 0) {
            return $this->error('', '模板分类名称已存在');
        }
        $data[ 'create_time' ] = time();
        $data[ 'level' ] = $data[ 'pid' ] ? 2 : 1;
        $res = model("diy_template_category")->add($data);
        return $this->success($res);
    }

    /**
     * 编辑自定义模板分类
     * @param $data
     * @param $condition
     * @return array
     */
    public function editCategory($data, $condition)
    {
        $data[ 'modify_time' ] = time();
        $res = model("diy_template_category")->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除自定义模板分类
     * @param $condition
     * @return array
     */
    public function deleteCategory($condition)
    {
        $id = model('diy_template_category')->getValue($condition, 'category_id');

        $child_id = model('diy_template_category')->getValue([ [ 'pid', '=', $id ] ], 'category_id');

        if (!empty($child_id)) return $this->error('', '当前模板分类存在子模板分类，不可删除');

        $info = model('diy_template_goods')->getInfo([ [ 'category_id', '=', $id ] ], 'category_id');

        if (!empty($info)) return $this->error('', '当前模板分类正在使用，不可删除');

        $res = model("diy_template_category")->delete($condition);
        return $this->success($res);
    }

    /**
     * 获取自定义模板分类信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getCategoryInfo($condition, $field = '*')
    {
        $info = model("diy_template_category")->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 自定义模板分类列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getCategoryTree($condition = [], $order = 'create_time desc', $field = 'category_id, name, pid, level, state')
    {
        $list = model("diy_template_category")->getList($condition, $field, $order);
        $category_list = [];

        //遍历一级商品分类
        foreach ($list as $k => $v) {
            if ($v[ 'level' ] == 1) {
                $category_list[] = $v;
                unset($list[ $k ]);
            }
        }

        $list = array_values($list);

        //遍历二级商品分类
        foreach ($list as $k => $v) {
            foreach ($category_list as $ck => $cv) {
                if ($v[ 'level' ] == 2 && $cv[ 'category_id' ] == $v[ 'pid' ]) {
                    $category_list[ $ck ][ 'child_list' ][] = $v;
                    unset($list[ $k ]);
                }
            }
        }
        return $this->success($category_list);
    }

    /**
     * 自定义模板分类列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getCategoryList($condition = [], $field = '*', $order = '')
    {
        $list = model("diy_template_category")->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 自定义模板分类列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getCategoryPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $field = '*', $order = '', $alias = 'a', $join = [])
    {
        $list = model("diy_template_category")->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

}