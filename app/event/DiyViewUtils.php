<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\event;

use ReflectionClass;
use ReflectionException;

/**
 * 自定义模板组件渲染
 */
class DiyViewUtils
{
    /**
     * 行为扩展的执行入口必须是run
     * @param $data
     * @return mixed | void
     * @throws ReflectionException
     */
    public function handle($data)
    {
        $port = [ 'app', 'addon' ];
        if (!empty($data[ 'name' ])) {
            $class_name = '';
            $is_exist = false;
            foreach ($port as $k => $v) {
                if (!empty($data[ 'addon_name' ])) {
                    $class_name = $v . '\\' . $data[ 'addon_name' ] . '\\component\\controller\\' . $data[ 'name' ];
                } else {
                    $class_name = $v . '\\component\\controller\\' . $data[ 'name' ];
                }
                if (class_exists($class_name)) {
                    $is_exist = true;
                    break;
                }
            }
            if ($is_exist) {
                $class = new ReflectionClass($class_name);
                $instance = $class->newInstanceArgs();
                return $instance->design();
            } else {
                var_dump("not found：" . $class_name);
            }
        }
    }

}