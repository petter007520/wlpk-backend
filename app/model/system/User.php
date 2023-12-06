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

use app\model\store\Store;
use think\facade\Session;
use app\model\BaseModel;

/**
 * 管理员模型
 */
class User extends BaseModel
{
    /*******************************************************************用户 编辑查询 start*****************************************************/

    /**
     * 添加用户
     * @param $data
     * @param int $store_id
     * @param string $source_type register 注册  add 添加
     * @return array
     */
    public function addUser($data, $store_id = 0, $source_type = 'register')
    {

        $site_id = isset($data[ 'site_id' ]) ? $data[ 'site_id' ] : '';
        $app_module = isset($data[ 'app_module' ]) ? $data[ 'app_module' ] : '';
        $member_id = isset($data[ 'member_id' ]) ? $data[ 'member_id' ] : 0;

        if ($site_id === '') return $this->error('', 'REQUEST_SITE_ID');
        if ($app_module === '') return $this->error('', 'REQUEST_APP_MODULE');
        if (empty($data[ "username" ])) return $this->error('', '用户名不能为空');
        if (empty($data[ "password" ])) return $this->error('', '密码不能为空');

        //判断 用户名 是否存在
        $user_info = model('user')->getInfo(
            [
                [ 'username', "=", $data[ "username" ] ],
                [ 'site_id', '=', $site_id ],
                [ 'app_module', '=', $app_module ]
            ]
        );

        if($source_type == 'add'){
            if (!empty($user_info)) {
                return $this->error('', '账号已存在');
            }
        }else{
            if (!empty($user_info)) {
                if (data_md5($data[ "password" ]) == $user_info[ 'password' ]) {
                    return $this->success(2);
                } else {
                    return $this->error('', '账号已存在');
                }
            }
        }

        if ($member_id > 0) {
            $temp_condition = array (
                "app_module" => $data[ "app_module" ],
                "member_id" => $member_id
            );
            $temp_count = model('user')->getCount($temp_condition, 'uid');
            if ($temp_count > 0) {
                return $this->error('', 'USERNAME_EXISTED');
            }
        }

        $group_id = isset($data[ 'group_id' ]) ? $data[ 'group_id' ] : 0;
        if ($group_id > 0) {
            $group_model = new Group();
            $group_info = $group_model->getGroupInfo([ [ "group_id", "=", $group_id ], [ "site_id", "=", $site_id ], [ "app_module", "=", $app_module ] ], "group_name")['data'];
            $data[ "group_name" ] = $group_info[ "group_name" ];
        }

        $data[ "password" ] = data_md5($data[ "password" ]);
        $data[ "create_time" ] = time();

        model("user")->startTrans();
        try {
            $uid = model("user")->add($data);
            if ($uid === false) {
                model("user")->rollback();
                return $this->error('', 'UNKNOW_ERROR');
            }
            if (isset($data['store']) && !empty($data['store'])) {
                $store_user_list = [];
                foreach ($data['store'] as $item) {
                    if (empty($item['store_id'])) {
                        model("user")->rollback();
                        return $this->error('', '门店id不能为空');
                    }
                    if (empty($item['group_id'])) {
                        model("user")->rollback();
                        return $this->error('', '门店角色不能为空');
                    }
                    array_push($store_user_list, [
                        'uid' => $uid,
                        'site_id' => $data['site_id'],
                        'store_id' => $item['store_id'],
                        'group_id' => $item['group_id'],
                        'create_time' => time(),
                        'app_module' => 'store'
                    ]);
                }
                model('user_group')->addList($store_user_list);
            }
            model("user")->commit();
            return $this->success($uid);
        } catch (\Exception $e) {
            model("user")->rollback();
            return $this->error('', '用户添加失败');
        }
    }

    public function getUserColumn($condition = [], $field = '')
    {
        $res = model('user')->getColumn($condition, $field);
        return $res;
    }

    /**
     * 编辑用户
     * @param $data
     * @param $condition
     */
    public function editUser($data, $condition, $store_id = 0)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        $app_module = isset($check_condition[ 'app_module' ]) ? $check_condition[ 'app_module' ] : '';
        $uid = isset($check_condition[ 'uid' ]) ? $check_condition[ 'uid' ] : '';
        if ($uid === '') {
            return $this->error('', '缺少必须参数UID');
        }
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        if ($app_module === '') {
            return $this->error('', 'REQUEST_APP_MODULE');
        }
        $group_id = isset($data[ 'group_id' ]) ? $data[ 'group_id' ] : 0;
        if ($group_id > 0) {
            $group_model = new Group();
            $group_info = $group_model->getGroupInfo([ [ "group_id", "=", $group_id ], [ "site_id", "=", $site_id ], ['app_module', '=', $app_module] ], "group_name")['data'];
            $data[ "group_name" ] = $group_info[ "group_name" ] ?? '';
        }
        model('user')->startTrans();
        try {
            $res = model("user")->update($data, $condition);
            if ($res === false) {
                model('user')->rollback();
                return $this->error('', 'UNKNOW_ERROR');
            }

            model('user_group')->delete([ ['site_id', '=', $site_id ], ['uid', '=', $uid ], ['app_module', '=', 'store' ] ]);
            if (isset($data['store']) && !empty($data['store'])) {
                $store_user_list = [];
                foreach ($data['store'] as $item) {
                    if (empty($item['store_id'])) {
                        model("user")->rollback();
                        return $this->error('', '门店id不能为空');
                    }
                    if (empty($item['group_id'])) {
                        model("user")->rollback();
                        return $this->error('', '门店角色不能为空');
                    }
                    array_push($store_user_list, [
                        'uid' => $uid,
                        'site_id' => $site_id,
                        'store_id' => $item['store_id'],
                        'group_id' => $item['group_id'],
                        'create_time' => time(),
                        'app_module' => 'store'
                    ]);
                }
                model('user_group')->addList($store_user_list);
            }

            model("user")->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model("user")->rollback();
            return $this->error('', '用户编辑失败');
        }
    }

    /**
     * 编辑用户状态
     * @param $status
     * @param $condition
     */
    public function modifyUserStatus($status, $condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $data = array (
            "status" => $status,
            "update_time" => time()
        );
        $res = model('user')->update($data, $condition);
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 重置密码
     * @param $password
     * @param $condition
     * @return array|\multitype
     */
    public function modifyUserPassword($password, $condition)
    {
        $res = model('user')->update([ 'password' => data_md5($password) ], $condition);
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 系统用户修改密码
     * @param $condition
     * @param $new_password
     * @return array
     */
    public function modifyAdminUserPassword($condition , $new_password)
    {
        if(addon_is_exit("demo"))
        {
            return $this->error('', '权限不足，请联系客服');
        }
        $res = model('user')->getInfo($condition, "uid,password");
        if (!empty($res)) {
            $data = array (
                'password' => data_md5($new_password)
            );
            $res = model('user')->update($data, $condition);
            return $this->success($res, 'SUCCESS');
        } else {
            return $this->error('', 'PASSWORD_ERROR');
        }
    }

    /**
     * 删除用户
     * @param array $condition
     * @return multitype:string mixed
     */
    public function deleteUser($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $app_module = isset($check_condition[ 'app_module' ]) ? $check_condition[ 'app_module' ] : '';
        $uid = isset($check_condition[ 'uid' ]) ? $check_condition[ 'uid' ] : '';
        if ($uid === '') {
            return $this->error('', '缺少必须参数UID');
        }
        if ($app_module === '') {
            return $this->error('', 'REQUEST_APP_MODULE');
        }
        $res = model('user')->delete($condition);
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        model('user_group')->delete([ ['uid', '=', $uid ] ]);
        return $this->success($res);
    }

    /**
     * 清除后台所有用户的登录信息
     * @param $app_module
     * @param $site_id
     * @return array
     */
    public function deleteUserLoginInfo($app_module,$site_id)
    {
        $dir = './runtime/session';
        $this->deldir($dir);
        Session::delete($app_module . "_" . $site_id . ".uid");
        return $this->success();
    }

    public function deldir($dir) {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if($file != "." && $file!="..") {
                $fullpath = $dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
            }
        }
        closedir($dh);

    }

    /**
     * 获取用户信息
     * @param $condition
     * @param string $field
     * @return \multitype
     */
    public function getUserInfo($condition, $field = "uid, app_module, site_id, group_id, group_name, username, member_id, create_time, update_time, status, login_time, login_ip, is_admin")
    {
        $info = model('user')->getInfo($condition, $field);
        if (!empty($info)) {
            if (isset($info['uid'])) {
                $join = [
                    ['store s', 's.store_id = ug.store_id', 'inner'],
                    ['cashier_auth_group g', 'g.group_id = ug.group_id', 'inner']
                ];
                $info['user_group_list'] = model('user_group')->getList([ ['ug.uid', '=', $info['uid'] ] ], 'ug.store_id,ug.group_id,s.store_name,g.menu_array', 's.is_default desc', 'ug', $join);
            }
        }
        return $this->success($info);
    }

    /**
     * 获取用户列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     * @return multitype:string mixed
     */
    public function getUserList($condition = [], $field = 'uid, app_module, site_id, group_id, username, member_id, create_time, update_time, status, login_time, login_ip, is_admin, group_name', $order = '', $limit = null)
    {
        $list = model('user')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取会员分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     * @return multitype:string mixed
     */
    public function getUserPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = 'uid, app_module, site_id, group_id, username, member_id, create_time, update_time, status, login_time, login_ip, is_admin, group_name, login_time')
    {
        $list = model('user')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取站点用户分页列表
     * @param unknown $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     */
    public function getSiteUserPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '')
    {
        $field = ' nu.uid, nu.app_module, nu.app_group,
            nu.is_admin, nu.site_id, nu.group_id, nu.group_name, nu.username, nu.member_id, nu.create_time, 
            nu.update_time, nu.status, nu.login_time, nu.login_ip, ns.site_name,';
        $alias = 'nu';
        $join = [
            [
                'shop ns',
                'nu.site_id = ns.site_id',
                'left'
            ],
        ];
        $list = model("user")->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 检测权限
     * @param $url
     * @param $app_module
     * @param $group_info
     * @param string $addon
     * @return bool
     */
    public function checkAuth($url, $app_module, $group_info, $addon = '')
    {

        $auth_control = event("AuthControl", ['url' => $url, 'app_module' => $app_module], 1);
        if(!empty($auth_control))
        {
            if($auth_control['code'] < 0)
            {
                return false;
            }
        }

        $menu_model = new Menu();
        $menu_info = $menu_model->getMenuInfoByUrl($url, $app_module, $addon);
        if (!empty($menu_info[ 'data' ])) {
            if ($group_info[ 'is_system' ] == 1) {
                return true;
            }
            if ($menu_info[ 'data' ][ 'is_control' ] == 0) {
                return true;
            }

            //权限组
            if (empty($group_info)) {
                return false;
            }
            if (strpos(',' . $group_info[ 'menu_array' ] . ',', ',' . $menu_info[ 'data' ][ 'name' ] . ',') !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            $count = $menu_model->getMenuCount([['url', '=', $url]]);
            if($count['data'] > 0)
            {
                return false;
            }
            return true;
        }
    }

    /**
     * 获取相邻菜单
     * @param $url
     * @param $app_module
     * @param $group_info
     * @return multitype|array
     */
    public function getRedirectUrl($url, $app_module, $group_info, $addon = '')
    {
        if ($this->checkAuth($url, $app_module, $group_info) == false) {

            $menu_model = new Menu();
            $menu_info = $menu_model->getMenuInfoByUrl($url, $app_module, $addon)[ 'data' ];

            if(empty($menu_info))
            {
                //针对空值特殊
                $menu_count = $menu_model->getMenuCount([ [ 'url', "=", $url ] ]);
                if($menu_count['data'] > 0)
                {
                    return [
                        'url' => 'shop/login/login'
                    ];
                }
            }
            $menu_count = $menu_model->getMenuCount([ [ 'url', "=", $url ], [ 'app_module', "=", $app_module ] ])[ 'data' ];

            if ($menu_count == 1) {
                return [];
            }

            if ($menu_info[ 'level' ] == 1) {
            } elseif ($menu_info[ 'level' ] == 2) {
                $menu_second_info = $menu_model->getMenuInfo([ [ 'parent', '=', $menu_info[ 'parent' ] ], [ 'level', '=', 2 ], [ 'is_show', '=', 1 ], [ 'name', 'in', $group_info[ 'menu_array' ], [ 'app_module', '=', $app_module ] ] ])[ 'data' ];

                if (!empty($menu_second_info)) {
                    if($menu_info['addon'] == $menu_second_info['addon']){
                        return $menu_second_info;
                    }
                }
            } elseif ($menu_info[ 'level' ] == 3) {
                $check_menu_info = $menu_model->getMenuInfo([ [ 'parent', '=', $menu_info[ 'parent' ] ], [ 'level', '=', 3 ], [ 'is_show', '=', 1 ], [ 'name', 'in', $group_info[ 'menu_array' ], [ 'app_module', '=', $app_module ] ] ])[ 'data' ];

                if (!empty($check_menu_info)) {
                    if($menu_info['addon'] == $check_menu_info['addon']){
                        return $check_menu_info;
                    }
                } else {
                    $parent_menu_info = $menu_model->getMenuInfo([ [ 'name', '=', $menu_info[ 'parent' ] ], [ 'is_show', '=', 1 ], [ 'app_module', '=', $app_module ] ])[ 'data' ];

                    $check_menu_info = $menu_model->getMenuInfo([ [ 'parent', '=', $parent_menu_info[ 'parent' ] ], [ 'is_show', '=', 1 ], [ 'name', 'in', $group_info[ 'menu_array' ], [ 'app_module', '=', $app_module ] ] ])[ 'data' ];
                    if (!empty($check_menu_info)) {
                        if($menu_info['addon'] == $check_menu_info['addon']){
                            return $check_menu_info;
                        }
                    }
                }
            } elseif ($menu_info[ 'level' ] == 4){
                $check_menu_info = $menu_model->getMenuInfo([ [ 'parent', '=', $menu_info[ 'parent' ] ], [ 'level', '=', 3 ], [ 'is_show', '=', 1 ], [ 'name', 'in', $group_info[ 'menu_array' ], [ 'app_module', '=', $app_module ] ] ])[ 'data' ];
                if (!empty($check_menu_info)) {
                    if($menu_info['addon'] == $check_menu_info['addon']){
                        return $check_menu_info;
                    }
                } else {
                    $parent_menu_info = $menu_model->getMenuInfo([ [ 'name', '=', $menu_info[ 'parent' ] ], [ 'is_show', '=', 1 ], [ 'app_module', '=', $app_module ] ])[ 'data' ];

                    $check_menu_info = $menu_model->getMenuInfo([ [ 'parent', '=', $parent_menu_info[ 'parent' ] ], [ 'is_show', '=', 1 ], [ 'name', 'in', $group_info[ 'menu_array' ], [ 'app_module', '=', $app_module ] ] ])[ 'data' ];
                    if (!empty($check_menu_info)) {
                        if($menu_info['addon'] == $check_menu_info['addon']){
                            return $check_menu_info;
                        }
                    }
                }
            }
        }
        return [];
    }

    /*******************************************************************用户 编辑查询 end*****************************************************/

    /*******************************************************************用户注册登录 start*****************************************************/

    /**
     * 用户登录
     * @param unknown $mobile
     * @param unknown $password
     */
    public function login($username, $password, $app_module = 'shop', $site_id = 0)
    {

        $user_condition = [
            [ 'username', '=', $username ],
            [ 'app_module', '=', $app_module ],
            [ 'site_id', '=', $site_id ]
        ];
        if ($app_module == 'shop') $user_condition[] = ['group_id', '>', 0];
        $user_info = model('user')->getInfo($user_condition);
        if (empty($user_info)) {
            return $this->error('', 'USER_NOT_EXIST');
        }
        if ($user_info['password'] != data_md5($password)) {
            return $this->error('', 'PASSWORD_ERROR');
        }
        if ($user_info[ 'status' ] !== 1) {
            return $this->error([], 'USER_IS_LOCKED');
        }
        $this->initLogin($user_info);
        return $this->success();
    }

    /**
     * 初始化登录
     * @param unknown $user_info
     */
    private function initLogin($user_info)
    {
        $time = time();
        //初始化登录信息
        $auth = array (
            'uid' => $user_info[ 'uid' ],
            'username' => $user_info[ 'username' ],
            'create_time' => $user_info[ 'create_time' ],
            'status' => $user_info[ 'status' ],
            'group_id' => $user_info[ "group_id" ],
            'site_id' => $user_info[ "site_id" ],
            'app_group' => $user_info[ "app_group" ],
            'is_admin' => $user_info[ 'is_admin' ],
            'login_time' => $time,
            'login_ip' => request()->ip(),
            'sys_uid' => $user_info[ 'sys_uid' ]
        );

        //更新登录记录
        $data = [
            'login_time' => time(),
            'login_ip' => request()->ip(),
        ];
        model('user')->update($data, [ [ 'uid', "=", $user_info[ 'uid' ] ] ]);
        Session::set($user_info[ 'app_module' ] . "_" . $user_info[ 'site_id' ] . ".uid", $user_info[ 'uid' ]);
        Session::set($user_info[ 'app_module' ] . "_" . $user_info[ 'site_id' ] . ".user_info", $auth);
        Session::set('app_module' . "_" . $user_info[ 'site_id' ] . ".login_module", $user_info[ 'app_module' ]);
        $this->addUserLog($user_info[ 'uid' ], $user_info[ 'username' ], $user_info[ 'site_id' ], "用户登录", []);//添加日志
    }

    /**
     * uni-app端用户登录
     * @param $username
     * @param $password
     * @param $app_module
     * @return array
     */
    public function uniAppLogin($username, $password, $app_module)
    {
        $time = time();
        // 验证参数 预留
        $user_info = $this->getUserInfo([ [ 'username', "=", $username ] ], 'uid,app_module,site_id,group_id,group_name,username,status,is_admin,password')['data'];
        if (empty($user_info)) {
            return $this->error('', 'USER_LOGIN_ERROR');
        } else if (data_md5($password) !== $user_info[ 'password' ]) {
            return $this->error([], 'PASSWORD_ERROR');
        } else if ($user_info[ 'status' ] !== 1) {
            return $this->error([], 'USER_IS_LOCKED');
        }

        // 查询默认门店
        if ($app_module == 'store' && $user_info['is_admin']) {
            $store_info = (new Store())->getDefaultStore($user_info[ 'site_id' ])['data'] ?? [];
            if (empty($user_info['user_group_list'])) {
                $user_info['user_group_list'] = [ $store_info ];
            } else {
                $store_list = array_column($user_info['user_group_list'], null, 'store_id');
                if (!isset($store_list[ $store_info['store_id'] ])) array_push($user_info['user_group_list'], $store_info);
            }
        }

        //更新登录记录
        $data = [
            'login_time' => $time,
            'login_ip' => request()->ip(),
        ];
        model('user')->update($data, [ [ 'uid', "=", $user_info[ 'uid' ] ] ]);

        $this->addUserLog($user_info[ 'uid' ], $user_info[ 'username' ], $user_info[ 'site_id' ], "用户登录", []); //添加日志

        unset($user_info['password']);
        return $this->success($user_info);
    }

    /**
     * 获取当前登录uid
     * @param $app_module
     * @param int $site_id
     * @return mixed
     */
    public function uid($app_module, $site_id = 0)
    {
        return Session::get($app_module . "_" . $site_id . ".uid");
    }

    /**
     * 登录模块
     */
    public function loginModule($site_id)
    {
        $login_module = Session::get('app_module' . "_" . $site_id . ".login_module");
        if(empty($login_module) || !strstr($_SERVER["REQUEST_URI"], 'store/store'))
        {
            return 'shop';
        }else{
            return $login_module;
        }
    }

    /**
     * 获取当前登录管理员信息
     * @param $app_module
     * @param int $site_id
     * @return mixed
     */
    public function userInfo($app_module, $site_id = 0)
    {
        return Session::get($app_module . "_" . $site_id . ".user_info");
    }

    /**
     * 清除登录信息
     */
    public function clearLogin($app_module, $site_id = 0)
    {
        Session::delete($app_module . "_" . $site_id);
    }
    /*******************************************************************用户注册登录 end*****************************************************/

    /*******************************************************************用户日志 start*****************************************************/

    /**
     * 添加用户日志
     * @param $data
     */
    public function addUserLog($uid, $username, $site_id, $action_name, $data = [])
    {

        $url = request()->parseUrl();
        $ip = request()->ip();
        $log = array (
            "uid" => $uid,
            "username" => $username,
            "site_id" => $site_id,
            "url" => $url,
            "ip" => $ip,
            "data" => json_encode($data),
            "action_name" => $action_name,
            "create_time" => time(),
        );
        $res = model("user_log")->add($log);
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 删除用户日志
     */
    public function deleteUserLog($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        $res = model("user_log")->delete($condition);
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 获用户员日志分页列表
     *
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     * @return multitype:string mixed
     */
    public function getUserLogPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = 'username, site_id, url, id, uid, data, ip, action_name, create_time')
    {
        $list = model('user_log')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }
    /*******************************************************************用户日志 end*****************************************************/
}