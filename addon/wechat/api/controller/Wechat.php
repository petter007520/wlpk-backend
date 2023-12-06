<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechat\api\controller;

use addon\wechat\model\Config as ConfigModel;
use addon\wechat\model\Fans;
use addon\wechat\model\Wechat as WechatModel;
use app\api\controller\BaseApi;
use app\model\member\Member as MemberModel;
use think\facade\Cache;

class Wechat extends BaseApi
{

    /**
     * 获取openid
     */
    public function authCodeToOpenid()
    {
        $weapp_model = new WechatModel($this->site_id);
        $res = $weapp_model->getAuthByCode($this->params);
        return $this->response($res);
    }

    /**
     * 获取网页授权code
     */
    public function authcode()
    {
        $redirect_url = $this->params[ 'redirect_url' ] ?? '';
        $scopes = $this->params[ 'scopes' ] ?? 'snsapi_base';
        $weapp_model = new WechatModel($this->site_id);
        $res = $weapp_model->getAuthCode($redirect_url, $scopes);
        return $this->response($res);
    }

    /**
     * 获取jssdk配置
     */
    public function jssdkConfig()
    {
        $url = $this->params[ 'url' ] ?? '';
        $weapp_model = new WechatModel($this->site_id);
        $res = $weapp_model->getJssdkConfig($url);
        return $this->response($res);
    }

    /**
     * 分享设置
     */
    public function share()
    {
        $this->checkToken();

        //页面路径
        $url = $this->params[ 'url' ] ?? '';

        //sdk配置
        $weapp_model = new WechatModel($this->site_id);
        $jssdk_config = $weapp_model->getJssdkConfig($url);
        if ($jssdk_config[ 'code' ] < 0) return $this->response($jssdk_config);

        //分享配置
        $share_config = [];
        $share_data = event('WchatShareData', [
            'url' => $url,
            'site_id' => $this->site_id,
            'member_id' => $this->member_id,
        ], true);
        if (!empty($share_data)) {
            $share_config[ 'permission' ] = $share_data[ 'permission' ];
            $share_config[ 'data' ] = $share_data[ 'data' ];
        } else {
            $share_config[ 'permission' ] = [
                'hideOptionMenu' => true,
                'hideMenuItems' => [],
            ];
            $share_config[ 'data' ] = null;
        }

        $data = [
            'jssdk_config' => $jssdk_config[ 'data' ],
            'share_config' => $share_config,
        ];

        return $this->response($this->success($data));
    }

    /**
     * 绑定店铺openid
     * @return false|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function shopBindOpenid()
    {
        $key = $this->params[ "key" ];
        $weapp_model = new WechatModel($this->site_id);
        $res = $weapp_model->authCodeToOpenid($this->params);
        if ($res[ "code" ] >= 0) {
            Cache::set($key, $res[ "data" ][ "openid" ]);
            return $this->response($res);
        }
    }

    /**
     * 获取登录二维码
     * @return false|string
     */
    public function loginCode()
    {
        $key = str_replace('==', '', base64_encode(uniqid('')));
        $expire_time = 600;

        $wechat_model = new WechatModel($this->site_id);
        $res = $wechat_model->getTempQrcode('key_' . $key, $expire_time);
        if ($res[ 'code' ] != 0) return $this->response($res);

        Cache::set('wechat_' . $key, [ 'expire_time' => ( time() + $expire_time ) ], 3600);
        $data = $this->success([
            'key' => $key,
            'expire_time' => $expire_time,
            'qrcode' => $res[ 'data' ]
        ]);
        return $this->response($data);
    }

    /*
     * 验证公众号是否配置
     */
    public function verificationWx()
    {
        $config_model = new ConfigModel();
        $config_info = $config_model->getWechatConfig($this->site_id);
        if (!empty($config_info[ 'data' ])) {
            $config = $config_info[ 'data' ][ "value" ];
            if ($config[ 'appid' ] || $config[ 'appsecret' ]) {
                return $this->response($this->success());
            }
        }
        return $this->response($this->error());
    }

    /**
     * 获取公众号关注二维码
     * @return false|string
     */
    public function followQrcode()
    {
        if (!in_array($this->params[ 'app_type' ], [ 'weapp', 'wechat' ])) return $this->response($this->error('', '该接口仅支持微信浏览器中调用'));

        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $member_model = new MemberModel();
        $member_info = $member_model->getMemberInfo([ [ 'member_id', '=', $token[ 'data' ][ 'member_id' ], [ 'site_id', '=', $this->site_id ] ] ], 'member_id,wx_openid')[ 'data' ];
        if (!empty($member_info) && !empty($member_info[ 'wx_openid' ])) {

            $wechat_fans = ( new Fans() )->getFansInfo([ [ 'site_id', '=', $this->site_id ], [ 'openid', '=', $member_info[ 'wx_openid' ] ], [ 'is_subscribe', '=', 1 ] ], 'fans_id')[ 'data' ];
            if (!empty($wechat_fans)) return $this->response($this->success());

            if (!empty($wechat_fans[ 'fans_id' ])) {
                $wechat_model = new WechatModel();
                $res = $wechat_model->getFollowQrcode($wechat_fans[ 'fans_id' ]);
                if ($res[ 'code' ] == 0) return $this->response($res);
            }
        }

//        $fans_info = ( new MemberFans() )->getFansInfo([ [ 'fans_id', '=', $this->fans_id ] ], 'openid')[ 'data' ];
//        $wechat_fans = ( new Fans() )->getFansInfo([ [ 'site_id', '=', $this->site_id ], [ 'is_subscribe', '=', 1 ], [ 'openid', '=', $fans_info[ 'openid' ] ] ], 'fans_id');
//        if (!empty($wechat_fans[ 'data' ])) return $this->response($this->success());

        $config_model = new ConfigModel();
        $config_result = $config_model->getWechatConfig($this->site_id)[ 'data' ][ 'value' ];

        return $this->response($this->success([ 'qrcode' => $config_result[ 'qrcode' ] ]));
    }
}