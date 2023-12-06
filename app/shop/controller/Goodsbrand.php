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

use app\model\goods\GoodsBrand as GoodsBrandModel;

/**
 * 商品品牌管理 控制器
 */
class Goodsbrand extends BaseShop
{
    /**
     * 商品品牌列表
     */
    public function lists()
    {
        if (request()->isAjax()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_keys = input('search_keys', "");
            $condition = [
                [ 'site_id', '=', $this->site_id ]
            ];
            if (!empty($search_keys)) {
                $condition[] = [ 'brand_name', 'like', '%' . $search_keys . '%' ];
            }
            $goods_brand_model = new GoodsBrandModel();
            $list = $goods_brand_model->getBrandPageList($condition, $page_index, $page_size, 'create_time desc', 'brand_id,brand_name,brand_initial,image_url,sort,create_time,site_id');
            return $list;
        } else {
            return $this->fetch('goodsbrand/lists');
        }
    }

    /**
     * 商品品牌添加
     */
    public function addBrand()
    {
        if (request()->isAjax()) {
            $brand_name = input('brand_name', '');
            $brand_initial = input('brand_initial', '');
            $image_url = input('image_url', "");
            $sort = input('sort', 0);
            $data = [
                'site_id' => $this->site_id,
                'brand_name' => $brand_name,
                'brand_initial' => $brand_initial,
                'image_url' => $image_url,
                'sort' => $sort
            ];
            $goods_brand_model = new GoodsBrandModel();
            $res = $goods_brand_model->addBrand($data);
            return $res;
        } else {
            return $this->fetch('goodsbrand/add_brand');
        }
    }

    /**
     * 商品品牌编辑
     */
    public function editBrand()
    {
        $goods_brand_model = new GoodsBrandModel();
        if (request()->isAjax()) {
            $brand_id = input('brand_id', 0);
            $brand_name = input('brand_name', '');
            $brand_initial = input('brand_initial', '');
            $image_url = input('image_url', "");
            $sort = input('sort', 0);
            $data = [
                'brand_id' => $brand_id,
                'brand_name' => $brand_name,
                'brand_initial' => $brand_initial,
                'image_url' => $image_url,
                'sort' => $sort
            ];
            $condition = array (
                [ 'brand_id', '=', $data[ 'brand_id' ] ],
                [ 'site_id', '=', $this->site_id ]
            );
            $res = $goods_brand_model->editBrand($data, $condition);
            return $res;
        } else {
            $brand_id = input('brand_id', 0);
            $brand_info = $goods_brand_model->getBrandInfo([ [ 'brand_id', '=', $brand_id ] ])[ 'data' ];
            $this->assign("brand_info", $brand_info);
            return $this->fetch('goodsbrand/edit_brand');
        }
    }

    /**
     * 商品品牌删除
     */
    public function deleteBrand()
    {
        if (request()->isAjax()) {
            $brand_id = input("brand_id", 0);
            $goods_brand_model = new GoodsBrandModel();
            $condition = [
                [ "brand_id", '=', $brand_id ],
                [ 'site_id', '=', $this->site_id ]
            ];
            $res = $goods_brand_model->deleteBrand($condition);
            return $res;
        }
    }

    /**
     * 修改排序
     */
    public function modifySort()
    {
        $sort = input('sort', 0);
        $brand_id = input('brand_id', 0);
        $goods_brand_model = new GoodsBrandModel();
        $condition = array (
            [ 'brand_id', '=', $brand_id ],
            [ 'site_id', '=', $this->site_id ]
        );
        $res = $goods_brand_model->modifyBrandSort($sort, $condition);
        return $res;
    }

    /**
     * 品牌选择
     * @return array|mixed
     */
    public function brandSelect()
    {
        $goods_brand_model = new GoodsBrandModel();
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $brand_ids = input('brand_ids', '');
            $condition = [
                [ 'site_id', '=', $this->site_id ]
            ];
            if (!empty($search_text)) {
                if (!empty($brand_ids)) {
                    $condition[] = [ '', 'exp', \think\facade\Db::raw("brand_name like '%{$search_text}%' or brand_id in ({$brand_ids})") ];
                }else {
                    $condition[] = [ 'brand_name', 'like', '%' . $search_text . '%' ];
                }
            }

            $list = $goods_brand_model->getBrandPageList($condition, $page, $page_size, 'create_time desc', 'brand_id,brand_name,brand_initial,image_url');
            return $list;
        } else {
            //已经选择的商品sku数据
            $select_id = input('select_id', '');
            $this->assign('select_id', $select_id);
            $brand_list = $goods_brand_model->getBrandList([
                [ 'site_id', '=', $this->site_id ],
                [ 'brand_id', 'in', $select_id ]
            ], 'brand_id,brand_name,brand_initial,image_url')[ 'data' ];
            $this->assign('brand_list', $brand_list);
            return $this->fetch("goodsbrand/brand_select");
        }
    }
}