<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace app\event;

use think\app\Service;
use think\facade\Route;
use app\model\system\Addon;
use think\facade\Cache;
use app\model\web\WebSite;
/**
 * 初始化路由规则
 * @author Administrator
 *
 */
class InitRoute extends Service
{

    public function handle()
    {
        define("initroute_tag", 1);
        if (defined('BIND_MODULE') && BIND_MODULE === 'install')
            return;
        //检测当前pathinfo
        $pathinfo       = request()->pathinfo();
        $pathinfo_array = explode('/', $pathinfo);
        $url            = request()->domain();
        $check_model    = $pathinfo_array[0];
        //检测当前插件情况
        $addon = in_array($check_model, $GLOBALS['system_array']) ? '' : $check_model;
        if (!empty($addon)) {
            $module     = isset($pathinfo_array[1]) ? $pathinfo_array[1] : 'shop';
            $controller = isset($pathinfo_array[2]) ? $pathinfo_array[2] : 'index';
            $method     = isset($pathinfo_array[3]) ? $pathinfo_array[3] : 'index';
            if(SHOP_MODULE != 'shop')
            {
                if($module == 'shop')
                {
                    throw new \think\exception\HttpException(404, '请求异常');
                }else{
                    if($module == SHOP_MODULE)
                    {
                        $module = 'shop';
                    }
                    $controller = str_replace(SHOP_MODULE, 'shop', $controller);
                    $method = str_replace(SHOP_MODULE, 'shop', $method);

                }
            }
            request()->addon($addon);
            $this->app->setNamespace("addon\\" . $addon . '\\' . $module);
            $this->app->setAppPath($this->app->getRootPath() . 'addon' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR);
        } else {
            $module     = isset($pathinfo_array[0]) ? $pathinfo_array[0] : 'shop';
            $controller = isset($pathinfo_array[1]) ? $pathinfo_array[1] : 'index';
            $method     = isset($pathinfo_array[2]) ? $pathinfo_array[2] : 'index';
            if(SHOP_MODULE != 'shop')
            {
                if($module == 'shop')
                {
                    throw new \think\exception\HttpException(404, '请求异常');
                }else{
                    if($module == SHOP_MODULE)
                    {
                        $module = 'shop';
                    }
                    $controller = str_replace(SHOP_MODULE, 'shop', $controller);
                    $method = str_replace(SHOP_MODULE, 'shop', $method);
                }
            }
            $this->app->setNamespace("app\\" . $module);
            $this->app->setAppPath($this->app->getRootPath() . 'app' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR);

        }
        //解析路由
        $pathinfo   = str_replace(".html", '', $pathinfo);
        $controller = str_replace(".html", '', $controller);
        $method     = str_replace(".html", '', $method);
        request()->module($module);
        Route::rule($pathinfo, $module . '/' . $controller . '/' . $method);
    }

}
