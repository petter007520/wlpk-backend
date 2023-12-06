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

use app\model\article\Article as ArticleModel;
use app\model\article\ArticleCategory;

/**
 * 文章
 * @package app\shop\controller
 */
class Article extends BaseShop
{

    /**
     * 文章列表
     */
    public function lists()
    {
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $condition = [ [ 'pn.site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ];
            $condition[] = [ 'pn.article_title', 'like', '%' . $search_text . '%' ];
            $order_by = 'pn.create_time desc';

            $article_model = new ArticleModel();
            return $article_model->getArticlePageList($condition, $page, $page_size, $order_by);
        } else {
            $this->forthMenu();
            return $this->fetch('article/lists');
        }
    }

    /**
     * 草稿箱
     */
    public function drafts()
    {
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $condition = [ [ 'pn.site_id', '=', $this->site_id ], [ 'status', '=', 0 ] ];
            $condition[] = [ 'pn.article_title', 'like', '%' . $search_text . '%' ];
            $order_by = 'pn.create_time desc';

            $article_model = new ArticleModel();
            return $article_model->getArticlePageList($condition, $page, $page_size, $order_by);
        } else {
            $this->forthMenu();
            return $this->fetch('article/drafts');
        }
    }

    /**
     * 文章添加
     */
    public function add()
    {
        $article_model = new ArticleModel();
        if (request()->isAjax()) {
            $articles_data = [
                'site_id' => $this->site_id,
                'article_title' => input('article_title', ''),
                'article_abstract' => input('article_abstract', ''),
                'category_id' => input('category_id', ''),
                'cover_img' => input('cover_img', ''),
                'article_content' => input('article_content', ''),
                'status' => input('status', ''),
                'sort' => input('sort', '0'),
                'is_show_release_time' => input('is_show_release_time', ''),
                'is_show_read_num' => input('is_show_read_num', ''),
                'is_show_dianzan_num' => input('is_show_dianzan_num', ''),
                'initial_read_num' => input('initial_read_num', ''),
                'initial_dianzan_num' => input('initial_dianzan_num', '')
            ];
            return $article_model->addArticle($articles_data);
        } else {
            $article_category_model = new ArticleCategory();
            $article_category_list = $article_category_model->getArticleCategoryList([ [ 'site_id', '=', $this->site_id ] ], 'category_id, category_name')[ 'data' ];
            $this->assign('category_list', $article_category_list);

            return $this->fetch('article/add');
        }
    }

    /**
     * 帮助编辑
     */
    public function edit()
    {
        $article_id = input('article_id', 0);
        $article_model = new ArticleModel();
        if (request()->isAjax()) {
            $articles_data = [
                'article_id' => $article_id,
                'site_id' => $this->site_id,
                'article_title' => input('article_title', ''),
                'article_abstract' => input('article_abstract', ''),
                'category_id' => input('category_id', ''),
                'cover_img' => input('cover_img', ''),
                'article_content' => input('article_content', ''),
                'status' => input('status', ''),
                'sort' => input('sort', '0'),
                'is_show_release_time' => input('is_show_release_time', ''),
                'is_show_read_num' => input('is_show_read_num', ''),
                'is_show_dianzan_num' => input('is_show_dianzan_num', ''),
                'initial_read_num' => input('initial_read_num', ''),
                'initial_dianzan_num' => input('initial_dianzan_num', '')
            ];
            return $article_model->editArticle($articles_data);
        } else {
            $this->assign('article_id', $article_id);

            $article_info = $article_model->getArticleInfo([ [ 'article_id', '=', $article_id ] ]);
            $this->assign('info', $article_info[ 'data' ]);

            $article_category_model = new ArticleCategory();
            $article_category_list = $article_category_model->getArticleCategoryList([ [ 'site_id', '=', $this->site_id ] ], 'category_id, category_name')[ 'data' ];
            $this->assign('category_list', $article_category_list);
            return $this->fetch('article/edit');
        }
    }

    /**
     * 文章删除
     */
    public function delete()
    {
        if (request()->isAjax()) {
            $article_id = input('article_id', 0);
            $article_model = new ArticleModel();
            return $article_model->deleteArticle([ [ 'article_id', '=', $article_id ] ]);
        }
    }

    /**
     * 修改排序
     */
    public function modifySort()
    {
        if (request()->isAjax()) {
            $sort = input('sort', 0);
            $article_id = input('article_id', 0);
            $article_model = new ArticleModel();
            return $article_model->modifyArticleSort($sort, $article_id, $this->site_id);
        }
    }

    //todo 移至草稿箱  或  草稿箱发布

    /**
     * 文章选择
     * @return array|mixed
     */
    public function articleSelect()
    {
        $article_model = new ArticleModel();
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $article_ids = input('article_ids', '');
            $condition = [
                [ 'pn.site_id', '=', $this->site_id ],
                [ 'pn.status', '=', 1 ]
            ];
            if (!empty($search_text)) {
                $condition[] = [ 'pn.article_title', 'like', '%' . $search_text . '%' ];
            }
            if (!empty($article_ids)) {
                $condition[] = [ 'pn.article_id', 'in', $article_ids ];
            }
            $list = $article_model->getArticlePageList($condition, $page, $page_size, 'pn.create_time desc', 'pn.article_id,pn.article_title,pn.article_abstract,pn.cover_img,pn.read_num,pn.create_time,png.category_name');
            return $list;
        } else {
            //已经选择的商品sku数据
            $select_id = input('select_id', '');
            $this->assign('select_id', $select_id);
            $article_list = $article_model->getArticleList([
                [ 'site_id', '=', $this->site_id ],
                [ 'article_id', 'in', $select_id ]
            ], 'article_id,article_title,article_abstract,cover_img,read_num')[ 'data' ];
            $this->assign('article_list', $article_list);
            return $this->fetch("article/article_select");
        }
    }

}