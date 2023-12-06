<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\api\controller;

use app\model\member\Member as MemberModel;
use app\model\shop\Shop;
use app\model\system\Api;
use extend\RSA;
use think\facade\Cache;
use addon\store\model\Config as StoreConfig;
use app\model\store\Store;

class BaseApi
{
    public $lang;

    public $params;

    public $token;

    protected $member_id;

    protected $site_id;

    protected $app_module = "shop";

    public $app_type;

    private $refresh_token;

    /**
     * 所选门店id
     * @var
     */
    protected $store_id = 0; // 门店id

    /**
     * 门店数据
     * @var
     */
    protected $store_data = [
        'config' => [
            'store_business' => 'shop'
        ]
    ];

    public function __construct()
    {
        if ($_SERVER[ 'REQUEST_METHOD' ] == 'OPTIONS') {
            exit;
        }
        //获取参数
        $this->site_id = request()->siteid();
        $this->params = input();
        $this->params[ 'site_id' ] = $this->site_id;
        $shop_model = new Shop();
        $shop_status = $shop_model->getShopStatus($this->site_id, 'shop');

        //默认APP类型处理
        if (!isset($this->params[ 'app_type' ])) $this->params[ 'app_type' ] = 'h5';

        if ($this->params[ 'app_type' ] == 'pc') {
            if (!$shop_status[ 'data' ][ 'value' ][ 'shop_pc_status' ]) exit(json_encode($this->error([], 'SITE_CLOSE')));
        } else if ($this->params[ 'app_type' ] == 'weapp') {
            if (!$shop_status[ 'data' ][ 'value' ][ 'shop_weapp_status' ]) exit(json_encode($this->error([], 'SITE_CLOSE')));
        } else if ($this->params[ 'app_type' ] == 'h5') {
            if (!$shop_status[ 'data' ][ 'value' ][ 'shop_h5_status' ]) exit(json_encode($this->error([], 'SITE_CLOSE')));
        }

        if (isset($this->params[ 'encrypt' ]) && !empty($this->params[ 'encrypt' ])) {
            $this->decryptParams();
        }

        $this->store_id = $this->params[ 'store_id' ] ?? 0;
    }

    /**
     * 初始化门店数据
     */
    protected function initStoreData()
    {
        $store_model = new Store();
        $default_store = $store_model->getDefaultStore($this->site_id)[ 'data' ];
        $this->store_data[ 'default_store' ] = $default_store ? $default_store[ 'store_id' ] : 0;
        $this->store_id = $this->store_id ? $this->store_id : $this->store_data[ 'default_store' ];

        if (addon_is_exit('store', $this->site_id)) {
            $this->store_data[ 'config' ] = ( new StoreConfig() )->getStoreBusinessConfig($this->site_id)[ 'data' ][ 'value' ];

            if ($this->store_id == $this->store_data[ 'default_store' ]) $this->store_data[ 'store_info' ] = $default_store;
            else $this->store_data[ 'store_info' ] = $store_model->getStoreInfo([ [ 'site_id', '=', $this->site_id ], [ 'store_id', '=', $this->store_id ] ])[ 'data' ];

            if (empty($this->store_data[ 'store_info' ]) || $this->store_data[ 'store_info' ][ 'status' ] == 0) {
                exit(json_encode($this->error([], 'STORE_CLOSE')));
            }
        }
    }

    /**
     * api请求参数解密
     */
    private function decryptParams()
    {
        $api_model = new Api();
        $config = $api_model->getApiConfig();
        $config = $config[ 'data' ];

        if ($config[ 'is_use' ] && !empty($config[ 'value' ])) {
            $decrypted = RSA::decrypt(urldecode($this->params[ 'encrypt' ]), $config[ 'value' ][ 'private_key' ], $config[ 'value' ][ 'public_key' ]);
            if ($decrypted[ 'code' ] >= 0) {
                $this->params = json_decode($decrypted[ 'data' ], true);
                $this->params[ 'site_id' ] = $this->site_id;
            }
        }
    }

    /**
     * 检测token(使用私钥检测)
     */
    protected function checkToken() : array
    {
        if (empty($this->params[ 'token' ])) return $this->error('', 'TOKEN_NOT_EXIST');

        $key = 'site' . $this->site_id;
        $api_model = new Api();
        $api_config = $api_model->getApiConfig()[ 'data' ];
        if ($api_config[ 'is_use' ] && isset($api_config[ 'value' ][ 'private_key' ]) && !empty($api_config[ 'value' ][ 'private_key' ])) {
            $key = $api_config[ 'value' ][ 'private_key' ] . $key;
        }
        $decrypt = decrypt($this->params[ 'token' ], $key);
        if (empty($decrypt)) return $this->error('', 'TOKEN_ERROR');

        $data = json_decode($decrypt, true);

        if (!isset($data[ 'member_id' ]) || empty($data[ 'member_id' ])) return $this->error('', 'TOKEN_ERROR');

        $member_model = new MemberModel();
        $check_member_id = $member_model->checkMemberByMemberId($data[ 'member_id' ], $this->site_id);
        if (!$check_member_id) {
            return $this->error('', 'TOKEN_EXPIRE');
        }

        if ($data[ 'expire_time' ] < time()) {
            if ($data[ 'expire_time' ] != 0) {
                return $this->error('', 'TOKEN_EXPIRE');
            }
        } else if (( $data[ 'expire_time' ] - time() ) < 300 && !Cache::get('member_token' . $data[ 'member_id' ])) {
            $this->refresh_token = $this->createToken($data[ 'member_id' ]);
            Cache::set('member_token' . $data[ 'member_id' ], $this->refresh_token, 360);
        }

        $this->member_id = $data[ 'member_id' ];

        return success(0, '', $data);
    }

    /**
     * 创建token
     * @param
     */
    protected function createToken($member_id)
    {
        $api_model = new Api();
        $config_result = $api_model->getApiConfig();
        $config = $config_result[ "data" ];
        # $expire_time 有效时间  0为永久 单位s
        if ($config) {
            $expire_time = round($config[ 'value' ][ 'long_time' ] * 3600);
        } else {
            $expire_time = 0;
        }

        $key = 'site' . $this->site_id;
        $api_model = new Api();
        $api_config = $api_model->getApiConfig()[ 'data' ];
        if ($api_config[ 'is_use' ] && isset($api_config[ 'value' ][ 'private_key' ]) && !empty($api_config[ 'value' ][ 'private_key' ])) {
            $key = $api_config[ 'value' ][ 'private_key' ] . $key;
        }
        $data = [
            'member_id' => $member_id,
            'create_time' => time(),
            'expire_time' => empty($expire_time) ? 0 : time() + $expire_time
        ];
        $token = encrypt(json_encode($data), $key);
        return $token;
    }

    /**
     * 返回数据
     * @param $data
     * @return false|string
     */
    public function response($data)
    {
        $data[ 'timestamp' ] = time();
        if (!empty($this->refresh_token)) $data[ 'refreshtoken' ] = $this->refresh_token;
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
     * @return Ambigous <multitype:, unknown>
     */
    private function getLang()
    {
        $default_lang = config("lang.default_lang");
        $addon = request()->addon();
        $addon = isset($addon) ? $addon : '';
        $cache_common = Cache::get("lang_app/api/lang/" . $default_lang);

        if (empty($cache_common)) {
            $cache_common = include 'app/api/lang/' . $default_lang . '.php';
            Cache::tag("lang")->set("lang_app/api/lang/" . $default_lang, $cache_common);
        }

        if (!empty($addon)) {
            try {
                $addon_cache_common = include 'addon/' . $addon . '/api/lang/' . $default_lang . '.php';
                if (!empty($addon_cache_common)) {
                    $cache_common = array_merge($cache_common, $addon_cache_common);
                    Cache::tag("lang")->set("lang_app/api/lang/" . $addon . '_' . $default_lang, $addon_cache_common);
                }
            } catch (\Exception $e) {
            }
        }

        return $cache_common;

    }

    /**
     * 获取code编码
     * @return Ambigous <multitype:, unknown>
     */
    private function getCode()
    {
        $addon = request()->addon();
        $addon = isset($addon) ? $addon : '';
        $cache_common = Cache::get("lang_code_app/api/lang");

        if (!empty($addon)) {
            $addon_cache_common = Cache::get("lang_code_app/api/lang/" . $addon);
            if (!empty($addon_cache_common)) {
                $cache_common = array_merge($cache_common, $addon_cache_common);
            }
        }

        if (empty($cache_common)) {
            $cache_common = include 'app/api/lang/code.php';
            Cache::tag("lang_code")->set("lang_code_app/api/lang", $cache_common);

            if (!empty($addon)) {
                try {
                    $addon_cache_common = include 'addon/' . $addon . '/api/lang/code.php';
                    if (!empty($addon_cache_common)) {
                        Cache::tag("lang_code")->set("lang_code_app/api/lang/" . $addon, $addon_cache_common);
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
     * @param array $data 验证数据
     * @param $validate   验证类
     * @param $scene   验证场景
     */
    public function validate(array $data, $validate, $scene = '')
    {
        try {
            $class = new $validate;
            if (!empty($scene)) {
                $res = $class->scene($scene)->check($data);
            } else {
                $res = $class->check($data);
            }
            if (!$res) {
                return error(-1, $class->getError());
            } else
                return success(1);

        } catch (ValidateException $e) {
            return error(-1, $e->getError());
        } catch (\Exception $e) {
            return error(-1, $e->getMessage());
        }

    }
}