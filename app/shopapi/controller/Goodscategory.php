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

namespace app\shopapi\controller;

use app\model\goods\GoodsCategory as GoodscategoryModel;

/**
 * 商品分类管理 控制器
 */
class Goodscategory extends BaseApi
{

    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
    }

    /**
     * 获取商品分类
     */
    public function getCategoryByParent()
    {
        $pid = isset($this->params[ 'pid' ]) ? $this->params[ 'pid' ] : 0;// 上级id
        $level = isset($this->params[ 'level' ]) ? $this->params[ 'level' ] : 0;// 层级
        $goods_category_model = new GoodscategoryModel();
        if (!empty($level)) {
            $condition[] = [ 'level', '=', $level ];
        }
        if (!empty($pid)) {
            $condition[] = [ 'pid', '=', $pid ];
        }
        $list = $goods_category_list = $goods_category_model->getCategoryByParent($condition, 'category_id,category_name,pid,level,category_id_1,category_id_2,category_id_3');
        return $this->response($list);
    }

}