<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;

use app\model\system\Site;
use app\model\upload\Album as AlbumModel;
use app\model\web\Config as ConfigModel;
use app\model\web\DiyView as DiyViewModel;

/**
 * 相册
 * @package app\shop\controller
 */
class Album extends BaseShop
{
    public function __construct()
    {
        $this->app_module = input('app_module', SHOP_MODULE);
        if ($this->app_module == 'store') {
            $this->initConstructInfo(); // 加载构造函数重要信息
        } else {
            parent::__construct();
        }
    }

    /**
     * 图像
     */
    public function lists()
    {
        header("Expires:-1");
        header("Cache-Control:no_cache");
        header("Pragma:no-cache");
        $type = input('type', 'img');
        $album_model = new AlbumModel();
        if (request()->isAjax()) {
            $page = input('page', 1);
            $limit = input('limit', PAGE_LIST_ROWS);
            $album_id = input('album_id', '');
            $pic_name = input("pic_name", "");
            $order = input("order", "update_time desc");
            $condition = array (
                [ 'site_id', "=", $this->site_id ],
                [ 'album_id', "in", $album_id ],
            );
            if (!empty($pic_name)) {
                $condition[] = [ 'pic_name', 'like', '%' . $pic_name . '%' ];
            }
            $list = $album_model->getAlbumPicPageList($condition, $page, $limit, $order);
            return $list;
        } else {
            $album_list = $album_model->getAlbumList([ [ 'site_id', "=", $this->site_id ], [ 'type', '=', $type ] ]);
            $album_list_tree = $album_model->getAlbumListTree([ [ 'site_id', "=", $this->site_id ], [ 'type', '=', $type ] ]);
            $this->assign("album_list", $album_list[ 'data' ]);
            $this->assign("album_list_tree", $album_list_tree[ 'data' ]);
            $this->assign('type_list', $album_model->getType());
            $this->assign('type', $type);
            return $this->fetch('album/lists');
        }
    }

    /**
     * 获取相册分组
     */
    function getAlbumList()
    {
        if (request()->isAjax()) {
            $album_model = new AlbumModel();
            $type = input('type', 'img');
            $album_list = $album_model->getAlbumListTree([ [ 'site_id', "=", $this->site_id ], [ 'type', '=', $type ] ]);
            return $album_list;
        }

    }

    /**
     * 添加分组
     */
    public function addAlbum()
    {
        if (request()->isAjax()) {
            $album_name = input('album_name', '');
            $pid = input('pid', '0');
            $type = input('type', '0');
            $data = array (
                'site_id' => $this->site_id,
                'album_name' => $album_name,
                'pid' => $pid,
                'type' => $type,
                'level' => empty($pid) ? 1 : 2
            );
            $album_model = new AlbumModel();
            $res = $album_model->addAlbum($data);
            return $res;
        }
    }

    /**
     * 修改分组
     */
    public function editAlbum()
    {
        if (request()->isAjax()) {
            $album_name = input('album_name');
            $album_id = input('album_id');
            $data = array (
                'album_name' => $album_name
            );
            $condition = array (
                [ 'site_id', "=", $this->site_id ],
                [ 'album_id', "=", $album_id ]
            );
            $album_model = new AlbumModel();
            $res = $album_model->editAlbum($data, $condition);
            return $res;
        }
    }

    /**
     * 删除分组
     */
    public function deleteAlbum()
    {
        if (request()->isAjax()) {
            $album_id = input('album_id');
            $album_model = new AlbumModel();
            $condition = array (
                [ "album_id", "=", $album_id ],
                [ "site_id", "=", $this->site_id ]
            );
            $res = $album_model->deleteAlbum($condition);
            return $res;
        }
    }

    /**
     * 分组详情
     */
    public function albumInfo()
    {
        if (request()->isAjax()) {
            $album_id = input('album_id');
            $album_model = new AlbumModel();
            $condition = array (
                [ "album_id", "=", $album_id ],
                [ "site_id", "=", $this->site_id ]
            );
            $res = $album_model->getAlbumInfo($condition);
            return $res;
        }
    }

    /**
     * 修改文件名
     */
    public function modifyPicName()
    {
        if (request()->isAjax()) {
            $pic_id = input('pic_id', 0);
            $pic_name = input('pic_name', '');
            $album_id = input('album_id', 0);

            $album_model = new AlbumModel();
            $condition = array (
                [ "pic_id", "=", $pic_id ],
                [ "site_id", "=", $this->site_id ],
                [ 'album_id', '=', $album_id ]
            );
            $data = array (
                "pic_name" => $pic_name
            );
            $res = $album_model->editAlbumPic($data, $condition);
            return $res;
        }
    }

    /**
     * 修改图片分组
     */
    public function modifyFileAlbum()
    {
        if (request()->isAjax()) {
            $pic_id = input('pic_id', 0);//图片id
            $album_id = input('album_id', 0);//相册id
            $album_model = new AlbumModel();
            $condition = array (
                [ "pic_id", "in", $pic_id ],
                [ "site_id", "=", $this->site_id ]
            );
            $res = $album_model->modifyAlbumPicAlbum($album_id, $condition);
            return $res;
        }
    }

    /**
     * 删除图片
     */
    public function deleteFile()
    {
        if (request()->isAjax()) {
            $pic_id = input('pic_id', 0);//图片id
            $album_id = input('album_id', 0);
            $album_model = new AlbumModel();
            $condition = array (
                [ "pic_id", "in", $pic_id ],
                [ "site_id", "=", $this->site_id ],
                [ 'album_id', '=', $album_id ]
            );
            $res = $album_model->deleteAlbumPic($condition);
            return $res;
        }
    }

    /**
     * 相册管理界面
     * @return mixed
     */
    public function album()
    {
        $album_model = new AlbumModel();
        $type = input('type', 'img');
        $display_type = input('display_type', 'img');
        $is_thumb = input('is_thumb', 0);
        if (request()->isAjax()) {
            $page_index = input('page', 1);
            $list_rows = input('limit', PAGE_LIST_ROWS);
            $album_id = input('album_id', '');
            $pic_name = input("pic_name", "");
            $condition = array (
                [ 'site_id', "=", $this->site_id ],
                [ 'album_id', "in", $album_id ],
            );
            if (!empty($pic_name)) {
                $condition[] = [ 'pic_name', 'like', '%' . $pic_name . '%' ];
            }
            $list = $album_model->getAlbumPicPageList($condition, $page_index, $list_rows, 'update_time desc');
            return $list;
        } else {
            $album_list = $album_model->getAlbumList([ [ 'site_id', "=", $this->site_id ] ]);
            $this->assign("album_list", $album_list[ 'data' ]);

            $album_tree_list = $album_model->getAlbumListTree([ [ 'site_id', "=", $this->site_id ], [ 'type', '=', $type ] ]);
            $this->assign("album_tree_list", $album_tree_list[ 'data' ]);

            $this->assign('type_list', $album_model->getType());
            $this->assign('type', $type);
            $this->assign('display_type', $display_type);
            $this->assign('is_thumb', $is_thumb);
            return $this->fetch('album/album');
        }
    }

    /**
     * 生成缩略图
     */
    public function createThumb()
    {
        ignore_user_abort(true);
        if (request()->isAjax()) {
            $upload_model = new AlbumModel();
            $pic_ids = input('pic_ids', '');
            $thumb_batch = $upload_model->createThumbBatch($this->site_id, $pic_ids);
            return $thumb_batch;
        }
    }

    /**
     * 刷新相册数量
     */
    public function refreshAlbumNum()
    {
        ignore_user_abort(true);
        $upload_model = new AlbumModel();
        $upload_model->refreshAlbumNum($this->site_id);
    }

}