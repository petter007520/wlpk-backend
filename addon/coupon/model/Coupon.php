<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\coupon\model;

use app\model\BaseModel;
use app\model\system\Stat;

/**
 * 优惠券
 */
class Coupon extends BaseModel
{
    /**
     * 获取优惠券来源方式
     * @param $type
     */
    public function getCouponGetType($type = '')
    {
        $get_type = [
            1 => '消费奖励',
            2 => '直接领取',
            3 => '会员升级奖励',
            4 => '商家发放',
            6 => '活动奖励'
        ];
        $event = event('CouponGetType');
        if (!empty($event)) {
            foreach ($event as $k => $v) {
                $get_type[ array_keys($v)[ 0 ] ] = array_values($v)[ 0 ];
            }
        }
        if ($type) return $get_type[ $type ] ?? '';
        else return $get_type;
    }

    /**
     * 获取编码
     */
    public function getCode()
    {
        return random_keys(8);
    }

    /**
     * 领取优惠券
     * @param $coupon_type_id
     * @param $site_id
     * @param $member_id
     * @param $get_type
     * @param int $is_stock
     * @param int $is_limit
     * @return array
     */
    public function receiveCoupon($coupon_type_id, $site_id, $member_id, $get_type, $is_stock = 0, $is_limit = 1)
    {
        // 用户已领取数量
        if (empty($member_id)) {
            return $this->error('', '请先进行登录');
        }
        $coupon_type_info = model('promotion_coupon_type')->getInfo([ 'coupon_type_id' => $coupon_type_id, 'site_id' => $site_id ]);
        if (!empty($coupon_type_info)) {
            if ($coupon_type_info[ 'count' ] != -1 || $is_stock == 0) {
                if ($coupon_type_info[ 'count' ] == $coupon_type_info[ 'lead_count' ]) {
                    return $this->error('', '来迟了该优惠券已被领取完了');
                }
            }
            if ($coupon_type_info[ 'max_fetch' ] != 0 && $get_type == 2) {
                //限制领取
                $member_receive_num = model('promotion_coupon')->getCount([
                    'coupon_type_id' => $coupon_type_id,
                    'member_id' => $member_id,
                    'get_type' => 2
                ]);
                if ($member_receive_num >= $coupon_type_info[ 'max_fetch' ] && $is_limit == 1) {
                    return $this->error('', '该优惠券领取已达到上限');
                }
            }
            //只有正在进行中的优惠券可以添加或者发送领取)
            if ($coupon_type_info[ 'status' ] != 1) {
                return $this->error('', '该优惠券已过期');
            }
            $data = [
                'coupon_type_id' => $coupon_type_id,
                'site_id' => $site_id,
                'coupon_code' => $this->getCode(),
                'member_id' => $member_id,
                'money' => $coupon_type_info[ 'money' ],
                'state' => 1,
                'get_type' => $get_type,
                'goods_type' => $coupon_type_info[ 'goods_type' ],
                'fetch_time' => time(),
                'coupon_name' => $coupon_type_info[ 'coupon_name' ],
                'at_least' => $coupon_type_info[ 'at_least' ],
                'type' => $coupon_type_info[ 'type' ],
                'discount' => $coupon_type_info[ 'discount' ],
                'discount_limit' => $coupon_type_info[ 'discount_limit' ],
                'goods_ids' => $coupon_type_info[ 'goods_ids' ],
            ];

            if ($coupon_type_info[ 'validity_type' ] == 0) {
                $data[ 'end_time' ] = $coupon_type_info[ 'end_time' ];
            } elseif ($coupon_type_info[ 'validity_type' ] == 1) {
                $data[ 'end_time' ] = ( time() + $coupon_type_info[ 'fixed_term' ] * 86400 );
            }
            $res = model('promotion_coupon')->add($data);
            if ($is_stock == 0) {
                model('promotion_coupon_type')->setInc([ [ 'coupon_type_id', '=', $coupon_type_id ] ], 'lead_count');
            }
            $stat_model = new Stat();
            $stat_model->switchStat([ 'type' => 'receive_coupon', 'data' => [
                'site_id' => $site_id,
                'coupon_id' => $res
            ] ]);
            return $this->success($res);

        } else {
            return $this->error('', '未查找到该优惠券');
        }
    }

    /**
     * 发放优惠券
     * @param array $coupon_data [ ['coupon_type_id' => xx, 'num' => xx ] ]
     * @param int $site_id
     * @param int $member_id
     * @param int $get_type
     * @param int $related_id
     */
    public function giveCoupon(array $coupon_data, int $site_id, int $member_id, $get_type = 4, $related_id = 0)
    {
        if (empty($member_id)) return $this->error('', '请先选择会员');

        try {
            $coupon_list = [];
            foreach ($coupon_data as $item) {
                $coupon_type_info = model('promotion_coupon_type')->getInfo([ 'coupon_type_id' => $item[ 'coupon_type_id' ], 'site_id' => $site_id, 'status' => 1 ]);
                if (!empty($coupon_type_info)) {
                    $data = [
                        'coupon_type_id' => $item[ 'coupon_type_id' ],
                        'site_id' => $site_id,
                        'coupon_code' => $this->getCode(),
                        'member_id' => $member_id,
                        'money' => $coupon_type_info[ 'money' ],
                        'state' => 1,
                        'get_type' => $get_type,
                        'goods_type' => $coupon_type_info[ 'goods_type' ],
                        'fetch_time' => time(),
                        'coupon_name' => $coupon_type_info[ 'coupon_name' ],
                        'at_least' => $coupon_type_info[ 'at_least' ],
                        'type' => $coupon_type_info[ 'type' ],
                        'discount' => $coupon_type_info[ 'discount' ],
                        'discount_limit' => $coupon_type_info[ 'discount_limit' ],
                        'goods_ids' => $coupon_type_info[ 'goods_ids' ],
                        'related_id' => $related_id,
                        'end_time' => 0
                    ];
                    if ($coupon_type_info[ 'validity_type' ] == 0) {
                        $data[ 'end_time' ] = $coupon_type_info[ 'end_time' ];
                    } elseif ($coupon_type_info[ 'validity_type' ] == 1) {
                        $data[ 'end_time' ] = ( time() + $coupon_type_info[ 'fixed_term' ] * 86400 );
                    }
                    for ($i = 0; $i < $item[ 'num' ]; $i++) {
                        $data[ 'coupon_code' ] = $this->getCode();
                        array_push($coupon_list, $data);
                    }
                    model('promotion_coupon_type')->setInc([ [ 'coupon_type_id', '=', $item[ 'coupon_type_id' ] ] ], 'lead_count', $item[ 'num' ]);
                }
            }
            if (empty($coupon_list)) return $this->error('', '没有可发放的优惠券');

            $res = model('promotion_coupon')->addList($coupon_list);
            return $this->success($res);
        } catch (\Exception $e) {
            return $this->error('', '发放失败');
        }
    }

    /**
     * 使用优惠券
     * @param $data
     */
    public function useCoupon($coupon_id, $member_id, $use_order_id)
    {
        $data = array ( 'use_order_id' => $use_order_id, 'use_time' => time(), 'state' => 2 );
        $condition = array (
            [ 'coupon_id', '=', $coupon_id ],
            [ 'member_id', '=', $member_id ],
            [ 'state', '=', 1 ]
        );
        $result = model("promotion_coupon")->update($data, $condition);
        return $this->success($result);
    }

    /**
     * 退还优惠券
     * @param $coupon_id
     * @param $member_id
     */
    public function refundCoupon($coupon_id, $member_id)
    {
        //获取优惠券信息
        $info = model("promotion_coupon")->getInfo([ [ 'coupon_id', '=', $coupon_id ], [ 'member_id', '=', $member_id ], [ 'state', '=', 2 ] ]);
        if (empty($info)) {
            return $this->success();
        }

        $data = [ 'use_time' => 0, 'state' => 1 ];
        //判断优惠券是否过期
        if ($info[ 'end_time' ] <= time()) {
            $data[ 'state' ] = 3;
        }

        $result = model("promotion_coupon")->update($data, [ [ 'coupon_id', '=', $coupon_id ], [ 'member_id', '=', $member_id ], [ 'state', '=', 2 ] ]);
        return $this->success($result);
    }

    /**
     * 获取优惠券信息
     * @param $condition $coupon_code 优惠券编码
     * @param $field
     * @return array
     */
    public function getCouponInfo($condition, $field)
    {
        $info = model("promotion_coupon")->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取优惠券数量
     * @param $condition $coupon_code 优惠券编码
     * @return array
     */
    public function getCouponCount($condition)
    {
        $info = model("promotion_coupon")->getCount($condition);
        return $this->success($info);
    }

    /**
     * 获取优惠券列表
     * @param array $condition
     * @param bool $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getCouponList($condition = [], $field = true, $order = '', $limit = null)
    {
        $list = model("promotion_coupon")->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取优惠券列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getCouponPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'fetch_time desc', $field = 'coupon_id,type,discount,coupon_type_id,coupon_name,site_id,coupon_code,member_id,use_order_id,at_least,money,state,get_type,fetch_time,use_time,end_time', $alias = 'a', $join = [])
    {
        $list = model('promotion_coupon')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取会员优惠券列表
     * @param $condition
     * @param int $page
     * @param int $page_size
     * @return array
     */
    public function getMemberCouponPageList($condition, $page = 1, $page_size = PAGE_LIST_ROWS)
    {
        $field = 'npc.coupon_name,npc.type,npc.use_order_id,npc.coupon_id,npc.coupon_type_id,npc.site_id,npc.coupon_code,npc.member_id,npc.discount_limit,
		npc.at_least,npc.money,npc.discount,npc.state,npc.get_type,npc.fetch_time,npc.use_time,npc.end_time,mem.nickname,on.order_no,npc.end_time,mem.nickname,mem.headimg,mem.mobile';
        $alias = 'npc';
        $join = [
            [
                'member mem',
                'npc.member_id = mem.member_id',
                'inner'
            ],
            [
                'order on',
                'npc.use_order_id = on.order_id',
                'left'
            ]
        ];
        $list = model("promotion_coupon")->pageList($condition, $field, 'fetch_time desc', $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取优惠券信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getCouponTypeInfo($condition, $field = 'coupon_type_id,site_id,coupon_name,money,count,lead_count,max_fetch,at_least,end_time,image,validity_type,fixed_term,status,type,discount')
    {
        $info = model("promotion_coupon_type")->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取优惠券列表
     * @param array $condition
     * @param bool $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getCouponTypeList($condition = [], $field = true, $order = '', $limit = null, $alias = '', $join = [])
    {
        $list = model("promotion_coupon_type")->getList($condition, $field, $order, $alias, $join, '', $limit);
        return $this->success($list);
    }

    /**
     * 获取优惠券分页列表
     * @param $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getCouponTypePageList($condition, $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'coupon_type_id desc', $field = '*', $alias = '', $join = [])
    {
        $list = model("promotion_coupon_type")->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取会员已领取优惠券优惠券
     * @param $member_id
     * @param $state
     * @param int $site_id
     * @param int $money
     * @param string $order
     * @return array
     */
    public function getMemberCouponList($member_id, $state, $site_id = 0, $money = 0, $order = "fetch_time desc")
    {
        $condition = array (
            [ "member_id", "=", $member_id ],
            [ "state", "=", $state ],
//            [ "end_time", ">", time()]
        );
        if ($site_id > 0) {
            $condition[] = [ "site_id", "=", $site_id ];
        }
        if ($money > 0) {
//            $condition[] = [ "at_least", "=", 0 ];
            $condition[] = [ "at_least", "<=", $money ];
        }
        $list = model("promotion_coupon")->getList($condition, "*", $order, '', '', '', 0);
        return $this->success($list);
    }

    public function getMemberCouponCount($condition)
    {
        $list = model("promotion_coupon")->getCount($condition);
        return $this->success($list);
    }

    /**
     * 增加库存
     * @param $param
     * @return array
     */
    public function incStock($param)
    {
        $condition = array (
            [ "coupon_type_id", "=", $param[ "coupon_type_id" ] ]
        );
        $num = $param[ "num" ];
        $coupon_info = model("promotion_coupon_type")->getInfo($condition, "count,lead_count");
        if (empty($coupon_info))
            return $this->error(-1, "");

        //更新优惠券库存
        $result = model("promotion_coupon_type")->setDec($condition, "lead_count", $num);
        return $this->success($result);
    }

    /**
     * 减少库存
     * @param $param
     * @return array
     */
    public function decStock($param)
    {
        $condition = array (
            [ "coupon_type_id", "=", $param[ "coupon_type_id" ] ]
        );
        $num = $param[ "num" ];
        $coupon_info = model("promotion_coupon_type")->getInfo($condition, "count,lead_count");
        if (empty($coupon_info))
            return $this->error(-1, "找不到优惠券");

        //编辑sku库存

        if ($coupon_info[ "count" ] != -1) {
            if (( $coupon_info[ "count" ] - $coupon_info[ "lead_count" ] ) < $num)
                return $this->error(-1, "库存不足");
        }

        $result = model("promotion_coupon_type")->setInc($condition, "lead_count", $num);
        if ($result === false)
            return $this->error();

        return $this->success($result);
    }

    /**
     * 定时关闭
     * @return mixed
     */
    public function cronCouponEnd()
    {
        $res = model("promotion_coupon")->update([ 'state' => 3 ], [ [ 'state', '=', 1 ], [ 'end_time', '>', 0 ], [ 'end_time', '<=', time() ] ]);
        return $res;
    }

    /**
     * 核验会员是否还可以领用某一张优惠券
     * @param $params
     * @return array
     */
    public function checkMemberReceiveCoupon($params)
    {
        $member_id = $params[ 'member_id' ];//会员id
        $coupon_type_info = $params[ 'coupon_type_info' ];
        $site_id = $params[ 'site_id' ];
        $coupon_type_id = $params[ 'coupon_type_id' ] ?? 0;
        if ($coupon_type_id > 0) {
            $coupon_type_info = model('promotion_coupon_type')->getInfo([ 'coupon_type_id' => $coupon_type_id, 'site_id' => $site_id ]);
        }
        if (!empty($coupon_type_info)) {
            $coupon_type_id = $coupon_type_info[ 'coupon_type_id' ] ?? 0;
            if ($coupon_type_info[ 'count' ] != -1) {
                if ($coupon_type_info[ 'count' ] == $coupon_type_info[ 'lead_count' ]) {
                    return $this->error('', '来迟了该优惠券已被领取完了');
                }
            }
            if ($coupon_type_info[ 'max_fetch' ] != 0) {
                //限制领取
                $member_receive_num = model('promotion_coupon')->getCount([
                    'coupon_type_id' => $coupon_type_id,
                    'member_id' => $member_id,
                    'get_type' => 2
                ]);
                if ($member_receive_num >= $coupon_type_info[ 'max_fetch' ]) {
                    return $this->error('', '该优惠券领取已达到上限');
                }
            }
            //只有正在进行中的优惠券可以添加或者发送领取)
            if ($coupon_type_info[ 'status' ] != 1) {
                return $this->error('', '该优惠券已过期');
            }
        }
        return $this->success();
    }
}