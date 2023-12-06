<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\shop\controller;

use app\model\system\Addon;
use app\model\system\Database;
use app\model\system\Menu;
use app\model\web\Config as ConfigModel;
use app\model\web\DiyView as DiyViewModel;
use extend\database\Database as dbdatabase;
use think\facade\Cache;


class System extends BaseShop
{
    /*********************************************************系统缓存与数据库管理***************************************************/
    /**
     * 缓存设置
     */
    public function cache()
    {
        if (request()->isAjax()) {
            $type = input("key", '');
            $type_list = explode(',', $type);
            $res = [];
            $msg = '';
            foreach ($type_list as $k => $v) {
                switch ( $v ) {
                    case 'content':
                        Cache::clear();
                        $msg = '数据缓存清除成功';
                        break;
                    case 'data_table_cache':
                        // 数据表缓存清除
                        if (is_dir('runtime/schema')) {
                            rmdirs("schema");
                        }
                        $msg = '数据表缓存清除成功';
                        break;
                    case 'template_cache':
                        // 模板缓存清除
                        if (is_dir('runtime/temp')) {
                            rmdirs("temp");
                        }
                        $msg = '模板缓存清除成功';
                        break;
                    case 'menu_cache':
                        $addon_model = new Addon();

                        $menu = new Menu();
                        $menu->refreshMenu('shop', '');

                        $addon_model->cacheAddonMenu();

                        $addon_model->cacheAddon();
                        Cache::clear();
                        $msg = '刷新菜单成功';
                        break;
                    case 'diy_view':
                        $res = $this->refreshDiy();
                        Cache::clear();
                        $msg = '刷新自定义模板成功';
                        break;
                    case 'all':
                        // 清除缓存
                        $msg = '一键刷新成功';
                        break;
                }
            }
            return success(0, $msg, $res);
        } else {
            $config_model = new ConfigModel();
            $cache_list = $config_model->getCacheList();
            $this->assign("cache_list", $cache_list);
            return $this->fetch('system/cache');
        }
    }


    public function cach1e()
    {
        if (request()->isAjax()) {
            $type = input("key", '');
            $type_list = explode(',', $type);
            $msg = '';
            foreach ($type_list as $k => $v) {
                switch ( $v ) {
                    case 'all':
                        $msg = '一键刷新成功';
                        break;
                }
            }

            return success(0, $msg, '');
        } else {
            $config_model = new ConfigModel();
            $cache_list = $config_model->getCacheList();
            $this->assign("cache_list", $cache_list);
            return $this->fetch('system/cache');
        }
    }

    /**
     * 插件管理
     */
    public function addon()
    {
        $addon = new Addon();
        if (request()->isAjax()) {
            $addon_name = input("addon_name");
            $tag = input("tag", "install");
            if ($tag == 'install') {
                $res = $addon->install($addon_name);
                return $res;
            } else {
                $res = $addon->uninstall($addon_name);
                return $res;
            }
        }
        $addon = $addon->getAddonAllList();

        $this->assign("addons", $addon[ 'data' ][ 'install' ]);
        $this->assign("uninstall", $addon[ 'data' ][ 'uninstall' ]);
        return $this->fetch('system/addon');
    }

    /**
     * 数据库管理
     */
    public function database()
    {
        $database = new Database();
        $table = $database->getDatabaseList();
        $this->assign('list', $table);
        $this->forthMenu();
        return $this->fetch('system/database');
    }

    /**
     * 数据库还原页面展示
     */
    public function importlist()
    {
        $database = new Database();

        $path = $database->backup_path;
        if (!is_dir($path)) {
            $mode = intval('0777', 8);
            mkdir($path, $mode, true);
        }

        $flag = \FilesystemIterator::KEY_AS_FILENAME;
        $glob = new \FilesystemIterator($path, $flag);
        $list = array ();

        foreach ($glob as $name => $file) {

            if (preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)) {

                $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
                $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                $part = $name[ 6 ];

                if (isset($list[ "{$date} {$time}" ])) {
                    $info = $list[ "{$date} {$time}" ];
                    $info[ 'part' ] = max($info[ 'part' ], $part);
                    $info[ 'size' ] = $info[ 'size' ] + $file->getSize();
                    $info[ 'size' ] = $database->format_bytes($info[ 'size' ]);
                } else {
                    $info[ 'part' ] = $part;
                    $info[ 'size' ] = $file->getSize();
                    $info[ 'size' ] = $database->format_bytes($info[ 'size' ]);
                }

                $info[ 'name' ] = date('Ymd-His', strtotime("{$date} {$time}"));;
                $extension = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                $info[ 'compress' ] = ( $extension === 'SQL' ) ? '-' : $extension;
                $info[ 'time' ] = strtotime("{$date} {$time}");

                $list[] = $info;
            }
        }

        if (!empty($list)) {
            $list = $database->my_array_multisort($list, "time");
        }
        $this->assign('list', $list);
        $this->forthMenu();
        return $this->fetch('system/importlist');

    }

    /**
     * 还原数据库
     */
    public function importData()
    {

        $time = request()->post('time', '');
        $part = request()->post('part', 0);
        $start = request()->post('start', 0);

        $database = new Database();
        if (is_numeric($time) && ( is_null($part) || empty($part) ) && ( is_null($start) || empty($start) )) { // 初始化
            // 获取备份文件信息
            $name = date('Ymd-His', $time) . '-*.sql*';
            $path = realpath($database->backup_path) . DIRECTORY_SEPARATOR . $name;
            $files = glob($path);
            $list = array ();
            foreach ($files as $name) {
                $basename = basename($name);
                $match = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
                $gz = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
                $list[ $match[ 6 ] ] = array (
                    $match[ 6 ],
                    $name,
                    $gz
                );
            }
            ksort($list);
            // 检测文件正确性
            $last = end($list);
            if (count($list) === $last[ 0 ]) {
                session('backup_list', $list); // 缓存备份列表
                $return_data = [
                    'code' => 1,
                    'message' => '初始化完成',
                    'data' => [ 'part' => 1, 'start' => 0 ]
                ];
                return $return_data;
            } else {
                $return_data = [
                    'code' => -1,
                    'message' => '备份文件可能已经损坏，请检查！',
                ];
                return $return_data;
            }
        } elseif (is_numeric($part) && is_numeric($start)) {
            $list = session('backup_list');
            $db = new dbdatabase($list[ $part ], array (
                'path' => realpath($database->backup_path) . DIRECTORY_SEPARATOR,
                'compress' => $list[ $part ][ 2 ]
            ));

            $start = $db->import($start);
            if ($start === false) {
                $return_data = [
                    'code' => -1,
                    'message' => '还原数据出错！',
                ];
                return $return_data;
            } elseif ($start === 0) { // 下一卷
                if (isset($list[ ++$part ])) {
                    $data = array (
                        'part' => $part,
                        'start' => 0
                    );
                    $return_data = [
                        'code' => -1,
                        'message' => "正在还原...#{$part}",
                        'data' => $data
                    ];
                    return $return_data;
                } else {
                    session('backup_list', null);
                    $return_data = [
                        'code' => -1,
                        'message' => "还原完成！",
                    ];
                    return $return_data;
                }
            } else {
                $data = array (
                    'part' => $part,
                    'start' => $start[ 0 ]
                );
                if ($start[ 1 ]) {
                    $rate = floor(100 * ( $start[ 0 ] / $start[ 1 ] ));

                    $return_data = [
                        'code' => 1,
                        'message' => "正在还原...#{$part} ({$rate}%)",
                    ];
                    return $return_data;
                } else {
                    $data[ 'gz' ] = 1;
                    $return_data = [
                        'code' => 1,
                        'message' => "正在还原...#{$part}",
                        'data' => $data
                    ];
                    return $return_data;
                }
            }
        } else {
            $return_data = [
                'code' => -1,
                'message' => "参数有误",
            ];
            return $return_data;
        }
    }

    /**
     * 数据表修复
     */
    public function tablerepair()
    {
        if (request()->isAjax()) {
            $table_str = input('tables', '');
            $database = new Database();
            $res = $database->repair($table_str);
            return $res;
        }
    }


    /**
     * 数据表备份
     */
    public function backup()
    {
        $database = new Database();
        $tables = input('tables', []);
        $id = input('id', '');
        $start = input('start', '');

        if (!empty($tables) && is_array($tables)) { // 初始化
            // 读取备份配置
            $config = array (
                'path' => $database->backup_path . DIRECTORY_SEPARATOR,
                'part' => 20971520,
                'compress' => 1,
                'level' => 9
            );
            // 检查是否有正在执行的任务
            $lock = "{$config['path']}backup.lock";
            if (is_file($lock)) {
                return error(-1, '检测到有一个备份任务正在执行，请稍后再试！');
            } else {
                $mode = intval('0777', 8);
                if (!file_exists($config[ 'path' ]) || !is_dir($config[ 'path' ]))
                    mkdir($config[ 'path' ], $mode, true); // 创建锁文件

                file_put_contents($lock, date('Ymd-His', time()));
            }
            // 自动创建备份文件夹
            // 检查备份目录是否可写
            is_writeable($config[ 'path' ]) || exit('backup_not_exist_success');
            session('backup_config', $config);
            // 生成备份文件信息
            $file = array (
                'name' => date('Ymd-His', time()),
                'part' => 1
            );

            session('backup_file', $file);

            // 缓存要备份的表
            session('backup_tables', $tables);

            $dbdatabase = new dbdatabase($file, $config);
            if (false !== $dbdatabase->create()) {

                $data = array ();
                $data[ 'status' ] = 1;
                $data[ 'message' ] = '初始化成功';
                $data[ 'tables' ] = $tables;
                $data[ 'tab' ] = array (
                    'id' => 0,
                    'start' => 0
                );
                return $data;
            } else {
                return error(-1, '初始化失败，备份文件创建失败！');
            }
        } elseif (is_numeric($id) && is_numeric($start)) { // 备份数据
            $tables = session('backup_tables');
            // 备份指定表
            $dbdatabase = new dbdatabase(session('backup_file'), session('backup_config'));
            $start = $dbdatabase->backup($tables[ $id ], $start);
            if (false === $start) { // 出错
                return error(-1, '备份出错！');
            } elseif (0 === $start) { // 下一表
                if (isset($tables[ ++$id ])) {
                    $tab = array (
                        'id' => $id,
                        'table' => $tables[ $id ],
                        'start' => 0
                    );
                    $data = array ();
                    $data[ 'rate' ] = 100;
                    $data[ 'status' ] = 1;
                    $data[ 'message' ] = '备份完成！';
                    $data[ 'tab' ] = $tab;
                    return $data;
                } else { // 备份完成，清空缓存
                    unlink($database->backup_path . DIRECTORY_SEPARATOR . 'backup.lock');
                    session('backup_tables', null);
                    session('backup_file', null);
                    session('backup_config', null);
                    return success(1);
                }
            } else {
                $tab = array (
                    'id' => $id,
                    'table' => $tables[ $id ],
                    'start' => $start[ 0 ]
                );
                $rate = floor(100 * ( $start[ 0 ] / $start[ 1 ] ));
                $data = array ();
                $data[ 'status' ] = 1;
                $data[ 'rate' ] = $rate;
                $data[ 'message' ] = "正在备份...({$rate}%)";
                $data[ 'tab' ] = $tab;
                return $data;
            }
        } else { // 出错
            return error(-1, '参数有误!');
        }
    }

    /**
     * 删除备份文件
     */
    public function deleteData()
    {
        $name_time = input('time', '');
        if ($name_time) {
            $database = new Database();
            $name = date('Ymd-His', $name_time) . '-*.sql*';
            $path = realpath($database->backup_path) . DIRECTORY_SEPARATOR . $name;
            array_map("unlink", glob($path));
            if (count(glob($path))) {
                return error(-1, "备份文件删除失败，请检查权限！");
            } else {
                return success(1, "备份文件删除成功！");
            }
        } else {
            return error(-1, "参数有误！");
        }
    }

    /**
     * 刷新菜单 测试
     */
    public function refresh()
    {
        $menu = new Menu();
        $res = $menu->refreshAllMenu();
        var_dump($res);
    }

    /**
     * 刷新自定义组件
     */
    public function refreshDiy()
    {
        $addon = new Addon();
        $addon_list = $addon->getAddonList([], 'name')[ 'data' ];
        $res = [];
        $res[] = $addon->refreshDiyView('');

        foreach ($addon_list as $k => $v) {
            $res[] = $addon->refreshDiyView($v[ 'name' ]);
        }

        // 处理升级版本数据遇到的数据问题
        $this->handleVersionData();
        return $res;
    }

    /**
     * 处理升级版本数据遇到的数据问题
     */
    public function handleVersionData()
    {
        $msg = '处理成功';
        try {

            model('site_diy_view')->startTrans();

            // 处理微页面数据、图标显示问题
            $page = model('site_diy_view')->getList([], 'id,value');
            foreach ($page as $k => $v) {
                if (!empty($v[ 'value' ])) {
                    $value = json_decode($v[ 'value' ], true);

                    foreach ($value[ 'value' ] as $ck => $cv) {

                        if ($cv[ 'componentName' ] == 'Search') {

                            // 搜索框组件，v5.1.7新增
                            if (!isset($cv[ 'searchLink' ])) {
                                $value[ 'value' ][ $ck ][ 'searchLink' ] = [
                                    "name" => ""
                                ];
                            }

                        } elseif ($cv[ 'componentName' ] == 'GoodsList') {

                            // 商品列表组件

                        } elseif ($cv[ 'componentName' ] == 'ManyGoodsList') {

                            // 多商品组组件

                        } elseif ($cv[ 'componentName' ] == 'GoodsRecommend') {

                            // 商品推荐组件

                        } elseif ($cv[ 'componentName' ] == 'Seckill') {

                            // 秒杀组件

                        } elseif ($cv[ 'componentName' ] == 'Notice') {

                            // 公告组件，v5.1.7新增
                            if (!empty($cv[ 'list' ])) {
                                foreach ($cv[ 'list' ] as $notice_k => $notice_v) {
                                    $cv[ 'list' ][ $notice_k ][ 'id' ] = unique_random(12) . $notice_k;
                                }
                                $value[ 'value' ][ $ck ][ 'list' ] = $cv[ 'list' ];
                            }

                        } elseif ($cv[ 'componentName' ] == 'ImageAds') {

                            // 图片广告组件，v5.1.6新增
                            if (!isset($cv[ 'indicatorIsShow' ])) {
                                $value[ 'value' ][ $ck ][ 'indicatorIsShow' ] = true;
                            }

                        } elseif ($cv[ 'componentName' ] == 'MemberMyOrder') {

                            // 会员中心 我的订单组件，v5.1.7新增
                            if ($cv[ 'style' ] == 4) {
                                $value[ 'value' ][ $ck ][ 'icon' ] = [
                                    "waitPay" => [
                                        "title" => "待支付",
                                        "icon" => "icondiy icon-system-daizhifu",
                                        "style" => [
                                            "bgRadius" => 0,
                                            "fontSize" => 90,
                                            "iconBgColor" => [],
                                            "iconBgColorDeg" => 0,
                                            "iconBgImg" => "",
                                            "iconColor" => [ "#20DA86", "#03B352" ],
                                            "iconColorDeg" => 0
                                        ]
                                    ],
                                    "waitSend" => [
                                        "title" => "备货中",
                                        "icon" => "icondiy icon-system-beihuozhong",
                                        "style" => [
                                            "bgRadius" => 0,
                                            "fontSize" => 90,
                                            "iconBgColor" => [],
                                            "iconBgColorDeg" => 0,
                                            "iconBgImg" => "",
                                            "iconColor" => [ "#20DA86", "#03B352" ],
                                            "iconColorDeg" => 0
                                        ]
                                    ],
                                    "waitConfirm" => [
                                        "title" => "配送中",
                                        "icon" => "icondiy icon-system-paisongzhong",
                                        "style" => [
                                            "bgRadius" => 0,
                                            "fontSize" => 90,
                                            "iconBgColor" => [],
                                            "iconBgColorDeg" => 0,
                                            "iconBgImg" => "",
                                            "iconColor" => [ "#20DA86", "#03B352" ],
                                            "iconColorDeg" => 0
                                        ]
                                    ],
                                    "waitUse" => [
                                        "title" => "待评价",
                                        "icon" => "icondiy icon-system-daishiyong2",
                                        "style" => [
                                            "bgRadius" => 0,
                                            "fontSize" => 90,
                                            "iconBgColor" => [],
                                            "iconBgColorDeg" => 0,
                                            "iconBgImg" => "",
                                            "iconColor" => [ "#20DA86", "#03B352" ],
                                            "iconColorDeg" => 0
                                        ]
                                    ],
                                    "refunding" => [
                                        "title" => "退换货",
                                        "icon" => "icondiy icon-system-tuihuoguanli",
                                        "style" => [
                                            "bgRadius" => 0,
                                            "fontSize" => 90,
                                            "iconBgColor" => [],
                                            "iconBgColorDeg" => 0,
                                            "iconBgImg" => "",
                                            "iconColor" => [ "#20DA86", "#03B352" ],
                                            "iconColorDeg" => 0
                                        ]
                                    ]
                                ];
                            }

                        } elseif ($cv[ 'componentName' ] == 'FloatBtn') {

                            // 浮动按钮 组件，v5.1.7新增
                            if (!isset($cv[ 'imageSize' ])) {
                                $value[ 'value' ][ $ck ][ 'imageSize' ] = 40;
                            }

                        } elseif ($cv[ 'componentName' ] == 'TopCategory') {

                            // 分类导航 组件，v5.1.7新增
                            if (!isset($cv[ 'moreColor' ])) {
                                $value[ 'value' ][ $ck ][ 'moreColor' ] = '#333333';
                            }

                        }

                    }

                    model('site_diy_view')->update([ 'value' => json_encode($value) ], [ [ 'id', '=', $v[ 'id' ] ] ]);
                }
            }

            model('site_diy_view')->commit();
            Cache::clear();
        } catch (\Exception $e) {
            model('site_diy_view')->rollback();
            $msg = 'File：' . $e->getFile() . '，Line：' . $e->getLine() . '，Message：' . $e->getMessage() . ',Code：' . $e->getCode();
        }
        return $msg;
    }

    /**
     * 刷新收银端权限
     * @return array
     */
    public function refreshCashierAuth()
    {
        $res = ( new Addon() )->refreshAddonCashierAuth();
        dump($res);
    }
}