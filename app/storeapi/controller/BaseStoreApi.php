<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\storeapi\controller;

use addon\cashier\model\Menu;
use app\exception\ApiException;
use app\model\shop\Shop;
use app\model\system\Api;
use app\model\system\Group as GroupModel;
use app\model\system\Site;
use app\model\system\User as UserModel;
use extend\RSA;
use think\facade\Cache;

class BaseStoreApi
{
    public $lang;

    public $params;

    protected $user_info;

    protected $uid;

    protected $site_id;

    protected $store_id;

    protected $shop_info;

    public $app_type;

    protected $app_module = 'store';

    protected $api_config;

    protected $addon = '';

    protected $store_list;

    public function __construct()
    {
        if ($_SERVER[ 'REQUEST_METHOD' ] == 'OPTIONS') {
            exit;
        }
        $this->addon = request()->addon() ? request()->addon() : '';
        //获取参数
        $this->params = input();
        $this->getApiConfig();
        $this->site_id = request()->siteid();

        // 验证token
        $token = $this->checkToken();
        if ($token['code'] != 0) exit($this->response($token));

        if (empty($this->user_info['user_group_list'])) exit($this->response($this->error([], 'NO_PERMISSION')));
        $store_list = array_column($this->user_info['user_group_list'], null, 'store_id');

        if (isset($this->params['store_id']) && !empty($this->params['store_id'])) {
            $this->store_id = $this->params['store_id'];
        } else {
            $this->store_id = $this->user_info['user_group_list'][0]['store_id'];
        }
        if (!isset($store_list[ $this->store_id ])) exit($this->response($this->error([], 'NO_PERMISSION')));

        $this->store_list = $store_list;

        //判断权限
        if (!$this->checkAuth()) {
            exit($this->response($this->error([], 'NO_PERMISSION')));
        }
    }

    /**
     * 获取api配置
     */
    protected function getApiConfig()
    {
        $api_model = new Api();
        $config_result = $api_model->getApiConfig();
        $this->api_config = $config_result[ "data" ];
    }

    /**
     * 检测token(使用私钥检测)
     */
    protected function checkToken() : array
    {
        if (empty($this->params[ 'token' ])) {
            return $this->error('', 'TOKEN_NOT_EXIST');
        }

        if ($this->api_config[ 'is_use' ] && isset($this->api_config[ 'value' ][ 'private_key' ])
            && !empty($this->api_config[ 'value' ][ 'private_key' ])) {
            $decrypt = decrypt($this->params[ 'token' ], $this->api_config[ 'value' ][ 'private_key' ]);
        } else {
            $decrypt = decrypt($this->params[ 'token' ]);
        }
        if (empty($decrypt)) {
            return $this->error('', 'TOKEN_ERROR');
        }
        $data = json_decode($decrypt, true);
        if (empty($data)) {
            return $this->error('', 'TOKEN_ERROR');
        }
        if (!empty($data[ 'expire_time' ]) && $data[ 'expire_time' ] > time()) {
            return $this->error('', 'TOKEN_EXPIRE');
        }
        $this->user_info = $data[ 'user_info' ];
        $this->app_module = $this->user_info['app_module'];

        $this->uid = $data[ 'user_info' ][ 'uid' ];

        $this->getShopInfo();

        return success(0, '', $data);
    }

    /**
     * 检测权限
     * @return bool
     */
    protected function checkAuth(){
        if ($this->user_info['is_admin']) return true;

        $url = implode('/', array_filter([ request()->addon(), request()->module(), request()->controller(), request()->action() ]));
        $name = (new Menu())->getMenuValue([ ['url', '=', $url], ['type', '=', 'api'] ], 'name')['data'];
        if (empty($name)) return true;

        $menu_array = $this->store_list[ $this->store_id ]['menu_array'] ?? '';
        if (empty($menu_array)) return true;
        if (!in_array($name, explode(',', $menu_array))) return false;

        return true;
    }

    /**
     * 创建token
     * @param $user_info
     * @param int $expire_time 有效时间  0为永久 单位s
     * @return string
     */
    protected function createToken($user_info, $expire_time = 0)
    {
        $data = [
            'user_info' => $user_info,
            'expire_time' => empty($expire_time) ? 0 : time() + $expire_time
        ];
        if ($this->api_config[ 'is_use' ] && isset($this->api_config[ 'value' ][ 'private_key' ])
            && !empty($this->api_config[ 'value' ][ 'private_key' ])) {
            $token = encrypt(json_encode($data), $this->api_config[ 'value' ][ 'private_key' ]);
        } else {
            $token = encrypt(json_encode($data));
        }
        return $token;
    }

    public function getShopInfo()
    {
        //获取店铺信息
        $condition = array (
            [ "site_id", "=", $this->site_id ]
        );
        $shop_info_result = (new Shop())->getShopInfo($condition);
        $site_info = (new Site())->getSiteInfo($condition);

        $this->shop_info = array_merge($shop_info_result['data'], $site_info['data']);
    }

    /**
     * 返回数据
     * @param $data
     * @return false|string
     */
    public function response($data)
    {
        $data[ 'timestamp' ] = time();
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 操作成功返回值函数
     * @param string $data
     * @param string $code_var
     * @return array
     */
    public function success($data = '', $code_var = 'SUCCESS')
    {
        $lang_array = $this->getLang();
        $code_array = $this->getCode();
        $lang_var = isset($lang_array[ $code_var ]) ? $lang_array[ $code_var ] : $code_var;
        $code_var = isset($code_array[ $code_var ]) ? $code_array[ $code_var ] : $code_array[ 'SUCCESS' ];
        return success($code_var, $lang_var, $data);
    }

    /**
     * 操作失败返回值函数
     * @param string $data
     * @param string $code_var
     * @return array
     */
    public function error($data = '', $code_var = 'ERROR')
    {
        $lang_array = $this->getLang();
        $code_array = $this->getCode();
        $lang_var = isset($lang_array[ $code_var ]) ? $lang_array[ $code_var ] : $code_var;
        $code_var = isset($code_array[ $code_var ]) ? $code_array[ $code_var ] : $code_array[ 'ERROR' ];
        return error($code_var, $lang_var, $data);
    }

    /**
     * 获取语言包数组
     * @return array|mixed
     */
    private function getLang()
    {
        $default_lang = config("lang.default_lang");
        $addon = request()->addon();
        $addon = isset($addon) ? $addon : '';
        $cache_common = Cache::get("lang_app/storeapi/lang/" . $default_lang);

        if (!empty($addon)) {
            $addon_cache_common = Cache::get("lang_app/storeapi/lang/" . $addon . '_' . $default_lang);
            if (!empty($addon_cache_common)) {
                $cache_common = array_merge($cache_common, $addon_cache_common);
            }
        }

        if (empty($cache_common)) {
            $cache_common = include 'app/storeapi/lang/' . $default_lang . '.php';
            Cache::tag("lang")->set("lang_app/storeapi/lang/" . $default_lang, $cache_common);
            if (!empty($addon)) {
                try {
                    $addon_cache_common = include 'addon/' . $addon . '/storeapi/lang/' . $default_lang . '.php';
                    if (!empty($addon_cache_common)) {
                        $cache_common = array_merge($cache_common, $addon_cache_common);
                        Cache::tag("lang")->set(
                            "lang_app/storeapi/lang/" . $addon . '_' . $default_lang,
                            $addon_cache_common
                        );
                    }
                } catch (\Exception $e) {
                }
            }
        }
        $lang_path = isset($this->lang) ? $this->lang : '';
        if (!empty($lang_path)) {
            $cache_path = Cache::get("lang_" . $lang_path . "/" . $default_lang);
            if (empty($cache_path)) {
                $cache_path = include $lang_path . "/" . $default_lang . '.php';
                Cache::tag("lang")->set("lang_" . $lang_path . "/" . $default_lang, $cache_path);
            }
            $lang = array_merge($cache_common, $cache_path);
        } else {
            $lang = $cache_common;
        }
        return $lang;
    }

    /**
     * 获取code编码
     * @return array|mixed
     */
    private function getCode()
    {
        $addon = request()->addon();
        $addon = isset($addon) ? $addon : '';
        $cache_common = Cache::get("lang_code_app/storeapi/lang");

        if (!empty($addon)) {
            $addon_cache_common = Cache::get("lang_code_app/storeapi/lang/" . $addon);
            if (!empty($addon_cache_common)) {
                $cache_common = array_merge($cache_common, $addon_cache_common);
            }
        }

        if (empty($cache_common)) {
            $cache_common = include 'app/storeapi/lang/code.php';
            Cache::tag("lang_code")->set("lang_code_app/storeapi/lang", $cache_common);

            if (!empty($addon)) {
                try {
                    $addon_cache_common = include 'addon/' . $addon . '/storeapi/lang/code.php';
                    if (!empty($addon_cache_common)) {
                        Cache::tag("lang_code")->set("lang_code_app/storeapi/lang/" . $addon, $addon_cache_common);
                        $cache_common = array_merge($cache_common, $addon_cache_common);
                    }
                } catch (\Exception $e) {
                }
            }
        }
        $lang_path = isset($this->lang) ? $this->lang : '';
        if (!empty($lang_path)) {
            $cache_path = Cache::get("lang_code_" . $lang_path);
            if (empty($cache_path)) {
                $cache_path = include $lang_path . '/code.php';
                Cache::tag("lang")->set("lang_code_" . $lang_path, $cache_path);
            }
            $lang = array_merge($cache_common, $cache_path);
        } else {
            $lang = $cache_common;
        }
        return $lang;
    }

    /**
     * 添加日志
     * @param string $action_name
     * @param array $data
     */
    protected function addLog($action_name, $data = [])
    {
        $user = new UserModel();
        $user->addUserLog($this->uid, $this->user_info[ 'username' ], $this->site_id, $action_name, $data);
    }
}
