<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\article;

use app\model\BaseModel;

/**
 * 文章
 */
class Article extends BaseModel
{

    /**
     * 添加文章
     * @param $data
     * @return array
     */
    public function addArticle($data)
    {
        $data[ 'create_time' ] = time();

        model('article')->startTrans();
        try {
            //添加文章
            model('article')->add($data);
            //更新分组文章数等信息
            if ($data[ 'status' ] == 1) {
                model('article_category')->setInc([ [ 'category_id', '=', $data[ 'category_id' ] ] ], 'article_num');
            } else {
                model('article_category')->setInc([ [ 'category_id', '=', $data[ 'category_id' ] ] ], 'article_num');
            }
            model('article')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('article')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 编辑文章
     * @param $condition
     * @param $data
     * @return array
     */
    public function editArticle($data)
    {
        $data[ 'update_time' ] = time();

        $info = model('article')->getInfo([ [ 'site_id', '=', $data[ 'site_id' ] ], [ 'article_id', '=', $data[ 'article_id' ] ] ], 'category_id');
        $category_id = $info[ 'category_id' ] ?? 0;
        model('article')->startTrans();
        try {
            //添加文章
            model('article')->update($data, [ [ 'site_id', '=', $data[ 'site_id' ] ], [ 'article_id', '=', $data[ 'article_id' ] ] ]);

            //减掉原文章类的文章数量
            if ($category_id != $data[ 'category_id' ]) {
                //新分类增加
                model('article_category')->setInc([ [ 'category_id', '=', $data[ 'category_id' ] ] ], 'article_num');
                //旧分类减少
                model('article_category')->setDec([ [ 'category_id', '=', $category_id ] ], 'article_num');
            }

            model('article')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('article')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 删除文章
     * @param $article_id
     * @param $site_id
     * @return array|\multitype
     */
    public function deleteArticle($condition)
    {
        //文章数
        $article_info = model('article')->getInfo($condition, 'category_id,status');
        if (empty($article_info)) {
            return $this->success('', '数据不合法');
        } else {

            model('article')->startTrans();
            try {
                //删除文章
                model('article')->delete($condition);

                //更新分组文章数等信息
                if ($article_info[ 'status' ] == 1) {
                    model('article_category')->setDec([ [ 'category_id', '=', $article_info[ 'category_id' ] ] ], 'article_num');
                } else {
                    model('article_category')->setDec([ [ 'category_id', '=', $article_info[ 'category_id' ] ] ], 'article_num');
                }
                model('article')->commit();
                return $this->success();
            } catch (\Exception $e) {
                model('article')->rollback();
                return $this->error('', $e->getMessage());
            }
        }
    }

    /**
     * 修改排序
     * @param $sort
     * @param $article_id
     * @param $site_id
     * @return array
     */
    public function modifyArticleSort($sort, $article_id, $site_id)
    {
        $res = model('article')->update([ 'sort' => $sort ], [ [ 'article_id', '=', $article_id ], [ 'site_id', '=', $site_id ] ]);
        return $this->success($res);
    }

    /**
     * 获取文章信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getArticleInfo($condition = [], $field = '*')
    {
        $info = model("article")->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取文章信息
     * @param array $condition
     * @param string $field
     * @param int $type
     * @return array
     */
    public function getArticleDetailInfo($condition = [], $field = '*', $type = 1)
    {
        $info = model('article')->getInfo($condition, $field);
        //添加浏览记录
        if ($type == 2) {
            model('article')->setInc($condition, 'read_num', 1);
        }
        return $this->success($info);
    }

    /**
     * 获取文章列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getArticleList($condition = [], $field = '*', $order = '', $limit = null, $alias = '', $join = [])
    {
        $list = model('article')->getList($condition, $field, $order, $alias, $join, '', $limit);
        return $this->success($list);
    }

    /**
     * 获取文章分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getArticlePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'pn.sort asc', $field = 'pn.*,png.category_name')
    {
        $alias = 'pn';
        $join = [
            [
                'article_category png',
                'png.category_id = pn.category_id',
                'left'
            ]
        ];
        $list = model('article')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

}