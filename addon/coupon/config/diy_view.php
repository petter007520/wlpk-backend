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
        [
            'name' => 'Coupon',
            'title' => '优惠券',
            'type' => 'PROMOTION',
            'value' => '{"style":1,"sources":"initial","styleName":"风格一","couponIds":[],"count":6,"previewList":[],"nameColor":"","moneyColor":"#FFFFFF","limitColor":"#FFFFFF","btnStyle":{"maxLen": 4,"textColor":"#FFFFFF","bgColor":"","text":"立即领取","aroundRadius":0,"isBgColor":false,"isAroundRadius":false},"isName":false,"couponBgColor":"","couponBgUrl":"","couponType":"img","ifNeedBg":true}',
            'sort' => '30000',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconyouhuiquan',
        ],
    ],

    // 自定义页面路径
    'link' => [
        [
            'name' => 'COUPON_LIST',
            'title' => '优惠券',
            'parent' => 'MARKETING_LINK',
            'wap_url' => '',
            'web_url' => '',
            'sort' => 0,
            'child_list' => [
                [
                    'name' => 'COUPON_PREFECTURE',
                    'title' => '优惠券专区',
                    'wap_url' => '/pages_tool/goods/coupon',
                    'web_url' => '',
                    'sort' => 0
                ]
            ]
        ],
    ],

    // 自定义图标库
    'icon_library' => [],

    // uni-app 组件，格式：[ 'name' => '组件名称/文件夹名称', 'path' => '文件路径/目录路径' ]，多个逗号隔开，自定义组件名称前缀必须是diy-，也可以引用第三方组件
    'component' => [],

    // uni-app 页面，多个逗号隔开
    'pages' => [],

    // 模板信息，格式：'title' => '模板名称', 'name' => '模板标识', 'cover' => '模板封面图', 'preview' => '模板预览图', 'desc' => '模板描述'
    'info' => [],

    // 主题风格配色，格式可以自由定义扩展，【在uni-app中通过：this.themeStyle... 获取定义的颜色字段，例如：this.themeStyle.main_color】
    'theme' => [],

    // 自定义页面数据，格式：[ 'title' => '页面名称', 'name' => "页面标识", 'value' => [页面数据，json格式] ]
    'data' => []
];