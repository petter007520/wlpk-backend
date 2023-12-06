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

/**
 * 查询用户信息以user_group为主表
 * @author Administrator
 */
class UserGroup extends BaseModel
{
    /**
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     * @return multitype:string mixed
     */
    public function getUserPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'group_id desc', $field = '*', $alias = 'a', $join = [])
    {
        $list = model('user_group')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * @param  array  $condition
     * @param  string  $field
     * @param  string  $alias
     * @param  array  $join
     * @return array
     */
    public function getUserInfo($condition = [], $field = '*', $alias = 'a', $join = []){
        $list = model('user_group')->getInfo($condition, $field, $alias, $join);
        return $this->success($list);
    }

    /**
     * @param  array  $condition
     * @param  string  $field
     * @param  string  $alias
     * @param  array  $join
     * @return array
     */
    public function getUserList($condition = [], $field = '*', $order = '', $alias = 'a', $join = []){
        $list = model('user_group')->getList($condition, $field, $order, $alias, $join);
        return $this->success($list);
    }

    /**
     * 删除用户
     * @param $condition
     * @return array
     */
    public function deleteUser($condition){
        $res = model('user_group')->delete($condition);
        if ($res) return $this->success();
        return $this->error();
    }
}