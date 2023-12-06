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

    // 自定义模板页面类型，格式：[ 'title' => '页面类型名称', 'name' => '页面标识', 'path' => '页面路径', 'value' => '页面数据，json格式' ]
    'template' => [
        [
            'title' => '店铺首页',
            'name' => 'DIY_VIEW_INDEX',
            'path' => '/pages/index/index',
            'value' => '',
            'rule' => [
                'support' => [ '', 'DIY_VIEW_INDEX' ]
            ],
            'sort' => 1
        ],
        [
            'title' => '商品分类',
            'name' => 'DIY_VIEW_GOODS_CATEGORY',
            'path' => '/pages/goods/category',
            'value' => '',
            'rule' => [
                'support' => [ 'DIY_VIEW_GOODS_CATEGORY' ]
            ],
            'sort' => 2
        ],
        [
            'title' => '会员中心',
            'name' => 'DIY_VIEW_MEMBER_INDEX',
            'path' => '/pages/member/index',
            'value' => '',
            'rule' => [
                'support' => [ '', 'DIY_VIEW_MEMBER_INDEX' ]
            ],
            'sort' => 3
        ],
    ],

    /*
     * 后台自定义组件——装修
     * 组件类型 SYSTEM：基础组件，PROMOTION：营销组件，EXTEND：扩展组件
        [
            'name' => '',  // 组件标识，控制器名称
            'title' => '', // 组件名称
            'type' => '', // 组件类型，SYSTEM：基础组件，PROMOTION：营销组件，EXTEND：扩展组件
            'value' => '{}', // 数据结构，json格式
            'sort' => '10000', // 排序号
            'support_diy_view' => '', // 支持的自定义页面（为空表示公共组件都支持）
            'max_count' => 0, // 限制添加次数，0表示可以无限添加该组件
            'is_delete' => 0, // 组件是否可以删除，0 允许，1 禁用
            'icon' => 'iconfont iconbiaoti', // 组件字体图标
        ],
     */
    'util' => [
        [
            'name' => 'Text',
            'title' => '标题',
            'type' => 'SYSTEM',
            'value' => '{"style":"style-1","styleName":"风格1","text":"标题栏","link":{"name":""},"fontSize":16,"fontWeight":"normal","subTitle":{"text":"副标题","color":"#999999","fontSize":14,"isElementShow":false,"bgColor":"","icon":"","fontWeight":"normal"},"more":{"text":"查看更多","link":{"name":""},"isElementShow":false,"isShow":false,"color":"#999999"}}',
            'sort' => '10000',
            'support_diy_view' => '',
            'max_count' => 0,
            'is_delete' => 0,
            'icon' => 'iconfont iconbiaoti',
        ],
        [
            'name' => 'Notice',
            'title' => '公告',
            'type' => 'SYSTEM',
            'value' => '{"style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0},"sources":"diy","icon":"","contentStyle":"style-1","list":[{"title":"公告","link":{"name":""}}],"iconSources":"initial","noticeIds":[],"iconType":"img","imageUrl":"","scrollWay":"upDown","fontSize":14,"fontWeight":"normal"}',
            'sort' => '10002',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont icongonggao1',
        ],
        [
            'name' => 'GraphicNav',
            'title' => '图文导航',
            'type' => 'SYSTEM',
            'value' => '{"ornament":{"type":"default","color":"#EDEDED"},"list":[{"title":"","style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0},"link":{"name":""},"icon":"","iconType":"img","imageUrl":"","label":{"control":false,"text":"热门","textColor":"#FFFFFF","bgColorStart":"#F83287","bgColorEnd":"#FE3423"}},{"title":"","style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0},"link":{"name":""},"icon":"","iconType":"img","imageUrl":"","label":{"control":false,"text":"热门","textColor":"#FFFFFF","bgColorStart":"#F83287","bgColorEnd":"#FE3423"}},{"title":"","style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0},"link":{"name":""},"icon":"","iconType":"img","imageUrl":"","label":{"control":false,"text":"热门","textColor":"#FFFFFF","bgColorStart":"#F83287","bgColorEnd":"#FE3423"}},{"title":"","style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0},"link":{"name":""},"icon":"","iconType":"img","imageUrl":"","label":{"control":false,"text":"热门","textColor":"#FFFFFF","bgColorStart":"#F83287","bgColorEnd":"#FE3423"}}],"mode":"graphic","type":"img","showStyle":"fixed","rowCount":4,"pageCount":2,"carousel":{"type":"circle","color":"#FFFFFF"},"imageSize":40,"aroundRadius":25,"font":{"size":14,"weight":"normal","color":"#303133"}}',
            'sort' => '10003',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont icontuwendaohang2',
        ],
        [
            'name' => 'ImageAds',
            'title' => '图片广告',
            'type' => 'SYSTEM',
            'value' => '{"indicatorIsShow":true,"indicatorColor":"#ffffff","carouselStyle":"circle","indicatorLocation":"center","list":[{"imageUrl":"","link":{"name":""},"imgWidth":0,"imgHeight":0}]}',
            'sort' => '10004',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont icontupianguanggao1',
        ],
        [
            'name' => 'Search',
            'title' => '搜索框',
            'type' => 'SYSTEM',
            'value' => '{"searchStyle":1,"searchLink":{"name":""},"title":"搜索","textAlign":"left","borderType":2,"iconType":"img","icon":"","style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0},"imageUrl":"","positionWay":"static"}',
            'sort' => '10005',
            'support_diy_view' => '',
            'max_count' => 1,
            'icon' => 'iconfont iconsousuokuang',
        ],
        [
            'name' => 'RichText',
            'title' => '富文本',
            'type' => 'SYSTEM',
            'value' => '{"html":""}',
            'sort' => '10007',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconfuwenben1',
        ],
        [
            'name' => 'RubikCube',
            'title' => '魔方',
            'type' => 'SYSTEM',
            'value' => '{"mode":"row1-of2","imageGap":0,"list":[{"imageUrl":"","imgWidth":0,"imgHeight":0,"previewWidth":0,"previewHeight":0,"link":{"name":""}},{"imageUrl":"","imgWidth":0,"imgHeight":0,"previewWidth":0,"previewHeight":0,"link":{"name":""}}]}',
            'sort' => '10008',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconmofang1'
        ],
        [
            'name' => 'HorzLine',
            'title' => '辅助线',
            'type' => 'SYSTEM',
            'value' => '{"color":"#303133","borderStyle":"solid"}',
            'sort' => '10011',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconfuzhuxian1'
        ],
        [
            'name' => 'HorzBlank',
            'title' => '辅助空白',
            'type' => 'SYSTEM',
            'value' => '{"height":10}',
            'sort' => '10012',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconfuzhukongbai1'
        ],
        [
            'name' => 'Video',
            'title' => '视频',
            'type' => 'SYSTEM',
            'value' => '{"imageUrl":"","videoUrl":"","type":"upload"}',
            'sort' => '10013',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconshipin1'
        ],
//		[
//			'name' => 'VOICE',
//			'title' => '语音',
//			'type' => 'SYSTEM',
//			'controller' => '',
//			'value' => '',
//			'sort' => '10014',
//			'support_diy_view' => '',
//			'max_count' => 0
//		],
        [
            'name' => 'GoodsList',
            'title' => '商品列表',
            'type' => 'SYSTEM',
            'value' => '{"style":"style-2","sources":"initial","ornament":{"type":"default","color":"#EDEDED"},"template":"row1-of2","goodsMarginType":"default","goodsMarginNum":10,"count":6,"goodsId":[],"categoryId":0,"categoryName":"请选择","sortWay":"default","nameLineMode":"single","imgAroundRadius":0,"slideMode":"scroll","theme":"default","btnStyle":{"fontWeight":false,"padding":0,"cartEvent":"detail","text":"购买","textColor":"#FFFFFF","theme":"default","aroundRadius":25,"control":true,"support":true,"bgColor":"#FF6A00","style":"button","iconDiy":{"iconType":"icon","icon":"","style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0}}},"tag":{"text":"隐藏","value":"hidden"},"goodsNameStyle":{"color":"#303133","control":true,"fontWeight":false},"saleStyle":{"color":"#999CA7","control":false,"support":true},"priceStyle":{"mainColor":"#FF6A00","mainControl":true,"lineColor":"#999CA7","lineControl":false,"lineSupport":true}}',
            'sort' => '10016',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconshangpinliebiao1'
        ],
        [
            'name' => 'ManyGoodsList',
            'title' => '多商品组',
            'type' => 'SYSTEM',
            'value' => '{"style":"style-2","ornament":{"type":"default","color":"#EDEDED"},"template":"row1-of2","goodsMarginType":"default","goodsMarginNum":10,"count":6,"sortWay":"default","nameLineMode":"single","imgAroundRadius":0,"slideMode":"scroll","theme":"default","btnStyle":{"fontWeight":false,"padding":0,"cartEvent":"detail","text":"购买","textColor":"#FFFFFF","theme":"default","aroundRadius":25,"control":true,"support":true,"bgColor":"#FF6A00","style":"button","iconDiy":{"iconType":"icon","icon":"","style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0}}},"tag":{"text":"隐藏","value":"hidden"},"goodsNameStyle":{"color":"#303133","control":true,"fontWeight":false},"saleStyle":{"color":"#999CA7","control":false,"support":true},"priceStyle":{"mainColor":"#FF6A00","mainControl":true,"lineColor":"#999CA7","lineControl":false,"lineSupport":true},"list":[{"title":"热卖","desc":"热卖推荐","sources":"category","categoryId":0,"categoryName":"请选择","goodsId":[]}]}',
            'sort' => '10017',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconduoshangpinzu',
        ],
        [
            'name' => 'GoodsRecommend',
            'title' => '商品推荐',
            'type' => 'SYSTEM',
            'value' => '{"ornament":{"type":"default","color":"#EDEDED"},"style":"style-1","sources":"initial","count":6,"goodsId":[],"categoryId":0,"categoryName":"请选择","sortWay":"default","nameLineMode":"single","imgAroundRadius":10,"theme":"default","goodsNameStyle":{"color":"#303133","control":true,"fontWeight":false,"support":true},"saleStyle":{"color":"#999CA7","control":true,"support":true},"priceStyle":{"mainColor":"#FF1544","mainControl":true,"lineColor":"#999CA7","lineControl":false,"lineSupport":false},"topStyle":{"title":"今日推荐","subTitle":"大家都在买","icon":{"value":"icondiy icon-system-tuijian","color":"#FF3D3D","bgColor":""},"color":"#303133","subColor":"#999CA7","support":true},"bgUrl":"","styleName":"风格1","labelStyle":{"support":false,"bgColor":"#FF504D","title":"新人专享","color":"#FFFFFF"}}',
            'sort' => '10018',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont icontuijianshangpin',
        ],
        [
            'name' => 'GoodsCategory',
            'title' => '商品分类',
            'type' => 'SYSTEM',
            'value' => '{"level":"2","template":"1", "quickBuy": 0, "search": 1, "goodsLevel" : 1, "loadType": "all","sortWay":"default" }',
            'sort' => '10019',
            'support_diy_view' => 'DIY_VIEW_GOODS_CATEGORY',
            'max_count' => 1,
            'is_delete' => 1,
            'icon' => 'iconfont iconshangpinfenlei1'
        ],
        [
            'name' => 'GoodsBrand',
            'title' => '商品品牌',
            'type' => 'SYSTEM',
            'value' => '{"style":"style-1","sources":"initial","brandIds":[],"title":"品牌展示","fontWeight":false,"previewList":{},"count":8,"ornament":{"type":"default","color":"#EDEDED"}}',
            'sort' => '10020',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconshangpinpinpai'
        ],
        [
            'name' => 'TopCategory',
            'title' => '分类导航',
            'type' => 'SYSTEM',
            'value' => '{"title":"首页","selectColor":"#FF4544","noColor":"#333333","styleType":"line","moreColor":"#333333"}',
            'sort' => '10022',
            'support_diy_view' => '',
            'max_count' => 1,
            'icon' => 'iconfont icondaohang',
        ],
        [
            'name' => 'FloatBtn',
            'title' => '浮动按钮',
            'type' => 'SYSTEM',
            'value' => '{"btnBottom":"0","imageSize":40,"bottomPosition":"4","list":[{"imageUrl":"","link":{"name":""},"iconType":"img","icon":"","style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0}}]}',
            'sort' => '10023',
            'support_diy_view' => '',
            'max_count' => 1,
            'icon' => 'iconfont iconfudonganniu1',
        ],
        [
            'name' => 'Article',
            'title' => '文章',
            'type' => 'SYSTEM',
            'value' => '{"style":"style-1","sources":"initial","previewList":{},"count":8,"ornament":{"type":"default","color":"#EDEDED"},"articleIds":[]}',
            'sort' => '10024',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconwenzhang',
        ],
        [
            'name' => 'MemberInfo',
            'title' => '会员信息',
            'type' => 'SYSTEM',
            'value' => '{"style":2,"theme":"default","bgColorStart":"#FF7130","bgColorEnd":"#FF1542","gradientAngle":"129","infoMargin":15}',
            'sort' => '10025',
            'support_diy_view' => 'DIY_VIEW_MEMBER_INDEX',
            'max_count' => 1,
            'icon' => 'iconfont iconwenzhang',
        ],
        [
            'name' => 'MemberMyOrder',
            'title' => '我的订单',
            'type' => 'SYSTEM',
            'value' => '{"icon":{"waitPay":{"title":"待付款","icon":"icondiy icon-system-daifukuan2","style":{"bgRadius":0,"fontSize":65,"iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","iconColor":["#FF7B1D","#FF1544"],"iconColorDeg":111}},"waitSend":{"title":"待发货","icon":"icondiy icon-system-daifahuo2","style":{"bgRadius":0,"fontSize":65,"iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","iconColor":["#FF7B1D","#FF1544"],"iconColorDeg":111}},"waitConfirm":{"title":"待收货","icon":"icondiy icon-system-daishouhuo2","style":{"bgRadius":0,"fontSize":65,"iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","iconColor":["#FF7B1D","#FF1544"],"iconColorDeg":111}},"waitUse":{"title":"待使用","icon":"icondiy icon-system-daishiyong2","style":{"bgRadius":0,"fontSize":65,"iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","iconColor":["#FF7B1D","#FF1544"],"iconColorDeg":111}},"refunding":{"title":"售后","icon":"icondiy icon-system-shuhou2","style":{"bgRadius":0,"fontSize":65,"iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","iconColor":["#FF7B1D","#FF1544"],"iconColorDeg":111}}},"style":1}',
            'sort' => '10026',
            'support_diy_view' => 'DIY_VIEW_MEMBER_INDEX',
            'max_count' => 1,
            'icon' => 'iconfont iconwenzhang',
        ],
        [
            'name' => 'FollowOfficialAccount',
            'title' => '关注公众号',
            'type' => 'SYSTEM',
            'value' => '{"isShow":true,"welcomeMsg":"欢迎光顾我的小店，随时为您服务~"}',
            'sort' => '10026',
            'support_diy_view' => 'DIY_VIEW_INDEX',
            'max_count' => 1,
            'icon' => 'iconfont iconguanzhugongzhonghao',
        ],
        [
            'name' => 'QuickNav',
            'title' => '快捷导航',
            'type' => 'SYSTEM',
            'value' => '{"list":[{"title":"导航名称","style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0},"link":{"name":""},"icon":"","bgColorStart":"","bgColorEnd":"","textColor":"#303133","iconType":"img","imageUrl":""}],"ornament":{"type":"default","color":"#EDEDED"}}',
            'sort' => '10027',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconkuaijiedaohang'
        ],
        [
            'name' => 'HotArea',
            'title' => '热区',
            'type' => 'SYSTEM',
            'value' => '{"imageUrl":"","imgWidth":0,"imgHeight":0,"heatMapData":[]}',
            'sort' => '10028',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconrequ'
        ],
    ],

    // 自定义页面路径
    'link' => [
        [
            'name' => 'MALL_PAGE',
            'title' => '商城页面',
            'parent' => '',
            'wap_url' => '',
            'web_url' => '',
            'sort' => 1,
            'child_list' => [
                [
                    'name' => 'MALL_LINK',
                    'title' => '商城链接',
                    'wap_url' => '',
                    'web_url' => '',
                    'sort' => 0,
                    'child_list' => [
                        [
                            'name' => 'BASICS_LINK',
                            'title' => '基础链接',
                            'wap_url' => '',
                            'web_url' => '',
                            'sort' => 0,
                            'child_list' => [
                                [
                                    'name' => 'INDEX',
                                    'title' => '主页',
                                    'wap_url' => '/pages/index/index',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'SHOP_CATEGORY',
                                    'title' => '商品分类',
                                    'wap_url' => '/pages/goods/category',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'SHOP_GOODS_LIST',
                                    'title' => '商品列表',
                                    'wap_url' => '/pages/goods/list',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'SHOPPING_TROLLEY',
                                    'title' => '购物车',
                                    'wap_url' => '/pages/goods/cart',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'GOODS_COUPON_LIST',
                                    'title' => '优惠券列表',
                                    'wap_url' => '/pages/goods/coupon',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'SHOPPING_NOTICE',
                                    'title' => '公告',
                                    'wap_url' => '/pages_tool/notice/list',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'SHOPPING_HELP',
                                    'title' => '帮助',
                                    'wap_url' => '/pages_tool/help/list',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'SHOPPING_ARTICLE',
                                    'title' => '文章',
                                    'wap_url' => '/pages_tool/article/list',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'SHOPPING_BRAND',
                                    'title' => '品牌专区',
                                    'wap_url' => '/pages_tool/goods/brand',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
//                                [
//                                    'name' => 'CUSTOMER_SERVICE',
//                                    'title' => '客服',
//                                    'wap_url' => '',
//                                    'web_url' => '',
//                                    'sort' => 0
//                                ]
                            ]
                        ],
                        [
                            'name' => 'MEMBER',
                            'title' => '会员中心',
                            'wap_url' => '',
                            'web_url' => '',
                            'sort' => 1,
                            'child_list' => [
                                [
                                    'name' => 'MEMBER_CENTER',
                                    'title' => '会员中心',
                                    'wap_url' => '/pages/member/index',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'ALL_ORDER',
                                    'title' => '全部订单',
                                    'wap_url' => '/pages/order/list',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'OBLIGATION_ORDER',
                                    'title' => '待付款订单',
                                    'wap_url' => '/pages/order/list?status=waitpay',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'DELIVER_ORDER',
                                    'title' => '待发货订单',
                                    'wap_url' => '/pages/order/list?status=waitsend',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'TAKE_DELIVER_ORDER',
                                    'title' => '待收货订单',
                                    'wap_url' => '/pages/order/list?status=waitconfirm',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'EVALUATE_ORDER',
                                    'title' => '待评价订单',
                                    'wap_url' => '/pages/order/list?status=waitrate',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'REFUND_ORDER',
                                    'title' => '退款订单',
                                    'wap_url' => '/pages/order/activist',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'MEMBER_INFO',
                                    'title' => '个人资料',
                                    'wap_url' => '/pages_tool/member/info',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'SHIPPING_ADDRESS',
                                    'title' => '收货地址',
                                    'wap_url' => '/pages_tool/member/address',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'BALANCE',
                                    'title' => '我的余额',
                                    'wap_url' => '/pages_tool/member/balance',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'SIGN_IN',
                                    'title' => '签到',
                                    'wap_url' => '/pages_tool/member/signin',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'MEMBER_LEVEL',
                                    'title' => '会员等级',
                                    'wap_url' => '/pages_tool/member/level',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'FOOTPRINT',
                                    'title' => '我的足迹',
                                    'wap_url' => '/pages_tool/member/footprint',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'ATTENTION',
                                    'title' => '我的关注',
                                    'wap_url' => '/pages_tool/member/collection',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'ACCOUNT',
                                    'title' => '账户列表',
                                    'wap_url' => '/pages_tool/member/account',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'COUPON',
                                    'title' => '优惠券',
                                    'wap_url' => '/pages_tool/member/coupon',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'VERIFICATION_PLATFORM',
                                    'title' => '核销台',
                                    'wap_url' => '/pages_tool/verification/index',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'MEMBER_CARD',
                                    'title' => '会员卡',
                                    'wap_url' => '/pages_tool/member/card',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'MEMBER_RECHARGE_LIST',
                                    'title' => '充值列表',
                                    'wap_url' => '/pages_tool/recharge/list',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                                [
                                    'name' => 'MEMBER_CONTACT',
                                    'title' => '客服',
                                    'wap_url' => '/pages_tool/member/contact',
                                    'web_url' => '',
                                    'sort' => 0
                                ],
                            ]
                        ],
                    ]
                ],
                [
                    'name' => 'MICRO_PAGE',
                    'title' => '微页面',
                    'wap_url' => '',
                    'web_url' => '',
                    'sort' => 1,
                    'child_list' => []
                ],
                [
                    'name' => 'MARKETING_LINK',
                    'title' => '营销链接',
                    'wap_url' => '',
                    'web_url' => '',
                    'sort' => 2,
                    'child_list' => []
                ],
                [
                    'name' => 'GOODS_CATEGORY',
                    'title' => '商品分类',
                    'wap_url' => '',
                    'web_url' => '',
                    'sort' => 3,
                    'child_list' => []
                ]
            ]
        ],
        [
            'name' => 'COMMODITY',
            'title' => '商品',
            'parent' => '',
            'wap_url' => '',
            'web_url' => '',
            'sort' => 8,
            'child_list' => [
                [
                    'name' => 'ALL_GOODS',
                    'title' => '全部商品',
                    'wap_url' => '',
                    'web_url' => '',
                    'child_list' => [],
                    'sort' => 1,
                ]
            ]
        ],
        [
            'name' => 'INTERACTION_PROMOTION',
            'title' => '互动营销',
            'parent' => '',
            'wap_url' => '',
            'web_url' => '',
            'sort' => 9,
            'child_list' => []
        ],
        [
            'name' => 'OTHER',
            'title' => '其他',
            'parent' => '',
            'wap_url' => '',
            'web_url' => '',
            'sort' => 10,
            'child_list' => [
                [
                    'name' => 'CUSTOM_LINK',
                    'title' => '自定义链接',
                    'wap_url' => '',
                    'web_url' => '',
                    'sort' => 1,
                    'child_list' => []
                ],
                [
                    'name' => 'OTHER_APPLET',
                    'title' => '其他小程序',
                    'wap_url' => '',
                    'web_url' => '',
                    'sort' => 2,
                    'child_list' => []
                ],
                [
                    'name' => 'MOBILE',
                    'title' => '拨打电话',
                    'wap_url' => '',
                    'web_url' => '',
                    'sort' => 3,
                    'child_list' => []
                ]
            ]
        ]
    ],

    // 自定义图标库
    'icon_library' => [

        // 组件图标【用于后台组件装修】
        'component' => [
            'name' => 'iconfont', // 字体名称
            'path' => 'public/static/css/iconfont.css' // 文件路径
        ],

        // 图标
        'icon' => [
            'name' => 'icondiy', // 字体名称
            'path' => 'public/static/ext/diyview/css/font/iconfont.css' // 文件路径
        ],

        // 图标类型
        'type' => [
            'icon-system' => '系统',
            'icon-emoji' => '表情',
            'icon-food' => '美食',
            'icon-clothes' => '服装',
            'icon-beauty' => '美妆',
            'icon-weather' => '天气',
            'icon-edu' => '教育',
            'icon-sport' => '体育',
            'icon-tourism' => '旅游',
        ]
    ],

    /*
     * uni-app 组件，格式：[ 'name' => '组件名称/文件夹名称', 'path' => '文件路径/目录路径' ]，多个逗号隔开，自定义组件名称前缀必须是diy-，也可以引用第三方组件
     * 例如：
        [
            'name' => 'diy-horz-blank',
            'path' => 'uniapp/component/diy-components/diy-horz-blank.vue'
        ]
     */
    'component' => [],

    /*
     * uni-app 页面
     * 格式如下：多个逗号隔开，style：https://uniapp.dcloud.net.cn/collocation/pages.html#pages
        [
            'path' => '页面路径，例如：pages/index/index',
            'style' => [ 'navigationBarTitleText' => '店铺首页' ]
        ]
     */
    'pages' => [],

    // 模板信息，格式：'title' => '模板名称', 'name' => '模板标识', 'cover' => '模板封面图', 'preview' => '模板预览图', 'desc' => '模板描述'
    'info' => [],

    /*
     * 主题风格配色，格式可以自由定义扩展，【在uni-app中通过：this.themeStyle... 获取定义的颜色字段，例如：this.themeStyle.main_color】
     * 格式：
        [
            'title' => '主题风格名称',
            'name' => '风格标识',
            'preview' => [ '预览图' ],
            'color_img' => '配色图',
            'main_color' => '主色调',
            'aux_color' => '辅色调',
            'bg_color' => '主背景色',
            'bg_color_shallow' => '主题背景渐变浅色',
            'promotion_color' => '活动背景',
            'promotion_aux_color' => '活动背景辅色',
            'main_color_shallow' => '淡背景',
            'price_color' => '价格颜色',
            'btn_text_color' => '按钮文字颜色'
            ...定义更多颜色字段
        ]
     */
    'theme' => [
        [
            'title' => '热情红',
            'name' => 'default',
            'preview' => [
                'public/static/img/diy_view/style/decorate-default-1.jpg',
                'public/static/img/diy_view/style/decorate-default-2.jpg',
                'public/static/img/diy_view/style/decorate-default-3.jpg',
            ],
            'color_img' => 'public/static/img/diy_view/style/default.png', // 配色图
            'main_color' => '#F4391c',
            'aux_color' => '#F7B500',
            'bg_color' => '#FF4646',//主题背景
            'bg_color_shallow' => '#FF4646',//主题背景渐变浅色
            'promotion_color' => '#FF4646',//活动背景
            'promotion_aux_color' => '#F7B500',//活动背景辅色
            'main_color_shallow' => '#FFF4F4',//淡背景
            'price_color' => 'rgb(252,82,39)',//价格颜色
            'btn_text_color' => '#FFFFFF',//按钮文字颜色
            'goods_detail' => [
                'goods_price' => 'rgb(252,82,39,1)',//价格
                'promotion_tag' => '#FF4646',
                'goods_card_bg' => '#201A18',//会员卡背景
                'goods_card_bg_shallow' => '#7C7878',//会员卡背景浅色
                'goods_card_color' => '#FFD792',
                'goods_coupon' => '#FC5227',
                'goods_cart_num_corner' => '#FC5227',//购物车数量角标
                'goods_btn_color' => '#FF4646',//按钮颜色
                'goods_btn_color_shallow' => '#F7B500',//副按钮颜色
            ],
            'super_member' => [
                'super_member_start_bg' => '#7c7878',
                'super_member_end_bg' => '#201a18',
                'super_member_start_text_color' => '#FFDBA6',
                'super_member_end_text_color' => '#FFEBCA',
            ],
            'giftcard' => [
                'giftcard_promotion_color' => '#FF3369',//活动背景
                'giftcard_promotion_aux_color' => '#F7B500',//活动辅色
            ],
        ],
        [
            'title' => '商务蓝',
            'name' => 'blue',
            'preview' => [
                'public/static/img/diy_view/style/decorate-blue-1.jpg',
                'public/static/img/diy_view/style/decorate-blue-2.jpg',
                'public/static/img/diy_view/style/decorate-blue-3.jpg',
            ],
            'color_img' => 'public/static/img/diy_view/style/blue.png',
            'main_color' => '#36ABFF',
            'aux_color' => '#FA6400',
            'bg_color' => '#36ABFF',
            'bg_color_shallow' => '#36ABFF',
            'promotion_color' => '#36ABFF ',
            'promotion_aux_color' => '#FA6400',
            'main_color_shallow' => '#E2F3FF',
            'price_color' => 'rgba(252,82,39,1)',//价格颜色
            'btn_text_color' => '#FFFFFF',//按钮文字颜色
            'goods_detail ' => [
                'goods_price ' => 'rgba(252,82,39,1)',//价格
                'promotion_tag' => '#36ABFF',
                'goods_card_bg' => '#201A18',//会员卡背景
                'goods_card_bg_shallow' => '#7C7878',//会员卡背景浅色
                'goods_card_color' => '#FFD792',
                'goods_coupon' => '#FC5227',
                'goods_cart_num_corner ' => '#FC5227',//购物车数量角标
                'goods_btn_color' => '#36ABFF',//按钮颜色
                'goods_btn_color_shallow' => '#FA6400',//副按钮颜色
            ],
            'super_member' => [
                'super_member_start_bg' => '#7c7878',
                'super_member_end_bg' => '#201a18',
                'super_member_start_text_color' => '#FFDBA6',
                'super_member_end_text_color' => '#FFEBCA',
            ],
            'giftcard' => [
                'giftcard_promotion_color' => '#FF3369',//活动背景
                'giftcard_promotion_aux_color' => '#F7B500',//活动辅色
            ],
        ],
        [
            'title' => '纯净绿',
            'name' => 'green',
            'preview' => [
                'public/static/img/diy_view/style/decorate-green-1.jpg',
                'public/static/img/diy_view/style/decorate-green-2.jpg',
                'public/static/img/diy_view/style/decorate-green-3.jpg',
            ],
            'color_img' => 'public/static/img/diy_view/style/green.png',
            'main_color' => '#19C650',
            'aux_color' => '#FA6400',
            'bg_color' => '#19C650',
            'bg_color_shallow' => '#19C650',
            'promotion_color' => '#19C650',
            'promotion_aux_color' => '#FA6400',
            'main_color_shallow' => '#F0FFF5',//淡背景
            'price_color' => 'rgba(252,82,39,1)',//价格颜色
            'btn_text_color' => '#FFFFFF',//按钮文字颜色
            'goods_detail' => [
                'goods_price' => 'rgba(252,82,39,1)',//价格
                'promotion_tag' => '#19C650',
                'goods_card_bg' => '#201A18',//会员卡背景
                'goods_card_bg_shallow' => '#7C7878',//会员卡背景浅色
                'goods_card_color' => '#FFD792',
                'goods_coupon' => '#FC5227',
                'goods_cart_num_corner ' => '#FC5227',//购物车数量角标
                'goods_btn_color' => '#19C650',//按钮颜色
                'goods_btn_color_shallow' => '#FA6400',//副按钮颜色
            ],
            'super_member' => [
                'super_member_start_bg' => '#7c7878',
                'super_member_end_bg' => '#201a18',
                'super_member_start_text_color' => '#FFDBA6',
                'super_member_end_text_color' => '#FFEBCA',
            ],
            'giftcard' => [
                'giftcard_promotion_color' => '#FF3369',//活动背景
                'giftcard_promotion_aux_color' => '#F7B500',//活动辅色
            ],
        ],
        [
            'title' => '樱花粉',
            'name' => 'pink',
            'preview' => [
                'public/static/img/diy_view/style/decorate-pink-1.jpg',
                'public/static/img/diy_view/style/decorate-pink-2.jpg',
                'public/static/img/diy_view/style/decorate-pink-3.jpg',
            ],
            'color_img' => 'public/static/img/diy_view/style/pink.png',
            'main_color' => '#FF407E',
            'aux_color' => '#F7B500',
            'bg_color' => '#FF407E',//主题背景
            'bg_color_shallow' => '#FF407E',//主题背景渐变浅色
            'promotion_color' => '#FF407E',//活动背景
            'promotion_aux_color' => '#F7B500',//活动背景辅色
            'main_color_shallow' => '#FFF5F8',//淡背景
            'price_color' => 'rgba(252,82,39,1)',//价格颜色
            'btn_text_color' => '#FFFFFF',//按钮文字颜色
            'goods_detail ' => [
                'goods_price ' => 'rgba(252,82,39,1)',//价格
                'promotion_tag' => '#FF407E',
                'goods_card_bg' => '#201A18',//会员卡背景
                'goods_card_bg_shallow' => '#7C7878',//会员卡背景浅色
                'goods_card_color' => '#FFD792',
                'goods_coupon' => '#FC5227',
                'goods_cart_num_corner ' => '#FC5227',//购物车数量角标
                'goods_btn_color' => '#FF407E',//按钮颜色
                'goods_btn_color_shallow' => '#F7B500',//副按钮颜色
            ],
            'super_member' => [
                'super_member_start_bg' => '#7c7878',
                'super_member_end_bg' => '#201a18',
                'super_member_start_text_color' => '#FFDBA6',
                'super_member_end_text_color' => '#FFEBCA',
            ],
            'giftcard' => [
                'giftcard_promotion_color' => '#FF3369',//活动背景
                'giftcard_promotion_aux_color' => '#F7B500',//活动辅色
            ],
        ],
        [
            'title' => '魅力金',
            'name' => 'gold',
            'preview' => [
                'public/static/img/diy_view/style/decorate-gold-1.jpg',
                'public/static/img/diy_view/style/decorate-gold-2.jpg',
                'public/static/img/diy_view/style/decorate-gold-3.jpg',
            ],
            'color_img' => 'public/static/img/diy_view/style/gold.png',
            'main_color' => '#CFAF70',
            'aux_color' => '#444444',
            'bg_color' => '#CFAF70',//主题背景
            'bg_color_shallow' => '#CFAF70',//主题背景渐变浅色
            'promotion_color' => '#CFAF70',//活动背景
            'promotion_aux_color' => '#444444',//活动背景辅色
            'main_color_shallow' => '#FFFAF1',//淡背景
            'price_color' => 'rgba(252,82,39,1)',//价格颜色
            'btn_text_color' => '#FFFFFF',//按钮文字颜色
            'goods_detail ' => [
                'goods_price ' => 'rgba(252,82,39,1)',//价格
                'promotion_tag' => '#CFAF70',
                'goods_card_bg' => '#201A18',//会员卡背景
                'goods_card_bg_shallow' => '#7C7878',//会员卡背景浅色
                'goods_card_color' => '#FFD792',
                'goods_coupon' => '#FC5227',
                'goods_cart_num_corner ' => '#FC5227',//购物车数量角标
                'goods_btn_color' => '#CFAF70',//按钮颜色
                'goods_btn_color_shallow' => '#444444',//副按钮颜色
            ],
            'super_member' => [
                'super_member_start_bg' => '#7c7878',
                'super_member_end_bg' => '#201a18',
                'super_member_start_text_color' => '#FFDBA6',
                'super_member_end_text_color' => '#FFEBCA',
            ],
            'giftcard' => [
                'giftcard_promotion_color' => '#FF3369',//活动背景
                'giftcard_promotion_aux_color' => '#F7B500',//活动辅色
            ],
        ],
        [
            'title' => '丁香紫',
            'name' => 'purple',
            'preview' => [
                'public/static/img/diy_view/style/decorate-purple-1.jpg',
                'public/static/img/diy_view/style/decorate-purple-2.jpg',
                'public/static/img/diy_view/style/decorate-purple-3.jpg',
            ],
            'color_img' => 'public/static/img/diy_view/style/purple.png',
            'main_color' => '#A253FF',
            'aux_color' => '#222222',
            'bg_color' => '#A253FF',//主题背景
            'bg_color_shallow' => '#A253FF',//主题背景渐变浅色
            'promotion_color' => '#A253FF',//活动背景
            'promotion_aux_color' => '#222222',//活动背景辅色
            'main_color_shallow' => '#F8F3FF',//淡背景
            'price_color' => 'rgba(252,82,39,1)',//价格颜色
            'btn_text_color' => '#FFFFFF',//按钮文字颜色
            'goods_detail ' => [
                'goods_price ' => 'rgba(252,82,39,1)',//价格
                'promotion_tag' => '#A253FF',
                'goods_card_bg' => '#201A18',//会员卡背景
                'goods_card_bg_shallow' => '#7C7878',//会员卡背景浅色
                'goods_card_color' => '#FFD792',
                'goods_coupon' => '#FC5227',
                'goods_cart_num_corner ' => '#FC5227',//购物车数量角标
                'goods_btn_color' => '#A253FF',//按钮颜色
                'goods_btn_color_shallow' => '#222222',//副按钮颜色
            ],
            'super_member' => [
                'super_member_start_bg' => '#7c7878',
                'super_member_end_bg' => '#201a18',
                'super_member_start_text_color' => '#FFDBA6',
                'super_member_end_text_color' => '#FFEBCA',
            ],
            'giftcard' => [
                'giftcard_promotion_color' => '#FF3369',//活动背景
                'giftcard_promotion_aux_color' => '#F7B500',//活动辅色
            ],
        ],
        [
            'title' => '明艳黄',
            'name' => 'yellow',
            'preview' => [
                'public/static/img/diy_view/style/decorate-yellow-1.jpg',
                'public/static/img/diy_view/style/decorate-yellow-2.jpg',
                'public/static/img/diy_view/style/decorate-yellow-3.jpg',
            ],
            'color_img' => 'public/static/img/diy_view/style/yellow.png',
            'main_color' => '#FFD009',
            'aux_color' => '#1D262E',
            'bg_color' => '#FFD009',//主题背景
            'bg_color_shallow' => '#FFD009',//主题背景渐变浅色
            'promotion_color' => '#FFD009',//活动背景
            'promotion_aux_color' => '#1D262E',//活动背景辅色
            'main_color_shallow' => '#FFFBEF',//淡背景
            'price_color' => 'rgba(252,82,39,1)',//价格颜色
            'btn_text_color' => '#303133',//按钮文字颜色
            'goods_detail ' => [
                'goods_price ' => 'rgba(252,82,39,1)',//价格
                'promotion_tag' => '#FFD009',
                'goods_card_bg' => '#201A18',//会员卡背景
                'goods_card_bg_shallow' => '#7C7878',//会员卡背景浅色
                'goods_card_color' => '#FFD792',
                'goods_coupon' => '#FC5227',
                'goods_cart_num_corner ' => '#FC5227',//购物车数量角标
                'goods_btn_color' => '#FFD009',//按钮颜色
                'goods_btn_color_shallow' => '#1D262E',//副按钮颜色
            ],
            'super_member' => [
                'super_member_start_bg' => '#7c7878',
                'super_member_end_bg' => '#201a18',
                'super_member_start_text_color' => '#FFDBA6',
                'super_member_end_text_color' => '#FFEBCA',
            ],
            'giftcard' => [
                'giftcard_promotion_color' => '#FF3369',//活动背景
                'giftcard_promotion_aux_color' => '#F7B500',//活动辅色
            ],
        ],
        [
            'title' => '炫酷黑',
            'name' => 'black',
            'preview' => [
                'public/static/img/diy_view/style/decorate-black-1.jpg',
                'public/static/img/diy_view/style/decorate-black-2.jpg',
                'public/static/img/diy_view/style/decorate-black-3.jpg',
            ],
            'color_img' => 'public/static/img/diy_view/style/black.png',
            'main_color' => '#222222',
            'aux_color' => '#FFFFFF',
            'bg_color' => '#222222',//主题背景
            'bg_color_shallow' => '#333333',//主题背景渐变浅色
            'promotion_color' => '#222222',//活动背景
            'promotion_aux_color' => '#FA8B00',//活动背景辅色
            'main_color_shallow' => '#efefef',//淡背景
            'price_color' => 'rgba(255,0,0,1)',//价格颜色
            'btn_text_color' => '#FFFFFF',//按钮文字颜色
            'goods_detail ' => [
                'goods_price ' => 'rgba(255,0,0,1)',//价格
                'promotion_tag' => '#222222',
                'goods_card_bg' => '#201A18',//会员卡背景
                'goods_card_bg_shallow' => '#7C7878',//会员卡背景浅色
                'goods_card_color' => '#FFD792',
                'goods_coupon' => '#222222',
                'goods_cart_num_corner ' => '#FF0000',//购物车数量角标
                'goods_btn_color' => '#222222',//按钮颜色
                'goods_btn_color_shallow' => '#FA8B00',//副按钮颜色
            ],
            'super_member' => [
                'super_member_start_bg' => '#fadcb5',
                'super_member_end_bg' => '#f6bd74',
                'super_member_start_text_color' => '#ab6126',
                'super_member_end_text_color' => '#d19336',
            ],
            'giftcard' => [
                'giftcard_promotion_color' => '#FF3369',//活动背景
                'giftcard_promotion_aux_color' => '#F7B500',//活动辅色
            ],
        ]
    ],

    // 自定义页面数据，格式：[ 'title' => '页面名称', 'name' => "页面标识", 'value' => [页面数据，json格式] ]
    'data' => []
];