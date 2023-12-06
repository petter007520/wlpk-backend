<?php
namespace addon\wechatpay\event;

use addon\wechatpay\model\Config;
use addon\wechatpay\model\V3;
use app\model\member\Withdraw;

class TransferResult
{
    public function handle(array $params)
    {
        $withdraw_info = (new Withdraw())->getMemberWithdrawInfo([ ['id','=', $params['relate_id']] ], 'id,site_id,applet_type,withdraw_no')['data'];
        if (!empty($withdraw_info)) {
            $pay_config = (new Config())->getPayConfig($withdraw_info['site_id'])['data']['value'];
            if (!empty($pay_config)) {
                (new V3($pay_config))->getTransferResult($withdraw_info);
            }
        }
    }
}