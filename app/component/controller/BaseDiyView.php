<?php

namespace app\component\controller;

use app\Controller;
use liliuwei\think\Jump;

class BaseDiyView extends Controller
{
    use Jump;

    // 当前组件路径
    private $path;

    // 资源路径
    private $resource_path;

    // 相对路径
    private $relative_path;


    public function __construct()
    {
        parent::__construct();

        $class = get_class($this);
        $routes = explode('\\', $class);
        if ($routes[ 0 ] == 'app') {
            //系统·组件：app/component/controller/Text
            $this->path = './' . $routes[ 0 ] . '/';
            $this->resource_path = __ROOT__ . '/' . $routes[ 0 ] . '/' . $routes[ 1 ] . '/view';
            $this->relative_path = $routes[ 0 ] . '/' . $routes[ 1 ] . '/view';
        } elseif ($routes[ 0 ] == 'addon') {
            //插件·组件：addon/seckill/component/controller/seckill
            $this->path = './' . $routes[ 0 ] . '/' . $routes[ 1 ] . '/';
            $this->resource_path = __ROOT__ . '/' . $routes[ 0 ] . '/' . $routes[ 1 ] . '/' . $routes[ 2 ] . '/view';
            $this->relative_path = $routes[ 0 ] . '/' . $routes[ 1 ] . '/' . $routes[ 2 ] . '/view';
        }

    }

    /**
     * 后台编辑界面
     */
    public function design()
    {
    }

    /**
     * 加载模板输出
     *
     * @access protected
     * @param string $template 模板文件名
     * @param array $vars 模板输出变量
     * @param array $replace 模板替换
     */
    protected function fetch($template = '', $vars = [], $replace = [])
    {
        $comp_folder_name = explode('/', $template)[ 0 ];// 获取组件文件夹名称
        $template = $this->path . 'component/view/' . $template;
        $this->resource_path .= '/' . $comp_folder_name; // 拼接组件文件夹名称
        $this->relative_path .= '/' . $comp_folder_name; // 拼接组件文件夹名称
        parent::assign('resource_path', $this->resource_path);
        parent::assign('relative_path', $this->relative_path);
        return parent::fetch($template, $vars, $replace);
    }
}