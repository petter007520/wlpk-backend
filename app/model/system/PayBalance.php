<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\system;

use addon\cashier\model\order\CashierOrderPay;
use app\model\BaseModel;
use app\model\member\MemberAccount;
use think\facade\Log;

/**
 *
 */
class PayBalance extends BaseModel
{
    /**
     * 创建会员码生成记录
     * @param $param
     * @return array
     */
    public function create($param){
        model('pay_balance')->startTrans();
        try {
            $data = [
                'auth_code' => $this->createAuthCode($param['member_id']),
                'site_id' => $param['site_id'],
                'member_id' => $param['member_id'],
                'create_time' => time(),
                'expire_time' => time() + 120,
                'dynamic_code' => rand(1000, 9999)
            ];
            // 生成前将其他的失效
            model('pay_balance')->delete([ ['site_id', '=', $param['site_id'] ],['member_id', '=', $param['member_id']] ]);
            $res = model('pay_balance')->add($data);
            // 提交事务
            model('pay_balance')->commit();

            $barcode = getBarcode($data['auth_code'], '', 3);
            $qrcode = qrcode($data['auth_code'], 'upload/qrcode/pay/', $data['auth_code'], 16);

            $return = [
                'auth_code' => $data['auth_code'],
                'barcode' => 'data:image/png;base64,' . base64_encode(file_get_contents($barcode)),
                'qrcode' => 'data:image/png;base64,' . base64_encode(file_get_contents($qrcode)),
                'expire_time' => $data['expire_time'],
                'dynamic_code' => $data['dynamic_code']
            ];
            // 删除
            @unlink($barcode);
            @unlink($qrcode);
            return  $this->success($return);
        } catch (\Exception $e) {
            model('pay_balance')->rollback();
            Log::write('付款码生成失败:' . $e->getMessage().$e->getFile().$e->getLine());
            return $this->error('', '付款码生成失败');
        }
    }

    /**
     * 付款码支付
     * @param $auth_code
     * @param $out_trade_no
     */
    public function pay($auth_code, $out_trade_no){
        $pay_info = model('pay')->getInfo([ ['out_trade_no', '=', $out_trade_no] ]);

        if (empty($pay_info)) return $this->error('', '未获取到支付单据');
        if ($pay_info['pay_status'] != 0) return $this->error('', '支付单据状态异常');
        $pay_info['pay_json'] = !empty($pay_info['pay_json']) ? json_decode($pay_info['pay_json'], true) : [];

        $member_id = $pay_info['pay_json']['member_id'] ?? 0;
        $site_id = $pay_info['site_id'];

        $code_info = model('pay_balance')->getInfo([ ['auth_code', '=', $auth_code] ]);

        if (empty($code_info)) return $this->error('', '付款码已失效');
        if ($code_info['member_id'] != $member_id) return $this->error('', '不是当前会员的付款码');
        if ($code_info['status'] != 0) return $this->error('', '付款码状态异常');
        if ($code_info['expire_time'] < time()) return $this->error('', '付款码已失效');

        $member_info = model('member')->getInfo([ ['member_id', '=', $member_id ], ['site_id', '=', $site_id ], ['is_delete', '=', 0] ], 'balance,balance_money');
        if (empty($member_info)) return $this->error('', '未查找到会员信息');

        if (bccomp($pay_info['pay_money'], ($member_info['balance'] + $member_info['balance_money']), 2 ) == 1) return $this->error('', '余额不足');

        model('pay_balance')->startTrans();

        try {
            $cashier_order_pay = new CashierOrderPay();
            $cache = $cashier_order_pay->getCache($out_trade_no)['data'];
            $promotion = $cache['promotion'] ?? [];
            $promotion['is_use_balance'] = 1;
            $cache['promotion'] = $promotion;
            $cashier_order_pay->setCache($out_trade_no, $cache);
            $pay_data = [
                'site_id' => $site_id,//站点id
                'out_trade_no' => $out_trade_no,
                'store_id' => 0,
                'online_type' => 'online',
                'pay_type' => 'BALANCE',
                'member_id' => $member_id,
            ];
            $res = (new CashierOrderPay())->doPay($pay_data);
            if ($res['code'] != 0) {
                model('pay_balance')->rollback();
                return $res;
            }
            model('pay_balance')->update(['status' => 1, 'out_trade_no' => $pay_info['out_trade_no'], 'pay_time' => time() ], [ ['auth_code', '=', $auth_code] ]);
            model('pay_balance')->commit();
            return $this->success(['out_trade_no' => $out_trade_no]);
        } catch (\Exception $e) {
            model('pay_balance')->rollback();
            Log::write('付款码支付扣款失败:' . $e->getMessage().$e->getFile().$e->getLine());
            return $this->error('', '付款码支付扣款失败');
        }
    }

    /**
     * 查询信息
     * @param $condition
     * @param  string  $field
     * @return array
     */
    public function getInfo($condition, $field = '*'){
        $data = model('pay_balance')->getInfo($condition, $field);
        return $this->success($data);
    }

    /**
     * 创建会员支付码
     */
    private function createAuthCode($member_id){
        $code = [
            rand(1000, 9999),
            $member_id,
            (int)date('dis')
        ];
        $tmp = implode('', $code);
        for ($i = 0; $i < (18 - strlen($tmp)); $i++) {
            array_push($code, rand(0, 9));
        }
        shuffle($code);
        return implode('', $code);
    }

    /**
     * 检验付款码
     * @param $code
     * @param $member_id
     * @return array
     */
    public function checkPaymentCode($code, $member_id){
        $info = model('pay_balance')->getInfo([ ['auth_code|dynamic_code', '=', $code],['member_id', '=', $member_id] ], 'id,expire_time');
        if (empty($info)) return $this->error('', '无效付款码');
        if ($info['expire_time'] < time()) return $this->error('', '付款码已失效');
        model('pay_balance')->delete([ ['id', '=', $info['id'] ] ]);
        return $this->success();
    }
}