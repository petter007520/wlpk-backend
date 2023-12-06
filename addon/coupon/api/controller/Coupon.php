<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\coupon\api\controller;

use app\api\controller\BaseApi;
use addon\coupon\model\Coupon as CouponModel;
use addon\coupon\model\CouponType as CouponTypeModel;
use addon\coupon\model\MemberCoupon;
use think\facade\Db;

/**
 * 优惠券
 */
class Coupon extends BaseApi
{

    /**
     * 优惠券类型信息
     */
    public function typeinfo()
    {
        $coupon_type_id = isset($this->params[ 'coupon_type_id' ]) ? $this->params[ 'coupon_type_id' ] : 0;
        if (empty($coupon_type_id)) {
            return $this->response($this->error('', 'REQUEST_COUPON_TYPE_ID'));
        }

        $app_type = isset($this->params[ 'app_type' ]) ? $this->params[ 'app_type' ] : 'h5';

        $coupon_model = new CouponModel();
        $condition = [
            [ 'coupon_type_id', '=', $coupon_type_id ],
            [ 'is_show', '=', 1 ],
            [ 'site_id', '=', $this->site_id ]
        ];

        $coupon_type_model = new CouponTypeModel();
        $qrcode = $coupon_type_model->qrcode($coupon_type_id, $app_type, $this->site_id)[ 'data' ];

        $info = $coupon_model->getCouponTypeInfo($condition);
        if (!empty($info[ 'data' ]) && !empty($qrcode)) {
            $info[ 'data' ][ 'qrcode' ] = $qrcode[ 'path' ];
        }
        return $this->response($info);
    }

    /**
     * 列表信息
     */
    public function memberpage()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $state = isset($this->params[ 'state' ]) ? $this->params[ 'state' ] : 1;//优惠券状态 1已领用（未使用） 2已使用 3已过期

        $coupon_model = new CouponModel();
        $condition = [
            [ 'npc.member_id', '=', $token[ 'data' ][ 'member_id' ] ],
            [ 'npc.state', '=', $state ]
        ];

        //按类型筛选
        $type = isset($this->params[ 'type' ]) ? $this->params[ 'type' ] : '';
        $related_id = isset($this->params[ 'related_id' ]) ? $this->params[ 'related_id' ] : 0;
        switch ( $type ) {
            case "reward"://满减
                $condition[] = [ "npc.type", "=", "reward" ];
                break;
            case "discount"://折扣
                $condition[] = [ "npc.type", "=", "discount" ];
                break;
            case "no_threshold"://无门槛
                $condition[] = [ "npc.at_least", "=", 0 ];
                break;
        }
        if (!empty($related_id)) {
            $condition[] = [ 'related_id', '=', $related_id ];
        }

        $list = $coupon_model->getMemberCouponPageList($condition, $page, $page_size);

        return $this->response($list);
    }

    /**
     * 优惠券类型列表
     */
    public function typelists()
    {
        $num = isset($this->params[ 'num' ]) ? $this->params[ 'num' ] : 0;
        $coupon_type_id_arr = isset($this->params[ 'coupon_type_id_arr' ]) ? $this->params[ 'coupon_type_id_arr' ] : '';//coupon_type_id数组
        $can_receive = isset($this->params[ 'can_receive' ]) ? $this->params[ 'can_receive' ] : 0;// 是否只查询可领取的

        $token = $this->checkToken();

        $coupon_model = new CouponModel();
        $condition = [
            [ 'status', '=', 1 ],
            [ 'is_show', '=', 1 ],
            [ 'site_id', '=', $this->site_id ]
        ];

        //按类型查询
        $type = isset($this->params[ 'type' ]) ? $this->params[ 'type' ] : '';
        switch ( $type ) {
            case "reward"://满减
                $condition[] = [ "type", "=", "reward" ];
                break;
            case "discount"://折扣
                $condition[] = [ "type", "=", "discount" ];
                break;
            case "no_threshold"://无门槛
                $condition[] = [ "at_least", "=", 0 ];
                break;
        }

        if (!empty($coupon_type_id_arr)) {
            $condition[] = [ 'coupon_type_id', 'in', $coupon_type_id_arr ];
        }
        $field = 'coupon_type_id,type,site_id,coupon_name,money,discount,max_fetch,at_least,end_time,image,validity_type,fixed_term,status,is_show,goods_type,discount_limit,count,lead_count,IF(count < 0 or count - lead_count > 0, 1, 0) as is_remain';
        if ($can_receive == 1) {
            $condition[] = [ [ 'count', '<>', Db::raw('lead_count') ] ];
        }

        $order = Db::raw('IF(count < 0 or count - lead_count > 0, 1, 0) DESC,sort ASC');

        $list = $coupon_model->getCouponTypeList($condition, $field, $order, $num);
        if (!empty($list[ 'data' ]) && $this->member_id) {
            foreach ($list[ 'data' ] as $k => $v) {
                $list[ 'data' ][ $k ][ 'member_coupon_num' ] = $coupon_model->getCouponCount([
                    [ 'get_type', '=', 2 ],
                    [ 'member_id', '=', $this->member_id ],
                    [ 'coupon_type_id', '=', $v[ 'coupon_type_id' ] ]
                ])[ 'data' ];
            }
        }

        return $this->response($list);
    }

    /**
     * 优惠券类型分页列表
     */
    public function typepagelists()
    {
        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $coupon_type_id_arr = isset($this->params[ 'coupon_type_id_arr' ]) ? $this->params[ 'coupon_type_id_arr' ] : '';//coupon_type_id数组
        $can_receive = isset($this->params[ 'can_receive' ]) ? $this->params[ 'can_receive' ] : 0;// 是否只查询可领取的

        $token = $this->checkToken();

        $coupon_model = new CouponModel();
        $condition = [
            [ 'status', '=', 1 ],
            [ 'is_show', '=', 1 ],
            [ 'site_id', '=', $this->site_id ]
        ];

        //按类型查询
        $type = isset($this->params[ 'type' ]) ? $this->params[ 'type' ] : '';
        switch ( $type ) {
            case "reward"://满减
                $condition[] = [ "type", "=", "reward" ];
                break;
            case "discount"://折扣
                $condition[] = [ "type", "=", "discount" ];
                break;
            case "no_threshold"://无门槛
                $condition[] = [ "at_least", "=", 0 ];
                break;
        }

        if (!empty($coupon_type_id_arr)) {
            $condition[] = [ 'coupon_type_id', 'in', $coupon_type_id_arr ];
        }
        $field = 'coupon_type_id,type,site_id,coupon_name,money,discount,max_fetch,at_least,end_time,image,validity_type,fixed_term,status,is_show,goods_type,discount_limit,count,lead_count,IF(count < 0 or count - lead_count > 0, 1, 0) as is_remain';
        if ($can_receive == 1) {
            $condition[] = [ [ 'count', '<>', Db::raw('lead_count') ] ];
        }

        if ($this->member_id) {
            $prefix = config('database.connections.mysql.prefix');
            $field .= ', (select count(coupon_id) from ' . $prefix . 'promotion_coupon pc where pc.coupon_type_id = ct.coupon_type_id and pc.get_type=2 and pc.member_id=' . $this->member_id . ') as member_coupon_num';
        }

        $order = Db::raw('IF(count < 0 or count - lead_count > 0, 1, 0) DESC,sort ASC');

        $list = $coupon_model->getCouponTypePageList($condition, $page, $page_size, $order, $field, 'ct');

        return $this->response($list);
    }

    /**
     * 获取优惠券
     */
    public function receive()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $site_id = $this->site_id;
        $coupon_type_id = isset($this->params[ 'coupon_type_id' ]) ? $this->params[ 'coupon_type_id' ] : 0;
        $get_type = isset($this->params[ 'get_type' ]) ? $this->params[ 'get_type' ] : 2;//获取方式:1订单2.直接领取3.活动领取

        if (empty($coupon_type_id)) {
            return $this->response($this->error('', 'REQUEST_COUPON_TYPE_ID'));
        }

        $coupon_model = new CouponModel();
        $res = $coupon_model->receiveCoupon($coupon_type_id, $site_id, $token[ 'data' ][ 'member_id' ], $get_type);
        $res[ 'data' ] = [];
        //判断一下用户是否拥有当前优惠券
        $coupon_result = $coupon_model->getCouponInfo([ [ 'coupon_type_id', '=', $coupon_type_id ], [ 'site_id', '=', $site_id ], [ 'member_id', '=', $token[ 'data' ][ 'member_id' ] ] ], 'coupon_id');
        $coupon = $coupon_result[ 'data' ];
        $res[ 'data' ][ 'is_exist' ] = empty($coupon) ? 0 : 1;
        return $this->response($res);
    }

    /**
     * 会员优惠券数量
     * @return string
     */
    public function num()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $state = $this->params[ 'state' ] ?? 1;
        $coupon_model = new MemberCoupon();

        $count = $coupon_model->getMemberCouponNum($token[ 'data' ][ 'member_id' ], $state);
        return $this->response($count);
    }

    /**
     * 是否可以领取
     */
    public function receivedNum()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $coupon_type_id = isset($this->params[ 'coupon_type_id' ]) ? $this->params[ 'coupon_type_id' ] : 0;

        $coupon_model = new MemberCoupon();
        $res = $coupon_model->receivedNum($coupon_type_id, $this->member_id);
        return $this->response($res);
    }

    /**
     * 查询商品可用的优惠券
     * @param int $id
     * @return false|string
     */
    public function goodsCoupon($id = 0)
    {
        $this->checkToken();
        $coupon_model = new CouponModel();
        $goods_id = $this->params[ 'goods_id' ] ?? 0;
        if (!empty($id)) {
            $goods_id = $id;
        }
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'status', '=', 1 ],
            [ 'is_show', '=', 1 ],
            [ 'goods_type', '=', 1 ]
        ];

        $field = 'count,lead_count,coupon_type_id,coupon_type_id as type_id,type,site_id,coupon_name,money,discount,max_fetch,at_least,end_time,validity_type,fixed_term,goods_type,discount_limit';

        if ($this->member_id) {
            $prefix = config('database.connections.mysql.prefix');
            $field .= ',(select count(coupon_id) from ' . $prefix . 'promotion_coupon pc where pc.coupon_type_id = type_id and pc.get_type=2 and pc.member_id=' . $this->member_id . ') as member_coupon_num';
        }

        $list = $coupon_model->getCouponTypeList($condition, $field, "money desc", null, 'ct');
        $goods_condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'status', '=', 1 ],
            [ 'is_show', '=', 1 ],
            [ 'goods_type', '=', 2 ],
            [ 'goods_ids', 'like', "%,$goods_id,%" ]
        ];

        $goods_coupon = $coupon_model->getCouponTypeList($goods_condition, $field, "money desc", null, 'ct');

        if (!empty($goods_coupon[ 'data' ])) {
            $list[ 'data' ] = array_merge($list[ 'data' ], $goods_coupon[ 'data' ]);
        }

        if ($list[ 'data' ] && $this->member_id) {
            foreach ($list[ 'data' ] as $k => $v) {
                // 已抢光
                if ($v[ 'count' ] == $v[ 'lead_count' ]) {
                    unset($list[ 'data' ][ $k ]);
                } elseif ($v[ 'max_fetch' ] != 0 && $v[ 'member_coupon_num' ] > 0 && $v[ 'member_coupon_num' ] >= $v[ 'max_fetch' ]) {
                    // 已领取
                    unset($list[ 'data' ][ $k ]);
                }
            }
            $list[ 'data' ] = array_values($list[ 'data' ]);
        }
        return $this->response($list);
    }

    /**
     * 查询优惠券通过优惠券类型id
     */
    public function couponById()
    {
        $id = $this->params[ 'id' ] ?? 0;
        $coupon_model = new CouponModel();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'status', '=', 1 ],
            [ 'coupon_type_id', 'in', $id ]
        ];
        $list = $coupon_model->getCouponTypeList($condition, "coupon_type_id,type,site_id,coupon_name,money,discount,max_fetch,at_least,end_time,validity_type,fixed_term,goods_type,discount_limit", "money desc", "");
        return $this->response($list);
    }
}