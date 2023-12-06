<?php

namespace app\component\controller;


/**
 * 关注公众号·组件
 */
class FollowOfficialAccount extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {
        return $this->fetch("follow_official_account/design.html");
    }
}