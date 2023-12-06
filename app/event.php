<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */
return [
    'bind' => [

    ],

    'listen' => [

        /**
         * 系统基础事件
         * 完成系统基础化操作执行
         */
        //应用初始化事件
        'AppInit' => [
            'app\event\InitConfig',
            'app\event\InitRoute',
            'app\event\InitAddon',
            'app\event\InitCron',

        ],
        'HttpRun' => [],
        'HttpEnd' => [],
        'LogLevel' => [],
        'LogWrite' => [],

        /**
         * 营销活动查询事件
         * 用于添加到对应营销活动展示
         */
        //营销活动
        'ShowPromotion' => [
            'app\event\ShowPromotion'
        ],
        'ExtensionInformation' => [
            'app\event\ExtensionInformation'
        ],

        /**
         * 店铺相关事件
         * 完成店铺相关功能操作
         */
        'AddSite' => [
            'app\event\AddSiteDiyView',//增加默认自定义数据：主页主页、商品分类、底部导航
            'app\event\AddMemberLevel',//增加默认会员等级
            'app\event\AddRegisterAgreement',//增加默认会员注册协议
            'app\event\AddSiteConfig',//增加默认配置项
            'app\event\AddSiteDelivery',//增加默认配送管理数据
            'app\event\AddSiteExpressCompany',//增加默认物流公司数据
            'app\event\AddMemberClusterCronRefresh',//增加会员群体定时刷新任务
            'app\event\AddSiteAdv', // 增加默认广告
            'app\event\AddStoreDiyView', //增加门店主页装修
        ],
        // 添加店铺演示数据
        'AddYanshiData' => [
            'app\event\AddYanshiData',//增加默认商品相关数据：商品1~3个、商品分类、商品服务
        ],

        //添加店铺账户数据
        'AddShopAccount' => [],

        //添加门店事件
        'AddStore' => [
        ],

        /**
         * 会员相关事件
         *完成会员相关功能操作调用
         */
        //添加会员账户数据
        'AddMemberAccount' => [
            'app\event\UpdateMemberLevel',//会员账户变化检测会员等级
        ],
        'MemberLevelUpdate' => [
            'app\event\MemberLevelUpdate',//会员等级更新
        ],
        //会员行为事件
        'MemberAction' => [],
        //会员营销活动标志
        'MemberPromotion' => [],
        //会员注册后执行事件
        'MemberRegister' => [

        ],
        'MemberLogin' => [
            'app\event\MemberLogin'
        ],


        /**
         * 支付功能事件
         * 对应支付相关功能调用
         */
        //支付异步回调(支付插件完成，作用判定支付成功，返回对应支付编号)
        'PayNotify' => [

        ],

        /**
         * 订单功能事件
         * 完成订单相关操作调用
         */
        //订单支付异步执行
        'OrderPayNotify' => [
            'app\event\OrderPayNotify',//商城订单支付异步回调
        ],

        //店铺订单支付异步操作
        'SysOrderPayNotify' => [
            'app\event\SysOrderPayNotify',
        ],

        //订单创建后执行事件
        'OrderCreate' => [
            'app\event\OrderCreate'
        ],
        'OrderPay' => [
            'app\event\ShopOrderCalc',  //订单支付后统计

        ],  //订单支付成功后执行事件
        'OrderDelivery' => [], //订单发货
        'orderTakeDelivery' => [], //订单收货
        'OrderComplete' => [
            //订单完成后执行 后续事件
            'app\event\OrderComplete',  //订单支付后统计
        ],  //订单完成后执行事件
        //系统订单完成
        'SysOrderComplete' => [
            'app\event\SysOrderComplete',
            'app\event\SmsOrderComplete',
        ],
        'OrderClose' => [], //订单关闭后执行事件
        'OrderRefundFinish' => [
            'app\event\OrderRefundFinish'
        ],//订单项完成退款操作之后
        //核销类型
        'VerifyType' => [
        ],
        //核销
        'Verify' => [
            'app\event\PickupOrderVerify',//自提订单核销
            'app\event\VirtualGoodsVerify',//虚拟商品核销
        ],
        //执行店铺续签申请后店铺入驻时间续期
        'CronShopRelpay' => [
            'app\event\CronShopRelpay'
        ],
        'CronOrderClose' => [
            'app\event\CronOrderClose'
        ],
        // 核销商品临期提醒
        'VerifyOrderOutTime' => [
            'app\event\VerifyOrderOutTime'
        ],
        // 核销码过期提醒
        'CronVerifyCodeExpire' => [
            'app\event\CronVerifyCodeExpire'
        ],
        'CronOrderTakeDelivery' => [
            'app\event\CronOrderTakeDelivery'
        ],
        //自动执行订单自动完成
        'CronOrderComplete' => [
            'app\event\CronOrderComplete'
        ],
        // 自动关闭订单售后
        'CronOrderAfterSaleClose' => [
            'app\event\CronOrderAfterSaleClose'
        ],
        // 订单催付通知
        'CronOrderUrgePayment' => [
            'app\event\CronOrderUrgePayment'
        ],


        // 自定义组件
        'DiyViewUtils' => [
            'app\event\DiyViewUtils',
        ],

        // 自定义页面编辑
        'DiyViewEdit' => [
            'app\event\DiyViewEdit',
        ],

        /**
         * 物流公司
         */

        //物流跟踪
        'Trace' => [
            'app\event\Kd100Trace',//快递100 物流查询
            'app\event\KdbirdTrace'//快递鸟物流查询
        ],
        'CloseTrace' => [
            'app\event\CloseKd100Trace',//快递100 物流查询关闭
            'app\event\CloseKdbirdTrace'//快递鸟物流查询关闭
        ],

        /**
         * 消息发送
         */
        //消息模板
        'SendMessageTemplate' => [

            // 订单核销通知
            'app\event\MessageShopVerified',
            // 核销商品临期提醒
            'app\event\MessageVerifyOrderOutTime',
            // 订单催付通知
            'app\event\MessageOrderUrgePayment',
            // 订单关闭
            'app\event\MessageOrderClose',
            // 订单完成
            'app\event\MessageOrderComplete',
            // 订单支付
            'app\event\MessageOrderPaySuccess',
            // 订单发货
            'app\event\MessageOrderDelivery',

            // 商家同意退款
            'app\event\MessageShopRefundAgree',
            // 商家拒绝退款
            'app\event\MessageShopRefundRefuse',
            // 核销通知
            'app\event\MessageShopVerified',
            // 核销码过期提醒
            'app\event\MessageVerifyCodeExpire',

            // 注册验证
            'app\event\MessageRegisterCode',
            // 找回密码
            'app\event\MessageFindCode',
            // 会员登陆成功
            'app\event\MessageLogin',
            // 帐户绑定验证码
            'app\event\MessageBindCode',
            // 动态码登陆验证码
            'app\event\MessageLoginCode',
            // 支付密码修改通知
            'app\event\MessageMemberPayPassword',
            // 设置密码
            'app\event\MessageSetPassWord',
            // 买家发起退款提醒
            'app\event\MessageOrderRefundApply',
            // 买家已退货提醒
            'app\event\MessageOrderRefundDelivery',
            // 买家支付通知商家
            'app\event\MessageBuyerPaySuccess',
            // 买家订单完成通知
            'app\event\MessageBuyerOrderComplete',
            // 会员申请提现通知
            'app\event\MessageUserWithdrawalApply',
            // 会员提现成功通知
            'app\event\MessageUserWithdrawalSuccess',

            // 会员提现失败通知
            'app\event\MessageUserWithdrawalError',

            // 分销申请提现通知
            'app\event\MessageFenxiaoWithdrawalApply',
            // 分销提现成功通知
            'app\event\MessageFenxiaoWithdrawalSuccess',
            // 分销提现失败通知
            'app\event\MessageFenxiaoWithdrawalError',
            // 分销佣金发放通知
            'app\event\MessageOrderCommissionGrant',

            // 会员注销成功通知
            'app\event\MessageCancelSuccess',
            // 会员注销失败通知
            'app\event\MessageCancelFail',
            // 会员注销申请通知
            'app\event\MessageCancelApply',
            // 会员账户变动通知通知
            'app\event\MessageAccountChangeNotice',
            // 收银台会员验证验证码
            'app\event\MessageCashierMemberVerifyCode',
        ],
        //发送短信
        'sendSms' => [

        ],

        'Qrcode' => [
            'app\event\Qrcode'
        ],

        // api配置变更
        'ApiConfigChange' => [
            'app\event\ApiConfigChange'
        ],

        //关闭游戏
        'CloseGame' => [
            'app\event\CloseGame'
        ],
        //开启游戏
        'OpenGame' => [
            'app\event\OpenGame'
        ],

        //商品上架
        'CronGoodsTimerOn' => [
            'app\event\CronGoodsTimerOn'
        ],

        //商品下架
        'CronGoodsTimerOff' => [
            'app\event\CronGoodsTimerOff'
        ],

        'GoodsClass' => [
            'app\event\GoodsClass',
            'app\event\VirtualGoodsClass'
        ],

        //会员群体定时刷新
        'CronMemberClusterRefresh' => [
            'app\event\CronMemberClusterRefresh'
        ],

        //网站部署配置
        'SiteDeployData' => [
            // 域名跳转
            'app\event\DomainJumpSiteDeployData',
            // h5
            'app\event\H5SiteDeployData',
            // 客服
            'app\event\ServicerSiteDeployData'
        ],

        //微信分享数据
        'WchatShareData' => [
            'app\event\WchatShareData',
        ],
        //微信分享配置
        'WchatShareConfig' => [
            'app\event\WchatShareConfig',
        ],
        //小程序分享数据
        'WeappShareData' => [
            'app\event\WeappShareData',
        ],
        //小程序分享配置
        'WeappShareConfig' => [
            'app\event\WeappShareConfig',
        ],
    ],

    'subscribe' => [
    ],
];
