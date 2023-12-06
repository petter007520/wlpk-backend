<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace app\shop\controller;

use app\model\web\Adv as AdvModel;
use app\model\web\AdvPosition;


/**
 * 广告管理
 */
class Adv extends BaseShop
{

    /**
     * 广告位管理
     * @return mixed
     */
    public function index()
    {
        $adv_position = new AdvPosition();
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $type = input('type', '');//位置类型   1 电脑端  2 手机端

            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'type', '=', 2 ]
            ];
            if (!empty($search_text)) {
                $condition[] = [ 'ap_name', 'like', '%' . $search_text . '%' ];
            }
            if ($type !== '') {
                $condition[] = [ 'type', '=', $type ];
            }
            return $adv_position->getAdvPositionPageList($condition, $page, $page_size);
        } else {
            $this->forthMenu();
            return $this->fetch("adv/index");
        }
    }

    /**
     * 添加广告位
     */
    public function addPosition()
    {
        $adv_position = new AdvPosition();
        if (request()->isAjax()) {
            $data = [
                'ap_name' => input('ap_name', ''),
                'keyword' => input('keyword', ''),
                'ap_intro' => input('ap_intro', ''),
                'ap_height' => input('ap_height', 0),
                'ap_width' => input('ap_width', 0),
                'default_content' => input('default_content', ''),
                'ap_background_color' => input('ap_background_color', ''),
                'type' => input('type', 2),
                'state' => input('state', 0),
                'site_id' => $this->site_id
            ];
            return $adv_position->addAdvPosition($data);
        } else {
            return $this->fetch("adv/add_position");
        }
    }

    /**
     * 编辑广告位
     */
    public function editPosition()
    {
        $adv_position = new AdvPosition();
        $ap_id = input('ap_id', 0);
        if (request()->isAjax()) {
            $data = [
                'ap_name' => input('ap_name', ''),
                'keyword' => input('keyword', ''),
                'ap_intro' => input('ap_intro', ''),
                'ap_height' => input('ap_height', 0),
                'ap_width' => input('ap_width', 0),
                'default_content' => input('default_content', ''),
                'ap_background_color' => input('ap_background_color', ''),
                'state' => input('state', 0),
            ];
            return $adv_position->editAdvPosition($data, [ [ 'ap_id', '=', $ap_id ], [ 'site_id', '=', $this->site_id ] ]);
        } else {
            $ap_info = $adv_position->getAdvPositionInfo([ [ 'ap_id', '=', $ap_id ], [ 'site_id', '=', $this->site_id ] ]);
            $this->assign('info', $ap_info[ 'data' ]);
            return $this->fetch("adv/edit_position");
        }
    }


    /**
     * 修改广告位字段
     */
    public function editPositionField()
    {
        if (request()->isAjax()) {
            $adv_position = new AdvPosition();
            $type = input('type', '');
            $value = input('value', 0);
            $ap_id = input('ap_id', 0);
            $data = [
                $type => $value
            ];
            return $adv_position->editAdvPosition($data, [ [ 'ap_id', '=', $ap_id ] ]);
        }
    }


    /**
     * 删除广告位
     */
    public function deletePosition()
    {
        if (request()->isAjax()) {
            $ap_ids = input('ap_ids', 0);
            $adv_position = new AdvPosition();
            return $adv_position->deleteAdvPosition([ [ 'ap_id', 'in', $ap_ids ], [ 'site_id', '=', $this->site_id ] ], $ap_ids);
        }
    }

    /**
     * 广告列表
     */
    public function lists()
    {
        $adv = new AdvModel();
        $adv_position = new AdvPosition();

        $ap_id = input('ap_id', '');
        $keyword = input('keyword', '');
        if (!empty($keyword)) {
            $info = $adv_position->getAdvPositionInfo([ [ 'keyword', '=', $keyword ] ], 'ap_id')[ 'data' ];
            if (!empty($info)) {
                $ap_id = $info[ 'ap_id' ];
            }
        }

        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $slide_sort = input('sort', '');
            //查询所有手机端广告位
            $conditions[] = [ 'type', '=', 2 ];
            $positions = $adv_position->getAdvPositionList($conditions);
            $positions_ids = array_column($positions[ 'data' ], 'ap_id');
            $condition = [
                [ 'a.site_id', '=', $this->site_id ],
                [ 'a.ap_id', 'in', $positions_ids ]
            ];
            if (!empty($search_text)) {
                $condition[] = [ 'a.adv_title', 'like', '%' . $search_text . '%' ];
            }
            if ($ap_id !== '') {
                $condition[] = [ 'a.ap_id', '=', $ap_id ];
            }

            //排序
            $slide_sort = input('order', 'slide_sort');
            $sort = input('sort', 'desc');
            if ($slide_sort == 'slide_sort') {
                $order_by = 'a.' . $slide_sort . ' ' . $sort;
            } else {
                $order_by = 'a.' . $slide_sort . ' ' . $sort . ',a.slide_sort desc';
            }

            return $adv->getAdvPageList($condition, $page, $page_size, $order_by);
        } else {
            $this->assign('ap_id', $ap_id);
            $this->forthMenu();

            $adv_position = new AdvPosition();
            $adv_position_list = $adv_position->getAdvPositionList([ [ 'site_id', '=', $this->site_id ], [ 'type', '=', 2 ] ], 'ap_id,ap_name')[ 'data' ];
            $this->assign('adv_position', $adv_position_list);

            return $this->fetch("adv/lists");
        }
    }

    /**
     * 添加广告
     */
    public function addAdv()
    {
        $adv = new AdvModel();
        if (request()->isAjax()) {
            $data = [
                'ap_id' => input('ap_id', 0),
                'adv_title' => input('adv_title', ''),
                'adv_url' => input('adv_url', ''),
                'adv_image' => input('adv_image', ''),
                'slide_sort' => input('slide_sort', 0),
                'price' => input('price', 0),
                'background' => input('background', ''),
                'state' => input('state', 0),
                'site_id' => $this->site_id
            ];
            return $adv->addAdv($data);
        } else {
            $adv_position = new AdvPosition();
            $adv_position_list = $adv_position->getAdvPositionList([ [ 'site_id', '=', $this->site_id ], [ 'type', '=', 2 ] ]);
            $this->assign('adv_position_list', $adv_position_list[ 'data' ]);

            $ap_id = input('ap_id', 0);
            $this->assign('ap_id', $ap_id);

            return $this->fetch("adv/add_adv");
        }
    }

    /**
     * 编辑广告
     */
    public function editAdv()
    {
        $adv_id = input('adv_id', '');
        $adv = new AdvModel();
        if (request()->isAjax()) {
            $data = [
                'ap_id' => input('ap_id', 0),
                'adv_title' => input('adv_title', ''),
                'adv_url' => input('adv_url', ''),
                'adv_image' => input('adv_image', ''),
                'slide_sort' => input('slide_sort', 0),
                'price' => input('price', 0),
                'background' => input('background', ''),
//                'state' => input('state', 0),
            ];
            return $adv->editAdv($data, [ [ 'adv_id', '=', $adv_id ], [ 'site_id', '=', $this->site_id ] ]);
        } else {
            $adv_position = new AdvPosition();
            $adv_position_list = $adv_position->getAdvPositionList([ [ 'site_id', '=', $this->site_id ], [ 'type', '=', 2 ] ]);
            $this->assign('adv_position_list', $adv_position_list[ 'data' ]);
            $adv_info = $adv->getAdvInfo($adv_id);
            $this->assign('adv_info', $adv_info[ 'data' ]);
            // 得到当前广告图类型
            $type = 2;// 1 pc、2 wap
            foreach ($adv_position_list[ 'data' ] as $k => $v) {
                if ($v[ 'ap_id' ] == $adv_info[ 'data' ][ 'ap_id' ]) {
                    $type = $v[ 'type' ];
                    break;
                }
            }
            $this->assign("type", $type);

            $ap_id = input('ap_id', 0);
            $this->assign('ap_id', $ap_id);

            return $this->fetch("adv/edit_adv");
        }
    }

    /**
     * 修改广告字段
     */
    public function editAdvField()
    {
        if (request()->isAjax()) {
            $adv = new AdvModel();
            $type = input('type', '');
            $value = input('value', '');
            $adv_id = input('adv_id', '');
            $data = [
                $type => $value
            ];
            return $adv->editAdv($data, [ [ 'adv_id', '=', $adv_id ] ]);
        }
    }

    /**
     * 删除广告
     */
    public function deleteAdv()
    {
        if (request()->isAjax()) {
            $adv_ids = input('adv_ids', 0);
            $adv = new AdvModel();
            return $adv->deleteAdv([ [ 'adv_id', 'in', $adv_ids ], [ 'site_id', '=', $this->site_id ] ]);
        }
    }

    /**
     * 修改广告位状态
     * @return array
     */
    public function alterAdvPositionState()
    {
        if (request()->isAjax()) {
            $ap_id = input('ap_id', 0);
            $state = input('state', 0);
            $ap_model = new AdvPosition();
            return $ap_model->editAdvPosition([ 'state' => $state ], [ [ 'ap_id', '=', $ap_id ], [ 'site_id', '=', $this->site_id ] ]);
        }
    }

    /**
     * 修改广告状态
     * @return array
     */
    public function alterAdvState()
    {
        if (request()->isAjax()) {
            $adv_id = input('adv_id', 0);
            $state = input('state', 0);
            $ap_model = new AdvModel();
            return $ap_model->editAdv([ 'state' => $state ], [ [ 'adv_id', '=', $adv_id ], [ 'site_id', '=', $this->site_id ] ]);
        }
    }

}