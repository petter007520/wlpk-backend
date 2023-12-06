<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace app\shopapi\controller;

use app\model\member\MemberLevel as MemberLevelModel;

/**
 * 会员等级
 * @package app\shop\controller
 */
class Memberlevel extends BaseApi
{
    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
        $token = $this->checkToken();
        if ($token['code'] < 0) {
            echo $this->response($token);
            exit;
        }
    }

    /**
     * 会员等级列表
     */
    public function lists()
    {
        $condition = [
            ['site_id', '=', $this->site_id],
            ['level_type', '=', 0]
        ];
        $list = (new MemberLevelModel())->getMemberLevelList($condition, 'level_id, level_name', 'growth asc');
        return $this->response($list);
    }
}