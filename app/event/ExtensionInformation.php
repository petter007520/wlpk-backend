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

/**
 * 平台推广营销类展示
 */
class ExtensionInformation
{

    /**
     * 活动展示
     * @param $params
     * @return array
     */
    public function handle($params)
    {
        $solitaire = [
            "h5" => [
                'path' => "",
                'url' => ""
            ],
            "weapp" => [
                'path' => "",
            ]
        ];
        $h5_qrcode = event('Qrcode', [
            'site_id' => $params[ 'site_id' ],
            'app_type' => 'h5',
            'type' => 'create',
            'data' => $params[ 'data' ],
            'page' => $params[ 'page' ],
            'qrcode_path' => $params[ 'qrcode_path' ],
            'qrcode_name' => $params[ 'qrcode_name' ][ 'h5_name' ],
        ], true);
        if ($h5_qrcode[ 'code' ] >= 0) {
            $solitaire[ 'h5' ][ 'path' ] = img($h5_qrcode[ 'data' ][ 'path' ]);
            $wap_domain = getH5Domain();
            $solitaire[ 'h5' ][ 'url' ] = $wap_domain . $params[ 'h5_path' ];
        }
        $weapp_qrcode = event('Qrcode', [
            'site_id' => $params[ 'site_id' ],
            'app_type' => 'weapp',
            'type' => 'create',
            'data' => $params[ 'data' ],
            'page' => $params[ 'page' ],
            'qrcode_path' => $params[ 'qrcode_path' ],
            'qrcode_name' => $params[ 'qrcode_name' ][ 'weapp_name' ],
        ], true);
        if ($weapp_qrcode[ 'code' ] >= 0) {
            $solitaire[ 'weapp' ][ 'path' ] = img($weapp_qrcode[ 'data' ][ 'path' ]);
        }
        return $solitaire;
    }
}