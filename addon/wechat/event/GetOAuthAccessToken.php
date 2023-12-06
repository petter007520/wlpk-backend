<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\system\Wechat\event;

use addon\system\Wechat\common\model\Wechat;

class GetOAuthAccessToken
{
    /**
     * 获取watch的AccessToken(包含openid)
     * @param array $param
     */
    public function getOAuthAccessToken($param = [])
    {
        $weatch_model = new Wechat();
        $res          = $weatch_model->getOAuthAccessToken(["site_id" => $param["site_id"]]);
        return $res;
    }
}