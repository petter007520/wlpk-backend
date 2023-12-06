<?php
namespace app;

// 应用请求对象类
class Request extends \think\Request
{
    /**
     * 站点id
     * @var int
     */
    protected $site_id = 0;
    
    /**
     * 当前访问插件
     * @var string
     */
    protected $addon;
    
    /**
     * 当前访问应用（模块）
     * @var string
     */
    protected $module;
    
    /**
     * 解析url
     * @var unknown
     */
    protected $parseUrl;

    protected $filter = ['filterEmoji'];
    
    /**
     * 站点id
     * @param number $siteid
     */
    public function siteid($siteid = 1)
    {
        
        return 1;
    }
    
    /**
     * 当前访问插件
     * @param string $addon
     * @return string
     */
    public function addon($addon = '')
    {
        if(!empty($addon))
        {
            $GLOBALS["REQUEST_ADDON"] = $addon;
        }
        return isset($GLOBALS["REQUEST_ADDON"]) ? $GLOBALS["REQUEST_ADDON"] : '';
    }
    
    /**
     * 当前访问模块
     * @param string $module
     */
    public function module($module = '')
    {
        if(!empty($module))
        {
            $GLOBALS["REQUEST_MODULE"] = $module;
        }
        return isset($GLOBALS["REQUEST_MODULE"]) ? $GLOBALS["REQUEST_MODULE"] : '';
    }
    
    
    /**
     * 判断当前是否是微信浏览器
     */
    public function isWeixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return 1;
        }
        return 0;
    }
    
    /**
     * 当前登录用户id
     * @return mixed|number
     */
    public function uid($app_module)
    {

        $uid = session($app_module."."."uid");
        if(!empty($uid))
        {
            return $uid;
        }else{
            return 0;
        }
    }
    
    /**
     * 解析url
     */
    public function parseUrl()
    {
        $addon = $this->addon() ? $this->addon() . '://' : '';
        return $addon.$this->module().'/'.$this->controller().'/'.$this->action();
    }
    
}
