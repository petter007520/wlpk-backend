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

use addon\weapp\model\Config as WeappConfig;
use app\model\diy\Template;
use app\model\diy\Theme;
use app\model\goods\GoodsCategory;
use app\model\shop\Shop as ShopModel;
use app\model\web\DiyView as DiyViewModel;
use addon\wechat\model\Config as WechatConfig;
use app\model\web\DiyViewLink;

/**
 * 网站装修控制器
 */
class Diy extends BaseShop
{

    public function __construct()
    {
        $this->app_module = input('app_module', SHOP_MODULE);
        if ($this->app_module == 'store') {
            $this->initConstructInfo(); // 加载构造函数重要信息
        } else {
            parent::__construct();
        }
    }

    /**
     * 管理预览
     * @return mixed
     */
    public function management()
    {

        $shop_model = new ShopModel();
        $qrcode_info = $shop_model->qrcode($this->site_id)[ 'data' ];
        $this->assign('qrcode_info', $qrcode_info[ 'path' ][ 'h5' ] ?? '');

        $wechat_config_model = new WechatConfig();
        $wechat_config_result = $wechat_config_model->getWechatConfig($this->site_id)[ "data" ][ 'value' ];
        $this->assign('wechat_config', $wechat_config_result);

        $weapp_config_model = new WeappConfig();
        $weapp_config_result = $weapp_config_model->getWeappConfig($this->site_id)[ 'data' ][ 'value' ];
        $this->assign('weapp_config', $weapp_config_result);

        $store_business = 'shop'; // 店铺运营模式

        // 检测店铺运营模式
        if (addon_is_exit('store')) {
            $config_model = new \addon\store\model\Config();
            $business_config = $config_model->getStoreBusinessConfig($this->site_id)[ 'data' ][ 'value' ];
            $store_business = $business_config[ 'store_business' ];
        }

        $this->assign('store_is_exit', addon_is_exit('store'));
        $this->assign('store_business', $store_business);

        return $this->fetch('diy/management');
    }

    /**
     * 网站主页
     */
    public function index()
    {
        $data = [
            'site_id' => $this->site_id,
            'name' => 'DIY_VIEW_INDEX'
        ];
        $edit_view = event('DiyViewEdit', $data, true);
        return $edit_view;
    }

    /**
     * 商品分类页面
     */
    public function goodsCategory()
    {
        $data = [
            'site_id' => $this->site_id,
            'name' => 'DIY_VIEW_GOODS_CATEGORY'
        ];
        $edit_view = event('DiyViewEdit', $data, true);
        return $edit_view;
    }

    /**
     * 会员中心
     */
    public function memberIndex()
    {
        $data = [
            'site_id' => $this->site_id,
            'name' => 'DIY_VIEW_MEMBER_INDEX'
        ];
        $edit_view = event('DiyViewEdit', $data, true);
        return $edit_view;
    }

    /**
     * 编辑
     */
    public function edit()
    {
        if (request()->isAjax()) {
            $res = 0;
            $id = input('id', 0);
            $name = input('name', '');
            $title = input('title', '');
            $value = input('value', '');
            $template_id = input('template_id', 0); // 所属模板id
            $page_type = input('page_type', ''); // 页面类型
            if (!empty($name) && !empty($title) && !empty($value)) {
                $diy_view = new DiyViewModel();
                $data = [
                    'site_id' => $this->site_id,
                    'name' => $name,
                    'title' => $title,
                    'value' => $value,
                    'template_id' => $template_id
                ];
                if (!empty($page_type)) {
                    $diy_template_model = new Template();
                    $template_info = $diy_template_model->getTemplateInfo([ [ 'name', '=', $page_type ] ], 'title,name')[ 'data' ];
                    $data[ 'type' ] = $template_info[ 'name' ];
                    $data[ 'type_name' ] = $template_info[ 'title' ];
                } else {
                    $data[ 'is_default' ] = 1; // 自定义页面，默认为1
                }
                if ($id == 0) {
                    $res = $diy_view->addSiteDiyView($data);
                } else {
                    $res = $diy_view->editSiteDiyView($data, [ [ 'id', '=', $id ] ]);
                }
            }
            return $res;
        } else {
            $id = input('id', 0);
            $template_id = input('template_id', 0);
            $title = input('title', '');
            $page_type = input('page_type', ''); // 页面类型

            $data = [
                'site_id' => $this->site_id,
                'id' => $id,
                'template_id' => $template_id,
                'title' => $title,
                'page_type' => $page_type
            ];
            $edit_view = event('DiyViewEdit', $data, true);

            return $edit_view;
        }
    }

    /**
     * 微页面
     */
    public function lists()
    {
        $type = input('type', ''); // 页面类型

        $diy_template_goods_model = new Template();

        // 页面类型
        $template_list = $diy_template_goods_model->getTemplateList([], 'id,title,name')[ 'data' ];
        $template_list_arr = array_column($template_list, 'name');

        if (request()->isAjax()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', ''); // 页面名称
            $template_id = input('template_id', 0); // 所属模板id
            $condition = [
                [ 'site_id', '=', $this->site_id ],
//                [ 'name', 'like', [ '%DIY_VIEW_RANDOM_%', 'DIY_VIEW_INDEX' ], 'or' ]
            ];

            if (!empty($search_text)) {
                $condition[] = [ 'title', 'like', '%' . $search_text . '%' ];
            }

            if (!empty($type) && $type != 'ALL') {
                $condition[] = [ 'type', '=', $type ];
            }

            if (!empty($template_id)) {
                $condition[] = [ 'template_id', '=', $template_id ];
            }

            $field = 'id, name, title, template_id, template_item_id, type, type_name, click_num, is_default, sort, create_time';

            $order_by = "is_default desc,INSTR('" . implode(',', $template_list_arr) . "', name) desc, create_time desc";

            $diy_view = new DiyViewModel();
            $list = $diy_view->getSiteDiyViewPageList($condition, $page_index, $page_size, $order_by, $field);
            return $list;
        } else {
            if (empty($type)) {
                $type = 'ALL';
            }
            $this->assign('type', $type);

            // 店铺模板
            $template_goods_list = $diy_template_goods_model->getTemplateGoodsList([], 'goods_id, title, name')[ 'data' ];
            $this->assign('template_goods_list', $template_goods_list);

            // 选择页面类型
            $page_type_list = [];
            foreach ($template_list as $k => $v) {
                $icon = '';
                if ($v[ 'name' ] == 'DIY_VIEW_INDEX') {
                    $icon = 'iconfont icondianpushouye';
                } elseif ($v[ 'name' ] == 'DIY_VIEW_GOODS_CATEGORY') {
                    $icon = 'iconfont iconshangpinfenlei2';
                } elseif ($v[ 'name' ] == 'DIY_VIEW_MEMBER_INDEX') {
                    $icon = 'iconfont iconhuiyuanzhongxin';
                } elseif ($v[ 'name' ] == 'DIY_STORE') {
                    $icon = 'iconfont iconmendianzhuye';
                }

                $page_type_list[] = [
                    'title' => $v[ 'title' ],
                    'name' => $v[ 'name' ],
                    'preview' => 'public/static/ext/diyview/img/preview/' . strtolower($v[ 'name' ]) . '.jpg',
                    'icon' => $icon
                ];
            }
            $page_type_list[] = [
                'title' => '自定义页面',
                'name' => 'DIY_PAGE',
                'preview' => 'public/static/ext/diyview/img/preview/diy_page.jpg',
                'icon' => 'iconfont iconzidingyiyemian'
            ];
            $this->assign('page_type_list', $page_type_list);

            $this->getPageCount();

            return $this->fetch('diy/lists');
        }
    }

    /**
     * 获取页面数量
     * @return array
     */
    public function getPageCount()
    {
        $diy_view = new DiyViewModel();
        $diy_template_goods_model = new Template();
        $template_list = $diy_template_goods_model->getTemplateList([], 'id,title,name')[ 'data' ];

        array_unshift($template_list, [
            'id' => 0,
            'title' => '全部页面',
            'name' => 'ALL'
        ]);
        $template_list[] = [
            'id' => 0,
            'title' => '自定义页面',
            'name' => 'DIY_PAGE'
        ];
        foreach ($template_list as $k => $v) {
            $condition = [
                [ 'site_id', '=', $this->site_id ]
            ];
            if ($v[ 'name' ] != 'ALL') {
                $condition[] = [ 'type', '=', $v[ 'name' ] ];
            }
            $template_list[ $k ][ 'count' ] = $diy_view->getSiteViewCount($condition)[ 'data' ];
        }
        if (request()->isAjax()) {
            return success(0, '', $template_list);
        } else {
            $this->assign('template_list', $template_list);
        }
    }

    /**
     * 页面路径
     * @return mixed
     */
    public function route()
    {
        if (request()->isAjax()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $condition = [
                [ 'wap_url', '<>', '' ]
            ];

            if (!empty($search_text)) {
                $condition[] = [ 'title', 'like', '%' . $search_text . '%' ];
            }

            $order_by = 'id asc';
            $field = 'id, name, title, wap_url';

            $link_model = new DiyViewLink();
            $list = $link_model->getLinkPageList($condition, $page_index, $page_size, $order_by, $field);
            return $list;
        } else {
            $h5_domain = getH5Domain();
            $this->assign('h5_domain', $h5_domain);
            return $this->fetch('diy/route');
        }
    }

    /**
     * 根据页面路径生成推广链接
     * @return array
     */
    public function promoteRoute()
    {
        if (request()->isAjax()) {
            $name = input('name', ''); // 链接标识
            $path = input('path', ''); // 链接地址
            $app_type = input('app_type', 'all'); // 端口类型
            $diy_view = new DiyViewModel();
            $res = $diy_view->qrcodeRoute([
                'site_id' => $this->site_id,
                'name' => $name,
                'path' => $path,
                'app_type' => $app_type
            ]);

            return $res;
        }
    }

    /**
     * 链接选择
     */
    public function link()
    {
        if (request()->isAjax()) {
            $link = input('link', '');
            $link_model = new DiyViewLink();
            $tree_params = [
                'site_id' => $this->site_id,
            ];
            $list = $link_model->getLinkTree($tree_params)[ 'data' ];
            $this->assign('list', $list);

            $select_link = json_decode($link, true);
            $this->assign('select_link', $select_link);

            // 商品分类
            $goods_category_model = new GoodsCategory();
            $goods_category_field = 'category_id,category_name,short_name,pid,level,image';
            $category_list = $goods_category_model->getCategoryTree([
                [ 'site_id', '=', $this->site_id ]
            ], $goods_category_field)[ 'data' ];
            $this->assign('category_list', $category_list);

            return $this->fetch('diy/link');
        }
    }

    /**
     * 删除自定义模板页面
     */
    public function deleteSiteDiyView()
    {
        if (request()->isAjax()) {
            $diy_view = new DiyViewModel();
            $id_array = input('id', 0);
            $condition = [
                [ 'id', 'in', $id_array ]
            ];
            $res = $diy_view->deleteSiteDiyView($condition);
            return $res;
        }
    }

    /**
     * 复制自定义模板页面
     */
    public function copySiteDiyView()
    {
        if (request()->isAjax()) {
            $diy_view = new DiyViewModel();
            $id_array = input('id', 0);
            $condition = [
                [ 'id', '=', $id_array ]
            ];
            // 获取被复制数据
            $data = $diy_view->getSiteDiyViewInfo($condition, '*');
            $data = $data[ 'data' ];
            unset($data[ 'id' ]);
            // 对数据进行处理
            $value = json_decode($data[ 'value' ], true);
            $data[ 'title' ] .= '_副本';
            $value[ 'global' ][ 'title' ] = $data[ 'title' ];
            $data[ 'value' ] = json_encode($value, JSON_UNESCAPED_UNICODE);
            $data[ 'create_time' ] = time();
            $data[ 'click_num' ] = 0;
            if ($data[ 'type' ] == 'DIY_PAGE') {
                $data[ 'is_default' ] = 1;
                $data[ 'name' ] = 'DIY_VIEW_RANDOM_' . time();
            } else {
                $data[ 'is_default' ] = 0; // 特定页面设为0
            }
            // 新增新数据
            $res = $diy_view->addSiteDiyView($data);
            return $res;
        }
    }

    /**
     * 底部导航
     */
    public function bottomNavDesign()
    {
        $diy_view = new DiyViewModel();
        if (request()->isAjax()) {
            $value = json_decode(input("value", ""), true);
            $res = $diy_view->setBottomNavConfig($value, $this->site_id);
            return $res;
        } else {
            $bottom_nav_info = $diy_view->getBottomNavConfig($this->site_id);
            $this->assign('bottom_nav_info', $bottom_nav_info[ 'data' ][ 'value' ]);
            return $this->fetch('diy/bottom_nav_design');
        }
    }

    /**
     * 店铺风格
     */
    public function style()
    {
        $diy_view = new DiyViewModel();
        $diy_theme_model = new Theme();
        if (request()->isAjax()) {
            $data = [
                'id' => input('id', 0),
                'title' => input('title', ''),
                'name' => input('name', ''),
                'main_color' => input('main_color', ''),
                'aux_color' => input('aux_color', '')
            ];
            $res = $diy_view->setStyleConfig($data, $this->site_id);
            $bottom_nav = $diy_view->getBottomNavConfig($this->site_id)[ 'data' ][ 'value' ];

            // 修改底部导航配色
            if ($bottom_nav[ 'type' ] == 1 || $bottom_nav[ 'type' ] == 2) {
                $bottom_nav[ 'textHoverColor' ] = $data[ 'main_color' ];
            }
            foreach ($bottom_nav[ 'list' ] as $k => $v) {
                if ($v[ 'selected_icon_type' ] == 'icon') {
                    $bottom_nav[ 'list' ][ $k ][ 'selected_style' ][ 'iconColor' ] = [ $data[ 'main_color' ] ];
                }
            }
            $diy_view->setBottomNavConfig($bottom_nav, $this->site_id);
            return $res;
        } else {
            // 主题风格
            $theme_list = $diy_theme_model->getThemeList([], 'id,title,name,main_color,aux_color,preview,color_img')[ 'data' ];
            if (!empty($theme_list)) {
                foreach ($theme_list as $k => $v) {
                    if (!empty($v[ 'preview' ])) {
                        $theme_list[ $k ][ 'preview' ] = explode(',', $v[ 'preview' ]);
                    }
                }
            }
            $this->assign('theme_list', $theme_list);

            $style = $diy_view->getStyleConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('style', $style);

            return $this->fetch('diy/style');
        }
    }

    /**
     * 自定义模板选择
     * @return array|mixed
     */
    public function template()
    {
        $diy_view = new DiyViewModel();
        $diy_theme_model = new Theme();

        // 主题风格
        $theme_list = $diy_theme_model->getThemeList([], 'id,title,name,main_color,aux_color,preview,color_img')[ 'data' ];
        if (!empty($theme_list)) {
            foreach ($theme_list as $k => $v) {
                if (!empty($v[ 'preview' ])) {
                    $theme_list[ $k ][ 'preview' ] = explode(',', $v[ 'preview' ]);
                }
            }
        }
        $this->assign('theme_list', $theme_list);

        $style = $diy_view->getStyleConfig($this->site_id)[ 'data' ][ 'value' ];
        $this->assign('style', $style);

        $diy_template = new Template();

        // 查询店铺正在使用的模板
        $site_diy_template_info = $diy_template->getSiteDiyTemplateInfo([
            [ 'is_default', '=', 1 ],
            [ 'site_id', '=', $this->site_id ]
        ], 'id,name,template_goods_id')[ 'data' ];

        $template_goods_list = $diy_template->getTemplateGoodsList([], 'goods_id,title,name,cover,preview,desc,goods_item_id')[ 'data' ];
        if (!empty($site_diy_template_info)) {
            foreach ($template_goods_list as $k => $v) {
                $template_goods_list[ $k ][ 'is_default' ] = 0;
                if ($v[ 'goods_id' ] == $site_diy_template_info[ 'template_goods_id' ]) {
                    $template_goods_list[ $k ][ 'is_default' ] = 1;
                }
            }
        }
        $this->assign('template', $template_goods_list);

        return $this->fetch('diy/template');
    }

    /**
     * 创建
     */
    public function create()
    {
        if (request()->isAjax()) {
            $goods_id = input('goods_id', 0);
            $diy_template = new Template();
            $res = $diy_template->useTemplate([
                'site_id' => $this->site_id,
                'goods_id' => $goods_id,
            ]);
            return $res;
        }
    }

    /**
     * 设为使用
     */
    public function setUse()
    {
        if (request()->isAjax()) {
            $diy_view = new DiyViewModel();
            $id = input('id', 0);
            $res = $diy_view->setUse($id, $this->site_id);
            return $res;
        }
    }

    /**
     * 修改排序
     */
    public function modifySort()
    {
        if (request()->isAjax()) {
            $sort = input('sort', 0);
            $id = input('id', 0);
            $diy_view = new DiyViewModel();
            return $diy_view->modifyDiyViewSort($sort, $id);
        }
    }

    /**
     * 热区设置
     */
    public function heatMap()
    {
        return $this->fetch('diy/heat_map');
    }

    /**
     * 矢量图库
     * @return array|mixed
     */
    public function iconfont()
    {
        $diy_view = new DiyViewModel();
        $icon = input('icon', '');
        if (request()->isAjax()) {
            $type = input('type', 'icon'); // 图标类型
            $icon_list = $diy_view->getIconList($type);
            return $icon_list;
        } else {
            $icon_type = $diy_view->getIconType();
            $this->assign('icon_type', $icon_type);
            $this->assign('icon', $icon);
            return $this->fetch('diy/iconfont');
        }
    }

    /**
     * icon风格设置
     * @return mixed
     */
    public function iconStyleSet()
    {
        $icon = input('icon', '');
        $this->assign('icon', $icon);
        $this->assign('icon_style', ( new DiyViewModel() )->iconStyle());
        return $this->fetch('diy/icon_style');
    }

    public function selectIconStyle()
    {
        $icon = input('icon', '');
        $this->assign('icon', $icon);
        $this->assign('icon_style', ( new DiyViewModel() )->iconStyle());
        return $this->fetch('diy/select_icon_style');
    }

    public function selectLabel()
    {
        $this->assign('data', input());
        return $this->fetch('diy/select_label');
    }

    public function getImgIcon()
    {
        $data = input('data', '');
        $this->assign('data', urldecode($data));
        $this->assign('id', 'id_' . time() . mt_rand(0000, 9999));
        return $this->fetch('diy/icon_img_view');
    }

}