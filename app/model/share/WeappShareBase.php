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
class WeappShareBase extends BaseModel
{
    protected $config = [];
    protected $sort = 999;

    /**
     * @param array $param
     * @return mixed
     */
    public function getShareData($param)
    {
        $path = parse_url($param['path'])['path'] ?? '';
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
            if(isset($val['imageUrl'])) $config_data['imageUrl'] = $val['imageUrl'];

            $config_model->setConfig($config_data, '小程序分享设置', 1, [
                ['site_id', '=', $site_id],
                ['app_module', '=', 'shop'],
                ['config_key', '=', $config_key],
            ]);
        }
        return $this->success();
    }

    /**
     * 获取分享路径
     * @param $param
     * @return string
     */
    public function getSharePath($param)
    {
        $member_id = $param['member_id'];
        $path = $param['path'];
        //如果链接中有原分享人数据要先去掉
        $path = preg_replace("/source_member=\d+/", "", $path);
        $path = rtrim($path, '?');
        if(!empty($member_id)){
            if(strpos($path, '?')){
                $path .= '&';
            }else{
                $path .= '?';
            }
            $path .= 'source_member='.$member_id;
        }
        return $path;
    }
}
