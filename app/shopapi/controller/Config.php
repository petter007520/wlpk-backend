<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shopapi\controller;

use app\model\web\Config as ConfigModel;

class Config extends BaseApi
{
    /**
     * 详情信息
     */
    public function defaultimg()
    {
        $upload_config_model = new ConfigModel();
        $res = $upload_config_model->getDefaultImg($this->site_id);
        if (!empty($res[ 'data' ][ 'value' ])) {
            return $this->response($this->success($res[ 'data' ][ 'value' ]));
        } else {
            return $this->response($this->error());
        }
    }

    /**
     * 版权信息
     */
    public function copyright()
    {
        $config_model = new ConfigModel();
        $res = $config_model->getCopyright();
        return $this->response($this->success($res[ 'data' ][ 'value' ]));
    }

    /**
     * 查询验证码设置
     * @return false|string
     */
    public function captchaConfig()
    {
        $config_model = new ConfigModel();
        $config_info = $config_model->getCaptchaConfig();
        return $this->response($this->success($config_info[ 'data' ][ 'value' ]));
    }

}