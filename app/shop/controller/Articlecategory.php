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

use app\model\article\ArticleCategory as CategoryModel;

/**
 * 文章分类
 */
class Articlecategory extends BaseShop
{

    /*
     *  文章分类列表
     */
    public function lists()
    {
        $model = new CategoryModel();

        $condition[] = ['site_id', '=', $this->site_id];
        if (request()->isAjax()) {

            $page      = (int)input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            //排序
            $order = input('order', 'sort');
            $sort = input('sort', 'desc');
            if($order == 'sort'){
                $order_by = $order . ' ' . $sort;
            }else{
                $order_by = $order . ' ' . $sort.',sort desc';
            }
            $list      = $model->getArticleCategoryPageList($condition, $page, $page_size, $order_by);
            return $list;
        } else {
            $this->forthMenu();
            return $this->fetch('articlecategory/lists');
        }
    }

    /**
     * 添加分组
     */
    public function add()
    {
        if (request()->isAjax()) {

            $data = [
                'site_id'    => $this->site_id,
                'category_name' => input('category_name', ''),
                'sort'       => input('sort'),
            ];

            $category_model = new CategoryModel();
            return $category_model->addArticleCategory($data);
        }
    }

    /**
     * 编辑分组
     */
    public function edit()
    {
        if (request()->isAjax()) {

            $data = [
                'category_id'   => input('category_id'),
                'site_id'    => $this->site_id,
                'category_name' => input('category_name', ''),
                'sort'       => input('sort'),
            ];

            $category_model = new CategoryModel();
            return $category_model->editArticleCategory([['site_id', '=', $this->site_id], ['category_id', '=', $data['category_id']]], $data);
        }
    }

    /**
     * 编辑分组排序
     * @return array
     */
    public function modifySort()
    {
        if (request()->isAjax()) {

            $data        = [
                'category_id' => input('category_id'),
                'site_id'  => $this->site_id,
                'sort'     => input('sort'),
            ];
            $category_model = new CategoryModel();
            return $category_model->editArticleCategory([['site_id', '=', $this->site_id], ['category_id', '=', $data['category_id']]], $data);
        }
    }

    /*
     *  删除分组
     */
    public function delete()
    {
        $category_id = input('category_id', '');
        $site_id  = $this->site_id;

        $category_model = new CategoryModel();
        return $category_model->deleteArticleCategory($category_id, $site_id);
    }

}