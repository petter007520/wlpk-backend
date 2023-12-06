<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\express;

use think\facade\Cache;
use app\model\BaseModel;

/**
 * 物流公司
 */
class ExpressCompany extends BaseModel
{

    /**
     * 添加店铺物流公司
     * @param $data
     * @return array
     */
    public function addExpressCompany($data)
    {
        $data[ 'create_time' ] = time();
        $data[ 'modify_time' ] = time();

        $company_template = new ExpressCompanyTemplate();
        $company_info = $company_template->getExpressCompanyTemplateInfo([ [ 'company_id', '=', $data[ 'company_id' ] ] ]);
        $data[ 'company_name' ] = $company_info[ 'data' ][ 'company_name' ];
        $data[ 'logo' ] = $company_info[ 'data' ][ 'logo' ];
        $data[ 'express_no' ] = $company_info[ 'data' ][ 'express_no' ];
        $brand_id = model('express_company')->add($data);
        return $this->success($brand_id);
    }

    /**
     * 修改店铺物流公司
     * @param $data
     * @param $condition
     * @return array
     */
    public function editExpressCompany($data, $condition)
    {
        $data[ 'modify_time' ] = time();
        $res = model('express_company')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除店铺物流公司
     * @param $condition
     * @return array
     */
    public function deleteExpressCompany($condition)
    {
        $res = model('express_company')->delete($condition);
        return $this->success($res);
    }

    /**
     * 获取店铺物流公司信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getExpressCompanyInfo($condition, $field = 'id, site_id, company_id, express_no, content_json, background_image, font_size, width, height, create_time, modify_time, scale')
    {
        $res = model('express_company')->getInfo($condition, $field);
        if (!empty($res)) {
            if (empty($res[ 'content_json' ])) {
                $res[ 'content_json' ] = json_encode($this->getPrintItemList());
            }
        }
        return $this->success($res);
    }

    /**
     * 获取店铺物流公司列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @param string $group
     * @param null $limit
     * @return array
     */
    public function getExpressCompanyList($condition = [], $field = 'id, site_id, company_id, express_no, content_json, background_image, font_size, width, height, create_time, modify_time, scale, company_name', $order = '', $alias = '', $join = [], $group = '', $limit = null)
    {
        $list = model('express_company')->getList($condition, $field, $order, $alias, $join, $group, $limit);
        return $this->success($list);
    }

    /**
     * 获取店铺物流公司分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getExpressCompanyPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = 'id, site_id, company_id,express_no, content_json, background_image, font_size, width, height, create_time, modify_time, scale')
    {
        $list = model('express_company')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取打印项
     * @return array
     */
    public function getPrintItemList()
    {
        $data = [
            [
                'item_name' => 'order_no',
                'item_title' => '订单编号',
            ],
            [
                'item_name' => 'sender_company',
                'item_title' => '发件人公司',
            ],
            [
                'item_name' => 'sender_name',
                'item_title' => '发件人姓名',
            ],
            [
                'item_name' => 'sender_address',
                'item_title' => '发件人地址',
            ],
            [
                'item_name' => 'sender_phone',
                'item_title' => '发件人电话',
            ],
            [
                'item_name' => 'sender_post_code',
                'item_title' => '发件人邮编',
            ],
            [
                'item_name' => 'receiver_name',
                'item_title' => '收件人姓名',
            ],
            [
                'item_name' => 'receiver_address',
                'item_title' => '收件人地址',
            ],
            [
                'item_name' => 'receiver_phone',
                'item_title' => '收件人电话',
            ],
            [
                'item_name' => 'receiver_post_code',
                'item_title' => '收件人邮编',
            ],
            [
                'item_name' => 'logistics_number',
                'item_title' => '货到付款物流编号',
            ],
            [
                'item_name' => 'collection_payment',
                'item_title' => '代收金额',
            ],
            [
                'item_name' => 'remark',
                'item_title' => '备注',
            ],
        ];
        return $data;
    }
}