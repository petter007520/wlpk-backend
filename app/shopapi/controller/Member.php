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

namespace app\shopapi\controller;


use app\model\member\Member as MemberModel;
use app\model\member\MemberAccount as MemberAccountModel;
use app\model\member\MemberLabel as MemberLabelModel;
use app\model\member\MemberLevel as MemberLevelModel;
use app\model\order\OrderCommon;
use app\model\order\OrderCommon as OrderCommonModel;
use think\facade\Db;

/**
 * 店铺会员
 * @package app\shop\controller
 */
class Member extends BaseApi
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
     * 店铺会员列表
     */
    public function lists()
    {
        $member = new MemberModel();

        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $start_date = $this->params['start_date'] ?? '';
        $end_date = $this->params['end_date'] ?? '';
        $start_order_complete_money = $this->params['start_order_complete_money'] ?? '';
        $end_order_complete_money = $this->params['end_order_complete_money'] ?? '';
        $start_point = $this->params['start_point'] ?? '';
        $end_point = $this->params['end_point'] ?? '';
        $start_balance = $this->params['start_balance'] ?? '';
        $end_balance = $this->params['end_balance'] ?? '';
        $start_growth = $this->params['start_growth'] ?? '';
        $end_growth = $this->params['end_growth'] ?? '';
        $is_member = $this->params['is_member'] ?? '';
        $status = $this->params['status'] ?? '';
        $search_text = $this->params['search_text'] ?? '';

        $condition = [
            ['site_id', '=', $this->site_id]
        ];
        if (!empty($search_text)) {
            $condition[] = ['nickname|mobile', 'like', "%" . $search_text . "%"];
        }
        // 关注时间
        if ($start_date != '' && $end_date != '') {
            $condition[] = ['reg_time', 'between', [strtotime($start_date), strtotime($end_date)]];
        } else if ($start_date != '' && $end_date == '') {
            $condition[] = ['reg_time', '>=', strtotime($start_date)];
        } else if ($start_date == '' && $end_date != '') {
            $condition[] = ['reg_time', '<=', strtotime($end_date)];
        }
        //会员状态
        if ($status != '') {
            $condition[] = [ 'status', '=', $status ];
        }
        //消费金额
        if ($start_order_complete_money != '' && $end_order_complete_money != '') {
            $condition[] = [ 'order_complete_money', 'between', [ $start_order_complete_money, $end_order_complete_money ] ];
        } else if ($start_order_complete_money != '' && $end_order_complete_money == '') {
            $condition[] = [ 'order_complete_money', '>=', $start_order_complete_money ];
        } else if ($start_order_complete_money == '' && $end_order_complete_money != '') {
            $condition[] = [ 'order_complete_money', '<=', $end_order_complete_money ];
        }
        //积分
        if ($start_point != '' && $end_point != '') {
            $condition[] = [ 'point', 'between', [ $start_point, $end_point ] ];
        } else if ($start_point != '' && $end_point == '') {
            $condition[] = [ 'point', '>=', $start_point ];
        } else if ($start_point == '' && $end_point != '') {
            $condition[] = [ 'point', '<=', $end_point ];
        }
        //余额
        if ($start_balance != '' && $end_balance != '') {
            $condition[] = [ '', 'exp', Db::raw("(balance + balance_money) between {$start_balance} and {$end_balance}") ];
        } else if ($start_balance != '' && $end_balance == '') {
            $condition[] = [ '', 'exp', Db::raw("(balance + balance_money) >= {$start_balance}") ];
        } else if ($start_balance == '' && $end_balance != '') {
            $condition[] = [ '', 'exp', Db::raw("(balance + balance_money) <= {$end_balance}") ];
        }
        //成长值
        if ($start_growth != '' && $end_growth != '') {
            $condition[] = [ 'growth', 'between', [ $start_growth, $end_growth ] ];
        } else if ($start_growth != '' && $end_growth == '') {
            $condition[] = [ 'growth', '>=', $start_growth ];
        } else if ($start_growth == '' && $end_growth != '') {
            $condition[] = [ 'growth', '<=', $end_growth ];
        }
        if ($is_member != '') $condition[] = [ 'is_member', '=', $is_member ];
        $list = $member->getMemberPageList($condition, $page, $page_size, 'last_visit_time desc', 'nickname,mobile,member_level_name,member_level,headimg,member_id,last_login_time,point,balance,balance_money,growth,status,is_member,order_money,order_complete_money');
        return $this->response($list);
    }

    /**
     * 会员详情
     */
    public function detail()
    {
        $member_id = isset($this->params['member_id']) ? $this->params['member_id'] : 0;
        $member = new MemberModel();
        $condition = [
            ['member_id', '=', $member_id],
            ['site_id', '=', $this->site_id]
        ];
        $field = 'member_id,username,headimg,nickname,mobile,member_level_name,member_label_name,birthday,sex,point,balance,growth,balance_money,is_member';
        $info = $member->getMemberInfo($condition, $field);
        $data['member_info'] = $info[ 'data' ];
        //会员等级
        $member_level_model = new MemberLevelModel();
        $member_level_list = $member_level_model->getMemberLevelList([ [ 'site_id', '=', $this->site_id ] ], 'level_id, level_name', 'growth asc');
        $data['member_level_list'] = $member_level_list[ 'data' ];

        //会员标签
        $member_label_model = new MemberLabelModel();
        $member_label_list = $member_label_model->getMemberLabelList([ [ 'site_id', '=', $this->site_id ] ], 'label_id, label_name', 'sort asc');
        $data['member_label_list'] = $member_label_list[ 'data' ];
        return $this->response($this->success($data));
    }

    /**
     * 会员编辑
     */
    public function editMember(){
        $data = [];
        if (isset($this->params['headimg'])) $data['headimg'] = $this->params['headimg'];
        if (isset($this->params['nickname'])) $data['nickname'] = $this->params['nickname'];
        if (isset($this->params['mobile'])) $data['mobile'] = $this->params['mobile'];
        if (isset($this->params['level_id']) && !empty($this->params['level_id'])) $data['member_level'] = $this->params['level_id'];
        if (isset($this->params['level_id']) && !empty($this->params['level_id'])){
            $member_level_model = new MemberLevelModel();
            $condition = [
                ['site_id', '=', $this->site_id],
                ['level_id', '=', $this->params['level_id']],
                ['status', '=', 1]
            ];
            $member_level = $member_level_model->getFirstMemberLevel($condition);
            $data['member_level_name'] = $member_level['data']['level_name'];
        }
        if (isset($this->params['label_id']) && !empty($this->params['label_id'])) $data['member_label'] = $this->params['label_id'];
        if (isset($this->params['label_id']) && !empty($this->params['label_id'])){
            $member_label_model = new MemberLabelModel();
            $member_label = $member_label_model->getMemberLabelInfo([['label_id', '=', $this->params['label_id']]]);
            $data['member_label_name'] = $member_label['data']['label_name'];
        }
        if (isset($this->params['sex'])) $data['sex'] = $this->params['sex'];
        if (isset($this->params['birthday'])) $data['birthday'] = $this->params['birthday'] ? strtotime($this->params['birthday']) : 0;
        $member_id = $this->params['member_id'];
        $member_model = new MemberModel();
        $this->addLog("编辑会员:id" . $member_id, $data);
        $info = $member_model->editMember($data, [ [ 'member_id', '=', $member_id ], ['site_id', '=', $this->site_id] ]);
        return $this->response($info);
    }
    /**
     * 账户流水
     * @return false|string
     */
    public function memberAccountList()
    {
        $member_id = isset($this->params['member_id']) ? $this->params['member_id'] : 0;
        $page_index = isset($this->params['page']) && !empty($this->params['page_size']) ? $this->params['page'] : 1;
        $page_size = isset($this->params['page_size']) && !empty($this->params['page_size']) ? $this->params['page_size'] : PAGE_LIST_ROWS;
        $account_type = isset($this->params[ 'account_type' ]) ? $this->params[ 'account_type' ] : '';
        if(!empty($account_type)){
            if (!in_array($account_type, ['point', 'growth', 'balance','balance_money' ])){
                return $this->response($this->error('', 'INVALID_PARAMETER'));
            }
        }
        $memberAcc = new MemberAccountModel();
        $condition = [
            ['member_id', '=', $member_id],
            ['site_id', '=', $this->site_id]
        ];
        if (!empty($account_type)) {
            $condition[] = [ 'account_type', '=', $account_type ];
        }
        $field = 'id,member_id,account_type,account_data,from_type,type_name,type_tag,remark,create_time,username,mobile,email';
        $data = $memberAcc->getMemberAccountPageList($condition, $page_index, $page_size, 'create_time desc', $field);

        if (!empty($data['data']['list'])) {
            $account_type = $memberAcc->getAccountType();
            $data['data']['list'] = array_map(function ($item) use ($account_type) {
                $item['account_type_name'] = $account_type[ $item['account_type'] ];
                return $item;
            }, $data['data']['list']);
        }
        return $this->response($data);
    }

    /**
     * 加入移除黑名单
     * @return false|string
     */
    public function joinBlacklist()
    {
        $member_id = input('member_id', 0);
        $status = input('status', 0);
        $condition = [
            ['member_id', '=', $member_id],
            ['site_id', '=', $this->site_id]
        ];
        $member_model = new MemberModel();
        $info = $member_model->modifyMemberStatus($status, $condition);
        return $this->response($info);
    }

    /**
     * 获取会员订单列表
     */
    public function orderList()
    {
        $search_text = isset($this->params['search_text']) ? $this->params['search_text'] : '';
        $member_id = isset($this->params['member_id']) ? $this->params['member_id'] : 0;
        $page_index = isset($this->params['page']) ? $this->params['page'] : 1;
        $page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGE_LIST_ROWS;
        if (!empty($search_text)) {
            $condition[] = ['order_no', 'like', '%' . $search_text . '%'];
        }
        $condition[] = ['member_id', '=', $member_id];
        $condition[] = ['site_id', '=', $this->site_id];
        $field = 'order_id,order_no,order_name,order_type,order_money,pay_money,balance_money,order_type_name,order_status_name,delivery_status_name,create_time';
        $order = new OrderCommon();
        $list = $order->getMemberOrderPageList($condition, $page_index, $page_size, 'order_id desc', $field);
        return $this->response($list);
    }

    /**
     * 重置密码
     */
    public function modifyMemberPassword()
    {
        $password = isset($this->params['password']) ? $this->params['password'] : '123456';
        $member_id = isset($this->params['member_id']) ? $this->params['member_id'] : 0;
        $member_model = new MemberModel();
        $info = $member_model->resetMemberPassword($password, [['member_id', '=', $member_id], ['site_id', '=', $this->site_id]]);
        return $this->response($info);
    }

    /**
     * 调整余额
     */
    public function modifyBalance()
    {
        $member_id = isset($this->params['member_id']) ? $this->params['member_id'] : 0;
        $adjust_num = isset($this->params['adjust_num']) ? $this->params['adjust_num'] : 0;
        $remark = isset($this->params['remark']) ? $this->params['remark'] : '商家调整';
        $this->addLog("会员余额调整id:" . $member_id . "金额" . $adjust_num);
        $member_account_model = new MemberAccountModel();
        $info = $member_account_model->addMemberAccount($this->site_id, $member_id, 'balance', $adjust_num, 'adjust', 0, $remark);
        return $this->response($info);
    }

    /**
     * 余额调整（可提现）
     */
    public function modifyBalanceMoney()
    {
        return $this->response($this->error());

        $member_id = isset($this->params['member_id']) ? $this->params['member_id'] : 0;
        $adjust_num = isset($this->params['adjust_num']) ? $this->params['adjust_num'] : 0;
        $remark = isset($this->params['remark']) ? $this->params['remark'] : '商家调整';
        $this->addLog("会员余额调整id:" . $member_id . "金额" . $adjust_num);
        $member_account_model = new MemberAccountModel();
        $info = $member_account_model->addMemberAccount($this->site_id, $member_id, 'balance_money', $adjust_num, 'adjust', 0, $remark);
        return $this->response($info);
    }

    /**
     * 积分调整
     */
    public function modifyPoint()
    {
        $member_id = isset($this->params['member_id']) ? $this->params['member_id'] : 0;
        $adjust_num = isset($this->params['adjust_num']) ? $this->params['adjust_num'] : 0;
        $remark = isset($this->params['remark']) ? $this->params['remark'] : '商家调整';
        $this->addLog("会员积分调整id:" . $member_id . "数量" . $adjust_num);
        $member_account_model = new MemberAccountModel();
        $info = $member_account_model->addMemberAccount($this->site_id, $member_id, 'point', $adjust_num, 'adjust', 0, $remark);
        return $this->response($info);
    }

    /**
     * 成长值调整
     */
    public function modifyGrowth()
    {
        $member_id = isset($this->params['member_id']) ? $this->params['member_id'] : 0;
        $adjust_num = isset($this->params['adjust_num']) ? $this->params['adjust_num'] : 0;
        $remark = isset($this->params['remark']) ? $this->params['remark'] : '商家调整';
        $this->addLog("会员成长值调整id:" . $member_id . "数量" . $adjust_num);
        $member_account_model = new MemberAccountModel();
        $info = $member_account_model->addMemberAccount($this->site_id, $member_id, 'growth', $adjust_num, 'adjust', 0, $remark);
        return $this->response($info);
    }
}