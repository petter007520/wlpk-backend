<?php

namespace app\component\controller;


use app\model\web\DiyView as DiyViewModel;

/**
 * 会员中心—>会员信息·组件
 */
class MemberInfo extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {
        $site_id = request()->siteid();
        $diy_view = new DiyViewModel();
        $system_color = $diy_view->getStyleConfig($site_id)[ 'data' ][ 'value' ];
        $this->assign('system_color', $system_color);
        return $this->fetch("member_info/design.html");
    }
}