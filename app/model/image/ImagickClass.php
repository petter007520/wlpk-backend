<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022-06-24
 * Time: 12:23
 */

namespace app\model\image;

/**
 * Imagick 图片策略类
 * Class ImagickClass
 * @package app\model\upload
 */
class ImagickClass
{
    /**
     * 获取图片实例
     * @param $path
     * @return mixed
     */
    public function open($path){
        $image = (new ImagickService())->open($path);

        return $image;
    }

    /**
     * 图片保存
     * @param $image
     * @param $new_file
     */
    public function save($image, $new_file, $compress = ''){
        return $image->save_to($new_file, $compress);
    }

    public function getImageParam($image){

        $size = $image->getImageParam();
        return [
            'width' => $size['width'],
            'height' => $size['height']
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
//        $spec = $this->getImageParam($image);
//        $x = $spec['width']/2;
//        $y = $spec['height']/2;
        $style = array(
            'font' => PUBLIC_PATH.'static/font/Microsoft.ttf',
            'font_size' => $size,
//            'fill_color' => $size,
//            'under_color' => $size,
        );
        $image->add_text($text, $x, $y, $angle = 0, $style);
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
        $image->add_watermark($water_path, $x, $y);
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
            $fit = 'force';
        }else{
            $fit = 'scale';
        }
        $image->resize_to($width, $height, $fit);
        return $image;
    }
}