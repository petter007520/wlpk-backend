<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\order;

use app\model\member\Member;
use app\model\message\Sms;
use app\model\BaseModel;
use addon\wechat\model\Message as WechatMessage;
use addon\weapp\model\Message as WeappMessage;
use app\model\shop\ShopAcceptMessage;

/**
 * 订单消息操作
 *
 * @author Administrator
 *
 */
class OrderMessage extends BaseModel
{

    /************************************************ 会员消息 start ************************************************************/

    /**
     * 订单催付通知
     * @param $data
     */
    public function messageOrderUrgePayment($data)
    {
        trace('进入订单催付消息发送');
        $order_info = model("order")->getInfo([["order_id", "=", $data['order_id']]], "full_address,site_id,create_time,address,order_no,mobile,member_id,order_type,create_time,order_name,order_money");

        //计算订单自动关闭时间
        $config_model = new Config();
        $order_config_result = $config_model->getOrderEventTimeConfig($order_info['site_id']);
        $order_config = $order_config_result[ "data" ];
        $execute_time = $order_info['create_time'] + $order_config[ "value" ][ "auto_close" ] * 60; //自动关闭时间

        //会员信息
        $member_model = new Member();
        $member_info = $member_model->getMemberInfo([["member_id", "=", $order_info["member_id"]]])['data'];

        // 发送短信
        if (!empty($member_info) && !empty($member_info['mobile'])) {
            $var_parse = array(
                'goodsname' => replaceSpecialChar(str_sub($order_info['order_name'])),//商品名称
                'expiretime' => date('d', $execute_time).'日'.date('H', $execute_time).'时'.date('i', $execute_time).'分'
            );
            $data["sms_account"] = $member_info["mobile"];//手机号
            $data["var_parse"] = $var_parse;
            $sms_model = new Sms();
            $res = $sms_model->sendMessage($data);
            trace($res, '订单催付短信发送结果');
        }

        // 公众号模板消息
        if (!empty($member_info) && !empty($member_info["wx_openid"])) {
            $wechat_model = new WechatMessage();
            $data["openid"] = $member_info["wx_openid"];
            $data["template_data"] = [
                'keyword1' => $order_info['order_no'],
                'keyword2' => $order_info['order_name'],
                'keyword3' => '待支付',
                'keyword4' => '请在'.time_to_date($execute_time).'前完成支付'
            ];
            $data["page"] = $this->handleUrl($order_info['order_type'], $data['order_id']);
            $res = $wechat_model->sendMessage($data);
            trace($res, '订单催付公众号模板消息发送结果');
        }

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
            $weapp_model = new WeappMessage();
            $data["openid"] = $member_info["weapp_openid"];
            $data["template_data"] = [
                'character_string1' => [
                    'value' => $order_info['order_no']
                ],
                'thing2' => [
                    'value' => $order_info['order_name']
                ],
                'character_string3' => [
                    'value' => $order_info['order_money'],
                ]
            ];
            $data["page"] = $this->handleUrl($order_info['order_type'], $data['order_id']);
            $res = $weapp_model->sendMessage($data);
            trace($res, '订单催付小程序订阅消息发送结果');
        }
    }

    /**
     * 消息发送——支付成功
     * @param $params
     * @return array|mixed|void
     */
    public function messagePaySuccess($params)
    {
        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $params["member_id"]]]);
        $member_info = $member_info_result["data"];

        // 发送短信
        if (!empty($member_info) && !empty($member_info['mobile'])) {
            $var_parse = [
                "orderno" => $params['order_no'],
                "username" => replaceSpecialChar($member_info["nickname"]),
                "ordermoney" => $params["order_money"],
            ];
            $params["sms_account"] = $member_info["mobile"] ?? '';//手机号
            $params["var_parse"] = $var_parse;
            $sms_model = new Sms();
            $res = $sms_model->sendMessage($params);
            trace($res, '订单支付短信发送结果');
        }


        //绑定微信公众号才发送
        if (!empty($member_info) && !empty($member_info["wx_openid"])) {
            $wechat_model = new WechatMessage();
            $data = $params;
            $data["openid"] = $member_info["wx_openid"];
            $data["template_data"] = [
                'keyword1' => time_to_date($params['create_time']),
                'keyword2' => $params['order_no'],
                'keyword3' => str_sub($params['order_name']),
                'keyword4' => $params['order_money'],
            ];
            $data["page"] = $this->handleUrl($params['order_type'], $params["order_id"]);
            $res = $wechat_model->sendMessage($data);
            trace($res, '订单支付公众号发送结果');
        }

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
            $weapp_model = new WeappMessage();
            $data = $params;
            $data["openid"] = $member_info["weapp_openid"];
            $data["template_data"] = [
                'character_string1' => [
                    'value' => $params['order_no']
                ],
                'time2' => [
                    'value' => time_to_date($params['create_time'])
                ],
                'thing4' => [
                    'value' => str_sub($params['order_name'])
                ],
                'amount3' => [
                    'value' => $params['order_money']
                ],
            ];
            $data["page"] = $this->handleUrl($params['order_type'], $params["order_id"]);
            $res = $weapp_model->sendMessage($data);
            trace($res, '订单支付小程序订阅号发送结果');
        }
    }

    /**
     * 订单关闭提醒
     * @param $data
     */
    public function messageOrderClose($data)
    {
        //发送短信
        $sms_model = new Sms();
        $order_id = $data["order_id"];
        $order_info = model("order")->getInfo([["order_id", "=", $order_id]], "order_type,order_no,mobile,member_id,order_name,create_time,order_money,close_time");

        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $order_info["member_id"]]]);
        $member_info = $member_info_result["data"];

        $var_parse = array(
            "orderno" => $order_info["order_no"],//商品名称
        );
        $data["sms_account"] = $member_info["mobile"];//手机号
        $data["var_parse"] = $var_parse;
        $sms_model->sendMessage($data);

        if (!empty($member_info) && !empty($member_info["wx_openid"])) {
            $wechat_model = new WechatMessage();
            $data["openid"] = $member_info["wx_openid"];
            $data["template_data"] = [
                'keyword1' => str_sub($order_info['order_name']),
                'keyword2' => $order_info['order_no'],
                'keyword3' => time_to_date($order_info['create_time']),
                'keyword4' => $order_info['order_money'],
                'keyword5' => time_to_date($order_info['close_time'])
            ];
            $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
            $wechat_model->sendMessage($data);
        }

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
            $weapp_model = new WeappMessage();
            $data["openid"] = $member_info["weapp_openid"];
            $data["template_data"] = [
                'thing4' => [
                    'value' => str_sub($order_info['order_name'])
                ],
                'character_string1' => [
                    'value' => $order_info['order_no']
                ],
                'time3' => [
                    'value' => time_to_date($order_info['create_time'])
                ],
                'amount6' => [
                    'value' => $order_info['order_money']
                ],
                'time5' => [
                    'value' => time_to_date($order_info['close_time'])
                ],
            ];
            $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
            $weapp_model->sendMessage($data);
        }

    }

    /**
     * 买家订单完成通知商家
     * @param $data
     */
    public function messageBuyerOrderComplete($data)
    {
        //发送短信
        // $sms_model = new Sms();
        $order_id = $data["order_id"];
        $order_info = model("order")->getInfo([["order_id", "=", $order_id]], "order_type,order_no,mobile,member_id,order_name,create_time,finish_time");

        $shop_accept_message_model = new ShopAcceptMessage();
        $result = $shop_accept_message_model->getShopAcceptMessageList();
        $list = $result['data'];

        //发送模板消息
        if(!empty($list)){
            foreach ($list as $v) {
                if (!empty($v["wx_openid"])) {
                    $wechat_model = new WechatMessage();
                    $data["openid"] = $v["wx_openid"];
                    $data["template_data"] = [
                        'keyword1' => $order_info['order_no'],
                        'keyword2' => str_sub($order_info['order_name']),
                    ];
                    $data["page"] = $this->handleMobileShopUrl($order_info['order_type'], $order_id);
                    $wechat_model->sendMessage($data);
                }
            }
        }
    }

    /**
     * 订单完成提醒
     * @param $data
     */
    public function messageOrderComplete($data)
    {
        //发送短信
        $sms_model = new Sms();
        $order_id = $data["order_id"];
        $order_info = model("order")->getInfo([["order_id", "=", $order_id]], "order_type,order_no,mobile,member_id,order_name,create_time,finish_time");

        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $order_info["member_id"]]]);
        $member_info = $member_info_result["data"];

        $var_parse = array(
            "orderno" => $order_info["order_no"],//商品名称
        );
        $data["sms_account"] = $member_info["mobile"];//手机号
        $data["var_parse"] = $var_parse;
        $sms_model->sendMessage($data);

        //发送模板消息
        $wechat_model = new WechatMessage();
        $data["openid"] = $member_info["wx_openid"];
        $data["template_data"] = [
            'keyword1' => $order_info['order_no'],
            'keyword2' => str_sub($order_info['order_name']),
            'keyword3' => time_to_date($order_info['create_time']),
        ];
        $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
        $wechat_model->sendMessage($data);

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
            $weapp_model = new WeappMessage();
            $data["openid"] = $member_info["weapp_openid"];
            $data["template_data"] = [

                'character_string1' => [
                    'value' => $order_info['order_no']
                ],
                'thing2' => [
                    'value' => str_sub($order_info['order_name'])
                ],
                'time4' => [
                    'value' => time_to_date($order_info['finish_time'])
                ]
            ];
            $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
            $weapp_model->sendMessage($data);
        }

    }

    /**
     * 订单发货提醒
     * @param $data
     */
    public function messageOrderDelivery($data)
    {
        //发送短信
        $sms_model = new Sms();
        $order_id = $data["order_id"];
        $order_info = model("order")->getInfo([["order_id", "=", $order_id]], "order_type,order_no,mobile,member_id,order_name,goods_num,order_money,delivery_time");

        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $order_info["member_id"]]]);
        $member_info = $member_info_result["data"];

        $var_parse = array(
            "orderno" => $order_info["order_no"],//商品名称
        );
        $data["sms_account"] = $member_info["mobile"];//手机号
        $data["var_parse"] = $var_parse;
        $sms_model->sendMessage($data);

        //发送模板消息
        $wechat_model = new WechatMessage();
        $data["openid"] = $member_info["wx_openid"];
        $data["template_data"] = [
            'keyword1' => $order_info['order_no'],
            'keyword2' => str_sub($order_info['order_name']),
            'keyword3' => $order_info['goods_num'],
            'keyword4' => $order_info['order_money'],
            'keyword5' => time_to_date($order_info['delivery_time']),
        ];
        $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
        $wechat_model->sendMessage($data);


        //发送订阅消息
        if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
            $weapp_model = new WeappMessage();
            $data["openid"] = $member_info["weapp_openid"];
            $data["template_data"] = [
                'character_string2' => [
                    'value' => $order_info['order_no']
                ],
                'thing1' => [
                    'value' => str_sub($order_info['order_name'])
                ],
                'amount7' => [
                    'value' => $order_info['order_money']
                ],
                'date3' => [
                    'value' => time_to_date($order_info['delivery_time'])
                ]
            ];
            $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
            $weapp_model->sendMessage($data);
        }

    }

    /**
     * 订单收货提醒
     * @param $data
     */
    public function messageOrderTakeDelivery($data)
    {
        //发送短信
        $sms_model = new Sms();
        $order_id = $data["order_id"];
        $order_info = model("order")->getInfo([["order_id", "=", $order_id]], "order_type,order_no,mobile,member_id,full_address,address,name,order_name,sign_time");

        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $order_info["member_id"]]]);
        $member_info = $member_info_result["data"];

        $var_parse = array(
            "orderno" => $order_info["order_no"],//商品名称
        );
        $data["sms_account"] = $member_info["mobile"];//手机号
        $data["var_parse"] = $var_parse;
        $sms_model->sendMessage($data);

        //发送模板消息
        $wechat_model = new WechatMessage();
        $data["openid"] = $member_info["wx_openid"];
        $data["template_data"] = [
            'keyword1' => $order_info['full_address'] . $order_info['address'],
            'keyword2' => $order_info["name"],
            'keyword3' => $order_info['order_no'],
            'keyword4' => $order_info['order_name'],
            'keyword5' => time_to_date($order_info['sign_time']),
        ];
        $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
        $wechat_model->sendMessage($data);

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
            $weapp_model = new WeappMessage();
            $data["openid"] = $member_info["weapp_openid"];
            $data["template_data"] = [
                'character_string1' => [
                    'value' => $order_info['order_no']
                ],
                'thing2' => [
                    'value' => str_sub($order_info['order_name'])
                ],
                'time7' => [
                    'value' => time_to_date($order_info['sign_time'])
                ],
                'thing9' => [
                    'value' => str_sub($order_info['name'])
                ]
            ];
            $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
            $weapp_model->sendMessage($data);
        }
    }

    /**
     * 订单退款同意提醒
     * @param $data
     */
    public function messageOrderRefundAgree($data)
    {
        //发送短信
        $sms_model = new Sms();
        $order_id = $data["order_id"];
        $order_info = model("order")->getInfo([["order_id", "=", $order_id]], "order_type,order_no,mobile,member_id");

        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $order_info["member_id"]]]);
        $member_info = $member_info_result["data"];

        $order_goods_info = model("order_goods")->getInfo([["order_goods_id", "=", $data["order_goods_id"]]], "refund_apply_money,refund_time,refund_action_time");
        $var_parse = array(
            "orderno" => $order_info["order_no"],//商品名称
        );
        $data["sms_account"] = $member_info["mobile"];//手机号
        $data["var_parse"] = $var_parse;
        $sms_model->sendMessage($data);

        //发送模板消息
        $wechat_model = new WechatMessage();
        $data["openid"] = $member_info["wx_openid"];
        $data["template_data"] = [
            'keyword1' => $order_info['order_no'],
            'keyword2' => $order_goods_info["refund_apply_money"],
            'keyword3' => time_to_date(time()),
        ];
        $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
        $wechat_model->sendMessage($data);

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
            $weapp_model = new WeappMessage();
            $data["openid"] = $member_info["weapp_openid"];
            $data["template_data"] = [
                'character_string3' => [
                    'value' => $order_info['order_no']
                ],
                'amount1' => [
                    'value' => $order_goods_info["refund_apply_money"]
                ],
                'phrase7' => [
                    'value' => '成功'
                ]
            ];
            $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
            $weapp_model->sendMessage($data);
        }
    }

    /**
     * 订单退款拒绝提醒
     * @param $data
     */
    public function messageOrderRefundRefuse($data)
    {
        //发送短信
        $sms_model = new Sms();
        $order_id = $data["order_id"];
        $order_info = model("order")->getInfo([["order_id", "=", $order_id]], "order_type,order_no,mobile,member_id");
        $order_goods_info = model("order_goods")->getInfo([["order_goods_id", "=", $data["order_goods_id"]]], "refund_apply_money,refund_time,refund_action_time");

        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $order_info["member_id"]]]);
        $member_info = $member_info_result["data"];

        $var_parse = array(
            "orderno" => $order_info["order_no"],//商品名称
        );
        $data["sms_account"] = $member_info["mobile"];//手机号
        $data["var_parse"] = $var_parse;
        $sms_model->sendMessage($data);

        //发送模板消息
        $wechat_model = new WechatMessage();
        $data["openid"] = $member_info["wx_openid"];
        $data["template_data"] = [
            'keyword1' => $order_info['order_no'],
            'keyword2' => $order_goods_info["refund_apply_money"],
            'keyword3' => time_to_date($order_goods_info['refund_action_time']),
        ];
        $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
        $wechat_model->sendMessage($data);
        //发送订阅消息
        if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
            $weapp_model = new WeappMessage();
            $data["openid"] = $member_info["weapp_openid"];
            $data["template_data"] = [
                'character_string4' => [
                    'value' => $order_info['order_no']
                ],
                'amount3' => [
                    'value' => $order_goods_info["refund_apply_money"]
                ]
            ];
            $data["page"] = $this->handleUrl($order_info['order_type'], $order_id);
            $weapp_model->sendMessage($data);
        }

    }

    /**
     * 核销码过期提醒
     * @param $data
     */
    public function messageVerifyCodeExpire($data){
        // 发送短信
        $sms_model = new Sms();
        // 商品表
        $goods_virtual_info = model('goods_virtual')->getInfo([ ["order_id", "=", $data["relate_id"] ] ]);
        // 总核销次数
        $total_verify_num =model('goods_virtual')->getCount([ ["order_id", "=", $data["relate_id"] ] ]);
        // 已核销次数
        $verify_num = model('goods_virtual')->getCount([ ["order_id", "=", $data["relate_id"] ], ['is_veirfy', '=', 1] ]);
        // 剩余次数
        $residue = $total_verify_num - $verify_num;
        // 用户信息
        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $goods_virtual_info['member_id']]]);
        $member_info = $member_info_result["data"];

        $order_info = model('order')->getInfo([["order_id", "=", $goods_virtual_info["order_id"]]],'mobile,order_no,order_name,order_type,pay_time');
        trace($residue);
        if($residue > 0){

            // 公众号模板消息
            //绑定微信公众号才发送
            if (!empty($member_info) && !empty($member_info["wx_openid"])) {
                $wechat_model = new WechatMessage();
                $data["openid"] = $member_info["wx_openid"];
                $data["template_data"] = [
                    'keyword1' => $order_info['order_no'],
                    'keyword2' => $order_info['order_name'],
                    'keyword3' => '已过期'
                ];
                $wechat_model->sendMessage($data);
            }

            //发送订阅消息
            if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
                $weapp_model = new WeappMessage();
                $data["openid"] = $member_info["weapp_openid"];
                $data["template_data"] = [
                    'character_string1' => [
                        'value' => $order_info['order_no'],
                    ],
                    'time2' => [
                        'value' => time_to_date($order_info['pay_time']) 
                    ],
                    'time3' => [
                        'value' => time_to_date(time())
                    ],
                    'thing5' => [
                        'value' => '您的订单核销码已过期',
                    ],
                ];
                $weapp_model->sendMessage($data);
            }

            // 短信通知
            if (!empty($member_info) && !empty($member_info['mobile'])) {
                //"desc" => '您购买的'.$goods_virtual_info['sku_name'].'将在'.date('Y-m-d H:i:s',$goods_virtual_info['expire_time']).'到期',//商品名称,

                $var_parse = [
                    "sitename" => replaceSpecialChar($data['site_info']['site_name']),
                    "sku_name" => $goods_virtual_info['sku_name']
                ];
                $data["sms_account"] = $member_info["mobile"];//手机号
                $data["var_parse"] = $var_parse;
                $sms_model->sendMessage($data);
            }
        }
        
    }

    /**
     * 核销商品临期提醒
     */
    public function messageVerifyOrderOutTime($data)
    {
        // 商品表
        $goods_virtual_info = model('goods_virtual')->getInfo([ ["order_id", "=", $data["order_id"] ] ]);
        // 总核销次数
        $total_verify_num =model('goods_virtual')->getCount([ ["order_id", "=", $data["order_id"] ] ]);
        // 已核销次数
        $verify_num = model('goods_virtual')->getCount([ ["order_id", "=", $data["order_id"] ], ['is_veirfy', '=', 1] ]);
        // 剩余次数
        $residue = $total_verify_num - $verify_num;
        // 用户信息
        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $goods_virtual_info['member_id']]]);
        $member_info = $member_info_result["data"];
        // 手机号
        $order_info = model('order')->getInfo([["order_id", "=", $data["order_id"]]],'order_type,mobile,order_no,order_name');
        if($residue > 0){
            // 公众号模板消息
            //绑定微信公众号才发送
            if (!empty($member_info) && !empty($member_info["wx_openid"])) {
                $wechat_model = new WechatMessage();
                $data["openid"] = $member_info["wx_openid"];
                $data["template_data"] = [
                    'keyword1' => $order_info['order_no'],
                    'keyword2' => $order_info['order_name'],
                    'keyword3' => '未核销'
                ];
                $wechat_model->sendMessage($data);
            }

            //发送订阅消息
            if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
                // 核销码临近到期时间（小时）
                $config_model = new Config();
                $verify_config = $config_model->getOrderVerifyConfig($data['site_id'], 'shop')['data']['value'];
                $order_verify_out_time = $verify_config['order_verify_time_out'] ?? 24;
                $time_strtime = $order_verify_out_time * 3600;
                $weapp_model = new WeappMessage();
                $data["openid"] = $member_info["weapp_openid"];

                $data["template_data"] = [
                    'thing1' => [
                        'value' => $order_info['order_name']
                    ],
                    'date2' => [
                        'value' => time_to_date(time() + $time_strtime),
                    ],
                    'thing3' => [
                        'value' => '请在到期前核销，以免影响您的使用！',
                    ]
                ];
                $weapp_model->sendMessage($data);
            }

            // 短信消息
             $sms = new Sms();

             $var_parse = [
                 'username' => $member_info['username'],//用户名称
                 'sku_name' => $goods_virtual_info['sku_name'],//商品名称
                 'expire_time' => date('Y-m-d H:i:s',$goods_virtual_info['expire_time'])//到期时间
             ];
             $data["sms_account"] = $order_info['phone'];//手机号
             $data["var_parse"] = $var_parse;
             $sms->sendMessage($data);

        }
    }

    /**
     * 订单核销通知
     * @param $data
     */
    public function messageOrderVerify($data)
    {
        //发送短信
        $sms_model = new Sms();
        $order_id = $data["order_id"];
        $order_info = model("order")->getInfo([["order_id", "=", $order_id]], "order_type,order_no,mobile,member_id,order_name,goods_num,sign_time,delivery_store_name");

        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $order_info["member_id"]]]);
        $member_info = $member_info_result["data"];

        $var_parse = array(
            "orderno" => $order_info["order_no"],//订单编号
        );
        $data["sms_account"] = $member_info["mobile"];//手机号
        $data["var_parse"] = $var_parse;
        $sms_model->sendMessage($data);
        
        // 公众号模板消息
        //绑定微信公众号才发送
        if (!empty($member_info) && !empty($member_info["wx_openid"])) {
            $wechat_model = new WechatMessage();
            $data["openid"] = $member_info["wx_openid"];
            $data["template_data"] = [
                'keyword1' => $order_info['order_name'],//用户名称
                'keyword2' => 1,
                'keyword3' => time_to_date(time()),
            ];
            $wechat_model->sendMessage($data);
        }

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info["weapp_openid"])) {
            $weapp_model = new WeappMessage();
            $data["openid"] = $member_info["weapp_openid"];
            $data["template_data"] = [
                'phrase1' => [
                    'value' => '已核销'
                ],
                'time2' => [
                    'value' => time_to_date(time()),
                ],
                'character_string3' => [
                    'value' => $order_info['order_no'],
                ]
            ];
            $data["page"] = $this->handleUrl($order_info['order_type'], $data['order_id']);
            $weapp_model->sendMessage($data);
        }
    }


    /************************************************ 会员消息 end ************************************************************/


    /**
     * 买家发起退款，卖家通知
     * @param $data
     */
    public function messageOrderRefundApply($data)
    {
        //发送短信
        $sms_model = new Sms();
        $order_goods_id = $data["order_goods_id"];
        $order_goods_info = model('order_goods')->getInfo(['order_goods_id' => $order_goods_id], '*');

        $order_info = model("order")->getInfo([["order_id", "=", $order_goods_info['order_id']]], "order_no,mobile,member_id,site_id,name,order_type");
        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $order_info["member_id"]]]);
        $member_info = $member_info_result["data"];


        $var_parse = array(
            "username" => replaceSpecialChar($member_info["nickname"]),//会员名
            "orderno" => $order_info["order_no"],//订单编号
            "goodsname" => mb_substr(replaceSpecialChar($order_goods_info["sku_name"]), 0, 25, 'UTF8'),//商品名称
            "refundno" => $order_goods_info["refund_no"],//退款编号
            "refundmoney" => $order_goods_info["refund_apply_money"],//退款申请金额
            "refundreason" => replaceSpecialChar($order_goods_info["refund_reason"]),//退款原因
        );
        $data["var_parse"] = $var_parse;

//        $site_id    = $data['site_id'];
//        $shop_info  = model("shop")->getInfo([["site_id", "=", $site_id]], "mobile,email");
//        $message_data["sms_account"] = $shop_info["mobile"];//手机号
        $shop_accept_message_model = new ShopAcceptMessage();
        $result = $shop_accept_message_model->getShopAcceptMessageList();
        $list = $result['data'];
        if (!empty($list)) {
            foreach ($list as $v) {
                $message_data = $data;
                $message_data["sms_account"] = $v["mobile"];//手机号
                $sms_model->sendMessage($message_data);

                if($v['wx_openid'] != ''){
                	$wechat_model = new WechatMessage();
                	$data["openid"] = $v['wx_openid'];
                	$data["template_data"] = [
                		'keyword1' => $order_goods_info['order_no'],
                		'keyword2' => time_to_date($order_goods_info['refund_action_time']),
                		'keyword3' => $order_goods_info['refund_apply_money'],
                	];
                    $data["page"] = 'pages/order/refund/detail?order_goods_id=' . $order_goods_id;
                	$wechat_model->sendMessage($data);
                }
            }
        }

    }


    /**
     * 买家已退款，卖家通知
     * @param $data
     */
    public function messageOrderRefundDelivery($data)
    {
        //发送短信
        $sms_model = new Sms();
        $order_id = $data['order_goods_info']["order_id"];
        $order_info = model("order")->getInfo([["order_id", "=", $order_id]], "*");

        $var_parse = array(
            "orderno" => $order_info["order_no"],//商品名称
        );

//        $site_id    = $data['site_id'];
//        $shop_info  = model("shop")->getInfo([["site_id", "=", $site_id]], "mobile,email");
//        $message_data["sms_account"] = $shop_info["mobile"];//手机号
        $data["var_parse"] = $var_parse;

        $shop_accept_message_model = new ShopAcceptMessage();
        $result = $shop_accept_message_model->getShopAcceptMessageList();
        $list = $result['data'];
        if (!empty($list)) {
            foreach ($list as $v) {
                $message_data = $data;
                $message_data["sms_account"] = $v["mobile"];//手机号
                $sms_model->sendMessage($message_data);
                
                if($v['wx_openid'] != ''){
                	$wechat_model = new WechatMessage();
                	$data["openid"] = $v['wx_openid'];
                	$data["template_data"] = [
                        'keyword1' => $data['order_goods_info']['order_no'],
                        'keyword2' => mb_substr($data['order_goods_info']['sku_name'],0,7,'utf-8'),
                        'keyword3' => $data['order_goods_info']['num'],
                        'keyword4' => $data['order_goods_info']['refund_real_money'],
                	];
                    $data["page"] = 'pages/order/refund/detail?order_goods_id=' . $data['order_goods_info']['order_goods_id'];
                	$wechat_model->sendMessage($data);
                }
            }
        }
    }

    /**
     * 买家支付成功，卖家通知
     * @param $data
     */
    public function messageBuyerPaySuccess($data)
    {
        //发送短信
        $sms_model = new Sms();

        $var_parse = array(
            "orderno" => $data["order_no"],//订单编号
            "ordermoney" => $data["order_money"],//退款申请金额
        );
//        $site_id    = $data['site_id'];
//        $shop_info  = model("shop")->getInfo([["site_id", "=", $site_id]], "mobile,email");
//        $message_data["sms_account"] = $shop_info["mobile"];//手机号
        $data["var_parse"] = $var_parse;

        $shop_accept_message_model = new ShopAcceptMessage();
        $result = $shop_accept_message_model->getShopAcceptMessageList();
        $list = $result['data'];
        if (!empty($list)) {
            foreach ($list as $v) {
                $message_data = $data;
                $message_data["sms_account"] = $v["mobile"];//手机号
                $sms_model->sendMessage($message_data);
                
                if($v['wx_openid'] != ''){
                	$wechat_model = new WechatMessage();
                	$data["openid"] = $v['wx_openid'];
                	$data["template_data"] = [
                		'keyword1' => time_to_date($data['pay_time']),
                		'keyword2' => $data['order_no'],
                		'keyword3' => str_sub($data['order_name']),
                		'keyword4' => $data['order_money'],
                	];
                    $data["page"] = $this->handleMobileShopUrl($data['order_type'], $data['order_id']);
                	$wechat_model->sendMessage($data);
                }
            }
        }
    }

    /**
     * 处理订单链接
     * @param $order_type
     * @param $order_id
     * @return string
     */
    public function handleUrl($order_type, $order_id)
    {
        switch ($order_type) {
            case 2:
                return 'pages/order/detail_pickup?order_id=' . $order_id;
                break;
            case 3:
                return 'pages/order/detail_local_delivery?order_id=' . $order_id;
                break;
            case 4:
                return 'pages_tool/order/detail_virtual?order_id=' . $order_id;
                break;
            default:
                return 'pages/order/detail?order_id=' . $order_id;
                break;
        }
    }

    /**
     * 处理商家端订单页面路径
     * @param $order_type
     * @param $order_id
     * @return string
     */
    public function handleMobileShopUrl($order_type, $order_id)
    {
        switch ($order_type) {
            case 2:
                return 'pages/order/detail/store?order_id=' . $order_id . '&template=store';
                break;
            case 3:
                return 'pages/order/detail/local?order_id=' . $order_id . '&template=local';
                break;
            case 4:
                return 'pages/order/detail/virtual?order_id=' . $order_id . '&template=virtual';
                break;
            default:
                return 'pages/order/detail/basis?order_id=' . $order_id . '&template=basis';
                break;
        }
    }
}