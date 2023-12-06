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
    'template' => [],

    // 后台自定义组件——装修
    'util' => [
//        [
//            'name' => 'TextExtend', // 组件控制器名称
//            'title' => '扩展标题',
//            'type' => 'EXTEND', // 组件类型，SYSTEM：基础组件，PROMOTION：营销组件，EXTEND：扩展组件
//            'value' => '{}',
//            'sort' => '50000',
//            'support_diy_view' => '', // 支持的自定义页面（为空表示公共组件都支持）
//            'max_count' => 0, // 限制添加次数，0表示可以无限添加该组件
//            'is_delete' => 0, // 组件是否可以删除，0 允许，1 禁用
//            'icon' => 'icon-comp-diy-default icon-wenben', // 组件字体图标
//        ],
    ],

    // 自定义页面路径
    'link' => [],

    // 自定义图标库
    'icon_library' => [

        // 组件图标【用于后台组件装修】
        'component' => [
//            'name' => 'icon-comp-diy-default', // 字体名称
//            'path' => 'addon/diy_default1/shop/view/public/css/comp_iconfont.css' // 文件路径
        ],

        // 自定义图标库
        'icon' => [
//            'name' => 'icondiy-my-template', // 字体名称
//            'path' => 'addon/diy_default1/shop/view/public/css/diy_iconfont.css' // 文件路径
        ],

        // 图标类型
        'type' => [
//            'icon-building' => '建筑',
//            'icon-furniture' => '家具',
//            'icon-animal' => '动物',
        ]
    ],

    // uni-app 组件，格式：[ 'name' => '组件名称/文件夹名称', 'path' => '文件路径/目录路径' ]，多个逗号隔开，自定义组件名称前缀必须是diy-，也可以引用第三方组件
    'component' => [
//        [
//            'name' => 'TextExtend', // 组件名称
//            'path' => 'components/diy-components/diy-text-extend.vue' // 路径
//        ],
//        [
//            'name' => 'my-music', // 文件夹名称
//            'path' => 'components/my-music' // 目录路径
//        ],
    ],

    // uni-app 页面，多个逗号隔开
    'pages' => [
//        [
//            'path' => 'pages/goods/my_cart',
//            'style' => [
//                'navigationBarTitleText' => '新购物车'
//            ]
//        ],
//        [
//            'path' => 'pages_promotion/index/index',
//            'style' => [
//                'navigationBarTitleText' => '营销活动中心'
//            ]
//        ],
    ],

    // 模板信息，格式：'title' => '模板名称', 'name' => '模板标识', 'cover' => '模板封面图', 'preview' => '模板预览图', 'desc' => '模板描述'
    'info' => [
        'title' => '官方模板一', // 模板名称
        'name' => 'official_default_round', // 模板标识
        'cover' => 'addon/diy_default1/shop/view/public/img/cover.png', // 模板封面图
        'preview' => 'addon/diy_default1/shop/view/public/img/preview.png', // 模板预览图
        'desc' => '该模板以简约为主，搭配新型元素使得商城简约而不失时尚，在首页尽可能的将商城的优惠力度最大程度体现出来，可以优惠券形式展现，广告位形式展现，图文导航展现、公告形式展示等等，您想要的体现形式应有尽有，适合大部分商城进行运营。', // 模板描述
    ],

    // 主题风格配色，格式可以自由定义扩展，【在uni-app中通过：this.themeStyle... 获取定义的颜色字段，例如：this.themeStyle.main_color】
    'theme' => [
//        [
//            'title' => '中国红',
//            'name' => 'colorful',
//            'preview' => [
//                'public/static/img/diy_view/style/decorate-default-1.jpg',
//                'public/static/img/diy_view/style/decorate-default-2.jpg',
//                'public/static/img/diy_view/style/decorate-default-3.jpg',
//            ],
//            'color_img' => 'public/static/img/diy_view/style/default.png', // 配色图
//            'main_color' => '#F4391c',
//            'aux_color' => '#F7B500',
//            'bg_color' => '#FF4646',//主题背景
//            'bg_color_shallow' => '#FF4646',//主题背景渐变浅色
//            'promotion_color' => '#FF4646',//活动背景
//            'promotion_aux_color' => '#F7B500',//活动背景辅色
//            'main_color_shallow' => '#FFF4F4',//淡背景
//            'price_color' => 'rgb(252,82,39)',//价格颜色
//            'btn_text_color' => '#FFFFFF',//按钮文字颜色
//            'goods_detail' => [
//                'goods_price' => 'rgb(252,82,39,1)',//价格
//                'promotion_tag' => '#FF4646',
//                'goods_card_bg' => '#201A18',//会员卡背景
//                'goods_card_bg_shallow' => '#7C7878',//会员卡背景浅色
//                'goods_card_color' => '#FFD792',
//                'goods_coupon' => '#FC5227',
//                'goods_cart_num_corner' => '#FC5227',//购物车数量角标
//                'goods_btn_color' => '#FF4646',//按钮颜色
//                'goods_btn_color_shallow' => '#F7B500',//副按钮颜色
//            ],
//            'super_member' => [
//                'super_member_start_bg' => '#7c7878',
//                'super_member_end_bg' => '#201a18',
//                'super_member_start_text_color' => '#FFDBA6',
//                'super_member_end_text_color' => '#FFEBCA',
//            ],
//            'giftcard' => [
//                'giftcard_promotion_color' => '#FF3369',//活动背景
//                'giftcard_promotion_aux_color' => '#F7B500',//活动辅色
//            ],
//        ],
    ],

    // 自定义页面数据，格式：[ 'title' => '页面名称', 'name' => "页面标识", 'value' => [页面数据，json格式] ]
    'data' => [
        [
            'title' => '官方模板一',
            'name' => "DIY_VIEW_INDEX",
            'value' => [
                "global" => [
                    "title" => "官方模板一",
                    "pageBgColor" => "#F6F9FF",
                    "topNavColor" => "#FFFFFF",
                    "topNavBg" => false,
                    "navBarSwitch" => true,
                    "textNavColor" => "#333333",
                    "topNavImg" => "",
                    "moreLink" => [
                        "name" => ""
                    ],
                    "openBottomNav" => true,
                    "navStyle" => 1,
                    "textImgPosLink" => "center",
                    "mpCollect" => false,
                    "popWindow" => [
                        "imageUrl" => "",
                        "count" => -1,
                        "show" => 0,
                        "link" => [
                            "name" => ""
                        ],
                        "imgWidth" => "",
                        "imgHeight" => ""
                    ],
                    "bgUrl" => 'addon/diy_default1/shop/view/public/img/bg.png',
                    "imgWidth" => "2250",
                    "imgHeight" => "1110",
                    "template" => [
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 12
                        ]
                    ],
                ],
                "value" => [
                    [
                        "id" => "5wtw72w1wj80",
                        'addonName' => '',
                        "componentName" => "Search",
                        "componentTitle" => "搜索框",
                        "isDelete" => 0,
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 10,
                            "bottom" => 10,
                            "both" => 12
                        ],
                        "title" => "请输入搜索关键词",
                        "textAlign" => "left",
                        "borderType" => 2,
                        "searchImg" => "",
                        "searchStyle" => 1,
                        "searchLink" => [
                            "name" => ""
                        ],
                        "pageBgColor" => "#FFFFFF",
                        "textColor" => "#303133",
                        "componentBgColor" => "",
                        "elementBgColor" => "#F6F9FF",
                        "iconType" => "img",
                        "icon" => "",
                        "style" => [
                            "fontSize" => "60",
                            "iconBgColor" => [],
                            "iconBgColorDeg" => 0,
                            "iconBgImg" => "",
                            "bgRadius" => 0,
                            "iconColor" => [
                                "#000000"
                            ],
                            "iconColorDeg" => 0
                        ],
                        "imageUrl" => "",
                        "positionWay" => "static"
                    ],
                    [
                        "id" => "2o7za2qmi900",
                        "list" => [
                            [
                                "link" => [
                                    "name" => ""
                                ],
                                "imageUrl" => 'addon/diy_default1/shop/view/public/img/banner.png',
                                "imgWidth" => "750",
                                "imgHeight" => "320",
                                "id" => "1iy3xvq2ngf40"
                            ]
                        ],
                        "indicatorIsShow" => true,
                        "indicatorColor" => "#ffffff",
                        "carouselStyle" => "circle",
                        "indicatorLocation" => "center",
                        'addonName' => '',
                        "componentName" => "ImageAds",
                        "componentTitle" => "图片广告",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 10,
                        "bottomAroundRadius" => 10,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 12,
                            "both" => 12
                        ]
                    ],
                    [
                        "id" => "113ohzka4n40",
                        "mode" => "graphic",
                        "type" => "img",
                        "showStyle" => "fixed",
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "rowCount" => 5,
                        "pageCount" => 2,
                        "carousel" => [
                            "type" => "circle",
                            "color" => "#FFFFFF"
                        ],
                        "imageSize" => 40,
                        "aroundRadius" => 25,
                        "font" => [
                            "size" => 14,
                            "weight" => 'normal',
                            "color" => "#303133"
                        ],
                        "list" => [
                            [
                                "title" => "团购",
                                "icon" => "icondiy icon-system-groupbuy-nav",
                                "imageUrl" => "",
                                "iconType" => "icon",
                                "style" => [
                                    "fontSize" => 50,
                                    "iconBgColor" => [
                                        "#FF9F3E",
                                        "#FF4116"
                                    ],
                                    "iconBgColorDeg" => 90,
                                    "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                                    "bgRadius" => 50,
                                    "iconColor" => [
                                        "#FFFFFF"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "ycafod7gfgg0"
                            ],
                            [
                                "title" => "拼团",
                                "icon" => "icondiy icon-system-pintuan-nav",
                                "imageUrl" => "",
                                "iconType" => "icon",
                                "style" => [
                                    "fontSize" => 50,
                                    "iconBgColor" => [
                                        "#58BCFF",
                                        "#1379FF"
                                    ],
                                    "iconBgColorDeg" => 90,
                                    "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                                    "bgRadius" => 50,
                                    "iconColor" => [
                                        "#FFFFFF"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "wnlf5ak6u8g0"
                            ],
                            [
                                "title" => "秒杀",
                                "icon" => "icondiy icon-system-seckill-time",
                                "imageUrl" => "",
                                "iconType" => "icon",
                                "style" => [
                                    "fontSize" => 50,
                                    "iconBgColor" => [
                                        "#FFCC26",
                                        "#FF9F29"
                                    ],
                                    "iconBgColorDeg" => 90,
                                    "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                                    "bgRadius" => 50,
                                    "iconColor" => [
                                        "#FFFFFF"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => true,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83288",
                                    "bgColorEnd" => "#FE3523"
                                ],
                                "id" => "lpg2grtvmxo0"
                            ],
                            [
                                "title" => " 积分",
                                "icon" => "icondiy icon-system-point-nav",
                                "imageUrl" => "",
                                "iconType" => "icon",
                                "style" => [
                                    "fontSize" => 50,
                                    "iconBgColor" => [
                                        "#02CC96",
                                        "#43EEC9"
                                    ],
                                    "iconBgColorDeg" => 90,
                                    "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                                    "bgRadius" => 50,
                                    "iconColor" => [
                                        "#FFFFFF"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "1jfs721gome8"
                            ],
                            [
                                "title" => "专题活动",
                                "icon" => "icondiy icon-system-topic-nav",
                                "imageUrl" => "",
                                "iconType" => "icon",
                                "style" => [
                                    "fontSize" => 50,
                                    "iconBgColor" => [
                                        "#BE79FF",
                                        "#7B00FF"
                                    ],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                                    "bgRadius" => 50,
                                    "iconColor" => [
                                        "#FFFFFF"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "1grejh3c8fwg0"
                            ],
                            [
                                "title" => "砍价",
                                "icon" => "icondiy icon-system-bargain-nav",
                                "imageUrl" => "",
                                "iconType" => "icon",
                                "style" => [
                                    "fontSize" => 50,
                                    "iconBgColor" => [
                                        "#5BBDFF",
                                        "#2E87FD"
                                    ],
                                    "iconBgColorDeg" => 90,
                                    "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                                    "bgRadius" => 50,
                                    "iconColor" => [
                                        "#FFFFFF"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "ycpsnfbaf800"
                            ],
                            [
                                "title" => "领券",
                                "icon" => "icondiy icon-system-get-coupon",
                                "imageUrl" => "",
                                "iconType" => "icon",
                                "style" => [
                                    "fontSize" => 50,
                                    "iconBgColor" => [
                                        "#BE79FF",
                                        "#7B00FF"
                                    ],
                                    "iconBgColorDeg" => 90,
                                    "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                                    "bgRadius" => 50,
                                    "iconColor" => [
                                        "#FFFFFF"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "COUPON_PREFECTURE",
                                    "title" => "优惠券专区",
                                    "wap_url" => "/pages_tool/goods/coupon",
                                    "parent" => "MARKETING_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "17dcs7xstz400"
                            ],
                            [
                                "title" => "文章",
                                "icon" => "icondiy icon-system-article-nav",
                                "imageUrl" => "",
                                "iconType" => "icon",
                                "style" => [
                                    "fontSize" => 50,
                                    "iconBgColor" => [
                                        "#FF8052",
                                        "#FF4830"
                                    ],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                                    "bgRadius" => 50,
                                    "iconColor" => [
                                        "#FFFFFF"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "SHOPPING_ARTICLE",
                                    "title" => "文章",
                                    "wap_url" => "/pages_tool/article/list",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "hg8450mb0hc0"
                            ],
                            [
                                "title" => "公告",
                                "icon" => "icondiy icon-system-notice-nav",
                                "imageUrl" => "",
                                "iconType" => "icon",
                                "style" => [
                                    "fontSize" => 50,
                                    "iconBgColor" => [
                                        "#FFCC26",
                                        "#FF9F29"
                                    ],
                                    "iconBgColorDeg" => 90,
                                    "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                                    "bgRadius" => 50,
                                    "iconColor" => [
                                        "#FFFFFF"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "SHOPPING_NOTICE",
                                    "title" => "公告",
                                    "wap_url" => "/pages_tool/notice/list",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "1cg964qu9f9c0"
                            ],
                            [
                                "title" => "帮助",
                                "icon" => "icondiy icon-system-help",
                                "imageUrl" => "",
                                "iconType" => "icon",
                                "style" => [
                                    "fontSize" => 50,
                                    "iconBgColor" => [
                                        "#02CC96",
                                        "#43EEC9"
                                    ],
                                    "iconBgColorDeg" => 90,
                                    "iconBgImg" => "public/static/ext/diyview/img/icon_bg/bg_06.png",
                                    "bgRadius" => 50,
                                    "iconColor" => [
                                        "#FFFFFF"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "SHOPPING_HELP",
                                    "title" => "帮助",
                                    "wap_url" => "/pages_tool/help/list",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "1v4budp7jav40"
                            ]
                        ],
                        'addonName' => '',
                        "componentName" => "GraphicNav",
                        "componentTitle" => "图文导航",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 10,
                        "bottomAroundRadius" => 10,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 12,
                            "both" => 12
                        ]
                    ],
                    [
                        "id" => "3tegcfvyijk0",
                        "list" => [
                            [
                                "link" => [
                                    "name" => ""
                                ],
                                "imageUrl" => 'addon/diy_default1/shop/view/public/img/mf_left.png',
                                "imgWidth" => "338",
                                "imgHeight" => "450",
                                "previewWidth" => 187.5,
                                "previewHeight" => "249.63px"
                            ],
                            [
                                "imageUrl" => 'addon/diy_default1/shop/view/public/img/mf_right1.png',
                                "link" => [
                                    "name" => ""
                                ],
                                "imgWidth" => "354",
                                "imgHeight" => "220",
                                "previewWidth" => 187.5,
                                "previewHeight" => "124.82px",
                            ],
                            [
                                "imageUrl" => 'addon/diy_default1/shop/view/public/img/mf_right2.png',
                                "imgWidth" => "354",
                                "imgHeight" => "220",
                                "previewWidth" => 187.5,
                                "previewHeight" => "124.82px",
                                "link" => [
                                    "name" => ""
                                ]
                            ]
                        ],
                        "mode" => "row1-lt-of2-rt",
                        "imageGap" => 10,
                        'addonName' => '',
                        "componentName" => "RubikCube",
                        "componentTitle" => "魔方",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 10,
                        "bottomAroundRadius" => 10,
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 12,
                            "both" => 12
                        ]
                    ],
                    [
                        "id" => "3acr0xjm1c80",
                        "style" => "style-16",
                        "subTitle" => [
                            "fontSize" => 14,
                            "text" => "超级优惠",
                            "isElementShow" => true,
                            "color" => "#FFFFFF",
                            "bgColor" => "#FF9F29",
                            "icon" => "icondiy icon-system-coupon",
                            "fontWeight" => 'bold'
                        ],
                        "link" => [
                            "name" => "COUPON_PREFECTURE",
                            "title" => "优惠券专区",
                            "wap_url" => "/pages_tool/goods/coupon",
                            "parent" => "MARKETING_LINK"
                        ],
                        "fontSize" => 16,
                        "styleName" => "风格16",
                        "fontWeight" => 'bold',
                        "more" => [
                            "text" => "",
                            "link" => [
                                "name" => "COUPON_PREFECTURE",
                                "title" => "优惠券专区",
                                "wap_url" => "/pages_tool/goods/coupon",
                                "parent" => "MARKETING_LINK"
                            ],
                            "isShow" => true,
                            "isElementShow" => true,
                            "color" => "#999999"
                        ],
                        "text" => "优惠专区",
                        'addonName' => '',
                        "componentName" => "Text",
                        "componentTitle" => "标题",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 10,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 12
                        ]
                    ],
                    [
                        "id" => "2parw5r2qq00",
                        "style" => "6",
                        "sources" => "initial",
                        "styleName" => "风格六",
                        "couponIds" => [],
                        "count" => 6,
                        "previewList" => [],
                        "nameColor" => "#303133",
                        "moneyColor" => "#FF0000",
                        "limitColor" => "#303133",
                        "btnStyle" => [
                            "textColor" => "#FFFFFF",
                            "bgColor" => "#303133",
                            "text" => "领取",
                            "aroundRadius" => 20,
                            "isBgColor" => true,
                            "isAroundRadius" => true
                        ],
                        "bgColor" => "",
                        "isName" => true,
                        "couponBgColor" => "#FFFFFF",
                        "couponBgUrl" => "",
                        "couponType" => "color",
                        "ifNeedBg" => true,
                        'addonName' => 'coupon',
                        "componentName" => "Coupon",
                        "componentTitle" => "优惠券",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementBgColor" => "",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 12
                        ]
                    ],
                    [
                        "id" => "68p4o1plca80",
                        "height" => 10,
                        'addonName' => '',
                        "componentName" => "HorzBlank",
                        "componentTitle" => "辅助空白",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 10,
                            "both" => 12
                        ]
                    ],
                    [
                        "id" => "29fhippqsrgg",
                        "list" => [
                            [
                                "link" => [
                                    "name" => ""
                                ],
                                "imageUrl" => 'addon/diy_default1/shop/view/public/img/gg.png',
                                "imgWidth" => "702",
                                "imgHeight" => "252",
                                "id" => "1z94aaav9klc0"
                            ]
                        ],
                        "indicatorIsShow" => true,
                        "indicatorColor" => "#ffffff",
                        "carouselStyle" => "circle",
                        "indicatorLocation" => "center",
                        'addonName' => '',
                        "componentName" => "ImageAds",
                        "componentTitle" => "图片广告",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 10,
                        "bottomAroundRadius" => 10,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 12,
                            "both" => 12
                        ]
                    ],
                    [
                        "id" => "i4xirbfy0m8",
                        "style" => "style-3",
                        "sources" => "initial",
                        "count" => 6,
                        "goodsId" => [],
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "nameLineMode" => "multiple",
                        "template" => "row1-of2",
                        "goodsMarginType" => "default",
                        "goodsMarginNum" => 6,
                        "btnStyle" => [
                            "fontWeight" => false,
                            "padding" => 0,
                            "cartEvent" => "detail",
                            "text" => "购买",
                            "textColor" => "#FFFFFF",
                            "theme" => "default",
                            "aroundRadius" => 25,
                            "control" => false,
                            "support" => false,
                            "bgColor" => "#FF6A00",
                            "style" => "button",
                            "iconDiy" => [
                                "iconType" => "icon",
                                "icon" => "",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ]
                            ]
                        ],
                        "categoryId" => 0,
                        "categoryName" => "请选择",
                        "sortWay" => "default",
                        "tag" => [
                            "text" => "隐藏",
                            "value" => "hidden"
                        ],
                        "imgAroundRadius" => 0,
                        "slideMode" => "scroll",
                        "goodsNameStyle" => [
                            "color" => "#303133",
                            "control" => true,
                            "fontWeight" => false
                        ],
                        "saleStyle" => [
                            "color" => "#999CA7",
                            "control" => true,
                            "support" => true
                        ],
                        "theme" => "default",
                        "priceStyle" => [
                            "mainColor" => "#FF6A00",
                            "mainControl" => true,
                            "lineColor" => "#999CA7",
                            "lineControl" => false,
                            "lineSupport" => false
                        ],
                        'addonName' => '',
                        "componentName" => "GoodsList",
                        "componentTitle" => "商品列表",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementBgColor" => "#FFFFFF",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 10,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 10,
                            "both" => 12
                        ]
                    ]
                ]
            ]
        ],
        [
            'title' => '商品分类',
            'name' => "DIY_VIEW_GOODS_CATEGORY",
            'value' => [
                "global" => [
                    "title" => "商品分类",
                    "pageBgColor" => "#FFFFFF",
                    "topNavColor" => "#FFFFFF",
                    "topNavBg" => false,
                    "navBarSwitch" => true,
                    "textNavColor" => "#333333",
                    "topNavImg" => "",
                    "moreLink" => [
                        "name" => ""
                    ],
                    "openBottomNav" => true,
                    "navStyle" => 1,
                    "textImgPosLink" => "left",
                    "mpCollect" => false,
                    "popWindow" => [
                        "imageUrl" => "",
                        "count" => -1,
                        "show" => 0,
                        "link" => [
                            "name" => ""
                        ],
                        "imgWidth" => "",
                        "imgHeight" => ""
                    ],
                    "bgUrl" => "",
                    "imgWidth" => "",
                    "imgHeight" => "",
                    "template" => [
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 0
                        ]
                    ]
                ],
                "value" => [
                    [
                        "level" => "2",
                        "template" => "2",
                        "quickBuy" => 1,
                        "search" => 1,
                        'addonName' => '',
                        "componentName" => "GoodsCategory",
                        "componentTitle" => "商品分类",
                        "isDelete" => 1,
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [],
                        "goodsLevel" => 1,
                        "loadType" => "part"
                    ]
                ]
            ]
        ],
        [
            'title' => '会员中心',
            'name' => "DIY_VIEW_MEMBER_INDEX",
            'value' => [
                "global" => [
                    "title" => "会员中心",
                    "pageBgColor" => "#F8F8F8",
                    "topNavColor" => "#FFFFFF",
                    "topNavBg" => true,
                    "navBarSwitch" => true,
                    "textNavColor" => "#333333",
                    "topNavImg" => "",
                    "moreLink" => [
                        "name" => ""
                    ],
                    "openBottomNav" => true,
                    "navStyle" => 1,
                    "textImgPosLink" => "center",
                    "mpCollect" => false,
                    "popWindow" => [
                        "imageUrl" => "",
                        "count" => -1,
                        "show" => 0,
                        "link" => [
                            "name" => ""
                        ],
                        "imgWidth" => "",
                        "imgHeight" => ""
                    ],
                    "bgUrl" => "",
                    "imgWidth" => "",
                    "imgHeight" => "",
                    "template" => [
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 0
                        ]
                    ]
                ],
                "value" => [
                    [
                        "style" => 4,
                        "theme" => "default",
                        "bgColorStart" => "#FF7230",
                        "bgColorEnd" => "#FF1544",
                        "gradientAngle" => "129",
                        "infoMargin" => 15,
                        "id" => "1tkaoxbhavj4",
                        'addonName' => '',
                        "componentName" => "MemberInfo",
                        "componentTitle" => "会员信息",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 0
                        ]
                    ],
                    [
                        "style" => "style-12",
                        "styleName" => "风格12",
                        "text" => "我的订单",
                        "link" => [
                            "name" => ""
                        ],
                        "fontSize" => 17,
                        "fontWeight" => 'bold',
                        "subTitle" => [
                            "fontSize" => 14,
                            "text" => "",
                            "isElementShow" => true,
                            "color" => "#999999",
                            "bgColor" => "#303133"
                        ],
                        "more" => [
                            "text" => "全部订单",
                            "link" => [
                                "name" => "ALL_ORDER",
                                "title" => "全部订单",
                                "wap_url" => "/pages/order/list",
                                "parent" => "MALL_LINK"
                            ],
                            "isShow" => true,
                            "isElementShow" => true,
                            "color" => "#999999"
                        ],
                        "id" => "2txcvx3d5u6",
                        'addonName' => '',
                        "componentName" => "Text",
                        "componentTitle" => "标题",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 9,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 15,
                            "bottom" => 0,
                            "both" => 15
                        ]
                    ],
                    [
                        "color" => "#EEEEEE",
                        "borderStyle" => "solid",
                        "id" => "3hsh2st470e0",
                        'addonName' => '',
                        "componentName" => "HorzLine",
                        "componentTitle" => "辅助线",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 20
                        ]
                    ],
                    [
                        "icon" => [
                            "waitPay" => [
                                "title" => "待付款",
                                "icon" => "icondiy icon-system-daifukuan2",
                                "style" => [
                                    "bgRadius" => 0,
                                    "fontSize" => 65,
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "iconColor" => [
                                        "#ffa3a3",
                                        "#FF4646"
                                    ],
                                    "iconColorDeg" => 0
                                ]
                            ],
                            "waitSend" => [
                                "title" => "待发货",
                                "icon" => "icondiy icon-system-daifahuo2",
                                "style" => [
                                    "bgRadius" => 0,
                                    "fontSize" => 65,
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "iconColor" => [
                                        "#ffa3a3",
                                        "#FF4646"
                                    ],
                                    "iconColorDeg" => 0
                                ]
                            ],
                            "waitConfirm" => [
                                "title" => "待收货",
                                "icon" => "icondiy icon-system-daishouhuo2",
                                "style" => [
                                    "bgRadius" => 0,
                                    "fontSize" => 65,
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "iconColor" => [
                                        "#ffa3a3",
                                        "#FF4646"
                                    ],
                                    "iconColorDeg" => 0
                                ]
                            ],
                            "waitUse" => [
                                "title" => "待使用",
                                "icon" => "icondiy icon-system-daishiyong2",
                                "style" => [
                                    "bgRadius" => 0,
                                    "fontSize" => 65,
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "iconColor" => [
                                        "#ffa3a3",
                                        "#FF4646"
                                    ],
                                    "iconColorDeg" => 0
                                ]
                            ],
                            "refunding" => [
                                "title" => "售后",
                                "icon" => "icondiy icon-system-shuhou2",
                                "style" => [
                                    "bgRadius" => 0,
                                    "fontSize" => 65,
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "iconColor" => [
                                        "#ffa3a3",
                                        "#FF4646"
                                    ],
                                    "iconColorDeg" => 0
                                ]
                            ]
                        ],
                        "style" => 1,
                        "id" => "51h05xpcanw0",
                        'addonName' => '',
                        "componentName" => "MemberMyOrder",
                        "componentTitle" => "我的订单",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 9,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 15
                        ]
                    ],
                    [
                        "style" => "style-12",
                        "styleName" => "风格12",
                        "text" => "常用工具",
                        "link" => [
                            "name" => ""
                        ],
                        "fontSize" => 17,
                        "fontWeight" => 'bold',
                        "subTitle" => [
                            "fontSize" => 14,
                            "text" => "",
                            "isElementShow" => true,
                            "color" => "#999999",
                            "bgColor" => "#303133"
                        ],
                        "more" => [
                            "text" => "",
                            "link" => [
                                "name" => ""
                            ],
                            "isShow" => 0,
                            "isElementShow" => true,
                            "color" => "#999999"
                        ],
                        "id" => "405rb6vv3rq0",
                        'addonName' => '',
                        "componentName" => "Text",
                        "componentTitle" => "标题",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 9,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 15,
                            "bottom" => 0,
                            "both" => 15
                        ]
                    ],
                    [
                        "mode" => "graphic",
                        "type" => "img",
                        "showStyle" => "fixed",
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "rowCount" => 4,
                        "pageCount" => 2,
                        "carousel" => [
                            "type" => "circle",
                            "color" => "#FFFFFF"
                        ],
                        "imageSize" => 30,
                        "aroundRadius" => 0,
                        "font" => [
                            "size" => 13,
                            "weight" => 'normal',
                            "color" => "#303133"
                        ],
                        "list" => [
                            [
                                "title" => "个人资料",
                                "imageUrl" => "public/uniapp/member/index/menu/default_person.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "MEMBER_INFO",
                                    "title" => "个人资料",
                                    "wap_url" => "/pages_tool/member/info",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "icon" => "",
                                "id" => "10rhv0x6phhc0"
                            ],
                            [
                                "title" => "收货地址",
                                "imageUrl" => "public/uniapp/member/index/menu/default_address.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "SHIPPING_ADDRESS",
                                    "title" => "收货地址",
                                    "wap_url" => "/pages_tool/member/address",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "icon" => "",
                                "id" => "1n8gycn6xqe80"
                            ],
                            [
                                "title" => "我的关注",
                                "imageUrl" => "public/uniapp/member/index/menu/default_like.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "ATTENTION",
                                    "title" => "我的关注",
                                    "wap_url" => "/pages_tool/member/collection",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "icon" => "",
                                "id" => "cnamoch6cvk0"
                            ],
                            [
                                "title" => "我的足迹",
                                "imageUrl" => "public/uniapp/member/index/menu/default_toot.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "FOOTPRINT",
                                    "title" => "我的足迹",
                                    "wap_url" => "/pages_tool/member/footprint",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "icon" => "",
                                "id" => "drf3hi3slo00"
                            ],
                            [
                                "title" => "账户列表",
                                "imageUrl" => "public/uniapp/member/index/menu/default_cash.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => "ACCOUNT",
                                    "title" => "账户列表",
                                    "wap_url" => "/pages_tool/member/account",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "1l4axfhbayqo0"
                            ],
                            [
                                "title" => "优惠券",
                                "imageUrl" => "public/uniapp/member/index/menu/default_discount.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => "COUPON",
                                    "title" => "优惠券",
                                    "wap_url" => "/pages_tool/member/coupon",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "1tnu0vihrnq80"
                            ],
                            [
                                "title" => "签到",
                                "imageUrl" => "public/uniapp/member/index/menu/default_sign.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => "SIGN_IN",
                                    "title" => "签到",
                                    "wap_url" => "/pages_tool/member/signin",
                                    "parent" => "MARKETING_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "hodjcxowf8g0"
                            ]
                        ],
                        "id" => "5ywbzsnigpw0",
                        'addonName' => '',
                        "componentName" => "GraphicNav",
                        "componentTitle" => "图文导航",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 9,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 15
                        ]
                    ]
                ]
            ]
        ]
    ]

];