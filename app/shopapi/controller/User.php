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

use app\model\system\User as UserModel;
use app\model\system\Group;
use app\model\system\UserGroup;

/**
 * 用户
 * Class User
 * @package app\shop\controller
 */
class User extends BaseApi
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
     * 用户列表
     * @return mixed
     */
    public function user()
    {
        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;

        $status = isset($this->params[ 'status' ]) ? $this->params[ 'status' ] : '';
        $search_keys = isset($this->params[ 'search_keys' ]) ? $this->params[ 'search_keys' ] : '';

        $condition = [];
        $condition[] = [ "site_id", "=", $this->site_id ];
        $condition[] = [ "app_module", "=", $this->app_module ];
        if (!empty($search_keys)) {
            $condition[] = [ 'username', 'like', '%' . $search_keys . '%' ];
        }
        if ($status != "") {
            $condition[] = [ "status", "=", $status ];
        }

        $user_model = new UserModel();
        $list = $user_model->getUserPageList($condition, $page, $page_size);
        if (!empty($list['data']['list']) && addon_is_exit('cashier', $this->site_id)) {
            $join = [
                ['store s', 's.store_id = ug.store_id', 'left'],
                ['cashier_auth_group cag', 'cag.group_id = ug.group_id', 'left']
            ];
            foreach ($list['data']['list'] as $k => $item) {
                $list['data']['list'][$k]['user_group_list'] = (new UserGroup())->getUserList([ ['ug.uid', '=', $item['uid'] ] ], 's.store_name,cag.group_name', '', 'ug', $join)['data'];
            }
        }
        return $this->response($list);
    }

    /**
     * 用户信息
     */
    public function info()
    {
        $uid = isset($this->params[ 'uid' ]) ? $this->params[ 'uid' ] : '';

        $user_model = new UserModel();
        $info = $user_model->getUserInfo([ [ 'uid', '=', $uid ], [ 'site_id', '=', $this->site_id ] ]);
        return $this->response($info);
    }

    /**
     * 添加用户
     * @return mixed
     */
    public function addUser()
    {
        $username = isset($this->params[ 'username' ]) ? $this->params[ 'username' ] : '';
        $password = isset($this->params[ 'password' ]) ? $this->params[ 'password' ] : '';
        $group_id = isset($this->params[ 'group_id' ]) ? $this->params[ 'group_id' ] : '';

        $user_model = new UserModel();
        $data = array (
            "username" => $username,
            "password" => $password,
            "group_id" => $group_id,
            "app_module" => $this->app_module,
            "site_id" => $this->site_id
        );
        $result = $user_model->addUser($data, 'add');
        return $this->response($result);
    }

    /**
     * 编辑用户
     * @return mixed
     */
    public function editUser()
    {
        $user_model = new UserModel();

        $group_id = isset($this->params[ 'group_id' ]) ? $this->params[ 'group_id' ] : '';
        $status = isset($this->params[ 'status' ]) ? $this->params[ 'status' ] : '';
        $uid = isset($this->params[ 'uid' ]) ? $this->params[ 'uid' ] : '';

        $condition = array (
            [ "uid", "=", $uid ],
            [ "site_id", "=", $this->site_id ],
            [ "app_module", "=", $this->app_module ],
        );
        $data = array (
            "group_id" => $group_id,
            "status" => $status
        );

        $result = $user_model->editUser($data, $condition);
        return $this->response($result);
    }

    /**
     * 删除用户
     */
    public function deleteUser()
    {
        $uid = isset($this->params[ 'uid' ]) ? $this->params[ 'uid' ] : '';
        $user_model = new UserModel();
        $condition = array (
            [ "uid", "=", $uid ],
            [ "app_module", "=", $this->app_module ],
            [ "site_id", "=", $this->site_id ],
        );
        $result = $user_model->deleteUser($condition);
        return $this->response($result);
    }

    /**
     * 编辑管理员状态
     */
    public function modifyUserStatus()
    {
        $uid = isset($this->params[ 'uid' ]) ? $this->params[ 'uid' ] : '';
        $status = isset($this->params[ 'status' ]) ? $this->params[ 'status' ] : 0;

        $user_model = new UserModel();
        $condition = array (
            [ "uid", "=", $uid ],
            [ "site_id", "=", $this->site_id ],
            [ "app_module", "=", $this->app_module ],
        );
        $result = $user_model->modifyUserStatus($status, $condition);
        return $this->response($result);
    }

    /**
     * 重置密码
     */
    public function modifyPassword()
    {
        $password = isset($this->params[ 'password' ]) ? $this->params[ 'password' ] : '123456';
        $uid = isset($this->params[ 'uid' ]) ? $this->params[ 'uid' ] : $this->uid;

        $site_id = $this->site_id;
        $user_model = new UserModel();
        $res = $user_model->modifyUserPassword($password, [ [ 'uid', '=', $uid ], [ 'site_id', '=', $site_id ] ]);
        return $this->response($res);
    }

    /**
     * 获取用户组列表
     */
    public function groupList()
    {
        $group_model = new Group();
        $group_list = $group_model->getGroupList([ [ "site_id", "=", $this->site_id ], [ "app_module", "=", $this->app_module ] ]);
        return $this->response($group_list);
    }

    /**
     * 用户组权限
     * @return false|string
     */
    public function permission(){
        $permission = empty($this->group_info['menu_array']) ? [] : explode(',', $this->group_info['menu_array']);
        return $this->response($this->success($permission));
    }
}