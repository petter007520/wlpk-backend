<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\postertemplate\model;

use app\model\BaseModel;

/**
 * 海报模板
 */
class PosterTemplate extends BaseModel
{
    //默认模板数据
    const DEFAULT_TEMPLATE = [
        'template_id' => 0,
        'template_type' => 'goods',
        'poster_name' => '',
        'background' => '',
        //二维码
        'qrcode_type' => '',
        'qrcode_width' => 80,
        'qrcode_height' => 80,
        'qrcode_top' => 540,
        'qrcode_left' => 260,
        //json数据
        'template_json' => [
            //头像
            'headimg_is_show' => 1,
            'headimg_shape' => 'circle',
            'headimg_width' => 30,
            'headimg_height' => 30,
            'headimg_top' => 30,
            'headimg_left' => 20,
            //昵称
            'nickname_is_show' => 1,
            'nickname_color' => '#000000',
            'nickname_font_size' => 14,
            'nickname_width' => 150,
            'nickname_height' => 14,
            'nickname_top' => 36,
            'nickname_left' => 60,
            //商品图片
            'goods_img_is_show' => 1,
            'goods_img_top' => 233,
            'goods_img_left' => 19,
            'goods_img_width' => 300,
            'goods_img_height' => 300,
            //商品名称
            'goods_name_is_show' => 1,
            'goods_name_color' => '#000000',
            'goods_name_font_size' => 14,
            'goods_name_top' => 174,
            'goods_name_left' => 40,
            'goods_name_width' => 200,
            'goods_name_height' => 30,
            //商品销售价
            'goods_price_is_show' => 1,
            'goods_price_color' => '#FF4544',
            'goods_price_font_size' => 18,
            'goods_price_width' => 100,
            'goods_price_height' => 14,
            'goods_price_top' => 568,
            'goods_price_left' => 28,
            //商品划线价
            'goods_market_price_is_show' => 1,
            'goods_market_price_color' => '#000000',
            'goods_market_price_font_size' => 14,
            'goods_market_price_width' => 100,
            'goods_market_price_height' => 14,
            'goods_market_price_top' => 572,
            'goods_market_price_left' => 108,
            //店铺名称
            'store_name_is_show' => 1,
            'store_name_color' => '#000000',
            'store_name_font_size' => 14,
            'store_name_width' => 80,
            'store_name_height' => 20,
            'store_name_top' => 44,
            'store_name_left' => 270,
            //店铺logo
            'store_logo_is_show' => 1,
            'store_logo_width' => 60,
            'store_logo_height' => 25,
            'store_logo_top' => 21,
            'store_logo_left' => 270,
        ]
    ];

    /**
     * 添加海报模板
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $field
     * @param string $order
     * @return array
     */
    public function addPosterTemplate($data)
    {
        $res = model('poster_template')->add($data);
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 编辑海报模板
     * @param $data
     * @param $condition
     * @return array
     */
    public function editPosterTemplate($data, $condition)
    {
        $res = model('poster_template')->update($data, $condition);
        if ($res === false) {
            return $this->error('', 'SAVE_FAIL');
        }
        return $this->success($res);
    }

    /**
     * 删除海报模板
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $field
     * @param string $order
     * @return array
     */
    public function deletePosterTemplate($condition)
    {
        $res = model('poster_template')->delete($condition);
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 获取海报模板信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getPosterTemplateInfo($condition = [], $field = '*')
    {
        $info = model('poster_template')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取海报模板列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getPosterTemplateList($condition = [], $field = '*', $order = 'create_time desc')
    {
        $list = model('poster_template')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 获取海报模板分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getPosterTemplatePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $field = '*', $order = 'create_time desc')
    {
        $list = model('poster_template')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /*************************** 模板默认数据 ********************************/

    public function getMubanInfo($condition = [], $field = '*', $alias = 'a', $join = [])
    {
        $list = model('poster_muban')->getInfo($condition, $field, $alias, $join);
        return $this->success($list);
    }

    public function getMubanList($condition = [], $field = '*')
    {
        $list = model('poster_muban')->getList($condition, $field);
        return $this->success($list);
    }
}