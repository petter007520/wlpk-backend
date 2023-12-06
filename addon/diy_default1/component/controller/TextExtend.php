<?php

namespace addon\diy_default1\component\controller;

use app\component\controller\BaseDiyView;

/**
 * 文本·组件
 */
class TextExtend extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {
        return $this->fetch("text_extend/design.html");
    }
}