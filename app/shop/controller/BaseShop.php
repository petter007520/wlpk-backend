<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;

use addon\weapp\model\Config as WeappConfigModel;
use app\Controller;
use app\model\store\Store as StoreModel;
use app\model\system\Config as SystemConfig;
use app\model\system\Group as GroupModel;
use app\model\system\Menu;
use app\model\system\Site;
use app\model\system\User as UserModel;
use app\model\upload\Config as UploadConfigModel;
use app\model\web\Config as ConfigModel;
use app\model\web\DiyView as DiyViewModel;

class BaseShop extends Controller
{
    protected $init_menu = [];
    protected $crumbs = [];
    protected $crumbs_array = [];

    protected $uid;
    protected $user_info;
    protected $url;
    protected $group_info;
    protected $menus;
    protected $menu_info;
    protected $site_id;
    protected $store_id;
    protected $shop_info;
    protected $store_info;
    protected $app_module = SHOP_MODULE;
    protected $replace = [];
    protected $addon = '';

    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
        //检测基础登录
        $this->site_id = request()->siteid();

        $user_model = new UserModel();
        $this->app_module = $user_model->loginModule($this->site_id);
        $this->uid = $user_model->uid($this->app_module, $this->site_id);
        if (empty($this->uid)) {
            $this->redirect(url("shop/login/login"));
        }

        $this->url = request()->parseUrl();
        $this->addon = request()->addon() ? request()->addon() : '';
        $this->user_info = $user_model->userInfo($this->app_module, $this->site_id);
        $this->assign("user_info", $this->user_info);
        $this->assign('app_module', $this->app_module);
        $this->checkLogin();
        //检测用户组
        $this->getGroupInfo();
        if ($this->app_module == 'store') {

            $this->replace = [
                'STORE_CSS' => __ROOT__ . '/addon/store/store/view/public/css',
                'STORE_JS' => __ROOT__ . '/addon/store/store/view/public/js',
                'STORE_IMG' => __ROOT__ . '/addon/store/store/view/public/img',
            ];
            //检测用户组,通过用户组查询对应门店id
            $store_model = new StoreModel();
            $store_info = $store_model->getStoreInfo([ [ 'store_id', '=', $this->store_id ] ]);
            $this->store_info = $store_info[ 'data' ];
            if ($this->store_info[ 'is_frozen' ]) {
                $this->error('该门店已关闭，请联系店铺管理员开启');
            }
            $this->assign('store_info', $this->store_info);
        }
        if (!$this->checkAuth()) {
            if (!request()->isAjax()) {
                $menu_info = $user_model->getRedirectUrl($this->url, $this->app_module, $this->group_info, $this->addon);

                if (empty($menu_info)) {
                    $this->error('权限不足，请联系客服');
                } else {
                    $this->redirect(addon_url($menu_info[ 'url' ]));
                }
            } else {
                echo json_encode(error(-1, '权限不足，请联系客服'));
                exit;
            }

        }
        //获取店铺信息
        $site_model = new Site();
        $shop_info = $site_model->getSiteInfo([ [ 'site_id', '=', $this->site_id ] ], 'site_id,site_name,logo,seo_keywords,seo_description, create_time');
        $this->shop_info = $shop_info[ 'data' ];

        $this->assign("shop_info", $shop_info[ 'data' ]);
        if (!request()->isAjax()) {
            //获取菜单
            $this->assign("help_show", HELP_SHOW);
            $this->menus = $this->getMenuList();

            $this->initBaseInfo();
        }

        // 加载自定义图标库
        $diy_view = new DiyViewModel();
        $diy_icon_url = $diy_view->getIconUrl()[ 'data' ];
        $this->assign('load_diy_icon_url', $diy_icon_url);

        // 上传图片配置
        $uplode_config_model = new UploadConfigModel();
        $upload_config = $uplode_config_model->getUploadConfig($this->site_id);
        $this->assign('upload_max_filesize', $upload_config[ 'data' ][ 'value' ][ 'upload' ][ 'max_filesize' ] / 1024);
        if ($this->app_module == 'store') {
            $base = 'addon/store/store/view/base.html';
            $this->assign('base', $base);
        } else {
            //$config_model = new ConfigModel();
            //$base = $config_model->getStyle($this->site_id);
            $base = 'app/shop/view/base/style2.html';
            $this->assign('base', $base);
        }
    }

    /**
     * 加载基础信息
     */
    private function initBaseInfo()
    {
        //获取一级权限菜单
        $this->getTopMenu();
        $menu_model = new Menu();
        $info_result = $menu_model->getMenuInfoByUrl($this->url, $this->app_module, $this->addon);
        $info = [];
        if (!empty($info_result[ "data" ])) {
            $info = $info_result[ "data" ];
            $this->getParentMenuList($info[ 'name' ]);
        } elseif ($this->url == '/Index/index') {
            $info_result = $menu_model->getMenuInfoByUrl($this->url, $this->app_module, $this->addon);
            if (!empty($info_result[ "data" ])) {
                $info = $info_result[ "data" ];
                $this->getParentMenuList($info[ 'name' ]);
            }
        }
        $this->menu_info = $info;
        $this->assign("menu_info", $info);
        if (!empty($this->crumbs)) {
            $this->crumbs = array_reverse($this->crumbs);
            $this->assign("crumbs", $this->crumbs);
        }

        //加载菜单树
        $init_menu = $this->initMenu($this->menus, '');
        $init_menu = $this->getRealMenu($init_menu);

        $this->assign("url", $this->url);
        $this->assign("menu", $init_menu);

        //加载版权信息
        $config_model = new ConfigModel();
        $copyright = $config_model->getCopyright();
        $this->assign('copyright', $copyright[ 'data' ][ 'value' ]);

        // 查询小程序配置信息
        $weapp_config_model = new WeappConfigModel();
        $weapp_config = $weapp_config_model->getWeappConfig($this->site_id);
        $weapp_config = $weapp_config[ 'data' ][ 'value' ];
        $this->assign('base_weapp_config', $weapp_config);
    }

    /**
     * 加载构造函数信息
     */
    public function initConstructInfo()
    {
        $this->site_id = input('site_id', 0);
        $config_model = new ConfigModel();
        $base = $config_model->getStyle($this->site_id);
        $this->assign('base', $base);

        $site_model = new Site();
        $shop_info = $site_model->getSiteInfo([ [ 'site_id', '=', $this->site_id ] ], 'site_name,logo,seo_keywords,seo_description, create_time')[ 'data' ];
        $this->assign("shop_info", $shop_info);
        $this->assign('app_module', $this->app_module);

        // 加载自定义图标库
        $diy_view = new DiyViewModel();
        $diy_icon_url = $diy_view->getIconUrl()[ 'data' ];
        $this->assign('load_diy_icon_url', $diy_icon_url);
    }

    /**
     * layui化处理菜单数据
     * @param $menus_list
     * @param string $parent
     * @return array
     */
    public function initMenu($menus_list, $parent = "")
    {
        $temp_list = [];
        if (!empty($menus_list)) {
            foreach ($menus_list as $menu_k => $menu_v) {

                if (in_array($menu_v[ 'name' ], $this->crumbs_array)) {
                    $selected = true;
                } else {
                    $selected = false;
                }

                if ($menu_v[ "parent" ] == $parent && $menu_v[ "is_show" ] == 1) {
                    $temp_item = array (
                        'name' => $menu_v[ 'name' ],
                        'level' => $menu_v[ 'level' ],
                        'addon' => $menu_v[ 'addon' ],
                        'selected' => $selected,
                        'url' => addon_url($menu_v[ 'url' ]),
                        'title' => $menu_v[ 'title' ],
                        'icon' => $menu_v[ 'picture' ],
                        'icon_selected' => $menu_v[ 'picture_select' ],
                        'target' => '',
                        'parent' => $menu_v[ 'parent' ]
                    );

                    $child = $this->initMenu($menus_list, $menu_v[ "name" ]);//获取下级的菜单

                    $temp_item[ "child_list" ] = $child;
                    $temp_list[ $menu_v[ "name" ] ] = $temp_item;
                }
            }
        }
        return $temp_list;
    }

    /**
     * 获取上级菜单列表
     * @param string $name
     */
    private function getParentMenuList($name = '')
    {
        if (!empty($name)) {
            $menu_model = new Menu();
            $menu_info_result = $menu_model->getMenuInfo([ [ 'name', "=", $name ], [ 'app_module', '=', $this->app_module ] ]);
            $menu_info = $menu_info_result[ "data" ];
            if (!empty($menu_info)) {
                $menu_info[ "url" ] = addon_url($menu_info[ "url" ]);
                $this->crumbs[] = $menu_info;
                $this->crumbs_array[] = $menu_info[ 'name' ];
                $this->getParentMenuList($menu_info[ 'parent' ]);
            }
        }

    }

    /**
     * 获取当前用户的用户组
     */
    private function getGroupInfo()
    {
        $group_model = new GroupModel();
        $group_info_result = $group_model->getGroupInfo([ [ "group_id", "=", $this->user_info[ "group_id" ] ], [ "app_module", "=", $this->app_module ] ]);
        $this->group_info = $group_info_result[ "data" ];
        if ($this->app_module == 'store') {
            //门店登录,用户权限对应站点id是门店id
            $this->store_id = $this->group_info[ 'site_id' ];
        }
    }

    /**
     * 验证登录
     */
    private function checkLogin()
    {
        //验证基础登录
        if (!$this->uid) {
            $this->redirect(url('shop/login/login'));
        }
    }

    /**
     * 检测权限
     */
    private function checkAuth()
    {
        if ($this->user_info[ 'is_admin' ] == 1) {
            return true;
        }
        $user_model = new UserModel();
        $res = $user_model->checkAuth($this->url, $this->app_module, $this->group_info, $this->addon);
        return $res;
    }

    /**
     * 获取菜单
     */
    private function getMenuList()
    {
        $menu_model = new Menu();
        //暂定全部权限，系统用户做完后放开
        if ($this->user_info[ 'is_admin' ] || $this->group_info[ 'is_system' ] == 1) {
            $menus = $menu_model->getMenuList([ [ 'app_module', "=", $this->app_module ] ], '*', 'level asc, sort asc');
        } else {
            $menus = $menu_model->getMenuList([ [ 'name', 'in', $this->group_info[ 'menu_array' ] ], [ 'app_module', "=", $this->app_module ] ], '*', 'level asc,sort asc');
            $control_menu = $menu_model->getMenuList([ [ 'is_control', '=', 0 ], [ 'app_module', "=", $this->app_module ] ], '*', 'sort asc');
            $menus[ 'data' ] = array_merge($control_menu[ 'data' ], $menus[ 'data' ]);
            $keys = array_column($menus[ 'data' ], 'sort');
            if (!empty($keys)) {
                array_multisort($keys, SORT_ASC, SORT_NUMERIC, $menus[ 'data' ]);
            }
        }

        return $menus[ 'data' ];
    }

    /**
     * 获取顶级菜单
     */
    protected function getTopMenu()
    {
        $list = array_filter($this->menus, function($v) {
            return $v[ 'parent' ] == '0';
        });
        return $list;
    }

    /**
     * 四级菜单
     * @param array $params
     */
    protected function forthMenu($params = [])
    {
        if (!empty($this->crumbs)) {
            //菜单的等级有可能是四级，也可能是五级，级数不能写死，直接取最后一个就行
            $crumbs = $this->crumbs;
            $menu_info = array_pop($crumbs);
            $menu_model = new Menu();
            $menus = $menu_model->getMenuList([ [ 'app_module', "=", $this->app_module ], [ 'is_show', "=", 1 ], [ 'parent', '=', $menu_info[ 'parent' ] ] ], '*', 'sort asc');
            foreach ($menus[ 'data' ] as $k => $v) {
                $menus[ 'data' ][ $k ][ 'parse_url' ] = addon_url($menus[ 'data' ][ $k ][ 'url' ], $params);
                if ($menus[ 'data' ][ $k ][ 'name' ] == $menu_info[ 'name' ]) {
                    $menus[ 'data' ][ $k ][ 'selected' ] = 1;
                } else {
                    $menus[ 'data' ][ $k ][ 'selected' ] = 0;
                }
            }
            $this->assign('forth_menu', $menus[ 'data' ]);
        }
    }

    /**
     * 添加日志
     * @param $action_name
     * @param array $data
     */
    protected function addLog($action_name, $data = [])
    {
        $user = new UserModel();
        $user->addUserLog($this->uid, $this->user_info[ 'username' ], $this->site_id, $action_name, $data);
    }

    /**
     * 切换风格
     * @return array
     */
    public function checkStyle()
    {
        $style = Array (
            'app/shop/view/base/style1.html',
            'app/shop/view/base/style2.html'
        );
        $type = input('type', 'old');
        $data = [];
        if ($type == 'old') {
            $data[ 'style' ] = $style[ 0 ];
        } else if ($type == 'new') {
            $data[ 'style' ] = $style[ 1 ];
        }
        $config_model = new ConfigModel();
        $res = $config_model->setStyle($data, $this->site_id);
        return $res;
    }

    /**
     * 获取真实的链接
     * @param $menu_list
     * @return mixed
     */
    protected function getRealMenu($menu_list)
    {
        if (empty($this->crumbs)) {
            return $menu_list;
        }
        if ($this->crumbs[ 0 ][ 'name' ] != 'PROMOTION_ROOT') return $menu_list;

        $config = new SystemConfig();
        $value = $config->getConfig([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', $this->app_module ], [ 'config_key', '=', 'COMMON_ADDON' ] ])[ 'data' ][ 'value' ];
        $promotion_addon = empty($value) ? [] : explode(',', $value[ 'promotion' ] ?? '');
        $tool_addon = empty($value) ? [] : explode(',', $value[ 'tool' ] ?? '');

        if (isset($this->crumbs[ 0 ])) {
            // 处理父级菜单不在营销下的插件
            $all_promotion = array_filter(array_column($menu_list[ 'PROMOTION_ROOT' ][ 'child_list' ][ 'PROMOTION_CENTER' ][ 'child_list' ], 'addon'));
            $promotion_diff = array_filter(array_diff($promotion_addon, $all_promotion));
            if (!empty($promotion_diff)) {
                foreach ($promotion_diff as $addon) {
                    $addon_menu = require 'addon/' . $addon . '/config/menu_' . $this->app_module . '.php';
                    $addon_info = require 'addon/' . $addon . '/config/info.php';
                    if (isset($addon_menu[ 0 ])) {
                        array_push($menu_list[ 'PROMOTION_ROOT' ][ 'child_list' ][ 'PROMOTION_CENTER' ][ 'child_list' ],
                            array_merge($addon_menu[ 0 ], [ 'title' => $addon_info[ 'title' ], 'selected' => false, 'url' => addon_url($addon_menu[ 0 ][ 'url' ]) ]));
                    }
                }
            }

            if(!empty($menu_list[ 'PROMOTION_ROOT' ][ 'child_list' ][ 'PROMOTION_TOOL' ])){
                $all_tool = array_filter(array_column($menu_list[ 'PROMOTION_ROOT' ][ 'child_list' ][ 'PROMOTION_TOOL' ][ 'child_list' ], 'addon'));
                $tool_diff = array_filter(array_diff($tool_addon, $all_tool));
                if (!empty($tool_diff)) {
                    foreach ($tool_diff as $addon) {
                        $addon_menu = require 'addon/' . $addon . '/config/menu_' . $this->app_module . '.php';
                        $addon_info = require 'addon/' . $addon . '/config/info.php';
                        if (isset($addon_menu[ 0 ])) {
                            array_push($menu_list[ 'PROMOTION_ROOT' ][ 'child_list' ][ 'PROMOTION_TOOL' ][ 'child_list' ],
                                array_merge($addon_menu[ 0 ], [ 'title' => $addon_info[ 'title' ], 'selected' => false, 'url' => addon_url($addon_menu[ 0 ][ 'url' ]) ]));
                        }
                    }
                }
            }


            foreach ($menu_list[ 'PROMOTION_ROOT' ][ 'child_list' ][ 'PROMOTION_CENTER' ][ 'child_list' ] as $k => &$val) {
                if (!empty($val[ 'addon' ]) && ( $val[ 'addon' ] != $this->addon && !in_array($val[ 'addon' ], $promotion_addon) )) {
                    unset($menu_list[ 'PROMOTION_ROOT' ][ 'child_list' ][ 'PROMOTION_CENTER' ][ 'child_list' ][ $k ]);
                }
            }
            if(!empty($menu_list[ 'PROMOTION_ROOT' ][ 'child_list' ][ 'PROMOTION_TOOL' ])){
                foreach ($menu_list[ 'PROMOTION_ROOT' ][ 'child_list' ][ 'PROMOTION_TOOL' ][ 'child_list' ] as $k => &$val) {
                    if (!empty($val[ 'addon' ]) && ( $val[ 'addon' ] != $this->addon && !in_array($val[ 'addon' ], $tool_addon) )) {
                        unset($menu_list[ 'PROMOTION_ROOT' ][ 'child_list' ][ 'PROMOTION_TOOL' ][ 'child_list' ][ $k ]);
                    }
                }
            }
            

        }
        return $menu_list;
    }

    public function __call($method, $args)
    {
        return $this->fetch(app()->getRootPath() . 'public/error/error.html');
    }
}