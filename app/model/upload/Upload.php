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

use app\model\image\ImageService;
use extend\Upload as UploadExtend;
use Intervention\Image\ImageManagerStatic as Image;
use app\model\BaseModel;

class Upload extends BaseModel
{

    public $upload_path = __UPLOAD__;//公共上传文件
    public $config = []; //上传配置
    public $site_id;
    public $rule_type;//允许上传 mime类型
    public $rule_ext;// 允许上传 文件后缀
    public $path;//上传路径

    public $ext = '';
    public $driver = 'gd';
    public $image_service;//图片类实例

    public function __construct($site_id = 1, $app_module = 'shop')
    {
        $this->site_id = $site_id;
        $config_model = new Config();
        $config_result = $config_model->getUploadConfig(1, 'shop');
        $this->config = $config_result[ "data" ][ "value" ];//上传配置
        $this->driver = config('upload')[ 'driver' ] ?? 'gd';
        $this->image_service = new ImageService($this->driver);
    }
    /************************************************************上传开始*********************************************/

    /**
     * 单图上传
     * @param number $site_id
     * @param string $thumb_type 生成缩略图类型
     */
    public function image($param)
    {
        $check_res = $this->checkImg();
        if ($check_res[ "code" ] >= 0) {
            $file = request()->file($param[ "name" ]);
            if (empty($file))
                return $this->error();

            $tmp_name = $file->getPathname();//获取上传缓存文件
            $original_name = $file->getOriginalName();//文件原名
            $file_path = $this->path;
            // 检测目录
            $checkpath_result = $this->checkPath($file_path);//验证写入文件的权限
            if ($checkpath_result[ "code" ] < 0)
                return $checkpath_result;

            $file_name = $file_path . $this->createNewFileName();
            $extend_name = $file->getOriginalExtension();
//            $thumb_type = $param[ "thumb_type" ];
            //原图保存
            $new_file = $file_name . "." . $extend_name;
            $image = $this->getImageService($tmp_name);

            $width = $image->width;//图片宽
            $height = $image->height;//图片高
            if (!empty($param[ 'width' ]) && !empty($param[ 'height' ]) && $width != $param[ 'width' ] && $height != $param[ 'height' ]) {
                return $this->error('', '图片尺寸限制为' . $param[ 'width' ] . ' x ' . $param[ 'height' ]);
            } elseif (!empty($param[ 'width' ]) && $width != $param[ 'width' ]) {
                return $this->error('', '图片尺寸宽度限制为' . $param[ 'width' ]);
            } elseif (!empty($param[ 'height' ]) && $height != $param[ 'height' ]) {
                return $this->error('', '图片尺寸高度限制为' . $param[ 'height' ]);
            }
//            $image->contrast(10);
            // 是否需生成水印
            if (isset($param[ 'watermark' ]) && $param[ 'watermark' ]) {
                $image = $this->imageWater($image);
            }
            // 是否需上传到云存储

            if (isset($param[ 'cloud' ]) && $param[ 'cloud' ]) {
                $result = $this->imageCloud($image, $new_file, $file);
                if ($result[ "code" ] < 0)
                    return $result;
            } else {
                try {
                    $image->save($new_file);
                    $result = $this->success($new_file, "UPLOAD_SUCCESS");
                } catch (\Exception $e) {
                    return $this->error('', $e->getMessage());
                }
            }

//            $thumb_res = $this->thumbBatch($tmp_name, $file_name, $extend_name, $thumb_type);//生成缩略图
//            if ($thumb_res[ "code" ] < 0)
//                return $result;

            $data = array (
                "pic_path" => $result[ "data" ],//图片云存储
                "pic_name" => $original_name,
                "file_ext" => $extend_name,
                "pic_spec" => $width . "*" . $height,
                "update_time" => time(),
                "site_id" => $this->site_id
            );
            return $this->success($data, "UPLOAD_SUCCESS");
        } else {
            //返回错误信息
            return $check_res;
        }
    }


    public function getImageService($file)
    {
        $image = $this->image_service->open($file);
        return $image;
    }

    /**
     * 相册图片上传
     * @param number $site_id
     * @param number $category_id
     * @param string $thumb_type
     */
    public function imageToAlbum($param)
    {
        $check_res = $this->checkImg();
        if ($check_res[ "code" ] >= 0) {
            $file = request()->file($param[ "name" ]);
            if (empty($file))
                return $this->error();

            $tmp_name = $file->getPathname();//获取上传缓存文件
            $original_name = $file->getOriginalName();//文件原名

            $file_path = $this->path;
            // 检测目录
            $checkpath_result = $this->checkPath($file_path);//验证写入文件的权限
            if ($checkpath_result[ "code" ] < 0)
                return $checkpath_result;

            $file_name = $file_path . $this->createNewFileName();
            $extend_name = $file->getOriginalExtension();
            $this->ext = $extend_name;
            $thumb_type = $param[ "thumb_type" ];//所留
            $album_id = $param[ "album_id" ];
            $is_thumb = $param[ 'is_thumb' ] ?? 0;
            $new_file = $file_name . "." . $extend_name;

            $image = $this->getImageService($tmp_name);
            $width = $image->width;//图片宽
            $height = $image->height;//图片高
            // 原图不需要加水印处理
//            if($is_thumb == 1){
//            $image = $this->imageWater($image);
//            }
            $result = $this->imageCloud($image, $new_file, $file);//原图云上传(文档流上传)

            if ($result[ "code" ] < 0)
                return $result;

            if ($is_thumb == 1) {
                $thumb_res = $this->thumbBatch($result[ 'data' ], $file_name, $extend_name, $thumb_type);//生成缩略图
                if ($thumb_res[ "code" ] < 0)
                    return $result;
            }
            $pic_name_first = substr(strrchr($original_name, '.'), 1);
            $pic_name = basename($original_name, "." . $pic_name_first);
            $data = array (
                "pic_path" => $result[ "data" ],//图片云存储
                "pic_name" => $pic_name,
                "pic_spec" => $width . "*" . $height,
                "update_time" => time(),
                "site_id" => $this->site_id,
                "album_id" => $album_id,
                "is_thumb" => $is_thumb,
            );
            $album_model = new Album();
            $res = $album_model->addAlbumPic($data);
            if ($res[ 'code' ] >= 0) {
                $data[ "id" ] = $res[ "data" ];
                return $this->success($data, "UPLOAD_SUCCESS");
            } else {
                return $this->error($res);
            }
        } else {
            //返回错误信息
            return $check_res;
        }

    }


    /*
     * 替换图片文件
     * */
    public function modifyFile($param)
    {
//        参数校验
        if (empty($param[ 'album_id' ])) {
            return $this->error('', "PARAMETER_ERROR");
        }

        if (empty($param[ 'pic_id' ])) {
            return $this->error('', "PARAMETER_ERROR");
        }

        if (empty($param[ 'filename' ])) {
            return $this->error('', "PARAMETER_ERROR");
        }

        if (empty($param[ 'suffix' ])) {
            return $this->error('', "PARAMETER_ERROR");
        }

        $check_res = $this->checkImg();

        if ($check_res[ "code" ] >= 0) {

            $file = request()->file($param[ "name" ]);
            if (empty($file))
                return $this->error();

            $tmp_name = $file->getPathname();//获取上传缓存文件
            $original_name = $file->getOriginalName();//文件原名

            $file_path = $this->path;
            // 检测目录
            $checkpath_result = $this->checkPath($file_path);//验证写入文件的权限
            if ($checkpath_result[ "code" ] < 0) {
                return $checkpath_result;
            }

//            保留原文件名和后缀
            $file_name = $file_path . $param[ 'filename' ];
            $extend_name = $param[ 'suffix' ];
            $thumb_type = $param[ "thumb_type" ];//所留
            //原图保存
            $new_file = $file_name . "." . $extend_name;
            $image = $this->getImageService($tmp_name);
            $width = $image->width;//图片宽
            $height = $image->height;//图片高
//            $image = $this->imageWater($image);

            $result = $this->imageCloud($image, $new_file, $file);//原图云上传(文档流上传)
            if ($result[ "code" ] < 0) {
                return $result;
            }

            $thumb_res = $this->thumbBatch($tmp_name, $file_name, $extend_name, $thumb_type);//生成缩略图
            if ($thumb_res[ "code" ] < 0) {
                return $thumb_res;
            }

            $pic_name_first = substr(strrchr($original_name, '.'), 1);
            $pic_name = basename($original_name, "." . $pic_name_first);

            $data = array (
                "pic_path" => $result[ "data" ],//图片云存储
                "pic_spec" => $width . "*" . $height,
                "update_time" => time(),
            );

            $album_model = new Album();
            $condition = array (
                [ "pic_id", "=", $param[ 'pic_id' ] ],
                [ "site_id", "=", $this->site_id ],
                [ 'album_id', "=", $param[ 'album_id' ] ],
            );

            $res = $album_model->editAlbumPic($data, $condition);

            if ($res[ 'code' ] >= 0) {
                $data[ "id" ] = $res[ "data" ];
                return $this->success($data, "UPLOAD_SUCCESS");
            } else {
                return $this->error($res);
            }

        } else {
            //返回错误信息
            return $check_res;
        }

    }

    /**
     * 视频上传
     * @param $param
     */
    public function videoToAlbum($param)
    {
        $check_res = $this->checkVideo();
        if ($check_res[ "code" ] >= 0) {
            // 获取表单上传文件
            $file = request()->file($param[ "name" ]);
            try {
                $extend_name = $file->getOriginalExtension();
                $new_name = $this->createNewFileName() . "." . $extend_name;
                $original_name = $file->getOriginalName();//文件原名
                $file_path = $this->path;
                \think\facade\Filesystem::disk('public')->putFileAs($file_path, $file, $new_name);
                $file_name = $file_path . $new_name;
                $result = $this->fileCloud($file_name);

                $pic_name_first = substr(strrchr($original_name, '.'), 1);
                $pic_name = basename($original_name, "." . $pic_name_first);
                $data = array (
                    "pic_path" => $result[ "data" ],//图片云存储
                    "pic_name" => $pic_name,
                    "pic_spec" => '',
                    "update_time" => time(),
                    "site_id" => $this->site_id,
                    "album_id" => $param[ 'album_id' ],
                    "is_thumb" => 0,
                );
                $album_model = new Album();
                $res = $album_model->addAlbumPic($data);

                if ($res[ 'code' ] >= 0) {
                    $data[ "id" ] = $res[ "data" ];
                    return $this->success($data, "UPLOAD_SUCCESS");
                } else {
                    return $this->error($res);
                }

            } catch (\think\exception\ValidateException $e) {
                return $this->error('', $e->getMessage());
            }
        } else {
            return $check_res;
        }
    }

    /**
     * 视频上传
     * @param $param
     */
    public function video($param)
    {
        $check_res = $this->checkVideo();
        if ($check_res[ "code" ] >= 0) {
            // 获取表单上传文件
            $file = request()->file($param[ "name" ]);
            try {
                $extend_name = $file->getOriginalExtension();
                $new_name = $this->createNewFileName() . "." . $extend_name;

                $file_path = $this->path;
                \think\facade\Filesystem::disk('public')->putFileAs($file_path, $file, $new_name);
                $file_name = $file_path . $new_name;
                $result = $this->fileCloud($file_name);
                return $this->success([ "path" => $result[ 'data' ] ?? '' ], "UPLOAD_SUCCESS");
            } catch (\think\exception\ValidateException $e) {
                return $this->error('', $e->getMessage());
            }
        } else {
            return $check_res;
        }
    }

    /*
     * 替换视频文件
     * */
    public function modifyVideoFile($param)
    {
//        参数校验
        if (empty($param[ 'album_id' ])) {
            return $this->error('', "PARAMETER_ERROR");
        }

        if (empty($param[ 'pic_id' ])) {
            return $this->error('', "PARAMETER_ERROR");
        }

        $check_res = $this->checkVideo();

        if ($check_res[ "code" ] >= 0) {

            $file = request()->file($param[ "name" ]);
            if (empty($file))
                return $this->error();

            $extend_name = $file->getOriginalExtension();
            $new_name = $this->createNewFileName() . "." . $extend_name;
            $original_name = $file->getOriginalName();//文件原名
            $file_path = $this->path;

            // 检测目录
            $checkpath_result = $this->checkPath($file_path);//验证写入文件的权限
            if ($checkpath_result[ "code" ] < 0) {
                return $checkpath_result;
            }

            \think\facade\Filesystem::disk('public')->putFileAs($file_path, $file, $new_name);
            $file_name = $file_path . $new_name;
            $result = $this->fileCloud($file_name);

            $pic_name_first = substr(strrchr($original_name, '.'), 1);
            $pic_name = basename($original_name, "." . $pic_name_first);

            $data = array (
                "pic_path" => $result[ "data" ],//图片云存储
                "update_time" => time(),
            );

            $album_model = new Album();
            $condition = array (
                [ "pic_id", "=", $param[ 'pic_id' ] ],
                [ "site_id", "=", $this->site_id ],
                [ 'album_id', "=", $param[ 'album_id' ] ],
            );

            $res = $album_model->editAlbumPic($data, $condition);

            if ($res[ 'code' ] >= 0) {
                $data[ "id" ] = $res[ "data" ];
                return $this->success($data, "UPLOAD_SUCCESS");
            } else {
                return $this->error($res);
            }

        } else {
            //返回错误信息
            return $check_res;
        }

    }

    /**
     * 上传文件
     * @param $param
     */
    public function file($param)
    {
        $check_res = $this->checkFile();
        if ($check_res[ "code" ] >= 0) {
            // 获取表单上传文件
            $file = request()->file($param[ "name" ]);
            try {
                $extend_name = $file->getOriginalExtension();
                if (!empty($param[ 'extend_type' ])) {
                    if (!in_array($extend_name, $param[ 'extend_type' ])) {
                        return $this->error([], 'UPLOAD_TYPE_ERROR');
                    }
                }
                $new_name = $this->createNewFileName() . "." . $extend_name;
                $file_path = $this->path;
                \think\facade\Filesystem::disk('public')->putFileAs($file_path, $file, $new_name);
                $file_name = $file_path . $new_name;
                return $this->success([ "path" => $file_name, 'name' => $new_name ], "UPLOAD_SUCCESS");
            } catch (\think\exception\ValidateException $e) {
                return $this->error('', $e->getMessage());
            }
        } else {
            return $check_res;
        }

    }

    /**
     *  域名校验文件
     */
    public function domainCheckFile($param)
    {
        $check_res = $this->checkFile();
        if ($check_res[ "code" ] >= 0) {
            // 获取表单上传文件
            $file = request()->file($param[ "name" ]);
            try {
                $file_name = $file->getOriginalName();
                $file_path = '';
                \think\facade\Filesystem::disk('public')->putFileAs($file_path, $file, $file_name);
                $file_name = $file_path . $file_name;
                return $this->success([ "path" => $file_name ], "UPLOAD_SUCCESS");
            } catch (\think\exception\ValidateException $e) {
                return $this->error('', $e->getMessage());
            }
        } else {
            return $check_res;
        }
    }
    /************************************************************上传结束*********************************************/
    /************************************************************上传功能组件******************************************/


    /**
     * 缩略图生成
     * @param unknown $file_name
     * @param unknown $extend_name
     * @param unknown $thumb_type
     * @return Ambigous <string, multitype:multitype:string  >
     */
    public function thumbBatch($file_path, $file_name, $extend_name, $thumb_type = [])
    {
        $thumb_type_array = array (
            "BIG" => array (
                "size" => "BIG",
                "width" => $this->config[ "thumb" ][ "thumb_big_width" ],
                "height" => $this->config[ "thumb" ][ "thumb_big_height" ],
                "thumb_name" => ""
            ),
            "MID" => array (
                "size" => "MID",
                "width" => $this->config[ "thumb" ][ "thumb_mid_width" ],
                "height" => $this->config[ "thumb" ][ "thumb_mid_height" ],
                "thumb_name" => ""
            ),
            "SMALL" => array (
                "size" => "SMALL",
                "width" => $this->config[ "thumb" ][ "thumb_small_width" ],
                "height" => $this->config[ "thumb" ][ "thumb_small_height" ],
                "thumb_name" => ""
            )
        );
        foreach ($thumb_type_array as $k => $v) {
            if (!empty($thumb_type) && in_array($k, $thumb_type)) {
                $new_path_name = $file_name . "_" . $v[ "size" ] . "." . $extend_name;
                $result = $this->imageThumb($file_path, $new_path_name, $v[ "width" ], $v[ "height" ], $v[ "size" ] != 'BIG' ? 'center' : '');
                //返回生成的缩略图路径
                if ($result[ "code" ] >= 0) {
                    $thumb_type_array[ $k ][ "thumb_name" ] = $new_path_name;
                } else {
                    return $result;
                }
            }
        }
        return $this->success($thumb_type_array);
    }

    /**
     * 缩略图
     * @param unknown $file_name
     * @param unknown $new_path
     * @param unknown $width
     * @param unknown $height
     * @return multitype:boolean unknown |multitype:boolean
     */
    public function imageThumb($file, $thumb_name, $width, $height, $fit = 'center')
    {
//        $image = $this->getImageService($file)->thumb($width, $height, $fit);
//        $result = $this->imageCloud($image, $thumb_name);
        $image = $this->getImageService($file);

        $image = $image->thumb($width, $height, $fit);
        $image = $this->imageWater($image);
        $result = $this->imageCloud($image, $thumb_name);
        return $result;
    }

    /**
     * 添加水印
     */
    public function imageWater($image)
    {
        //判断是否有水印(具体走配置)
        if ($this->config[ "water" ][ "is_watermark" ]) {
            switch ( $this->config[ "water" ][ "watermark_type" ] ) {
                case "1"://图片水印
                    if (!empty($this->config[ "water" ][ "watermark_source" ]) && is_file($this->config[ "water" ][ "watermark_source" ])) {
                        $water_path = $this->config[ "water" ][ "watermark_source" ];
                        $water_opacity = empty($this->config[ "water" ][ "watermark_opacity" ]) ? 0 : $this->config[ "water" ][ "watermark_opacity" ];
                        $water_rotate = empty($this->config[ "water" ][ "watermark_rotate" ]) ? 0 : $this->config[ "water" ][ "watermark_rotate" ];
                        $water_position = $this->config[ "water" ][ "watermark_position" ];
                        $water_x = $this->config[ "water" ][ "watermark_x" ];
                        $water_y = $this->config[ "water" ][ "watermark_y" ];
                        $image = $image->imageWater($water_path, $water_opacity, $water_rotate, $water_position, $water_x, $water_y);
                    }
                    break;
                case "2"://文字水印

                    if (!empty($this->config[ "water" ][ "watermark_text" ])) {
                        $text = $this->config[ "water" ][ "watermark_text" ];
                        $x = $this->config[ "water" ][ "watermark_x" ];
                        if(empty($x)){
                            $x = 0;
                        }
                        $y = $this->config[ "water" ][ "watermark_y" ];
                        if(empty($y)){
                            $y = 0;
                        }
                        $size = $this->config[ "water" ][ "watermark_text_size" ];
                        if(empty($size)){
                            $size = 12;
                        }
                        $color = $this->config[ "water" ][ "watermark_text_color" ];
                        $align = $this->config[ "water" ][ "watermark_text_align" ];
                        $valign = $this->config[ "water" ][ "watermark_text_valign" ];
                        $angle = $this->config[ "water" ][ "watermark_text_angle" ];
                        if(empty($angle)){
                            $angle = 0;
                        }
                        $image = $image->textWater($text, $x, $y, $size, $color, $align, $valign, $angle);
                    }
                    break;
            }
        }

        return $image;
    }

    public function to_unicode($string)
    {
        $str = mb_convert_encoding($string, 'gb2312', 'UTF-8');
        $arrstr = str_split($str, 2);
        $unistr = '';
        foreach ($arrstr as $n) {
            $dec = hexdec(bin2hex($n));
            $unistr .= '&#' . $dec . ';';
        }
        return $unistr;
    }

    /**
     * 删除文件
     * @param $file_name
     */
    public function deleteFile($file_name)
    {
        if (file_exists($file_name)) {
            $res = @unlink($file_name);
            if ($res) {
                return $this->success();
            } else {
                return $this->error();
            }
        }
        return $this->success();

    }

    /**
     * 图片云上传中转
     * @param $image
     * @param $file
     * @return array|mixed|string
     */
    public function imageCloud($image_class, $file, $tmp_file = '')
    {
        try {
            $compress_array = array (
                'large' => 90,
                'medium' => 75,
                'small' => 55,
                'original' => null
            );
            $compress = $this->config[ 'upload' ][ 'compress' ] ?? 'original';
            if ($compress == 'original' && !empty($tmp_file)) {
                \think\facade\Filesystem::disk('public')->putFileAs('', $tmp_file, $file);
            } else {
                $compress = $compress_array[ $this->config[ 'upload' ][ 'compress' ] ?? 'original' ];
                $image_class->save($file, $compress);
            }

            $result = $this->fileCloud($file);
            //云上传没有成功  保存到本地
            return $result;
        } catch (\Exception $e) {
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 云上传
     */
    public function fileCloud($file)
    {
        try {
            //走 云上传
            $put_result = event("Put", [ "file_path" => $file, "key" => $file ], true);
            if (!empty($put_result)) {
                if ($put_result[ "code" ] >= 0) {
                    $this->deleteFile($file);
                    $file = $put_result[ "data" ][ "path" ];
                } else {
                    return $put_result;
                }
            }
            //云上传没有成功  保存到本地
            return $this->success($file, "UPLOAD_SUCCESS");
        } catch (\Exception $e) {
            return $this->error('', $e->getMessage());
        }
    }


    /**
     * 图片验证
     * @param $file
     * @return \multitype
     */
    public function checkImg()
    {
        try {
            $file = request()->file();
            $rule_array = [];
            $size_rule = $this->config[ 'upload' ][ 'max_filesize' ];
            $ext_rule = 'jpg,jpeg,png,gif,pem,webp';
            $mime_rule = 'image/webp,image/jpg,image/jpeg,image/gif,image/png,text/plain';

            if (!empty($size_rule)) {
                $rule_array[] = "fileSize:{$size_rule}";
            }
            if (!empty($ext_rule)) {
                $rule_array[] = "fileExt:{$ext_rule}";
            }
            if (!empty($mime_rule)) {
                $rule_array[] = "fileMime:{$mime_rule}";
            }
            if (!empty($rule_array)) {
                $rule = implode('|', $rule_array);
                validate([ 'file' => $rule ])->check($file);
            }
            return $this->success();
        } catch (\think\exception\ValidateException $e) {
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 文件验证
     * @param $file
     * @return \multitype
     */
    public function checkFile()
    {
        try {
            $file = request()->file();
            $suffix = pathinfo($_FILES[ 'file' ][ 'name' ], PATHINFO_EXTENSION);
            if ($suffix == "pem" || $suffix == "crt") {
                return $this->success();
            }

            $rule_array = [];

            $size_rule = '';
            $ext_rule = "txt,xlsx,xls,csv,pem";
            $mime_rule = "text/plain,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv";

            if (!empty($size_rule)) {
                $rule_array[] = "fileSize:{$size_rule}";
            }
            if (!empty($ext_rule)) {
                $rule_array[] = "fileExt:{$ext_rule}";
            }
            if (!empty($mime_rule)) {
                $rule_array[] = "fileMime:{$mime_rule}";
            }
            $rule = implode("|", $rule_array);
            $res = validate([ 'file' => $rule ])->check($file);
            if ($res) {
                return $this->success();
            } else {
                return $this->error();
            }
        } catch (\think\exception\ValidateException $e) {
            return $this->error('', $e->getMessage());
        }
    }

    /************************************************************上传功能组件******************************************/

    public function checkVideo()
    {
        try {
            $file = request()->file();
            $rule_array = [];

            $size_rule = '';
            $ext_rule = "mp4,avi";
            $mime_rule = "video/mp4,video/x-msvideo";

            if (!empty($size_rule)) {
                $rule_array[] = "fileSize:{$size_rule}";
            }
            if (!empty($ext_rule)) {
                $rule_array[] = "fileExt:{$ext_rule}";
            }
            if (!empty($mime_rule)) {
                $rule_array[] = "fileMime:{$mime_rule}";
            }
            $rule = implode("|", $rule_array);
            $res = validate([ 'file' => $rule ])->check($file);
            if ($res) {
                return $this->success();
            } else {
                return $this->error();
            }
        } catch (\think\exception\ValidateException $e) {
            return $this->error('', $e->getMessage());
        }
    }

    /**
     *获取一个新文件名
     */
    public function createNewFileName()
    {
        $name = date('Ymdhis', time())
            . sprintf('%03d', microtime(true) * 1000)
            . sprintf('%02d', mt_rand(10, 99));
        return $name;
    }

    /**
     * 验证目录是否可写
     * @param unknown $path
     * @return boolean
     */
    public function checkPath($path)
    {
        if (file_exists($path) || mkdir($path, 0755, true)) {
            return $this->success();
        }

        return $this->error('', "上传目录 {$path} 创建失败，请检测权限");
    }

    /**
     * 设置上传目录
     * @param $path
     */
    public function setPath($path)
    {
        if ($this->site_id > 0) {
            $this->path = $this->site_id . "/" . $path;
        } else {
            $this->path = $path;
        }
        $this->path = $this->upload_path . "/" . $this->path;
        return $this;
    }


    /**
     * 远程拉取图片
     * @param $path
     */
    public function remotePull($path)
    {
        try {
            $file_path = $this->path;
            // 检测目录
            $checkpath_result = $this->checkPath($file_path);//验证写入文件的权限
            if ($checkpath_result[ "code" ] < 0)
                return $checkpath_result;

            $file_name = $file_path . $this->createNewFileName();
            $new_file = $file_name . ".png";


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            $file = curl_exec($ch);
            curl_close($ch);

            $image = $this->getImageService($file);
            $image = $this->imageWater($image);
            $result = $this->imageCloud($image, $new_file);//原图云上传(文档流上传)

            if ($result[ "code" ] < 0)
                return $result;

            return $this->success([ "pic_path" => $result[ "data" ] ]);
        } catch (\think\exception\ValidateException $e) {
            return $this->error('', $e->getMessage());
        }
    }

    public function remotePullBinary($file)
    {
        $file_path = $this->path;
        // 检测目录
        $checkpath_result = $this->checkPath($file_path);//验证写入文件的权限
        if ($checkpath_result[ "code" ] < 0)
            return $checkpath_result;

        $file_name = $file_path . $this->createNewFileName();
        $new_file = $file_name . ".png";

        $image = $this->getImageService($file);
        $result = $this->imageCloud($image, $new_file);//原图云上传(文档流上传)
        if ($result[ "code" ] < 0)
            return $result;

        return $this->success([ "pic_path" => $result[ "data" ] ]);
    }

    /**
     * 远程拉取图片到本地
     * @param $path
     */
    public function remotePullToLocal($path)
    {

        if (stristr($path, 'http://') || stristr($path, 'https://')) {
            $file_path = $this->path;
            // 检测目录
            $checkpath_result = $this->checkPath($file_path);//验证写入文件的权限
            if ($checkpath_result[ "code" ] < 0)
                return $checkpath_result;

            $file_name = $file_path . $this->createNewFileName();
            $new_file = $file_name . ".png";


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            $file = curl_exec($ch);
            curl_close($ch);

            $image = $this->getImageService($file);
            $image = $this->imageWater($image);
            $image->save($new_file);
            return $this->success([ "path" => $new_file ]);
        } else {
            return $this->success([ "path" => $path ]);
        }
    }

    /**
     * 远程拉取图片到本地
     * @param $path
     */
    public function remotePullToLocalPic($path)
    {
        if(strpos($path, 'http://') === false && strpos($path, 'https://') === false){
            $path = "https:".$path;
        }
        $file_path = $this->path;
        // 检测目录
        $checkpath_result = $this->checkPath($file_path);//验证写入文件的权限
        if ($checkpath_result[ "code" ] < 0)
            return $checkpath_result;

        $file_name = $file_path . $this->createNewFileName();
        $new_file = $file_name . ".png";

        $content = file_get_contents($path);
        //todo  考虑imagick支持文档流的可能性
        $image = Image::make($content);//兼容
        $image = $this->imageWater($image);
        $image->save($new_file);
        return $this->success([ "path" => $new_file ]);
    }


    /**
     * 二维码生成  返回base64
     * @param $url
     * @return array
     */
    public function qrcode($url)
    {

        $file_path = qrcode($url, "weixinpay/qrcode/" . date("Ymd") . '/', date("Ymd") . 'qrcode');
        //$file：图片地址
        //Filetype: JPEG,PNG,GIF
        $file = $file_path;
        if ($fp = fopen($file, "rb", 0)) {
            $gambar = fread($fp, filesize($file_path));
            fclose($fp);
            $base64 = "data:image/jpg/png/gif;base64," . chunk_split(base64_encode($gambar));
            $this->deleteFile($file_path);
            return $this->success($base64);
        } else {
            return $this->error();
        }
    }

    /**
     * 远程拉取商品图片
     */
    public function remoteGoodsPullToLocal($param)
    {
        $remote_result = $this->remotePullToLocalPic($param[ 'img' ]);
        if ($remote_result[ 'code' ] < 0) {
            return $remote_result;
        }
        $img_path = $remote_result[ 'data' ][ 'path' ];//原图本地化的图片路径
        $file_path = $this->path;
        $file_name = $file_path . $this->createNewFileName();//生成新的完整文件路径
        $img_array = explode('.', $param[ 'img' ]);
        $extend_name = end($img_array);//获取文件的后缀名
        $thumb_type = $param[ "thumb_type" ];
        //原图保存
        $new_file = $file_name . "." . $extend_name;

        $image = $this->getImageService($img_path);

        $width = $image->width;//图片宽
        $height = $image->height;//图片高

        $image = $this->imageWater($image);

        $result = $this->imageCloud($image, $new_file);//原图云上传(文档流上传)
        if ($result[ "code" ] < 0)
            return $result;

        if($param['thumb_type']){
            $thumb_res = $this->thumbBatch($img_path, $file_name, $extend_name, $thumb_type);//生成缩略图
            if ($thumb_res[ "code" ] < 0)
                return $result;
        }

        $data = array (
            "pic_path" => $result[ "data" ],//图片云存储
            "file_ext" => $extend_name,
            "pic_spec" => $width . "*" . $height,
            "update_time" => time(),
            "site_id" => $this->site_id
        );

        $album_data = array (
            "pic_path" => $result[ "data" ],//图片云存储
            "pic_name" => '',
            "pic_spec" => $width . "*" . $height,
            "update_time" => time(),
            "site_id" => $this->site_id,
            "album_id" => $param[ 'album_id' ]
        );
        $album_model = new Album();
        $res = $album_model->addAlbumPic($album_data);
        if ($res[ 'code' ] >= 0) {
            return $this->success($data, "UPLOAD_SUCCESS");
        } else {
            return $this->error($res);
        }
    }

    public function deletePic($pic_path, $site_id)
    {
        if (strpos($pic_path, 'https://') === 0 || strpos($pic_path, 'http://') === 0) {
            event("ClearAlbumPic", [ "pic_path" => $pic_path, "site_id" => $site_id ]);
        } else {
            if (file_exists($pic_path)) {
                unlink($pic_path);
            }
            if (file_exists(img($pic_path, 'big'))) {
                unlink(img($pic_path, 'big'));
            }
            if (file_exists(img($pic_path, 'mid'))) {
                unlink(img($pic_path, 'mid'));
            }
            if (file_exists(img($pic_path, 'small'))) {
                unlink(img($pic_path, 'small'));
            }
        }
    }
}