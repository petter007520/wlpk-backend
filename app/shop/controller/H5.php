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

use app\model\system\H5 as H5Model;
use app\model\web\Config;
use app\model\system\Upgrade;
use app\model\web\DiyView as DiyViewModel;

class H5 extends BaseShop
{
    /**
     * 刷新前端代码
     */
    public function refreshH5()
    {
        if (request()->isAjax()) {
            $h5 = new H5Model();
            $res = $h5->refresh();
            $this->h5DomainName(); // 修改H5域名
            return $res;
        } else {
            $refresh_time = 0;
            if (file_exists('h5/refresh.log')) {
                $refresh_time = file_get_contents('h5/refresh.log');
            }
            $config_model = new Config();
            $config = $config_model->geth5DomainName($this->site_id);
            $this->assign('config', $config[ 'data' ][ 'value' ]);

            $this->assign('refresh_time', $refresh_time);
            $this->assign("root_url", __ROOT__);

            // 检测授权
            $upgrade_model = new Upgrade();
            $auth_info = $upgrade_model->authInfo();

            $this->assign('is_auth', ( $auth_info[ 'code' ] == 0 ));
            return $this->fetch('h5/refresh_h5');
        }
    }

    /**
     * h5域名配置
     */
    public function h5DomainName()
    {
        $config_model = new Config();
        $domain_name = input("domain", "");
        $deploy_way = input("deploy_way", "default");

        if ($deploy_way == 'default') {
            $domain_name = __ROOT__ . '/h5';
        }

        $result = $config_model->seth5DomainName([
            'domain_name_h5' => $domain_name,
            'deploy_way' => $deploy_way
        ]);
        return $result;
    }

    /**
     * 独立部署版下载
     */
    public function downloadSeparate()
    {
        if (strstr(ROOT_URL, 'niuteam.cn') === false) {
            $domain_name = input("domain", "");
            $h5 = new H5Model();
            $res = $h5->downloadH5Separate($domain_name);
            if (isset($res[ 'code' ]) && $res[ 'code' ] != 0) {
                $this->error($res[ 'message' ]);
            } else {
                $config_model = new Config();
                $config_model->seth5DomainName([
                    'domain_name_h5' => $domain_name,
                    'deploy_way' => 'separate'
                ]);
            }
        }
    }

    /**
     * 下载uniapp源码，混入所选模板代码
     * @return array|bool|int|mixed|void
     */
    public function downloadUniapp()
    {
        if (strstr(ROOT_URL, 'niuteam.cn') === false) {
            $app_info = config('info');

            $upgrade_model = new Upgrade();
            $res = $upgrade_model->downloadUniapp($app_info[ 'version_no' ]);

            if ($res[ 'code' ] == 0) {
                $filename = "upload/{$app_info['version_no']}_uniapp.zip";
                $res = file_put_contents($filename, base64_decode($res[ 'data' ]));

                $zip = new \ZipArchive();
                $zip->open($filename, \ZipArchive::CREATE);
                $zip->extractTo('upload/temp/standard_uniapp'); // 将压缩包解压到指定目录
                $zip->close();

                $diy_view = new DiyViewModel();
                // 混入当前所选模板的代码，进行编译
                $diy_view->compileUniApp([
                    'site_id' => $this->site_id
                ]);

                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length: " . filesize($filename));
                header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\"");
                readfile($filename);
                @unlink($filename);
            } else {
                $this->error($res[ 'message' ]);
            }
        }
    }
}
