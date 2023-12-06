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

use addon\shopwithdraw\model\Config as ShopWithdrawConfig;
use app\model\shop\ShopApply;
use app\model\shop\ShopGroup as ShopGroupModel;
use app\model\system\Promotion as PromotionModel;
use app\model\web\WebSite as WebsiteModel;
use app\model\shop\Config as ShopConfigModel;
use app\model\shop\Shop as ShopModel;
use app\model\shop\ShopReopen as ShopReopenModel;
use app\model\system\Address as AddressModel;


/**
 * 快捷开店认证
 * Class Cert
 * @package app\shop\controller
 */
class Cert extends BaseApi
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
     * 认证首页
     */
    public function index()
    {
        $shop_group_model = new ShopGroupModel();
        $promotion_model = new PromotionModel();
        //插件
        $promotions = $promotion_model->getPromotions();
        $promotions = $promotions[ 'shop' ];
        //店铺等级
        $shop_group = $shop_group_model->getGroupList([ [ 'is_own', '=', 0 ] ], '*', 'fee asc')[ 'data' ];

        foreach ($shop_group as $k => $v) {
            $addon_array = !empty($v[ 'addon_array' ]) ? explode(',', $v[ 'addon_array' ]) : [];

            foreach ($promotions as $key => &$promotion) {
                if (!empty($promotion[ 'is_developing' ])) {
                    unset($promotions[ $key ]);
                    continue;
                }
                $promotion[ 'is_checked' ] = 0;
                if (in_array($promotion[ 'name' ], $addon_array)) {
                    $promotion[ 'is_checked' ] = 1;
                }
                $shop_group[ $k ][ 'promotion' ][] = $promotion;
            }
            array_multisort(array_column($shop_group[ $k ][ 'promotion' ], 'is_checked'), SORT_DESC, $shop_group[ $k ][ 'promotion' ]);
        }

        $data[ 'group_info' ] = $shop_group;

        //查询省级数据列表
        $address_model = new AddressModel();
        $list = $address_model->getAreaList([ [ "pid", "=", 0 ], [ "level", "=", 1 ] ]);
        $data[ 'province_list' ] = $list[ "data" ];

        $shop_apply_model = new ShopApply();
        $shop_apply_info = $shop_apply_model->getApplyDetail([ [ 'uid', '=', $this->uid ] ]);
        $data[ 'shop_apply_info' ] = $shop_apply_info[ 'data' ];

        //商家信息
        $shop_apply_model = new ShopModel();
        $shop = $shop_apply_model->getShopInfo([ [ 'site_id', '=', $this->site_id ] ], 'site_id,site_name,category_id,category_name');
        $data[ 'shop_info' ] = $shop[ 'data' ];

        //平台配置信息
        $website_model = new WebsiteModel();
        $website_info = $website_model->getWebSite([ [ 'site_id', '=', 0 ] ], 'web_qrcode,web_phone');
        $data[ 'website_info' ] = $website_info[ 'data' ];

        //收款信息
        $shop_config_model = new ShopConfigModel();
        $receivable_config = $shop_config_model->getSystemBankAccount();
        $data[ 'receivable_config' ] = $receivable_config[ 'data' ];
        $data[ 'support_transfer_type' ] = $this->getTransferType();

        return $this->response($this->success($data));
    }

    public function getTransferType()
    {
        $support_type = [];
        if (addon_is_exit("shopwithdraw")) {
            $config_model = new ShopWithdrawConfig();
            $config_result = $config_model->getConfig();
            $config = $config_result[ "data" ];
            if ($config[ "is_use" ]) {
                $support_type = explode(",", $config[ "value" ][ "transfer_type" ]);
            } else {
                $support_type = [ "alipay", "bank" ];
            }
        } else {
            $support_type = [ "alipay", "bank" ];
        }
        return $support_type;
    }


    /**
     * 申请续签
     */
    public function reopen()
    {
        if (request()->isPost()) {

            $site_id = $this->site_id;
            $reopen_data = [
                'site_id' => $site_id,//店铺ID
                'apply_year' => isset($this->params[ 'apply_year' ]) ? $this->params[ 'apply_year' ] : '',//入驻年长
                'shop_group_name' => isset($this->params[ 'shop_group_name' ]) ? $this->params[ 'shop_group_name' ] : '',//开店套餐名称
                'shop_group_id' => isset($this->params[ 'shop_group_id' ]) ? $this->params[ 'shop_group_id' ] : 0,//开店套餐id
                'paying_money_certificate' => isset($this->params[ 'paying_money_certificate' ]) ? $this->params[ 'paying_money_certificate' ] : '',//支付凭证
                'paying_money_certificate_explain' => isset($this->params[ 'paying_money_certificate_explain' ]) ? $this->params[ 'paying_money_certificate_explain' ] : '' //付款凭证说明
            ];

            $model = new ShopReopenModel();
            //计算入驻金额
            $apply_money = $model->getReopenMoney($reopen_data[ 'apply_year' ], $reopen_data[ 'shop_group_id' ]);
            $reopen_data[ 'paying_amount' ] = $apply_money[ 'data' ][ 'money' ];

            $result = $model->addReopen($reopen_data);

            return $this->response($result);
        } else {
            //获取店铺信息
            $condition[] = [ 'site_id', '=', $this->site_id ];
            $field = 'site_id,site_name,category_id,category_name,group_id,group_name';
            $shop_model = new ShopModel();
            $shop_info = $shop_model->getShopInfo($condition, $field);
            $data[ 'shop_info' ] = $shop_info[ 'data' ];

            $shop_group_model = new ShopGroupModel();
            $promotion_model = new PromotionModel();
            //插件
            $promotions = $promotion_model->getPromotions();
            $promotions = $promotions[ 'shop' ];
            //店铺等级
            $shop_group = $shop_group_model->getGroupList([ [ 'is_own', '=', 0 ] ], '*', 'fee asc');
            $shop_group = $shop_group[ 'data' ];

            foreach ($shop_group as $k => $v) {
                $addon_array = !empty($v[ 'addon_array' ]) ? explode(',', $v[ 'addon_array' ]) : [];

                foreach ($promotions as $key => &$promotion) {
                    if (!empty($promotion[ 'is_developing' ])) {
                        unset($promotions[ $key ]);
                        continue;
                    }
                    $promotion[ 'is_checked' ] = 0;
                    if (in_array($promotion[ 'name' ], $addon_array)) {
                        $promotion[ 'is_checked' ] = 1;
                    }
                    $shop_group[ $k ][ 'promotion' ][] = $promotion;
                }
                array_multisort(array_column($shop_group[ $k ][ 'promotion' ], 'is_checked'), SORT_DESC, $shop_group[ $k ][ 'promotion' ]);
            }
            $data[ 'group_info' ] = $shop_group;

            //平台配置信息
            $website_model = new WebsiteModel();
            $website_info = $website_model->getWebSite([ [ 'site_id', '=', 0 ] ], 'web_qrcode,web_phone');
            $data[ 'website_info' ] = $website_info[ 'data' ];

            //收款信息
            $shop_config_model = new ShopConfigModel();
            $receivable_config = $shop_config_model->getSystemBankAccount();
            $data[ 'receivable_config' ] = $receivable_config[ 'data' ];

            return $this->response($this->success($data));
        }

    }

    /**
     * 编辑续签首页
     */
    public function editReopenInfo()
    {
        $reopen_model = new ShopReopenModel();
        if (request()->isPost()) {
            $site_id = $this->site_id;
            $reopen_data = [
                'id' => isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : '',
                'site_id' => $site_id,//店铺ID
                'paying_money_certificate' => isset($this->params[ 'paying_money_certificate' ]) ? $this->params[ 'paying_money_certificate' ] : '',//支付凭证
                'paying_money_certificate_explain' => isset($this->params[ 'paying_money_certificate_explain' ]) ? $this->params[ 'paying_money_certificate_explain' ] : '' //付款凭证说明
            ];

            $result = $reopen_model->editReopen($reopen_data);

            return $this->response($result);
        } else {
            //获取店铺信息
            $condition[] = [ 'site_id', '=', $this->site_id ];
            $apply_model = new ShopModel();
            $field = 'site_id,site_name,category_id,category_name,group_id,group_name';
            $shop_info = $apply_model->getShopInfo($condition, $field);
            $data[ 'shop_info' ] = $shop_info[ 'data' ];

            $shop_group_model = new ShopGroupModel();
            $promotion_model = new PromotionModel();
            //插件
            $promotions = $promotion_model->getPromotions();
            $promotions = $promotions[ 'shop' ];
            //店铺等级
            $shop_group = $shop_group_model->getGroupList([ [ 'is_own', '=', 0 ] ], '*', 'fee asc')[ 'data' ];

            foreach ($shop_group as $k => $v) {
                $addon_array = !empty($v[ 'addon_array' ]) ? explode(',', $v[ 'addon_array' ]) : [];

                foreach ($promotions as $key => &$promotion) {
                    if (!empty($promotion[ 'is_developing' ])) {
                        unset($promotions[ $key ]);
                        continue;
                    }
                    $promotion[ 'is_checked' ] = 0;
                    if (in_array($promotion[ 'name' ], $addon_array)) {
                        $promotion[ 'is_checked' ] = 1;
                    }
                    $shop_group[ $k ][ 'promotion' ][] = $promotion;
                }
                array_multisort(array_column($shop_group[ $k ][ 'promotion' ], 'is_checked'), SORT_DESC, $shop_group[ $k ][ 'promotion' ]);
            }
            $data[ 'group_info' ] = $shop_group;

            //平台配置信息
            $website_model = new WebsiteModel();
            $website_info = $website_model->getWebSite([ [ 'site_id', '=', 0 ] ], 'web_qrcode,web_phone');
            $data[ 'website_info' ] = $website_info[ 'data' ];

            //收款信息
            $shop_config_model = new ShopConfigModel();
            $receivable_config = $shop_config_model->getSystemBankAccount();
            $data[ 'receivable_config' ] = $receivable_config[ 'data' ];

            //获取续签信息
            $reopen_info = $reopen_model->getReopenInfo([ [ 'sr.apply_state', 'in', '-1,1' ], [ 'sr.site_id', '=', $this->site_id ] ], '*');
            $data[ 'reopen_info' ] = $reopen_info[ 'data' ];

            return $this->response($this->success($data));
        }

    }

}