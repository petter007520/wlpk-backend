<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\upload;

use think\facade\Cache;
use app\model\BaseModel;

/**
 * 相册组件模型
 */
class Album extends BaseModel
{

    private $type = array (
        'img' => '图片',
        'video' => '视频'
    );

    public function getType()
    {
        return $this->type;
    }

    /*******************************************************************相册编辑查询 start*****************************************************/

    /**
     * 创建相册
     * @param $data
     */
    public function addAlbum($data)
    {
        $site_id = isset($data[ 'site_id' ]) ? $data[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        $count = model("album")->getCount([ ['album_name', '=', $data['album_name'] ],['site_id', '=', $data['site_id'] ],['type', '=', $data['type'] ] ]);
        if ($count) return $this->error('', '已存在相同名称的分组');

        $data[ "update_time" ] = time();

        Cache::tag("album_" . $site_id)->clear();
        $res = model("album")->add($data);

        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }

//        //上级的图片放到新相册
//        if($data['level'] && $data['level'] > 1 ){
//            model("album_pic")->update(['album_id' => $res], [ ['album_id', '=', $data['pid']] ]);
//            $temp_count       = model('album')->getInfo([ ['album_id', '=', $data['pid']] ]);
//            $save_data = ['num' => $temp_count['num']];
//            if($temp_count['is_default']){
//                $save_data['is_default'] = 1;
//                model('album')->update(['is_default' => 0] , [ ['album_id', '=', $data['pid']] ]);
//            }
//            model('album')->update(['num' => 0], [ ['album_id', '=', $data['pid']] ]);
//            model('album')->update($save_data , [ ['album_id', '=', $res] ]);
//        }

        return $this->success($res);
    }

    /**
     * 编辑相册
     * @param $data
     * @param $condition
     */
    public function editAlbum($data, $condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $album_info = model("album")->getInfo($condition);
        if (empty($album_info)) return $this->error('', '未获取到分组信息');

        $count = model("album")->getCount([ ['album_id', '<>', $album_info['album_id'] ], ['album_name', '=', $data['album_name'] ],['site_id', '=', $site_id ],['type', '=', $album_info['type'] ] ]);
        if ($count) return $this->error('', '已存在相同名称的分组');

        $data[ "update_time" ] = time();
        Cache::tag("album_" . $site_id)->clear();
        $res = model("album")->update($data, $condition);
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 删除相册
     * @param $condition
     * @return array
     */
    public function deleteAlbum($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '')
            return $this->error('', 'REQUEST_SITE_ID');

        //判断当前相册是否存在默认, 默认相册不可删除
//        $temp_count = model("album_pic")->getCount($condition, "*");
//        if ($temp_count > 0)
//            return $this->error("", "当前删除相册中存在图片,不可删除!");

        $info = model('album')->getInfo($condition);

        $temp_condition = $condition;
        $temp_condition[] = [ "is_default", "=", 1 ];
        $temp_condition[] = [ "type", "=", $info[ 'type' ] ];
        $temp_info = model('album')->getInfo($temp_condition);
        if ($temp_info && $temp_info[ 'level' ] == 1)
            return $this->error('', '当前删除相册中存在默认相册,默认相册不可删除!');

        $child_count = model('album')->getCount([ [ 'pid', '=', $info[ 'album_id' ] ] ]);
        if ($child_count > 0)
            return $this->error('', '当前相册中存在子相册,不可删除!');

        Cache::tag("album_" . $site_id)->clear();
        $res = model('album')->delete($condition);
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }

        if ($temp_info && $temp_info[ 'level' ] == 2) {
            //有同级，默认相册是同级，否则是上级
            $other_child = model('album')->getFirstData([ [ "pid", "=", $temp_info[ 'pid' ] ], [ "site_id", "=", $site_id ] ]);
            if ($other_child) {
                model("album")->update([ 'is_default' => 1 ], [ [ 'album_id', '=', $other_child[ 'album_id' ] ] ]);
            } else {
                model("album")->update([ 'is_default' => 1 ], [ [ 'album_id', '=', $temp_info[ 'pid' ] ] ]);
            }
        }

        //有上级、无同级:转移到上级；有上级、有同级:给到第一个同级；无上级:给到默认
        if ($info[ 'level' ] == 1) {
            $info_id = model("album")->getInfo([ [ "is_default", "=", 1 ], [ "site_id", "=", $site_id ], [ 'type', '=', $info[ 'type' ] ] ], "album_id");
            model("album_pic")->update([ 'album_id' => $info_id[ 'album_id' ] ], $condition);

            $count = model("album_pic")->getCount([ 'album_id' => $info_id[ 'album_id' ] ]);
            model("album")->update([ 'num' => $count ], [ [ 'album_id', '=', $info_id[ 'album_id' ] ] ]);

        } else {
            $parent_album = model("album")->getInfo([ [ "album_id", "=", $info[ 'pid' ] ], [ "site_id", "=", $site_id ] ], "album_id");
            $other_child = model('album')->getFirstData([ [ "pid", "=", $info[ 'pid' ] ], [ "site_id", "=", $site_id ] ]);
            if ($other_child) {
                model("album_pic")->update([ 'album_id' => $other_child[ 'album_id' ] ], $condition);

                $count = model("album_pic")->getCount([ 'album_id' => $other_child[ 'album_id' ] ]);
                model("album")->update([ 'num' => $count ], [ [ 'album_id', '=', $other_child[ 'album_id' ] ] ]);
            } else {
                model("album_pic")->update([ 'album_id' => $parent_album[ 'album_id' ] ], $condition);
                $count = model("album_pic")->getCount([ 'album_id' => $parent_album[ 'album_id' ] ]);
                model("album")->update([ 'num' => $count ], [ [ 'album_id', '=', $parent_album[ 'album_id' ] ] ]);
            }
        }

        return $this->success($res);
    }

    /**
     * 设置默认相册
     * @param $status
     * @param $condition
     */
    public function modifyAlbumDefault($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $album_id = isset($check_condition[ 'album_id' ]) ? $check_condition[ 'album_id' ] : '';
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        if ($album_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        //先将所有本站点的相册都设为非默认(一个站点只能有一个默认相册)
        $temp_condition = array (
            [ "site_id", "=", $site_id ],
        );
        Cache::tag("album_" . $site_id)->clear();
        $res = model('user')->update([ "is_default" => 0, "update_time" => time() ], $temp_condition);
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }

        //将本相册设置为默认相册
        $data = array (
            "is_default" => 1,
            "update_time" => time()
        );
        $res = model('album')->update($data, $condition);
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 获取相册信息
     * @param $condition
     * @param string $field
     * @return \multitype
     */
    public function getAlbumInfo($condition, $field = "album_id, site_id, album_name, sort, cover, desc, is_default, update_time, num")
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $data = json_encode([ $condition, $field ]);
        $cache = Cache::get("ablum_getAlbumInfo_" . $site_id . '_' . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $info = model('album')->getInfo($condition, $field);
        Cache::tag("album_" . $site_id)->set("ablum_getAlbumInfo_" . $site_id . '_' . $data, $info);
        return $this->success($info);
    }

    /**
     * 获取相册列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     * @return multitype:string mixed
     */
    public function getAlbumList($condition = [], $field = "album_id, site_id, album_name, sort, cover, desc, is_default, update_time, num, level, type", $order = '', $limit = null)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        $data = json_encode([ $condition, $field, $order, $limit ]);
        $cache = Cache::get("ablum_getAlbumList_" . $site_id . '_' . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('album')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("album_" . $site_id)->set("ablum_getAlbumList_" . $site_id . '_' . '_' . $data, $list);
        return $this->success($list);
    }

    /**
     * 获取相册分组
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     * @return multitype:string mixed
     */
    public function getAlbumListTree($condition = [], $field = "album_id, site_id, album_name, sort, cover, desc, is_default, update_time, num, level, pid", $order = '', $limit = null)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        $data = json_encode([ $condition, $field, $order, $limit ]);
        $cache = Cache::get("ablum_getAlbumListTree_" . $site_id . '_' . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('album')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("album_" . $site_id)->set("ablum_getAlbumListTree_" . $site_id . '_' . '_' . $data, $list);

        $album_list = [];

        //遍历一级
        foreach ($list as $k => $v) {
            if ($v[ 'level' ] == 1) {
                $album_list[] = $v;
                unset($list[ $k ]);
            }
        }

        $list = array_values($list);

        //遍历二级
        foreach ($list as $k => $v) {
            foreach ($album_list as $ck => $cv) {
                if ($v[ 'level' ] == 2 && $cv[ 'album_id' ] == $v[ 'pid' ]) {
                    $album_list[ $ck ]['num'] += $v['num'];
                    $album_list[ $ck ][ 'child_list' ][] = $v;
                    if (isset($album_list[ $ck ]['child'])) array_push($album_list[ $ck ]['child'], $v['album_id']);
                    else $album_list[ $ck ]['child'] = [ $cv[ 'album_id' ], $v['album_id'] ];
                    unset($list[ $k ]);
                }
            }
        }

        return $this->success($album_list);
    }

    /**
     * 获取会员分页列表
     *
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     * @return multitype:string mixed
     */
    public function getAlbumPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = 'album_id, site_id, album_name, sort, cover, desc, is_default, update_time, num')
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $data = json_encode([ $condition, $page, $page_size, $order, $field ]);
        $cache = Cache::get("album_getAlbumPageList_" . $site_id . '_' . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('album')->pageList($condition, $field, $order, $page, $page_size);
        Cache::tag("album_" . $site_id)->set("album_getAlbumPageList_" . $site_id . '_' . '_' . $data, $list);
        return $this->success($list);
    }

    /**
     * 同步修改相册下的图片数量
     * @param unknown $condition
     */
    public function syncAlbumNum($album_id)
    {
        $count = model("album_pic")->getCount([ [ "album_id", "=", $album_id ] ], "*");//获取本商品分组下的图片数量
        $data = array (
            "num" => $count
        );
        $res = model("album")->update($data, [ [ "album_id", "=", $album_id ] ]);
        return $this->success($res);
    }


    /**
     * 同步所有相册下的图片数量
     * @param unknown $condition
     */
    public function refreshAlbumNum($site_id)
    {
        $album_list = model('album')->getList([ ['site_id', '=', $site_id] ], 'album_id');
        foreach ($album_list as $k => $v){
            $this->syncAlbumNum($v['album_id']);
        }
        return $this->success();
    }



    /*******************************************************************相册编辑查询 end*****************************************************/

    /*******************************************************************相册图片编辑查询 start*****************************************************/

    /**
     * 添加相册图片
     * @param $data
     */
    public function addAlbumPic($data)
    {
        $site_id = isset($data[ 'site_id' ]) ? $data[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $data[ "update_time" ] = time();
        Cache::tag("album_pic_" . $site_id)->clear();
        $res = model("album_pic")->add($data);
        $this->syncAlbumNum($data[ "album_id" ]);//同步当前相册下的图片数量
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 编辑相册图片
     * @param $data
     * @param $condition
     */
    public function editAlbumPic($data, $condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        $data[ "update_time" ] = time();
        Cache::tag("album_pic_" . $site_id)->clear();
        $res = model("album_pic")->update($data, $condition);
        $this->syncAlbumNum($check_condition[ "album_id" ]);//同步当前相册下的图片数量
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 删除相册图片
     * @param array $condition
     * @return multitype:string mixed
     */
    public function deleteAlbumPic($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '')
            return $this->error('', 'REQUEST_SITE_ID');


        Cache::tag("album_pic_" . $site_id)->clear();

        $album_pic_list = model('album_pic')->getList($condition);
        if (!empty($album_pic_list)) {
            foreach ($album_pic_list as $key => $val) {
                $upload_model = new Upload();
                $upload_model->deletePic($val[ 'pic_path' ], $val[ 'site_id' ]);
            }
        }

        $res = model('album_pic')->delete($condition);
        $this->syncAlbumNum($check_condition[ "album_id" ]);//同步当前相册下的图片数量
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 编辑图片所在分组
     * @param $album_id
     * @param $condition
     */
    public function modifyAlbumPicAlbum($album_id, $condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '')
            return $this->error('', 'REQUEST_SITE_ID');

        $info = model("album_pic")->getInfo($condition);
        $original_album_id = $info[ "album_id" ];
        if ($original_album_id == $album_id) {
            return $this->success();
        }
        Cache::tag("album_pic_" . $site_id)->clear();
        Cache::tag("album_" . $site_id)->clear();
        $res = model("album_pic")->update([ "album_id" => $album_id ], $condition);//切换图片所在分组
        $this->syncAlbumNum($album_id);//同步当前相册下的图片数量
        $this->syncAlbumNum($original_album_id);//同步当前相册下的图片数量
        if ($res === false)
            return $this->error('', 'UNKNOW_ERROR');

        return $this->success($res);
    }

    /**
     * 获取相册图片信息
     * @param $condition
     * @param string $field
     * @return \multitype
     */
    public function getAlbumPicInfo($condition, $field = "pic_id, pic_name, pic_path, pic_spec, site_id, update_time")
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $data = json_encode([ $condition, $field ]);
        $cache = Cache::get("album_pic_getAlbumPicInfo_" . $site_id . '_' . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $info = model('album_pic')->getInfo($condition, $field);
        Cache::tag("album_pic_" . $site_id)->set("album_pic_getAlbumPicInfo_" . $site_id . '_' . $data, $info);
        return $this->success($info);
    }

    /**
     * 获取相册图片列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     * @return multitype:string mixed
     */
    public function getAlbumPicList($condition = [], $field = "pic_id, pic_name, pic_path, pic_spec, site_id, update_time", $order = '', $limit = null)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        $data = json_encode([ $condition, $field, $order, $limit ]);
        $cache = Cache::get("album_pic_getAlbumPicList_" . $site_id . '_' . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('album_pic')->getList($condition, $field, $order, '', '', '', $limit);
        Cache::tag("album_pic_" . $site_id)->set("album_pic_getAlbumPicList_" . $site_id . '_' . '_' . $data, $list);
        return $this->success($list);
    }

    /**
     * 获取相册图片分页列表
     *
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     * @return multitype:string mixed
     */
    public function getAlbumPicPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = 'pic_id, pic_name, pic_path, pic_spec, update_time,is_thumb')
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = isset($check_condition[ 'site_id' ]) ? $check_condition[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $data = json_encode([ $condition, $page, $page_size, $order, $field ]);
        $cache = Cache::get("album_pic_getAlbumPicPageList_" . $site_id . '_' . $data);
        if (!empty($cache)) {
            return $this->success($cache);
        }
        $list = model('album_pic')->pageList($condition, $field, $order, $page, $page_size);
        Cache::tag("album_pic_" . $site_id)->set("album_pic_getAlbumPicPageList_" . $site_id . '_' . '_' . $data, $list);
        return $this->success($list);
    }
    /*******************************************************************相册图片 end*****************************************************/

    /**
     * 生成缩略图，是否生成缩略图
     * @return boolean
     */
    public function createThumbBatch($site_id, $pic_ids)
    {
        $condition = [
            [ 'pic_id', 'in', $pic_ids ],
            [ 'is_thumb', '=', 0 ]
        ];
        $list = model("album_pic")->getList($condition);
        $upload = new Upload($site_id);
        foreach ($list as $key => $val) {
            if ($val[ 'is_thumb' ] == 0) {
                //分两种情况
                //1、云存储 'http://aaa.com/upload/a.jpg' 这里需要把域名去掉，upload左边也不能有/，否则会变为绝对路径
                //2、本地存储'upload/a.jpg'
                $parse_res = parse_url($val[ 'pic_path' ]);
                $pic_path = ltrim($parse_res[ 'path' ], '/');
                $file_name = substr($pic_path, 0, strrpos($pic_path, '.'));
                $extend_name = substr($pic_path, strrpos($pic_path, '.') + 1);
                $thumb_type = [
                    0 => "BIG",
                    1 => "MID",
                    2 => "SMALL"
                ];
                $upload->thumbBatch($val[ 'pic_path' ], $file_name, $extend_name, $thumb_type);//生成缩略图
            }
        }
        $res = model("album_pic")->update([ 'is_thumb' => 1 ], $condition);
        return $this->success($res);
    }

}