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
use app\model\system\Config as ConfigModel;
use app\model\system\Cron;
use app\model\upload\Upload;

/**
 * 优惠券活动
 */
class CouponType extends BaseModel
{
    //优惠券类型状态
    private $coupon_type_status = [
        1 => '进行中',
        2 => '已结束',
        -1 => '已关闭',
    ];

    public function getCouponTypeStatus()
    {
        return $this->coupon_type_status;
    }

    /**
     * 添加优惠券活动
     * @param $data
     * @return array
     */
    public function addCouponType($data)
    {
        //只要创建了就是进行中
        $data[ 'status' ] = 1;
        $data[ 'create_time' ] = time();
        //获取商品id
        if ($data[ 'goods_type' ] == 1) {//全部商品参与
            $data[ 'goods_ids' ] = '';
        }

        $data[ 'goods_ids' ] = ',' . $data[ 'goods_ids' ] . ',';
        $res = model("promotion_coupon_type")->add($data);
        if ($data[ 'validity_type' ] == 0) {
            $cron = new Cron();
            $cron->addCron(1, 1, '优惠券活动定时结束', 'CronCouponTypeEnd', $data[ 'end_time' ], $res);
        }

        $this->qrcode($res, 'all', $data[ 'site_id' ]);
        return $this->success($res);
    }

    /**
     * 编辑优惠券活动
     * @param $data
     * @param $coupon_type_id
     * @return array
     */
    public function editCouponType($data, $coupon_type_id)
    {
        $data[ 'update_time' ] = time();

        //获取商品id
        if ($data[ 'goods_type' ] == 1) {//全部商品参与
            $data[ 'goods_ids' ] = '';
        }

        $coupon_info = model("promotion_coupon_type")->getInfo([ [ 'coupon_type_id', '=', $coupon_type_id ] ]);
        if (!empty($coupon_info[ 'image' ]) && !empty($data[ 'image' ]) && $coupon_info[ 'image' ] != $data[ 'image' ]) {
            $upload_model = new Upload();
            $upload_model->deletePic($coupon_info[ 'image' ], $coupon_info[ 'site_id' ]);
        }

        $data[ 'goods_ids' ] = ',' . $data[ 'goods_ids' ] . ',';
        $res = model("promotion_coupon_type")->update($data, [ [ 'coupon_type_id', '=', $coupon_type_id ] ]);
        model('promotion_coupon')->update([ 'goods_ids' => $data[ 'goods_ids' ], 'goods_type' => $data[ 'goods_type' ] ], [ [ 'coupon_type_id', '=', $coupon_type_id ], [ 'state', '=', 1 ] ]);
        $cron = new Cron();
        $cron->deleteCron([ [ 'event', '=', 'CronCouponTypeEnd' ], [ 'relate_id', '=', $coupon_type_id ] ]);
        if ($data[ 'validity_type' ] == 0) {
            $cron->addCron(1, 1, '优惠券活动定时结束', 'CronCouponTypeEnd', $data[ 'end_time' ], $coupon_type_id);
        }
        return $this->success($res);
    }

    /**
     * 关闭优惠券
     * @param $coupon_type_id
     * @param $site_id
     * @return array
     */
    public function closeCouponType($coupon_type_id, $site_id)
    {
        $res = model('promotion_coupon_type')->update([ 'status' => -1 ], [ [ 'coupon_type_id', '=', $coupon_type_id ], [ 'site_id', '=', $site_id ] ]);
//        if ($res) {
//            model("promotion_coupon")->update(['state' => 3], [['coupon_type_id', '=', $coupon_type_id], ['site_id', '=', $site_id]]);
//        }
        $cron = new Cron();
        $cron->deleteCron([ [ 'event', '=', 'CronCouponTypeEnd' ], [ 'relate_id', '=', $coupon_type_id ] ]);
        return $this->success($res);
    }

    /**
     * 删除优惠券活动
     * @param $coupon_type_id
     * @param $site_id
     * @return array
     */
    public function deleteCouponType($coupon_type_id, $site_id)
    {
        $coupon_info = model("promotion_coupon_type")->getInfo([ [ 'coupon_type_id', '=', $coupon_type_id ] ]);
        if (!empty($coupon_info[ 'image' ])) {
            $upload_model = new Upload();
            $upload_model->deletePic($coupon_info[ 'image' ], $coupon_info[ 'site_id' ]);
        }

        $res = model("promotion_coupon_type")->delete([ [ 'coupon_type_id', '=', $coupon_type_id ], [ 'site_id', '=', $site_id ] ]);
        if ($res) {
            model("promotion_coupon")->delete([ [ 'coupon_type_id', '=', $coupon_type_id ] ]);
        }
        $cron = new Cron();
        $cron->deleteCron([ [ 'event', '=', 'CronCouponTypeEnd' ], [ 'relate_id', '=', $coupon_type_id ] ]);

        return $this->success($res);
    }

    /**
     * 获取优惠券活动详情
     * @param $coupon_type_id
     * @param $site_id
     * @return array
     */
    public function getCouponTypeInfo($coupon_type_id, $site_id)
    {
        $res = model('promotion_coupon_type')->getList([ [ 'coupon_type_id', 'in', $coupon_type_id ], [ 'site_id', '=', $site_id ] ]);
        if (!empty($res)) {
            foreach ($res as $k => $v) {
                if ($v[ 'goods_type' ] == 2) {
                    $field[ $k ] = 'goods_id,goods_name,goods_stock,goods_image,price,sort';
                    $goods_ids[ $k ] = substr($v[ 'goods_ids' ], '1', '-1');
                    $goods_list[ $k ] = model('goods')->getList([ [ 'goods_id', 'in', $goods_ids[ $k ] ] ], $field[ $k ]);
                }
                $res[ $k ][ 'goods_list' ] = isset($goods_list[ $k ]) ? $goods_list[ $k ] : [];
                $res[ $k ][ 'goods_list_count' ] = count($res[ $k ][ 'goods_list' ]);
            }
        }
        return $this->success($res);
    }

    /**
     * 获取优惠券活动信息
     * @param array $where
     * @param bool $field
     * @param string $alias
     * @param null $join
     * @param null $data
     * @return array
     */
    public function getInfo($where = [], $field = true, $alias = 'a', $join = null, $data = null)
    {
        $res = model('promotion_coupon_type')->getInfo($where, $field, $alias, $join, $data);
        return $this->success($res);
    }

    /**
     * 获取优惠券类型列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getCouponTypeList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $res = model('promotion_coupon_type')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($res);
    }

    /**
     * 获取优惠券活动分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getCouponTypePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $condition[] = [ 'promotion_type', '=', 0 ];
        $list = model('promotion_coupon_type')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 排序
     * @param $coupon_type_id
     * @param $sort
     * @return array
     */
    public function couponSort($coupon_type_id, $sort)
    {
        $res = model('promotion_coupon_type')->update([ 'sort' => $sort ], [ [ 'coupon_type_id', '=', $coupon_type_id ] ]);
        return $this->success($res);
    }

    /**
     * 生成优惠券二维码
     * @param $coupon_type_id
     * @param string $app_type all为全部
     * @param string $type 类型 create创建 get获取
     * @return mixed|array
     */
    public function qrcode($coupon_type_id, $app_type, $site_id, $type = 'create')
    {
        $res = event('Qrcode', [
            'site_id' => $site_id,
            'app_type' => $app_type,
            'type' => $type,
            'data' => [
                'coupon_type_id' => $coupon_type_id
            ],
            'page' => '/pages_tool/goods/coupon_receive',
            'qrcode_path' => 'upload/qrcode/coupon',
            'qrcode_name' => 'coupon_type_code_' . $coupon_type_id . '_' . $site_id,
        ], true);
        return $res;
    }

    /**
     * 优惠券定时结束
     * @param $coupon_type_id
     * @return array
     */
    public function couponCronEnd($coupon_type_id)
    {
        $res = model('promotion_coupon_type')->update([ 'status' => 2 ], [ [ 'coupon_type_id', '=', $coupon_type_id ] ]);
        return $this->success($res);
    }

    public function spread($coupon_type_id, $name, $site_id, $type = 'create')
    {
        $data = [
            'site_id' => $site_id,
            'app_type' => "all", // all为全部
            'type' => $type, // 类型 create创建 get获取
            'data' => [
                "coupon_type_id" => $coupon_type_id
            ],
            'page' => '/pages_tool/goods/coupon_receive',
            'qrcode_path' => 'upload/qrcode/coupon',
            'qrcode_name' => 'coupon_type_code_' . $coupon_type_id . '_' . $site_id,
        ];
        event('Qrcode', $data, true);
        $app_type_list = config('app_type');
        $path = [];
        foreach ($app_type_list as $k => $v) {
            switch ( $k ) {
                case 'h5':
                    $wap_domain = getH5Domain();
                    $path[ $k ][ 'status' ] = 1;
                    $path[ $k ][ 'url' ] = $wap_domain . $data[ 'page' ] . '?coupon_type_id=' . $coupon_type_id;
                    $path[ $k ][ 'img' ] = "upload/qrcode/coupon/coupon_type_code_" . $coupon_type_id . "_" . $site_id . "_" . $k . ".png";
                    break;
                case 'weapp' :
                    $config = new ConfigModel();
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信小程序';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }
                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信小程序';
                    }
                    break;

                case 'wechat' :
                    $config = new ConfigModel();
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信公众号';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }
                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信公众号';
                    }
                    break;
            }

        }

        $return = [
            'path' => $path,
            'name' => $name,
        ];

        return $this->success($return);
    }

    public function urlQrcode($page, $qrcode_param, $promotion_type, $site_id)
    {
        $params = [
            'site_id' => $site_id,
            'data' => $qrcode_param,
            'page' => $page,
            'promotion_type' => $promotion_type,
            'h5_path' => $page . '?coupon_type_id=' . $qrcode_param[ 'coupon_type_id' ],
            'qrcode_path' => 'upload/qrcode/coupon',
            'qrcode_name' => [
                'h5_name' => 'coupon_type_code_' . $promotion_type . '_h5_' . $qrcode_param[ 'coupon_type_id' ] . '_' . $site_id,
                'weapp_name' => 'coupon_type_code_' . $promotion_type . '_weapp_' . $qrcode_param[ 'coupon_type_id' ] . '_' . $site_id
            ]
        ];

        $solitaire = event('ExtensionInformation', $params);
        return $this->success($solitaire[ 0 ]);
    }
}