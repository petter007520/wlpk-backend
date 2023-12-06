<?php
/**
 * Index.php
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 * @author : niuteam
 * @date : 2022.8.8
 * @version : v5.0.0.1
 */

namespace app\api\controller;

use app\model\member\Login as LoginModel;
use app\model\message\Message;
use app\model\member\Register as RegisterModel;
use Exception;
use think\facade\Cache;
use app\model\member\Config as ConfigModel;
use app\model\web\Config;
use think\facade\Session;

class Login extends BaseApi
{
    #can_receive_registergift  判断新人礼
    /**
     * 登录方法
     */
    public function login()
    {
        $config = new ConfigModel();
        $config_info = $config->getRegisterConfig($this->site_id, 'shop');

        if (strstr($config_info[ 'data' ][ 'value' ][ 'login' ], 'username') === false) return $this->response($this->error([], "用户名登录未开启!"));

//        $auth_info = Session::get("auth_info");
//        if (!empty($auth_info)) {
//            $this->params = array_merge($this->params, $auth_info);
//        }

        // 校验验证码
        $config_model = new Config();
        $info = $config_model->getCaptchaConfig();
        if ($info[ 'data' ][ 'value' ][ 'shop_reception_login' ] == 1) {
            $captcha = new Captcha();
            $check_res = $captcha->checkCaptcha();
            if ($check_res[ 'code' ] < 0) return $this->response($check_res);
        }

        // 登录
        $login = new LoginModel();
        if (empty($this->params[ "password" ]))
            return $this->response($this->error([], "密码不可为空!"));

        $res = $login->login($this->params);

        //生成access_token
        if ($res[ 'code' ] >= 0) {
            $token = $this->createToken($res[ 'data' ][ 'member_id' ]);
            return $this->response($this->success([ 'token' => $token, 'can_receive_registergift' => $res[ 'data' ][ 'can_receive_registergift' ] ]));
        }
        return $this->response($res);
    }

    /**
     * 第三方登录
     */
    public function auth()
    {
        $login = new LoginModel();
        $res = $login->authLogin($this->params);
        //生成access_token
        if ($res[ 'code' ] >= 0) {
            $token = $this->createToken($res[ 'data' ][ 'member_id' ]);
            $data = [
                'token' => $token,
                'can_receive_registergift' => $res[ 'data' ][ 'can_receive_registergift' ]
            ];
            if (isset($res[ 'data' ][ 'is_register' ])) $data[ 'is_register' ] = 1;
            return $this->response($this->success($data));
        }
        return $this->response($res);
    }

    /**
     * 授权登录仅登录
     * @return false|string
     */
    public function authOnlyLogin(){
        $login = new LoginModel();
        $res = $login->authOnlyLogin($this->params);
        //生成access_token
        if ($res[ 'code' ] >= 0) {
            $token = $this->createToken($res[ 'data' ][ 'member_id' ]);
            $data = [
                'token' => $token,
            ];
            return $this->response($this->success($data));
        }
        return $this->response($res);
    }

    /**
     * 检测openid是否存在
     */
    public function openidIsExits()
    {
        $login = new LoginModel();
        $res = $login->openidIsExits($this->params);
        return $this->response($res);
    }

    /**
     * 手机动态码登录
     */
    public function mobile()
    {
        $config = new ConfigModel();
        $config_info = $config->getRegisterConfig($this->site_id, 'shop');
        if (strstr($config_info[ 'data' ][ 'value' ][ 'login' ], 'mobile') === false) return $this->response($this->error([], "动态码登录未开启!"));

        $key = $this->params[ 'key' ];
        $verify_data = Cache::get($key);
        if (!empty($verify_data) && $verify_data[ "mobile" ] == $this->params[ "mobile" ] && $verify_data[ "code" ] == $this->params[ "code" ]) {

            $register = new RegisterModel();
            $exist = $register->mobileExist($this->params[ "mobile" ], $this->site_id);

            if ($exist) {
                $login = new LoginModel();
                $res = $login->mobileLogin($this->params);
                if ($res[ 'code' ] >= 0) {
                    $token = $this->createToken($res[ 'data' ][ 'member_id' ]);
                    $res = $this->success([ 'token' => $token, 'can_receive_registergift' => $res[ 'data' ][ 'can_receive_registergift' ] ]);
                }
            } else {
                $res = $this->error("", "该手机号未注册");
            }

        } else {
            $res = $this->error("", "手机动态码不正确");
        }
        return $this->response($res);

    }

    /**
     * 微信公众号登录 新增2021.06.18
     * captcha_id 验证码id
     * captcha_code 验证码
     * mobile 手机号码
     * code 手机验证码
     */
    public function wechatLogin()
    {
        //校验验证码
        $captcha = new Captcha();
        $check_res = $captcha->checkCaptcha();
        if ($check_res[ 'code' ] < 0) return $this->response($check_res);

        $auth_info = Session::get("auth_info");

        if (!empty($auth_info)) {
            $this->params = array_merge($this->params, $auth_info);
        }

        $key = $this->params[ 'key' ];
        $verify_data = Cache::get($key);
        //判断手机验证码
        if ($verify_data[ "mobile" ] == $this->params[ "mobile" ] && $verify_data[ "code" ] == $this->params[ "code" ]) {
            $register = new RegisterModel();
            $exist = $register->mobileExist($this->params[ "mobile" ], $this->site_id);

            if ($exist) {
                //手机号码存在绑定wx_openid并登录

                //绑定openid 如果该手机号有openid直接替换
                $member_id = $register->getMemberId($this->params[ "mobile" ], $this->site_id);
                $res = $register->wxopenidBind([ 'wx_openid' => $this->params[ 'wx_openid' ], 'member_id' => $member_id, 'site_id' => $this->site_id ]);

                if ($res[ 'code' ] >= 0) {
                    $login = new LoginModel();
                    $res = $login->mobileLogin($this->params);
                    if ($res[ 'code' ] >= 0) {
                        $token = $this->createToken($res[ 'data' ][ 'member_id' ]);
                        $res = $this->success([ 'token' => $token, 'can_receive_registergift' => $res[ 'data' ][ 'can_receive_registergift' ] ]);
                    }
                }

            } else {
                //获取存放的缓存推荐人id
                $source_member = Session::get('source_member') ?? 0;
                if ($source_member > 0) {
                    $this->params[ 'source_member' ] = $source_member;
                }
                //手机号码不存在注册账号
                $res = $register->mobileRegister($this->params);
                if ($res[ 'code' ] >= 0) {
                    $token = $this->createToken($res[ 'data' ]);
                    $res = $this->success([ 'token' => $token ]);
                }
            }
        } else {
            $res = $this->error("", "手机动态码不正确");
        }
        return $this->response($res);

    }

    /**
     * 获取手机号登录验证码
     * @throws Exception
     */
    public function mobileCode()
    {
        // 校验验证码
        $config_model = new Config();
        $info = $config_model->getCaptchaConfig();
        if ($info[ 'data' ][ 'value' ][ 'shop_reception_login' ] == 1) {
            $captcha = new Captcha();
            $check_res = $captcha->checkCaptcha(false);
            if ($check_res[ 'code' ] < 0) return $this->response($check_res);
        }

        $mobile = $this->params[ 'mobile' ];

        if (empty($mobile)) return $this->response($this->error([], "手机号不可为空!"));

        $register = new RegisterModel();
        $exist = $register->mobileExist($this->params[ "mobile" ], $this->site_id);
        if (!$exist) return $this->response($this->error([], "该手机号未注册!"));

        $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);// 生成4位随机数，左侧补0
        $message_model = new Message();
        $res = $message_model->sendMessage([ 'type' => 'code', "mobile" => $mobile, "site_id" => $this->site_id, "support_type" => [ 'sms' ], "code" => $code, "keywords" => "LOGIN_CODE" ]);
        if ($res[ "code" ] >= 0) {
            //将验证码存入缓存
            $key = 'login_mobile_code_' . md5(uniqid(null, true));
            Cache::tag("login_mobile_code")->set($key, [ 'mobile' => $mobile, 'code' => $code ], 600);
            return $this->response($this->success([ "key" => $key ]));
        } else {
            return $this->response($res);
        }
    }

    /**
     * 获取第三方首次扫码登录绑定/注册手机号码验证码      手机号码存不存在都可以发送 新增2021.06.18
     * captcha_id 验证码id
     * captcha_code 验证码
     * mobile 手机号码
     */
    public function getMobileCode()
    {
        // 校验验证码 start
        $captcha = new Captcha();
        $check_res = $captcha->checkCaptcha(false);
        if ($check_res[ 'code' ] < 0) return $this->response($check_res);
        // 校验验证码 end

        $mobile = $this->params[ 'mobile' ];
        if (empty($mobile)) return $this->response($this->error([], "手机号不可为空!"));


        $register = new RegisterModel();
        $exist = $register->mobileExist($this->params[ "mobile" ], $this->site_id);

        //判断该手机号码是否已绑定wx_openid
//        $opneid_exist    = $register->openidExist($this->params["mobile"], $this->site_id);
//        if ($opneid_exist) return $this->response($this->error([], "该手机号已绑定其他微信公众号!"));

        if ($exist) {
            $keywords = 'LOGIN_CODE';
        } else {
            $keywords = 'REGISTER_CODE';
        }

        $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);// 生成4位随机数，左侧补0
        $message_model = new Message();
        $res = $message_model->sendMessage([ 'type' => 'code', "mobile" => $mobile, "site_id" => $this->site_id, "support_type" => [ 'sms' ], "code" => $code, "keywords" => $keywords ]);
        if ($res[ "code" ] >= 0) {
//            if ($res["code"]) {
            //将验证码存入缓存
            $key = 'login_mobile_code_' . md5(uniqid(null, true));
            Cache::tag("login_mobile_code")->set($key, [ 'mobile' => $mobile, 'code' => $code ], 600);
            return $this->response($this->success([ "key" => $key ]));
//            return $this->response($this->success(["key" => $key,"code"=>$code]));
        } else {
            return $this->response($res);
        }
    }

    /**
     * 手机号授权登录
     */
    public function mobileAuth()
    {
        $decrypt_data = event('DecryptData', $this->params, true);
        if ($decrypt_data[ 'code' ] < 0) return $this->response($decrypt_data);

        $this->params[ 'mobile' ] = $decrypt_data[ 'data' ][ 'purePhoneNumber' ];

        $register = new RegisterModel();
        $exist = $register->mobileExist($this->params[ "mobile" ], $this->site_id);

        if ($exist) {
            $login = new LoginModel();
            $res = $login->mobileLogin($this->params);
            if ($res[ 'code' ] >= 0) {
                $token = $this->createToken($res[ 'data' ][ 'member_id' ]);
                $res = $this->success([ 'token' => $token ]);
            }
        } else {
            $res = $register->mobileRegister($this->params);
            if ($res[ 'code' ] >= 0) {
                $token = $this->createToken($res[ 'data' ]);
                $res = $this->success([ 'token' => $token ]);
            }
        }
        return $this->response($res);
    }

    /**
     * 验证token有效性
     */
    public function verifyToken()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        return $this->response($this->success());
    }


    /**
     * 检测登录
     * @return false|string
     */
    public function checkLogin()
    {
        $key = $this->params[ 'key' ];
        $cache = Cache::get('wechat_' . $key);

        if (!empty($cache)) {
            if (isset($cache[ 'openid' ]) && !empty($cache[ 'openid' ])) {
                $login = new LoginModel();
                $data = [
                    'wx_openid' => $cache[ 'openid' ],
                    'site_id' => $this->site_id
                ];
                $is_exits = $login->openidIsExits($data);
                if ($is_exits[ 'data' ]) {
                    // 存在即登录
                    $res = $login->authLogin($data);
                    //生成access_token
                    if ($res[ 'code' ] >= 0) {
                        $token = $this->createToken($res[ 'data' ][ 'member_id' ]);
//                        Session::set($this->params[ 'app_type' ] . "_token_" . $this->site_id, $token);
//                        Session::set($this->params[ 'app_type' ] . "_member_id_" . $this->site_id, $res[ 'data' ][ 'member_id' ]);
                        return $this->response($this->success([ 'token' => $token, 'can_receive_registergift' => $res[ 'data' ][ 'can_receive_registergift' ] ]));
                    }
                    return $this->response($res);
                } else {

                    // 将openid存入session
                    Session::set("auth_info", [
                        'wx_openid' => $cache[ 'openid' ],
                        'nickname' => $cache[ 'nickname' ],
                        'headimg' => $cache[ 'headimgurl' ]
                    ]);

                    $config = new ConfigModel();
                    $config_info = $config->getRegisterConfig($this->site_id, 'shop');

                    if ($config_info[ 'data' ][ 'value' ][ 'third_party' ] && !$config_info[ 'data' ][ 'value' ][ 'bind_mobile' ]) {

                        $data = [
                            'wx_openid' => $cache[ 'openid' ] ?? "",
                            'site_id' => $this->site_id,
                            'avatarUrl' => $cache[ 'headimgurl' ],
                            'nickName' => $cache[ 'nickname' ],
                            'wx_unionid' => $cache[ 'unionid' ],
                        ];
                        Cache::set('wechat_' . $key, null);
                        $res = $login->authLogin($data);

                        //生成access_token
                        if ($res[ 'code' ] >= 0) {
                            $token = $this->createToken($res[ 'data' ][ 'member_id' ]);
//                        Session::set($this->params[ 'app_type' ] . "_token_" . $this->site_id, $token);
//                        Session::set($this->params[ 'app_type' ] . "_member_id_" . $this->site_id, $res[ 'data' ][ 'member_id' ]);
                            return $this->response($this->success([ 'token' => $token, 'can_receive_registergift' => $res[ 'data' ][ 'can_receive_registergift' ] ]));
                        }
                    }

                    Cache::set('wechat_' . $key, null);
                    return $this->response($this->success());
                }
            } elseif (time() > $cache[ 'expire_time' ]) {
                Cache::set('wechat_' . $key, null);
                return $this->response($this->error('', '已失效'));
            } else {
                return $this->response($this->error('', 'no login'));
            }
        } else {
            return $this->response($this->error('', '已失效'));
        }
    }
}