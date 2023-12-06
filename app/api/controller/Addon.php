<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 * @author : niuteam
 */

namespace app\api\controller;

use app\model\system\Addon as AddonModel;

/**
 * 插件管理
 * @author Administrator
 *
 */
class Addon extends BaseApi
{
    /**
     * 列表信息
     */
    public function lists()
    {
        $addon = new AddonModel();
        $list = $addon->getAddonList();
        return $this->response($list);
    }

    public function addonIsExit()
    {
        $addon_model = new AddonModel();
        $addon_data = $addon_model->getAddonList([], 'name');
        $addons = array_column($addon_data[ 'data' ], 'name');
        $res = [];
        $res[ 'fenxiao' ] = in_array("fenxiao", $addons) ? 1 : 0;                        // 分销
        $res[ 'pintuan' ] = in_array("pintuan", $addons) ? 1 : 0;                        // 拼团
        $res[ 'membersignin' ] = in_array("membersignin", $addons) ? 1 : 0;            // 会员签到
        $res[ 'memberrecharge' ] = in_array("memberrecharge", $addons) ? 1 : 0;        // 会员充值
        $res[ 'memberwithdraw' ] = in_array("memberwithdraw", $addons) ? 1 : 0;        // 会员提现
        $res[ 'pointexchange' ] = in_array("pointexchange", $addons) ? 1 : 0;            // 积分兑换
        $res[ 'manjian' ] = in_array("manjian", $addons) ? 1 : 0;                       //满减
        $res[ 'memberconsume' ] = in_array("memberconsume", $addons) ? 1 : 0;            //会员消费
        $res[ 'memberregister' ] = in_array("memberregister", $addons) ? 1 : 0;        //会员注册
        $res[ 'coupon' ] = in_array("coupon", $addons) ? 1 : 0;                        //优惠券
        $res[ 'bundling' ] = in_array("bundling", $addons) ? 1 : 0;                    //组合套餐
        $res[ 'discount' ] = in_array("discount", $addons) ? 1 : 0;                    //限时折扣
        $res[ 'seckill' ] = in_array("seckill", $addons) ? 1 : 0;                        //秒杀
        $res[ 'topic' ] = in_array("topic", $addons) ? 1 : 0;                              //专题活动
        $res[ 'store' ] = in_array("store", $addons) ? 1 : 0;                             //门店管理
        $res[ 'groupbuy' ] = in_array("groupbuy", $addons) ? 1 : 0;                    //团购
        $res[ 'bargain' ] = in_array("bargain", $addons) ? 1 : 0;                   //砍价
        $res[ 'presale' ] = in_array("presale", $addons) ? 1 : 0;                  // 预售
        $res[ 'notes' ] = in_array("notes", $addons) ? 1 : 0;                   // 店铺笔记
        $res[ 'membercancel' ] = in_array("membercancel", $addons) ? 1 : 0;         // 会员注销
        $res[ 'servicer' ] = in_array("servicer", $addons) ? 1 : 0;        // 客服
        $res[ 'live' ] = in_array("live", $addons) ? 1 : 0;        // 小程序直播
        $res[ 'cards' ] = in_array("cards", $addons) ? 1 : 0;       // 刮刮乐
        $res[ 'egg' ] = in_array("egg", $addons) ? 1 : 0;         // 砸金蛋
        $res[ 'turntable' ] = in_array("turntable", $addons) ? 1 : 0;        // 幸运抽奖
        $res[ 'memberrecommend' ] = in_array("memberrecommend", $addons) ? 1 : 0; // 推荐奖励
        $res[ 'supermember' ] = in_array("supermember", $addons) ? 1 : 0; // 超级会员卡
        $res[ 'giftcard' ] = in_array("giftcard", $addons) ? 1 : 0; // 兑换卡
        $res[ 'divideticket' ] = in_array("divideticket", $addons) ? 1 : 0; // 兑换卡
        $res[ 'birthdaygift' ] = in_array("birthdaygift", $addons) ? 1 : 0; // 兑换卡
        $res[ 'scenefestival' ] = in_array("scenefestival", $addons) ? 1 : 0; // 兑换卡
        $res[ 'pinfan' ] = in_array("pinfan", $addons) ? 1 : 0; // 拼团返利
        $res[ 'hongbao' ] = in_array("hongbao", $addons) ? 1 : 0; // 裂变红包
        $res[ 'blindbox' ] = in_array("blindbox", $addons) ? 1 : 0; // 盲盒
        $res[ 'virtualcard' ] = in_array("virtualcard", $addons) ? 1 : 0; // 卡密商品
        $res[ 'cardservice' ] = in_array("cardservice", $addons) ? 1 : 0; // 卡项与服务商品
        $res[ 'cashier' ] = in_array("cashier", $addons) ? 1 : 0; // 收银台
        $res[ 'form' ] = in_array("form", $addons) ? 1 : 0; // 系统表单

        return $this->response($this->success($res));
    }

}