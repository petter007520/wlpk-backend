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

use app\model\shop\ShopReopen as ShopReopenModel;

/**
 * 店铺
 * Class Shop
 * @package app\shop\controller
 */
class Shopreopen extends BaseApi
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


    public function getReopenDetail()
    {
        $reopen_model = new ShopReopenModel();
        $reopen_info = $reopen_model->getReopenInfo([ [ 'sr.apply_state', 'in', '-1,1' ], [ 'sr.site_id', '=', $this->site_id ] ], '*');
        return $this->response($reopen_info);
    }

    /**
     * 获取续费信息
     */
    public function getReopenInfo()
    {
        $reopen_model = new ShopReopenModel();

        $id = isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : '';
        //获取续签信息
        $result = $reopen_model->getReopenInfo([ [ 'sr.id', '=', $id ], [ 'sr.site_id', '=', $this->site_id ] ], '*');
        return $this->response($result);
    }

    /**
     * 添加续签
     * @return false|string
     */
    public function addReopen()
    {
        $reopen_data = [
            'site_id' => $this->site_id,//店铺ID
            'apply_year' => isset($this->params[ 'apply_year' ]) ? $this->params[ 'apply_year' ] : '',//入驻年长
            'shop_group_name' => isset($this->params[ 'shop_group_name' ]) ? $this->params[ 'shop_group_name' ] : '',//开店套餐名称
            'shop_group_id' => isset($this->params[ 'shop_group_id' ]) ? $this->params[ 'shop_group_id' ] : '',//开店套餐id
            'paying_money_certificate' => isset($this->params[ 'paying_money_certificate' ]) ? $this->params[ 'paying_money_certificate' ] : '',//支付凭证
            'paying_money_certificate_explain' => isset($this->params[ 'paying_money_certificate_explain' ]) ? $this->params[ 'paying_money_certificate_explain' ] : ''//付款凭证说明
        ];

        $reopen_model = new ShopReopenModel();
        //计算入驻金额
        $apply_money = $reopen_model->getReopenMoney($reopen_data[ 'apply_year' ], $reopen_data[ 'shop_group_id' ]);
        $reopen_data[ 'paying_amount' ] = $apply_money[ 'data' ][ 'money' ];

        $result = $reopen_model->addReopen($reopen_data);

        return $this->response($result);
    }

    /**
     * 编辑续签
     * @return false|string
     */
    public function editReopen()
    {
        $reopen_data = [
            'site_id' => $this->site_id,
            'id' => isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : '',
            'paying_money_certificate' => isset($this->params[ 'paying_money_certificate' ]) ? $this->params[ 'paying_money_certificate' ] : '',//支付凭证
            'paying_money_certificate_explain' => isset($this->params[ 'paying_money_certificate_explain' ]) ? $this->params[ 'paying_money_certificate_explain' ] : ''//付款凭证说明
        ];

        $model = new ShopReopenModel();
        $result = $model->editReopen($reopen_data);
        return $this->response($result);
    }

    /*
     * 删除续签
     */
    public function deleteReopen()
    {
        $id = isset($this->params[ 'id' ]) ? $this->params[ 'id' ] : '';
        $model = new ShopReopenModel();

        $res = $model->deleteReopen($id);
        return $this->response($res);
    }

    /**
     * 获取续签金额
     */
    public function getReopenMoney()
    {
        $apply_year = isset($this->params[ 'apply_year' ]) ? $this->params[ 'apply_year' ] : '';//入驻年长
        $group_id = isset($this->params[ 'group_id' ]) ? $this->params[ 'group_id' ] : '';//店铺等级ID

        $model = new ShopReopenModel();
        $result = $model->getReopenMoney($apply_year, $group_id);
        return $this->response($result);
    }

}