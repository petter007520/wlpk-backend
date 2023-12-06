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

namespace app\model;

use think\facade\Db;
use think\Validate;
use think\facade\Cache;

/**
 * 模型基类
 */
class Model
{

    // 查询对象
    private static $query_obj = null;

    protected $table = '';
    //验证规则
    protected $rule = [];
    //验证信息
    protected $message = [];
    //验证场景
    protected $scene = [];
    //错误信息
    protected $error;

    protected $is_cache = 1;

    public function __construct($table = '')
    {
        if ($table) {
            $this->table = $table;
        }
        $this->is_cache = $this->isCache();

    }

    public function isCache()
    {

        return 1;
    }

    /**
     * 获取列表数据
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @param array $join
     * @param string $group
     * @param string $limit
     * @param string $data
     * @return mixed
     */
    final public function getList($condition = [], $field = true, $order = '', $alias = 'a', $join = [], $group = '', $limit = null)
    {
        if ($this->is_cache && empty($join)) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        self::$query_obj = Db::name($this->table)->where($condition)->order($order);

        if (!empty($join)) {
            self::$query_obj->alias($alias);
            self::$query_obj = $this->parseJoin(self::$query_obj, $join);
        }

        if (!empty($group)) {
            self::$query_obj = self::$query_obj->group($group);
        }

        if (!empty($limit)) {
            self::$query_obj = self::$query_obj->limit($limit);
        }

        $result = self::$query_obj->field($field)->select()->toArray();

        self::$query_obj->removeOption();
        if ($this->is_cache && empty($join)) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $result);
        }

        return $result;
    }

    final public function all()
    {
        if ($this->is_cache) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_';
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        $result = Db::name($this->table)->select()->toArray();
        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $result);
        }

        return $result;
    }

    /**
     * 获取分页列表数据
     * @param unknown $where
     * @param string $field
     * @param string $order
     * @param number $page
     * @param string $list_rows
     * @param string $alias
     * @param unknown $join
     * @param string $group
     * @param string $limit
     */
    final public function pageList($condition = [], $field = true, $order = '', $page = 1, $list_rows = PAGE_LIST_ROWS, $alias = 'a', $join = [], $group = null, $limit = null)
    {
        //关联查询多表无法控制不缓存,单独业务处理
        if ($this->is_cache && empty($join)) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        self::$query_obj = Db::name($this->table)->alias($alias)->where($condition)->order($order);
        $count_obj = Db::name($this->table)->alias($alias)->where($condition)->order($order);
        if (!empty($join)) {

            $db_obj = self::$query_obj;
            self::$query_obj = $this->parseJoin($db_obj, $join);
            $count_obj = $this->parseJoin($count_obj, $join);
        }

        if (!empty($group)) {
            self::$query_obj = self::$query_obj->group($group);
            $count_obj = $count_obj->group($group);
        }

        if (!empty($limit)) {
            self::$query_obj = self::$query_obj->limit($limit);
        }

        $count = $count_obj->count();
        if ($list_rows == 0) {
            //查询全部
            $result_data = self::$query_obj->field($field)->limit($count)->page($page)->select()->toArray();
            $result[ 'page_count' ] = 1;
        } else {
            $result_data = self::$query_obj->field($field)->limit($list_rows)->page($page)->select()->toArray();
            $result[ 'page_count' ] = ceil($count / $list_rows);
        }
        $result[ 'count' ] = $count;
        $result[ 'list' ] = $result_data;


        self::$query_obj->removeOption();
        if ($this->is_cache && empty($join)) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $result);
        }

        return $result;
    }

    /**
     * 获取分页列表数据
     * @param unknown $where
     * @param string $field
     * @param string $order
     * @param number $page
     * @param string $list_rows
     * @param string $alias
     * @param unknown $join
     * @param string $group
     * @param string $limit
     */
    final public function rawPageList($condition = [], $field = true, $order = '', $page = 1, $list_rows = PAGE_LIST_ROWS, $alias = 'a', $join = [], $group = null, $limit = null)
    {
        //关联查询多表无法控制不缓存,单独业务处理
        if ($this->is_cache && empty($join)) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        if (is_array($order)) {
            self::$query_obj = Db::name($this->table)->alias($alias)->where($condition)->order($order);
            $count_obj = Db::name($this->table)->alias($alias)->where($condition)->order($order);
        } else {
            self::$query_obj = Db::name($this->table)->alias($alias)->where($condition)->orderRaw($order);
            $count_obj = Db::name($this->table)->alias($alias)->where($condition)->orderRaw($order);
        }

        if (!empty($join)) {
            $db_obj = self::$query_obj;
            self::$query_obj = $this->parseJoin($db_obj, $join);
            $count_obj = $this->parseJoin($count_obj, $join);
        }

        if (!empty($group)) {
            self::$query_obj = self::$query_obj->group($group);
            $count_obj = $count_obj->group($group);
        }

        if (!empty($limit)) {
            self::$query_obj = self::$query_obj->limit($limit);
        }

        $count = $count_obj->count();
        if ($list_rows == 0) {
            //查询全部
            $result_data = self::$query_obj->field($field)->limit($count)->page($page)->select()->toArray();
            $result[ 'page_count' ] = 1;
        } else {
            $result_data = self::$query_obj->field($field)->limit($list_rows)->page($page)->select()->toArray();
            $result[ 'page_count' ] = ceil($count / $list_rows);
        }
        $result[ 'count' ] = $count;
        $result[ 'list' ] = $result_data;


        self::$query_obj->removeOption();
        if ($this->is_cache && empty($join)) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $result);
        }

        return $result;
    }

    /**
     * 获取单条数据
     * @param array $where
     * @param string $field
     * @param string $join
     * @param string $data
     * @return mixed
     */
    final public function getInfo($where = [], $field = true, $alias = 'a', $join = null, $data = null)
    {
        //关联查询多表无法控制不缓存,单独业务处理
        if ($this->is_cache && empty($join)) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        if (empty($join)) {
            $result = Db::name($this->table)->where($where)->field($field)->find($data);
        } else {
            $db_obj = Db::name($this->table)->alias($alias);
            $db_obj = $this->parseJoin($db_obj, $join);
            $result = $db_obj->where($where)->field($field)->find($data);
        }
        if ($this->is_cache && empty($join)) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $result);
        }

        return $result;
    }

    /**
     * join分析
     * @access protected
     * @param array $join
     * @param array $options 查询条件
     * @return string
     */
    protected function parseJoin($db_obj, $join)
    {
        foreach ($join as $item) {
            list($table, $on, $type) = $item;
            $type = strtolower($type);
            switch ( $type ) {
                case "left":
                    $db_obj = $db_obj->leftJoin($table, $on);
                    break;
                case "inner":
                    $db_obj = $db_obj->join($table, $on);
                    break;
                case "right":
                    $db_obj = $db_obj->rightjoin($table, $on);
                    break;
                case "full":
                    $db_obj = $db_obj->fulljoin($table, $on);
                    break;
                default:
                    break;
            }
        }
        return $db_obj;
    }

    /**
     * /**
     * 获取某个列的数组
     * @param array $where 条件
     * @param string $field 字段名 多个字段用逗号分隔
     * @param string $key 索引
     * @return array
     */
    final public function getColumn($where = [], $field = '', $key = '')
    {
        if ($this->is_cache) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        $result = Db::name($this->table)->where($where)->column($field, $key);
        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $result);
        }

        return $result;
    }

    /**
     * 得到某个字段的值
     * @access public
     * @param array $where 条件
     * @param string $field 字段名
     * @param mixed $default 默认值
     * @param bool $force 强制转为数字类型
     * @return mixed
     */
    final public function getValue($where = [], $field = '', $default = null, $force = false)
    {
        if ($this->is_cache) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        $result = Db::name($this->table)->where($where)->value($field, $default, $force);
        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $result);
        }

        return $result;
    }

    /**
     * 新增数据
     * @param array $data 数据
     * @param boolean $is_return_pk 返回自增主键
     */
    final public function add($data = [], $is_return_pk = true)
    {


        $res = Db::name($this->table)->insert($data, true, $is_return_pk);
        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->clear();
        }
        return $res;
    }

    /**
     * 新增多条数据
     * @param array $data 数据
     * @param int $limit 限制插入行数
     */
    final public function addList($data = [], $limit = null)
    {


        $res =  Db::name($this->table)->insertAll($data, false, $limit);
        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->clear();
        }
        return $res;
    }

    /**
     * 更新数据
     * @param array $where 条件
     * @param array $data 数据
     */
    final public function update($data = [], $where = [])
    {

        $res = Db::name($this->table)->where($where)->update($data);

        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->clear();
        }
        return $res;

    }

    /**
     * 设置某个字段值
     * @param array $where 条件
     * @param string $field 字段
     * @param string $value 值
     */
    final public function setFieldValue($where = [], $field = '', $value = '')
    {

        $res = $this->update([ $field => $value ], $where);

        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->clear();
        }
        return $res;

    }

    /**
     * 设置数据列表
     * @param array $data_list 数据
     * @param boolean $replace 是否自动识别更新和写入
     */
    final public function setList($data_list = [], $replace = false)
    {


        $res = Db::name($this->table)->saveAll($data_list, $replace);
        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->clear();
        }
        return $res;
    }

    /**
     * 删除数据
     * @param array $where 条件
     */
    final public function delete($where = [])
    {

        $res = Db::name($this->table)->where($where)->delete();
        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->clear();
        }
        return $res;

    }

    /**
     * 统计数据
     * @param array $where 条件
     * @param string $type 查询类型  count:统计数量|max:获取最大值|min:获取最小值|avg:获取平均值|sum:获取总和
     */
    final public function stat($where = [], $type = 'count', $field = 'id')
    {
        if ($this->is_cache) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        $result = Db::name($this->table)->where($where)->$type($field);
        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $result);
        }

        return $result;
    }

    /**
     * SQL查询
     * @param string $sql
     * @return mixed
     */
    final public function query($sql = '')
    {
        return Db::query($sql);
    }

    /**
     * 返回总数
     * @param unknown $where
     */
    final public function getCount($where = [], $field = '*', $alias = 'a', $join = null, $group = null)
    {
        if ($this->is_cache && empty($join)) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }
        if (empty($join)) {
            if (empty($group)) {
                $result = Db::name($this->table)->where($where)->count($field);
            } else {
                $result = Db::name($this->table)->group($group)->where($where)->count($field);
            }
        } else {
            $db_obj = Db::name($this->table)->alias($alias);
            $db_obj = $this->parseJoin($db_obj, $join);
            if (empty($group)) {
                $result = $db_obj->where($where)->count($field);
            } else {
                $result = $db_obj->group($group)->where($where)->count($field);
            }
        }
        if ($this->is_cache && empty($join)) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $result);
        }

        return $result;
    }

    /**
     * 返回总数
     * @param unknown $where
     */
    final public function getSum($where = [], $field = '', $alias = 'a', $join = null)
    {
        if ($this->is_cache && empty($join)) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        if (empty($join)) {
            $result = Db::name($this->table)->where($where)->sum($field);
        } else {
            $db_obj = Db::name($this->table)->alias($alias);
            $db_obj = $this->parseJoin($db_obj, $join);
            $result = $db_obj->where($where)->sum($field);
        }
        if ($this->is_cache && empty($join)) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $result);
        }

        return $result;
    }

    /**
     * SQL执行,注意只能处理查询问题，如果执行修改需要手动删除缓存
     */
    final public function execute($sql = '')
    {
        return Db::execute($sql);
    }

    /**
     * 查询第一条数据
     * @param array $condition
     */
    final function getFirstData($condition, $field = '*', $order = "")
    {
        if ($this->is_cache) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        $data = Db::name($this->table)->where($condition)->order($order)->field($field)->find();
        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $data);
        }

        return $data;
    }

    /**
     * 查询第一条数据
     * @param array $condition
     */
    final function getFirstDataView($condition, $field = '*', $order = "", $alias = 'a', $join = [], $group = null)
    {
        if ($this->is_cache && empty($join)) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }

        self::$query_obj = Db::name($this->table)->alias($alias)->where($condition)->order($order)->field($field);
        if (!empty($join)) {
            $db_obj = self::$query_obj;
            self::$query_obj = $this->parseJoin($db_obj, $join);
        }

        if (!empty($group)) {
            self::$query_obj = self::$query_obj->group($group);
        }
        $data = self::$query_obj->find();
        if ($this->is_cache && empty($join)) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $data);
        }

        return $data;
    }


    /**
     * 验证
     * @param array $data
     * @param string $scene_name
     * @return array[$code, $error]
     */
    public function fieldValidate($data, $scene_name = '')
    {
        $validate = new Validate($this->rule, $this->message);

        if (empty($scene_name)) {
            $validate_result = $validate->batch(false)->check($data);
        } else {
            $validate->scene($this->scene);
            $validate_result = $validate->scene($scene_name)->batch(false)->check($data);
        }

        return $validate_result ? [ true, '' ] : [ false, $validate->getError() ];
    }

    /**
     * 事物开启
     */
    final public function startTrans()
    {

        return Db::startTrans();
    }

    /**
     * 事物提交
     */
    final public function commit()
    {

        return Db::commit();
    }

    /**
     * 事物回滚
     */
    final public function rollback()
    {
        Cache::clear();
        return Db::rollback();
    }

    /**
     * 获取错误信息
     */
    final public function getError()
    {
        return $this->error;
    }

    /**
     * 自增数据
     * @param array $where
     * @param $field
     * @param int $num
     * @return int
     * @throws \think\db\exception\DbException
     */
    final public function setInc($where, $field, $num = 1)
    {

        $res = Db::name($this->table)->where($where)->inc($field, $num)->update();

        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->clear();
        }
        return $res;
    }

    /**
     * 自减数据
     * @param $where
     * @param $field
     * @param int $num
     * @return int
     * @throws \think\db\exception\DbException
     */
    final public function setDec($where, $field, $num = 1)
    {

        $res = Db::name($this->table)->where($where)->dec($field, $num)->update();

        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->clear();
        }

        return $res;

    }

    /**
     * 获取最大值
     * @param array $where
     * @param $field
     * @return mixed
     */
    final public function getMax($where, $field)
    {
        if ($this->is_cache) {
            $cache_name = $this->table . '_' . __FUNCTION__ . '_' . serialize(func_get_args());
            $cache = Cache::get($cache_name);
            if (!empty($cache)) {
                return $cache;
            }
        }


        $data = Db::name($this->table)->where($where)->max($field);
        if ($this->is_cache) {
            Cache::tag("cache_table" . $this->table)->set($cache_name, $data);
        }

        return $data;
    }

    /**
     * 获取分页列表数据 只是单纯的实现部分功能 其他使用还是用pageList吧
     * @param unknown $where
     * @param string $field
     * @param string $order
     * @param number $page
     * @param string $list_rows
     * @param string $alias
     * @param unknown $join
     * @param string $group
     * @param string $limit
     */
    final public function Lists($condition = [], $field = true, $order = '', $page = 1, $list_rows = PAGE_LIST_ROWS, $alias = 'a', $join = [], $group = null, $limit = null)
    {
        self::$query_obj = Db::name($this->table)->alias($alias)->where($condition);
        $count_obj = Db::name($this->table)->alias($alias)->where($condition);
        if (!empty($join)) {
            $db_obj = self::$query_obj;
            self::$query_obj = $this->parseJoin($db_obj, $join);
            $count_obj = $this->parseJoin($count_obj, $join);
        }

        if (!empty($group)) {
            self::$query_obj = self::$query_obj->group($group);
            $count_obj = $count_obj->group($group);
        }

        if (!empty($limit)) {
            self::$query_obj = self::$query_obj->limit($limit);
        }

        $count = $count_obj->count();
        if ($list_rows == 0) {
            //查询全部
            $result_data = self::$query_obj->field($field)->order($order)->limit($count)->page($page)->select()->toArray();
            $result[ 'page_count' ] = 1;
        } else {
            $result_data = self::$query_obj->field($field)->order($order)->limit($list_rows)->page($page)->select()->toArray();
            $result[ 'page_count' ] = ceil($count / $list_rows);
        }
        $result[ 'count' ] = $count;
        $result[ 'list' ] = $result_data;

        self::$query_obj->removeOption();
        return $result;
    }

    /**
     * 不读取缓存--获取单条数据
     * @param array $where
     * @param string $field
     * @param string $join
     * @param string $data
     * @return mixed
     */
    final public function getInfoTo($where = [], $field = true, $alias = 'a', $join = null, $data = null)
    {
        if (empty($join)) {
            $result = Db::name($this->table)->where($where)->field($field)->find($data);
        } else {
            $db_obj = Db::name($this->table)->alias($alias);
            $db_obj = $this->parseJoin($db_obj, $join);
            $result = $db_obj->where($where)->field($field)->find($data);
        }

        return $result;
    }

    public function setIsCache($is_cache = 1){
        $this->is_cache = $is_cache;
        return $this;
    }
}
