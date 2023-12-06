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

class Login extends BaseApi
{

    /**
     * 登录方法
     */
    public function login()
    {
        if (empty($this->params[ "username" ])) return $this->response($this->error([], "商家账号不能为空!"));
        if (empty($this->params[ "password" ])) return $this->response($this->error([], "密码不可为空!"));

        $config_model = new ConfigModel();
        $config_info = $config_model->getCaptchaConfig();
        $config = $config_info[ 'data' ][ 'value' ];
        $shop_login = $config[ "shop_login" ] ?? 0;

        if ($shop_login == 1) {
            // 校验验证码
            $captcha = new Captcha();
            $check_res = $captcha->checkCaptcha();
            if ($check_res[ 'code' ] < 0) return $this->response($check_res);
        }

        // 登录
        $login = new UserModel();
        $res = $login->uniAppLogin($this->params[ 'username' ], $this->params[ "password" ], $this->app_module);

        //生成access_token
        if ($res[ 'code' ] >= 0) {
            $token = $this->createToken($res[ 'data' ]);
            return $this->response($this->success([ 'token' => $token, 'site_id' => $res[ 'data' ][ 'site_id' ] ]));
        }
        return $this->response($res);
    }

    /**
     * 修改密码
     * */
    public function modifyPassword()
    {
        if (empty($this->params[ "old_pass" ])) return $this->response($this->error([], "旧密码不能为空!"));
        if (empty($this->params[ "new_pass" ])) return $this->response($this->error([], "新密码不能为空!"));
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $user_model = new UserModel();
        $condition = [
            ['uid','=', $this->uid],
            ['password', '=', data_md5($this->params[ 'old_pass' ])]
        ];
        $res = $user_model->modifyAdminUserPassword($condition,  $this->params[ 'new_pass' ]);

        return $this->response($res);
    }
}