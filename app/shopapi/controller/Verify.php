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

use app\model\member\Member;
use app\model\verify\Verifier;
use app\model\verify\Verify as VerifyModel;

/**
 * 核销
 * Class Verify
 * @package app\shop\controller
 */
class Verify extends BaseApi
{

    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            echo $this->response($token);
            exit;
        }
    }

    /**
     * 核销记录
     * @return mixed
     */
    public function records()
    {
        $verify_model = new VerifyModel();

        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $order = isset($this->params[ 'order' ]) ? $this->params[ 'order' ] : 'create_time desc';
        $verify_type = isset($this->params[ 'verify_type' ]) ? $this->params[ 'verify_type' ] : '';//验证类型
        $verify_code = isset($this->params[ 'verify_code' ]) ? $this->params[ 'verify_code' ] : '';//验证码
        $verifier_name = isset($this->params[ 'verifier_name' ]) ? $this->params[ 'verifier_name' ] : '';
        $start_time = isset($this->params[ 'start_time' ]) ? $this->params[ 'start_time' ] : '';
        $end_time = isset($this->params[ 'end_time' ]) ? $this->params[ 'end_time' ] : '';

        $condition = [
            [ 'site_id', "=", $this->site_id ],
            [ 'is_verify', '=', 1 ]
        ];
        if (!empty($verify_type)) {
            $condition[] = [ "verify_type", "=", $verify_type ];
        }
        if (!empty($verify_code)) {
            $condition[] = [ "verify_code", 'like', '%' . $verify_code . '%' ];
        }
        if (!empty($verifier_name)) {
            $condition[] = [ 'verifier_name', 'like', '%' . $verifier_name . '%' ];
        }
        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ 'create_time', '>=', date_to_time($start_time) ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ "create_time", "<=", date_to_time($end_time) ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
        }
        $list = $verify_model->getVerifyPageList($condition, $page, $page_size, $order, $field = 'id, verify_code, verify_type, verify_type_name, verify_content_json, verifier_id, verifier_name, is_verify, create_time, verify_time');

        return $this->response($list);
    }

    /**
     * 核销类型
     * @return false|string
     */
    public function verifyType()
    {
        $verify_model = new VerifyModel();
        $verify_type = $verify_model->getVerifyType();
        return $this->response($this->success($verify_type));
    }

    /**
     * 核销信息
     */
    public function verifyInfo()
    {
        $id = isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : '';

        $verify_model = new VerifyModel();
        $info = $verify_model->getVerifyInfo([ [ 'id', '=', $id ], [ 'site_id', '=', $this->site_id ] ]);

        return $this->response($info);
    }

    /**
     * 核销台
     * @return mixed
     */
    public function verifyCard()
    {

        $verify_code = isset($this->params[ 'verify_code' ]) ? $this->params[ 'verify_code' ] : '';
        $verify_model = new VerifyModel();
        $res = $verify_model->getVerifyInfo([ [ "verify_code", "=", $verify_code ], [ "site_id", "=", $this->site_id ] ]);

        return $this->response($res);
    }

    /**
     * 核销人员
     * @return mixed
     */
    public function user()
    {
        $verifier = new Verifier();
        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $order = isset($this->params[ 'order' ]) ? $this->params[ 'order' ] : 'v.create_time desc';
        $verifier_name = isset($this->params[ 'verifier_name' ]) ? $this->params[ 'verifier_name' ] : '';
        $condition = [];
        $condition[] = [ 'v.site_id', "=", $this->site_id ];
        if ($verifier_name) {
            $condition[] = [ 'v.verifier_name', '=', $verifier_name ];
        }
        $list = $verifier->getVerifierPageList($condition, $page, $page_size, $order);

        return $this->response($list);
    }

    /**
     * 添加核销人员
     * @return mixed
     */
    public function addUser()
    {
        $verifier_name = isset($this->params[ 'verifier_name' ]) ? $this->params[ 'verifier_name' ] : '';
        $member_id = isset($this->params[ 'member_id' ]) ? $this->params[ 'member_id' ] : 0;//会员账号
        $model = new Verifier();
        if ($member_id <= 0) {
            $model->error([], "EMPTY_BIND_MEMBER");
        }

        $uid = isset($this->params[ 'uid' ]) ? $this->params[ 'uid' ] : 0;//管理员账号
        $data = array ();
        $data[ 'site_id' ] = $this->site_id;
        $data[ 'create_time' ] = time();
        $data[ "verifier_name" ] = $verifier_name;
        $data[ "member_id" ] = $member_id;
        $data[ "uid" ] = $uid;
        $result = $model->addVerifier($data);
        return $this->response($result);
    }

    /**
     * 获取核销人员详情
     */
    public function verifyUSerInfo()
    {
        $model = new Verifier();
        $verifier_id = isset($this->params[ 'verifier_id' ]) ? $this->params[ 'verifier_id' ] : 0;
        //用户信息
        $condition = [
            [ "verifier_id", "=", $verifier_id ],
            [ "site_id", "=", $this->site_id ],
        ];
        $info_result = $model->getVerifierInfo($condition);
        $info = $info_result[ "data" ];
        $member_account = "";
        if (!empty($info[ "member_id" ])) {
            $member_model = new Member();
            $member_info_result = $member_model->getMemberInfo([ [ "member_id", "=", $info[ "member_id" ] ] ], "username");
            $member_info = $member_info_result[ "data" ];
            if (!empty($member_info)) {
                $member_account = $member_info[ "username" ];
            }

        }
        $info[ "member_account" ] = $member_account;

        return $this->response($this->success($info));
    }

    /**
     * 编辑核销人员
     * @return mixed
     */
    public function editUser()
    {
        $model = new Verifier();
        $verifier_id = isset($this->params[ 'verifier_id' ]) ? $this->params[ 'verifier_id' ] : 0;//核销员id

        $verifier_name = isset($this->params[ 'verifier_name' ]) ? $this->params[ 'verifier_name' ] : '';
        $member_id = isset($this->params[ 'member_id' ]) ? $this->params[ 'member_id' ] : '';//会员账号
        if ($member_id <= 0) {
            $model->error([], "EMPTY_BIND_MEMBER");
        }

        $data = [
            'verifier_name' => $verifier_name,
            'modify_time' => time(),
        ];
        $data[ "member_id" ] = $member_id;
        $data[ "uid" ] = 0;
        $condition = array (
            [ 'verifier_id', '=', $verifier_id ],
            [ 'site_id', '=', $this->site_id ],
        );

        $result = $model->editVerifier($data, $condition);

        return $this->response($result);
    }

    /**
     * 删除核销人员
     * @return mixed
     */
    public function deleteUser()
    {
        $verifier = new Verifier();

        $verifier_id = isset($this->params[ 'ids' ]) ? $this->params[ 'ids' ] : '';
        $res = $verifier->deleteVerifier($verifier_id, $this->site_id);

        return $this->response($res);
    }

    /**
     * 核销
     */
    public function verify()
    {
        //先验证登录用户是否具备核销权限
        $info = array (
            "verifier_id" => $this->uid,
            "verifier_name" => $this->user_info[ 'username' ],
            "verify_from" => 'mobile',
        );
        $verify_code = isset($this->params[ 'verify_code' ]) ? $this->params[ 'verify_code' ] : '';
        $verify_model = new VerifyModel();
        $res = $verify_model->verify($info, $verify_code);

        return $this->response($res);
    }

    /**
     * 搜索会员
     * 不是菜单 不入权限
     */
    public function searchMember()
    {
        $search_text = isset($this->params[ 'search_text' ]) ? $this->params[ 'search_text' ] : '';
        $member_model = new Member();
        $member_info = $member_model->getMemberInfo([ [ 'username|mobile', '=', $search_text ] ]);

        return $this->response($member_info);
    }

}