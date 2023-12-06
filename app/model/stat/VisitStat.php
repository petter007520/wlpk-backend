<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\stat;

use app\model\BaseModel;
use app\model\system\Stat;

/**
 * 统计
 * @author Administrator
 *
 */
class VisitStat extends BaseModel
{
    /**
     * 用于多场景访问量
     * @param $params
     */
    public function addVisitStat($params){
        $app_module = $params['app_module'] ?? 'wechat';
//        switch($app_module){
//            case 'h5':
//                $visit_name = 'h5_visit_count';
//                break;
//            case 'weapp':
//                $visit_name = 'weapp_visit_count';
//                break;
//            case 'wechat':
//                $visit_name = 'wechat_visit_count';
//                break;
//            case 'pc':
//                $visit_name = 'pc_visit_count';
//                break;
//        }
        $visit_name = $app_module.'_visit_count';
        $site_id = $params['site_id'] ?? 0;

        $stat_data = array(
            'site_id' => $site_id,
            'visit_count' => 1,
            $visit_name => 1
        );
        $stat_model = new Stat();
        $result = $stat_model->addShopStat($stat_data);
        return $result;
    }
    
}