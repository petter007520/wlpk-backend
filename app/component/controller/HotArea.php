<?php

namespace app\component\controller;


/**
 * 热区·组件
 */
class HotArea extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {
        return $this->fetch("hot_area/design.html");
    }
}