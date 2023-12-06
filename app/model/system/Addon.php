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

use app\model\BaseModel;
use app\model\diy\Template;
use app\model\diy\Theme;
use app\model\web\DiyView as DiyViewModel;
use app\model\web\DiyViewLink;
use think\facade\Cache;
use think\facade\Db;

/**
 * 插件表
 */
class Addon extends BaseModel
{

    /**
     * 获取单条插件信息
     * @param array $condition
     * @param string $field
     */
    public function getAddonInfo($condition, $field = "*")
    {
        $data = json_encode([ $condition, $field ]);
        $cache = Cache::get("addon_getAddonInfo_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $addon_info = model('addon')->getInfo($condition, $field);
        Cache::tag("addon")->set("addon_getAddonInfo_" . $data, $addon_info);
        return $this->success($addon_info);
    }

    /**
     * 获取插件列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getAddonList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $data = json_encode([ $condition, $field, $order, $limit ]);
        $cache = Cache::get("addon_getAddonList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $addon_list = model('addon')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("addon")->set("addon_getAddonList_" . $data, $addon_list);
        return $this->success($addon_list);
    }

    /**
     * 获取插件分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getAddonPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $data = json_encode([ $condition, $page, $page_size, $order, $field ]);
        $cache = Cache::get("addon_getAddonPageList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('addon')->pageList($condition, $field, $order, $page, $page_size);
        Cache::tag("addon")->set("addon_getAddonPageList_" . $data, $list);
        return $this->success($list);
    }

    /**
     * 获取所有插件
     * @return array
     */
    public function getAddonAllList()
    {
        //获取官网记录的所有数据
        $upgrade_service = new Upgrade();
        $data = $upgrade_service->getAuthPlugin();
        if (isset($data[ 'code' ]) && $data[ 'code' ] >= 0) {
            $temp_auth_addon_list = $data[ 'data' ];
        } else {
            $temp_auth_addon_list = [];
        }

        //以code为key组装正式数据
        $auth_addon_list = [];
        foreach ($temp_auth_addon_list as $key => $val) {
            $auth_addon_list[ $val[ 'code' ] ] = $val;
        }
        //存在的插件
        $existed_addons = array_map('basename', glob('addon/*', GLOB_ONLYDIR));
        //已安装的插件
        $installed_addon_array = model('addon')->getColumn([], 'name');
        //初始化数据
        $undownload_addons = [];
        $uninstall_addons = [];
        $install_addons = [];
        //获取未下载插件
        foreach ($auth_addon_list as $key => $val) {
            $index = array_search($val[ 'code' ], $existed_addons);
            if ($index === false) {
                $undownload_addons[] = [
                    'name' => $val[ 'code' ],
                    'title' => $val[ 'goods_name' ],
                    'icon' => $val[ 'logo' ],
                    'description' => $val[ 'introduction' ],
                    'version' => $val[ 'last_online_version_name' ],
                    'download' => 1,
                    'auth' => true,
                    'update' => false
                ];
            }
        }
        //获取已下载插件 区分已安装和为安装 是否需要升级 是否已授权
        foreach ($existed_addons as $key => $val) {
            $info_file_path = 'addon/' . $val . '/config/info.php';
            if (file_exists($info_file_path)) {
                $info = include_once $info_file_path;
                $info[ 'icon' ] = 'addon/' . $val . '/icon.png';
                $info[ 'download' ] = 0;
                $info[ 'auth' ] = isset($auth_addon_list[ $val ]) ? true : false;
                $info[ 'update' ] = ( isset($auth_addon_list[ $val ]) && $auth_addon_list[ $val ][ 'last_online_version_no' ] > $info[ 'version_no' ] ) ? true : false;
                $info[ 'last_online_version_no' ] = isset($auth_addon_list[ $val ]) ? $auth_addon_list[ $val ][ 'last_online_version_no' ] : '';
                if (!in_array($val, $installed_addon_array)) {
                    $uninstall_addons[] = $info;
                } else {
                    $install_addons[] = $info;
                }
            }
        }
        return $this->success([
            'uninstall' => array_merge($undownload_addons, $uninstall_addons),
            'install' => $install_addons,
        ]);
    }

    /**
     * 获取未安装的插件列表
     */
    public function getUninstallAddonList()
    {

        $dirs = array_map('basename', glob('addon/*', GLOB_ONLYDIR));
        $addon_names = model('addon')->getColumn([], 'name');
        $addons = [];
        foreach ($dirs as $key => $value) {
            if (!in_array($value, $addon_names)) {
                $info_name = 'addon/' . $value . '/config/info.php';
                if (file_exists($info_name)) {
                    $info = include_once $info_name;
                    $info[ 'icon' ] = 'addon/' . $value . '/icon.png';
                    $addons[] = $info;
                }
            }
        }
        return $this->success($addons);
    }

    /*******************************************************************插件安装方法开始****************************************************/
    /**
     * 插件安装
     *
     * @param string $addon_name
     */
    public function install($addon_name)
    {

        Db::startTrans();
        try {
            // 插件预安装

            $res2 = $this->preInstall($addon_name);
            if ($res2[ 'code' ] != 0) {
                Db::rollback();
                return $res2;
            }

            // 安装菜单
            $res3 = $this->installMenu($addon_name);
            if ($res3[ 'code' ] != 0) {
                Db::rollback();
                return $res3;
            }

            // 安装自定义模板
            $res4 = $this->refreshDiyView($addon_name);
            if ($res4[ 'code' ] != 0) {
                Db::rollback();
                return $res4;
            }

            // 添加插件入表
            $addons_model = model('addon');
            $addon_info = require 'addon/' . $addon_name . '/config/info.php';
            $addon_info[ 'create_time' ] = time();
            $addon_info[ 'icon' ] = 'addon/' . $addon_name . '/icon.png';

            $data = $addons_model->add($addon_info);

            if (!$data) {
                Db::rollback();
                return $this->error($data, 'ADDON_ADD_FAIL');
            }
            // 清理缓存
            Cache::clear();

            Db::commit();
            return $this->success();
        } catch (\Exception $e) {
            // 清理缓存
            Cache::clear();
            Db::rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 插件预安装
     */
    private function preInstall($addon_name)
    {
        $class_name = "addon\\" . $addon_name . "\\event\\Install";
        $install = new $class_name;
        $res = $install->handle($addon_name);
        if ($res[ 'code' ] != 0) {
            return $res;
        }
        return $this->success();
    }

    /**
     * 安装插件菜单
     */
    private function installMenu($addon)
    {
        $menu = new Menu();
        $menu->refreshMenu('shop', $addon);
        return $this->success();
    }


    /**
     * 刷新插件自定义页面配置
     * @param $addon
     * @param $site_id
     * @return array
     */
    public function refreshDiyView($addon)
    {
        try {
            if (empty($addon)) {
                $diy_view_file = 'config/diy_view.php';
            } else {
                $diy_view_file = 'addon/' . $addon . '/config/diy_view.php';
            }

            if (!file_exists($diy_view_file)) {
                return $this->success();
            }

            $link_model = new DiyViewLink();
            $diy_view_model = new DiyViewModel();
            $diy_template_model = new Template();
            $diy_theme_model = new Theme();

            // 查询原模板组列表，用于更新自定义页面的所属模板id
            $diy_template_goods_list_old = $diy_template_model->getTemplateGoodsList([ [ 'addon_name', '=', $addon ] ], 'goods_id,name')[ 'data' ];

            // 清空数据
            $link_model->deleteLink([ [ 'addon_name', '=', $addon ] ]); // 链接
            $diy_view_model->deleteUtil([ [ 'addon_name', '=', $addon ] ]); // 组件
            $diy_template_model->deleteTemplate([ [ 'addon_name', '=', $addon ] ]); // 页面类型
            $diy_theme_model->deleteTheme([ [ 'addon_name', '=', $addon ] ]); // 主题风格
            $diy_template_model->deleteTemplateGoods([ [ 'addon_name', '=', $addon ] ]); // 模板组
            $diy_template_model->deleteTemplateGoodsItem([ [ 'addon_name', '=', $addon ] ]); // 模板页面

            $diy_view = require $diy_view_file;

            // 自定义链接
            if (isset($diy_view[ 'link' ])) {
                $diy_view_link_data = $link_model->getViewLinkList($diy_view[ 'link' ], $addon);
                if ($diy_view_link_data) {
                    model('link')->addList($diy_view_link_data);
                }
            }

            // 自定义模板组件
            if (isset($diy_view[ 'util' ])) {
                $diy_view_util_data = [];
                foreach ($diy_view[ 'util' ] as $k => $v) {
                    $util_item = [
                        'name' => $v[ 'name' ], // 组件标识
                        'title' => $v[ 'title' ], // 组件名称
                        'type' => $v[ 'type' ], // 组件类型，SYSTEM：基础组件，PROMOTION：营销组件，EXTEND：扩展组件
                        'value' => $v[ 'value' ], // 组件数据结构json格式
                        'sort' => $v[ 'sort' ],
                        'support_diy_view' => $v[ 'support_diy_view' ] ?? '', // 支持的自定义页面（为空表示公共组件都支持）
                        'addon_name' => $addon,
                        'max_count' => $v[ 'max_count' ] ?? 0, // 限制添加次数，0表示可以无限添加该组件
                        'is_delete' => $v[ 'is_delete' ] ?? 0, // 组件是否可以删除，0 允许，1 禁用
                        'icon' => $v[ 'icon' ] ?? '' // 组件字体图标
                    ];
                    $diy_view_util_data[] = $util_item;
                }
                if ($diy_view_util_data) {
                    $diy_view_model->addUtilList($diy_view_util_data);
                }
            }

            // 自定义模板页面类型
            if (isset($diy_view[ 'template' ]) && !empty($diy_view[ 'template' ])) {
                $template_data = [];
                foreach ($diy_view[ 'template' ] as $k => $v) {
                    // 检测防重复
                    $count = $diy_template_model->getTemplateCount([ [ 'name', '=', $v[ 'name' ] ] ])[ 'data' ];
                    if ($count == 0) {
                        $template_data[] = [
                            'title' => $v[ 'title' ], // 模板名称
                            'name' => $v[ 'name' ], // 模板标识
                            'page' => $v[ 'path' ], // 页面路径
                            'addon_name' => $addon,
                            'value' => $v[ 'value' ] ?? '',
                            'rule' => isset($v[ 'rule' ]) ? json_encode($v[ 'rule' ]) : '',
                            'sort' => $v[ 'sort' ] ?? 0
                        ];
                    }
                }

                if (!empty($template_data)) {
                    $diy_template_model->addTemplateList($template_data);
                }
            }

            // 主题风格配色
            if (isset($diy_view[ 'theme' ]) && !empty($diy_view[ 'theme' ])) {
                $theme_data = [];
                foreach ($diy_view[ 'theme' ] as $k => $v) {
                    // 检测防重复
                    $count = $diy_theme_model->getThemeCount([ [ 'name', '=', $v[ 'name' ] ] ])[ 'data' ];
                    if ($count == 0) {
                        $theme_value = $v;
                        unset($theme_value[ 'title' ], $theme_value[ 'name' ], $theme_value[ 'main_color' ], $theme_value[ 'aux_color' ], $theme_value[ 'preview' ]);
                        $theme_data[] = [
                            'title' => $v[ 'title' ],
                            'name' => $v[ 'name' ],
                            'addon_name' => $addon,
                            'main_color' => $v[ 'main_color' ],
                            'aux_color' => $v[ 'aux_color' ],
                            'preview' => implode(',', $v[ 'preview' ]),
                            'color_img' => $v[ 'color_img' ],
                            'value' => json_encode($theme_value),
                        ];
                    }

                }
                if (!empty($theme_data)) {
                    $diy_theme_model->addThemeList($theme_data);
                }
            }

            // 模板信息
            $diy_goods_id = 0;
            if (isset($diy_view[ 'info' ]) && !empty($diy_view[ 'info' ])) {
                $template_goods_data = [
                    'title' => $diy_view[ 'info' ][ 'title' ], // 模板名称
                    'name' => $diy_view[ 'info' ][ 'name' ], // 模板标识
                    'addon_name' => $addon,
                    'cover' => $diy_view[ 'info' ][ 'cover' ], // 模板封面图
                    'preview' => $diy_view[ 'info' ][ 'preview' ], // 模板预览图
                    'desc' => $diy_view[ 'info' ][ 'desc' ], // 模板描述
                ];

                // 检测防重复
                $count = $diy_template_model->getTemplateGoodsCount([ [ 'name', '=', $template_goods_data[ 'name' ] ] ])[ 'data' ];
                if ($count == 0) {
                    $diy_goods_id = $diy_template_model->addTemplateGoods($template_goods_data)[ 'data' ];
                }

                if (!empty($diy_template_goods_list_old)) {
                    foreach ($diy_template_goods_list_old as $k => $v) {
                        // 更新自定义页面的所属模板id
//                        $diy_view_model->editSiteDiyView([
//                            'template_id' => $diy_goods_id,
//                        ], [
//                            [ 'name', 'like', '%DIY_VIEW_RANDOM_%' ],
//                            [ 'template_id', '=', $v[ 'goods_id' ] ]
//                        ]);

                        // 更新店铺关联模板关系id
                        $diy_template_model->editSiteDiyTemplate([
                            'template_goods_id' => $diy_goods_id,
                        ], [
                            [ 'addon_name', '=', $addon ],
                            [ 'name', '=', $v[ 'name' ] ],
                            [ 'template_goods_id', '=', $v[ 'goods_id' ] ]
                        ]);
                    }

                }

            } else {
                // 模板不存在，则清除店铺与模板之间的关系
                $diy_template_model->deleteSiteDiyTemplate([ [ 'addon_name', '=', $addon ] ]); // 模板页面关联关系
            }

            // 自定义页面数据
            if (isset($diy_view[ 'data' ]) && !empty($diy_view[ 'data' ])) {
                $goods_item_id = 0;
                foreach ($diy_view[ 'data' ] as $k => $v) {
                    $goods_item_data = [
                        'goods_id' => $diy_goods_id, // 模板组id
                        'title' => $v[ 'title' ], // 名称
                        'addon_name' => $addon,
                        'name' => $v[ 'name' ], // 所属页面（首页、分类，空为微页面）
                        'value' => json_encode($v[ 'value' ]), // 模板数据
                        'create_time' => time()
                    ];
                    $item_id = $diy_template_model->addTemplateGoodsItem($goods_item_data)[ 'data' ];

                    // 默认装修第一个页面
                    if ($k == 0) {
                        $goods_item_id = $item_id;
                    }
                }

                $diy_template_model->editTemplateGoods([ 'goods_item_id' => $goods_item_id ], [ [ 'goods_id', '=', $diy_goods_id ] ]);

                // 更新页面的所属模板id
                $diy_template_goods_item_list = $diy_template_model->getTemplateGoodsItemList([ [ 'addon_name', '=', $addon ] ], 'goods_id,goods_item_id,name')[ 'data' ];
                if (!empty($diy_template_goods_item_list)) {
                    foreach ($diy_template_goods_item_list as $k => $v) {
                        $diy_view_model->editSiteDiyView([
                            'template_id' => $v[ 'goods_id' ],
                            'template_item_id' => $v[ 'goods_item_id' ]
                        ], [
                            [ 'name', '=', $v[ 'name' ] ],
                            [ 'addon_name', '=', $addon ]
                        ]);
                    }
                }

            }

            return $this->success();
        } catch (\Exception $e) {
            return $this->error('', $e->getMessage() . '-' . $e->getFile() . '-' . $e->getLine());
        }
    }

    /**************************************************************插件安装结束*********************************************************/

    /**************************************************************插件卸载开始*********************************************************/
    public function uninstall($addon_name)
    {
        Db::startTrans();
        try {
            $addon_info = model("addon")->getInfo([ [ 'name', '=', $addon_name ] ], '*');
            // 插件预卸载
            $res1 = $this->preUninstall($addon_name);
            if ($res1[ 'code' ] != 0) {
                Db::rollback();
                return $res1;
            }
            // 卸载菜单
            $res2 = $this->uninstallMenu($addon_name);
            if ($res2[ 'code' ] != 0) {
                Db::rollback();
                return $res2;
            }
            $res3 = $this->uninstallDiyView($addon_name);
            if ($res3[ 'code' ] != 0) {
                Db::rollback();
                return $res3;
            }
            $delete_res = model('addon')->delete([
                [ 'name', '=', $addon_name ]
            ]);
            if ($delete_res === false) {
                Db::rollback();
                return $this->error();
            }
            //清理缓存
            Cache::clear();
            Db::commit();
            return $this->success();
        } catch (\Exception $e) {
            //清理缓存
            Cache::clear();
            Db::rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 插件预卸载
     */
    private function preUninstall($addon_name)
    {
        $class_name = "addon\\" . $addon_name . "\\event\\UnInstall";
        $install = new $class_name;
        $res = $install->handle($addon_name);
        return $res;
    }

    /**
     * 卸载插件菜单
     */
    private function uninstallMenu($addon_name)
    {
        $res = model('menu')->delete([
            [ 'addon', '=', $addon_name ]
        ]);
        return $this->success($res);
    }

    /**
     * 卸载自定义数据（清除：自定义链接、组件、主题风格、模板页面类型、模板组、模板组页面、店铺拥有的模板组、店铺自定义模板）
     * @param $addon_name
     * @return array
     */
    private function uninstallDiyView($addon_name)
    {
        model('link')->delete([ [ 'addon_name', '=', $addon_name ] ]); // 自定义链接
        model('diy_view_util')->delete([ [ 'addon_name', '=', $addon_name ] ]); // 自定义组件
        model('diy_theme')->delete([ [ 'addon_name', '=', $addon_name ] ]); // 主题风格
        model('diy_template')->delete([ [ 'addon_name', '=', $addon_name ] ]); // 模板页面类型
        model('diy_template_goods')->delete([ [ 'addon_name', '=', $addon_name ] ]); // 模板组
        model('diy_template_goods_item')->delete([ [ 'addon_name', '=', $addon_name ] ]); // 模板组页面
        model('site_diy_template')->delete([ [ 'addon_name', '=', $addon_name ] ]); // 店铺拥有的模板组
//        model('site_diy_view')->delete([ [ 'addon_name', '=', $addon_name ] ]); // 店铺自定义模板
        return $this->success();
    }

    /***************************************************************插件卸载结束********************************************************/

    /************************************************************* 安装全部插件 start *************************************************************/

    /**
     * 安装全部插件
     */
    public function installAllAddon()
    {
        $addon_list_result = $this->getUninstallAddonList();
        $addon_list = $addon_list_result[ "data" ];
        foreach ($addon_list as $k => $v) {
            $item_result = $this->install($v[ "name" ]);
            if ($item_result[ "code" ] < 0)
                return $item_result;
        }
        return $this->success();
    }
    /************************************************************* 安装全部插件 end *************************************************************/

    /**
     * 刷新应用插件
     * @return array
     */
    public function cacheAddon()
    {
        //刷新插件信息
        $addon_list = model('addon')->getList();
        foreach ($addon_list as $k => $v) {
            $data = require 'addon/' . $v[ 'name' ] . '/config/info.php';
            if (empty($data)) {
                $data = [];
            }
            $data[ 'create_time' ] = time();
            $data[ 'icon' ] = 'addon/' . $v[ 'name' ] . '/icon.png';
            model('addon')->update($data, [ 'name' => $v[ 'name' ] ]);
        }
        return $this->success();
    }

    /**
     * 刷新所有插件菜单
     */
    public function cacheAddonMenu()
    {
        $addon_list = model('addon')->getList([], 'name');
        $menu_model = new Menu();
        foreach ($addon_list as $k => $v) {
            $addon_menu_res = $menu_model->refreshMenu('shop', $v[ 'name' ]);
            // 刷新收银端权限
            $menu_model->refreshCashierAuth($v[ 'name' ]);
        }
        return $this->success($addon_menu_res);
    }

    /**
     * 刷新插件收银台权限
     * @return array
     */
    public function refreshAddonCashierAuth()
    {
        $addon_list = model('addon')->getList([], 'name');
        $menu_model = new Menu();
        foreach ($addon_list as $k => $v) {
            $addon_menu_res = $menu_model->refreshCashierAuth($v[ 'name' ]);
        }
        return $this->success();
    }
}
