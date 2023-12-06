<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechatpay\model;

use app\model\BaseModel;
use app\model\system\Pay as PayCommon;
use app\model\upload\Upload;
use EasyWeChat\Factory;
use think\facade\Log;

/**
 * 微信支付v3支付
 * 版本 1.0.4
 */
class Yi extends BaseModel
{
    /**
     * 微信支付配置
     */
    private $api;
    private $apikey;
    private $mchId;
    private $callback_url;

    /**
     * 支付实例
     * @var
     */
    private $app;

    public function __construct($config)
    {
        $this->api = $config['api'] ? $config['api'].'/api/v3/payment' : '';
        $this->apikey = $config['app_secrect'];
        $this->mchId = $config['mch_id'];
        $this->callback_url = $config['callback_url'];
    }

    /**
     * 支付
     * @param  array  $param
     * @return array
     */
    public function pay(array $param){
        $signBody = [
            "partnerid" => $this->mchId,//商户ID
            "amount" => $param["pay_money"],//*字符串类型 “20.00”  请保留2位小数
            "notifyurl" => $this->callback_url,//交易金额（元）
            "orderid" => $param["out_trade_no"],//订单时间（例如：2021-05-06 10:20:09）
            "remark" => '星新新能源',// 有填值就行，签名用
            "paytype" => '1',//1:微信支付 2：支付宝支付 3：银行卡支付 4：USDT-TRC20 5：USDT-ERC20
            "returnurl" => 'test',//页面跳转返回地址
        ];

        $sign = $this->sign($signBody);
        $signBody = array_merge($signBody,['sign'=>$sign]);
        $result = $this->curl($this->api,http_build_query($signBody));
        Log::write('支付返回：'.json_encode($result).'--签名参数'.json_encode($signBody));
        if ($result["status"] != 200 ) return $this->error([], $result["msg"]);

        $return = [
            "type" => "url",
            "url" => $result['url']
        ];
        return $this->success($return);
    }

    /**
     * 生成签名
     * @param $signBody
     * @return string
     */
    private function sign($signBody): string
    {
        //签名
        ksort($signBody);//ASCII码排序
        $signStr = "";
        foreach ($signBody as $key => $val) {
            $signStr .= $key."=".$val."&";
        }
        $signParams = $signStr.'key='.$this->apikey;
        return strtolower(md5($signParams));
    }

    public function curl($url, $data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result,true);
    }

    /**
     * 异步回调
     */
    public function payNotify()
    {
        $response = $this->app->handlePaidNotify(function ($message, $fail) {
            $pay_common = new PayCommon();
            if ($message['return_code'] === 'SUCCESS') {
                // return_code 表示通信状态，不代表支付状态
                if ($message['result_code'] === 'SUCCESS') {
                    // 判断支付金额是否等于支付单据的金额
                    $pay_info = $pay_common->getPayInfo($message['out_trade_no'])['data'];
                    if (empty($pay_info)) return $fail('通信失败，请稍后再通知我');
                    if ($message['total_fee'] != round($pay_info['pay_money'] * 100)) return;
                    // 用户是否支付成功
                    $pay_common->onlinePay($message['out_trade_no'], "wechatpay", $message["transaction_id"], "wechatpay");
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            return true;
        });
        $response->send();
        return $response;
    }

    /**
     * 关闭支付单据
     * @param  array  $param
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException     */
    public function payClose(array $param)
    {
        $result = $this->app->order->close($param["out_trade_no"]);

        if ($result["return_code"] == 'FAIL') {
            return $this->error([], $result["return_msg"]);
        }
        if ($result["result_code"] == 'FAIL') {
            if ($result['err_code'] == 'ORDERPAID') return $this->error(['is_paid' => 1, 'pay_type' => 'wechatpay'], $result['err_code_des']);
            return $this->error([], $result['err_code_des']);
        }

        return $this->success();
    }

    /**
     * 申请退款
     * @param  array  $param
     */
    public function refund(array $param)
    {
        $pay_info = $param["pay_info"];
        $refund_no = $param["refund_no"];
        $total_fee = round($pay_info["pay_money"] * 100);
        $refund_fee = round($param["refund_fee"] * 100);

        $result = $this->app->refund->byOutTradeNumber($pay_info["out_trade_no"], $refund_no, $total_fee, $refund_fee, []);
        //调用失败
        if ($result["return_code"] == 'FAIL') return $this->error([], $result["return_msg"]);
        if ($result["result_code"] == 'FAIL') return $this->error([], $result["err_code_des"]);

        return $this->success();
    }

    /**
     * 转账
     * @param  array  $param
     * @return array\
     */
    public function transfer(array $param)
    {
        $data = [
            'partner_trade_no' => $param['out_trade_no'], // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
            'openid' => $param['account_number'],
            'check_name' => 'FORCE_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
            're_user_name' => $param['real_name'], // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
            'amount' => $param['amount'] * 100, // 转账金额
            'desc' => $param['desc']
        ];
        $res = $this->app->transfer->toBalance($data);
        if ($res['return_code'] == 'SUCCESS') {
            if ($res['result_code'] == 'SUCCESS') {
                return $this->success([
                    'out_trade_no' => $res['partner_trade_no'], // 商户交易号
                    'payment_no' => $res['payment_no'], // 微信付款单号
                    'payment_time' => $res['payment_time'] // 付款成功时间
                ]);
            } else {
                return $this->error([], $res['err_code_des']);
            }
        } else {
            return $this->error([], $res['return_msg']);
        }
    }

    /**
     * 付款码支付
     * @param  array  $param
     * @return array\
     */
    public function micropay(array $param){
        $data = [
            'body' => str_sub($param["pay_body"], 15),
            'out_trade_no' => $param["out_trade_no"],
            'total_fee' => $param["pay_money"] * 100,
            'auth_code' => $param["auth_code"]
        ];
        $result = $this->app->base->pay($data);
        if ($result[ 'return_code' ] == 'FAIL') {
            return $this->error([], $result[ 'return_msg' ]);
        }
        if ($result[ 'result_code' ] == 'FAIL') {
            return $this->error([], $result[ 'err_code_des' ]);
        }
        return $this->success($result);
    }
}