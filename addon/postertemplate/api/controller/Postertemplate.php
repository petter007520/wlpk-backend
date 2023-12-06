<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\postertemplate\api\controller;

use addon\postertemplate\model\PosterTemplate as PosterTemplateModel;
use app\api\controller\BaseApi;

/**
 * 海报模板 控制器
 */
class Postertemplate extends BaseApi
{

    /**
     * 海报模板列表
     * @return mixed
     */
    public function lists()
    {
        $page_index = input('page', 1);
        $page_size = input('page_size', PAGE_LIST_ROWS);
        $poster_template_model = new PosterTemplateModel();
        $res = $poster_template_model->getPosterTemplatePageList([ [ 'site_id', '=', $this->site_id ] ], $page_index, $page_size);
        return $res;
    }


}