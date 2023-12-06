<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022-06-24
 * Time: 12:23
 */

namespace app\model\image;



/**
 * 图片处理服务类
 */
class ImageService
{

    public $imageClass;

    public $image;

    public $width;
    public $height;

    public function __construct($driver = 'gd')
    {
        //可以考虑设计成策略(author 周)
        switch($driver){
            case 'gd':
                $this->imageClass = new GdClass();
                break;
            case 'imagick':
                $this->imageClass = new ImagickClass();
                break;
        }
//        return $this;
    }

    /**
     * 获取图片实例
     * @param $path
     * @return mixed
     */
    public function open($path){

        $this->image = $this->imageClass->open($path);
        $this->getImageParam();
        return $this;
    }


    public function getImageParam(){
        $param = $this->imageClass->getImageParam($this->image);
        $this->width = $param['width'];
        $this->height = $param['height'];

    }
    /**
     * 图片保存
     * @param $image
     * @param $new_file
     */
    public function save($new_file, $compress = 90){
        return $this->imageClass->save($this->image, $new_file, $compress);
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
    public function textWater($text, $x, $y, $size, $color, $align, $valign, $angle){
        $this->image = $this->imageClass->textWater($this->image, $text, $x, $y, $size, $color, $align, $valign, $angle);
        return $this;
    }

    /**
     * 图片水印
     * @param $water_path
     * @param $water_position
     * @param $x
     * @param $y
     * @return mixed
     */
    public function imageWater($water_path, $water_opacity, $water_rotate, $water_position, $x, $y){
        $this->image = $this->imageClass->imageWater($this->image, $water_path, $water_opacity, $water_rotate, $water_position, $x, $y);
        return $this;
    }

    /**
     * 缩略图
     * @param $width
     * @param $height
     * @param $fit
     * @param $fill_color
     */
    public function thumb($width, $height, $fit = 'center', $fill_color = 'ffffff'){
        $this->image = $this->imageClass->thumb($this->image, $width, $height, $fit, $fill_color);
        return $this;
    }
}