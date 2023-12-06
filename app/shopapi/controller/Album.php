<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shopapi\controller;

use app\model\upload\Album as AlbumModel;

/**
 * 相册
 * Class Album
 * @package app\shopapi\controller
 */
class Album extends BaseApi
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
     * 获取相册分组
     * @return false|string
     */
    public function lists()
    {
        $album_model = new AlbumModel();
        $type = $this->params['type'] ?? 'img';
        $album_list = $album_model->getAlbumListTree([ [ 'site_id', "=", $this->site_id ], ['type', '=', $type] ]);
        return $this->response($album_list);
    }

    /**
     * 获取图片列表
     * @return false|string
     */
    public function picList()
    {
        $page = isset($this->params[ 'page' ]) ? $this->params[ 'page' ] : 1;
        $limit = isset($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $album_id = isset($this->params[ 'album_id' ]) ? $this->params[ 'album_id' ] : 0;

        $album_model = new AlbumModel();
        $condition = array (
            [ 'site_id', "=", $this->site_id ],
            [ 'album_id', "=", $album_id ],
        );
        if (!empty($pic_name)) {
            $condition[] = [ 'pic_name', 'like', '%' . $pic_name . '%' ];
        }
        $list = $album_model->getAlbumPicPageList($condition, $page, $limit, 'update_time desc','pic_path');
        return $this->response($list);
    }

    /**
     * 生成缩略图
     */
    public function createThumb()
    {
        ignore_user_abort(true);
        $upload_model = new AlbumModel();
        $pic_path = isset($this->params[ 'pic_path' ]) ? $this->params[ 'pic_path' ] : '';
        $pic_ids = $upload_model->getAlbumPicList([ ['pic_path', 'in', $pic_path], ['site_id', '=', $this->site_id] ], 'pic_id')['data'] ?? [];
        $pic_ids = array_column($pic_ids, 'pic_id');
        $thumb_batch = $upload_model->createThumbBatch($this->site_id, $pic_ids);
        return $this->response($thumb_batch);
    }
}