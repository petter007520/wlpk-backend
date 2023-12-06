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

namespace app\model\web;


use think\facade\Cache;
use app\model\BaseModel;
use app\model\upload\Upload;

/**
 * 广告管理
 * @author Administrator
 *
 */
class Adv extends BaseModel
{
    /**
     * 添加广告
     * @param array $data
     */
    public function addAdv($data)
    {
        $ap_id = model('adv')->add($data);
        Cache::tag("adv")->clear();
        return $this->success($ap_id);
    }

    /**
     * 修改广告
     * @param array $data
     */
    public function editAdv($data, $condition)
    {
        if (isset($data[ 'adv_image' ])) {
            $adv_info = model('adv')->getInfo($condition);
            if ($adv_info[ 'adv_image' ] && $data[ 'adv_image' ] && $adv_info[ 'adv_image' ] != $data[ 'adv_image' ]) {
                $upload_model = new Upload();
                $upload_model->deletePic($adv_info[ 'adv_image' ], $adv_info[ 'site_id' ]);
            }
        }

        $res = model('adv')->update($data, $condition);
        Cache::tag("adv")->clear();
        return $this->success($res);
    }

    /**
     * 删除广告
     * @param array $condition
     */
    public function deleteAdv($condition)
    {
        $list = model('adv')->getList($condition);
        if ($list) {
            foreach ($list as $k => $v) {
                if ($v[ 'adv_image' ]) {
                    $upload_model = new Upload();
                    $upload_model->deletePic($v[ 'adv_image' ], $v[ 'site_id' ]);
                }
            }
        }
        $res = model('adv')->delete($condition);
        Cache::tag("adv")->clear();
        return $this->success($res);
    }

    /**
     * 获取广告基础信息
     * @param int $ap_id
     * @return multitype:string mixed
     */
    public function getAdvInfo($ap_id)
    {
        $cache = Cache::get("adv_getAdvInfo_" . $ap_id);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $res = model('adv')->getInfo([ [ 'adv_id', '=', $ap_id ] ], 'adv_id, adv_title, ap_id, adv_url, adv_image, slide_sort, price, background,state');
        Cache::tag("adv")->set("adv_getAdvInfo_" . $ap_id, $res);
        return $this->success($res);
    }

    /**
     * 获取广告列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getAdvList($condition = [], $field = 'adv_id, adv_title, ap_id, adv_url, adv_image, slide_sort, price, background', $order = 'slide_sort desc,adv_id desc', $limit = null)
    {
        $data = json_encode([ $condition, $field, $order, $limit ]);
        $cache = Cache::get("adv_getAdvList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('adv')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("adv")->set("adv_getAdvList_" . $data, $list);

        return $this->success($list);
    }

    /**
     * 获取广告分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getAdvPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'a.adv_id desc', $field = 'a.adv_id, a.ap_id, a.adv_title, a.adv_url, a.adv_image, a.slide_sort, a.price, a.background, a.state, ap.ap_name')
    {
        $data = json_encode([ $condition, $field, $order, $page, $page_size ]);
        $cache = Cache::get("adv_getAdvPageList_" . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $join = [
            [
                'adv_position ap',
                'a.ap_id = ap.ap_id',
                'left'
            ]
        ];
        $list = model('adv')->pageList($condition, $field, $order, $page, $page_size, 'a', $join);
        Cache::tag("adv")->set("adv_getAdvPageList_" . $data, $list);
        return $this->success($list);
    }

}