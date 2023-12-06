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

use app\model\express\ExpressCompany;
use app\model\express\ExpressTemplate as ExpressTemplateModel;

/**
 * 配送
 * Class Express
 * @package app\shop\controller
 */
class Express extends BaseApi
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
     * 获取运费模板
     * @return false|string
     */
    public function getExpressTemplateList()
    {
        $express_template_model = new ExpressTemplateModel();
        $express_template_list = $express_template_model->getExpressTemplateList([ [ 'site_id', "=", $this->site_id ] ], 'template_id,template_name', 'is_default desc');
        return $this->response($express_template_list);
    }

    /**
     * 物流公司
     * @return mixed
     */
    public function expressCompany()
    {
        $express_company_model = new ExpressCompany();
        $company_list_result = $express_company_model->getExpressCompanyList([ [ "site_id", "=", $this->site_id ] ]);
        return $this->response($company_list_result);
    }

}