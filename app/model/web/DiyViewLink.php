<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\web;

use app\model\BaseModel;
use app\model\web\DiyView as DiyViewModel;
use think\facade\Cache;

class DiyViewLink extends BaseModel
{
    public $list = [];

    /**
     * 获取链接信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getLinkInfo($condition, $field = '*')
    {
        $list = model('link')->getInfo($condition, $field);
        return $this->success($list);
    }

    /**
     * 获取链接列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getLinkList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $list = model('link')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 链接分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getLinkPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $data = json_encode([ $condition, $page, $page_size, $order, $field ]);
        $cache = Cache::get("link_getLinkPageList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('link')->pageList($condition, $field, $order, $page, $page_size);
        Cache::tag("link")->set("link_getLinkPageList_" . $data, $list);
        return $this->success($list);
    }

    /**
     * 查询自定义微页面
     * @param $site_id
     * @return array
     */
    public function getMicroPageLinkList($site_id)
    {
        $diy_view_model = new DiyViewModel();

        $condition = [
            [ 'site_id', '=', $site_id ],
            [ 'name', 'like', '%DIY_VIEW_RANDOM_%' ]
        ];

        $data = json_encode($condition);
        $cache = Cache::get("site_diy_view_getMicroPageLinkList_" . $site_id . "_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $site_diy_view_list = $diy_view_model->getSiteDiyViewList($condition, 'sort desc,create_time desc', 'name, title')[ 'data' ];

        $link_mic = [
            'name' => 'MICRO_PAGE_LIST',
            'title' => '微页面',
            'parent' => 'MICRO_PAGE',
            'child_list' => []
        ];
        foreach ($site_diy_view_list as $page_k => $page_v) {
            $link_mic[ 'child_list' ][] = [
                'name' => $page_v[ 'name' ],
                'title' => $page_v[ 'title' ],
                'parent' => 'MICRO_PAGE_LIST',
                'wap_url' => '/pages_tool/index/diy?name=' . $page_v[ 'name' ]
            ];
        }
        Cache::tag("site_diy_view")->set("site_diy_view_getMicroPageLinkList_" . $site_id . "_" . $data, $link_mic);
        return $this->success($link_mic);
    }

    /**
     * 自定义链接树结构
     * @param $params
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getLinkTree($params, $field = 'title, name, addon_name, parent, level, wap_url', $order = 'level asc,sort asc,id asc')
    {
        $condition = [
            [ 'level', '<=', isset($params[ 'level' ]) ? $params[ 'level' ] : 4 ]
        ];

        $data = json_encode([ $condition, $field, $order ]);
        $cache = Cache::get("site_diy_view_getLinkTree_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }

        // 查询全部自定义链接
        $list = model('link')->getList($condition, $field, $order);
        $link_list = [];
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                // 要查询当前站点是否购买此插件
                if ($v[ 'addon_name' ] && !addon_is_exit($v[ 'addon_name' ], $params[ 'site_id' ])) {
                    unset($list[ $k ]);
                    continue;
                }
            }
            $list = array_values($list);

            // 一级
            foreach ($list as $k => $v) {
                if ($v[ 'parent' ] == '') {
                    $link_list [] = $v;
                    unset($list[ $k ]);
                }
            }
            $list = array_values($list);

            // 二级
            foreach ($list as $k => $v) {
                foreach ($link_list as $ck => $cv) {
                    if ($v[ 'level' ] == 2 && $cv[ 'name' ] == $v[ 'parent' ]) {
                        $link_list[ $ck ][ 'child_list' ][] = $v;
                        unset($list[ $k ]);
                    }

                }
            }
            $list = array_values($list);

            // 三级
            foreach ($list as $k => $v) {
                foreach ($link_list as $ck => $cv) {
                    if (!empty($cv[ 'child_list' ])) {
                        foreach ($cv[ 'child_list' ] as $third_k => $third_v) {
                            if ($v[ 'level' ] == 3 && $third_v[ 'name' ] == $v[ 'parent' ]) {
                                $link_list[ $ck ][ 'child_list' ][ $third_k ][ 'child_list' ][] = $v;
                                unset($list[ $k ]);
                            }
                        }
                    }
                }
            }

            $list = array_values($list);

            // 四级
            foreach ($list as $k => $v) {
                foreach ($link_list as $ck => $cv) {
                    if (!empty($cv[ 'child_list' ])) {
                        foreach ($cv[ 'child_list' ] as $third_k => $third_v) {
                            if (!empty($third_v[ 'child_list' ])) {
                                foreach ($third_v[ 'child_list' ] as $four_k => $four_v) {
                                    if ($v[ 'level' ] == 4 && $four_v[ 'name' ] == $v[ 'parent' ]) {
                                        $link_list[ $ck ][ 'child_list' ][ $third_k ][ 'child_list' ][ $four_k ][ 'child_list' ][] = $v;
                                        unset($list[ $k ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // 自定义微页面
        foreach ($link_list as $k => $v) {
            if (isset($v[ 'child_list' ])) {
                foreach ($v[ 'child_list' ] as $ck => $cv) {
                    if ($cv[ 'name' ] == 'MICRO_PAGE') {
                        $link_list[ $k ][ 'child_list' ][ $ck ][ 'child_list' ] = [ $this->getMicroPageLinkList($params[ 'site_id' ])[ 'data' ] ];
                    }
                }
            } else {
                $link_list[ $k ][ 'child_list' ] = [];
            }
        }

        Cache::tag("site_diy_view")->set("site_diy_view_getLinkTree_" . $data, $link_list);
        return $this->success($link_list);
    }

    /**
     * 删除自定义链接
     * @param $condition
     * @return array
     */
    public function deleteLink($condition)
    {
        $res = model('link')->delete($condition);
        return $this->success($res);
    }

    /**
     * @param $tree
     * @param $addon
     * @return array
     */
    public function getViewLinkList($tree, $addon)
    {
        $list = [];
        foreach ($tree as $k => $v) {
            $parent = '';
            $level = 1;
            if (isset($v[ 'parent' ])) {
                $parent_menu_info = model('link')->getInfo([ [ 'name', "=", $v[ 'parent' ] ] ]);
                if ($parent_menu_info) {
                    $parent = $parent_menu_info[ 'name' ];
                    $level = $parent_menu_info[ 'level' ] + 1;
                }
            }

            $item = [
                'title' => $v[ 'title' ],
                'name' => $v[ 'name' ],
                'addon_name' => isset($addon) ? $addon : '',
                'parent' => $parent,
                'level' => $level,
                'wap_url' => isset($v[ 'wap_url' ]) ? $v[ 'wap_url' ] : '',
                'web_url' => isset($v[ 'web_url' ]) ? $v[ 'web_url' ] : '',
                'icon' => isset($v[ 'icon' ]) ? $v[ 'icon' ] : '',
                'support_diy_view' => isset($v[ 'support_diy_view' ]) ? $v[ 'support_diy_view' ] : '',
                'sort' => isset($v[ 'sort' ]) ? $v[ 'sort' ] : 0,
            ];

            array_push($list, $item);
            if (isset($v[ 'child_list' ])) {
                $this->list = [];
                $this->linkTreeToList($v[ 'child_list' ], $addon, $v[ 'name' ], $level + 1);
                $list = array_merge($list, $this->list);
            }
        }
        return $list;
    }

    /**
     * 链接树转化为列表
     * @param $tree
     * @param string $addon
     * @param string $parent
     * @param int $level
     */
    private function linkTreeToList($tree, $addon = '', $parent = '', $level = 1)
    {
        if (is_array($tree)) {
            foreach ($tree as $key => $value) {
                $item = [
                    'title' => $value[ 'title' ],
                    'name' => $value[ 'name' ],
                    'addon_name' => $addon,
                    'parent' => $parent,
                    'level' => $level,
                    'wap_url' => isset($value[ 'wap_url' ]) ? $value[ 'wap_url' ] : '',
                    'web_url' => isset($value[ 'web_url' ]) ? $value[ 'web_url' ] : '',
                    'support_diy_view' => isset($value[ 'support_diy_view' ]) ? $value[ 'support_diy_view' ] : '',
                    'icon' => isset($value[ 'icon' ]) ? $value[ 'icon' ] : '',
                    'sort' => isset($value[ 'sort' ]) ? $value[ 'sort' ] : 0,
                ];
                $refer = $value;
                if (isset($refer[ 'child_list' ])) {
                    unset($refer[ 'child_list' ]);
                    array_push($this->list, $item);
                    $p_name = $refer[ 'name' ];
                    $this->linkTreeToList($value[ 'child_list' ], $addon, $p_name, $level + 1);
                } else {
                    array_push($this->list, $item);
                }
            }
        }
    }
}