<?php

namespace app\component\controller;

/**
 * 快捷导航·组件
 */
class QuickNav extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {
        return $this->fetch("quick_nav/design.html");
    }
}