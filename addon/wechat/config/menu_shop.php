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
    [
        'name' => 'WECHAT_ROOT',
        'title' => '微信公众号',
        'url' => 'wechat://shop/wechat/setting',
        'parent' => 'CHANNEL_ROOT',
        'picture_select' => '',
        'picture' => 'addon/wechat/shop/view/public/img/menu_icon/wechat_icon_new.png',
        'picture_selected' => 'addon/wechat/shop/view/public/img/menu_icon/wechat_icon_select.png',
        'is_show' => 1,
        'sort' => 3,
        'child_list' => [
            [
                'name' => 'WCHAT_ROOT_CONFIG',
                'title' => '概况',
                'url' => 'wechat://shop/wechat/setting',
                'is_show' => 1,
                'sort' => 1,
            ],
            [
                'name' => 'WCHAT_CONFIG',
                'title' => '基础配置',
                'url' => 'wechat://shop/wechat/config',
                'is_show' => 0,
                'sort' => 2,
            ],
            [
                'name' => 'WECHAT_MENU',
                'title' => '菜单管理',
                'url' => 'wechat://shop/menu/menu',
                'is_show' => 0,
                'sort' => 3,
            ],
            [
                'name' => 'WECHAT_MENU_EDIT',
                'title' => '编辑微信菜单',
                'url' => 'wechat://shop/menu/edit',
                'is_show' => 0,
            ],
            [
                'name' => 'WECHAT_MATERIAL',
                'title' => '消息素材',
                'url' => 'wechat://shop/material/lists',
                'is_show' => 0,
                'sort' => 4,
            ],
            [
                'name' => 'WECHAT_MATERIAL_ADD_TEXT',
                'title' => '添加文本素材',
                'url' => 'wechat://shop/material/addTextMaterial',
                'is_show' => 0,
            ],
            [
                'name' => 'WECHAT_MATERIAL_ADD',
                'title' => '添加图文',
                'url' => 'wechat://shop/material/add',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_MATERIAL_EDIT',
                'title' => '修改图文',
                'url' => 'wechat://shop/material/edit',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_MATERIAL_DELETE',
                'title' => '删除图文',
                'url' => 'wechat://shop/material/delete',
                'is_show' => 0
            ],

            [
                'name' => 'WECHAT_MATERIAL_ADD_TEXT',
                'title' => '添加文本',
                'url' => 'wechat://shop/material/addtextmaterial',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_MATERIAL_EDIT_TEXT',
                'title' => '修改文本',
                'url' => 'wechat://shop/material/edittextmaterial',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_MATERIAL_DELETE_TEXT',
                'title' => '删除文本',
                'url' => 'wechat://shop/material/textmaterial',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_QRCODE',
                'title' => '推广二维码管理',
                'url' => 'wechat://shop/wechat/qrcode',
                'is_show' => 0,
            ],
            [
                'name' => 'WECHAT_QRCODE_ADD',
                'title' => '推广二维码添加',
                'url' => 'wechat://shop/wechat/addqrcode',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_QRCODE_EDIT',
                'title' => '推广二维码编辑',
                'url' => 'wechat://shop/wechat/editqrcode',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_QRCODE_DELETE',
                'title' => '推广二维码删除',
                'url' => 'wechat://shop/wechat/deleteqrcode',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_QRCODE_DEFAULT',
                'title' => '设置默认推广二维码',
                'url' => 'wechat://shop/wechat/qrcodeDefault',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_SHARE',
                'title' => '分享内容',
                'url' => 'wechat://shop/wechat/share',
                'is_show' => 0,
                'sort' => 5,
            ],
            [
                'name' => 'WECHAT_REPLAY_INDEX',
                'title' => '回复设置',
                'url' => 'wechat://shop/replay/replay',
                'is_show' => 0,
                'sort' => 6,
            ],
            [
                'name' => 'WECHAT_REPLAY_KEYS',
                'title' => '关键词自动回复',
                'url' => 'wechat://shop/replay/replay',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_REPLAY_KEYS',
                'title' => '默认自动回复',
                'url' => 'wechat://shop/replay/default',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_REPLAY_FOLLOW',
                'title' => '关注后自动回复',
                'url' => 'wechat://shop/replay/follow',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_MESSAGE_CONFIG',
                'title' => '模板消息',
                'url' => 'wechat://shop/message/config',
                'is_show' => 0,
                'sort' => 8
            ],
            [
                'name' => 'WECHAT_MESSAGE_EDIT',
                'parent' => 'MESSAGE_LISTS',
                'title' => '编辑消息模板',
                'url' => 'wechat://shop/message/edit',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_MESSAGE_CONFIG',
                'title' => '是否需跳转到小程序',
                'url' => 'wechat://shop/message/messageConfig',
                'is_show' => 0
            ],
            [
                'name' => 'WECHAT_MESSAGE_SET_WECHAT_STATUS',
                'title' => '设置微信模板消息状态',
                'url' => 'wechat://shop/message/setWechatStatus',
                'is_show' => 0
            ],
        ]
    ],

];