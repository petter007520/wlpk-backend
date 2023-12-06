<?php

namespace app\component\controller;

/**
 * 商品品牌·组件
 */
class GoodsBrand extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {
        return $this->fetch("goods_brand/design.html");
    }
}