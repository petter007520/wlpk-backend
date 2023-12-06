<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;

use app\model\member\Member;
use app\model\store\Store;
use app\model\verify\Verifier;
use app\model\verify\Verify as VerifyModel;
use app\model\verify\VerifyRecord;
use app\model\web\Config as ConfigModel;

/**
 * 核销
 * Class Verify
 * @package app\shop\controller
 */
class Verify extends BaseShop
{

    /**
     * 核销码
     * @return array|mixed
     */
    public function lists()
    {
        $verify_model = new VerifyModel();
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $order = input("order", "create_time desc");
            $verify_type = input('verify_type', "");//验证类型
            $verify_code = input('verify_code', "");//验证码
            $verifier_name = input('verifier_name', "");
            $start_time = input("start_time", '');
            $end_time = input("end_time", '');
            $verify_from = input('verify_from', '');

            $condition = [
                [ 'site_id', "=", $this->site_id ],
                [ 'is_verify', '=', 1 ]
            ];
            if (!empty($verify_type)) {
                $condition[] = [ "verify_type", "=", $verify_type ];
            }
            if (!empty($verify_from)) {
                $condition[] = [ "verify_from", "=", $verify_from ];
            }
            if (!empty($verify_code)) {
                $condition[] = [ "verify_code", 'like', '%' . $verify_code . '%' ];
            }
            if (!empty($verifier_name)) {
                $condition[] = [ 'verifier_name', 'like', '%' . $verifier_name . '%' ];
            }
            if (!empty($start_time) && empty($end_time)) {
                $condition[] = [ 'verify_time', '>=', date_to_time($start_time) ];
            } elseif (empty($start_time) && !empty($end_time)) {
                $condition[] = [ "verify_time", "<=", date_to_time($end_time) ];
            } elseif (!empty($start_time) && !empty($end_time)) {
                $condition[] = [ 'verify_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
            }
            $list = $verify_model->getVerifyPageList($condition, $page, $page_size, $order, $field = 'id, verify_code, verify_type, verify_type_name, verify_content_json, verifier_id, verifier_name,verify_from,verify_remark, is_verify, create_time, verify_time');
            return $list;
        } else {
            $verify_type = $verify_model->getVerifyType();
            $verify_from = $verify_model->verifyFrom;
            $this->assign('verify_from', $verify_from);
            $this->assign('verify_type', $verify_type);
            return $this->fetch("verify/lists");
        }

    }

    /**
     * 核销记录
     * @return mixed
     */
    public function records()
    {
        $verify_model = new VerifyModel();
        $verify_record_model = new VerifyRecord();
        $verify_code = input('verify_code', "");//验证码
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $order = input("order", "create_time desc");
            $verify_type = input('verify_type', "");//验证类型
            $verifier_name = input('verifier_name', "");
            $start_time = input("start_time", '');
            $end_time = input("end_time", '');
            $store_id = input('store_id', '');

            $condition = [
                [ 'vr.site_id', "=", $this->site_id ],
//                ['is_verify', '=', 1]
            ];
            if (!empty($verify_type)) {
                $condition[] = [ "v.verify_type", "=", $verify_type ];
            }
            if (!empty($store_id)) {
                $condition[] = [ "vr.store_id", "=", $store_id ];
            }
            if (!empty($verify_code)) {
                $condition[] = [ "vr.verify_code", 'like', '%' . $verify_code . '%' ];
            }
            if (!empty($verifier_name)) {
                $condition[] = [ 'vr.verifier_name', 'like', '%' . $verifier_name . '%' ];
            }
            if (!empty($start_time) && empty($end_time)) {
                $condition[] = [ 'vr.verify_time', '>=', date_to_time($start_time) ];
            } elseif (empty($start_time) && !empty($end_time)) {
                $condition[] = [ "vr.verify_time", "<=", date_to_time($end_time) ];
            } elseif (!empty($start_time) && !empty($end_time)) {
                $condition[] = [ 'vr.verify_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
            }
            $list = $verify_record_model->getVerifyRecordsViewPageList($condition, $page, $page_size, $order, $field = 'id, verify_code, verify_type, verify_type_name, verify_content_json, verifier_id, verifier_name,verify_from,verify_remark, is_verify, create_time, verify_time');
            return $list;
        } else {
            $verify_type = $verify_model->getVerifyType();
            $verify_from = $verify_model->verifyFrom;
            $this->assign('verify_code', $verify_code);
            $this->assign('verify_from', $verify_from);
            $this->assign('verify_type', $verify_type);
            $store_list = ( new Store() )->getStoreList([ [ 'site_id', '=', $this->site_id ] ], 'store_name,store_id');
            $this->assign('store_list', $store_list[ 'data' ]);
            return $this->fetch("verify/records");
        }

    }

    /**
     * 订单核销
     * @return mixed
     */
    public function orderverify()
    {
        $verify_model = new VerifyModel();
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $verify_code = input('verify_code', "");
            $start_time = input("start_time", '');
            $end_time = input("end_time", '');
            $verify_from = input('verify_from', '');
            $verify_type = input('verify_type', '');
            $is_verify = input('is_verify', '');

            $condition = [
                [ 'site_id', "=", $this->site_id ],

            ];
            if (!empty($verify_type)) {
                $condition[] = [ "verify_type", "=", $verify_type ];
            }
            if ($is_verify !== '') {
                $condition[] = [ "is_verify", "=", $is_verify ];
            }
            if (!empty($verify_from)) {
                $condition[] = [ "verify_from", "=", $verify_from ];
            }
            if (!empty($verify_code)) {
                $condition[] = [ "verify_code", 'like', '%' . $verify_code . '%' ];
            }

            if (!empty($start_time) && empty($end_time)) {
                $condition[] = [ 'create_time', '>=', date_to_time($start_time) ];
            } elseif (empty($start_time) && !empty($end_time)) {
                $condition[] = [ "create_time", "<=", date_to_time($end_time) ];
            } elseif (!empty($start_time) && !empty($end_time)) {
                $condition[] = [ 'create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
            }
            $list = $verify_model->getVerifyPageList($condition, $page, $page_size, 'id desc');
            return $list;
        } else {

            $verify_count = $verify_model->getVerifyInfo([ [ 'site_id', '=', $this->site_id ] ], 'count(id) as total_count, sum(verify_use_num) as verify_use_num,is_verify,verify_content_json')[ 'data' ] ?? [];
            $this->assign('total_count', $verify_count[ 'total_count' ] ?? 0);
            $this->assign('verify_use_num', $verify_count[ 'verify_use_num' ] ?? 0);

            $verify_info = $verify_model->getVerifyInfo([ [ 'site_id', '=', $this->site_id ], [ 'verify_type', '=', 'virtualgoods' ] ], 'count(id) as total_count, sum(verify_total_count) as verify_total_count, sum(verify_use_num) as verify_use_num,is_verify,verify_content_json')[ 'data' ] ?? [];
            $this->assign('verify_goods_num', (int) abs($verify_info[ 'verify_total_count' ] - $verify_info[ 'verify_use_num' ]));
            $this->assign('verify_goods_count', $verify_info[ 'total_count' ]);

            $verify_info = $verify_model->getVerifyInfo([ [ 'site_id', '=', $this->site_id ], [ 'verify_type', '=', 'pickup' ] ], 'count(id) as total_count,sum(verify_total_count) as verify_total_count, sum(verify_use_num) as verify_use_num,is_verify,verify_content_json')[ 'data' ] ?? [];
            $this->assign('pickup_num', (int) abs($verify_info[ 'verify_total_count' ] - $verify_info[ 'verify_use_num' ]));
            $this->assign('pickup_count', $verify_info[ 'total_count' ]);

            $card_goods_count = $verify_model->getVerifyCount([ [ 'site_id', '=', $this->site_id ], [ 'verify_type', '=', 'cardgoods' ] ], 'id')[ 'data' ] ?? [];
            $verify_info = $verify_model->getVerifyInfo([ [ 'site_id', '=', $this->site_id ], [ 'verify_type', '=', 'cardgoods' ], [ 'verify_total_count', '>', 0 ] ], 'sum(verify_total_count) as verify_total_count, sum(verify_use_num) as verify_use_num,is_verify,verify_content_json')[ 'data' ] ?? [];
            $card_goods_num = (int) abs($verify_info[ 'verify_total_count' ] - $verify_info[ 'verify_use_num' ]);
            $card_goods_num += $verify_model->getVerifyCount([ [ 'site_id', '=', $this->site_id ], [ 'verify_type', '=', 'cardgoods' ], [ 'verify_total_count', '=', 0 ], [ 'expire_time', '>', 0 ], [ 'expire_time', '<', time() ] ], 'id')[ 'data' ];

            $this->assign('card_goods_num', $card_goods_num);
            $this->assign('card_goods_count', $card_goods_count);

            $verify_type = $verify_model->getVerifyType();
            $this->assign('verify_type', $verify_type);
            return $this->fetch("verify/order_verify");
        }

    }

    /**
     * 核销信息
     */
    public function verifyInfo()
    {
        $id = input('id', '');

        $verify_model = new VerifyModel();
        $info = $verify_model->getVerifyInfo([ [ 'id', '=', $id ], [ 'site_id', '=', $this->site_id ] ]);
        return $info;
    }

    /**
     * 核销台
     * @return mixed
     */
    public function verifyCard()
    {
        if (request()->isAjax()) {
            $verify_code = input("verify_code", "");
            $verify_model = new VerifyModel();
            $res = $verify_model->getVerifyInfo([ [ "verify_code", "=", $verify_code ], [ "site_id", "=", $this->site_id ] ]);
            return $res;
        } else {
            return $this->fetch("verify/verify_card");
        }

    }

    /**
     * 核销人员
     * @return mixed
     */
    public function user()
    {
        if (request()->isAjax()) {
            $verifier = new Verifier();
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $order = input("order", "v.create_time desc");
            $verifier_name = input('verifier_name', '');
            $verifier_type = input('verifier_type', 0);
            $store_id = input('store_id', 0);
            $condition = [];
            $condition[] = [ 'v.site_id', "=", $this->site_id ];
            if ($verifier_name) {
                $condition[] = [ 'v.verifier_name', '=', $verifier_name ];
            }
            if ($verifier_type != '') {
                $condition[] = [ 'v.verifier_type', '=', $verifier_type ];
            }
            if ($store_id) {
                $condition[] = [ 'v.store_id', '=', $store_id ];
            }
            $list = $verifier->getVerifierPageList($condition, $page, $page_size, $order);
            return $list;
        } else {
            // 门店列表
            $store_model = new Store();
            $store_list = $store_model->getStoreList([ [ 'site_id', "=", $this->site_id ], [ 'is_frozen', '=', 0 ] ], 'store_id,store_name', 'store_id desc')[ 'data' ];
            $this->assign('store_list', $store_list);
            return $this->fetch("verify/user");
        }
    }

    /**
     * 添加核销人员
     * @return mixed
     */
    public function addUser()
    {
        if (request()->isAjax()) {
            $verifier_name = input("verifier_name", "");
            $member_id = input("member_id", 0);//会员账号
            $uid = input("uid", 0);//管理员账号
            $verifier_type = input('verifier_type', 0);//核销员类型：0平台核销员，1门店核销员
            $store_id = input('store_id', 0);//门店ID
            $model = new Verifier();
            $data = [
                'site_id' => $this->site_id,
                "verifier_name" => $verifier_name,
                "member_id" => $member_id,
                "uid" => $uid,
                'verifier_type' => $verifier_type,
                'store_id' => $store_id
            ];
            $result = $model->addVerifier($data);
            return $result;
        } else {
            $upload_config_model = new ConfigModel();
            $upload_config_result = $upload_config_model->getDefaultImg($this->site_id, $this->app_module)[ 'data' ][ 'value' ];
            $this->assign("default_headimg", $upload_config_result[ 'head' ]);

            // 门店列表
            $store_model = new Store();
            $store_list = $store_model->getStoreList([ [ 'site_id', "=", $this->site_id ], [ 'is_frozen', '=', 0 ] ], 'store_id,store_name', 'store_id desc')[ 'data' ];
            $this->assign('store_list', $store_list);

            return $this->fetch("verify/add_user");
        }
    }

    /**
     * 编辑核销人员
     * @return mixed
     */
    public function editUser()
    {
        $verifier_id = input("verifier_id", 0);//核销员id
        $model = new Verifier();

        if (request()->isAjax()) {
            $verifier_name = input("verifier_name", "");
            $member_id = input("member_id", 0);//会员账号
            $verifier_type = input('verifier_type', 0);//核销员类型：0平台核销员，1门店核销员
            $store_id = input('store_id', 0);//门店ID
            $data = [
                'verifier_name' => $verifier_name,
                'member_id' => $member_id,
                'uid' => 0,
                'verifier_type' => $verifier_type,
                'store_id' => $verifier_type == 1 ? $store_id : 0
            ];
            $condition = array (
                [ 'verifier_id', '=', $verifier_id ],
                [ 'site_id', '=', $this->site_id ],
            );

            $result = $model->editVerifier($data, $condition);
            return $result;
        } else {
            $this->assign("verifier_id", $verifier_id);

            //用户信息
            $info = $model->getVerifierInfo([
                [ "verifier_id", "=", $verifier_id ],
                [ "site_id", "=", $this->site_id ],
            ])[ 'data' ];

            if (empty($info)) $this->error('未获取到核销员数据', addon_url('shop/verify/user'));

            $info[ "member_name" ] = '';
            if (!empty($info[ "member_id" ])) {
                $member_model = new Member();
                $member_info = $member_model->getMemberInfo([ [ "member_id", "=", $info[ "member_id" ] ] ], "username")[ "data" ];
                $info[ "member_name" ] = $member_info[ "username" ];
            }

            if ($info[ 'verifier_type' ] == 1) {
                // 门店列表
                $store_model = new Store();
                $store = $store_model->getStoreInfo([ [ 'store_id', '=', $info[ 'store_id' ] ] ], 'store_name')[ 'data' ];
                $info[ 'store_name' ] = $store[ 'store_name' ];
            }

            $this->assign("data", $info);

            $upload_config_model = new ConfigModel();
            $upload_config_result = $upload_config_model->getDefaultImg($this->site_id, $this->app_module)[ 'data' ][ 'value' ];
            $this->assign("default_headimg", $upload_config_result[ 'head' ]);

            // 门店列表
            $store_model = new Store();
            $store_list = $store_model->getStoreList([ [ 'site_id', "=", $this->site_id ], [ 'is_frozen', '=', 0 ] ], 'store_id,store_name', 'store_id desc')[ 'data' ];
            $this->assign('store_list', $store_list);

            return $this->fetch("verify/edit_user");
        }

    }

    /**
     * 删除核销人员
     * @return mixed
     */
    public function deleteUser()
    {
        if (request()->isAjax()) {
            $verifier = new Verifier();
            $verifier_id = input('ids', 0);
            $res = $verifier->deleteVerifier($verifier_id, $this->site_id);
            return $res;
        }
    }

    /**
     * 核销
     */
    public function verify()
    {
        $info = array (
            "verifier_id" => $this->uid,
            "verifier_name" => $this->user_info[ 'username' ],
            "verify_from" => 'shop'
        );
        $verify_code = input("verify_code", "");
        $verify_model = new VerifyModel();
        $res = $verify_model->verify($info, $verify_code);
        return $res;
    }

    /**
     * 搜索会员
     * 不是菜单 不入权限
     */
    public function searchMember()
    {
        if (request()->isAjax()) {
            $search_text = input('search_text', '');
            $member_model = new Member();
            $member_info = $member_model->getMemberInfo([ [ 'username|mobile', '=', $search_text ], [ 'site_id', '=', $this->site_id ] ]);
            return $member_info;
        }
    }

    /**
     * 核销记录导出
     */
    public function exportVerify()
    {
        $verify_model = new VerifyModel();
        $page = input('page', 1);
        $page_size = 0;
        $order = input("order", "create_time desc");
        $verify_type = input('verify_type', "");//验证类型
        $verify_code = input('verify_code', "");//验证码
        $verifier_name = input('verifier_name', "");
        $start_time = input("start_time", '');
        $end_time = input("end_time", '');

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
            $condition[] = [ 'verify_time', '>=', date_to_time($start_time) ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ "verify_time", "<=", date_to_time($end_time) ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'verify_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
        }
        $list_result = $verify_model->getVerifyPageList($condition, $page, $page_size, $order, $field = 'id, verify_code, verify_type, verify_type_name, verify_content_json, verifier_id, verifier_name, is_verify, create_time, verify_time');
        $list = $list_result[ 'data' ][ 'list' ];

        // 实例化excel
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $phpExcel->getProperties()->setTitle("核销记录");
        $phpExcel->getProperties()->setSubject("核销记录");
        //单独添加列名称
        $phpExcel->setActiveSheetIndex(0);

        $phpExcel->getActiveSheet()->setCellValue("A1", '核销码');
        $phpExcel->getActiveSheet()->setCellValue("B1", '核销类型');
        $phpExcel->getActiveSheet()->setCellValue("C1", '核销员');
        $phpExcel->getActiveSheet()->setCellValue("D1", '状态');
        $phpExcel->getActiveSheet()->setCellValue("E1", '创建时间');
        $phpExcel->getActiveSheet()->setCellValue("F1", '核销时间');

        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $start = $k + 2;
                $phpExcel->getActiveSheet()->setCellValue('A' . $start, $v[ 'verify_code' ] . "\t");
                $phpExcel->getActiveSheet()->setCellValue('B' . $start, $v[ 'verify_type_name' ] . "\t");
                $phpExcel->getActiveSheet()->setCellValue('C' . $start, $v[ 'verifier_name' ] . "\t");
                if ($v[ 'is_verify' ] == 1) {
                    $verify_status = "已核销";
                } else {
                    $verify_status = "尚未核销";
                }
                $phpExcel->getActiveSheet()->setCellValue('D' . $start, $verify_status . "\t");
                $phpExcel->getActiveSheet()->setCellValue('E' . $start, time_to_date($v[ 'create_time' ]) . "\t");
                $phpExcel->getActiveSheet()->setCellValue('F' . $start, time_to_date($v[ 'verify_time' ]) . "\t");
            }
        }

        // 重命名工作sheet
        $phpExcel->getActiveSheet()->setTitle('核销记录');
        // 设置第一个sheet为工作的sheet
        $phpExcel->setActiveSheetIndex(0);
        // 保存Excel 2007格式文件，保存路径为当前路径，名字为export.xlsx
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($phpExcel, 'Xlsx');
        $file = date('Y年m月d日-核销记录', time()) . '.xlsx';
        $objWriter->save($file);

        header("Content-type:application/octet-stream");

        $filename = basename($file);
        header("Content-Disposition:attachment;filename = " . $filename);
        header("Accept-ranges:bytes");
        header("Accept-length:" . filesize($file));
        readfile($file);
        unlink($file);
        exit;
    }
}