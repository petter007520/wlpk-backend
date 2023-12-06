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
use app\model\diy\Template;
use app\model\diy\Theme;
use app\model\system\Addon;
use app\model\system\Config as ConfigModel;
use think\facade\Cache;

/**
 * 自定义模板
 */
class DiyView extends BaseModel
{

    /**
     * 组件分类
     * @param $type
     * @return mixed
     */
    public function getTypeName($type)
    {
        $arr = [
            'SYSTEM' => '基础组件', // 排序号范围：10000~20000
            'PROMOTION' => '营销组件', // 排序号范围：30000~40000
            'EXTEND' => '扩展组件', // 排序号范围：50000~60000
        ];
        return $arr[ $type ];
    }

    /**
     * 获取图标分类
     * @return array
     */
    public function getIconType()
    {
        $icon_type = $this->getIconAllList()[ 'type' ];
        return $icon_type;
    }

    /**
     * 获取所有图标库数据
     * @param array $params
     * @return array
     */
    private function getIconAllList($params = [])
    {
        $data = json_encode($params);
        $cache = Cache::get("diy_icon_getIconAllList_" . $data);
        if (!empty($cache)) {
            return $cache;
        }

        $condition = [];

        if (!empty($params)) {
            $condition[] = [ 'name', '=', $params[ 'addon_name' ] ];
        }

        $addon = new Addon();
        $addon_list = $addon->getAddonList($condition, 'name')[ 'data' ];

        $icon_list = []; // 自定义图标库列表

        $res = [
            'component' => [], // 组件图标
            'icon' => [], // 自定义图标
            'type' => [] // 图标类型
        ];

        // app下的图标库
        $diy_view_file = 'config/diy_view.php';
        if (file_exists($diy_view_file)) {
            $diy_view = require $diy_view_file;
            if (isset($diy_view[ 'icon_library' ]) && !empty($diy_view[ 'icon_library' ])) {
                $icon_list[] = $diy_view[ 'icon_library' ];
            }
        }

        // 循环插件中的图标库
        foreach ($addon_list as $k => $v) {
            $diy_view_file = 'addon/' . $v[ 'name' ] . '/config/diy_view.php';
            if (file_exists($diy_view_file)) {
                $diy_view = require $diy_view_file;
                if (isset($diy_view[ 'icon_library' ]) && !empty($diy_view[ 'icon_library' ])) {
                    $icon_list[] = $diy_view[ 'icon_library' ];
                }
            }
        }

        foreach ($icon_list as $k => $v) {

            // 组件图标
            if (!empty($v[ 'component' ]) && !empty($v[ 'component' ][ 'name' ]) && !empty($v[ 'component' ][ 'path' ])) {
                $component_name_arr = array_column($res[ 'component' ], 'name');
                // 检测防重复
                if (!empty($v[ 'component' ][ 'name' ]) && !in_array($v[ 'component' ][ 'name' ], $component_name_arr)) {
                    $res[ 'component' ][] = $v[ 'component' ];
                }
            }

            // 自定义图标
            if (!empty($v[ 'icon' ]) && !empty($v[ 'icon' ][ 'name' ]) && !empty($v[ 'icon' ][ 'path' ])) {
                // 检测防重复
                $icon_name_arr = array_column($res[ 'icon' ], 'name');
                if (!empty($v[ 'icon' ][ 'name' ]) && !in_array($v[ 'icon' ][ 'name' ], $icon_name_arr)) {
                    $res[ 'icon' ][] = $v[ 'icon' ];
                }
            }

            // 图标类型
            if (!empty($v[ 'type' ])) {
                $res[ 'type' ] = array_merge($res[ 'type' ], array_filter($v[ 'type' ]));
            }
        }

        Cache::tag("site_diy_view")->set("diy_icon_getIconAllList_" . $data, $res);
        return $res;

    }

    /**
     * 获取自定义图标库列表
     * @param $type
     * @return array
     */
    public function getIconList($type)
    {
        $icon_path = $this->getIconAllList()[ 'icon' ];
        $icon_list = [];
        foreach ($icon_path as $k => $v) {
            if (file_exists($v[ 'path' ])) {
                $fp = fopen($v[ 'path' ], "r");
                $str = fread($fp, filesize($v[ 'path' ])); // 指定读取大小，这里把整个文件内容读取出来
                $exc = '/[.](' . $type . '\S+):before{1}/';// 匹配图标，格式：.icon名字:before
                preg_match_all($exc, $str, $match);
                sort($match[ 1 ]); // 按名称正序排序
                foreach ($match[ 1 ] as $ck => $cv) {
                    $match[ 1 ][ $ck ] = $v[ 'name' ] . ' ' . $cv; // 拼接字体图标名称
                }
                $icon_list = array_merge($icon_list, $match[ 1 ]);

            }
        }
        return $this->success($icon_list);
    }

    /**
     * 获取图标库文件路径
     * @return array
     */
    public function getIconUrl()
    {
        $icon_list = $this->getIconAllList();
        $component_path = $icon_list[ 'component' ]; // 组件图标
        $icon_path = $icon_list[ 'icon' ]; // 自定义图标
        $url = [];
        $arr = array_merge($component_path, $icon_path);
        foreach ($arr as $k => $v) {
            if (!empty($v[ 'path' ])) {
                $url[] = __ROOT__ . '/' . $v[ 'path' ];
            }
        }

        foreach ($url as $k => $v) {
            $url[ $k ] = '<link rel="stylesheet" type="text/css" href="' . $v . '" />';
        }
        return $this->success($url);
    }

    /**
     * 获取扩展组件列表
     * @param array $params
     * @return array|mixed
     */
    public function getExtendComponentList($params = [])
    {
        $data = json_encode($params);
        $cache = Cache::get("diy_extend_component_getExtendComponentList_" . $data);
        if (!empty($cache)) {
            return $cache;
        }

        $condition = [];
        if (!empty($params)) {
            $condition[] = [ 'name', '=', $params[ 'addon_name' ] ];
        }

        $addon = new Addon();
        $addon_list = $addon->getAddonList($condition, 'name')[ 'data' ];

        $component_list = []; // 扩展组件列表

        // 循环插件中的组件
        foreach ($addon_list as $k => $v) {
            $diy_view_file = 'addon/' . $v[ 'name' ] . '/config/diy_view.php';
            if (file_exists($diy_view_file)) {
                $diy_view = require $diy_view_file;
                if (isset($diy_view[ 'component' ]) && !empty($diy_view[ 'component' ])) {
                    foreach ($diy_view[ 'component' ] as $ck => $cv) {
                        if (!empty($cv[ 'name' ]) && !in_array($cv[ 'name' ], $component_list)) {
                            $cv[ 'path' ] = 'addon/' . $v[ 'name' ] . '/uniapp/' . $cv[ 'path' ];
                            $component_list[] = $cv;
                        }
                    }
                }
            }
        }

        Cache::tag("site_diy_view")->set("diy_extend_component_getExtendComponentList_" . $data, $component_list);
        return $component_list;
    }

    /**
     * 获取uni-app页面列表
     * @param array $params
     * @return array|mixed
     */
    public function getUniAppPageList($params = [])
    {
        $data = json_encode($params);
        $cache = Cache::get("diy_pages_getUniAppPageList_" . $data);
        if (!empty($cache)) {
            return $cache;
        }

        $condition = [];
        if (!empty($params)) {
            $condition[] = [ 'name', '=', $params[ 'addon_name' ] ];
        }

        $addon = new Addon();
        $addon_list = $addon->getAddonList($condition, 'name')[ 'data' ];

        $page_list = []; // 页面列表

        // 循环插件中的页面
        foreach ($addon_list as $k => $v) {
            $diy_view_file = 'addon/' . $v[ 'name' ] . '/config/diy_view.php';
            if (file_exists($diy_view_file)) {
                $diy_view = require $diy_view_file;
                if (isset($diy_view[ 'pages' ]) && !empty($diy_view[ 'pages' ])) {
                    foreach ($diy_view[ 'pages' ] as $ck => $cv) {
                        if (!empty($cv[ 'path' ]) && !in_array($cv[ 'path' ], $page_list)) {
                            $cv[ 'route' ] = $cv[ 'path' ]; // 路由
                            $cv[ 'path' ] = 'addon/' . $v[ 'name' ] . '/uniapp/' . $cv[ 'path' ] . '.vue'; // 源文件路径
                            $page_list[] = $cv;
                        }

                    }
                }
            }
        }

        Cache::tag("site_diy_view")->set("diy_pages_getUniAppPageList_" . $data, $page_list);
        return $page_list;
    }

    /**
     * 添加组件
     * @param $data
     * @return array
     */
    public function addUtil($data)
    {
        $res = model('diy_view_util')->add($data);
        return $this->success($res);
    }

    /**
     * 添加多个组件
     * @param $data
     * @return array
     */
    public function addUtilList($data)
    {
        $res = model('diy_view_util')->addList($data);
        return $this->success($res);
    }

    /**
     * 编辑组件
     * @param $data
     * @param $condition
     * @return array
     */
    public function editUtil($data, $condition)
    {
        $res = model('diy_view_util')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除自定义组件
     * @param $condition
     * @return array
     */
    public function deleteUtil($condition)
    {
        $res = model('diy_view_util')->delete($condition);
        return $this->success($res);
    }

    /**
     * 查询组件信息
     * @param $condition
     * @param $field
     * @return array
     */
    public function getUtilInfo($condition, $field)
    {
        $info = model('diy_view_util')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 查询组件数量
     * @param $condition
     * @return array
     */
    public function getUtilCount($condition)
    {
        $info = model('diy_view_util')->getCount($condition);
        return $this->success($info);
    }

    /**
     * 获取自定义模板组件集合
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     * @return array
     */
    public function getDiyViewUtilList($condition = [], $field = 'id,name,title,type,value,addon_name,support_diy_view,max_count,is_delete,icon', $order = 'sort asc', $limit = null)
    {
        $res = model('diy_view_util')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($res);
    }

    /**
     * 添加自定义模板
     * @param $data
     * @return array
     */
    public function addSiteDiyView($data)
    {
        // 将同类页面的默认值改为0，默认页面只有一个
        if (!empty($data[ 'is_default' ])) {
            model("site_diy_view")->update([ 'is_default' => 0 ], [ [ 'site_id', '=', $data[ 'site_id' ] ], [ 'name', '=', $data[ 'name' ] ] ]);
        }
        $data[ 'create_time' ] = time();
        $res = model('site_diy_view')->add($data);
        if ($res) {
            Cache::tag("site_diy_view")->clear();
            return $this->success($res);
        } else {
            return $this->error($res);
        }
    }

    /**
     * 添加多条自定义模板数据
     * @param $data
     * @return array
     */
    public function addSiteDiyViewList($data)
    {
        $res = model('site_diy_view')->addList($data);
        if ($res) {
            Cache::tag("site_diy_view")->clear();
            return $this->success($res);
        } else {
            return $this->error($res);
        }
    }

    /**
     * 修改自定义模板
     * @param array $data
     * @param array $condition
     * @return array
     */
    public function editSiteDiyView($data, $condition)
    {
        // 将同类页面的默认值改为0，默认页面只有一个
        if (!empty($data[ 'is_default' ])) {
            model("site_diy_view")->update([ 'is_default' => 0 ], [ [ 'site_id', '=', $data[ 'site_id' ] ], [ 'name', '=', $data[ 'name' ] ] ]);
        }
        $data[ 'modify_time' ] = time();
        $res = model('site_diy_view')->update($data, $condition);
        if ($res) {
            Cache::tag("site_diy_view")->clear();
            return $this->success($res);
        } else {
            return $this->error($res);
        }
    }

    /**
     * 删除站点微页面
     * @param array $condition
     * @return array
     */
    public function deleteSiteDiyView($condition = [])
    {
        $res = model('site_diy_view')->delete($condition);
        if ($res) {
            Cache::tag("site_diy_view")->clear();
            return $this->success($res);
        } else {
            return $this->error($res);
        }
    }

    /**
     * 获取自定义模板数据集合
     * @param array $condition
     * @param string $order
     * @param string $field
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getSiteDiyViewList($condition = [], $order = '', $field = '*', $alias = '', $join = [])
    {
        $res = model('site_diy_view')->getList($condition, $field, $order, $alias, $join);
        return $this->success($res);
    }

    /**
     * 获取自定义模板分页数据集合
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getSiteDiyViewPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $res = model('site_diy_view')->rawPageList($condition, $field, $order, $page, $page_size);
        return $this->success($res);
    }

    /**
     * 获取自定义模板信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getSiteDiyViewInfo($condition = [], $field = 'id,site_id,name,title,value')
    {
        $data = json_encode([ $condition, $field ]);
        $cache = Cache::get("site_diy_view_getSiteDiyViewInfo_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }

        $info = model('site_diy_view')->getInfo($condition, $field);

        Cache::tag("site_diy_view")->set("diy_view_getSiteDiyViewInfo_" . $data, $info);
        return $this->success($info);
    }

    /**
     * 获取自定义模板详细信息
     * @param array $condition
     * @return array
     */
    public function getSiteDiyViewDetail($condition = [])
    {
        $condition = array_column($condition, 2, 0);
        $site_id = $condition[ 'site_id' ];
        $data = json_encode($condition);
        $cache = Cache::get("diy_view_getSiteDiyViewDetail_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }

        $field = 'id,site_id,name,title,template_id,template_item_id,type,type_name,value,is_default';
        $info = model('site_diy_view')->getInfo($condition, $field);

        if (!empty($info) && !empty($info[ 'value' ])) {

            // 查询模板页面类型
            $diy_template = new Template();
            $diy_template_info = $diy_template->getTemplateInfo([ [ 'name', '=', $info[ 'name' ] ] ], 'title,name,rule')[ 'data' ];

            $util_condition = [];

            if (!empty($diy_template_info)) {
                $diy_template_info[ 'rule' ] = json_decode($diy_template_info[ 'rule' ], true);

                // 支持的自定义页面（为空表示公共组件都支持）
                if (!empty($diy_template_info[ 'rule' ][ 'support' ])) {
                    $util_condition[] = [ 'support_diy_view', 'in', $diy_template_info[ 'rule' ][ 'support' ], 'or' ];
                }

                // 组件类型
                if (!empty($diy_template_info[ 'rule' ][ 'util_type' ])) {
                    $util_condition[] = [ 'type', 'in', $diy_template_info[ 'rule' ][ 'util_type' ] ];
                }
            } else {
                // 自定义页面，只查询公共组件
                $util_condition[] = [ 'support_diy_view', '=', '' ];
            }

            $json_data = json_decode($info[ 'value' ], true);
            foreach ($json_data[ 'value' ] as $k => $v) {
                if (!empty($v[ 'addonName' ])) {
                    $is_exit = addon_is_exit($v[ 'addonName' ], $site_id);
                    // 检查插件是否存在
                    if ($is_exit == 0) {
                        unset($json_data[ 'value' ][ $k ]);
                        continue;
                    }
                }

                // 检测页面是否支持当前组件
                $util = $util_condition;
                $util[] = [ 'name', '=', $v[ 'componentName' ] ];

                $count = $this->getUtilCount($util)[ 'data' ];
                if ($count == 0) {
                    unset($json_data[ 'value' ][ $k ]);
                    continue;
                }

            }
            $json_data[ 'value' ] = array_values($json_data[ 'value' ]);
            $info[ 'value' ] = json_encode($json_data);
        }
        Cache::tag("site_diy_view")->set("diy_view_getSiteDiyViewDetail_" . $data, $info);
        return $this->success($info);
    }

    /**
     * 获取自定义页面数量
     * @param array $condition
     * @return array
     */
    public function getSiteViewCount($condition = [])
    {
        $count = model('site_diy_view')->getCount($condition);
        return $this->success($count);
    }

    /**
     * 设置平台端的底部导航配置
     * @param $data
     * @param $site_id
     * @return array
     */
    public function setBottomNavConfig($data, $site_id)
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '店铺端自定义底部导航', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'DIY_VIEW_SHOP_BOTTOM_NAV_CONFIG_SHOP_' . $site_id ] ]);
        return $res;
    }

    /**
     * 获取平台端的底部导航配置
     * @param $site_id
     * @return array
     */
    public function getBottomNavConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'DIY_VIEW_SHOP_BOTTOM_NAV_CONFIG_SHOP_' . $site_id ] ]);

        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                "type" => 1,
                "theme" => "default",
                "backgroundColor" => "#FFFFFF",
                "textColor" => "#333333",
                "textHoverColor" => "#FF4D4D",
                "bulge" => true,
                "list" => [
                    [
                        "iconPath" => "icondiy icon-system-shouyeweixuanzhongbeifen",
                        "selectedIconPath" => "icondiy icon-system-shouyexuanzhongbeifen2",
                        "text" => "主页",
                        "link" => [
                            "name" => "INDEX",
                            "title" => "主页",
                            "wap_url" => "/pages/index/index",
                            "parent" => "MALL_LINK"
                        ],
                        "id" => "h1lx8nhr2lc0",
                        "imgWidth" => "40",
                        "imgHeight" => "40",
                        "iconClass" => "icon-system-home",
                        "icon_type" => "icon",
                        "selected_icon_type" => "icon",
                        "style" => [
                            "fontSize" => 100,
                            "iconBgColor" => [],
                            "iconBgColorDeg" => 0,
                            "iconBgImg" => "",
                            "bgRadius" => 0,
                            "iconColor" => [ "#000000" ],
                            "iconColorDeg" => 0
                        ],
                        "selected_style" => [
                            "fontSize" => 100,
                            "iconBgColor" => [],
                            "iconBgColorDeg" => 0,
                            "iconBgImg" => "",
                            "bgRadius" => 0,
                            "iconColor" => [ "#FF4D4D" ],
                            "iconColorDeg" => 0
                        ]
                    ],
                    [
                        "iconPath" => "icondiy icon-system-fenleiweixuanzhongbeifen2",
                        "selectedIconPath" => "icondiy icon-system-fenleixuanzhongbeifen1",
                        "text" => "商品分类",
                        "link" => [
                            "name" => "SHOP_CATEGORY",
                            "title" => "商品分类",
                            "wap_url" => "/pages/goods/category",
                            "parent" => "MALL_LINK"
                        ],
                        "imgWidth" => "40",
                        "imgHeight" => "40",
                        "id" => "1dasmaqndsyo0",
                        "iconClass" => "icon-system-category",
                        "icon_type" => "icon",
                        "selected_icon_type" => "icon",
                        "style" => [
                            "fontSize" => 100,
                            "iconBgColor" => [],
                            "iconBgColorDeg" => 0,
                            "iconBgImg" => "",
                            "bgRadius" => 0,
                            "iconColor" => [ "#000000" ],
                            "iconColorDeg" => 0
                        ],
                        "selected_style" => [
                            "fontSize" => 100,
                            "iconBgColor" => [],
                            "iconBgColorDeg" => 0,
                            "iconBgImg" => "",
                            "bgRadius" => 0,
                            "iconColor" => [ "#FF4D4D" ],
                            "iconColorDeg" => 0
                        ]
                    ],
                    [
                        "iconPath" => "icondiy icon-system-cart",
                        "selectedIconPath" => "icondiy icon-system-cart-selected",
                        "text" => "购物车",
                        "link" => [
                            "name" => "SHOPPING_TROLLEY",
                            "title" => "购物车",
                            "wap_url" => "/pages/goods/cart",
                            "parent" => "MALL_LINK"
                        ],
                        "imgWidth" => "40",
                        "imgHeight" => "40",
                        "id" => "1p1pm6ebtvs00",
                        "iconClass" => "icon-system-cart",
                        "icon_type" => "icon",
                        "selected_icon_type" => "icon",
                        "style" => [
                            "fontSize" => 100,
                            "iconBgColor" => [],
                            "iconBgColorDeg" => 0,
                            "iconBgImg" => "",
                            "bgRadius" => 0,
                            "iconColor" => [ "#000000" ],
                            "iconColorDeg" => 0
                        ],
                        "selected_style" => [
                            "fontSize" => 100,
                            "iconBgColor" => [],
                            "iconBgColorDeg" => 0,
                            "iconBgImg" => "",
                            "bgRadius" => 0,
                            "iconColor" => [ "#FF4D4D" ],
                            "iconColorDeg" => 0
                        ]
                    ],
                    [
                        "iconPath" => "icondiy icon-system-my",
                        "selectedIconPath" => "icondiy icon-system-my-selected",
                        "text" => "我的",
                        "link" => [
                            "name" => "MEMBER_CENTER",
                            "title" => "会员中心",
                            "wap_url" => "/pages/member/index",
                            "parent" => "MALL_LINK"
                        ],
                        "imgWidth" => "40",
                        "imgHeight" => "40",
                        "id" => "1b2tc256egsg0",
                        "iconClass" => "icon-system-my",
                        "icon_type" => "icon",
                        "selected_icon_type" => "icon",
                        "style" => [
                            "fontSize" => 100,
                            "iconBgColor" => [],
                            "iconBgColorDeg" => 0,
                            "iconBgImg" => "",
                            "bgRadius" => 0,
                            "iconColor" => [ "#000000" ],
                            "iconColorDeg" => 0
                        ],
                        "selected_style" => [
                            "fontSize" => 100,
                            "iconBgColor" => [],
                            "iconBgColorDeg" => 0,
                            "iconBgImg" => "",
                            "bgRadius" => 0,
                            "iconColor" => [ "#FF4D4D" ],
                            "iconColorDeg" => 0
                        ]
                    ]
                ],
                "imgType" => 2,
                "iconColor" => "#333333",
                "iconHoverColor" => "#FF4D4D"
            ];
        }

        return $res;
    }

    /**
     * 设置店铺风格配置
     * @param $data
     * @param $site_id
     * @return array
     */
    public function setStyleConfig($data, $site_id)
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '店铺风格设置', '1', [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'SHOP_STYLE_CONFIG' ] ]);
        return $res;
    }

    /**
     * 获取店铺风格配置
     * @param $site_id
     * @return array
     */
    public function getStyleConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'SHOP_STYLE_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $diy_theme_model = new Theme();
            $theme = $diy_theme_model->getFirstTheme([], 'id,title,name,main_color,aux_color', 'id asc')[ 'data' ];
            $res[ 'data' ][ 'value' ] = $theme;
        }
        return $res;
    }

    /**
     * 推广二维码
     * @param $params
     * @return array
     */
    public function qrcode($params)
    {
        $site_id = isset($params[ 'site_id' ]) ? $params[ 'site_id' ] : 0;
        $app_type = isset($params[ 'app_type' ]) ? $params[ 'app_type' ] : 'all';

        $condition = [
            [ 'site_id', '=', $params[ 'site_id' ] ],
        ];

        if (isset($params[ 'id' ])) {
            $condition[] = [ 'id', '=', $params[ 'id' ] ];
        }

        $diy_view_info = $this->getSiteDiyViewInfo($condition, 'id,name,is_default,template_id,template_item_id')[ 'data' ];
        if (empty($diy_view_info)) {
            return $this->success();
        }

        $page_path = '/pages_tool/index/diy';
        if ($diy_view_info[ 'name' ] == 'DIY_VIEW_GOODS_CATEGORY') {
            $page_path = '/pages/goods/category'; // 商品分类页面特殊处理
        }
        if ($diy_view_info[ 'name' ] == 'DIY_VIEW_MEMBER_INDEX') {
            $page_path = '/pages/member/index'; // 会员中心页面特殊处理
        }
        if ($diy_view_info[ 'name' ] == 'DIY_VIEW_INDEX' || $diy_view_info[ 'name' ] == 'DIY_STORE') {
            $page_path = ''; // 首页、门店页面特殊处理
        }

        $data = [
            'app_type' => $app_type, // all为全部
            'type' => isset($params[ 'type' ]) ? $params[ 'type' ] : 'create', // 类型 create创建 get获取
            'site_id' => $site_id,
            'data' => [
                'id' => $diy_view_info[ 'id' ],
                "name" => $diy_view_info[ 'name' ]
            ],
            'page' => $page_path,
            'qrcode_path' => 'upload/qrcode/diy',
            'qrcode_name' => "diy_qrcode_" . $diy_view_info[ 'name' ] . '_' . $diy_view_info[ 'template_id' ] . '_' . $diy_view_info[ 'template_item_id' ] . '_' . $site_id,
        ];

        event('Qrcode', $data, true);
        if ($app_type == 'all') {
            $app_type_list = config('app_type');
        } else {
            $app_type_list = [
                'h5' => []
            ];
        }

        $path = [];
        $config = new ConfigModel();

        foreach ($app_type_list as $k => $v) {
            switch ( $k ) {
                case 'h5':
                    $h5_domain = getH5Domain();
                    $path[ $k ][ 'status' ] = 1;
                    if ($diy_view_info[ 'name' ] == 'DIY_VIEW_INDEX' || $diy_view_info[ 'name' ] == 'DIY_VIEW_GOODS_CATEGORY' || $diy_view_info[ 'name' ] == 'DIY_VIEW_MEMBER_INDEX') {
                        $path[ $k ][ 'url' ] = $h5_domain . $page_path;
                        if ($diy_view_info[ 'is_default' ] == 0) {
                            $path[ $k ][ 'url' ] .= '?id=' . $diy_view_info[ 'id' ];
                        }
                    } else {
                        $path[ $k ][ 'url' ] = $h5_domain . $page_path . '?name=' . $diy_view_info[ 'name' ] . '&id=' . $diy_view_info[ 'id' ];
                    }

                    $path[ $k ][ 'img' ] = $data[ 'qrcode_path' ] . '/' . $data[ 'qrcode_name' ] . "_" . $k . ".png?" . time();
                    break;
                case 'weapp':
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信小程序';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }

                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信小程序';
                    }
                    break;
                case 'wechat':
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信公众号';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }
                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信公众号';
                    }
                    break;
            }

        }

        $return = [
            'path' => $path
        ];

        return $this->success($return);
    }

    /**
     * 根据页面路径生成二维码
     * @param $condition
     * @param string $type
     * @return array
     */
    public function qrcodeRoute($params)
    {
        $site_id = isset($params[ 'site_id' ]) ? $params[ 'site_id' ] : 0;
        $app_type = isset($params[ 'app_type' ]) ? $params[ 'app_type' ] : 'all';

        $data = [
            'app_type' => $app_type, // all为全部
            'type' => isset($params[ 'type' ]) ? $params[ 'type' ] : 'create', // 类型 create创建 get获取
            'site_id' => $site_id,
            'data' => [
                "name" => $params[ 'name' ],
            ],
            'page' => $params[ 'path' ],
            'qrcode_path' => 'upload/qrcode/diy',
            'qrcode_name' => "diy_qrcode_" . $params[ 'name' ] . '_' . $site_id,
        ];

        $qrcode_result = event('Qrcode', $data, true);
        $app_type_list = config('app_type');
        if (!empty($app_type) && $app_type != 'all' && in_array($app_type, [ 'h5', 'wechat', 'weapp' ])) {
            $app_type_list = [
                $app_type => []
            ];
        }

        $path = [];
        $config = new ConfigModel();

        foreach ($app_type_list as $k => $v) {
            switch ( $k ) {
                case 'h5':
                case 'wechat':
                    $h5_domain = getH5Domain();
                    $path[ $k ][ 'status' ] = 1;
                    $path[ $k ][ 'url' ] = $h5_domain . $params[ 'path' ];
                    $path[ $k ][ 'img' ] = $data[ 'qrcode_path' ] . '/' . $data[ 'qrcode_name' ] . "_" . $k . ".png?" . time();
                    break;
                case 'weapp':
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if ($qrcode_result[ 'code' ] >= 0) {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $qrcode_result[ 'data' ][ 'path' ];
                        } else {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = $qrcode_result[ 'message' ];
                        }
                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信小程序';
                    }
                    break;
            }

        }

        $return = [
            'path' => $path
        ];

        return $this->success($return);
    }

    /**
     * 设为使用
     * @param $port
     * @param $type
     * @param $id
     * @param $site_id
     * @return array
     */
    public function setUse($id, $site_id)
    {
        model('site_diy_view')->startTrans();
        try {
            $info = model('site_diy_view')->getInfo([ [ 'id', '=', $id ], [ 'site_id', '=', $site_id ] ], 'name');
            if (empty($info)) {
                return $this->error('页面不存在');
            }

            model('site_diy_view')->update([ 'is_default' => 0 ], [ [ 'name', '=', $info[ 'name' ] ], [ 'site_id', '=', $site_id ] ]);
            model('site_diy_view')->update([ 'is_default' => 1 ], [ [ 'id', '=', $id ], [ 'site_id', '=', $site_id ] ]);
            Cache::tag("site_diy_view")->clear();
            model('site_diy_view')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('site_diy_view')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 修改微页面排序
     * @param $sort
     * @param $id
     * @return array
     */
    public function modifyDiyViewSort($sort, $id)
    {
        $res = model('site_diy_view')->update([ 'sort' => $sort ], [ [ 'id', '=', $id ] ]);
        Cache::tag("site_diy_view")->clear();
        return $this->success($res);
    }

    /**
     * 修改微页面点击量
     * @param $condition
     * @return array
     */
    public function modifyClick($condition)
    {
        model("site_diy_view")->setInc($condition, 'click_num', 1);
        return $this->success(1);
    }

    /**
     * 图标风格
     * @return array
     */
    public function iconStyle()
    {
        return [
//            [
//                "fontSize" => 50,
//                "iconBgColor" => [
//                    "#7b00ff"
//                ],
//                "iconBgColorDeg" => 0,
//                "iconBgImg" => "",
//                "bgRadius" => 19,
//                "iconColor" => [
//                    "#fff"
//                ],
//                "iconColorDeg" => 0
//            ],
            [
                "fontSize" => 50,
                "iconBgColor" => [
                    "#0068ff"
                ],
                "iconBgColorDeg" => 0,
                "iconBgImg" => "",
                "bgRadius" => 38,
                "iconColor" => [
                    "#fff"
                ],
                "iconColorDeg" => 0
            ],
            [
                "fontSize" => 50,
                "iconBgColor" => [
                    "#ff1c1c"
                ],
                "iconBgColorDeg" => 0,
                "iconBgImg" => "",
                "bgRadius" => 50,
                "iconColor" => [
                    "#fff"
                ],
                "iconColorDeg" => 0
            ],
            [
                "fontSize" => 50,
                "iconBgColor" => [
                    "#fa6400"
                ],
                "iconBgColorDeg" => 0,
                "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_01.png",
                "bgRadius" => 19,
                "iconColor" => [
                    "#fff"
                ],
                "iconColorDeg" => 0
            ],
            [
                "fontSize" => 50,
                "iconBgColor" => [
                    "#b620e0"
                ],
                "iconBgColorDeg" => 0,
                "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_02.png",
                "bgRadius" => 19,
                "iconColor" => [
                    "#fff"
                ],
                "iconColorDeg" => 0
            ],
            [
                "fontSize" => 50,
                "iconBgColor" => [
                    "#ff3c5a"
                ],
                "iconBgColorDeg" => 0,
                "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_03.png",
                "bgRadius" => 19,
                "iconColor" => [
                    "#fff"
                ],
                "iconColorDeg" => 0
            ],
            [
                "fontSize" => 50,
                "iconBgColor" => [
                    "#ff9200"
                ],
                "iconBgColorDeg" => 0,
                "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_04.png",
                "bgRadius" => 19,
                "iconColor" => [
                    "#fff"
                ],
                "iconColorDeg" => 0
            ],
            [
                "fontSize" => 50,
                "iconBgColor" => [
                    "#44d7b6"
                ],
                "iconBgColorDeg" => 0,
                "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_05.png",
                "bgRadius" => 38,
                "iconColor" => [
                    "#fff"
                ],
                "iconColorDeg" => 0
            ],
            [
                "fontSize" => 50,
                "iconBgColor" => [
                    "#ff5615"
                ],
                "iconBgColorDeg" => 0,
                "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                "bgRadius" => 50,
                "iconColor" => [
                    "#fff"
                ],
                "iconColorDeg" => 0
            ],
            [
                "fontSize" => 100,
                "iconBgColor" => [],
                "iconBgColorDeg" => 0,
                "iconBgImg" => "",
                "bgRadius" => 0,
                "iconColor" => [
                    "#be71ff",
                    "#8e00ff"
                ],
                "iconColorDeg" => 125
            ]
        ];
    }

    /**
     * 编译uni-app，生成压缩包下载
     * 主题风格、图标库、组件、页面、路由
     * @param $params
     * @return array
     */
    public function compileUniApp($params)
    {

        // 查询店铺正在使用的模板
        $diy_template_model = new Template();
        $site_diy_template_info = $diy_template_model->getSiteDiyTemplateInfo([
            [ 'is_default', '=', 1 ],
            [ 'site_id', '=', $params[ 'site_id' ] ]
        ], 'name,template_goods_id,addon_name')[ 'data' ];

        if (empty($site_diy_template_info)) {
            return $this->error('', '没有找到正在使用的模板，请设置默认模板');
        }

        // 找到uni-app项目
        $uniapp_path = 'upload/temp/standard_uniapp'; // uni-app文件夹
        $compile_path = 'upload/temp/compile_uniapp'; // 编译后的uni-app文件夹，临时位置，生成压缩包后删除

        // <= 2 是因为[ .，.. ]
        if (!is_dir($uniapp_path) || count(scandir($uniapp_path)) <= 2) {
            return $this->error('', '没有找到uni-app文件');
        }

        // 编译后的uni-app文件夹
        if (is_dir($compile_path)) {
            // 先将之前的文件删除
            if (count(scandir($compile_path)) > 2) deleteDir($compile_path);
        } else {
            // 创建uni-app目录
            mkdir($compile_path, intval('0777', 8), true);
        }
        try {
            // 编译uni-app项目
            $this->copyFile($uniapp_path, $compile_path, $compile_path, $site_diy_template_info);

            // 编译pages页面
            $this->getCompilePageCode($compile_path, $site_diy_template_info[ 'addon_name' ]);

            // 编译页面路由
            $this->getCompileRoutesCode($compile_path, $site_diy_template_info[ 'addon_name' ]);

            // 生成压缩包文件
            $file_arr = getFileMap($compile_path);

            if (!empty($file_arr)) {
                $zipname = 'upload/compile_uniapp_' . date('Ymd') . '.zip';

                $zip = new \ZipArchive();
                $res = $zip->open($zipname, \ZipArchive::CREATE);
                if ($res === TRUE) {
                    foreach ($file_arr as $file_path => $file_name) {
                        if (is_dir($file_path)) {
                            $file_path = str_replace($compile_path . '/', '', $file_path);
                            $zip->addEmptyDir($file_path);
                        } else {
                            $zip_path = str_replace($compile_path . '/', '', $file_path);
                            $zip->addFile($file_path, $zip_path);
                        }
                    }
                    $zip->close();

                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($zipname));
                    header("Content-Disposition: attachment; filename=\"" . basename($zipname) . "\"");
                    readfile($zipname);
                    @unlink($zipname);
                    deleteDir($compile_path); // 删除临时文件
                    deleteDir($uniapp_path); // 删除临时文件
                }
            }

            return $this->success();
        } catch (\Exception $e) {
            return $this->error('', 'Error File：' . $e->getFile() . ',Line：' . $e->getLine() . '，Message：' . $e->getMessage());
        }

    }

    /**
     * 复制文件
     * @param string $source_path 源文件路径
     * @param string $to_path 目标位置
     * @param string $compile_path 编译路径
     * @param array $site_diy_template_info 店铺正在使用的模板
     */
    private function copyFile($source_path, $to_path, $compile_path, $site_diy_template_info)
    {
        $files = scandir($source_path);
        foreach ($files as $path) {
            if ($path != '.' && $path != '..') {
                $temp_path = $source_path . '/' . $path;
                if (is_dir($temp_path)) {
                    // 创建文件夹
                    mkdir($to_path . '/' . $path, intval('0777', 8), true);
                    $this->copyFile($temp_path, $to_path . '/' . $path, $compile_path, $site_diy_template_info);
                } else {
                    if (file_exists($temp_path)) {
                        copy($temp_path, $to_path . '/' . $path);

                        // 找到主题风格文件 common/js/style_color.js
                        if (preg_match("/common\/js\/style_color.js$/", $temp_path)) {
                            $content = $this->getCompileThemeCode($site_diy_template_info[ 'addon_name' ]);// 写入内容
                            file_put_contents($to_path . '/' . $path, $content);
                        }

                        // 找到图标库文件 common/css/icon/extend.css
                        if (preg_match("/common\/css\/icon\/extend.css$/", $temp_path)) {
                            $content = $this->getCompileIconCode($compile_path, $site_diy_template_info[ 'addon_name' ]); // 写入内容
                            file_put_contents($to_path . '/' . $path, $content);
                        }

                        // 找到组件扩展文件 diy-comp-extend.vue
                        if (preg_match("/diy-comp-extend.vue$/", $temp_path)) {
                            $content = $this->getCompileComponentCode($compile_path, $site_diy_template_info[ 'addon_name' ]); // 写入内容
                            file_put_contents($to_path . '/' . $path, $content);
                        }

                    }
                }
            }
        }
    }

    /**
     * 获取编译主题风格代码
     * 编写顺序
     * 1、查询所有主题风格列表，条件：main_color、aux_color 不为空的数据
     * 2、编译代码内容并返回
     * @param $addon_name
     * @return string
     */
    private function getCompileThemeCode($addon_name)
    {
        // 查询店铺正在使用的模板，主题风格列表，以及系统主题风格
        $diy_theme_model = new Theme();
        $theme_list = $diy_theme_model->getThemeList([
            [ 'main_color', '<>', '' ],
            [ 'aux_color', '<>', '' ],
            [ 'addon_name', 'in', [ '', $addon_name ] ]
        ], 'id, title, name, main_color, aux_color, value')[ 'data' ];
        $content = "export default {\r\n";

        foreach ($theme_list as $k => $v) {
            $value = [];
            if (!empty($v[ 'value' ])) {
                $value = json_decode($v[ 'value' ]);
            }
            $content .= "   '{$v['name']}' : {\r\n";
            $content .= "       // {$v['title']}：{$v['id']}\r\n";
            $content .= "       'name' : '{$v['name']}',\r\n";
            $content .= "       'main_color' : '{$v['main_color']}',\r\n";
            $content .= "       'aux_color' : '{$v['aux_color']}',\r\n";
            foreach ($value as $ck => $cv) {
                $type = gettype($cv);
                if ($type == 'string') {
                    $content .= "       '{$ck}'  : '{$cv}',\r\n";
                } elseif ($type == 'object' && !empty($cv)) {
                    $content .= "       '{$ck}' : {\r\n";
                    foreach ($cv as $third_k => $third_v) {
                        $content .= "          '{$third_k}'  : '{$third_v}',\r\n";
                    }
                    $content .= "       },\r\n";
                }
            }
            $content .= "   },\r\n";
        }

        $content .= '}';

        return $content;
    }

    /**
     * 获取编译图标库代码
     * 编写顺序
     * 1、查询店铺正在使用的模板，图标库，以及系统图标
     * 2、循环列表，找到图标文件并复制，文件名称要重命名，目的是防止文件名称重复
     * 3、将文件复制到指定文件目录下，common/css/icon 文件夹下，common/css/icon/extend.css 文件中引用图标库文件
     * @param $compile_path
     * @param $addon_name
     * @return string
     */
    private function getCompileIconCode($compile_path, $addon_name)
    {
        // 查询店铺正在使用的模板，图标库，以及系统图标
        $icon_list = $this->getIconAllList([ 'addon_name' => $addon_name ])[ 'icon' ];

        $content = "/* 引用扩展图标库文件 */\n";

        foreach ($icon_list as $k => $v) {
            // 检测文件是否存在
            if (file_exists($v[ 'path' ])) {
                $path_arr = explode('/', $v[ 'path' ]);
                $file_name = $path_arr[ count($path_arr) - 1 ];

                // 生成新文件名称，防止重复
                $file_name = str_replace('.css', '_' . random_keys(5) . '.css', $file_name);

                // 将文件复制到指定文件目录下
                copy($v[ 'path' ], $compile_path . '/common/css/icon/' . $file_name);
                $content .= "@import url('{$file_name}');\n";
            }
        }
        return $content;
    }

    /**
     * 获取编译组件代码
     * 编写顺序
     * 1、查询店铺正在使用的模板，扩展组件列表
     * 2、循环列表，复制文件/文件夹，存放到对应目录下
     * 3、如果是自定义组件文件，前缀开头：diy-，编写代码，写入到 diy-comp-extend.vue 文件中
     * @param $compile_path
     * @param $addon_name
     * @return string
     */
    private function getCompileComponentCode($compile_path, $addon_name)
    {
        // 查询店铺正在使用的模板，扩展组件列表
        $component_list = $this->getExtendComponentList([ 'addon_name' => $addon_name ]);

        $content = <<<EOT
        <template>
            <view>
                <!-- 扩展组件 -->\n
        EOT;

        foreach ($component_list as $k => $v) {
            // 检查文件/文件夹是否存在
            if (file_exists($v[ 'path' ])) {
                $path = str_replace('addon/' . $addon_name . '/uniapp/', '', $v[ 'path' ]);
                $path_arr = explode('/', $path);
                $file_name = $path_arr[ count($path_arr) - 1 ];

                // 文件复制
                if (is_file($v[ 'path' ])) {
                    copy($v[ 'path' ], $compile_path . '/' . $path);
                    // 检测文件是否为自定义扩展组件，前缀开头：diy-
                    if (strpos($file_name, 'diy-') !== false) {
                        $file_name = str_replace('.vue', '', $file_name);
                        $content .= "        <template v-if=\"value.componentName == '{$v['name']}'\">\n";
                        $content .= "            <$file_name :value=\"value\"></{$file_name}>\n";
                        $content .= "        </template>\n";
                    }

                }

                // 文件夹复制
                if (is_dir($v[ 'path' ])) {
                    dir_copy($v[ 'path' ], $compile_path . '/' . $path);
                }
            }
        }

        $content .= <<<EOT
            </view>
        </template>
        <script>
        // 自定义扩展组件
        export default {
            name: 'diy-comp-extend',
            props: {
                value: {
                    type: Object
                }
            },
            data() {
                return {};
            },
            computed: {},
            created() {},
            methods: {}
        };
        </script>
        
        <style></style>\n
        EOT;

        return $content;
    }

    /**
     * 编译pages页面
     * 编写顺序
     * 1、查询店铺正在使用的模板，页面列表
     * 2、循环列表，将页面文件存放到对应目录下，存在则替换
     * @param $compile_path
     * @param $addon_name
     */
    private function getCompilePageCode($compile_path, $addon_name)
    {
        // 查询店铺正在使用的模板，页面列表
        $page_list = $this->getUniAppPageList([ 'addon_name' => $addon_name ]);
        foreach ($page_list as $k => $v) {
            // 检查文件是否存在
            if (file_exists($v[ 'path' ])) {
                $file = $compile_path . '/' . $v[ 'route' ] . '.vue';
                $route = explode('/', $v[ 'route' ]);
                unset($route[ count($route) - 1 ]); // 清除最后一项页面

                $folder = $compile_path;

                foreach ($route as $ck => $cv) {
                    $folder .= "/{$cv}";
                    // 创建uni-app目录
                    if (!is_dir($folder)) {
                        mkdir($folder, intval('0777', 8), true);
                    }
                }

                // 复制文件，存在则覆盖
                copy($v[ 'path' ], $file);
            }
        }

    }

    /**
     * 编译页面路由代码
     * 编写顺序
     * 1、查询店铺正在使用的模板，页面列表
     * 2、循环【pages、pages_promotion、pages_tool】包，找到页面路由集合
     * 3、分3次处理页面路由集合，编译代码内容
     * 4、找到页面路由文件 pages.jon 写入内容
     * @param $compile_path
     * @param $addon_name
     * @return bool|int
     */
    private function getCompileRoutesCode($compile_path, $addon_name)
    {
        // 查询店铺正在使用的模板，页面列表
        $page_list = $this->getUniAppPageList([ 'addon_name' => $addon_name ]);

        $package = [ 'pages', 'pages_promotion', 'pages_tool' ];// 主包、营销活动分包、其他分包

        // 特殊页面，隐藏导航栏
        $special_page = [
            'pages/index/index', 'pages/member/index',
            'pages/goods/detail', 'topics/goods_detail', 'seckill/detail',
            'pintuan/detail', 'groupbuy/detail', 'pinfan/detail', 'presale/detail',
            'pages/order/payment', 'topics/payment', 'seckill/payment', 'pintuan/payment', 'bargain/payment', 'groupbuy/payment',
            'pinfan/payment', 'presale/payment', 'bale/payment'
        ];
        $route_arr = []; // 路由集合

        foreach ($package as $k => $v) {
            $file_arr = getFileMap($compile_path . '/' . $v);
            if (!empty($file_arr)) {
                foreach ($file_arr as $ck => $cv) {
                    if (strpos($cv, '.vue') !== false) {
                        $route = str_replace($compile_path . '/', '', $ck);
                        $route = str_replace('.vue', '', $route);
                        $route_arr[ $v ][] = $route;
                    }
                }
            }
        }

        // 排序
        foreach ($package as $k => $v) {
            sort($route_arr[ $v ], SORT_STRING);
        }

        // 获取首页下标
        $index_page = array_search('pages/index/index', $route_arr[ 'pages' ]);
        $index_route = $route_arr[ 'pages' ][ $index_page ];

        // 将首页设为启动页
        $route_arr[ 'pages' ][ $index_page ] = $route_arr[ 'pages' ][ 0 ];
        $route_arr[ 'pages' ][ 0 ] = $index_route;

        $content = "{\n";

        // 主包
        $content .= "   \"pages\" : [ // pages数组中第一项表示应用启动页，参考：https://uniapp.dcloud.io/collocation/pages\n";
        foreach ($route_arr[ 'pages' ] as $k => $v) {
            $content .= "       {\n";
            $content .= "           \"page\" : \"{$v}\",\n";
            $content .= "           \"style\" : {\n";

            // 找到页面 style，追加数据
            $page_index = array_search($v, array_column($page_list, 'route'));

            if ($page_index !== false && !empty($page_list[ $page_index ][ 'style' ])) {
                $style = $page_list[ $page_index ][ 'style' ];

                if (in_array($v, $special_page)) {
                    $content .= "               \"navigationStyle\" : \"custom\",\n";
                } else {
                    $content .= "               // #ifdef H5\n";
                    $content .= "               \"navigationStyle\" : \"custom\",\n";
                    $content .= "               // #endif\n";
                }

                $style_index = 0;
                foreach ($style as $style_k => $style_v) {
                    $content .= "               \"{$style_k}\" : \"{$style_v}\"";
                    // 最后一个不能加逗号,
                    if (( count($style) - 1 ) == $style_index) {
                        $content .= "\n";
                    } else {
                        $content .= ",\n";
                    }
                    $style_index++;
                }
            } else {
                if (in_array($v, $special_page)) {
                    $content .= "               \"navigationStyle\" : \"custom\"\n";
                } else {
                    $content .= "               // #ifdef H5\n";
                    $content .= "               \"navigationStyle\" : \"custom\"\n";
                    $content .= "               // #endif\n";
                }
            }

            $content .= "           }\n";

            // 最后一个不能加逗号,
            if (( count($route_arr[ 'pages' ]) - 1 ) == $k) {
                $content .= "       }\n";
            } else {
                $content .= "       },\n";
            }

        }
        $content .= "   ],\n";

        // 分包
        $content .= "   \"subPackages\" : [\n";

        // 分包——营销活动
        $content .= "       {\n";
        $content .= "           \"root\": \"pages_promotion\",\n";
        $content .= "           \"pages\": [\n";

        foreach ($route_arr[ 'pages_promotion' ] as $k => $v) {

            $path = str_replace('pages_promotion/', '', $v);

            $content .= "               {\n";
            $content .= "                   \"path\": \"{$path}\",\n";
            $content .= "                   \"style\" : {\n";

            // 找到页面 style，追加数据
            $page_index = array_search($v, array_column($page_list, 'route'));

            if ($page_index !== false && !empty($page_list[ $page_index ][ 'style' ])) {
                $style = $page_list[ $page_index ][ 'style' ];

                if (in_array($path, $special_page)) {
                    $content .= "                       \"navigationStyle\" : \"custom\",\n";
                } else {
                    $content .= "                       // #ifdef H5\n";
                    $content .= "                       \"navigationStyle\" : \"custom\",\n";
                    $content .= "                       // #endif\n";
                }

                $style_index = 0;
                foreach ($style as $style_k => $style_v) {
                    $content .= "                       \"{$style_k}\" : \"{$style_v}\"";
                    // 最后一个不能加逗号,
                    if (( count($style) - 1 ) == $style_index) {
                        $content .= "\n";
                    } else {
                        $content .= ",\n";
                    }
                    $style_index++;
                }
            } else {
                if (in_array($path, $special_page)) {
                    $content .= "                       \"navigationStyle\" : \"custom\"\n";
                } else {
                    $content .= "                       // #ifdef H5\n";
                    $content .= "                       \"navigationStyle\" : \"custom\"\n";
                    $content .= "                       // #endif\n";
                }
            }

            $content .= "                   }\n";

            // 最后一个不能加逗号,
            if (( count($route_arr[ 'pages_promotion' ]) - 1 ) == $k) {
                $content .= "               }\n";
            } else {
                $content .= "               },\n";
            }

        }
        $content .= "           ]\n";
        $content .= "       },\n";

        // 分包——其他
        $content .= "       {\n";
        $content .= "           \"root\": \"pages_tool\",\n";
        $content .= "           \"pages\": [\n";

        foreach ($route_arr[ 'pages_tool' ] as $k => $v) {

            $path = str_replace('pages_tool/', '', $v);

            $content .= "               {\n";
            $content .= "                   \"path\": \"{$path}\",\n";
            $content .= "                   \"style\" : {\n";

            // 找到页面 style，追加数据
            $page_index = array_search($v, array_column($page_list, 'route'));

            if ($page_index !== false && !empty($page_list[ $page_index ][ 'style' ])) {
                $style = $page_list[ $page_index ][ 'style' ];

                if (in_array($path, $special_page)) {
                    $content .= "                       \"navigationStyle\" : \"custom\",\n";
                } else {
                    $content .= "                       // #ifdef H5\n";
                    $content .= "                       \"navigationStyle\" : \"custom\",\n";
                    $content .= "                       // #endif\n";
                }

                $style_index = 0;
                foreach ($style as $style_k => $style_v) {
                    $content .= "                       \"{$style_k}\" : \"{$style_v}\"";
                    // 最后一个不能加逗号,
                    if (( count($style) - 1 ) == $style_index) {
                        $content .= "\n";
                    } else {
                        $content .= ",\n";
                    }
                    $style_index++;
                }
            } else {
                if (in_array($path, $special_page)) {
                    $content .= "                       \"navigationStyle\" : \"custom\"\n";
                } else {
                    $content .= "                       // #ifdef H5\n";
                    $content .= "                       \"navigationStyle\" : \"custom\"\n";
                    $content .= "                       // #endif\n";
                }
            }

            $content .= "                   }\n";

            // 最后一个不能加逗号,
            if (( count($route_arr[ 'pages_tool' ]) - 1 ) == $k) {
                $content .= "               }\n";
            } else {
                $content .= "               },\n";
            }

        }
        $content .= "           ]\n";
        $content .= "       }\n";

        $content .= "   ],\n";

        // globalStyle
        $content .= "   \"globalStyle\": {\n";
        $content .= "       \"navigationBarTextStyle\": \"black\",\n";
        $content .= "       \"navigationBarTitleText\": \"\",\n";
        $content .= "       \"navigationBarBackgroundColor\": \"#ffffff\",\n";
        $content .= "       \"backgroundColor\": \"#F7f7f7\",\n";
        $content .= "       \"backgroundColorTop\": \"#f7f7f7\",\n";
        $content .= "       \"backgroundColorBottom\": \"#f7f7f7\"\n";
        $content .= "   },\n";

        // tabBar
        $content .= "   \"tabBar\": {\n";
        $content .= "       // #ifdef H5\n";
        $content .= "       \"custom\": true,\n";
        $content .= "       // #endif\n";
        $content .= "       \"color\": \"#333\",\n";
        $content .= "       \"selectedColor\": \"#FF0036\",\n";
        $content .= "       \"backgroundColor\": \"#fff\",\n";
        $content .= "       \"borderStyle\": \"white\",\n";
        $content .= "       \"list\": [\n";

        $content .= "           {\n";
        $content .= "               \"pagePath\": \"pages/index/index\",\n";
        $content .= "               \"text\": \"首页\"\n";
        $content .= "           },\n";

        $content .= "           {\n";
        $content .= "               \"pagePath\": \"pages/goods/category\",\n";
        $content .= "               \"text\": \"分类\"\n";
        $content .= "           },\n";

        $content .= "           {\n";
        $content .= "               \"pagePath\": \"pages/goods/cart\",\n";
        $content .= "               \"text\": \"购物车\"\n";
        $content .= "           },\n";

        $content .= "           {\n";
        $content .= "               \"pagePath\": \"pages/member/index\",\n";
        $content .= "               \"text\": \"个人中心\"\n";
        $content .= "           }\n";

        $content .= "       ]\n";

        $content .= "   },\n";

        // easycom
        $content .= "   \"easycom\": {\n";
        $content .= "       \"diy-*(\\W.*)\": \"@/components/diy-components/diy$1.vue\"\n";
        $content .= "   },\n";

        // preloadRule
        $content .= "   \"preloadRule\": {\n";
        $content .= "       \"pages/index/index\": {\n";
        $content .= "           \"network\": \"all\",\n";
        $content .= "           \"packages\": [\"pages_tool\"]\n";
        $content .= "       }\n";
        $content .= "   }\n";

        $content .= "}\n";

        // 找到页面路由文件 pages.json，写入内容
        $res = file_put_contents($compile_path . '/pages.json', $content);

        return $res;
    }

}