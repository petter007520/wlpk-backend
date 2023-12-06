<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\system;

use addon\postertemplate\model\PosterTemplate as PosterTemplateModel;
use addon\store\model\Config as StoreConfig;
use app\model\BaseModel;
use app\model\upload\Upload;
use extend\Poster as PosterExtend;

/**
 * 海报生成类
 */
class Poster extends BaseModel
{
    /**
     * 商品海报
     */
    public function goods($app_type, $page, $qrcode_param, $promotion_type, $site_id, $store_id = 0)
    {
        try {

            $goods_info = $this->getGoodsInfo($qrcode_param['goods_id'], $site_id, $store_id);
            if (empty($goods_info)) return $this->error('未获取到商品信息');

            $qrcode_info = $this->getGoodsQrcode($app_type, $page, $qrcode_param, $promotion_type, $site_id);

            if ($qrcode_info['code'] < 0) return $qrcode_info;
            //判断海报是否存在或停用
            $template_info = $this->getTemplateInfo($goods_info['template_id']);

            $site_model = new Site();
            $condition = array (
                [ "site_id", "=", $site_id ]
            );
            $site_info = $site_model->getSiteInfo($condition);

            if (!empty($qrcode_param['source_member'])) {
                $member_info = $this->getMemberInfo($qrcode_param['source_member']);
            }
            if(empty($goods_info['template_id']) || empty($template_info) || $template_info['template_status']==0){
//                $poster_width  = 740;
//                $poster_height = !empty($member_info) ? 1120 : 1000;

                $poster_width  = 720;
                $poster_height = 1280;

                $poster = new PosterExtend($poster_width, $poster_height);

                $option = [
                    [
                        'action' => 'setBackground', // 设背景色
                        'data'   => [255, 255, 255]
                    ],
                [
                        'action' => 'imageCopy', // 写入商品图
                        'data'   => [
                            explode(',', $goods_info['goods_image'])[0] ?? '',
                            20,
                            20,
                            680,
                            680,
                            'square',
                            true,
                            1
                        ]
                    ],
                    [
                        'action' => 'imageText', // 写入商品名称
                        'data'   => [
                            $goods_info['goods_name'],
                            22,
                            [0, 0, 0],
                            20,
                            !empty($member_info) ? 895 : 775,
                            490,
                            2,
                            true,
                            1
                        ]
                    ],
                    [
                        'action' => 'imageCopy', // 写入商品二维码
                        'data'   => [
                            $qrcode_info['data']['path'],
                            490,
                            !empty($member_info) ? 860 : 740,
                            210,
                            210,
                            'square',
                            0,
                            1
                        ]
                    ],
                    [
                        'action' => 'imageText', // 写入提示
                        'data'   => [
                            '长按扫码购买',
                            16,
                            [102, 102, 102],
                            535,
                            !empty($member_info) ? 1100 : 980,
                            490,
                            1,
                            1,
                            1
                        ]
                    ],
                    [
                        'action' => 'imageText', // 写入商品推广语
                        'data'   => [
                            $goods_info['introduction'],
                            18,
                            [102, 102, 102],
                            20,
                            !empty($member_info) ? 975 : 855,
                            490,
                            1,
                            1,
                            1
                        ]
                    ],
                    [
                        'action' => 'imageText', // 写入商品价格
                        'data'   => [
                            '¥' . $goods_info['discount_price'],
                            30,
                            [255, 0, 0],
                            20,
                            !empty($member_info) ? 1065 : 945,
                            490,
                            2,
                            true,
                            1
                        ]
                    ],
                ];
                if (!empty($member_info)) {
                    $member_option = [
                        [
                            'action' => 'imageCircularCopy', // 写入用户头像
                            'data'   => [
                                !empty($member_info['headimg']) ? $member_info['headimg'] : 'public/static/img/default_img/head.png',
                                20,
                                740,
                                100,
                                100
                            ]
                        ],
                        [
                            'action' => 'imageText', // 写入分享人昵称
                            'data'   => [
                                $member_info['nickname'],
                                22,
                                [10, 10, 10],
                                140,
                                790,
                                580,
                                1,
                                1,
                                1
                            ]
                        ],
                        [
                            'action' => 'imageText', // 写入分享人昵称
                            'data'   => [
                                '分享给你一个商品',
                                18,
                                [102, 102, 102],
                                140,
                                825,
                                580,
                                1,
                                1,
                                1
                            ]
                        ]
                    ];
                    $option        = array_merge($option, $member_option);
                }

            }else{
                $condition = [
                    ['template_id', '=', $goods_info['template_id']],
                    ['site_id', '=', $site_id]
                ];
                $poster_template_model = new PosterTemplateModel();
                $poster_data = $poster_template_model->getPosterTemplateInfo($condition);
                $poster_data['data']['template_json'] = json_decode($poster_data['data']['template_json'],true);
                $poster_width  = 720;
                $poster_height = 1280;
                $poster = new PosterExtend($poster_width, $poster_height);
                $fontRate = 0.725;  // 20px 等于 14.5磅，换算比率 1px = 0.725磅
                if ($goods_info['price'] ==0 ){
                    $line = '一一一';
                }else{
                    $line = '一一一一';
                }
                if(!empty($poster_data['data']['background'])){
                    $background = str_replace(" ", "", img($poster_data['data']['background']));
                    list($width,$height,$type,$attr)=getimagesize($background);

                    $height = 720*$height/$width;
                    $back_ground = [
                        'action' => 'imageCopy', // 写入背景图
                        'data'   => [
                            img($poster_data['data']['background']),
                            0,
                            0,
                            $poster_width,
                            $poster_height,
                            'square',
                            true,
                            1
                        ]
                    ];
                }else{
                    $back_ground = [
                        'action' => 'setBackground', // 设背景色
                        'data'   => [255, 255, 255]
                    ];
                }
                $ground = [[
                    'action' => 'setBackground', // 设背景色
                    'data'   => [255, 255, 255]
                ]];
                $option = [
                    $back_ground,
                    [
                        'action' => 'imageText', // 写入店铺名称
                        'data'   => [
                            $site_info['data']['site_name'],
                            $poster_data['data']['template_json']['store_name_font_size']*$fontRate*2,
                            hex2rgb($poster_data['data']['template_json']['store_name_color']),
                            $poster_data['data']['template_json']['store_name_left']*2,
                            ($poster_data['data']['template_json']['store_name_top']+$poster_data['data']['template_json']['store_name_font_size'])*2,
                            $poster_data['data']['template_json']['store_name_width']*2,
                            $poster_data['data']['template_json']['store_name_height']*2,
                            true,
                            $poster_data['data']['template_json']['store_name_is_show']
                        ]
                    ],
                    [
                        'action' => 'imageCopy', // 店铺logo
                        'data'   => [
                            !empty($site_info['data']['logo_square']) ? $site_info['data']['logo_square']: getUrl() . '/app/shop/view/public/img/shop_logo.png',
                            $poster_data['data']['template_json']['store_logo_left']*2,
                            $poster_data['data']['template_json']['store_logo_top']*2,
                            $poster_data['data']['template_json']['store_logo_width']*2,
                            $poster_data['data']['template_json']['store_logo_height']*2,
                            'square',
                            true,
                            $poster_data['data']['template_json']['store_logo_is_show']
                        ]
                    ],
                    [
                        'action' => 'imageCopy', // 写入商品图
                        'data'   => [
                            $goods_info['sku_image'],
                            $poster_data['data']['template_json']['goods_img_left']*2,
                            $poster_data['data']['template_json']['goods_img_top']*2,
                            $poster_data['data']['template_json']['goods_img_width']*2,
                            $poster_data['data']['template_json']['goods_img_height']*2,
                            !empty($poster_data['data']['template_json']['goods_img_shape'])?$poster_data['data']['template_json']['goods_img_shape']:'square',
                            0,
                            $poster_data['data']['template_json']['goods_img_is_show']
                        ]
                    ],
                    [
                        'action' => 'imageText', // 写入商品名称
                        'data'   => [
                            $goods_info['goods_name'],
                            $poster_data['data']['template_json']['goods_name_font_size']*$fontRate*2,
                            hex2rgb($poster_data['data']['template_json']['goods_name_color']),
                            $poster_data['data']['template_json']['goods_name_left']*2,
                            ($poster_data['data']['template_json']['goods_name_top']+$poster_data['data']['template_json']['goods_name_font_size'])*2+10,
                            $poster_data['data']['template_json']['goods_name_width']*2,
                            1,//文本行数  $poster_data['data']['template_json']['goods_name_height']*2,
                            true,
                            $poster_data['data']['template_json']['goods_name_is_show']
                        ]
                    ],
                    [
                        'action' => 'imageCopy', // 写入商品二维码
                        'data'   => [
                            $qrcode_info['data']['path'],
                            $poster_data['data']['qrcode_left']*2,
                            $poster_data['data']['qrcode_top']*2,
                            $poster_data['data']['qrcode_width']*2,
                            $poster_data['data']['qrcode_height']*2,
                            'square',
                            0,
                            1
                        ]
                    ],
                    [
                        'action' => 'imageText', // 写入商品价格
                        'data'   => [
                            '¥' . $goods_info['discount_price'],
                            $poster_data['data']['template_json']['goods_price_font_size']*$fontRate*2,
                            hex2rgb($poster_data['data']['template_json']['goods_price_color']),
                            $poster_data['data']['template_json']['goods_price_left']*2,
                            ($poster_data['data']['template_json']['goods_price_top']+$poster_data['data']['template_json']['goods_price_font_size'])*2,
                            $poster_data['data']['template_json']['goods_price_width']*2,
                            $poster_data['data']['template_json']['goods_price_height']*2,
                            true,
                            $poster_data['data']['template_json']['goods_price_is_show']
                        ]
                    ],
                ];

                if($goods_info['market_price'] > 0){
                    $market_price = [
                        [
                            'action' => 'imageText', // 写入商品划线价格
                            'data'   => [
                                '¥' . $goods_info['market_price'],
                                $poster_data['data']['template_json']['goods_market_price_font_size']*$fontRate*2,
                                hex2rgb($poster_data['data']['template_json']['goods_market_price_color']),
                                $poster_data['data']['template_json']['goods_market_price_left']*2,
                                ($poster_data['data']['template_json']['goods_market_price_top']+$poster_data['data']['template_json']['goods_market_price_font_size'])*2,
                                $poster_data['data']['template_json']['goods_market_price_width']*2,
                                $poster_data['data']['template_json']['goods_market_price_height']*2,
                                true,
                                $poster_data['data']['template_json']['goods_market_price_is_show']
                            ]
                        ],
                        [
                            'action' => 'imageText', // 写入线
                            'data'   => [
                                $line,
                                $poster_data['data']['template_json']['goods_market_price_font_size']*$fontRate*2,
                                hex2rgb($poster_data['data']['template_json']['goods_market_price_color']),
                                $poster_data['data']['template_json']['goods_market_price_left']*2-5,
                                ($poster_data['data']['template_json']['goods_market_price_top']+$poster_data['data']['template_json']['goods_market_price_font_size'])*2,
                                $poster_data['data']['template_json']['goods_market_price_width']*2,
                                $poster_data['data']['template_json']['goods_market_price_height']*2,
                                true,
                                $poster_data['data']['template_json']['goods_market_price_is_show']
                            ]
                        ],
                    ];
                    $option  = array_merge($option, $market_price);
                }

                if (!empty($member_info)) {
                    $member_option = [
                        [
                            'action' => 'imageCopy', // 写入用户头像
                            'data'   => [
                                !empty($member_info['headimg']) ? $member_info['headimg'] : 'public/static/img/default_img/head.png',
                                $poster_data['data']['template_json']['headimg_left']*2,
                                $poster_data['data']['template_json']['headimg_top']*2,
                                $poster_data['data']['template_json']['headimg_width']*2,
                                $poster_data['data']['template_json']['headimg_height']*2,
                                !empty($poster_data['data']['template_json']['headimg_shape'])?$poster_data['data']['template_json']['headimg_shape']:'square',
                                0,
                                $poster_data['data']['template_json']['headimg_is_show']
                            ]
                        ],
                        [
                            'action' => 'imageText', // 写入分享人昵称
                            'data'   => [
                                $member_info['nickname'],
                                $poster_data['data']['template_json']['nickname_font_size']*$fontRate*2,
                                hex2rgb($poster_data['data']['template_json']['nickname_color']),
                                $poster_data['data']['template_json']['nickname_left']*2,
                                ($poster_data['data']['template_json']['nickname_top']+$poster_data['data']['template_json']['nickname_font_size'])*2,
                                $poster_data['data']['template_json']['nickname_width']*2,
                                $poster_data['data']['template_json']['nickname_height']*2,
                                0,
                                $poster_data['data']['template_json']['nickname_is_show']
                            ]
                        ],
                    ];
                    $option  = array_merge($ground,$option, $member_option);
                }
            }
            $option_res = $poster->create($option);
            if (is_array($option_res)) return $option_res;

            $res = $option_res->jpeg('upload/poster/goods', 'goods_' . $promotion_type . '_' . $qrcode_param['goods_id'] . '_' . $qrcode_param['source_member'] . '_' .time().'_' . $app_type);
            if ($res['code'] == 0) {
                $upload = new Upload($site_id);
                $cloud_res = $upload->fileCloud($res['data']['path']);
                if ($cloud_res['code'] >= 0) {
                    return $this->success([ "path" => $cloud_res[ 'data' ]]);
                } else {
                    return $this->error();
                }
            }
            return $res;
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取用户信息
     * @param unknown $member_id
     */
    private function getMemberInfo($member_id)
    {
        $info = model('member')->getInfo(['member_id' => $member_id], 'nickname,headimg');
        return $info;
    }

    /**
     * 获取商品信息
     * @param unknown $sku_id
     */
    private function getGoodsInfo($goods_id, $site_id, $store_id)
    {
        $field = 'g.goods_id,g.goods_name,g.introduction,gs.price,gs.discount_price,g.goods_image,g.sku_id,gs.collect_num,g.template_id,g.market_price,gs.sku_image';
        $join = [
            ['goods_sku gs', 'gs.sku_id=g.sku_id', 'left']
        ];
        if (!empty($store_id)) {
            $store_config = (new StoreConfig())->getStoreBusinessConfig($site_id)[ 'data' ][ 'value' ];
            if ($store_config['store_business'] == 'store') {
                $join[] = [ 'store_goods_sku sgs', 'gs.sku_id = sgs.sku_id and sgs.store_id=' . $store_id, 'left' ];
                $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.price,sgs.price), gs.price) as price', $field);
                $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_pirce = 1,gs.discount_price,sgs.price), gs.discount_price) as discount_price', $field);
            }
        }
        $info = model('goods')->getInfo(['g.goods_id' => $goods_id], $field, 'g', $join);
        return $info;
    }

    /**
     * 获取商品二维码
     * @param unknown $app_type 请求类型
     * @param unknown $page uniapp页面路径
     * @param unknown $qrcode_param 二维码携带参数
     * @param string $promotion_type 活动类型 null为无活动
     */
    private function getGoodsQrcode($app_type, $page, $qrcode_param, $promotion_type, $site_id)
    {
        $res = event('Qrcode', [
            'site_id'     => $site_id,
            'app_type'    => $app_type,
            'type'        => 'create',
            'data'        => $qrcode_param,
            'page'        => $page,
            'qrcode_path' => 'upload/qrcode/goods',
            'qrcode_name' => 'goods_' . $promotion_type . '_' . $qrcode_param['goods_id'] . '_' . $qrcode_param['source_member'] . '_' . $site_id,
        ], true);
        return $res;
    }

    /**
     * 获取海报信息
     * @param unknown $template_id
     */
    private function getTemplateInfo($template_id)
    {
        $info = model('poster_template')->getInfo(['template_id' => $template_id], 'template_id,template_status');
        return $info;
    }

    /**
     * 分享图片
     * @param $page
     * @param $qrcode_param
     * @param $site_id
     * @return array|\extend\multitype|PosterExtend|string|string[]
     */
    public function shareImg($page, $qrcode_param, $site_id, $store_id = 0)
    {
        try {
            $goods_info = $this->getGoodsInfo($qrcode_param['goods_id'], $site_id, $store_id);
            if (empty($goods_info)) return $this->error('未获取到商品信息');

//            $file_path = 'upload/share_img/goods_'.$goods_info['goods_id'] .'/sku_'.$goods_info['sku_id'] .'.jpg';
//            if (file_exists($file_path)) return $this->success(['path' => $file_path]);

            $poster_width = 600;
            $poster_height = 480;

            $poster = new PosterExtend($poster_width, $poster_height);
            $option = [
                [
                    'action' => 'setBackground', // 设背景色
                    'data'   => [255, 255, 255]
                ],
                [
                    'action' => 'imageCopy', // 商品图
                    'data'   => [
                        $goods_info['sku_image'],
                        30,
                        50,
                        200,
                        200,
                        'square',
                        50,
                        1
                    ]
                ],
                [
                    'action' => 'imageText', // 写入商品名称
                    'data'   => [
                        $goods_info['goods_name'],
                        25,
                        [51, 51, 51],
                        250,
                        100,
                        330,
                        2,
                        false,
                        1
                    ]
                ],
                [
                    'action' => 'imageText', // 写入商品价格
                    'data'   => [
                        '¥',
                        15,
                        [255, 0, 0],
                        250,
                        230,
                        300,
                        2,
                        false,
                        1,
                        PUBLIC_PATH . 'static/font/custom.ttf'
                    ]
                ],
                [
                    'action' => 'imageText', // 写入商品价格
                    'data'   => [
                        $goods_info['discount_price'],
                        32,
                        [255, 0, 0],
                        265,
                        230,
                        300,
                        2,
                        false,
                        1,
                        PUBLIC_PATH . 'static/font/custom.ttf'
                    ]
                ],
                [
                    'action' => 'imageCopy', // 背景图
                    'data' => [
                        img('upload/share_img/bg/goods_1.png'),
                        0,
                        0,
                        600,
                        480,
                        'square',
                        0,
                        1
                    ]
                ],
            ];

            $option_res = $poster->create($option);
            if (is_array($option_res)) {
                return $option_res;
            }

            $res = $option_res->jpeg('upload/share_img/goods_'.$goods_info['goods_id'],
                'sku_'.$goods_info['sku_id']);
            return $res;
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 删除分享图片
     * @param  int $goods_id
     */
    public function clearShareImg(int $goods_id)
    {
        $dir = 'upload/share_img/goods_' . $goods_id;
        @deleteDir($dir);
    }
}