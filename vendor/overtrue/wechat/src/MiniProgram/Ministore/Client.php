<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\MiniProgram\Ministore;

use EasyWeChat\Kernel\BaseClient;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;

class Client extends BaseClient
{
    /************************************************************ 接入申请 *********************************************************/
    /**
     * 申请接入
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function apply(){
        return $this->httpPostJson('shop/register/apply', []);
    }

    /**
     * 完成接入任务
     * @param int $access_info_item
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function finish_access_info(int $access_info_item){
        return $this->httpPostJson('shop/register/finish_access_info', ['access_info_item' => $access_info_item]);
    }

    /**
     * 获取接入状态
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function check(){
        return $this->httpPostJson('shop/register/check', []);
    }

    /************************************************************ 类目 *********************************************************/
    /**
     * 获取所有类目的详情
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(){
        return $this->httpPostJson('shop/cat/get', []);
    }


    /**
     * 品牌审核
     * @param array $audit_req
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function auditbrand(array $audit_req){
        $params = [
            'audit_req' => $audit_req
        ];

        return $this->httpPostJson('shop/audit/audit_brand', $params);
    }

    /**
     * 类目审核
     * @param array $audit_req
     */
    public function auditCategory(array $audit_req){
        $params = [
            'audit_req' => $audit_req
        ];

        return $this->httpPostJson('shop/audit/audit_category', $params);
    }

    /**
     * 获取审核结果
     * @param $audit_id
     */
    public function auditResult($audit_id){
        $params = [
            'audit_id' => $audit_id
        ];

        return $this->httpPostJson('shop/audit/result', $params);
    }

    /**
     *获取小程序资质
     * @param $req_type
     */
    public function getMiniappCertificate($req_type){
        $params = [
            'req_type' => $req_type
        ];

        return $this->httpPostJson('shop/audit/get_miniapp_certificate', $params);
    }
    /************************************************************ 商家入驻查询 *********************************************************/
    /**
     *获取商家类目列表
     */
    public function getCategoryList(){
        $params = [];
        return $this->httpPostJson('shop/account/get_category_list', $params);
    }
    /************************************************************ SPU *********************************************************/
    /**
     * 添加spu
     * @param $params
     */
    public function addSpu($params){
        return $this->httpPostJson('shop/spu/add', $params);
    }

    /**
     * 删除spu
     * @param $product_id
     * @param $out_product_id
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delSpu($product_id, $out_product_id){
        $params = array(
            'product_id' => $product_id,
            'out_product_id' => $out_product_id
        );
        return $this->httpPostJson('shop/spu/del', $params);
    }

    /**
     * 撤回商品审核
     * @param $product_id
     * @param $out_product_id
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delSpuAudit($product_id, $out_product_id){
        $params = array(
            'product_id' => $product_id,
            'out_product_id' => $out_product_id
        );
        return $this->httpPostJson('shop/spu/del_audit', $params);
    }

    /**
     * 获取商品
     * @param $product_id
     * @param $out_product_id
     * @param int $need_edit_spu
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSpu($product_id, $out_product_id, $need_edit_spu = 0){
        $params = array(
            'product_id' => $product_id,
            'out_product_id' => $out_product_id,
            'need_edit_spu' => $need_edit_spu
        );
        return $this->httpPostJson('shop/spu/del_audit', $params);
    }

    /**
     * 获取商品列表
     * @param $params
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSpuList($params){
        return $this->httpPostJson('shop/spu/get_list', $params);
    }


    /**
     * 更新商品
     * @param $params
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateSpu($params){
        return $this->httpPostJson('shop/spu/update', $params);
    }

    /**
     * 上架商品
     * @param $product_id
     * @param $out_product_id
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listingSpu($product_id, $out_product_id){
        $params = array(
            'product_id' => $product_id,
            'out_product_id' => $out_product_id
        );
        return $this->httpPostJson('shop/spu/listing', $params);
    }

    /**
     * 下架商品
     * @param $product_id
     * @param $out_product_id
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delistingSpu($product_id, $out_product_id){
        $params = array(
            'product_id' => $product_id,
            'out_product_id' => $out_product_id
        );
        return $this->httpPostJson('shop/spu/delisting', $params);
    }

    /**************************************************************** 订单接口 *********************************************************/

    /**
     * 检查场景值是否在支付校验范围内
     * @param $scene
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkScene($scene){
        $params = array(
            'scene' => $scene
        );
        return $this->httpPostJson('shop/scene/check', $params);
    }

    /**
     * 生成订单
     * @param $params
     */
    public function addOrder($params){

        return $this->httpPostJson('shop/order/add', $params);
    }

    /**
     * 同步订单支付结果
     * @param $params
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function payOrder($params){
        return $this->httpPostJson('shop/order/pay', $params);
    }

    /**
     * 获取订单详情
     * @param $order_id
     * @param $out_order_id
     * @param $openid
     */
    public function getOrder($order_id, $out_order_id, $openid){
        $params = array(
            'order_id' => $order_id,
            'out_order_id' => $out_order_id,
            'openid' => $openid,
        );
        return $this->httpPostJson('shop/order/get', $params);
    }


    /**************************************************************** 物流接口 *********************************************************/

    /**
     * 获取快递公司列表
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDeliveryCompanyList(){
        $params = [];
        return $this->httpPostJson('shop/delivery/get_company_list', $params);
    }

    /**
     * 订单发货
     * @param $params
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendDelivery($order_id, $out_order_id, $openid, $finish_all_delivery = 0, $delivery_list){
        $params = array(
            'order_id' => $order_id,
            'out_order_id' => $out_order_id,
            'openid' => $openid,
            'finish_all_delivery' => $finish_all_delivery,
            'delivery_list' => $delivery_list,
            'ship_done_time' => date("Y-m-d H:i:s", time())
        );
        return $this->httpPostJson('shop/delivery/send', $params);
    }

    /**
     * 订单收货
     * @param $params
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function recieveDelivery($order_id, $out_order_id, $openid){
        $params = array(
            'order_id' => $order_id,
            'out_order_id' => $out_order_id,
            'openid' => $openid,
        );
        return $this->httpPostJson('shop/delivery/recieve', $params);
    }
    /**************************************************************** 售后接口 *********************************************************/

    /**
     * 创建售后
     * @param $params
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addAftersale($params){
        return $this->httpPostJson('shop/ecaftersale/add', $params);
    }

    /**
     * 获取售后
     * @param $order_id
     * @param $out_order_id
     * @param $openid
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAftersale($params){
        return $this->httpPostJson('shop/ecaftersale/get', $params);
    }

    /**
     * 更新售后
     * @param $order_id
     * @param $out_order_id
     * @param $openid
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateAftersale($params){
        return $this->httpPostJson('shop/ecaftersale/update', $params);
    }






    /**
     * @return \Psr\Http\Message\ResponseInterface|\EasyWeChat\Kernel\Support\Collection|array|object|string
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function createActivityId()
    {
        return $this->httpGet('cgi-bin/message/wxopen/activityid/create');
    }

    /**
     * @param string $activityId
     * @param int    $state
     * @param array  $params
     *
     * @return \Psr\Http\Message\ResponseInterface|\EasyWeChat\Kernel\Support\Collection|array|object|string
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateMessage(string $activityId, int $state = 0, array $params = [])
    {
        if (!in_array($state, [0, 1], true)) {
            throw new InvalidArgumentException('"state" should be "0" or "1".');
        }

        $params = $this->formatParameters($params);

        $params = [
            'activity_id' => $activityId,
            'target_state' => $state,
            'template_info' => ['parameter_list' => $params],
        ];

        return $this->httpPostJson('cgi-bin/message/wxopen/updatablemsg/send', $params);
    }

    /**
     * @param array $params
     *
     * @return array
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    protected function formatParameters(array $params)
    {
        $formatted = [];

        foreach ($params as $name => $value) {
            if (!in_array($name, ['member_count', 'room_limit', 'path', 'version_type'], true)) {
                continue;
            }

            if ('version_type' === $name && !in_array($value, ['develop', 'trial', 'release'], true)) {
                throw new InvalidArgumentException('Invalid value of attribute "version_type".');
            }

            $formatted[] = [
                'name' => $name,
                'value' => strval($value),
            ];
        }

        return $formatted;
    }

    /**
     * 获取图片
     * @param $audit_id
     */
    public function uploadImg($url){
        $params = [
            ['name' => 'img_url', 'contents' => $url],
            ['name' => 'resp_type', 'contents' => 1],
            ['name' => 'upload_type', 'contents' => 1],
        ];
        return $this->request('shop/img/upload', 'POST', ['multipart' => $params]);
    }

    public function updateShop($params){

        return $this->httpPostJson('shop/account/update_info', $params);
    }

    /**
     * 视频号订单同步订单结果
     */
    public function updateOrderType($params){

        return $this->httpPostJson('shop/order/pay', $params);
    }

    //获取支付参数
    public function getPaymentParams($params){
        return $this->httpPostJson('shop/order/getpaymentparams', $params);
    }

    //同意退款
    public function orderRefund($params){
        return $this->httpPostJson('shop/ecaftersale/acceptrefund', $params);
    }

    //拒绝退款
    public function orderNoRefund($params){
        return $this->httpPostJson('shop/ecaftersale/reject', $params);
    }

    //同意退货
    public function aceptreturn($params){
        return $this->httpPostJson('shop/ecaftersale/acceptreturn', $params);
    }

    //取消售后
    public function cancel($params){
        return $this->httpPostJson('shop/ecaftersale/cancel', $params);
    }

    //买家发货
    public function uploadreturninfo($params){
        return $this->httpPostJson('shop/ecaftersale/uploadreturninfo', $params);
    }

    public function getOrderList($params){
        return $this->httpPostJson('shop/ecaftersale/get_list', ['offset' => 0, 'limit' => 50]);
    }
}
