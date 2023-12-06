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

use app\model\system\User as UserModel;
use app\model\web\Config as ConfigModel;

class Register extends BaseApi
{

    /**
     * 用户名密码注册
     */
    public function register()
    {
        $config_model = new ConfigModel();
        $config_info = $config_model->getCaptchaConfig();
        $config = $config_info[ 'data' ][ 'value' ];

        $register = new UserModel();

        if (empty($this->params[ "username" ])) return $this->response($this->error([], "用户名不可为空!"));
        if (empty($this->params[ "password" ])) return $this->response($this->error([], "密码不可为空!"));

        // 校验验证码
        if ($config[ "shop_login" ] == 1) {
            $captcha = new Captcha();
            $check_res = $captcha->checkCaptcha();
            if ($check_res[ 'code' ] < 0) return $this->response($check_res);
        }

        $data[ 'username' ] = $this->params[ 'username' ];
        $data[ 'password' ] = $this->params[ 'password' ];
        $data[ 'app_module' ] = $this->app_module;
        $data[ 'site_id' ] = 0;

        $res = $register->addUser($data);

        //生成access_token
        if ($res[ 'code' ] >= 0) {
            $token = $this->createToken($res[ 'data' ]);
            return $this->response($this->success([ 'token' => $token, 'site_id' => $res[ 'data' ][ 'site_id' ] ]));
        }
        return $this->response($res);
    }

}