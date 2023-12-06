<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\goods;

use app\model\BaseModel;
use think\facade\Db;

/**
 * 商品分类
 */
class GoodsCategory extends BaseModel
{

    /**
     * 添加商品分类
     * @param $data
     * @return \multitype
     */
    public function addCategory($data)
    {
        $site_id = isset($data[ 'site_id' ]) ? $data[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $category_id = model('goods_category')->add($data);
        $common_data = array (
            'category_id_1' => 0,
            'category_id_2' => 0,
            'category_id_3' => 0,
        );
        switch ( $data[ 'level' ] ) {
            case 1:
                $common_data[ 'category_id_1' ] = $category_id;
                break;
            case 2:
                $common_data[ 'category_id_1' ] = $data[ 'pid' ];//这让我并没有验证合法性,业务发生变动之后要注意(author:周)
                $common_data[ 'category_id_2' ] = $category_id;
                break;
            case 3:
                //错误数据纠正
                $parent_category_info = model('goods_category')->getInfo([ [ 'category_id', '=', $data[ 'pid' ] ], [ 'site_id', '=', $site_id ] ], 'pid');//这让我并没有验证合法性,业务发生变动之后要注意(author:周)
                $common_data[ 'category_id_1' ] = $parent_category_info[ 'pid' ];//这让我并没有验证合法性,业务发生变动之后要注意(周)
                $common_data[ 'category_id_2' ] = $data[ 'pid' ];
                $common_data[ 'category_id_3' ] = $category_id;

                break;
        }
        $data = array_merge($data, $common_data);

        model('goods_category')->update([ 'category_id_' . $data[ 'level' ] => $category_id ], [ [ 'category_id', '=', $category_id ] ]);
        return $this->success($category_id);
    }

    /**
     * 验证分类是否可以修改
     */
    public function checkEditCategory($data)
    {
        $category_id = $data[ 'category_id' ];
        $pid = $data[ 'pid' ] ?? 0;
        $parent_category_info = model('goods_category')->getInfo([ [ 'category_id', '=', $pid ], [ 'site_id', '=', $data[ 'site_id' ] ] ]) ?? [];
        if (empty($parent_category_info)) return $this->success();
        $category_info = model('goods_category')->getInfo([ [ 'category_id', '=', $category_id ], [ 'site_id', '=', $data[ 'site_id' ] ] ]) ?? [];
        if ($parent_category_info[ 'category_id' ] == $category_info[ 'category_id' ]) {
            return $this->error('', '不能修改上级为自己');
        }
        if ($category_info[ 'level' ] < 3 && $parent_category_info[ 'level' ] == 2) {
            $child_list = model('goods_category')->getCount([ [ 'pid', '=', $category_info[ 'category_id' ] ] ], 'category_id');
            if ($child_list > 0) return $this->error('', '当前等级存在下级，不可修改为该上级');
        }
        if ($category_info[ 'level' ] == 1 && $parent_category_info[ 'level' ] == 1) {
            $child_list = model('goods_category')->getColumn([ [ 'pid', '=', $category_info[ 'category_id' ] ] ], 'category_id');

            if ($child_list) {
                $child_child_list = model('goods_category')->getCount([ [ 'pid', 'in', $child_list ] ], 'category_id');
                if ($child_child_list > 0) return $this->error('', '当前等级存在下下级，不可修改为该上级');
            }
        }

        return $this->success();
    }

    /**
     * 修改商品分类
     * @param $data
     * @return \multitype
     */
    public function editCategory($data)
    {
        $site_id = isset($data[ 'site_id' ]) ? $data[ 'site_id' ] : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $check_res = $this->checkEditCategory($data);
        if ($check_res[ 'code' ] < 0) return $check_res;
        model('goods_category')->startTrans();
        try {
            //仅限当分类不支持跨级别修改
            $pid = $data[ 'pid' ];
            $level = 1;
            $parent_category_info = model('goods_category')->getInfo([ [ 'category_id', '=', $data[ 'pid' ] ], [ 'site_id', '=', $site_id ] ]);
            if ($parent_category_info) $level = (int) $parent_category_info[ 'level' ] + 1;
            $data[ 'level' ] = $level;

            //获取该分类信息
            $info = model('goods_category')->getInfo([ [ 'category_id', '=', $data[ 'category_id' ] ], [ 'site_id', '=', $site_id ] ]);
            $common_data = array (
                'category_id_1' => 0,
                'category_id_2' => 0,
                'category_id_3' => 0,
            );
            switch ( $level ) {
                case 1:
                    $common_data[ 'category_id_1' ] = $info[ 'category_id' ];
                    break;
                case 2:
                    $common_data[ 'category_id_1' ] = $data[ 'pid' ];
                    $common_data[ 'category_id_2' ] = $info[ 'category_id' ];
                    //二三级要同步改动
                    model('goods_category')->update($common_data, [ [ 'pid', '=', $info[ 'category_id' ] ] ]);

                    model('goods_category')->update([ 'category_id_3' => Db::raw('category_id') ], [ [ 'pid', '=', $info[ 'category_id' ] ], [ 'level', '=', '3' ] ]);

                    break;
                case 3:
                    //错误数据纠正
                    $parent_category_info = model('goods_category')->getInfo([ [ 'category_id', '=', $data[ 'pid' ] ], [ 'site_id', '=', $site_id ] ], 'pid');//这让我并没有验证合法性,业务发生变动之后要注意(author:周)
                    $common_data[ 'category_id_1' ] = $parent_category_info[ 'pid' ];//这让我并没有验证合法性,业务发生变动之后要注意(周)
                    $common_data[ 'category_id_2' ] = $data[ 'pid' ];
                    $common_data[ 'category_id_3' ] = $info[ 'category_id' ];

                    break;
            }
            $info = array_merge($info, $common_data);
            $data = array_merge($data, $common_data);

            if ($data[ 'is_show' ] == -1) {

                switch ( $level ) {
                    case 1:
                        model('goods_category')->update([ 'is_show' => -1 ], [ [ 'category_id_1', '=', $info[ 'category_id_1' ] ] ]);
                        $data[ 'category_full_name' ] = $data[ 'category_name' ];
                        break;

                    case 2:
                        model('goods_category')->update([ 'is_show' => -1 ], [ [ 'category_id_2', '=', $info[ 'category_id_2' ] ] ]);

                        $info_1 = model('goods_category')->getInfo([ [ 'category_id', '=', $data[ 'category_id_1' ] ], [ 'site_id', '=', $site_id ] ], 'category_id_1,category_id_2,category_id_3,level,category_name');
                        $category_full_name = $info_1[ 'category_name' ] . '/' . $data[ 'category_name' ];
                        $data[ 'category_full_name' ] = $category_full_name;
                        break;
                    case 3:
//                        model('goods_category')->update(['is_show' => -1], [['category_id', 'in', [$info['category_id_1'], $info['category_id_2']]]]);

                        $info_1 = model('goods_category')->getInfo([ [ 'category_id', '=', $data[ 'category_id_1' ] ], [ 'site_id', '=', $site_id ] ], 'category_id_1,category_id_2,category_id_3,level,category_name');
                        $info_2 = model('goods_category')->getInfo([ [ 'category_id', '=', $data[ 'category_id_2' ] ], [ 'site_id', '=', $site_id ] ], 'category_id_1,category_id_2,category_id_3,level,category_name');
                        $category_full_name = $info_1[ 'category_name' ] . '/' . $info_2[ 'category_name' ] . '/' . $data[ 'category_name' ];
                        $data[ 'category_full_name' ] = $category_full_name;
                        break;
                }
            } else {
                switch ( $level ) {
                    case 1:
                        model('goods_category')->update([ 'is_show' => 0 ], [ [ 'category_id_1', '=', $info[ 'category_id_1' ] ] ]);

                        $data[ 'category_full_name' ] = $data[ 'category_name' ];
                        break;
                    case 2:
                        model('goods_category')->update([ 'is_show' => 0 ], [ [ 'category_id', '=', $info[ 'category_id_1' ] ] ]);

                        $info_1 = model('goods_category')->getInfo([ [ 'category_id', '=', $data[ 'category_id_1' ] ], [ 'site_id', '=', $site_id ] ], 'category_id_1,category_id_2,category_id_3,level,category_name');

                        $category_full_name = $info_1[ 'category_name' ] . '/' . $data[ 'category_name' ];
                        $data[ 'category_full_name' ] = $category_full_name;
                        break;
                    case 3:
//                        model('goods_category')->update(['is_show' => 0], [['category_id', 'in', [$info['category_id_1'], $info['category_id_2']]]]);

                        $info_1 = model('goods_category')->getInfo([ [ 'category_id', '=', $data[ 'category_id_1' ] ], [ 'site_id', '=', $site_id ] ], 'category_id_1,category_id_2,category_id_3,level,category_name');
                        $info_2 = model('goods_category')->getInfo([ [ 'category_id', '=', $data[ 'category_id_2' ] ], [ 'site_id', '=', $site_id ] ], 'category_id_1,category_id_2,category_id_3,level,category_name');
                        $category_full_name = $info_1[ 'category_name' ] . '/' . $info_2[ 'category_name' ] . '/' . $data[ 'category_name' ];
                        $data[ 'category_full_name' ] = $category_full_name;
                        break;
                }
            }

            if ($info[ 'pid' ] != $data[ 'pid' ]) {
                if ($level == 2) {
                    model('goods')->update([
                        'category_json' => Db::raw("REPLACE(category_json, '[\"{$info['pid']},{$data[ 'category_id' ]},', '[\"{$data['pid']},{$data[ 'category_id' ]},')")
                    ], [ [ 'category_id', 'like', "%,{$info['pid']},{$data[ 'category_id' ]},%" ], [ 'site_id', '=', $site_id ] ]);

                    model('goods')->update([
                        'category_json' => Db::raw("REPLACE(category_json, '[\"{$info['pid']},{$data[ 'category_id' ]}\"]', '[\"{$data['pid']},{$data[ 'category_id' ]}\"]')")
                    ], [ [ 'category_id', 'like', "%,{$info['pid']},{$data[ 'category_id' ]},%" ], [ 'site_id', '=', $site_id ] ]);

                    model('goods')->update([
                        'category_id' => Db::raw("REPLACE(category_id, ',{$info['pid']},{$data[ 'category_id' ]},', ',{$data['pid']},{$data[ 'category_id' ]},')")
                    ], [ [ 'category_id', 'like', "%,{$info['pid']},{$data[ 'category_id' ]},%" ], [ 'site_id', '=', $site_id ] ]);
                } else {
                    model('goods')->update([
                        'category_json' => Db::raw("REPLACE(category_json, '[\"{$info['category_id_1']},{$info['category_id_2']},{$info['category_id_3']}\"]', '[\"{$data['category_id_1']},{$data['category_id_2']},{$data['category_id_3']}\"]')")
                    ], [ [ 'category_id', 'like', "%,{$info['pid']},{$data[ 'category_id' ]},%" ], [ 'site_id', '=', $site_id ] ]);

                    model('goods')->update([
                        'category_id' => Db::raw("REPLACE(category_id, ',{$info['category_id_1']},{$info[ 'category_id_2' ]},{$info[ 'category_id_3' ]},', ',{$data['category_id_1']},{$data[ 'category_id_2' ]},{$data[ 'category_id_3' ]},')")
                    ], [ [ 'category_id', 'like', "%,{$info['pid']},{$data[ 'category_id' ]},%" ], [ 'site_id', '=', $site_id ] ]);
                }

            }

            $res = model('goods_category')->update($data, [ [ 'category_id', '=', $data[ 'category_id' ] ], [ 'site_id', '=', $site_id ] ]);

            //变更下级等级层级
            $child_list = model('goods_category')->getColumn([ [ 'site_id', '=', $site_id ], [ 'pid', '=', $data[ 'category_id' ] ] ], 'category_id');
            if ($child_list) {
                model('goods_category')->update([ 'level' => (int) $data[ 'level' ] + 1 ], [ [ 'category_id', 'in', $child_list ] ]);
                $child_child_list = model('goods_category')->getColumn([ [ 'site_id', '=', $site_id ], [ 'pid', 'in', $child_list ] ], 'category_id');
                model('goods_category')->update([ 'level' => (int) $data[ 'level' ] + 2 ], [ [ 'category_id', 'in', $child_child_list ] ]);
            }

            model('goods_category')->commit();
            return $this->success($res);

        } catch (\Exception $e) {
            model('goods_category')->rollback();
            return $this->error('', $e->getMessage() . $e->getFile() . $e->getLine());
        }

    }

    /**
     * 删除分类
     * @param $category_id
     * @return \multitype
     */
    public function deleteCategory($category_id, $site_id)
    {
        $site_id = isset($site_id) ? $site_id : '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        //判断该分类下是否存在商品
        /*   $goods_count = model('goods')->getCount([ [ 'category_id', 'like', '%,' . $category_id . ',%' ], [ 'site_id', '=', $site_id ] ]);
           if ($goods_count > 0) {
               return $this->error('', '该分类下存在商品，暂不能删除');
           }*/

        $goods_category_info = $this->getCategoryInfo([
            [ 'category_id', '=', $category_id ], [ 'site_id', '=', $site_id ]
        ], "level")[ 'data' ];
        $field = "category_id_" . $goods_category_info[ 'level' ];

        $res = model('goods_category')->delete([ [ $field, '=', $category_id ], [ 'site_id', '=', $site_id ] ]);
        model('goods_category')->delete([ [ 'category_id', '=', $category_id ], [ 'site_id', '=', $site_id ] ]);
        return $this->success($res);
    }

    /**
     * 获取商品分类信息
     * @param array $condition
     * @param string $field
     */
    public function getCategoryInfo($condition, $field = 'category_id,category_name,short_name,pid,level,is_recommend,is_show,sort,image,keywords,description,attr_class_id,attr_class_name,category_id_1,category_id_2,category_id_3,category_full_name,commission_rate,image_adv,link_url,icon')
    {
        $res = model('goods_category')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取商品分类列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return \multitype
     */
    public function getCategoryList($condition = [], $field = 'category_id,category_name,short_name,pid,level,is_recommend,is_show,sort,image,attr_class_id,attr_class_name,category_id_1,category_id_2,category_id_3,commission_rate,image_adv,icon', $order = '', $limit = null)
    {
        $list = model('goods_category')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取商品分类树结构
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return \multitype
     */
    public function getCategoryTree($condition = [], $field = 'category_id,category_name,short_name,pid,level,is_recommend,is_show,sort,image,attr_class_name,category_id_1,category_id_2,category_id_3,commission_rate,icon', $order = 'sort asc,category_id desc', $limit = null)
    {
        $list = model('goods_category')->getList($condition, $field, $order, '', '', '', $limit);
        $goods_category_list = [];
        //遍历一级商品分类
        foreach ($list as $k => $v) {
            if ($v[ 'level' ] == 1) {
                $goods_category_list[] = $v;
                unset($list[ $k ]);
            }
        }

        $list = array_values($list);

        //遍历二级商品分类
        foreach ($list as $k => $v) {
            foreach ($goods_category_list as $ck => $cv) {
                if ($v[ 'level' ] == 2 && $cv[ 'category_id' ] == $v[ 'pid' ]) {
                    $goods_category_list[ $ck ][ 'child_list' ][] = $v;
                    unset($list[ $k ]);
                }
            }
        }

        $list = array_values($list);

        //遍历三级商品分类
        foreach ($list as $k => $v) {
            foreach ($goods_category_list as $ck => $cv) {

                if (!empty($cv[ 'child_list' ])) {
                    foreach ($cv[ 'child_list' ] as $third_k => $third_v) {

                        if ($v[ 'level' ] == 3 && $third_v[ 'category_id' ] == $v[ 'pid' ]) {
                            $goods_category_list[ $ck ][ 'child_list' ][ $third_k ][ 'child_list' ][] = $v;
                            unset($list[ $k ]);
                        }
                    }
                }
            }
        }

        return $this->success($goods_category_list);
    }

    /**
     * 获取商品分类分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return \multitype
     */
    public function getCategoryPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = 'category_id,category_name,short_name,pid,level,is_recommend,is_show,sort,image,category_id_1,category_id_2,category_id_3,category_full_name,commission_rate,icon')
    {
        $list = model('goods_category')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取商品分类列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return \multitype
     */
    public function getCategoryByParent($condition = [], $field = 'category_id,category_name,short_name,pid,level,is_recommend,is_show,sort,image,attr_class_id,attr_class_name,category_id_1,category_id_2,category_id_3,commission_rate,icon', $order = 'sort asc,category_id desc', $limit = null)
    {
        $list = model('goods_category')->getList($condition, $field, $order, '', '', '', $limit);
        foreach ($list as $k => $v) {
            $child_count = model('goods_category')->getCount([ 'pid' => $v[ 'category_id' ] ]);
            $list[ $k ][ 'child_count' ] = $child_count;
        }

        return $this->success($list);
    }

    /**
     * 修改排序
     * @param int $sort
     * @param int $category_id
     */
    public function modifyGoodsCategorySort($sort, $category_id, $site_id)
    {
        $res = model('goods_category')->update([ 'sort' => $sort ], [ [ 'category_id', '=', $category_id ], [ 'site_id', '=', $site_id ] ]);
        return $this->success($res);
    }

}