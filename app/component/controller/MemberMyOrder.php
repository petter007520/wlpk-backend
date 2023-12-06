<?php

namespace app\component\controller;


/**
 * 会员中心—>我的订单·组件
 */
class MemberMyOrder extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {
        return $this->fetch("member_my_order/design.html");
    }
}