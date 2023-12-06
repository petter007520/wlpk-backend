<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\weapp\api\controller;

use addon\weapp\model\Config;
use addon\weapp\model\Message;
use app\api\controller\BaseApi;
use addon\weapp\model\Weapp as WeappModel;

class Weapp extends BaseApi
{

    /**
     * 获取openid
     */
    public function authCodeToOpenid()
    {
        $weapp_model = new WeappModel($this->site_id);
        $res         = $weapp_model->authCodeToOpenid($this->params);
        return $this->response($res);
    }

    /**
     * 获取消息模板id(最多三条)
     */
    public function messageTmplIds(){
        $keywords = $this->params['keywords'] ?? '';
        $message = new Message();
        $res = $message->getMessageTmplIds($this->site_id, $keywords);
        return $this->response($res);
    }

    /*
     * 获取小程序码
     */
    public function qrcode(){
        $config_model = new Config();
        $config = $config_model->getWeappConfig($this->site_id);
        $qrcode = $config['data']['value']['qrcode'] ?? '';
        return $this->response($this->success($qrcode));
    }

    /**
     * 分享
     * @return false|string
     */
    public function share()
    {
        /*$config_model = new Config();
        $config = $config_model->getShareConfig($this->site_id, 'shop');
        $share_config = $config['data']['value'];*/

        $this->checkToken();

        //页面路径
        $path          = $this->params['path'] ?? '';

        //分享配置
        $share_config = [];
        $share_data = event('WeappShareData', [
            'path' => $path,
            'site_id' => $this->site_id,
            'member_id' => $this->member_id,
        ], true);
        if(!empty($share_data)){
            $share_config['permission'] = $share_data['permission'];
            $share_config['data'] = $share_data['data'];
        }else{
            $share_config['permission'] = [
                'onShareAppMessage' => false,
                'onShareTimeline' => false,
            ];
            $share_config['data'] = null;
        }

        return $this->response($this->success($share_config));
    }
}