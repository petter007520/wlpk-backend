<?php
// +----------------------------------------------------------------------
// | 平台端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'ADDON_POSTERTEMPLATE',
        'title' => '商品海报',
        'url' => 'postertemplate://shop/postertemplate/lists',
        'parent' => 'PROMOTION_TOOL',
        'is_show' => 1,
        'is_control' => 1,
        'is_icon' => 0,
        'picture' => 'addon/postertemplate/poster.jpg',
        'picture_select' => 'addon/postertemplate/poster_selected.jpg',
        'sort' => 100,
        'child_list' => [
            [
                'name' => 'ADDON_POSTERTEMPLATE_LIST',
                'title' => '海报列表',
                'url' => 'postertemplate://shop/postertemplate/lists',
                'is_show' => 1,
            ],
            [
                'name' => 'ADDON_POSTERTEMPLATE_ADD',
                'title' => '新增海报',
                'url' => 'postertemplate://shop/postertemplate/addpostertemplate',
                'is_show' => 0,
            ],
            [
                'name' => 'ADDON_POSTERTEMPLATE_EDIT',
                'title' => '编辑海报',
                'url' => 'postertemplate://shop/postertemplate/editpostertemplate',
                'is_show' => 0,
            ],
        ],

    ]

];
