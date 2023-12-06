<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\manjian\api\controller;

use app\api\controller\BaseApi;
use addon\manjian\model\Manjian as ManjianModel;

/**
 * 满减
 */
class Manjian extends BaseApi
{

    /**
     * 信息
     * @param int $id
     * @return false|string
     */
    public function info($id = 0)
    {
        $goods_id = isset($this->params[ 'goods_id' ]) ? $this->params[ 'goods_id' ] : 0;
        if (!empty($id)) {
            $goods_id = $id;
        }
        if (empty($goods_id)) {
            return $this->response($this->error('', 'REQUEST_GOODS_ID'));
        }
        $manjian_model = new ManjianModel();
        $res = $manjian_model->getGoodsManjianInfo($goods_id, $this->site_id);
        return $this->response($res);
    }

}