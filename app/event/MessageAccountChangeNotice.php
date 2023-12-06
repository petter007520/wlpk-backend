<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\event;

use app\model\member\MemberAccount;

/**
 *  会员账户变动通知通知
 */
class MessageAccountChangeNotice
{
    /**
     * @param $param
     */
    public function handle($param)
    {
        if ($param[ "keywords" ] == "USER_BALANCE_CHANGE_NOTICE") {
            $model = new MemberAccount();
            return $model->messageAccountChangeNotice($param);
        }
    }

}