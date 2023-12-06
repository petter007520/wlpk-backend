<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\system;

use app\model\BaseModel;
use app\model\web\Config;

class H5 extends BaseModel
{
    private $h5_domain = __ROOT__ . '/h5';

    public function refresh()
    {
        try {
            $h5_template_path = 'public/h5/default'; // h5模板文件目录
            $h5_path = 'h5'; // h5端生成目录
            if (!is_dir($h5_template_path) || count(scandir($h5_template_path)) <= 2) {
                return $this->error('', '未查找到h5模板');
            }

            if (is_dir($h5_path)) {
                // 先将之前的文件删除
                if (count(scandir($h5_path)) > 2) deleteDir($h5_path);
            } else {
                // 创建H5目录
                mkdir($h5_path, intval('0777', 8), true);
            }
            $this->copyFile($h5_template_path, $h5_path);
            file_put_contents($h5_path . '/refresh.log', time());
            return $this->success();
        } catch ( \Exception $e ) {
            return $this->error('', $e->getMessage() . $e->getLine());
        }
    }

    /**
     * 独立部署下载
     * @return array
     */
    function downloadH5Separate($domain_name)
    {
        $this->h5_domain = $domain_name;

        $h5_template_path = 'public/h5/separate'; // h5模板文件目录
        if (!is_dir($h5_template_path) || count(scandir($h5_template_path)) <= 2) {
            return $this->error('', '未查找到h5模板');
        }

        $source_file_path = 'upload/temp/h5';
        if (is_dir($source_file_path)) {
            // 先将之前的文件删除
            if (count(scandir($source_file_path)) > 2) deleteDir($source_file_path);
        } else {
            // 创建H5目录
            mkdir($source_file_path, intval('0777', 8), true);
        }
        $this->copyFile($h5_template_path, $source_file_path);
        $file_arr = getFileMap($source_file_path);

        if (!empty($file_arr)) {
            $zipname = 'h5_' . date('Ymd') . '.zip';

            $zip = new \ZipArchive();
            $res = $zip->open($zipname, \ZipArchive::CREATE);
            if ($res === TRUE) {
                foreach ($file_arr as $file_path => $file_name) {
                    if (is_dir($file_path)) {
                        $file_path = str_replace($source_file_path . '/', '', $file_path);
                        $zip->addEmptyDir($file_path);
                    } else {
                        $zip_path = str_replace($source_file_path . '/', '', $file_path);
                        $zip->addFile($file_path, $zip_path);
                    }
                }
                $zip->close();

                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length: " . filesize($zipname));
                header("Content-Disposition: attachment; filename=\"" . basename($zipname) . "\"");
                readfile($zipname);
                @unlink($zipname);
                deleteDir($source_file_path);
            }
        }
    }

    private function copyFile($source_path, $to_path = '')
    {
        $files = scandir($source_path);
        foreach ($files as $path) {
            if ($path != '.' && $path != '..') {
                $temp_path = $source_path . '/' . $path;
                if (is_dir($temp_path)) {
                    mkdir($to_path . '/' . $path, intval('0777', 8), true);
                    $this->copyFile($temp_path, $to_path . '/' . $path);
                } else {
                    if (file_exists($temp_path)) {
                        if (preg_match("/(index.)(\w{8})(.js)$/", $temp_path)) {
                            $content = file_get_contents($temp_path);
                            $content = $this->paramReplace($content);
                            file_put_contents($to_path . '/' . $path, $content);
                        } else {
                            copy($temp_path, $to_path . '/' . $path);
                        }
                    }
                }
            }
        }
    }

    /**
     * 参数替换
     * @param $site_id
     * @param $string
     * @return null|string|string[]
     */
    private function paramReplace($string)
    {
        $api_model = new Api();
        $api_config = $api_model->getApiConfig()['data'];

        $web_config_model = new Config();
        $web_config = $web_config_model->getMapConfig();
        $web_config = $web_config['data']['value'];

        $socket_url = (strstr(__ROOT__, 'https://') === false ? str_replace('http', 'ws', __ROOT__) : str_replace('https', 'wss', __ROOT__)) . '/wss';

        $patterns = [
            '/\{\{\$baseUrl\}\}/',
            '/\{\{\$imgDomain\}\}/',
            '/\{\{\$h5Domain\}\}/',
            '/\{\{\$mpKey\}\}/',
            '/\{\{\$apiSecurity\}\}/',
            '/\{\{\$publicKey\}\}/',
            '/\{\{\$webSocket\}\}/'
        ];
        $replacements = [
            __ROOT__,
            __ROOT__,
            $this->h5_domain,
            $web_config['tencent_map_key'] ?? '',
            $api_config['is_use'] ?? 0,
            $api_config['value']['public_key'] ?? '',
            $socket_url
        ];
        $string = preg_replace($patterns, $replacements, $string);
        return $string;
    }

}