<?php

namespace app\component\controller;


/**
 * 文章·组件
 */
class Article extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {
        return $this->fetch("article/design.html");
    }
}