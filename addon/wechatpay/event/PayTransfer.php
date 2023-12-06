<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechatpay\event;

use addon\wechatpay\model\Pay;

class PayTransfer
{
    public function handle(array $params)
    {
        if ($params['transfer_type'] == 'wechatpay') {
            $is_weapp = $params['is_weapp'] ?? 0;
            $pay      = new Pay($is_weapp, $params['site_id']);
            $res      = $pay->transfer($params);
            return $res;
        }
    }
}