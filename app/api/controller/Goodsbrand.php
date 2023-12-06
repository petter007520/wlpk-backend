<?php

namespace app\api\controller;

use app\model\goods\GoodsBrand as GoodsBrandModel;

/**
 * 商品品牌接口
 * Class Goodsbrand
 * @package app\api\controller
 */
class Goodsbrand extends BaseApi
{

    public function page()
    {
        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $brand_id_arr = isset($this->params[ 'brand_id_arr' ]) ? $this->params[ 'brand_id_arr' ] : '';
        $goods_brand_model = new GoodsBrandModel();
        $condition = [
            [ 'site_id', '=', $this->site_id ]
        ];
        if (!empty($brand_id_arr)) {
            $condition[] = [ 'brand_id', 'in', $brand_id_arr ];
        }
        $list = $goods_brand_model->getBrandPageList($condition, $page, $page_size, 'sort desc,create_time desc', 'brand_id,brand_name,brand_initial,image_url');
        return $this->response($list);
    }

}