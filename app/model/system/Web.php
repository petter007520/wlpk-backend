<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\system;

use app\model\BaseModel;
use think\facade\Cache;

class Web extends BaseModel
{
    private $url = "https://www.niushop.com/api/%s";

    public function __construct()
    {

    }

    /**
     * 官网资讯
     */
    public function news()
    {
        $cache = Cache::get("new_day");
        if (!empty($cache)) {
            return $cache;
        }
        $url       = sprintf($this->url, 'news/news');
        $post_data = [

        ];
        $result    = $this->doPost($url, $post_data);
        $res       = json_decode($result, true);
        if ($res["code"] >= 0) {
            Cache::set("new_day", $res, 86400);
        }
        return $res;
    }

    /**
     * post 服务器请求
     */
    private function doPost($post_url, $post_data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if ($post_data != '' && !empty($post_data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}