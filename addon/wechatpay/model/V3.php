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

use app\exception\ApiException;
use app\model\BaseModel;
use app\model\system\Cron;
use app\model\system\Pay as PayCommon;
use app\model\upload\Upload;
use think\exception\HttpException;
use think\facade\Cache;
use think\facade\Log;
use WeChatPay\Builder;
use WeChatPay\ClientDecoratorInterface;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Formatter;
use WeChatPay\Util\PemUtil;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;

/**
 * 微信支付v3支付
 * 版本 1.0.4
 */
class V3 extends BaseModel
{
    /**
     * 应用实例
     * @var \WeChatPay\BuilderChainable
     */
    private $app;

    /**
     * @var 平台证书实例
     */
    private $plateform_certificate_instance;

    /**
     * @var 平台证书序列号
     */
    private $plateform_certificate_serial;

    /**
     * 微信支付配置
     */
    private $config;

    public function __construct($config)
    {
        $this->config = $config;

        $merchant_certificate_instance = PemUtil::loadCertificate(realpath($config['apiclient_cert']));
        // 证书序列号
        $merchant_certificate_serial = PemUtil::parseCertificateSerialNo($merchant_certificate_instance);

        // 检测平台证书是否存在
        if (empty($config['plateform_cert'])) {
            $create_res = $this->certificates();
            if ($create_res['code'] != 0) throw new ApiException(-1, "微信支付配置错误");
            // 保存平台证书
            $this->config['plateform_cert'] = $create_res['data']['cert_path'];
            (new Config())->setPayConfig($this->config, $this->config['site_id']);
        }

        // 加载平台证书
        $this->plateform_certificate_instance = PemUtil::loadCertificate(realpath($this->config['plateform_cert']));
        // 平台证书序列号
        $this->plateform_certificate_serial = PemUtil::parseCertificateSerialNo($this->plateform_certificate_instance);

        $this->app = Builder::factory([
            // 商户号
            'mchid' => $config['mch_id'],
            // 商户证书序列号
            'serial' => $merchant_certificate_serial,
            // 商户API私钥
            'privateKey' => PemUtil::loadPrivateKey(realpath($config['apiclient_key'])),
            'certs' => [
                $this->plateform_certificate_serial => $this->plateform_certificate_instance
            ]
        ]);
    }

    /**
     * 生成平台证书
     */
    private function certificates(){
        try {
            $merchant_certificate_instance = PemUtil::loadCertificate(realpath($this->config['apiclient_cert']));
            // 证书序列号
            $merchant_certificate_serial = PemUtil::parseCertificateSerialNo($merchant_certificate_instance);

            $certs = ['any' => null];
            $app = Builder::factory([
                // 商户号
                'mchid' => $this->config['mch_id'],
                // 商户证书序列号
                'serial' => $merchant_certificate_serial,
                // 商户API私钥
                'privateKey' => PemUtil::loadPrivateKey(realpath($this->config['apiclient_key'])),
                'certs' => &$certs
            ]);

            $stack = $app->getDriver()->select(ClientDecoratorInterface::JSON_BASED)->getConfig('handler');
            $stack->after('verifier', Middleware::mapResponse(self::certsInjector($this->config['v3_pay_signkey'], $certs)), 'injector');
            $stack->before('verifier', Middleware::mapResponse(self::certsRecorder((string) dirname($this->config['apiclient_key']), $certs)), 'recorder');

            $param = [
                'url' => '/v3/certificates',
                'timestamp' => (string)Formatter::timestamp(),
                'noncestr' => uniqid()
            ];
            $resp = $app->chain("v3/certificates")
                ->get([
                    'headers' => [
                        'Authorization' =>  Rsa::sign(
                            Formatter::joinedByLineFeed(...array_values($param)),
                            Rsa::from('file://' . realpath($this->config['apiclient_key']))
                        )
                    ]
                ]);
            $result = json_decode($resp->getBody()->getContents(), true);
            $file_path = dirname($this->config['apiclient_key']) . '/plateform_cert.pem';
            return $this->success(['cert_path' => $file_path]);
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $result = json_decode($e->getResponse()->getBody()->getContents(), true);
                return $this->error($result, $result['message']);
            } else {
                return $this->error([], $e->getMessage());
            }
        }
    }

    private static function certsInjector(string $apiv3Key, array &$certs): callable {
        return static function(ResponseInterface $response) use ($apiv3Key, &$certs): ResponseInterface {
            $body = (string) $response->getBody();
            /** @var object{data:array<object{encrypt_certificate:object{serial_no:string,nonce:string,associated_data:string}}>} $json */
            $json = \json_decode($body);
            $data = \is_object($json) && isset($json->data) && \is_array($json->data) ? $json->data : [];
            \array_map(static function($row) use ($apiv3Key, &$certs) {
                $cert = $row->encrypt_certificate;
                $certs[$row->serial_no] = AesGcm::decrypt($cert->ciphertext, $apiv3Key, $cert->nonce, $cert->associated_data);
            }, $data);

            return $response;
        };
    }

    private static function certsRecorder(string $outputDir, array &$certs): callable {
        return static function(ResponseInterface $response) use ($outputDir, &$certs): ResponseInterface {
            $body = (string) $response->getBody();
            /** @var object{data:array<object{effective_time:string,expire_time:string:serial_no:string}>} $json */
            $json = \json_decode($body);
            $data = \is_object($json) && isset($json->data) && \is_array($json->data) ? $json->data : [];
            \array_walk($data, static function($row, $index, $certs) use ($outputDir) {
                $serialNo = $row->serial_no;
                $outpath = $outputDir . \DIRECTORY_SEPARATOR . 'plateform_cert.pem';
                \file_put_contents($outpath, $certs[$serialNo]);
            }, $certs);

            return $response;
        };
    }

    /**
     * 支付
     * @param  array  $param
     * @return array
     */
    public function pay(array $param)
    {
        $self = $this;
        $site_id = $param['site_id'];
        $data = [
            'json' => [
                'appid' => $this->config['appid'],
                'mchid' => $this->config['mch_id'],
                'description' => str_sub($param["pay_body"], 15),
                'out_trade_no' => $param["out_trade_no"],
                'notify_url' => $param["notify_url"],
                'amount' => [
                    'total' => round($param["pay_money"] * 100)
                ]
            ]
        ];
        switch ($param["trade_type"]) {
            case 'JSAPI':
                $data['json']['payer'] = [ 'openid' => $param['openid'] ];
                $data['trade_type'] = 'jsapi';
                $data['callback'] = function ($result) use ($self) {
                    return success(0, '',  [
                        "type" => "jsapi",
                        "data" => $self->jsskdConfig($result['prepay_id'])
                    ]);
                };
                break;
            case 'APPLET':
                $data['json']['payer'] = [ 'openid' => $param['openid'] ];
                $data['trade_type'] = 'jsapi';
                $data['callback'] = function ($result) use ($self) {
                    return success(0, '',  [
                        "type" => "jsapi",
                        "data" => $self->jsskdConfig($result['prepay_id'])
                    ]);
                };
                break;
            case 'NATIVE':
                $data['trade_type'] = 'native';
                $data['callback'] = function ($result) use ($site_id) {
                    $upload_model = new Upload($site_id);
                    $qrcode_result = $upload_model->qrcode($result['code_url']);
                    return success(0, '',  [
                        "type" => "qrcode",
                        "qrcode" => $qrcode_result['data'] ?? ''
                    ]);
                };
                break;
            case 'MWEB':
                $data['trade_type'] = 'h5';
                $data['json']['scene_info'] = [
                    'payer_client_ip' => request()->ip(),
                    'h5_info' => [
                        'type' => 'Wap'
                    ]
                ];
                $data['callback'] = function ($result){
                    return success(0, '',  [
                        "type" => "url",
                        "url" => $result['h5_url']
                    ]);
                };
                break;
            case 'APP':
                $data['trade_type'] = 'app';
                $data['callback'] = function ($result) use ($self) {
                    return success(0, '',  [
                        "type" => "app",
                        "data" => $self->appConfig($result['prepay_id'])
                    ]);
                };
                break;
        }

        $result = $this->unify($data);
        if ($result['code'] != 0) return $result;

        $result = $data['callback']($result['data']);
        return $result;
    }

    /**
     * 统一下单接口
     * @param  array  $param
     */
    public function unify(array $param){
        try {
            $resp = $this->app->chain('v3/pay/transactions/'.$param['trade_type'])->post([
                'json' => $param['json']
            ]);
            $result = json_decode($resp->getBody()->getContents(), true);
            return $this->success($result);
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $result = json_decode($e->getResponse()->getBody()->getContents(), true);
                return $this->error($result, $result['message']);
            } else {
                return $this->error([], $e->getMessage());
            }
        }
    }

    /**
     * 生成支付配置
     * @param  string  $prepay_id
     */
    private function jsskdConfig(string $prepay_id)
    {
        $param = [
            'appId' => $this->config['appid'],
            'timeStamp' => (string)Formatter::timestamp(),
            'nonceStr' => uniqid(),
            'package' => "prepay_id=$prepay_id"
        ];
        $param += ['paySign' => Rsa::sign(
            Formatter::joinedByLineFeed(...array_values($param)),
            Rsa::from('file://' . realpath($this->config['apiclient_key']))
        ), 'signType' => 'RSA'];
        return $param;
    }

    /**
     * 生成支付配置
     * @param  string  $prepay_id
     * @return array
     */
    private function appConfig(string $prepay_id)
    {
        $param = [
            'appid' => $this->config['appid'],
            'timestamp' => (string)Formatter::timestamp(),
            'noncestr' => uniqid(),
            'prepayid' => $prepay_id
        ];
        $param += [
            'sign' => Rsa::sign(
                Formatter::joinedByLineFeed(...array_values($param)),
                Rsa::from('file://' . realpath($this->config['apiclient_key']))
            ),
            'package' => 'Sign=WXPay',
            'partnerid' => $this->config['mch_id']
        ];
        return $param;
    }

    /**
     * 异步回调
     */
    public function payNotify(){
        $inWechatpaySignature = request()->header('Wechatpay-Signature'); // 从请求头中拿到 签名
        $inWechatpayTimestamp = request()->header('Wechatpay-Timestamp'); // 从请求头中拿到 时间戳
        $inWechatpaySerial = request()->header('Wechatpay-Serial');  // 从请求头中拿到 时间戳
        $inWechatpayNonce = request()->header('Wechatpay-Nonce'); // 从请求头中拿到 时间戳
        $inBody = file_get_contents('php://input');

        $platformPublicKeyInstance = Rsa::from('file://' . realpath($this->config['plateform_cert']), Rsa::KEY_TYPE_PUBLIC);

        $timeOffsetStatus = 300 >= abs(Formatter::timestamp() - (int)$inWechatpayTimestamp);
        $verifiedStatus = Rsa::verify(
        // 构造验签名串
            Formatter::joinedByLineFeed($inWechatpayTimestamp, $inWechatpayNonce, file_get_contents('php://input')),
            $inWechatpaySignature,
            $platformPublicKeyInstance
        );
        if ($timeOffsetStatus && $verifiedStatus) {
            // 转换通知的JSON文本消息为PHP Array数组
            $inBodyArray = (array)json_decode($inBody, true);
            // 使用PHP7的数据解构语法，从Array中解构并赋值变量
            ['resource' => [
                'ciphertext'      => $ciphertext,
                'nonce'           => $nonce,
                'associated_data' => $aad
            ]] = $inBodyArray;
            // 加密文本消息解密
            $inBodyResource = AesGcm::decrypt($ciphertext, $this->config['v3_pay_signkey'], $nonce, $aad);
            // 把解密后的文本转换为PHP Array数组
            $message = json_decode($inBodyResource, true);
            Log::write('message'.$inBodyResource);
            // 交易状态为成功
            if (isset($message['trade_state']) && $message['trade_state'] == 'SUCCESS') {
                if (isset($message['out_trade_no'])) {
                    $pay_common = new PayCommon();
                    $pay_info = $pay_common->getPayInfo($message['out_trade_no'])['data'];
                    if (empty($pay_info)) return;
                    if ($message['amount']['total'] != round($pay_info['pay_money'] * 100)) return;
                    // 用户是否支付成功
                    $pay_common->onlinePay($message['out_trade_no'], "wechatpay", $message["transaction_id"], "wechatpay");
                    header('', '', 200);
                }
            } else {
                throw new HttpException(500, '失败', null, [], 'FAIL');
            }
        } else {
           throw new HttpException(500, '失败', null, [], 'FAIL');
        }
    }

    /**
     * 支付单据关闭
     * @param  array  $param
     */
    public function payClose(array $param)
    {
        try {
            $resp = $this->app->chain("v3/pay/transactions/out-trade-no/{$param['out_trade_no']}/close")->post([
                'json' => [
                    'mchid' => $this->config['mch_id']
                ]
            ]);
            $result = json_decode($resp->getBody()->getContents(), true);
            return $this->success($result);
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $result = json_decode($e->getResponse()->getBody()->getContents(), true);
                if (isset($result['code']) && ($result['code'] == 'ORDERPAID' || $result['code'] == 'ORDER_PAID'))
                    return $this->error(['is_paid' => 1, 'pay_type' => 'wechatpay'], $result['code']);
                return $this->error($result, $result['message']);
            } else {
                return $this->error([], $e->getMessage());
            }
        }
    }

    /**
     * 申请退款
     * @param  array  $param
     */
    public function refund(array $param)
    {
        $pay_info = $param["pay_info"];

        try {
            $resp = $this->app->chain("v3/refund/domestic/refunds")->post([
                'json' => [
                    'out_trade_no' => $pay_info['out_trade_no'],
                    'out_refund_no' => $param['refund_no'],
                    'notify_url' => addon_url("pay/pay/refundnotify"),
                    'amount' => [
                        'refund' => round($param['refund_fee'] * 100),
                        'total' => round($pay_info['pay_money'] * 100),
                        'currency' => $param['currency'] ?? 'CNY'
                    ]
                ]
            ]);
            $result = json_decode($resp->getBody()->getContents(), true);
            if (isset($result['status']) && ($result['status'] == 'SUCCESS' || $result['status'] == 'PROCESSING'))
                return $this->success($result);
            else return $this->success($result, '退款异常');
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $result = json_decode($e->getResponse()->getBody()->getContents(), true);
                return $this->error($result, $result['message']);
            } else {
                return $this->error([], $e->getMessage());
            }
        }
    }

    /**
     * 转账
     * @param  array  $param
     */
    public function transfer(array $param)
    {
        $data = [
            'appid' => $this->config['appid'],
            'out_batch_no' => $param['out_trade_no'],
            'batch_name' => '客户提现转账',
            'batch_remark' => '客户提现转账提现交易号' . $param['out_trade_no'],
            'total_amount' => round($param['amount'] * 100),
            'total_num' => 1,
            'transfer_detail_list' => [
                [
                    'out_detail_no' => $param['out_trade_no'],
                    'transfer_amount' => $param['amount'] * 100,
                    'transfer_remark' => $param['desc'],
                    'openid' => $param['account_number'],
                    'user_name' => $this->encryptor($param['real_name'])
                ]
            ]
        ];

        $this->app->chain('v3/transfer/batches')
            ->postAsync([
                'json' => $data,
                'headers' => [
                    'Wechatpay-Serial' => $this->plateform_certificate_serial
                ]
            ])->then(static function($response) use (&$result) {
                $result = json_decode($response->getBody()->getContents(), true);
                $result = success(0, '', $result);
            })->otherwise(static function ($exception) use (&$result) {
                if ($exception instanceof \GuzzleHttp\Exception\RequestException && $exception->hasResponse()) {
                    $result = json_decode($exception->getResponse()->getBody()->getContents(), true);
                    $result = error(-1, $result['message'], $result);
                } else {
                    $result = error(-1, $exception->getMessage());
                }
            })->wait();
        return $result;
    }

    /**
     * 查询转账明细
     * @param  string  $out_batch_no
     * @param  string  $out_detail_no
     * @return array
     */
    public function transferDetail(string $out_batch_no, string $out_detail_no) : array
    {
        try {
            $resp = $this->app->chain("v3/transfer/batches/out-batch-no/{$out_batch_no}/details/out-detail-no/{$out_detail_no}")
                ->get();
            $result = json_decode($resp->getBody()->getContents(), true);
            return $this->success($result);
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $result = json_decode($e->getResponse()->getBody()->getContents(), true);
                return $this->error($result, $result['message']);
            } else {
                return $this->error([], $e->getMessage());
            }
        }
    }

    /**
     * 加密数据
     * @param  string  $str
     * @return string
     */
    public function encryptor(string $str){
        $publicKey = $this->plateform_certificate_instance;
        // 加密方法
        $encryptor = function($msg) use ($publicKey) { return Rsa::encrypt($msg, $publicKey); };
        return $encryptor($str);
    }

    /**
     * 获取转账结果
     * @param $id
     * @return array
     */
    public function getTransferResult($withdraw_info)
    {
        $result = $this->transferDetail($withdraw_info['withdraw_no'], $withdraw_info['withdraw_no']);
        if ($result['code'] != 0 || (isset($result['data']['detail_status']) && $result['data']['detail_status'] == 'PROCESSING')) {
            $error_num = Cache::get('get_transfer_result' . $withdraw_info['withdraw_no']) ?: 0;
            if (!$error_num || $error_num < 5) {
                (new Cron())->addCron(1, 0, "查询转账结果", "TransferResult", (time() + 60), $withdraw_info['id']);
                Cache::set('get_transfer_result' . $withdraw_info['withdraw_no'], ($error_num + 1), 600);
            }
            return $result;
        }

        if ($result['data']['detail_status'] == 'FAIL') {
            $reason = [
                'ACCOUNT_FROZEN' => '账户冻结',
                'REAL_NAME_CHECK_FAIL' => '用户未实名',
                'NAME_NOT_CORRECT' => '用户姓名校验失败',
                'OPENID_INVALID' => 'Openid校验失败',
                'TRANSFER_QUOTA_EXCEED' => '超过用户单笔收款额度',
                'DAY_RECEIVED_QUOTA_EXCEED' => '超过用户单日收款额度',
                'MONTH_RECEIVED_QUOTA_EXCEED' => '超过用户单月收款额度',
                'DAY_RECEIVED_COUNT_EXCEED' => '超过用户单日收款次数',
                'PRODUCT_AUTH_CHECK_FAIL' => '产品权限校验失败',
                'OVERDUE_CLOSE' => '转账关闭',
                'ID_CARD_NOT_CORRECT' => '用户身份证校验失败',
                'ACCOUNT_NOT_EXIST' => '用户账户不存在',
                'TRANSFER_RISK' => '转账存在风险',
                'REALNAME_ACCOUNT_RECEIVED_QUOTA_EXCEED' => '用户账户收款受限，请引导用户在微信支付查看详情',
                'RECEIVE_ACCOUNT_NOT_PERMMIT' => '未配置该用户为转账收款人',
                'PAYER_ACCOUNT_ABNORMAL' => '商户账户付款受限，可前往商户平台-违约记录获取解除功能限制指引',
                'PAYEE_ACCOUNT_ABNORMAL' => '用户账户收款异常，请引导用户完善其在微信支付的身份信息以继续收款',
            ];
            $fail_reason = '';
            if (isset($result['data']['fail_reason'])) $fail_reason = $reason[$result['data']['fail_reason']] ?? '';
            model('member_withdraw')->update(['status' => -2, 'status_name' => '转账失败', 'fail_reason' => $fail_reason], [['id', '=', $withdraw_info['id']]]);
        } else if ($result['data']['detail_status'] != 'SUCCESS') {
            model('member_withdraw')->update(['status' => -2, 'status_name' => '转账失败', 'fail_reason' => '未获取到转账结果'], [['id', '=', $withdraw_info['id']]]);
        }
        Cache::delete('get_transfer_result' . $withdraw_info['withdraw_no']);
        return $this->success();
    }
}