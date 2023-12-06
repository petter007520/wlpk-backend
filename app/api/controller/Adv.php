<?php

/**
 * Adv.php
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @author : niuteam
 * @date : 2022.8.8
 * @version : v5.0.0.1
 */

namespace app\api\controller;

use app\model\web\AdvPosition as AdvPositionModel;
use app\model\web\Adv as AdvModel;

class Adv extends BaseApi
{

    /**
     * 详情信息
     */
    public function detail()
    {
        $keyword = isset($this->params[ 'keyword' ]) ? $this->params[ 'keyword' ] : '';
        //广告位
        $adv_position_model = new AdvPositionModel();
        $adv_position_info = $adv_position_model->getAdvPositionInfo([
            [ 'keyword', '=', $keyword ],
            [ 'site_id', '=', $this->site_id ],
            [ 'state', '=', 1 ],
        ])[ 'data' ];

        //广告图
        $adv_list = [];
        if (!empty($adv_position_info)) {
            $adv_model = new AdvModel();
            $adv_list = $adv_model->getAdvList(
                [
                    [ 'ap_id', '=', $adv_position_info[ 'ap_id' ] ],
                    [ 'state', '=', 1 ],
                ],
                $field = 'adv_id, adv_title, ap_id, adv_url, adv_image, slide_sort, price, background'
            )[ 'data' ];
        }

        $res = [
            'adv_position' => $adv_position_info,
            'adv_list' => $adv_list,
        ];

        return $this->response($this->success($res));
    }
}
