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

use app\model\member\Member;
use app\model\order\OrderPay;
use think\facade\Cache;
use app\model\BaseModel;
use app\model\order\Config as OrderConfig;
use think\facade\Log;

/**
 * 系统配置类
 */
class Pay extends BaseModel
{

    public $refund_pay_type = array (
        'offline_refund_pay' => '线下退款',
        'online_refund_pay' => '原路退款'
    );

    /********************************************************************支付**********************************************************/

    /**
     * 支付
     * @param $pay_type 支付方式
     * @param $out_trade_no 交易号
     * @param $app_type 请求来源类型
     * @param $member_id 会员id
     * @param null $return_url 同步回调地址
     * @param int $is_balance 是否使用余额
     * @param int $scene 场景值
     * @return mixed|void
     */
    public function pay($pay_type, $out_trade_no, $app_type, $member_id, $return_url = null, $is_balance = 0, $scene = 0)
    {
        $data = $this->getPayInfo($out_trade_no)[ 'data' ];
        if (empty($data)) return $this->error('', '未获取到支付信息');
        if ($data['pay_status'] == 2) return $this->success(['pay_success' => 1]);

        $notify_url = addon_url('pay/pay/notify');
        if (empty($return_url)) {
            $return_url = addon_url('pay/pay/payreturn');
        }

        // 是否使用余额
        if ($is_balance) {
            $data[ 'member_id' ] = $member_id;
            $use_res = $this->useBalance($data)['data'];
            if (isset($use_res['pay_success'])) return $this->success($use_res);
            $data = $this->getPayInfo($out_trade_no)[ 'data' ];
        }

        $data[ 'app_type' ] = $app_type;
        $data[ 'notify_url' ] = $notify_url;
        $data[ 'return_url' ] = $return_url;
        $data[ 'pay_type' ] = $pay_type;
        $data[ 'member_id' ] = $member_id;
        $data[ 'scene' ] = $scene;
        $res = event('Pay', $data, true);
        if (empty($res)) return $this->error('', '没有可用的支付方式');
        return $res;
    }

    /**
     * 创建支付流水号
     */
    public function createOutTradeNo($member_id = 0)
    {
        $cache = Cache::get('pay_out_trade_no' .$member_id. time());
        if (empty($cache)) {
            Cache::set('pay_out_trade_no' .$member_id. time(), 1000);
            $cache = Cache::get('pay_out_trade_no' .$member_id. time());
        } else {
            $cache = $cache + 1;
            Cache::set('pay_out_trade_no' .$member_id. time(), $cache);
        }
        $no = time() . rand(1000, 9999).$member_id . $cache;
        return $no;
    }

    /**
     * 添加支付信息
     * @param int $site_id //站点id  默认平台配置为0
     * @param string $out_trade_no 交易流水号
     * @param string $app_type 支付端口类型
     * @param int $pay_type 支付方式，默认为空
     * @param string $pay_body 支付主体
     * @param string $pay_detail 支付细节
     * @param double $pay_money 支付金额
     * @param string $pay_no 支付账号
     * @param string $notify_url 要求的异步回调网址，实际支付后会进行执行或者回调，可以是事件或者域名
     * @param string $return_url 同步回调网址，知己支付后会进行同步回调
     */
    public function addPay($site_id, $out_trade_no, $pay_type, $pay_body, $pay_detail, $pay_money, $pay_no, $notify_url, $return_url)
    {
        $data = array (
            'site_id' => $site_id,
            'out_trade_no' => $out_trade_no,
            'pay_body' => $pay_body,
            'pay_detail' => $pay_detail,
            'pay_money' => $pay_money,
            'pay_no' => $pay_no,
            'event' => $notify_url,
            'return_url' => $return_url,
            'pay_status' => 0,
            'create_time' => time(),
        );
        $result = model('pay')->add($data);
        if ($pay_money == 0) {
            $this->onlinePay($out_trade_no, $pay_type, '', '');
        }
        return $result;
    }

    /**
     * 在线支付
     * @param $out_trade_no
     * @param $pay_type
     * @param $trade_no
     * @param $pay_addon
     * @param array $log_data
     * @return array|mixed|void
     * @throws \Exception
     */
    public function onlinePay($out_trade_no, $pay_type, $trade_no, $pay_addon, $log_data = [])
    {
        $pay_info_result = $this->getPayInfo($out_trade_no);

        $pay_info = $pay_info_result[ 'data' ];
        $pay_type = empty($pay_type) ? 'ONLINE_PAY' : $pay_type;
        //支付状态 (未支付  未取消)
        if ($pay_info[ 'pay_status' ] == 0) {
            $data = array (
                'trade_no' => $trade_no,
                'pay_type' => $pay_type,
                'pay_addon' => $pay_addon,
                'pay_time' => time(),
                'pay_status' => 2
            );
            $res = model('pay')->update($data, [ [ 'out_trade_no', '=', $out_trade_no ] ]);
            //成功则直接给应用异步回调地址发送
            $return_data = array (
                'out_trade_no' => $out_trade_no,
                'trade_no' => $trade_no,
                'pay_type' => $pay_type,
                'log_data' => $log_data
            );

            //根据事件成功后执行
            if (strpos($pay_info[ 'event' ], 'http://') !== 0 || strpos($pay_info[ 'event' ], 'https://') !== 0) {
                $result = event($pay_info[ 'event' ], $return_data, true);
                if(!empty($result)) {
                    $code = $result['code'] ?? 0;
                    if ($code < 0) {
                        model('pay')->update(['pay_status' => 0, 'pay_time' => 0], [['out_trade_no', '=', $out_trade_no]]);
                        return $result;
                    }
                }
            } else {
                http($pay_info[ 'event' ], 1);
            }

            return $this->success($return_data);

        } else {
            return $this->success('', '当前单据已支付');
        }

    }

    /**
     * 删除并关闭支付
     * @param $out_trade_no
     */
    public function deletePay($out_trade_no)
    {
        $pay_info_result = $this->getPayInfo($out_trade_no);
        $pay_info = $pay_info_result[ 'data' ];
        if (!empty($pay_info)) {
            //支付状态 (未支付  未取消)
            if ($pay_info[ 'pay_status' ] == 0) {
                if (!empty($pay_info[ 'pay_type' ])) {
                    $close_result = event('payClose', $pay_info, true);
                    if ($close_result[ 'code' ] < 0) {
                        return $close_result;
                    }
                }
                // 冻结中的余额返还
                if ($pay_info['member_id']) {
                    if ($pay_info['balance']) model('member')->setDec([ ['site_id', '=', $pay_info['site_id']], ['member_id', '=', $pay_info['member_id']] ], 'balance_lock', $pay_info['balance']);
                    if ($pay_info['balance_money'])  model('member')->setDec([ ['site_id', '=', $pay_info['site_id']], ['member_id', '=', $pay_info['member_id']] ], 'balance_money_lock', $pay_info['balance_money']);
                }
                $res = model('pay')->delete([ [ 'out_trade_no', '=', $out_trade_no ] ]);
                if ($res === false) {
                    return $this->error('', 'UNKNOW_ERROR');
                } else {
                    return $this->success($res);
                }
            } else {
                return $this->error([], '当前支付已完成');
            }
        } else {
            return $this->success();
        }
    }

    /**
     * 重新生成新的pay支付记录
     * @param $out_trade_no  原交易流水号
     * @param $pay_money
     */
    public function rewritePay($out_trade_no, $pay_money)
    {
        $pay_info_result = $this->getPayInfo($out_trade_no);
        $pay_info = $pay_info_result[ 'data' ];
        //支付状态 (未支付  未取消)
        if ($pay_info[ 'pay_status' ] == 0) {
            if (!empty($pay_info[ 'pay_type' ])) {
                $close_result = event('payClose', $pay_info, true);
                if ($close_result[ 'code' ] < 0) {
                    return $close_result;
                }
            }
            $new_out_trade_no = $this->createOutTradeNo();
            $data = array (
                'out_trade_no' => $new_out_trade_no,
                'pay_money' => $pay_money
            );
            $res = model('pay')->update($data, [ [ 'out_trade_no', '=', $out_trade_no ] ]);
            if ($res === false) {
                return $this->error('', 'UNKNOW_ERROR');
            } else {

                return $this->success($new_out_trade_no);
            }
        } else {
            return $this->error([], '当前支付已完成');
        }
    }

    /**
     * 支付绑定商户信息
     * @param $condition
     * @param $data
     */
    public function bindMchPay($out_trade_no, $json_data)
    {
        $data = array (
            'mch_info' => json_encode($json_data, JSON_UNESCAPED_UNICODE)
        );
        $condition = array (
            [ 'out_trade_no', '=', $out_trade_no ]
        );
        $res = model('pay')->update($data, $condition);
        return $res;
    }

    /**
     * 获取支付方式
     * @param unknown $params 'pay_scene' => ['wap', 'wechat', 'app', 'pc', 'wechat_applet']
     */
    public function getPayType($params = [])
    {
        $res = event('PayType', $params);
        return $this->success($res);
    }

    /**
     * 获取支付信息详情
     * @param string $out_trade_no
     */
    public function getPayInfo($out_trade_no)
    {
        $get_pay_list = model('pay')->setIsCache(0)->getInfo([ [ 'out_trade_no', '=', $out_trade_no ] ]);
        return $this->success($get_pay_list);
    }

    /**
     * 支付记录
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     * @return multitype:string mixed
     */
    public function getPayPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = model('pay')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 支付统计
     * @param unknown $site_id
     */
    public function getPayStatistics($condition)
    {
        $statistics_array = array (
            'count' => model('pay')->getCount($condition),
            'sum_money' => model('pay')->getSum($condition, 'pay_money')
        );
        return $statistics_array;
    }

    /****************************************************************退款**************************************************************/

    /**
     * 创建退款流水号
     */
    public function createRefundNo()
    {
        $cache = Cache::get('pay_refund_out_trade_no' . time());
        if (empty($cache)) {
            Cache::set('niutk' . time(), 1000);
            $cache = Cache::get('pay_refund_out_trade_no' . time());
        } else {
            $cache = $cache + 1;
            Cache::set('pay_refund_out_trade_no' . time(), $cache);
        }
        $no = date('Ymdhis', time()) . rand(1000, 9999) . $cache;
        return $no;
    }

    /**
     * 原路退款
     * @param $refund_no
     * @param $refund_fee
     * @param $out_trade_no
     * @param $refund_desc
     * @param $total_fee  实际支付金额
     * @param $refund_type  退款方式 1 原路退款  2 线下支付
     */
    public function refund($refund_no, $refund_fee, $out_trade_no, $refund_desc, $total_fee, $site_id, $refund_type, $order_goods_id = 0, $is_video_number = 0)
    {
        //是否是原理退款方式退款
        if ($refund_type == 1) {
            $pay_info_result = $this->getPayInfo($out_trade_no);
            $pay_info = $pay_info_result[ 'data' ];
            if (empty($pay_info))
                return $this->error('', '付款记录不存在!');

            $order_goods_info = model('order_goods')->getInfo([['order_goods_id', '=', $order_goods_id]]);
            $data = array (
                'refund_no' => $refund_no,
                'refund_fee' => $refund_fee,
                'refund_desc' => $refund_desc,
                'pay_info' => $pay_info,
                'total_fee' => $total_fee,
                'site_id' => $site_id,
                'order_goods_id' => $order_goods_id,
                'is_video_number' => $is_video_number,
                'out_aftersale_id' => $order_goods_info['out_aftersale_id']
            );
            //退款金额许大于0
            if ($refund_fee > 0 && !in_array($pay_info[ 'pay_type' ], [ 'OFFLINE_PAY', 'BALANCE', 'ONLINE_PAY' ])) {
                $result = event('PayRefund', $data, true);
                if (empty($result))
                    return $this->error('', '找不到可用的退款方式!');

                if ($result[ 'code' ] < 0)
                    return $result;
            }


        }
        $refund_data = array (
            'refund_no' => $refund_no,
            'refund_fee' => $refund_fee,
            'total_money' => $total_fee,
            'refund_type' => $refund_type,
            'site_id' => $site_id,
            'out_trade_no' => $out_trade_no,
        );
        $this->addRefundPay($refund_data);
        return $this->success();

    }

    /**
     * 添加退款记录
     * @param unknown $data
     */
    public function addRefundPay($data)
    {
        $data[ 'create_time' ] = time();
        $data[ 'refund_detail' ] = '支付交易号:' . $data[ 'out_trade_no' ] . '，退款金额:' . $data[ 'refund_fee' ] . '元';
        $res = model('pay_refund')->add($data);
        if ($res == false) {
            return $this->error($res);
        }
        return $this->success($res);
    }

    /**
     * 获取支付状态
     * @param unknown $out_trade_no
     * @return multitype:
     */
    public function getPayStatus($out_trade_no)
    {
        $info = model('pay')->getInfo([ [ 'out_trade_no', '=', $out_trade_no ] ], 'pay_status');
        if (empty($info)) return $this->error();
        return $this->success($info);
    }

    /**
     * 查询转账方式
     * @return array
     */
    public function getTransferType($site_id)
    {
        $data = array (
            'bank' => '银行卡'
        );
        $temp_array = event('TransferType', [ 'site_id' => $site_id ]);

        if (!empty($temp_array)) {
            foreach ($temp_array as $k => $v) {
                $data[ $v[ 'type' ] ] = $v[ 'type_name' ];
            }
        }
        return $data;
    }

    /**
     * 使用余额
     * @param $pay_info
     */
    public function useBalance($pay_info){
        // 查询是否可使用余额
        $balance_config = (new OrderConfig())->getBalanceConfig($pay_info['site_id'])['data']['value'];
        if (!$balance_config['balance_show']) return $this->success();

        // 查询会员当前可用余额
        $balance_data = (new Member())->getMemberUsableBalance($pay_info['site_id'], $pay_info['member_id']);
        if ($balance_data['code'] != 0) return $balance_data;
        $balance_data = $balance_data['data'];
        if ($balance_data['usable_balance'] <= 0) return $this->success();

        $data = [
            'pay_money' => $pay_info['pay_money'],
            'member_id' => $pay_info['member_id']
        ];

        if ($balance_data['balance'] > 0) {
            $data['balance'] = bccomp($balance_data['balance'], $data['pay_money'], 2) == 1 ? $data['pay_money'] : $balance_data['balance'];
            $data['pay_money'] -= $data['balance'];
        }
        if ($balance_data['balance_money'] > 0 && $data['pay_money'] > 0) {
            $data['balance_money'] = bccomp($balance_data['balance_money'], $data['pay_money'], 2) == 1 ? $data['pay_money'] : $balance_data['balance_money'];
            $data['pay_money'] -= $data['balance_money'];
        }

        model('pay')->startTrans();
        try {
            model('pay')->update($data, [ ['out_trade_no', '=', $pay_info['out_trade_no'] ] ]);
            if (isset($data['balance']) && $data['balance'] > 0) model('member')->setInc([ ['site_id', '=', $pay_info['site_id']], ['member_id', '=', $pay_info['member_id']] ], 'balance_lock', $data['balance']);
            if (isset($data['balance_money']) && $data['balance_money'] > 0) model('member')->setInc([ ['site_id', '=', $pay_info['site_id']], ['member_id', '=', $pay_info['member_id']] ], 'balance_money_lock', $data['balance_money']);

            if ($data['pay_money'] == 0) {
                $res = $this->onlinePay($pay_info['out_trade_no'], 'BALANCE', '', '');
                if ($res['code'] != 0) {
                    model('pay')->rollback();
                    return $res;
                }
                model('pay')->commit();
                return $this->success(['pay_success' => 1]);
            }
            model('pay')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('pay')->rollback();
            return $this->error('', '支付冻结余额错误');
        }
    }

    /**
     * 重置支付
     * @param $params
     */
    public function resetPay($params){
        $out_trade_no = $params['out_trade_no'];
        $pay_info = $this->getPayInfo($out_trade_no)['data'] ?? [];
        if(empty($pay_info))
            return $this->error();
        $result = event('PayReset', $pay_info, true);//各种插件自己实现
        if(empty($result)){
            switch($pay_info['event']){
                case 'OrderPayNotify':
                    $order_pay_model = new OrderPay();
                    $result = $order_pay_model->reset0rderTradeNo([ 'out_trade_no' => $out_trade_no ]);
                    break;
            }
        }
        if($result['code'] < 0)
            return $result;

        return $result;
    }

    /**
     * 支付编辑(切勿调用,收银业务专用)
     * @param $data
     * @param $condition
     */
    public function edit($data, $condition){
        model('pay')->update($data, $condition);
        return $this->success();
    }

}