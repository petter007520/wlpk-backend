<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\system;

use think\facade\Cache;
use app\model\BaseModel;

/**
 * 菜单表
 * @author Administrator
 *
 */
class Menu extends BaseModel
{
    public $list = [];

    /***************************************** 系统菜单开始*****************************************************************************/
    /**
     * 获取菜单列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getMenuList($condition = [], $field = 'id, app_module, title, name, parent, level, url, is_show, sort, is_icon, picture, picture_select, is_control', $order = '', $limit = null)
    {

        $data = json_encode([ $condition, $field, $order, $limit ]);
        $cache = Cache::get("getMenuList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('menu')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("menu")->set("getMenuList_" . $data, $list);

        return $this->success($list);
    }

    /**
     * 获取菜单数量
     * @param $condition
     * @return array
     */
    public function getMenuCount($condition)
    {
        $count = model('menu')->getCount($condition);
        return $this->success($count);
    }

    /**
     * 获取菜单树
     * @param number $level 层级 0不限层级
     */
    public function menuTree($level = 0, $menu_type = 1)
    {
        $condition = [];
        if ($level > 0) {
            $condition = [
                [ 'level', 'elt', $level ]
            ];
        }
        $list = $this->getMenuList($condition, 'id, app_module, title, name, parent, level, url, is_show, sort, is_icon, picture, picture_select, is_control', 'sort asc');
        $tree = list_to_tree($list[ 'data' ], 'menu_id', 'parent', 'child_list');
        return $this->success($tree);
    }

    /**
     * 通过主键获取菜单信息
     * @param unknown $menu_id
     * @return multitype:string mixed
     */
    public function getMenuInfo($condition, $field = 'id, app_module, name, title, parent, level, url, is_show, sort, `desc`, picture, is_icon, picture_select, is_control, addon')
    {

        $data = json_encode([ $condition, $field ]);
        $cache = Cache::get("getMenuInfo_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $menu_info = model('menu')->getInfo($condition, $field);
        Cache::tag("menu")->set("getMenuInfo_" . $data, $menu_info);
        return $this->success($menu_info);
    }

    /**
     * 通过url和端口查询对应菜单信息
     * @param unknown $url
     * @param unknown $app_module
     * @param $addon 插件名称
     */
    public function getMenuInfoByUrl($url, $app_module, $addon = '')
    {

        $cache = Cache::get("getMenuInfoByUrl_" . $url . "_" . $app_module . '_' . $addon);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $info = model('menu')->getFirstData([ [ 'url', "=", $url ], [ 'app_module', "=", $app_module ] ], 'id, app_module, name, title, parent, level, url, is_show, sort, `desc`, picture, is_icon, picture_select, is_control, addon', 'level desc');

        if (empty($info)) {
            return $this->error();
        }
        Cache::tag("menu")->set("getMenuInfoByUrl_" . $url . "_" . $app_module . '_' . $addon, $info);
        return $this->success($info);
    }


    /**
     * 刷新菜单
     * @param $app_module
     * @param $addon
     * @return array
     */
    public function refreshMenu($app_module, $addon)
    {
        try {

            if (empty($addon)) {
                $tree_name = 'config/menu_' . $app_module . '.php';
            } else {
                $tree_name = 'addon/' . $addon . '/config/menu_' . $app_module . '.php';

            }
            model('menu')->delete([ [ 'app_module', "=", $app_module ], [ 'addon', "=", $addon ] ]);

            $tree = require $tree_name;

            $list = $this->getAddonMenuList($tree, $app_module, $addon);
            if (!empty($list)) {
                $res = model('menu')->addList($list);
                return $this->success($res);
            } else {
                return $this->success();
            }
        } catch (\Exception $e) {
            halt($list);
        }

    }

    /**
     * 刷新收银端权限
     * @param $addon
     * @return array
     */
    public function refreshCashierAuth($addon){
        $tree_name = 'addon/' . $addon . '/config/cashier_auth.php';
        if (!file_exists($tree_name)) return $this->error();

        $tree = require $tree_name;
        if (!$tree) return $this->error();

        model('cashier_auth')->delete([ ['addon', '=', $addon] ]);

        $list = [];
        $this->getCashierAuthList($tree, $addon, '', $list);
        $res = model('cashier_auth')->addList($list);
        // 清除缓存
        Cache::clear('cashier_menu');
        return $this->success();
    }

    /**
     * 获取收银端权限集
     * @param $addon
     * @param  array  $list
     */
    private function getCashierAuthList($tree, $addon, $parent = '', &$list = []){
        foreach ($tree as $item) {
            $children = $item['children'] ?? [];
            if (isset($item['children'])) unset($item['children']);

            $item = array_merge($item, ['addon' => $addon, 'parent' => $item['parent'] ?? $parent ]);
            ksort($item);
            array_push($list, $item);

            if (!empty($children)) $this->getCashierAuthList($children, $addon, $item['name'], $list);
        }
    }

    /**
     * 刷新全部菜单
     */
    public function refreshAllMenu()
    {
        $shop_menu_res = $this->refreshMenu("shop", '');
        $addon_model = new Addon();
        $addon_list = $addon_model->getAddonList([], 'name');
        $addon_list = $addon_list[ 'data' ];
        foreach ($addon_list as $k_addon => $v_addon) {
            $addon_menu_res = $this->refreshMenu('shop', $v_addon[ 'name' ]);
        }
    }

    /**
     * 刷新店铺端菜单
     * @param $addon
     * @param string $app_module
     * @return array|int
     */
    public function cacheMenu($addon, $app_module = 'shop')
    {
        if (!empty($addon)) {
            $tree_name = 'addon/' . $addon . '/config/menu_' . $app_module . '.php';
        } else {
            $tree_name = $addon . '/config/menu_' . $app_module . '.php';
        }

        if (file_exists($tree_name)) {
            model('menu')->delete([ [ 'app_module', "=", $app_module ], [ 'addon', "=", $addon ] ]);
            $tree = require $tree_name;
            $list = $this->getAddonMenuList($tree, $app_module, $addon);
            if (!empty($list)) {
                $res = model('menu')->addList($list);
                return $res;
            } else {
                return $this->success();
            }
        } else {
            return $this->success();
        }
    }

    /**
     * 获取菜单
     * @param unknown $tree
     * @param unknown $port
     */
    public function getAddonMenuList($tree, $app_module, $addon)
    {
        try {
            $list = [];
            if (!$tree) {
                return [];
            }

            foreach ($tree as $k => $v) {
                $parent = '';
                if (isset($v[ 'parent' ])) {
                    if ($v[ 'parent' ] == '') {
                        $parent = '';
                        $level = 1;
                    } else {
                        $parent_menu_info = model('menu')->getInfo([
                            [ 'name', "=", $v[ 'parent' ] ]
                        ]);
                        if ($parent_menu_info) {
                            $parent = $parent_menu_info[ 'name' ];
                            $level = $parent_menu_info[ 'level' ] + 1;
                        } else {
                            $level = 1;
                        }
                    }
                } else {
                    $parent = '';
                    $level = 1;
                }
                $item = [
                    'app_module' => $app_module,
                    'addon' => $addon,
                    'title' => $v[ 'title' ],
                    'name' => $v[ 'name' ],
                    'parent' => $parent,
                    'level' => $level,
                    'url' => $v[ 'url' ],
                    'is_show' => isset($v[ 'is_show' ]) ? $v[ 'is_show' ] : 1,
                    'sort' => isset($v[ 'sort' ]) ? $v[ 'sort' ] : 100,
                    'is_icon' => isset($v[ 'is_icon' ]) ? $v[ 'is_icon' ] : 0,
                    'picture' => isset($v[ 'picture' ]) ? $v[ 'picture' ] : '',
                    'picture_select' => isset($v[ 'picture_selected' ]) ? $v[ 'picture_selected' ] : '',
                    'is_control' => isset($v[ 'is_control' ]) ? $v[ 'is_control' ] : 1,
                    'desc' => isset($v[ 'desc' ]) ? $v[ 'desc' ] : '',
                ];

                array_push($list, $item);

                if (isset($v[ 'child_list' ])) {
                    $this->list = [];
                    $this->menuTreeToList($v[ 'child_list' ], $app_module, $addon, $v[ 'name' ], $level + 1);
                    $list = array_merge($list, $this->list);
                }
            }
            return $list;

        } catch (\Exception $e) {
            return json(error(-1, $e->getMessage() . ",File：" . $e->getFile() . "，line：" . $e->getLine()));
        }
    }

    /**
     * 菜单树转化为列表
     * @param unknown $tree
     * @param unknown $module
     * @param unknown $port
     * @param string $pid
     * @param number $level
     */
    private function menuTreeToList($tree, $app_module, $addon = '', $parent = '', $level = 1)
    {
        if (is_array($tree)) {
            foreach ($tree as $key => $value) {
                $item = [
                    'app_module' => $app_module,
                    'addon' => $addon,
                    'title' => $value[ 'title' ],
                    'name' => $value[ 'name' ],
                    'parent' => $parent,
                    'level' => $level,
                    'url' => $value[ 'url' ],
                    'is_show' => isset($value[ 'is_show' ]) ? $value[ 'is_show' ] : 1,
                    'sort' => isset($value[ 'sort' ]) ? $value[ 'sort' ] : 100,
                    'is_icon' => isset($value[ 'is_icon' ]) ? $value[ 'is_icon' ] : 0,
                    'picture' => isset($value[ 'picture' ]) ? $value[ 'picture' ] : '',
                    'picture_select' => isset($value[ 'picture_selected' ]) ? $value[ 'picture_selected' ] : '',
                    'is_control' => isset($value[ 'is_control' ]) ? $value[ 'is_control' ] : 1,
                    'desc' => isset($value[ 'desc' ]) ? $value[ 'desc' ] : '',
                ];
                $refer = $value;
                if (isset($refer[ 'child_list' ])) {
                    unset($refer[ 'child_list' ]);
                    array_push($this->list, $item);
                    $p_name = $refer[ 'name' ];
                    $this->menuTreeToList($value[ 'child_list' ], $app_module, $addon, $p_name, $level + 1);
                } else {
                    array_push($this->list, $item);
                }
            }
        }
    }


}