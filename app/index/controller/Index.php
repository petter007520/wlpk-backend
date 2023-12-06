<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\index\controller;

use app\Controller;
use app\model\goods\Goods as GoodsModel;
use app\model\web\Config as ConfigModel;
use app\model\shop\Shop as ShopModel;
use app\model\web\DiyView as DiyViewModel;
use app\shop\controller\Goods;

class Index extends Controller
{
    /**
     * 域名默认跳转  测试提交
     *
     * @return void
     */
    public function index()
    {
        $config_model = new ConfigModel();
        $domain = $config_model->getDomainJumpConfig();
        $jump_type = $domain[ 'data' ][ 'value' ][ 'jump_type' ];
        // 用户前台
        if ($jump_type == 1) {
            if ($this->isMobile()) {
                $domain_name_h5 = $config_model->geth5DomainName();
                $url = $domain_name_h5[ 'data' ][ 'value' ][ 'domain_name_h5' ];
            } else {
                // 检测插件是否存在
                if (addon_is_exit('pc') == 1) {
                    $domain_name_pc = $config_model->getPcDomainName();
                    $url = $domain_name_pc[ 'data' ][ 'value' ][ 'domain_name_pc' ];
                } else {
                    $domain_name_h5 = $config_model->geth5DomainName();
                    $url = $domain_name_h5[ 'data' ][ 'value' ][ 'domain_name_h5' ];
                }
            }
        } else {
            return $this->center();
        }
        $this->redirect($url);
    }

    /**
     * 端口展示中心页面
     */
    private function center()
    {
        $config_model = new ConfigModel();
        $domain_name_h5 = $config_model->geth5DomainName();
        $domain_name_pc = $config_model->getPcDomainName();
        $copy = $config_model->getCopyright();
        $this->assign("h5_url", $domain_name_h5[ 'data' ][ 'value' ][ 'domain_name_h5' ]);
        $this->assign("pc_url", $domain_name_pc[ 'data' ][ 'value' ][ 'domain_name_pc' ]);
        $this->assign("copy", $copy[ 'data' ][ 'value' ]);
        $this->assign("shop_url", url("shop/index/index"));

        return $this->fetch("index/center");
    }

    private function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER[ 'HTTP_X_WAP_PROFILE' ])) {
            return true;
        }

        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息

        if (isset($_SERVER[ 'HTTP_VIA' ])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER[ 'HTTP_VIA' ], "wap") ? true : false;
        }

        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER[ 'HTTP_USER_AGENT' ])) {
            $clientkeywords = array (
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );

            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER[ 'HTTP_USER_AGENT' ]))) {
                return true;
            }
        }

        // 协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER[ 'HTTP_ACCEPT' ])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if (( strpos($_SERVER[ 'HTTP_ACCEPT' ], 'vnd.wap.wml') !== false ) && ( strpos($_SERVER[ 'HTTP_ACCEPT' ], 'text/html') === false || ( strpos($_SERVER[ 'HTTP_ACCEPT' ], 'vnd.wap.wml') < strpos($_SERVER[ 'HTTP_ACCEPT' ], 'text/html') ) )) {
                return true;
            }
        }
        return false;
    }

    /**
     * 店铺推广
     * return
     */
    public function shopUrl()
    {
        //获取商品sku_id
        $shop_model = new ShopModel();
        $res = $shop_model->qrcode(1);
        // dump($res);exit;
        return $res;
    }

    /**
     * 手机端预览
     */
    public function h5Preview()
    {
        $id = input('id', 0);
        $type = input('type', '');

        if ($type == 'page') {
            $diy_view = new DiyViewModel();
            $res = $diy_view->qrcode([
                'site_id' => 1,
                'id' => $id,
                'app_type' => 'h5'
            ])[ 'data' ][ 'path' ][ 'h5' ];
        } elseif ($type == 'goods') {
            $goods_model = new GoodsModel();
            $res = $goods_model->qrcode($id, '', 1)[ 'data' ][ 'path' ][ 'h5' ];
        } else {
            $shop_model = new ShopModel();
            $res = $shop_model->qrcode(1)[ 'data' ][ 'path' ][ 'h5' ];
        }

        $this->assign('h5_data', $res);
        $this->assign('is_mobile', isMobile());
        return $this->fetch("index/h5_preview");
    }

}