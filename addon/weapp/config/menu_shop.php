<?php
// +----------------------------------------------------------------------
// | 平台端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'WEAPP_ROOT',
        'title' => '微信小程序',
        'url' => 'weapp://shop/weapp/setting',
        'parent' => 'CHANNEL_ROOT',
        'picture_select' => '',
        'picture' => 'addon/weapp/shop/view/public/img/menu_icon/wechat_app_new.png',
        'picture_selected' => 'addon/weapp/shop/view/public/img/menu_icon/wechat_app_select.png',
        'is_show' => 1,
        'sort' => 4,
        'child_list' => [
            [
                'name' => 'WEAPP_ROOT_CONFIG',
                'title' => '概况',
                'url' => 'weapp://shop/weapp/setting',
                'is_show' => 1,
                'sort' => 1,
            ],
            [
                'name' => 'WEAPP_CONFIG',
                'title' => '基础配置',
                'url' => 'weapp://shop/weapp/config',
                'is_show' => 0,
                'sort' => 2,
            ],
            [
                'name' => 'WEAPP_PACKAGE',
                'title' => '小程序发布',
                'url' => 'weapp://shop/weapp/package',
                'is_show' => 0,
                'sort' => 3,
            ],
            [
                'name' => 'WEAPP_PACKAGE',
                'title' => '订阅消息',
                'url' => 'weapp://shop/message/config',
                'is_show' => 0,
                'sort' => 4,
                'chile_list' => []
            ],
            [
                'name' => 'WEAPP_PACKAGE_EDIT',
                'parent' => 'MESSAGE_LISTS',
                'title' => '编辑订阅消息',
                'url' => 'weapp://shop/message/edit',
                'is_show' => 0,
                'sort' => 1,
            ],
            [
                'name' => 'WEAPP_SHARE',
                'title' => '小程序分享',
                'url' => 'weapp://shop/weapp/share',
                'is_show' => 0,
                'sort' => 6
            ]
        ]
    ],

];
