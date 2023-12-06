<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\event;

use app\Controller;
use app\model\diy\Template;
use app\model\web\DiyView as DiyViewModel;

/**
 * 自定义页面编辑
 */
class DiyViewEdit extends Controller
{
    /**
     * 行为扩展的执行入口必须是run
     * @param $data
     * @return mixed
     */
    public function handle($data)
    {

        $diy_view = new DiyViewModel();
        $diy_template = new Template();

        if (empty($data[ 'is_default' ])) {
            $data[ 'is_default' ] = 1;
        }

        $template_id = $data[ 'template_id' ] ?? 0; // 所属模板id
        $title = $data[ 'title' ] ?? ''; // 页面名称

        // 页面类型
        if (!empty($data[ 'page_type' ])) {
            $name = $data[ 'page_type' ];
        } else {
            $name = 'DIY_VIEW_RANDOM_' . time();
        }

        if (!empty($data[ 'id' ]) || !empty($data[ 'name' ])) {
            $diy_view_condition = [
                [ 'site_id', '=', $data[ 'site_id' ] ],
            ];

            if (!empty($data[ 'id' ])) {
                $diy_view_condition[] = [ 'id', '=', $data[ 'id' ] ];
            } elseif (!empty($data[ 'name' ])) {
                $diy_view_condition[] = [ 'name', '=', $data[ 'name' ] ];
                $name = $data[ 'name' ];

                // 查询模板页面类型
                $diy_template_info = $diy_template->getTemplateInfo([ [ 'name', '=', $name ] ], 'name')[ 'data' ];
                if (!empty($diy_template_info)) {
                    $diy_view_condition[] = [ 'is_default', '=', 1 ];
                }
            }

            $diy_view_info = $diy_view->getSiteDiyViewDetail($diy_view_condition)[ 'data' ];
            if (!empty($diy_view_info)) {
                $name = $diy_view_info[ 'name' ];
                $template_id = $diy_view_info[ 'template_id' ];
            }
            $this->assign("diy_view_info", $diy_view_info);
        }

        // 查询模板页面类型
        $diy_template_info = $diy_template->getTemplateInfo([ [ 'name', '=', $name ] ], 'title,name,rule')[ 'data' ];

        $diy_view_utils = [];
        $extend_comp = []; // 第三方扩展的特定页面组件

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

            $diy_view_utils[] = [
                'type' => $diy_template_info[ 'name' ],
                'type_name' => '页面组件',
                'list' => []
            ];
            $title = $diy_template_info[ 'title' ];
            $this->assign('page_type', $diy_template_info[ 'name' ]);
        } else {
            // 自定义页面，只查询公共组件
            $util_condition[] = [ 'support_diy_view', '=', '' ];
        }

        $utils = $diy_view->getDiyViewUtilList($util_condition)[ 'data' ];

        if (!empty($utils)) {

            // 先遍历，组件分类
            foreach ($utils as $k => $v) {
                $value = [];
                $value[ 'type' ] = $v[ 'type' ];
                $value[ 'type_name' ] = $diy_view->getTypeName($v[ 'type' ]);
                $value[ 'list' ] = [];

                if (!in_array($value, $diy_view_utils)) {
                    array_push($diy_view_utils, $value);
                }
            }

            // 遍历每一个组件，将其添加到对应的分类中
            foreach ($utils as $k => $v) {
                foreach ($diy_view_utils as $diy_k => $diy_v) {
                    $is_add = true;
                    if (!empty($v[ 'addon_name' ])) {
                        $is_exit = addon_is_exit($v[ 'addon_name' ], $data[ 'site_id' ]);
                        // 检查插件是否存在
                        if ($is_exit == 0) {
                            $is_add = false;
                        }
                    }

                    // 特定页面组件归类
                    if (!empty($v[ 'support_diy_view' ]) && $v[ 'support_diy_view' ] == $diy_v[ 'type' ] && $is_add) {
                        if ($v[ 'type' ] == 'EXTEND') {
                            // 第三方扩展的特定页面组件
                            $extend_comp[] = $v;
                        } else {
                            array_push($diy_view_utils[ $diy_k ][ 'list' ], $v);
                        }
                        break;
                    } elseif ($diy_v[ 'type' ] == $v[ 'type' ] && $is_add) {
                        array_push($diy_view_utils[ $diy_k ][ 'list' ], $v);
                    }
                }
            }
        }

        // 第三方组件——>特定页面组件
        if (!empty($extend_comp)) {
            foreach ($diy_view_utils as $k => $v) {
                if ($v[ 'type' ] == 'EXTEND') {
                    if (empty($v[ 'list' ])) {
                        $diy_view_utils[ $k ][ 'type_name' ] = '页面组件';
                        $diy_view_utils[ $k ][ 'list' ] = array_merge($extend_comp, $diy_view_utils[ $k ][ 'list' ]);
                    } else {
                        // 页面组件排在第一位置
                        array_splice($diy_view_utils, $k, 0, [
                            [
                                'type' => 'EXTEND',
                                'type_name' => '页面组件',
                                'list' => $extend_comp
                            ]
                        ]);
                    }
                }
            }
        }

        // 清除组件分类下空列表
        foreach ($diy_view_utils as $k => $v) {
            if (empty($v[ 'list' ])) {
                unset($diy_view_utils[ $k ]);
            }
        }

        $diy_view_utils = array_values($diy_view_utils);

        $this->assign('diy_view_utils', $diy_view_utils);

        $this->assign("time", time());
        $this->assign("name", $name); // 页面标识

        $this->assign('template_id', $template_id); // 所属模板id
        $this->assign('title', $title); // 页面名称

        $request_url = 'shop/diy/edit';

        $replace = [];
        if (!empty($data[ 'app_module' ]) && $data[ 'app_module' ] == 'store') {
            $replace = [
                'STORE_CSS' => __ROOT__ . '/addon/store/store/view/public/css',
                'STORE_JS' => __ROOT__ . '/addon/store/store/view/public/js',
                'STORE_IMG' => __ROOT__ . '/addon/store/store/view/public/img',
            ];
            $this->assign("extend_base", 'addon/store/store/view/base.html');
        } else {
            $this->assign("extend_base", 'app/shop/view/base.html');
        }

        $this->assign("request_url", $request_url);

        $this->assign('store_is_exit', addon_is_exit('store', $data[ 'site_id' ]));
        $template = dirname(realpath(__DIR__)) . '/shop/view/diy/edit.html';
        return $this->fetch($template, [], $replace);
    }

}