<?php
// +----------------------------------------------------------------------
// | 平台端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'MEMBER_CONSUME',
        'title' => '消费奖励',
        'url' => 'memberconsume://shop/config/index',
        'parent' => 'PROMOTION_CENTER',
        'is_show' => 1,
        'is_control' => 1,
        'is_icon' => 0,
        'picture' => '',
        'picture_select' => '',
        'sort' => 100,
        'child_list' => [
            [
                'name' => 'MEMBER_CONSUME_CONFIG',
                'title' => '奖励设置',
                'url' => 'memberconsume://shop/config/index',
                'is_show' => 1,
            ],
            [
                'name' => 'MEMBER_CONSUME_LIST',
                'title' => '奖励记录',
                'url' => 'memberconsume://shop/config/lists',
                'is_show' => 1,
            ],
        ]
    ],
];
