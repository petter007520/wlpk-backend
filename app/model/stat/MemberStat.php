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
class MemberStat extends BaseModel
{
    /**
     * 用于新增会员(便于之后扩展统计各个场景添加的会员)
     * @param $params
     */
    public function addMemberStat($params){
        $member_id = $params['member_id'] ?? 0;
        $site_id = $params['site_id'] ?? 0;

        $stat_data = array(
            'site_id' => $site_id,
            'member_count' => 1
        );
        $stat_model = new Stat();
        $result = $stat_model->addShopStat($stat_data);
        return $result;
    }

}