<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\share;

use app\model\BaseModel;
use app\model\system\Config as ConfigModel;

/**
 * 分享
 */
class WchatShareBase extends BaseModel
{
    protected $config = [];
    protected $sort = 999;

    /**
     * @param array $param
     * @return mixed
     */
    public function getShareData($param)
    {
        $path = parse_url($param['url'])['path'] ?? '';
        $path = preg_replace("/^\/h5/", '', $path);

        foreach($this->config as $val){
            if(in_array($path, $val['path'])){
                $method = $val['method_prefix'].'ShareData';
                if(method_exists($this, $method)){
                    return $this->$method(array_merge($param, ['config' => $val]));
                }
            }
        }
    }

    /**
     * 获取分享配置
     * @param $param
     * @return array
     */
    public function getShareConfig($param)
    {
        $data = [];
        foreach($this->config as $val){
            $method = $val['method_prefix'].'ShareConfig';
            if(method_exists($this, $method)){
                $item = $this->$method(array_merge($param, ['config' => $val]));
                $item['config'] = $val;
                $data[] = $item;
            }
        }
        return [
            'sort' => $this->sort,
            'data' => $data,
        ];
    }

    /**
     * 设置分享内容
     * @param $site_id
     * @param $data
     * @return array
     */
    public function setShareConfig($site_id, $data)
    {
        $config_model = new ConfigModel();
        foreach($data as $key=>$val){
            $config_key = $val['config_key'];
            $config_data = [];
            if(isset($val['title'])) $config_data['title'] = $val['title'];
            if(isset($val['desc'])) $config_data['desc'] = $val['desc'];
            if(isset($val['imgUrl'])) $config_data['imgUrl'] = $val['imgUrl'];

            $config_model->setConfig($config_data, '公众号分享设置', 1, [
                ['site_id', '=', $site_id],
                ['app_module', '=', 'shop'],
                ['config_key', '=', $config_key],
            ]);
        }
        return $this->success();
    }

    /**
     * 获取分享链接
     * @param $param
     * @return string
     */
    protected function getShareLink($param)
    {
        $member_id = $param['member_id'];
        $link = $param['url'];
        //如果链接中有原分享人数据要先去掉
        $link = preg_replace("/source_member=\d+/", "", $link);
        $link = rtrim($link, '?');
        if(!empty($member_id)){
            if(strpos($link, '?')){
                $link .= '&';
            }else{
                $link .= '?';
            }
            $link .= 'source_member='.$member_id;
        }
        return $link;
    }

    /**
     * 默认分享图标
     * @return string
     */
    protected function getDefaultShareIcon()
    {
        return img('public/static/img/wx_share_icon.png');
    }
}
