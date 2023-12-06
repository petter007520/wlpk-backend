<?php

namespace app\api\controller;

use app\model\article\Article as ArticleModel;

/**
 * 文章接口
 * Class Goodsbrand
 * @package app\api\controller
 */
class Article extends BaseApi
{

    public function info()
    {
        $article_id = isset($this->params[ 'article_id' ]) ? $this->params[ 'article_id' ] : 0;
        if (empty($article_id)) {
            return $this->response($this->error('', '缺少参数article_id'));
        }
        $article_model = new ArticleModel();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'article_id', '=', $article_id ],
            [ 'status', '=', 1 ]
        ];
        $field = 'article_id, article_title, article_abstract, category_id, cover_img, article_content, is_show_release_time, is_show_read_num, is_show_dianzan_num, read_num, dianzan_num, create_time, initial_read_num, initial_dianzan_num';
        $res = $article_model->getArticleDetailInfo($condition, $field, 2);
        if(empty($res['data'])){
            return $this->response($this->error('', '文章不存在'));
        }
        return $this->response($res);
    }

    public function page()
    {
        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $article_id_arr = isset($this->params[ 'article_id_arr' ]) ? $this->params[ 'article_id_arr' ] : '';
        $condition = [
            [ 'pn.site_id', '=', $this->site_id ],
            [ 'pn.status', '=', 1 ],
        ];
        if (!empty($article_id_arr)) {
            $condition[] = [ 'article_id', 'in', $article_id_arr ];
        }
        $order_by = 'pn.sort desc,pn.create_time desc';
        $article_model = new ArticleModel();
        $list = $article_model->getArticlePageList($condition, $page, $page_size, $order_by);
        return $this->response($list);
    }

    public function lists()
    {
        $num = isset($this->params[ 'num' ]) ? $this->params[ 'num' ] : 0;
        $article_id_arr = isset($this->params[ 'article_id_arr' ]) ? $this->params[ 'article_id_arr' ] : '';
        $condition = [
            [ 'pn.site_id', '=', $this->site_id ],
            [ 'pn.status', '=', 1 ],
        ];
        if (!empty($article_id_arr)) {
            $condition[] = [ 'article_id', 'in', $article_id_arr ];
        }
        $order_by = 'pn.sort desc,pn.create_time desc';
        $alias = 'pn';
        $join = [
            [
                'article_category png',
                'png.category_id = pn.category_id',
                'left'
            ]
        ];
        $field = 'pn.*,png.category_name';
        $article_model = new ArticleModel();
        $list = $article_model->getArticleList($condition, $field, $order_by,$num, $alias, $join);
        return $this->response($list);
    }

}