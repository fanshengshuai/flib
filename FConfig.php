<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-02 01:21:51
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: FConfig.php 192 2012-08-11 08:31:08Z fanshengshuai $
 */
class FConfig {

    private $_values = array();
    protected static $_instance = null;
    protected static $_loaded = array();

    /**
     * &getInstance
     * 获取一个FConfig类的实例
     *
     * @return object
     */
    public static function &getInstance() {

        if (!self::$_instance) {
            self::$_instance = new FConfig();
        }

        return self::$_instance;
    }

    /**
     * get
     * 获取一个配置的值
     *
     * @param string $key 配置的key
     * @param mixed $defaultValue key不存在时返回默认值
     *
     * @return mixed
     */
    public static function get($key, $defaultValue = null) {

        $config =& FConfig::getInstance();
        $value = $config->_GET($key);
        if (!$value) {
            return $defaultValue;
        }

        return $value;
    }

    /**
     * exists
     * 检查一个配置是否存在
     *
     * @param  string $key 配置的key
     *
     * @return boolean
     */
    public static function exists($key) {

        $config =& FConfig::getInstance();
        return $config->_exists($key);
    }

    /**
     * set
     * 设置值
     *
     * @param  string $key 键
     * @param  mixed $value 值
     *
     * @return void
     */
    public static function set($key, $value) {

        $config =& FConfig::getInstance();
        $config->_values[$key] = $value;
    }

    /**
     * _GET
     * 获取一个配置的值
     *
     * @param string $key 配置的key
     *
     * @return mixed
     */
    protected function _GET($key) {

        if ($this->_values[$key]) {
            return $this->_values[$key];
        }

        $this->_loadKey($key);
        $value = $this->_match($key);
        FConfig::set($key, $value);

        // 对于全局配置
        if ($this->_values['global.' . $key]) {
            return $this->_values['global.' . $key];
        }


        return $value;
    }

    /**
     * _match
     * 匹配
     *
     * @param  string $key
     *
     * @return mixed
     */
    protected function _match($key) {

        if ($this->_values[$key]) {
            return $this->_values[$key];
        }

        $parts = explode('.', $key);
        if (!$parts) {
            return false;
        }

        $leave = array();
        for ($i = 0; $i < count($parts); $i++) {
            $part = array_pop($parts);
            array_unshift($leave, $part);

            $pattern = join('.', $parts);
            $array = $this->_values[$pattern];
            if ($array) {
                break;
            }
        }

        if (!$array || !$leave) {
            return false;
        }

        if (!is_array($array)) {
            return null;
        }

        $value = $array;
        foreach ($leave as $part) {

            if ($value && is_array($value)) {
                $value = $value[$part];
            } else {
                $value = null;
                break;
            }
        }

        return $value;
    }

    /**
     * _exists
     * 检查一个配置是否存在
     *
     * @param  string $key 配置的key
     *
     * @return boolean
     */
    protected function _exists($key) {

        $value = self::get($key);
        return $value !== null;
    }

    /**
     * _loadKey
     * 根据key加载配置
     *
     * @param string $key
     *
     * @return void
     */
    protected function _loadKey($key) {

        $path = explode('.', $key);

        if (is_file(APP_ROOT . "config/{$path[0]}.php")) {
            require APP_ROOT . "config/{$path[0]}.php";

            $_v = $_config[$path[0]];
            foreach ($path as $_path_key => $_subkey) {

                if ($_path_key == 0) {
                    continue;
                }

                if (isset($_v[$_subkey])) {
                    $_v = $_v[$_subkey];
                } else {
                    $_v = '';
                }

                //echo $_v;
            }

            FConfig::set($key, $_v);
        } else {
            return false;
        }

        //$this->_load($rootkey, false, $file);
    }

    /**
     * _load
     * 载入配置
     *
     * @param  string $rootkey 根key
     * @param  mixed $conf 配置
     * @param  string $file 定义配置文件
     * throw new FConfig_Exception
     *
     * @return void
     */
    protected function _load($rootkey, $conf = false, $file = null) {

        if ($conf === false) {
            if (in_array($file, self::$_loaded)) {
                return;
            } else {
                array_push(self::$_loaded, $file);
            }

            if (!is_file($file)) {
                $file = FLIB_ROOT . '/config/global.php';
            }
            require_once($file);
        }

        if (is_array($conf)) {
            foreach ($conf as $key => $value) {
                FConfig::set($rootkey . '.' . $key, $value);
            }
        }
    }

}
