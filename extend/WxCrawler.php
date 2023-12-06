<?php
namespace extend;
/**
 * 微信公众号⽂章爬取类
 */
class WxCrawler
{
    //微信内容div正则
    private $wxContentDiv = "/id=\"js_content\" style=\"visibility: hidden;\">(.*)<\/div>/iUs";
    //微信图⽚样式
    private $imageStyle = 'style="max-width: 100%;height:auto"';

    /**
     * 爬取内容
     * @param  $url
     * @return false|string
     * @author bignerd
     * @since  2016-08-16T10:13:58+0800
     */
    private function _get($url)
    {
        return file_get_contents($url);
    }

    public function crawByUrl($url)
    {
        $content = $this->_get($url);

        if(empty($content)){
            return  error(-1, '⽂章不存在');

        }
        $basicInfo = $this->articleBasicInfo($content);
        $content_result = $this->contentHandle($content);
        if(!empty($content_result['code']) && $content_result['code'] < 0){
            return $content_result;
        }
        list($content_html, $content_text) = $content_result;
        return success(0,'',array_merge($basicInfo, ['content_html' => $content_html, 'content_text' => $content_text]));
    }

    /**
     * 处理微信⽂章源码，提取⽂章主体，处理图⽚链接
     * @author bignerd
     * @since  2016-08-16T15:59:27+0800
     * @param  $content 抓取的微信⽂章源码
     * @return [带图html⽂本，⽆图html⽂本]
     */
    private function contentHandle($content)
    {
        $content_html_pattern = $this->wxContentDiv;
        preg_match_all($content_html_pattern, $content, $html_matchs);
        if (empty(array_filter($html_matchs))) {
            return  error(-1, '⽂章不存在');
        }
        $content_html = $html_matchs[1][0];
//        $content_html = "<div id='js_content'>".$content_html;
        $content_html = "<style>img{max-width:100% !important;height:auto !important}</style>".$content_html;
        $content_html = str_replace("preview.html","player.html",$content_html);
        //去除掉hidden隐藏
//        $content_html = str_replace('style="visibility: hidden;"', '', $content_html);
        //过滤掉iframe
//        $content_html = preg_replace('/<iframe(.*?)<\/iframe>/', '', $content_html);
//		$content_html = preg_replace('/<iframe(.*?)<\/iframe>/', '', $content_html);
        $path = 'article/';
        /** @var  带图⽚html⽂本 */
        $content_html = preg_replace_callback('/data-src="(.*?)"/', function ($matches) use ($path) {
            return 'src="' . img($this->getImg($matches[1])) . '" ' ;
        }, $content_html);
        //添加微信样式
//        $content_html = '<div style="max-width: 677px;margin-left: auto;margin-right: auto;">' . $content_html . '</div>';
        /** @var  ⽆图html⽂本 */
        $content_text = preg_replace('/<img.*?>/s', '', $content_html);
        return [$content_html, $content_text];
    }

    /**
     * 获取⽂章的基本信息
     * @author bignerd
     * @since  2016-08-16T17:16:32+0800
     * @param  $content ⽂章详情源码
     * @return $basicInfo
     */
    private function articleBasicInfo($content)
    {
        //待获取item
        $item = [
            'ct' => 'date',//发布时间
            'msg_title' => 'title',//标题
            'msg_desc' => 'digest',//描述
            'msg_link' => 'content_url',//⽂章链接
            'msg_cdn_url' => 'cover',//封⾯图⽚链接
            'nickname' => 'wechatname',//公众号名称
        ];
        $basicInfo = [
            'author' => '',
            'copyright_stat' => '',
        ];
        foreach ($item as $k => $v) {
            if ($k == 'msg_title')
                $pattern = '/var ' . $k . ' = \'(.*?)\'\.html\(false\);/s';
            else
                $pattern = '/var ' . $k . ' = "\'(.*?)\'";/s';
            preg_match_all($pattern, $content, $matches);
            if (array_key_exists(1, $matches) && !empty($matches[1][0])) {
                $basicInfo[$v] = trim($this->htmlTransform($matches[1][0]));
            } else {
                $basicInfo[$v] = '';
            }
        }

//  // 获取作者
//  preg_match('/<em class="rich_media_meta rich_media_meta_text">(.*?)<\/em>/s', $content, $matchAuthor);
//  if(!empty($matchAuthor[1])) $basicInfo['author'] = $matchAuthor[1];
//  // ⽂章类型
//  preg_match('/<span id="copyright_logo" class="rich_media_meta meta_original_tag">(.*?)<\/span>/s', $content, $matchType);
//  if(!empty($matchType[1])) $basicInfo['copyright_stat'] = $matchType[1];
        return $basicInfo;
    }

    /**
     * 特殊字符转换
     * @author bignerd
     * @since  2016-08-16T17:30:52+0800
     * @param  $string
     * @return $string
     */
    private function htmlTransform($string)
    {
        $string = str_replace('&quot;', '"', $string);
        $string = str_replace('&amp;', '&', $string);
        $string = str_replace('amp;', '', $string);
        $string = str_replace('&lt;', '<', $string);
        $string = str_replace('&gt;', '>', $string);
        $string = str_replace('&nbsp;', ' ', $string);
        $string = str_replace("\\", '', $string);
        return $string;
    }

    /**
     * @param $url
     * @return string
     */
    private function getImg($url)
    {

        $upload_model = new \app\model\upload\Upload();
        $path = 'common/article/' . date('Ymd') . '/';

        $result = $upload_model->setPath($path)->remotePull($url);

        return $result['data']['pic_path'] ?? '';

//        $refer = "http://www.qq.com/";
//        $opt = [
//            'http' => [
//                'header' => "Referer: " . $refer
//            ]
//        ];
//        $context = stream_context_create($opt);
//        //接受数据流
//        $file_contents = file_get_contents($url, false, $context);
//        $imageSteam = Imagecreatefromstring($file_contents);
//        $path = __UPLOAD__.'article/';
//        if (!file_exists($path))
//            mkdir($path, 0777, true);
//        $fileName = time() . rand(0, 99999) . '.jpg';
//        //⽣成新图⽚
//        imagejpeg($imageSteam, $path . $fileName);
//        return $fileName;
    }
}

