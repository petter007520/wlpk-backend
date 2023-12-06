<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\event;


use app\model\web\Adv;
use app\model\web\AdvPosition;

/**
 * 增加默认广告位 广告图
 */
class AddSiteAdv
{
    private $adv_data = [
        [
            'ap_name' => 'PC端首页',
            'keyword' => 'NS_PC_INDEX',
            'ap_intro' => '',
            'ap_width' => '763',
            'ap_height' => '430',
            'default_content' => '',
            'ap_background_color' => '#FFFFFF',
            'type' => 1,
            'adv' => [
                [
                    'adv_title' => '广告一',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_index_carousel_1.png',
                    'background' => '#e7171f'
                ]
            ]
        ],
        [
            'ap_name' => 'PC端首页顶部',
            'keyword' => 'NS_PC_INDEX_TOP',
            'ap_intro' => '',
            'ap_width' => '1210',
            'ap_height' => '70',
            'default_content' => '',
            'ap_background_color' => '#FFFFFF',
            'type' => 1,
            'adv' => [
                [
                    'adv_title' => '广告一',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_index_top_carousel_1.png',
                    'background' => '#FF5726'
                ]
            ]
        ],
        [
            'ap_name' => 'PC端首页中部左侧',
            'keyword' => 'NS_PC_INDEX_MID_LEFT',
            'ap_intro' => '',
            'ap_width' => '291',
            'ap_height' => '372',
            'default_content' => '',
            'ap_background_color' => '#FFFFFF',
            'type' => 1,
            'adv' => [
                [
                    'adv_title' => '广告一',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_index_mid_left_1.png',
                    'background' => '#FFFFFF'
                ],
                [
                    'adv_title' => '广告二',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_index_mid_left_2.png',
                    'background' => '#FFFFFF'
                ]
            ]
        ],
        [
            'ap_name' => 'PC端首页中部右侧',
            'keyword' => 'NS_PC_INDEX_MID_RIGHT',
            'ap_intro' => '',
            'ap_width' => '291',
            'ap_height' => '180',
            'default_content' => '',
            'ap_background_color' => '#FFFFFF',
            'type' => 1,
            'adv' => [
                [
                    'adv_title' => '广告一',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_index_mid_right_1.png',
                    'background' => '#FFFFFF'
                ],
                [
                    'adv_title' => '广告二',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_index_mid_right_2.png',
                    'background' => '#FFFFFF'
                ],
                [
                    'adv_title' => '广告三',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_index_mid_right_3.png',
                    'background' => '#FFFFFF'
                ],
                [
                    'adv_title' => '广告四',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_index_mid_right_4.png',
                    'background' => '#FFFFFF'
                ]
            ]
        ],
        [
            'ap_name' => 'PC端领券中心',
            'keyword' => 'NS_PC_COUPON',
            'ap_intro' => '',
            'ap_width' => '810',
            'ap_height' => '406',
            'default_content' => '',
            'ap_background_color' => '#FFFFFF',
            'type' => 1,
            'adv' => [
                [
                    'adv_title' => '广告一',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_coupon_carousel_1.png',
                    'background' => '#FFFFFF'
                ]
            ]
        ],
        [
            'ap_name' => 'PC端团购专区',
            'keyword' => 'NS_PC_GROUPBUY',
            'ap_intro' => '',
            'ap_width' => '1200',
            'ap_height' => '440',
            'default_content' => '',
            'ap_background_color' => '#FFFFFF',
            'type' => 1,
            'adv' => [
                [
                    'adv_title' => '广告一',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_groupbuy_carousel_1.png',
                    'background' => '#FFFFFF'
                ]
            ]
        ],
        [
            'ap_name' => 'PC端秒杀专区',
            'keyword' => 'NS_PC_SECKILL',
            'ap_intro' => '',
            'ap_width' => '1200',
            'ap_height' => '440',
            'default_content' => '',
            'ap_background_color' => '#FFFFFF',
            'type' => 1,
            'adv' => [
                [
                    'adv_title' => '广告一',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_seckill_carousel_1.png',
                    'background' => '#FFFFFF'
                ]
            ]
        ],
        [
            'ap_name' => 'PC端登录',
            'keyword' => 'NS_PC_LOGIN',
            'ap_intro' => '',
            'ap_width' => '800',
            'ap_height' => '460',
            'default_content' => '',
            'ap_background_color' => '#FFFFFF',
            'type' => 1,
            'adv' => [
                [
                    'adv_title' => '广告一',
                    'adv_url' => '',
                    'adv_image' => 'public/static/img/pc/gg_pc_login_carousel_1.png',
                    'background' => '#F53E45'
                ]
            ]
        ]
    ];

    public function handle($param)
    {
        if (!empty($param[ 'site_id' ])) {
            $adv_position_model = new AdvPosition();
            $adv_model = new Adv();

            foreach ($this->adv_data as $k => $v) {
                $v[ 'site_id' ] = $param[ 'site_id' ];
                $adv_data = $v[ 'adv' ];
                unset($v[ 'adv' ]);
                $res_adv_position = $adv_position_model->addAdvPosition($v);
                $ap_id = $res_adv_position[ 'data' ];
                if (!empty($ap_id) && !empty($adv_data)) {
                    foreach ($adv_data as $ck => $cv) {
                        $cv[ 'site_id' ] = $param[ 'site_id' ];
                        $cv[ 'ap_id' ] = $ap_id;
                        $adv_model->addAdv($cv);
                    }
                }
            }
        }
    }

}