<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\coupon\shop\controller;

use addon\coupon\model\MemberCoupon;
use app\shop\controller\BaseShop;
use addon\coupon\model\CouponType as CouponTypeModel;
use addon\coupon\model\Coupon as CouponModel;
use think\facade\Db;

/**
 * 优惠券
 * @author Administrator
 *
 */
class Coupon extends BaseShop
{

    public function __construct()
    {
        $this->app_module = input('app_module', SHOP_MODULE);
        if ($this->app_module == 'store') {
            $this->initConstructInfo(); // 加载构造函数重要信息
        } else {
            parent::__construct();
        }
    }

    /**
     * 添加活动
     */
    public function add()
    {

        if (request()->isAjax()) {
            $data = [
                'site_id' => $this->site_id,
                'coupon_name' => input('coupon_name', ''),//优惠券名称
                'type' => input('type'),//优惠券类型
                'goods_type' => input('goods_type', 1),
                'goods_ids' => input('goods_ids', ''),
                'sort' => input('sort', '0'), //优惠券排序
                'money' => input('money', 0),//优惠券面额
                'discount' => input('discount', 0),//优惠券折扣
                'discount_limit' => input('discount_limit', 0),//最多优惠
                'count' => input('count', ''),//发放数量
                'max_fetch' => input('max_fetch', ''),//最大领取数量
                'at_least' => input('at_least', ''),//满多少元可以使用
                'end_time' => strtotime(input('end_time', '')),//活动结束时间
                'image' => input('image', ''),//优惠券图片
                'validity_type' => input('validity_type', ''),//有效期类型 0固定时间 1领取之日起
                'fixed_term' => input('fixed_term', ''),//领取之日起N天内有效
                'is_show' => input('is_show', 0),//是否允许直接领取 1:是 0：否 允许直接领取，用户才可以在手机端和PC端进行领取，否则只能以活动的形式发放。
            ];

            $coupon_type_model = new CouponTypeModel();
            return $coupon_type_model->addCouponType($data);
        } else {
            return $this->fetch("coupon/add");
        }
    }

    /**
     * 编辑活动
     */
    public function edit()
    {
        $coupon_type_model = new CouponTypeModel();
        if (request()->isAjax()) {
            $data = [
                'site_id' => $this->site_id,
                'coupon_name' => input('coupon_name', ''),//优惠券名称
                'type' => input('type'),//优惠券类型
                'goods_type' => input('goods_type', 1),
                'goods_ids' => input('goods_ids', ''),
                'money' => input('money', 0),//优惠券面额
                'sort' => input('sort', 0),//优惠券面额
                'discount' => input('discount', 0),//优惠券折扣
                'discount_limit' => input('discount_limit', 0),//最多优惠
                'count' => input('count', ''),//发放数量
                'max_fetch' => input('max_fetch', ''),//最大领取数量
                'at_least' => input('at_least', ''),//满多少元可以使用
                'end_time' => strtotime(input('end_time', '')),//活动结束时间
                'image' => input('image', ''),//优惠券图片
                'validity_type' => input('validity_type', ''),//有效期类型 0固定时间 1领取之日起
                'fixed_term' => input('fixed_term', ''),//领取之日起N天内有效
                'is_show' => input('is_show', 0),//是否允许直接领取 1:是 0：否 允许直接领取，用户才可以在手机端和PC端进行领取，否则只能以活动的形式发放。
            ];
            $coupon_type_id = input('coupon_type_id', 0);

            return $coupon_type_model->editCouponType($data, $coupon_type_id);
        } else {
            $coupon_type_id = input('coupon_type_id', 0);
            $this->assign('coupon_type_id', $coupon_type_id);

            $coupon_type_info = $coupon_type_model->getCouponTypeInfo($coupon_type_id, $this->site_id);
            if (empty($coupon_type_info[ 'data' ])) $this->error('未获取到优惠券数据', addon_url('coupon://shop/coupon/lists'));
            $this->assign('coupon_type_info', $coupon_type_info[ 'data' ][ 0 ]);

            return $this->fetch("coupon/edit");
        }
    }

    /**
     * 活动详情
     */
    public function detail()
    {
        $coupon_type_id = input('coupon_type_id', 0);
        $coupon_type_model = new CouponTypeModel();
        $coupon_type_info = $coupon_type_model->getCouponTypeInfo($coupon_type_id, $this->site_id)[ 'data' ] ?? [];
        if (empty($coupon_type_info)) $this->error('未获取到优惠券数据', addon_url('coupon://shop/coupon/lists'));
        $this->assign('info', $coupon_type_info[ 0 ]);
        $this->assign('get_type',  (new CouponModel())->getCouponGetType());
        return $this->fetch("coupon/detail");
    }

    /**
     * 活动列表
     */
    public function lists()
    {
        $coupon_type_model = new CouponTypeModel();
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $coupon_name = input('coupon_name', '');
            $status = input('status', '');
            $inventory_count = input('inventory_count', '');#剩余数量

            $condition = [];
            if ($status !== "") {
                $condition[] = [ 'status', '=', $status ];
            }
            $type = input('type');
            if ($type) {
                $condition[] = [ 'type', '=', $type ];
            }
            //类型
            $validity_type = input('validity_type', '');
            if ($validity_type !== '') {
                $start_time = input('start_time', '');
                $end_time = input('end_time', '');
                switch ( $validity_type ) {
                    case 0: //固定
                        $condition[] = [ 'end_time', 'between', [ $start_time, $end_time ] ];
                        break;
                    case 1:
                        $condition[] = [ 'fixed_term', 'between', [ $start_time, $end_time ] ];
                        break;
                    case 2:
                        $condition[] = [ 'validity_type', '=', 2 ];
                        break;
                }
            }
            if ($inventory_count) {
                $condition[] = [ '', "exp", Db::raw("(lead_count < count && count != -1) OR count = -1") ];
            }

            $condition[] = [ 'site_id', '=', $this->site_id ];
            $condition[] = [ 'coupon_name', 'like', '%' . $coupon_name . '%' ];
            $field = '*';

            //排序
            $link_sort = input('order', 'create_time');
            $sort = input('sort', 'desc');
            if ($link_sort == 'sort') {
                $order_by = $link_sort . ' ' . $sort;
            } else {
                $order_by = $link_sort . ' ' . $sort . ',sort desc';
            }

            $res = $coupon_type_model->getCouponTypePageList($condition, $page, $page_size, $order_by, $field);

            //获取优惠券状态
            $coupon_type_status_arr = $coupon_type_model->getCouponTypeStatus();
            foreach ($res[ 'data' ][ 'list' ] as $key => $val) {
                $res[ 'data' ][ 'list' ][ $key ][ 'status_name' ] = $coupon_type_status_arr[ $val[ 'status' ] ];
            }
            return $res;
        } else {
            $this->forthMenu();
            //优惠券状态
            $coupon_type_status_arr = $coupon_type_model->getCouponTypeStatus();
            $this->assign('coupon_type_status_arr', $coupon_type_status_arr);

            return $this->fetch("coupon/lists");
        }
    }

    /**
     * 排序
     * @return mixed
     */
    public function couponSort()
    {
        $sort = input('sort', 0);
        $coupon_type_id = input('coupon_type_id', 0);
        $coupon_type_model = new CouponTypeModel();
        return $coupon_type_model->couponSort($coupon_type_id, $sort);
    }

    /**
     * 优惠券推广
     */
    public function couponUrl()
    {
        $coupon_type_id = input('coupon_type_id', '');
        $coupon_model = new couponTypeModel();
//        $coupon_info = $coupon_model->getInfo([['coupon_type_id','=',$coupon_type_id]],'coupon_type_id,coupon_name,site_id');
//        $coupon_info = $coupon_info[ 'data' ];
//        $res = $coupon_model->spread($coupon_info[ 'coupon_type_id' ], $coupon_info[ 'coupon_name' ], $coupon_info[ 'site_id' ]);
        $res = $coupon_model->urlQrcode('/pages_tool/goods/coupon_receive', [ 'coupon_type_id' => $coupon_type_id ], 'coupon', $this->site_id);
        return $res;
    }

    /**
     * 发送优惠券
     */
    public function send()
    {
        $member_id = input('member_id');
        $coupon_type_model = new CouponTypeModel();
        if (request()->isAjax()) {
            $coupon_data = json_decode(input('coupon_data', '[]'), true);
            $get_type = input('get_type', 4);
            if (empty($coupon_data)) {
                return error('', 'REQUEST_COUPON_TYPE_ID');
            }
            $res = ( new CouponModel() )->giveCoupon($coupon_data, $this->site_id, $member_id, $get_type);
            return $res;
        } else {

            //优惠券状态
            $coupon_type_status_arr = $coupon_type_model->getCouponTypeStatus();
            $this->assign('coupon_type_status_arr', $coupon_type_status_arr);

            $this->assign('member_id', $member_id);

            return $this->fetch("coupon/send");
        }
    }

    /**
     * 活动列表
     */
    public function couponSelect()
    {
        $coupon_type_model = new CouponTypeModel();
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $coupon_name = input('coupon_name', '');

            $condition[] = [ 'site_id', '=', $this->site_id ];
            $condition[] = [ 'status', '=', 1 ];
            $condition[] = [ 'coupon_name', 'like', '%' . $coupon_name . '%' ];
            $order = 'create_time desc';
            $field = '*';

            $res = $coupon_type_model->getCouponTypePageList($condition, $page, $page_size, $order, $field);

            //获取优惠券状态
            $coupon_type_status_arr = $coupon_type_model->getCouponTypeStatus();
            foreach ($res[ 'data' ][ 'list' ] as $key => $val) {
                $res[ 'data' ][ 'list' ][ $key ][ 'status_name' ] = $coupon_type_status_arr[ $val[ 'status' ] ];
            }
            return $res;

        } else {
            //优惠券状态
            $coupon_type_status_arr = $coupon_type_model->getCouponTypeStatus();
            $this->assign('coupon_type_status_arr', $coupon_type_status_arr);
            $select_id = input('select_id', '');
            $this->assign('select_id', $select_id);

            return $this->fetch("coupon/coupon_select");
        }
    }

    /**
     * 关闭活动
     */
    public function close()
    {
        if (request()->isAjax()) {
            $coupon_type_id = input('coupon_type_id', 0);
            $coupon_type_model = new CouponTypeModel();
            return $coupon_type_model->closeCouponType($coupon_type_id, $this->site_id);
        }
    }

    /**
     * 删除活动
     */
    public function delete()
    {
        if (request()->isAjax()) {
            $coupon_type_id = input('coupon_type_id', 0);
            $coupon_type_model = new CouponTypeModel();
            return $coupon_type_model->deleteCouponType($coupon_type_id, $this->site_id);
        }
    }

    /**
     * 优惠券领取记录
     * */
    public function receive()
    {
        $coupon_model = new CouponModel();
        if (request()->isAjax()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $coupon_type_id = input('coupon_type_id', 0);
            $state = input('state', '');
            $condition = [];
            $condition[] = [ 'npc.coupon_type_id', '=', $coupon_type_id ];
            $condition[] = [ 'npc.site_id', '=', $this->site_id ];
            if ($state !== '') {
                $condition[] = [ 'npc.state', '=', $state ];
            }
            $res = $coupon_model->getMemberCouponPageList($condition, $page, $page_size);
            return $res;
        } else {
            $coupon_type_id = input('coupon_type_id', 0);
            $this->assign('coupon_type_id', $coupon_type_id);
            $this->assign('get_type', $coupon_model->getCouponGetType());
            return $this->fetch("coupon/receive");
        }
    }

    /**
     * 优惠券回收
     */
    public function recoveryCoupon()
    {
        if (request()->isAjax()) {
            $conpon_list = json_decode(input('coupon_list', '[]'), true);
            return (new MemberCoupon())->recoveryCoupon($conpon_list, $this->site_id);
        }
    }
}