<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace app\model\member;

use addon\coupon\model\Coupon;
use app\model\system\Cron;
use think\facade\Cache;
use app\model\BaseModel;
use addon\coupon\model\CouponType;

/**
 * 会员等级
 */
class MemberLevel extends BaseModel
{
    public $level_change_type = [
        'upgrade' => '自动升级',
        'buy' => '付费购卡',
        'adjust' => '商家调整',
        'expire' => '到期降级'
    ];

    public $level_time = [
        'week' => '一周',
        'month' => '一月',
        'quarter' => '一季',
        'year' => '一年',
    ];

    /**
     * 添加会员等级
     *
     * @param array $data
     */
    public function addMemberLevel($data)
    {
        $res = model('member_level')->add($data);

        Cache::tag("member_level")->clear();
        return $this->success($res);
    }

    /**
     * 修改会员等级(不允许批量处理)
     *
     * @param array $data
     * @param array $condition
     */
    public function editMemberLevel($data, $condition)
    {
        $res = model('member_level')->update($data, $condition);
        $check_condition = array_column($condition, 2, 0);
        $level_id = isset($check_condition[ 'level_id' ]) ? $check_condition[ 'level_id' ] : 0;
        if (!empty($level_id) && isset($data['level_name'])) {
            model('member')->update([ 'member_level_name' => $data['level_name'] ], [ ['member_level', '=', $level_id] ]);
        }
        Cache::tag("member_level")->clear();
        return $this->success();
    }

    /**
     * 更新会员等级
     * @param $site_id
     */
    public function startlevel($site_id)
    {
        $cron = new Cron();
        $cron->addCron(1, 0, "会员等级更新", "MemberLevelUpdate", time(), 0);
        return $this->success();
    }

    /**
     * 刷新会员等级排序
     */
    private function refreshSort($site_id)
    {
        $list = model('member_level')->getList([['site_id', '=', $site_id]], 'level_id, growth', 'growth asc');
        foreach ($list as $k => $v) {
            model('member_level')->update(['sort' => $k], [['level_id', '=', $v['level_id']]]);
        }
    }

    /**
     * 刷新会员等级
     */
    private function refreshLevel($site_id)
    {
        model('member_level')->update(['is_default' => 0], [['is_default', '=', 1], ['site_id', '=', $site_id]]);
    }

    /**
     * 删除会员等级
     * @param array $condition
     */
    public function deleteMemberLevel($level_id, $site_id)
    {
        $count = model('member')->getCount([ ['member_level', '=', $level_id], ['is_delete', '=', 0] ]);
        if ($count > 0) return $this->error('', '有会员正持有该等级不可删除');
        $condition = [
            ['level_id', '=', $level_id],
            ['site_id', '=', $site_id],
        ];
        $res = model('member_level')->delete($condition);
        Cache::tag("member_level")->clear();
        return $this->success($res);
    }

    /**
     * 获取一条等级
     * @param $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getFirstMemberLevel($condition, $field = '*', $order = ""){
        $data = model('member_level')->getFirstData($condition, $field, $order);
        return $this->success($data);
    }

    /**
     * 获取会员等级信息
     *
     * @param array $condition
     * @param string $field
     */
    public function getMemberLevelInfo($condition = [], $field = '*')
    {

        $data  = json_encode([$condition, $field]);
        $cache = Cache::get("member_level_getMemberLevelInfo_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $info = model('member_level')->getInfo($condition, $field);
        if ($info) {
            //获取优惠券信息
            if (isset($info['send_coupon']) && !empty($info['send_coupon'])) {
                //优惠券字段
                $coupon_field = 'coupon_type_id,type,coupon_name,image,money,discount,validity_type,fixed_term,status,is_limit,at_least,count,lead_count,end_time';

                $model               = new CouponType();
                $coupon              = $model->getCouponTypeList([['coupon_type_id', 'in', $info['send_coupon']]], $coupon_field);
                $info['coupon_list'] = $coupon;
            }
        }
        Cache::tag("member_level")->set("member_level_getMemberLevelInfo_" . $data, $info);
        return $this->success($info);
    }

    /**
     * 获取会员等级列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getMemberLevelList($condition = [], $field = '*', $order = 'sort asc, level_id asc', $limit = null)
    {

        $data  = json_encode([$condition, $field, $order, $limit]);
        $cache = Cache::get("member_level_getMemberLevelList_" . $data);

        if (!empty($cache)) {
            return $this->success($cache);
        }

        $list = model('member_level')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("member_level")->set("member_level_getMemberLevelList_" . $data, $list);

        return $this->success($list);
    }

    /**
     * 获取会员等级分页列表
     *
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getMemberLevelPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'sort asc, level_id asc', $field = '*')
    {

        $data  = json_encode([$condition, $field, $order, $page, $page_size]);
        $cache = Cache::get("member_level_getMemberLevelPageList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('member_level')->pageList($condition, $field, $order, $page, $page_size);
        Cache::tag("member_level")->set("member_level_getMemberLevelPageList_" . $data, $list);
        return $this->success($list);
    }

    /**
     * 添加会员卡变更记录
     * @param $member_id 变更会员
     * @param $site_id 站点id
     * @param $after_level 变更之后的会员卡
     * @param $action_uid 操作人
     * @param $action_type 操作人类型
     * @param $action_name 操作人昵称
     * @param string $action_desc 描述
     */
    public function addMemberLevelChangeRecord($member_id, $site_id, $after_level, $expire_time, $change_type, $action_uid, $action_type, $action_name, $action_desc = ''){
        model('member_level_records')->startTrans();

        try {
            $member_info = model('member')->getInfo([ ['member_id', '=', $member_id] ], 'member_level,member_level_name,member_level_type,level_expire_time,is_member');
            $level_info = model('member_level')->getInfo([ ['level_id', '=', $after_level], ['site_id', '=', $site_id] ], 'level_id,level_name,level_type');
            if ($member_info['member_level'] == $level_info['level_id']) {
                model('member_level_records')->rollback();
                return $this->success('', '会员卡未发生变更');
            }

            $prev_record = model('member_level_records')->getFirstData([ ['member_id', '=', $member_id] ], 'id', 'change_time desc');
            // 添加变更记录
            $data = [
                'member_id' => $member_id,
                'site_id' => $site_id,
                'before_level_id' => $member_info['member_level'],
                'before_level_name' => $member_info['member_level_name'],
                'before_level_type' => $member_info['member_level_type'],
                'before_expire_time' => $member_info['level_expire_time'],
                'after_level_id' => $level_info['level_id'],
                'after_level_name' => $level_info['level_name'],
                'after_level_type' => $level_info['level_type'],
                'prev_id' => $prev_record['id'] ?? 0,
                'change_time' => time(),
                'action_uid' => $action_uid,
                'action_type' => $action_type,
                'action_name' => $action_name,
                'action_desc' => $action_desc,
                'change_type' => $change_type,
                'change_type_name' => $this->level_change_type[$change_type]
            ];
            model('member_level_records')->add($data);

            // 变更会员等级
            $edit_memeber_data = [
                'member_level' => $level_info['level_id'],
                'member_level_name' => $level_info['level_name'],
                'member_level_type' => $level_info['level_type'],
                'level_expire_time' => $level_info['level_type'] == 0 ? 0 : $expire_time
            ];
            if (!$member_info['is_member']) {
                $edit_memeber_data['is_member'] = 1;
                $edit_memeber_data['member_time'] = time();
            }
            model('member')->update($edit_memeber_data, [ ['member_id','=',$member_id] ]);
            $cron = new Cron();
            $cron->deleteCron([ ['event', '=', 'MemberLevelAutoExpire'], ['relate_id', '=', $member_id ] ]);
            if ($level_info['level_type']) {
                $cron->addCron(1, 0, "会员卡自动过期", "MemberLevelAutoExpire", $expire_time, $member_id);
            }
            Cache::tag("member_level_records")->clear();
            model('member_level_records')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('member_level_records')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 获取会员会员卡变更记录
     * @param array $condition
     * @param int $page
     * @param int $list_rows
     * @param string $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @param null $group
     * @return array
     */
    public function getMemberLevelRecordPageList($condition = [], $page = 1, $list_rows = PAGE_LIST_ROWS, $field = '*', $order = 'change_time desc',  $alias = 'a', $join = [], $group = null){
        $data  = json_encode([$condition, $field, $order, $page, $list_rows, $alias, $join, $group]);
        $cache = Cache::get("getMemberLevelRecordPageList" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('member_level_records')->pageList($condition, $field, $order, $page, $list_rows, $alias, $join, $group);
        Cache::tag("member_level_records")->set("getMemberLevelRecordPageList" . $data, $list);
        return $this->success($list);
    }

    /**
     * 会员卡过期
     * @param $member_id
     */
    public function memberLevelExpire($member_id){
        $member_info = model('member')->getInfo([ ['member_id', '=', $member_id] ], 'member_id,site_id,nickname,member_level,level_expire_time,growth');
        if (!empty($member_info) && !empty($member_info['level_expire_time']) && $member_info['level_expire_time'] < time()) {
            $alias = 'mlr';
            $join = [
                ['member_level ml', 'ml.level_id = mlr.before_level_id', 'inner']
            ];
            // 如果会员还存在未过期的付费会员卡
            $level_info = model('member_level_records')->getFirstDataView([ ['before_expire_time', '>', time()], ['member_id', '=', $member_id] ], 'mlr.*', 'change_time desc', $alias, $join);

            if (!empty($level_info)) {
                $this->addMemberLevelChangeRecord($member_id, $member_info['site_id'], $level_info['before_level_id'], $level_info['before_expire_time'], 'expire', $member_id, 'member', $member_info['nickname']);
            } else {
                // 如果之前免费卡还存在
                $level_info = model('member_level_records')->getFirstDataView([ ['before_level_type', '=', 0], ['member_id', '=', $member_id] ], 'mlr.*', 'change_time desc', $alias, $join);
                if (!empty($level_info)) {
                    $this->addMemberLevelChangeRecord($member_id, $member_info['site_id'], $level_info['before_level_id'], $level_info['before_expire_time'], 'expire', $member_id, 'member', $member_info['nickname']);
                    event("AddMemberAccount", ['account_type' => 'growth', 'member_id' => $member_id, 'site_id' => $member_info['site_id']]);
                } else {
                    // 如果之前的免费卡不存在
                    $level_info = model('member_level')->getFirstData([ ['site_id', '=', $member_info['site_id']], ['level_type', '=', 0], ['growth', '<=', $member_info['growth']] ], '*', 'growth desc');

                    if (!empty($level_info)) {
                        $this->addMemberLevelChangeRecord($member_id, $member_info['site_id'], $level_info['level_id'], 0, 'expire', $member_id, 'member', $member_info['nickname']);
                        $member_account = new MemberAccount();
                        //赠送红包
                        if ($level_info['send_balance'] > 0) {
                            $member_account->addMemberAccount($member_info['site_id'], $member_info['member_id'], 'balance', $level_info['send_balance'], 'upgrade', '会员升级得红包' . $level_info['send_balance'], '会员等级升级奖励');
                        }
                        //赠送积分
                        if ($level_info['send_point'] > 0) {
                            $member_account->addMemberAccount($member_info['site_id'], $member_info['member_id'], 'point', $level_info['send_point'], 'upgrade', '会员升级得积分' . $level_info['send_point'], '会员等级升级奖励');
                        }
                        //给用户发放优惠券
                        if (!empty($level_info['send_coupon'])) {
                            $coupon_array = explode(',', $level_info['send_coupon']);
                            $coupon_model = new Coupon();
                            $coupon_array = array_map(function ($value){
                                return ['coupon_type_id' => $value, 'num' => 1];
                            }, $coupon_array);
                            $coupon_model->giveCoupon($coupon_array, $member_info['site_id'], $member_info['member_id'], 3);
                        }
                    }
                }
            }
        }
    }
}