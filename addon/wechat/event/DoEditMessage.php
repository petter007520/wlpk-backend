<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\system\Wechat\event;

use liliuwei\think\Jump;

class DoEditMessage
{
    use Jump;

    /**
     * 编辑消息模板
     * @param array $param
     */
    public function handle($param = [])
    {
        if ($param["name"] == "Wechat") {
            $this->redirect(addon_url('Wechat://sitehome/message/edit', ['keyword' => $param['keyword']]));
        }
    }
}