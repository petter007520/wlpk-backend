<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022-06-24
 * Time: 12:23
 */

namespace app\model\image;
use Intervention\Image\ImageManagerStatic as Image;
/**
 * Gd 图片策略类(也就是默认)
 * Class ImagickClass
 * @package app\model\upload
 */
class GdClass
{



    /**
     * 获取图片实例
     * @param $path
     * @return mixed
     */
    public function open($path){

        $image = Image::make($path);
        return $image;
    }

    /**
     * 图片保存
     * @param $image
     * @param $new_file
     */
    public function save($image, $new_file, $compress){
        return $image->save($new_file, $compress);
    }

    public function getImageParam($image){
        $width = $image->width();//图片宽
        $height = $image->height();//图片高
        return [
            'width' => $width,
            'height' => $height
        ];
    }
    /**
     * 文字水印
     * @param $text
     * @param $x
     * @param $y
     * @param $size
     * @param $color
     * @param $align
     * @param $valign
     * @param $angle
     * @return mixed
     */
    public function textWater($image, $text, $x, $y, $size, $color, $align, $valign, $angle){
//        $x = $image->width()/2;
//        $y = $image->height()/2;
        $image->text($text, $x, $y, function($font) use ($size, $color, $align, $valign, $angle){
//                        $font->file($this->config["water"]["watermark_text_file"]);//设置字体文件位置
            $font->file(PUBLIC_PATH.'static/font/Microsoft.ttf');
            $font->size($size);//设置字号大小
            $font->color($color);//设置字号颜色
            $font->align($align);//设置字号水平位置
            $font->valign($valign);//设置字号 垂直位置
            $font->angle($angle);//设置字号倾斜角度
        });
        return $image;
    }

    /**
     * 图片水印
     * @param $water_path
     * @param $water_position
     * @param $x
     * @param $y
     * @return mixed
     */
    public function imageWater($image, $water_path, $watermark_opacity, $water_rotate, $water_position, $x, $y){
        $watermark = Image::make($water_path)->opacity($watermark_opacity)->rotate($water_rotate);
        $image->insert($watermark, $water_position, $x, $y);
        return $image;
    }

    /**
     * 缩略图
     * @param $width
     * @param $height
     * @param $fit
     * @param $fill_color
     */
    public function thumb($image, $width, $height, $fit = 'center', $fill_color = 'ffffff'){


        if(!empty($fit)){
            $image = $image->fit($width, $height, function ($constraint) {
//                $constraint->aspectRatio();
//                $constraint->upsize();
            });
        }else{
            $image = $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        return $image;
    }
}