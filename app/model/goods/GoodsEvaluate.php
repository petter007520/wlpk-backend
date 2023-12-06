<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\goods;

use app\model\order\Config as ConfigModel;
use app\model\order\OrderCommon;
use think\facade\Db;
use think\facade\Cache;
use app\model\BaseModel;

/**
 * 商品评价
 */
class GoodsEvaluate extends BaseModel
{
    private $evaluate_status = [
        0 => '未评价',
        1 => '已评价',
        2 => '已追评'
    ];

    /**
     * 添加评价
     * @param array $data
     */
    public function addEvaluate($data, $site_id)
    {
        model('goods')->startTrans();
        try {
            $res = model('goods_evaluate')->getInfo([ [ 'order_id', '=', $data[ 'order_id' ] ] ], 'evaluate_id');
            if (empty($res)) {

                $config_model = new ConfigModel();
                //订单评价设置
                $order_evaluate_config = $config_model->getOrderEvaluateConfig($site_id);
                $order_evaluate_config = $order_evaluate_config[ 'data' ][ 'value' ];

                $data_arr = [];
                foreach ($data[ 'goods_evaluate' ] as $k => $v) {
                    if (empty($v[ 'content' ])) {
                        model('goods')->rollback();
                        return $this->error('', '商品的评价不能为空!');
                    }
                    $item = [
                        'order_id' => $data[ 'order_id' ],
                        'order_no' => $data[ 'order_no' ],
                        'member_id' => $data[ 'member_id' ],
                        'member_name' => $data[ 'member_name' ],
                        'member_headimg' => $data[ 'member_headimg' ],
                        'is_anonymous' => $data[ 'is_anonymous' ],
                        'order_goods_id' => $v[ 'order_goods_id' ],
                        'goods_id' => $v[ 'goods_id' ],
                        'sku_id' => $v[ 'sku_id' ],
                        'site_id' => $site_id,
                        'sku_name' => $v[ 'sku_name' ],
                        'sku_price' => $v[ 'sku_price' ],
                        'sku_image' => $v[ 'sku_image' ],
                        'content' => !empty($v[ 'content' ]) ? $v[ 'content' ] : '此用户没有填写评价。',
                        'images' => $v[ 'images' ],
                        'scores' => $v[ 'scores' ],
                        'explain_type' => $v[ 'explain_type' ],
                        'is_audit' => ( $order_evaluate_config[ 'evaluate_audit' ] == 1 ? 0 : 1 ),
                        'create_time' => time()
                    ];
                    $data_arr[] = $item;

                    $evaluate = 0; //评价
                    $evaluate_shaitu = 0; //晒图
                    $evaluate_shipin = 0; //视频
                    $evaluate_haoping = 0; //好评
                    $evaluate_zhongping = 0; //中评
                    $evaluate_chaping = 0; //差评
                    if ($v[ 'explain_type' ] == 1) {
                        //好评
                        $evaluate = 1; //评价
                        $evaluate_haoping = 1; //好评

                    } elseif ($v[ 'explain_type' ] == 2) {
                        //中评
                        $evaluate = 1; //评价
                        $evaluate_zhongping = 1; //中评

                    } elseif ($v[ 'explain_type' ] == 3) {
                        //差评
                        $evaluate = 1; //评价
                        $evaluate_chaping = 1; //差评
                    }
                    if (!empty($v[ 'images' ])) {
                        $evaluate_shaitu = 1; //晒图
                    }

                    if ($order_evaluate_config[ 'evaluate_audit' ] == 0) {
                        Db::name('goods')->where([ [ 'goods_id', '=', $v[ 'goods_id' ] ] ])
                            ->update(
                                [
                                    "evaluate" => Db::raw('evaluate+' . $evaluate),
                                    "evaluate_shaitu" => Db::raw('evaluate_shaitu+' . $evaluate_shaitu),
                                    "evaluate_haoping" => Db::raw('evaluate_haoping+' . $evaluate_haoping),
                                    "evaluate_zhongping" => Db::raw('evaluate_zhongping+' . $evaluate_zhongping),
                                    "evaluate_chaping" => Db::raw('evaluate_chaping+' . $evaluate_chaping),
                                ]);
                        Db::name('goods_sku')->where([ [ 'sku_id', '=', $v[ 'sku_id' ] ] ])
                            ->update(
                                [
                                    "evaluate" => Db::raw('evaluate+' . $evaluate),
                                    "evaluate_shaitu" => Db::raw('evaluate_shaitu+' . $evaluate_shaitu),
                                    "evaluate_haoping" => Db::raw('evaluate_haoping+' . $evaluate_haoping),
                                    "evaluate_zhongping" => Db::raw('evaluate_zhongping+' . $evaluate_zhongping),
                                    "evaluate_chaping" => Db::raw('evaluate_chaping+' . $evaluate_chaping),
                                ]);
                    }
                }
                //记录订单日志 start
                $order_common_model = new OrderCommon();
                $member_info = model('member')->getInfo([ 'member_id' => $data[ 'member_id' ] ], 'nickname');
                $order_info = model('order')->getInfo([ 'order_id' => $data[ 'order_id' ] ], 'order_status,order_status_name');
                $buyer_name = empty($member_info[ 'nickname' ]) ? '' : '【' . $member_info[ 'nickname' ] . '】';
                $log_data = [
                    'order_id' => $data[ 'order_id' ],
                    'action' => '买家'.$buyer_name.'评价了订单',
                    'uid' => $data[ 'member_id' ],
                    'nick_name' => $member_info[ 'nickname' ],
                    'action_way' => 1,
                    'order_status' => $order_info[ 'order_status' ],
                    'order_status_name' => $order_info[ 'order_status_name' ]
                ];
                $order_common_model->addOrderLog($log_data);

                //记录订单日志 end

//			修改订单表中的评价标识
                model("order")->update([ 'is_evaluate' => 1, 'evaluate_status' => 1, 'evaluate_status_name' => $this->evaluate_status[ 1 ] ], [ [ 'order_id', '=', $data[ 'order_id' ] ] ]);
                $evaluate_id = model('goods_evaluate')->addList($data_arr);
                model('goods')->commit();
                Cache::tag("goods_evaluate")->clear();
                return $this->success($evaluate_id);
            } else {
                return $this->error();
            }
        } catch (\Exception $e) {
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 评价回复
     * @param unknown $data
     */
    public function evaluateApply($data)
    {
        $res = model("goods_evaluate")->update($data, [ [ 'evaluate_id', '=', $data[ 'evaluate_id' ] ] ]);
        Cache::tag("goods_evaluate")->clear();
        return $this->success($res);
    }

    /**
     * 修改评价
     * @param $data
     * @param array $condition
     * @return array
     */
    public function editEvaluate($data, $condition = [])
    {
        $res = model("goods_evaluate")->update($data, $condition);
        Cache::tag("goods_evaluate")->clear();
        return $this->success($res);
    }

    //操作评价状态
    public function modifyAuditEvaluate($data, $condition = [])
    {
        $list = model("goods_evaluate")->getList($condition, 'goods_id,sku_id,is_audit,explain_type,images');
        if (!empty($list)) {

            $goods_evaluate = 0; //评价
            $goods_evaluate_haoping = 0; //好评
            $goods_evaluate_zhongping = 0; //中评
            $goods_evaluate_chaping = 0; //差评
            $goods_evaluate_shaitu = 0; //晒图

            $sku_evaluate = 0; //评价
            $sku_evaluate_haoping = 0; //好评
            $sku_evaluate_zhongping = 0; //中评
            $sku_evaluate_chaping = 0; //差评
            $sku_evaluate_shaitu = 0; //晒图

            foreach ($list as $k => $v) {

                if ($data[ 'is_audit' ] == 1) {
                    $symbol = "+";
                    if ($v[ 'explain_type' ] == 1) {
                        //好评
                        $goods_evaluate = 1; //评价
                        $sku_evaluate = 1;
                        $goods_evaluate_haoping = 1; //好评
                        $sku_evaluate_haoping = 1;
                    } elseif ($v[ 'explain_type' ] == 2) {
                        //中评
                        $goods_evaluate = 1; //评价
                        $sku_evaluate = 1;
                        $goods_evaluate_zhongping = 1; //中评
                        $sku_evaluate_zhongping = 1;
                    } elseif ($v[ 'explain_type' ] == 3) {
                        //差评
                        $goods_evaluate = 1; //评价
                        $sku_evaluate = 1;
                        $goods_evaluate_chaping = 1; //差评
                        $sku_evaluate_chaping = 1;
                    }

                    if (!empty($v[ 'images' ])) {
                        $goods_evaluate_shaitu = 1; //晒图
                        $sku_evaluate_shaitu = 1;
                    }

                    Db::name('goods')->where([ [ 'goods_id', '=', $v[ 'goods_id' ] ] ])
                        ->update(
                            [
                                "evaluate" => Db::raw('evaluate' . $symbol . $goods_evaluate),
                                "evaluate_shaitu" => Db::raw('evaluate_shaitu' . $symbol . $goods_evaluate_shaitu),
                                "evaluate_haoping" => Db::raw('evaluate_haoping' . $symbol . $goods_evaluate_haoping),
                                "evaluate_zhongping" => Db::raw('evaluate_zhongping' . $symbol . $goods_evaluate_zhongping),
                                "evaluate_chaping" => Db::raw('evaluate_chaping' . $symbol . $goods_evaluate_chaping),
                                "wait_evaluate_num" => Db::raw('wait_evaluate_num-' . 1),
                                "success_evaluate_num" => Db::raw('success_evaluate_num+' . 1),
                            ]);
                    Db::name('goods_sku')->where([ [ 'sku_id', '=', $v[ 'sku_id' ] ] ])
                        ->update(
                            [
                                "evaluate" => Db::raw('evaluate' . $symbol . $sku_evaluate),
                                "evaluate_shaitu" => Db::raw('evaluate_shaitu' . $symbol . $sku_evaluate_shaitu),
                                "evaluate_haoping" => Db::raw('evaluate_haoping' . $symbol . $sku_evaluate_haoping),
                                "evaluate_zhongping" => Db::raw('evaluate_zhongping' . $symbol . $sku_evaluate_zhongping),
                                "evaluate_chaping" => Db::raw('evaluate_chaping' . $symbol . $sku_evaluate_chaping),
                                "wait_evaluate_num" => Db::raw('wait_evaluate_num-' . 1),
                                "success_evaluate_num" => Db::raw('success_evaluate_num+' . 1),
                            ]);
                } else {
                    Db::name('goods')->where([ [ 'goods_id', '=', $v[ 'goods_id' ] ] ])
                        ->update(
                            [
                                "wait_evaluate_num" => Db::raw('wait_evaluate_num-' . 1),
                                "fail_evaluate_num" => Db::raw('fail_evaluate_num+' . 1),
                            ]);
                    Db::name('goods_sku')->where([ [ 'sku_id', '=', $v[ 'sku_id' ] ] ])
                        ->update(
                            [
                                "wait_evaluate_num" => Db::raw('wait_evaluate_num-' . 1),
                                "fail_evaluate_num" => Db::raw('fail_evaluate_num+' . 1),
                            ]);
                }


            }

        }
        $res = model("goods_evaluate")->update($data, $condition);
        Cache::tag("goods_evaluate")->clear();
        return $this->success($res);
    }

    //操作追评状态
    public function modifyAgainAuditEvaluate($data, $condition = [])
    {

        $list = model("goods_evaluate")->getList($condition, 'goods_id,sku_id,is_audit,explain_type,images');

        $res = model("goods_evaluate")->update($data, $condition);

        foreach ($list as $k => $v) {
            if ($res) {
                model("goods")->setInc([ [ 'goods_id', '=', $v[ 'goods_id' ] ] ], 'evaluate_zhuiping', 1);
                model("goods_sku")->setInc([ [ 'sku_id', '=', $v[ 'sku_id' ] ] ], 'evaluate_zhuiping', 1);
            }
        }

        Cache::tag("goods_evaluate")->clear();
        return $this->success($res);
    }

    /**
     * 修改商品评价数量
     * @param $evaluate_ids
     * @return array
     */
    public function modifyGoodsEvaluateCount($evaluate_ids)
    {
        $list = model("goods_evaluate")->getList([ [ 'evaluate_id', 'in', $evaluate_ids ], [ 'is_audit', '<>', 0 ] ], 'goods_id,sku_id,is_audit');
        if (!empty($list)) {
            $evaluate = 1; //评价
            $evaluate_shaitu = 1; //晒图
            $evaluate_haoping = 1; //好评
            $evaluate_zhongping = 1; //中评
            $evaluate_chaping = 1; //差评
            foreach ($list as $k => $v) {

                if ($v[ 'is_audit' ] == 1) {
                    // 审核拒绝
                    $symbol = "+";

                    Db::name('goods')->where([ [ 'goods_id', '=', $v[ 'goods_id' ] ] ])
                        ->update(
                            [
                                "evaluate" => Db::raw('evaluate' . $symbol . $evaluate),
                                "evaluate_shaitu" => Db::raw('evaluate_shaitu' . $symbol . $evaluate_shaitu),
                                "evaluate_haoping" => Db::raw('evaluate_haoping' . $symbol . $evaluate_haoping),
                                "evaluate_zhongping" => Db::raw('evaluate_zhongping' . $symbol . $evaluate_zhongping),
                                "evaluate_chaping" => Db::raw('evaluate_chaping' . $symbol . $evaluate_chaping),
                            ]);
                    Db::name('goods_sku')->where([ [ 'sku_id', '=', $v[ 'sku_id' ] ] ])
                        ->update(
                            [
                                "evaluate" => Db::raw('evaluate' . $symbol . $evaluate),
                                "evaluate_shaitu" => Db::raw('evaluate_shaitu' . $symbol . $evaluate_shaitu),
                                "evaluate_haoping" => Db::raw('evaluate_haoping' . $symbol . $evaluate_haoping),
                                "evaluate_zhongping" => Db::raw('evaluate_zhongping' . $symbol . $evaluate_zhongping),
                                "evaluate_chaping" => Db::raw('evaluate_chaping' . $symbol . $evaluate_chaping),
                            ]);
                }


            }

        }

        return $this->success();
    }

    /**
     * 追评
     * @param unknown $data
     * @return multitype:string
     */
    public function evaluateAgain($data, $site_id)
    {
        $config_model = new ConfigModel();
        //订单评价设置
        $order_evaluate_config = $config_model->getOrderEvaluateConfig($site_id);
        $order_evaluate_config = $order_evaluate_config[ 'data' ][ 'value' ];

        foreach ($data[ 'goods_evaluate' ] as $k => $v) {
            $item = [
                'order_id' => $data[ 'order_id' ],
                'order_goods_id' => $v[ 'order_goods_id' ],
                'goods_id' => $v[ 'goods_id' ],
                'sku_id' => $v[ 'sku_id' ],
                'again_content' => $v[ 'again_content' ],
                'again_images' => $v[ 'again_images' ],
                'again_time' => time(),
                'again_is_audit' => ( $order_evaluate_config[ 'evaluate_audit' ] == 1 ? 0 : 1 ),
            ];
            $res = model("goods_evaluate")->update($item, [ [ 'order_goods_id', '=', $v[ 'order_goods_id' ] ] ]);

            if ($order_evaluate_config[ 'evaluate_audit' ] == 0) {
                if ($res) {
                    model("goods")->setInc([ [ 'goods_id', '=', $v[ 'goods_id' ] ] ], 'evaluate_zhuiping', 1);
                    model("goods_sku")->setInc([ [ 'sku_id', '=', $v[ 'sku_id' ] ] ], 'evaluate_zhuiping', 1);
                }
            }

        }
        model("order")->update([ 'is_evaluate' => 0, 'evaluate_status' => 2, 'evaluate_status_name' => $this->evaluate_status[ 2 ] ], [ [ 'order_id', '=', $data[ 'order_id' ] ] ]);
        Cache::tag("goods_evaluate")->clear();
        return $this->success($res);
    }

    /**
     * 删除评价
     * @param unknown $condition
     */
    public function deleteEvaluate($evaluate_ids)
    {
        $res = model('goods_evaluate')->delete([ [ 'evaluate_id', 'in', $evaluate_ids ] ]);
        Cache::tag("goods_evaluate")->clear();
        return $this->success($res);
    }

    /**
     * 获取评价信息
     * @param $condition
     * @param $field
     * @param $order
     * @return \multitype
     */
    public function getFirstEvaluateInfo($condition, $field = 'evaluate_id,order_goods_id,goods_id,sku_id,sku_name,sku_price,content,images,explain_first,member_name,member_headimg,member_id,is_anonymous,again_content,again_images,again_explain,create_time,again_time', $order = "create_time desc")
    {
        $data = json_encode([ $condition, $field ]);
        $cache = Cache::get("goods_evaluate_getFirstEvaluateInfo_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $info = model('goods_evaluate')->getFirstData($condition, $field, $order);
        Cache::tag("goods_evaluate")->set("goods_evaluate_getFirstEvaluateInfo_" . $data, $info);
        return $this->success($info);
    }

    /**
     * 获取评价信息
     * @param $condition
     * @param $field
     * @param $order
     * @return \multitype
     */
    public function getSecondEvaluateInfo($condition, $field = 'evaluate_id,order_goods_id,goods_id,sku_id,sku_name,sku_price,content,images,explain_first,member_name,member_headimg,member_id,is_anonymous,again_content,again_images,again_explain,create_time,again_time,scores', $order = "create_time desc")
    {
        $data = json_encode([ $condition, $field ]);
        $cache = Cache::get("goods_evaluate_getSecondEvaluateInfo_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $info = model('goods_evaluate')->getList($condition, $field, $order, '', '', '', 2);
        Cache::tag("goods_evaluate")->set("goods_evaluate_getSecondEvaluateInfo_" . $data, $info);
        return $this->success($info);
    }

    /**
     * 获取评价列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getEvaluateList($condition = [], $field = 'evaluate_id, order_id, order_no, order_goods_id, goods_id, sku_id, sku_name, sku_price, sku_image, content, images, explain_first, member_name, member_id, is_anonymous, scores, again_content, again_images, again_explain, explain_type, is_show, create_time, again_time,shop_desccredit,shop_servicecredit,shop_deliverycredit', $order = '', $limit = null)
    {
        $data = json_encode([ $condition, $field, $order, $limit ]);
        $cache = Cache::get("goods_evaluate_getEvaluateList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('goods_evaluate')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("goods_evaluate")->set("goods_evaluate_getEvaluateList_" . $data, $list);
        return $this->success($list);
    }

    /**
     * 获取评价分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getEvaluatePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'create_time desc', $field = 'evaluate_id, order_id, order_no, order_goods_id, goods_id, sku_id, sku_name, sku_price, sku_image, content, images, explain_first, member_name,member_headimg, member_id, is_anonymous, scores, again_content, again_images, again_explain, explain_type, is_show, create_time, again_time,shop_desccredit,shop_servicecredit,shop_deliverycredit,is_audit,again_is_audit')
    {
        $data = json_encode([ $condition, $field, $order, $page, $page_size ]);
        $cache = Cache::get("goods_evaluate_getEvaluatePageList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('goods_evaluate')->pageList($condition, $field, $order, $page, $page_size);
        Cache::tag("goods_evaluate")->set("goods_evaluate_getEvaluatePageList_" . $data, $list);
        return $this->success($list);
    }

    /**
     *  查询评论数量
     * @param $condition
     * @return array
     */
    public function getEvaluateCount($condition){
        $count = model('goods_evaluate')->getCount($condition);
        return $this->success($count);
    }

}