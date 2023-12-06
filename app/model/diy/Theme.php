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
 * 自定义模板主题风格配色
 */
class Theme extends BaseModel
{

    /**
     * 添加自定义模板主题风格配色
     * @param $data
     * @return array
     */
    public function addTheme($data)
    {
        $count = model("diy_theme")->getCount([ [ 'name', '=', $data[ 'name' ] ] ]);
        if ($count > 0) {
            return $this->error('', '主题风格已存在');
        }
        $res = model("diy_theme")->add($data);
        return $this->success($res);
    }

    /**
     * 删除自定义模板主题风格配色
     * @param $condition
     * @return array
     */
    public function deleteTheme($condition)
    {
        $res = model("diy_theme")->delete($condition);
        return $this->success($res);
    }

    /**
     * 添加自定义模板主题风格配色
     * @param $data
     * @return array
     */
    public function addThemeList($data)
    {
        foreach ($data as $k => $v) {
            $count = model("diy_theme")->getCount([ [ 'name', '=', $v[ 'name' ] ] ]);
            if ($count > 0) {
                return $this->error('', '主题风格已存在');
            }
        }
        $res = model("diy_theme")->addList($data);
        return $this->success($res);
    }

    /**
     * 编辑自定义模板主题风格配色
     * @param $data
     * @param $condition
     * @return array
     */
    public function editTheme($data, $condition)
    {
        $res = model("diy_theme")->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 获取主题风格数量
     * @param $condition
     * @return array
     */
    public function getThemeCount($condition)
    {
        $res = model('diy_theme')->getCount($condition);
        return $this->success($res);
    }

    /**
     * 获取自定义模板主题风格配色信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getThemeInfo($condition, $field = '*')
    {
        $info = model("diy_theme")->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取一条主题风格
     * @param $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getFirstTheme($condition, $field = '*', $order = '')
    {
        $info = model('diy_theme')->getFirstData($condition, $field, $order);
        return $this->success($info);
    }

    /**
     * 自定义模板主题风格配色列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getThemeList($condition = [], $field = '*', $order = '')
    {
        $list = model("diy_theme")->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 自定义模板主题风格配色列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getThemePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $field = '*', $order = '', $alias = 'a', $join = [])
    {
        $list = model("diy_theme")->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

}