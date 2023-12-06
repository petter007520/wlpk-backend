<?php

namespace app\component\controller;

/**
 * 视频·组件
 */
class Video extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {
        return $this->fetch("video/design.html");
    }
}