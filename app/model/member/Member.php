<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\member;

use addon\memberregister\model\Register as RegisterModel;
use app\model\BaseModel;
use app\model\message\Sms;
use app\model\system\Stat;
use app\model\upload\Upload;
use app\model\system\Address;
use think\facade\Cache;
use think\facade\Db;
use addon\wechat\model\Fans;

/**
 * 会员管理
 */
class Member extends BaseModel
{

    /**
     * 添加会员(注意等级名称)
     * @param $data
     * @return array
     */
    public function addMember($data)
    {
        if ($data[ 'username' ]) {
            $count = model('member')->getCount([
                [ 'username', '=', $data[ 'username' ] ],
                [ 'site_id', '=', $data[ 'site_id' ] ],
                [ 'is_delete', '=', 0 ]
            ]);
            if ($count > 0) {
                return $this->error('', 'USERNAME_EXISTED');
            }
        }

        if ($data[ 'mobile' ]) {
            $count = model('member')->getCount([
                [ 'mobile', '=', $data[ 'mobile' ] ],
                [ 'site_id', '=', $data[ 'site_id' ] ],
                [ 'is_delete', '=', 0 ]
            ]);
            if ($count > 0) {
                return $this->error('', 'MOBILE_EXISTED');
            }
        }

        if ($data[ 'email' ]) {
            $count = model('member')->getCount([
                [ 'email', '=', $data[ 'email' ] ],
                [ 'site_id', '=', $data[ 'site_id' ] ],
                [ 'is_delete', '=', 0 ]
            ]);
            if ($count > 0) {
                return $this->error('', 'EMAIL_EXISTED');
            }
        }

        $register_model = new RegisterModel();

        $register_config = $register_model->getConfig($data[ 'site_id' ])[ 'data' ];
        if ($register_config[ 'is_use' ]) {
            $data[ 'can_receive_registergift' ] = 1;
        }
        if ($data['member_level']) {
            $data['is_member'] = 1;
            $data['member_time'] = time();
        }

        $res = model('member')->add($data);
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        //添加统计
        $stat = new Stat();
        $stat->switchStat([ 'type' => 'add_member', 'data' => [ 'member_count' => 1, 'site_id' => $data[ 'site_id' ] ] ]);
//        $stat->addShopStat([ 'member_count' => 1, 'site_id' => $data['site_id'] ]);
        return $this->success($res);
    }

    /**
     * 修改会员(注意标签与等级名称)
     * @param $data
     * @param $condition
     * @return array
     */
    public function editMember($data, $condition)
    {
        if (isset($data[ 'mobile' ]) && $data[ 'mobile' ] != '') {
            $check_condition = array_column($condition, 2, 0);
            $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : 0;
            $member_id = isset($check_condition[ 'member_id' ]) ? $check_condition[ 'member_id' ] : 0;
            $mobile_is_exist = model('member')->getCount([ [ 'mobile', '=', $data[ 'mobile' ] ], [ 'member_id', '<>', $member_id ], [ 'site_id', '=', $site_id ], [ 'is_delete', '=', 0 ] ]);
            if ($mobile_is_exist > 0) return $this->error('', 'MOBILE_EXISTED');
        }
        $res = model('member')->update($data, $condition);
        if ($res === false) {
            return $this->error('', 'SAVE_FAIL');
        }

        return $this->success($res);
    }

    /**
     * 修改用户名
     * @param $member_id
     * @param $username
     */
    public function editUsername($member_id, $site_id, $username)
    {
        $member_info = model('member')->getInfo([ [ 'member_id', '=', $member_id ] ], 'username,is_edit_username');

        if (empty($member_info)) return $this->error('', '未查找到该用户');
        if ($member_info[ 'username' ] == $username) return $this->error('', '与原用户名一致，无需修改');
        if (!$member_info[ 'is_edit_username' ]) return $this->error('', '用户名不可进行修改');

        $count = model('member')->getCount([ [ 'username', '=', $username ], [ 'site_id', '=', $site_id ] ], 'member_id');
        if ($count != 0) return $this->error('', '该用户名已存在');

        model('member')->update([ 'username' => $username, 'is_edit_username' => 0 ], [ [ 'member_id', '=', $member_id ] ]);
        return $this->success();
    }

    /**
     * 修改会员状态
     * @param $status
     * @param $condition
     * @return array
     */
    public function modifyMemberStatus($status, $condition)
    {
        $res = model('member')->update([
            'status' => $status
        ], $condition);
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }

        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : 0;
        Cache::set('member_blacklist_' . $site_id, null);
        return $this->success($res);
    }

    /**
     * 修改会员标签
     * @param $label_ids
     * @param $condition
     * @return array
     */
    public function modifyMemberLabel($label_ids, $condition)
    {
        //查询会员标签
        $label_list = model("member_label")->getList([ [ 'label_id', 'in', $label_ids ] ], 'label_id,label_name');

        $label_ids = '';
        $label_names = '';
        if (!empty($label_list)) {
            foreach ($label_list as $k => $v) {
                $label_ids = $label_ids . $v[ 'label_id' ] . ',';
                $label_names = $label_names . $v[ 'label_name' ] . ',';
            }
        }
        $res = model('member')->update([
            'member_label' => $label_ids,
            'member_label_name' => $label_names
        ], $condition);
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 重置密码
     * @param string $password
     * @param $condition
     * @return array
     */
    public function resetMemberPassword($password, $condition)
    {
        $res = model('member')->update([
            'password' => data_md5($password)
        ], $condition);

        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 修改密码
     * @param $member_id
     * @param $old_password
     * @param $new_password
     * @return array
     */
    public function modifyMemberPassword($member_id, $old_password, $new_password)
    {
        $res = model('member')->getCount([
            [ 'password', '=', data_md5($old_password) ],
            [ 'member_id', '=', $member_id ],
        ]);
        if ($res > 0) {
            $res = model('member')->update([
                'password' => data_md5($new_password)
            ], [ [ 'member_id', '=', $member_id ] ]);
            if ($res === false) {
                return $this->error('', 'RESULT_ERROR');
            }
            return $this->success($res);
        } else {
            return $this->error('', 'PASSWORD_ERROR');
        }
    }

    /**
     * 删除会员（应用后台）
     * @param $condition
     * @return array
     */
    public function deleteMember($condition)
    {
        $res = model('member')->delete($condition);
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 获取会员信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getMemberInfo($condition = [], $field = '*')
    {
        $condition[] = [ 'is_delete', '=', 0 ];
        $member_info = model('member')->setIsCache(0)->getInfo($condition, $field);

        if (!empty($member_info) && empty($member_info[ 'wx_openid' ]) && !empty($member_info[ 'wx_unionid' ])) {
            $fans_model = new Fans();
            $fans_condition[] = [ "unionid", "=", $member_info[ 'wx_unionid' ] ];
            $fans_info = $fans_model->getFansInfo($fans_condition);
            if (!empty($fans_info[ 'data' ])) {
                $member_info[ 'wx_openid' ] = $fans_info[ 'data' ][ 'openid' ];
            }
        }

        return $this->success($member_info);
    }

    /**
     * 获取会员信息
     * @param int $member_id
     * @return array
     */
    public function getMemberDetail($member_id, $site_id)
    {
        $field = 'member_id,source_member,username,nickname,mobile,email,status,headimg,member_level,member_level_name,member_label,member_label_name,qq,realname,sex,location,birthday,reg_time,point,balance,growth,balance_money,account5,pay_password,member_level_type';
        $member_info = model('member')->getInfo([ [ 'member_id', '=', $member_id ], [ 'site_id', '=', $site_id ] ], $field);
        if (!empty($member_info)) {
            $member_info[ 'balance_total' ] = $member_info[ 'balance' ] + $member_info[ 'balance_money' ];
            return $this->success($member_info);
        }
        return $this->error();
    }

    /**
     * 获取会员数量
     * @param array $condition
     * @return array
     */
    public function getMemberCount($condition = [])
    {
        $condition[] = [ 'is_delete', '=', 0 ];
        $member_info = model('member')->getCount($condition);
        return $this->success($member_info);
    }

    /**
     * 获取会员字段总和
     * @param array $condition
     * @return array
     */
    public function getMemberSum($condition, $field)
    {
        $member_info = model('member')->getSum($condition, $field);
        return $this->success($member_info);
    }

    /**
     * 获取会员分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getMemberPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $condition[] = [ 'is_delete', '=', 0 ];
        $list = model('member')->pageList($condition, $field, $order, $page, $page_size, '', '', '');
        return $this->success($list);
    }

    /**
     * 获取会员列表
     * @param array $where
     * @param bool $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @param string $group
     * @param null $limit
     * @return array
     */
    public function getMemberList($where = [], $field = true, $order = '', $alias = 'a', $join = [], $group = '', $limit = null)
    {
        $where[] = [ 'is_delete', '=', 0 ];
        $res = model('member')->getList($where, $field, $order, $alias, $join, $group, $limit);
        return $this->success($res);
    }

    /**
     * 客户设置成会员
     * @param $member_id
     */
    public function makeMember($member_id)
    {
        $res = model('member')->update(['is_member' => 1, 'member_time' => time()], [['member_id', '=', $member_id]] );
        return $res;
    }

    /**
     * 检测成为会员条件
     * @param $member_id
     */
    public function checkMember($member_id)
    {

        $member_info = model('member')->getInfo([['member_id', '=', $member_id]], 'member_level');
        if(empty($member_info))
        {
            return false;
        }
        if($member_info['member_level'] == 0)
        {
            return false;
        }
        return true;
    }

    /**
     * 检测当前的memberid是否是有效会员
     * @param $member_id
     * @param $site_id
     */
    public function checkMemberByMemberId($member_id, $site_id)
    {
        //检测会员是否以被删除
        $member_info = model('member')->getInfo([[ 'member_id', '=', $member_id ], [ 'is_delete', '=', 0 ],['status', '<>', 0], [ 'site_id', '=', $site_id ]], 'member_id');
        $check_member_id = $member_info['member_id'] ?? 0;
        if(empty($check_member_id))
        {
            return false;
        }
        return true;

    }

    /**
     * 绑定发送验证码
     * @param $data
     * @return array|mixed|void
     */
    public function bindCode($data)
    {
        //发送短信
        $sms_model = new Sms();
        $var_parse = array (
            "code" => $data[ "code" ],//验证码
        );
        $data[ "sms_account" ] = $data[ "mobile" ] ?? '';//手机号
        $data[ "var_parse" ] = $var_parse;
        $sms_result = $sms_model->sendMessage($data);
        if ($sms_result[ "code" ] < 0)
            return $sms_result;

        return $this->success();
    }

    /**
     * 找回密码发送验证码
     * @param $data
     * @return array|mixed|void
     */
    public function findCode($data)
    {
        //发送短信
        $sms_model = new Sms();
        $var_parse = array (
            "code" => $data[ "code" ],//验证码
        );
        $data[ "sms_account" ] = $data[ "mobile" ] ?? '';//手机号
        $data[ "var_parse" ] = $var_parse;
        $sms_result = $sms_model->sendMessage($data);
        if ($sms_result[ "code" ] < 0)
            return $sms_result;

        return $this->success();
    }

    /**
     * 设置会员交易密码
     * @param unknown $member_id
     * @param unknown $old_password
     * @param unknown $new_password
     */
    public function modifyMemberPayPassword($member_id, $password)
    {
        $res = model('member')->update([
            'pay_password' => data_md5($password)
        ], [ [ 'member_id', '=', $member_id ] ]);
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 会员是否已设置支付密码
     * @param unknown $member_id
     */
    public function memberIsSetPayPassword($member_id)
    {
        $info = model('member')->getInfo([ [ 'member_id', '=', $member_id ] ], 'pay_password');
        if (empty($info[ 'pay_password' ])) return $this->success(0);
        else return $this->success(1);
    }

    /**
     * 检测会员支付密码是否正确
     * @param $member_id
     * @param $pay_password
     * @return array
     */
    public function checkPayPassword($member_id, $pay_password)
    {
        $res = model('member')->getCount([
            [ 'pay_password', '=', data_md5($pay_password) ],
            [ 'member_id', '=', $member_id ]
        ]);
        if ($res > 0) {
            return $this->success($res);
        } else {
            return $this->error('', 'PAY_PASSWORD_ERROR');
        }
    }

    /**
     * 找回密码发送验证码
     * @param $data
     * @return array|mixed|void
     */
    public function paypasswordCode($data)
    {
        //发送短信
        $sms_model = new Sms();
        $var_parse = array (
            "code" => $data[ "code" ],//验证码
        );
        $member_info_result = $this->getMemberInfo([ [ "member_id", "=", $data[ "member_id" ] ] ], "mobile");
        $member_info = $member_info_result[ "data" ];
        $data[ "sms_account" ] = $member_info[ "mobile" ] ?? '';//通过member_id获得手机号
        $data[ "var_parse" ] = $var_parse;
        $sms_result = $sms_model->sendMessage($data);
        if ($sms_result[ "code" ] < 0)
            return $sms_result;

        return $this->success();
    }

    /**
     * 拉取用户头像到本地
     * @param unknown $token
     */
    public function pullHeadimg($member_id)
    {
        $member_info = model("member")->getInfo([ [ 'member_id', '=', $member_id ] ], 'headimg');
        if (!empty($member_info[ 'headimg' ]) && is_url($member_info[ 'headimg' ])) {
            $upload = new Upload();
            $res = $upload->setPath("headimg/" . date("Ymd") . '/')->remotePull($member_info[ 'headimg' ]);
            if ($res[ 'code' ] >= 0) {
                model("member")->update([ 'headimg' => $res[ 'data' ][ 'pic_path' ] ], [ [ 'member_id', '=', $member_id ] ]);
            }
        }
    }

    /**
     * 获取店铺会员数量
     * @param array $condition
     * @param string $alias
     * @param unknown $join
     */
    public function getMemberAreaCount($condition, $alias = 'a', $join = [], $group = null)
    {
        $db = Db::name('member')->where($condition);
        if (!empty($join)) {
            $db = $this->parseJoin($db->alias($alias), $join);
        }
        if (!empty($group)) {
            $db = $db->group($group);
        }
        $count = $db->count();
        return $this->success($count);
    }

    /**
     * 按地域分布查询会员数量
     * @param unknown $site_id
     * @param string $handle
     */
    public function getMemberCountByArea($site_id, $handle = false)
    {
        $total_count = $this->getMemberAreaCount([ [ 'site_id', '=', $site_id ], [ 'is_delete', '=', 0 ] ]);

        $address = new Address();
        $list = $address->getAreaList([ [ 'pid', '=', 0 ] ], 'id,shortname', 'sort asc');

        $data = [];

        if ($total_count[ 'data' ]) {
            foreach ($list[ 'data' ] as $item) {
                $count = $this->getMemberAreaCount([ [ 'site_id', '=', $site_id ], [ 'province_id', '=', $item[ 'id' ] ], [ 'is_delete', '=', 0 ] ]);
                if ($handle) {
                    if ($count[ 'data' ] > 0) {
                        array_push($data, [
                            'name' => $item[ 'shortname' ],
                            'value' => $count[ 'data' ],
                            'ratio' => $count[ 'data' ] > 0 ? sprintf("%.2f", $count[ 'data' ] / $total_count[ 'data' ] * 100) : 0
                        ]);
                    }
                } else {
                    array_push($data, [
                        'name' => $item[ 'shortname' ],
                        'value' => $count[ 'data' ],
                        'ratio' => $count[ 'data' ] > 0 ? sprintf("%.2f", $count[ 'data' ] / $total_count[ 'data' ] * 100) : 0
                    ]);
                }
            }
        }

        if ($handle) {
            array_multisort(array_column($data, 'value'), SORT_DESC, $data);
        }

        return $this->success([
            'page_count' => 1,
            'count' => $total_count[ 'data' ],
            'list' => $data
        ]);
    }

    /**
     * 处理表连接
     * @param unknown $db_obj
     * @param unknown $join
     */
    protected function parseJoin($db_obj, $join)
    {
        foreach ($join as $item) {
            list($table, $on, $type) = $item;
            $type = strtolower($type);
            switch ( $type ) {
                case "left":
                    $db_obj = $db_obj->leftJoin($table, $on);
                    break;
                case "inner":
                    $db_obj = $db_obj->join($table, $on);
                    break;
                case "right":
                    $db_obj = $db_obj->rightjoin($table, $on);
                    break;
                case "full":
                    $db_obj = $db_obj->fulljoin($table, $on);
                    break;
                default:
                    break;
            }
        }
        return $db_obj;
    }

    /**
     *
     */
    public function getMemberImportLogList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {

        $list = model('member_import_log')->pageList($condition, $field, $order, $page, $page_size, '', '', '');

        if (!empty($list[ 'list' ])) {
            foreach ($list[ 'list' ] as $key => $val) {
                $list[ 'list' ][ $key ][ "create_time" ] = date('Y-m-d H:i:s', $val[ 'create_time' ]);
            }
        }

        return $this->success($list);
    }

    /**
     * @param $param
     * @param $site_id
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \think\exception\PDOException
     */
    public function importMember($param, $site_id)
    {

        $PHPReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');

        //载入文件
        $PHPExcel = $PHPReader->load($param[ 'path' ]);

        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet = $PHPExcel->getSheet(0);

        //获取总行数
        $allRow = $currentSheet->getHighestRow();

        if ($allRow < 2) {
            return $this->error('', '导入了一个空文件');
        }

        $index = $param[ 'index' ];

        //每次导入100条
        $length = $index * 100;
        if ($index == 1) {
            $num = 2;
            $success_num = 0;
            $error_num = 0;

            $data_record = [
                "member_num" => ( $allRow - 1 ),
                "success_num" => 0,
                "error_num" => 0,
                "create_time" => time(),
                "status_name" => "等待导入"
            ];
            $record = model('member_import_record')->add($data_record);

        } else {
            $num = ( ( $index - 1 ) * 100 ) + 1;
            $success_num = $param[ 'success_num' ];
            $error_num = $param[ 'error_num' ];
            $record = $param[ 'record' ];
        }
        $type_num = 0;
        model('member')->startTrans();
        try {

            for ($i = $num; $i <= $length; $i++) {

                if ($i > $allRow) {
                    break;
                }
                $type_num = $i;
                //用户名
                $username = $PHPExcel->getActiveSheet()->getCell('A' . $i)->getValue();
                $username = trim($username, ' ');

                //手机号
                $mobile = $PHPExcel->getActiveSheet()->getCell('B' . $i)->getValue();
                $mobile = trim($mobile, ' ');

                //昵称
                $nickname = $PHPExcel->getActiveSheet()->getCell('C' . $i)->getValue();
                $nickname = trim($nickname, ' ');

                //密码（明文）
                $password = $PHPExcel->getActiveSheet()->getCell('D' . $i)->getValue();
                $password = trim($password, ' ');

                //微信公众号openid
                $wx_openid = $PHPExcel->getActiveSheet()->getCell('E' . $i)->getValue();
                $wx_openid = trim($wx_openid, ' ');

                //微信小程序openid
                $weapp_openid = $PHPExcel->getActiveSheet()->getCell('F' . $i)->getValue();
                $weapp_openid = trim($weapp_openid, ' ');

                //真实姓名
                $realname = $PHPExcel->getActiveSheet()->getCell('G' . $i)->getValue();
                $realname = trim($realname, ' ');

                //积分
                $point = $PHPExcel->getActiveSheet()->getCell('H' . $i)->getValue();
                $point = trim($point, ' ');

                //成长值
                $growth = $PHPExcel->getActiveSheet()->getCell('I' . $i)->getValue();
                $growth = trim($growth, ' ');

                //余额(可提现)
                $balance_money = $PHPExcel->getActiveSheet()->getCell('J' . $i)->getValue();
                $balance_money = trim($balance_money, ' ');

                //余额(不可提现)
                $balance = $PHPExcel->getActiveSheet()->getCell('K' . $i)->getValue();
                $balance = trim($balance, ' ');

                //会员等级(id)
                $membeer_level_id = $PHPExcel->getActiveSheet()->getCell('L' . $i)->getValue();
                $membeer_level_id = trim($membeer_level_id, ' ');

                $not_data = [
                    "username" => $username,
                    "mobile" => $mobile,
                    "nickname" => $nickname,
                    "password" => $password,
                    "wx_openid" => $wx_openid,
                    "weapp_openid" => $weapp_openid,
                    "realname" => $realname,
                    "create_time" => time(),
                    "record_id" => $record
                ];

                if ($username == "" && $mobile == "") {
                    $not_data[ 'content' ] = "失败，用户名或手机号必须存在一个";
                    model('member_import_log')->add($not_data);
                    $error_num++;
                    continue;
                }

                if (!empty($mobile) && !preg_match('/^1[3456789]\d{9}$/ims', $mobile)) {
                    $not_data[ 'content' ] = "失败，手机号格式错误";
                    model('member_import_log')->add($not_data);
                    $error_num++;
                    continue;
                }

                if ($nickname == "") {
                    $not_data[ 'content' ] = "失败，用户昵称不能为空";
                    model('member_import_log')->add($not_data);
                    $error_num++;
                    continue;
                }

                if ($password == "") {
                    $not_data[ 'content' ] = "失败，用户密码不能为空";
                    model('member_import_log')->add($not_data);
                    $error_num++;
                    continue;
                }
                if ($username) {
                    $username_res = model("member")->getInfo([ 'username' => $username ]);//根据用户名查找
                    if ($username_res) {
                        $not_data[ 'content' ] = "失败，已存在相同的用户名";
                        model('member_import_log')->add($not_data);
                        $error_num++;
                        continue;
                    }
                }

                if ($mobile) {
                    $mobile_res = model("member")->getInfo([ 'mobile' => $mobile ]);//根据手机号查找
                    if ($mobile_res) {
                        $not_data[ 'content' ] = "失败，已存在相同的手机号";
                        model('member_import_log')->add($not_data);
                        $error_num++;
                        continue;
                    }
                }

                if ($wx_openid) {
                    $wx_openid_res = model("member")->getInfo([ 'wx_openid' => $wx_openid ]);//根据微信公众号ID查找
                    if ($wx_openid_res) {
                        $not_data[ 'content' ] = "失败，已存在相同的公众号openid";
                        model('member_import_log')->add($not_data);
                        $error_num++;
                        continue;
                    }
                }

                if ($weapp_openid) {
                    $weapp_openid_res = model("member")->getInfo([ 'weapp_openid' => $weapp_openid ]);//根据小程序ID查找
                    if ($weapp_openid_res) {
                        $not_data[ 'content' ] = "失败，已存在相同的小程序openid";
                        model('member_import_log')->add($not_data);
                        $error_num++;
                        continue;
                    }
                }

                if ($membeer_level_id == "") {
                    $is_member = 0;
                } else {
                    $is_member = 1;
                    $member_level_info = model('member_level')->getInfo([ 'level_name' => $membeer_level_id ]);
                    if (empty($member_level_info)) {
                        $not_data[ 'content' ] = "失败，未查到该会员等级";
                        model('member_import_log')->add($not_data);
                        $error_num++;
                        break;
                    }
                }

                $data = [
                    "username" => isset($username) ? $username : '',
                    "mobile" => isset($mobile) ? $mobile : '',
                    "nickname" => $nickname,
                    "password" => data_md5($password),
                    "member_level" => $member_level_info[ 'level_id' ] ?? "",
                    "wx_openid" => isset($wx_openid) ? $wx_openid : '',
                    "weapp_openid" => isset($weapp_openid) ? $weapp_openid : '',
                    "realname" => isset($realname) ? $realname : '',
                    'member_level_name' => $member_level_info[ 'level_name' ] ?? "",
                    'point' => isset($point) ? $point : 0,
                    'growth' => isset($growth) ? $growth : 0,
                    'balance_money' => isset($balance_money) ? $balance_money : 0.00,
                    'balance' => isset($balance) ? $balance : 0.00,
                    'reg_time' => time(),
                    'login_time' => time(),
                    'last_login_time' => time(),
                    'site_id' => 1,
                    'is_member' => $is_member,
                    'member_code' => isset($mobile) ? $mobile : ''
                ];

                model('member')->add($data);
                $not_data[ 'content' ] = "成功";
                model('member_import_log')->add($not_data);
                $success_num++;
            }
            model('member')->commit();
            if ($success_num + $error_num == ( $allRow - 1 )) {
                $data_record = [
                    "member_num" => ( $allRow - 1 ),
                    "success_num" => $success_num,
                    "error_num" => $error_num,
                    "create_time" => time(),
                    "status_name" => "导入成功"
                ];
                model('member_import_record')->update($data_record, [ 'id' => $record ]);
            }
            return $this->success([
                "allRow" => $allRow,
                "num" => $type_num,
                "path" => $param[ 'path' ],
                "name" => $param[ 'filename' ],
                "success_num" => $success_num,
                "error_num" => $error_num,
                "record" => $record
            ]);
        } catch (\Exception $e) {
            model('member')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 获取用户黑名单
     * @return array
     */
    public function getMemberBlacklist($site_id)
    {
        $cache = Cache::get('member_blacklist_' . $site_id);
        if (!empty($cache)) return $this->success($cache);

        $blacklist = model('member')->getColumn([ [ 'status', '=', 0 ] ], 'member_id');
        Cache::set('member_blacklist_' . $site_id, $blacklist);

        return $this->success($blacklist);
    }

    /**
     *  获取会员导入记录列表
     */
    public function getMemberImportRecordList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'create_time desc', $field = '*')
    {

        $list = model('member_import_record')->pageList($condition, $field, $order, $page, $page_size, '', '', '');

        if (!empty($list[ 'list' ])) {
            foreach ($list[ 'list' ] as $key => $val) {
                $list[ 'list' ][ $key ][ "create_time" ] = date('Y-m-d H:i:s', $val[ 'create_time' ]);
            }
        }

        return $this->success($list);
    }

    /**
     * 获取导入记录单条数据
     */
    public function getMemberImportRecordInfo($id)
    {

        $info = model('member_import_record')->getInfo([ 'id' => $id ]);

        return $this->success($info);
    }

    //更改
    public function alterShareRelation($member_id, $share_member, $site_id)
    {
        $member_info = model('member')->getInfo([
            [ 'member_id', '=', $member_id ],
            [ 'site_id', '=', $site_id ],
        ]);
        if (empty($member_info)) {
            return $this->error(null, '会员数据有误');
        }

        //只有普通会员 并且没有绑定上级才修改关系
        if ($member_info[ 'is_fenxiao' ] == 0 && $member_info[ 'fenxiao_id' ] == 0) {
            model('member')->update([
                'share_member' => $share_member,
            ], [
                [ 'member_id', '=', $member_id ],
                [ 'site_id', '=', $site_id ],
            ]);
            event('AlterShareRelation', [ 'site_id' => $site_id, 'member_id' => $member_id ]);
        }

        return $this->success();
    }

    /**
     * 更新会员最后访问时间
     * @param $member_id
     */
    public static function modifyLastVisitTime($member_id)
    {
        $res = model("member")->update([ 'last_visit_time' => time() ], [ 'member_id' => $member_id ]);
        return $res;

    }

    /**
     * 更新会员最后消费时间
     * @param $member_id
     */
    public static function modifyLastConsumTime($member_id)
    {
        $res = model("member")->update([ 'last_consum_time' => time() ], [ 'member_id' => $member_id ]);
        return $res;

    }

    /**
     * 获取用户可用余额
     * @param $site_id
     * @param $member_id
     * @return array
     */
    public function getMemberUsableBalance($site_id, $member_id)
    {
        $member_info = model('member')->getInfo([
            [ 'site_id', '=', $site_id ], [ 'member_id', '=', $member_id ], [ 'is_delete', '=', 0 ]
        ], 'balance - balance_lock as balance,balance_money - balance_money_lock as balance_money');
        if (empty($member_info)) {
            return $this->error('', '未获取到用户信息');
        }

        $member_info[ 'balance' ] = $member_info[ 'balance' ] < 0 ? 0 : $member_info[ 'balance' ];
        $member_info[ 'balance_money' ] = $member_info[ 'balance_money' ] < 0 ? 0 : $member_info[ 'balance_money' ];
        $member_info[ 'usable_balance' ] = round($member_info[ 'balance' ] + $member_info[ 'balance_money' ], 2);

        return $this->success($member_info);
    }


    /**
     * 生成随机数
     * @param int $length
     * @return string
     */
    public function memberCode($length)
    {
        $pattern = array(
            '1','2','3','4','5','6','7','8','9','0'
        );
        $keys = array_rand($pattern, $length);
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern[$keys[$i]];    //生成php随机数
        }
        return $key;
    }

    /**
     * 办理会员
     * @param $data
     */
    public function handleMember($data)
    {
        model('member')->startTrans();
        try {
            $member_info = model('member')->getInfo([ ['member_id', '=', $data['member_id']], ['site_id', '=', $data['site_id']] ]);
            if($member_info['is_member'] == 1){
                return $this->success();
            }

            $level_info = model('member_level')->getInfo([ ['site_id', '=', $data['site_id']], ['level_id', '=', $data['level_id']] ], 'level_id, sort, growth, level_name');
            $growth = $level_info['growth'] - $member_info['growth'];

            $save_data = ['is_member' => 1];
            $member_code = $data['member_code'] ?? '';

            if($member_code){
                $member_count = model('member')->getInfo([ ['site_id', '=', $data['site_id']],['member_code', '=', $member_code], ['member_id', '<>', $data['member_id']] ]);
                if($member_count) return $this->error('', '当前会员编码已存在，请重新设置');

                $save_data['member_code'] = $member_code;
            }else{
                $member_code = $member_info['mobile'] ? $member_info['mobile'] : $this->memberCode(11);
                $save_data['member_code'] = $member_code;
            }

            model('member')->update($save_data, [
                ['member_id', '=', $data['member_id']],
                ['site_id', '=', $data['site_id']],
            ]);

            if($growth > 0) {
                $account = new MemberAccount();
                $res = $account->addMemberAccount($data['site_id'], $data['member_id'], 'growth', $growth, 'adjust', '等级调整', '管理员调整客户等级', 0);
                if($res['code'] < 0) return $res;
            }

            model('member')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('member')->rollback();
            return $this->error($e->getMessage() . $e->getFile() . $e->getLine());
        }

    }
}