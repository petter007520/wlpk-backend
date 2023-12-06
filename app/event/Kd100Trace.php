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

use app\model\express\Kd100;

/**
 * 初始化配置信息
 * @author Administrator
 *
 */
class Kd100Trace
{
    public function handle($data)
    {
        $express_no_data = $data["express_no_data"];

        $kd100_model   = new Kd100();
        $config_result = $kd100_model->getKd100Config($express_no_data["site_id"]);
        $config        = $config_result["data"];

        if ($config["is_use"]) {
            $express_no = $express_no_data["express_no_kd100"];
            $result     = $kd100_model->trace($data["code"], $express_no, $express_no_data["site_id"]);
            return $result;
        }
    }


}
