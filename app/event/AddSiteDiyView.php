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

use app\model\diy\Template;

/**
 * 增加默认自定义数据：网站主页、商品分类、底部导航
 */
class AddSiteDiyView
{

    public function handle($param)
    {
        if (!empty($param[ 'site_id' ])) {
            $diy_template = new Template();
            // 查询一条模板组
            $template_goods_info = $diy_template->getFirstTemplateGoods([], 'goods_id', 'goods_id asc')[ 'data' ];
            if (!empty($template_goods_info)) {
                $res = $diy_template->useTemplate([
                    'site_id' => $param[ 'site_id' ],
                    'goods_id' => $template_goods_info[ 'goods_id' ],
                ]);
                return $res;
            }
        }
    }

}