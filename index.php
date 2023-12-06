<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;
if (version_compare(PHP_VERSION, '7.4.0', '<'))
	die('require PHP > 7.4.0 !');

// 检测PHP环境  允许前端跨域请求
header("Access-Control-Allow-Origin:*");
// 响应类型
header('Access-Control-Allow-Methods:GET, POST, PUT, DELETE');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with, content-type');

if (!file_exists('./install.lock')) {
	header('location: ./install.php');
	exit();
}
$query_string = $_SERVER['REQUEST_URI'];
if(!empty($query_string))
{
    $file_ext = substr($query_string, -3);
    $array = [ 'jpg', 'png', 'css', '.js', 'txt', 'doc', 'ocx', 'peg' ];
    if (in_array($file_ext, $array)) {
        exit();
    }
    $query_string_array = explode('/', $query_string);
    $app_name = $query_string_array[1] ?? '';
    switch ($app_name) {
        case 'h5':
            echo file_get_contents('./h5/index.html');exit();
//        case 'web':
//            echo file_get_contents('./web/index.html',);exit();
        case 'mshop':
            echo file_get_contents('./mshop/index.html',);exit();
        case 'cashregister':
            echo file_get_contents('./cashregister/index.html', );exit();
    }

}

require __DIR__ . '/vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
