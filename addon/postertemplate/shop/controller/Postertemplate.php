<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\postertemplate\shop\controller;

use addon\postertemplate\model\PosterTemplate as PosterTemplateModel;
use app\model\system\Site;
use app\model\upload\Upload;
use app\shop\controller\BaseShop;
use extend\Poster as PosterExtend;

/**
 * 海报模板 控制器
 */
class Postertemplate extends BaseShop
{
    /**
     * 海报模板列表
     * @return mixed
     */
    public function lists()
    {
        if (request()->isAjax()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $condition = [ [ 'site_id', '=', $this->site_id ] ];
            $condition[] = [ 'template_type', '=', 'goods' ];
            if (!empty($search_text)) {
                $condition[] = [ 'poster_name', 'like', '%' . $search_text . '%' ];
            }
            $poster_template_model = new PosterTemplateModel();
            $res = $poster_template_model->getPosterTemplatePageList($condition, $page_index, $page_size);
            return $res;
        } else {
            return $this->fetch('poster_template/lists');
        }
    }

    /**
     * 添加海报模板
     * @return mixed
     */
    public function addPosterTemplate()
    {
        $poster_template_model = new PosterTemplateModel();
        if (request()->isAjax()) {
            $default_template = PosterTemplateModel::DEFAULT_TEMPLATE;
            $add_data = [];
            $template_json = [];
            foreach ($default_template as $field => $field_value) {
                if ($field == 'template_json') {
                    foreach ($default_template[ 'template_json' ] as $json_field => $json_field_value) {
                        $template_json[ $json_field ] = input($json_field, $json_field_value);
                    }
                } else {
                    $add_data[ $field ] = input($field, $field_value);
                }
            }
            unset($add_data[ 'template_id' ]);
            $add_data[ 'site_id' ] = $this->site_id;
            $add_data[ 'template_json' ] = json_encode($template_json, true);

            return $poster_template_model->addPosterTemplate($add_data);
        } else {
            //模板信息
            $muban_id = input('muban_id', 0);
            $muban_info = $muban_info = $poster_template_model->getMubanInfo([ [ 'muban_id', '=', $muban_id ] ])[ 'data' ];
            if (!empty($muban_info)) {
                $template_info = $muban_info;
                $template_info[ 'template_json' ] = json_decode($template_info[ 'template_json' ], true);
                $template_info[ 'template_id' ] = 0;
            } else {
                $template_info = PosterTemplateModel::DEFAULT_TEMPLATE;
            }
            $template_info = array_merge($template_info, $template_info[ 'template_json' ]);
            unset($template_info[ 'template_json' ]);
            $this->assign('template_info', $template_info);

            //站点信息
            $site_model = new Site();
            $site_info = $site_model->getSiteInfo([ [ "site_id", "=", $this->site_id ] ], 'site_name,logo')[ 'data' ];
            $this->assign('site_data', $site_info);

            return $this->fetch('poster_template/add');
        }
    }

    /**
     * 添加海报模板
     * @return mixed
     */
    public function editPosterTemplate()
    {
        $template_id = input('template_id', '');
        $poster_template_model = new PosterTemplateModel();
        if (request()->isAjax()) {
            $default_template = PosterTemplateModel::DEFAULT_TEMPLATE;
            $edit_data = [];
            $template_json = [];
            foreach ($default_template as $field => $field_value) {
                if ($field == 'template_json') {
                    foreach ($default_template[ 'template_json' ] as $json_field => $json_field_value) {
                        $template_json[ $json_field ] = input($json_field, $json_field_value);
                    }
                } else {
                    $edit_data[ $field ] = input($field, $field_value);
                }
            }
            unset($edit_data[ 'template_id' ]);
            $edit_data[ 'site_id' ] = $this->site_id;
            $edit_data[ 'template_json' ] = json_encode($template_json, true);
            return $poster_data = $poster_template_model->editPosterTemplate($edit_data, [
                [ 'template_id', '=', $template_id ],
                [ 'site_id', '=', $this->site_id ]
            ]);
        } else {
            //模板信息
            $template_info = $poster_template_model->getPosterTemplateInfo([
                [ 'site_id', '=', $this->site_id ],
                [ 'template_id', '=', $template_id ],
            ], '*')[ 'data' ];
            $template_info = array_merge($template_info, json_decode($template_info[ 'template_json' ], true));
            if (empty($template_info)) $this->error('模板信息有误');
            unset($template_info[ 'template_json' ]);
            $this->assign('template_info', $template_info);

            //站点信息
            $site_model = new Site();
            $site_info = $site_model->getSiteInfo([ [ "site_id", "=", $this->site_id ] ], 'site_name,logo');
            $this->assign('site_data', $site_info[ 'data' ]);
            return $this->fetch('poster_template/add');
        }
    }

    /**
     * 获取海报模板预览
     * @return mixed
     */
    public function posterTemplateDetail()
    {
        $template_id = input('template_id', '');
        $condition = [
            [ 'template_id', '=', $template_id ],
            [ 'site_id', '=', $this->site_id ]
        ];
        $poster_template_model = new PosterTemplateModel();
        $poster_data = $poster_template_model->getPosterTemplateInfo($condition);
        if (empty($poster_data[ 'data' ])) {
            return $poster_template_model->error(null, '模板信息有误');
        }
        $poster_data[ 'data' ][ 'template_json' ] = json_decode($poster_data[ 'data' ][ 'template_json' ], true);

        $site_model = new Site();
        $site_info = $site_model->getSiteInfo([ [ "site_id", "=", $this->site_id ] ]);

        $poster_width = 720;
        $poster_height = 1280;
        $poster = new PosterExtend($poster_width, $poster_height);
        $ground = [
            [
                'action' => 'setBackground', // 设背景色
                'data' => [ 255, 255, 255 ]
            ],
        ];

        $fontRate = 0.725;  // 20px 等于 14.5磅，换算比率 1px = 0.725磅
        $option = [

            [
                'action' => 'imageText', // 写入店铺名称
                'data' => [
                    $site_info[ 'data' ][ 'site_name' ],
                    $poster_data[ 'data' ][ 'template_json' ][ 'store_name_font_size' ] * $fontRate * 2,
                    hex2rgb($poster_data[ 'data' ][ 'template_json' ][ 'store_name_color' ]),
                    $poster_data[ 'data' ][ 'template_json' ][ 'store_name_left' ] * 2,
                    ( $poster_data[ 'data' ][ 'template_json' ][ 'store_name_top' ] + $poster_data[ 'data' ][ 'template_json' ][ 'store_name_font_size' ] ) * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'store_name_width' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'store_name_height' ] * 2,
                    0,
                    $poster_data[ 'data' ][ 'template_json' ][ 'store_name_is_show' ]
                ]
            ],
            [
                'action' => 'imageCopy', // 店铺logo
                'data' => [
                    !empty($site_info[ 'data' ][ 'logo_square' ]) ? $site_info[ 'data' ][ 'logo_square' ] : getUrl() . '/app/shop/view/public/img/shop_logo.png',
                    $poster_data[ 'data' ][ 'template_json' ][ 'store_logo_left' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'store_logo_top' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'store_logo_width' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'store_logo_height' ] * 2,
                    'square',
                    0,
                    $poster_data[ 'data' ][ 'template_json' ][ 'store_logo_is_show' ]
                ]
            ],
            [
                'action' => 'imageCopy', // 写入商品图
                'data' => [
                    getUrl() . '/public/static/img/goods_template.png',
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_img_left' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_img_top' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_img_width' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_img_height' ] * 2,
                    !empty($poster_data[ 'data' ][ 'template_json' ][ 'goods_img_shape' ]) ? $poster_data[ 'data' ][ 'template_json' ][ 'goods_img_shape' ] : 'square',
                    0,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_img_is_show' ]
                ]
            ],
            [
                'action' => 'imageText', // 写入商品名称
                'data' => [
                    '商品名称-商品名称-商品名称',
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_name_font_size' ] * $fontRate * 2,
                    hex2rgb($poster_data[ 'data' ][ 'template_json' ][ 'goods_name_color' ]),
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_name_left' ] * 2,
                    ( $poster_data[ 'data' ][ 'template_json' ][ 'goods_name_top' ] + $poster_data[ 'data' ][ 'template_json' ][ 'goods_name_font_size' ] ) * 2 + 10,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_name_width' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_name_height' ] * 2,
                    0,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_name_is_show' ]
                ]
            ],
            [
                'action' => 'imageCopy', // 写入商品二维码
                'data' => [
                    getUrl() . '/public/static/img/caner_erweima.png',
                    $poster_data[ 'data' ][ 'qrcode_left' ] * 2,
                    $poster_data[ 'data' ][ 'qrcode_top' ] * 2,
                    $poster_data[ 'data' ][ 'qrcode_width' ] * 2,
                    $poster_data[ 'data' ][ 'qrcode_height' ] * 2,
                    'square',
                    0,
                    1
                ]
            ],
            [
                'action' => 'imageText', // 写入商品价格
                'data' => [
                    '¥' . '100.00',
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_price_font_size' ] * $fontRate * 2,
                    hex2rgb($poster_data[ 'data' ][ 'template_json' ][ 'goods_price_color' ]),
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_price_left' ] * 2,
                    ( $poster_data[ 'data' ][ 'template_json' ][ 'goods_price_top' ] + $poster_data[ 'data' ][ 'template_json' ][ 'goods_price_font_size' ] ) * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_price_width' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_price_height' ] * 2,
                    0,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_price_is_show' ]
                ]
            ],
            [
                'action' => 'imageText', // 写入商品划线价格
                'data' => [
                    '¥' . '199.00',
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_font_size' ] * $fontRate * 2,
                    hex2rgb($poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_color' ]),
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_left' ] * 2,
                    ( $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_top' ] + $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_font_size' ] ) * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_width' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_height' ] * 2,
                    0,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_is_show' ]
                ]
            ],
            [
                'action' => 'imageText', // 写入线
                'data' => [
                    '一一一一',
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_font_size' ] * $fontRate * 2,
                    hex2rgb($poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_color' ]),
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_left' ] * 2 - 5,
                    ( $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_top' ] + $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_font_size' ] ) * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_width' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_height' ] * 2,
                    0,
                    $poster_data[ 'data' ][ 'template_json' ][ 'goods_market_price_is_show' ]
                ]
            ],
        ];
        $member_option = [
            [
                'action' => 'imageCopy', // 写入用户头像
                'data' => [
                    getUrl() . '/public/static/img/default_img/head.png',
                    $poster_data[ 'data' ][ 'template_json' ][ 'headimg_left' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'headimg_top' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'headimg_width' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'headimg_height' ] * 2,
                    !empty($poster_data[ 'data' ][ 'template_json' ][ 'headimg_shape' ]) ? $poster_data[ 'data' ][ 'template_json' ][ 'headimg_shape' ] : 'square',
                    0,
                    $poster_data[ 'data' ][ 'template_json' ][ 'headimg_is_show' ]
                ]
            ],
            [
                'action' => 'imageText', // 写入分享人昵称
                'data' => [
                    '用户昵称',
                    $poster_data[ 'data' ][ 'template_json' ][ 'nickname_font_size' ] * $fontRate * 2,
                    hex2rgb($poster_data[ 'data' ][ 'template_json' ][ 'nickname_color' ]),
                    $poster_data[ 'data' ][ 'template_json' ][ 'nickname_left' ] * 2,
                    ( $poster_data[ 'data' ][ 'template_json' ][ 'nickname_top' ] + $poster_data[ 'data' ][ 'template_json' ][ 'nickname_font_size' ] ) * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'nickname_width' ] * 2,
                    $poster_data[ 'data' ][ 'template_json' ][ 'nickname_height' ] * 2,
                    0,
                    $poster_data[ 'data' ][ 'template_json' ][ 'nickname_is_show' ]
                ]
            ],
        ];
        if (!empty($poster_data[ 'data' ][ 'background' ])) {
            list($width, $height, $type, $attr) = getimagesize(img(trim($poster_data[ 'data' ][ 'background' ])));
            $height = 720 * $height / $width;
            $back_ground = [
                [
                    'action' => 'imageCopy', // 写入背景图
                    'data' => [
                        img($poster_data[ 'data' ][ 'background' ]),
                        0,
                        0,
                        720,
                        $height,
                        'square',
                        0,
                        1
                    ]
                ],
            ];
        } else {
            $back_ground = [
                [
                    'action' => 'setBackground', // 设背景色
                    'data' => [ 255, 255, 255 ]
                ],
            ];
        }

        $option = array_merge($ground, $back_ground, $option, $member_option);

        $option_res = $poster->create($option);
        if (is_array($option_res)) return $option_res;
        $pic_name = rand(10000, 99999);
        $res = $option_res->jpeg('upload/poster/goods', 'goods_' . $pic_name);
        if ($res[ 'code' ] == 0) {
            $upload = new Upload($this->site_id);
            $res = $upload->fileCloud($res[ 'data' ][ 'path' ]);
        }
        return $res;
    }

    /**
     * 删除海报模板
     * @return mixed
     */
    public function delPosterTemplate()
    {
        if (request()->isAjax()) {
            $template_ids = input('template_ids', '');
            $condition = [
                [ 'template_id', 'in', $template_ids ],
                [ 'site_id', '=', $this->site_id ],
            ];
            $poster_template_model = new PosterTemplateModel();
            $res = $poster_template_model->deletePosterTemplate($condition);
            return $res;
        }
    }

    /**
     * 编辑模板状态啊
     * @return array
     */
    public function editstatus()
    {
        if (request()->isAjax()) {
            $template_id = input('template_id', 0);
            $template_status = input('template_status', 0);
            $condition = [
                [ 'template_id', 'in', $template_id ],
                [ 'site_id', '=', $this->site_id ]
            ];
            $data = [ 'template_status' => $template_status ];
            $poster_template_model = new PosterTemplateModel();
            $res = $poster_template_model->editPosterTemplate($data, $condition);
            return $res;
        }
    }

}