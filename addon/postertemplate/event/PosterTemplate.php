<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */


namespace addon\postertemplate\event;

use addon\postertemplate\model\PosterTemplate as PosterTemplateModel;

/**
 * 活动展示
 */
class PosterTemplate
{

    /**
     * 活动展示
     * 
     * @return
     */
	public function handle($params)
	{
        $poster_template_model = new PosterTemplateModel();
        $poster_list = $poster_template_model ->getPosterTemplateList([['site_id', '=', $params['site_id']],['template_status','=',1]],'template_id,poster_name,site_id');
	    return $poster_list;
	}
}