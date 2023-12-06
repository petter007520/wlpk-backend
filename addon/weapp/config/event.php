<?php
// 事件定义文件
return [
    'bind' => [

    ],

    'listen' => [
        // 生成获取二维码
        'Qrcode'          => [
            'addon\weapp\event\Qrcode'
        ],
        // 开放数据解密
        'DecryptData'     => [
            'addon\weapp\event\DecryptData'
        ],
        // 获取手机号
        'PhoneNumber' => [
            'addon\weapp\event\PhoneNumber'
        ],
        // api配置变更
        'ApiConfigChange' => [
            'addon\weapp\event\ApiConfigChange'
        ],
        //电脑端网站部署配置
        'SiteDeployData' => [
            'addon\weapp\event\SiteDeployData'
        ]
    ],

    'subscribe' => [
    ],
];
