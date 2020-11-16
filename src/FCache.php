<?php

/**
 * Class FCache
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2010-11-12 01:21:07
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: FCache.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
class FCache {

    const CACHE_TYPE_NULL = 0;
    const CACHE_TYPE_REDIS = 1;
    const CACHE_TYPE_MEMCACHE = 2;
    const CACHE_TYPE_FILE = 3;
    private $_cache_type = null;
    private $_cache_key = '';
    /**
     * @var Memcache
     */
    private $_memcache_conn = null;
    /**
     * @var FRedis
     */
    private $_redis_conn;

    /**
     * 设置缓存
     * @param $cache_key
     * @param $cache_content
     * @param int $cache_time 如果用的是memcache，时间最长为30天
     * @param bool $force
     */
    public static function set($cache_key, $cache_content, $cache_time = 7200, $force = false) {

        self::getInstance()->_set($cache_key, $cache_content, $cache_time, $force);
    }

    /**
     * @param      $cache_key
     * @param      $cache_content
     * @param int $cache_time
     * @param bool $force
     */
    public function _set($cache_key, $cache_content, $cache_time = 7200, $force = false) {
        $this->_cache_key = $cache_key;

        $this->connect();


        switch ($this->_cache_type) {
            case self::CACHE_TYPE_REDIS:
                $this->redisSetCache($cache_key, $cache_content, $cache_time, $force);
                break;
            case self::CACHE_TYPE_MEMCACHE:
                $this->memcacheSetCache($cache_key, $cache_content, $cache_time, $force);
                break;
            case self::CACHE_TYPE_FILE:
                $this->fileSetCache($cache_key, $cache_content, $cache_time, $force);
                break;
        }
    }

    /**
     * 连接到指定的配置文件
     * @return bool
     */
    public function connect() {

        // 如果 getInstance 中指定了链接
        if ($this->_cache_type == self::CACHE_TYPE_REDIS) {
            $this->_redis_conn = $this->redisConnect();
            return $this->_redis_conn;
        } elseif ($this->_cache_type == self::CACHE_TYPE_MEMCACHE) {
            $this->_memcache_conn = $this->memcacheConnect();
            return $this->_memcache_conn;
        }

        if (FConfig::get('cache.redis.enable')) {
            $this->_cache_type = self::CACHE_TYPE_REDIS;
            $this->_redis_conn = $this->redisConnect();
            return $this->_redis_conn;
        } elseif (FConfig::get('cache.memcache.enable')) {
            $this->_cache_type = self::CACHE_TYPE_MEMCACHE;
            $this->_memcache_conn = $this->memcacheConnect();
            return $this->_memcache_conn;
        } else {
            $this->_cache_type = self::CACHE_TYPE_FILE;

            return null;
        }
    }

    public function redisConnect() {

        static $redis_server_count = 0;
        static $redis_conn = array();

        $redis_server_config = FConfig::get('cache.redis.server');
        if (!$redis_server_count) {
            $redis_server_count = count($redis_server_config);
        }

        if ($redis_server_count <= 1) {
            $server_id = 0;
        } else {
            $server_id = FMisc::str2int($this->_cache_key) % $redis_server_count;
        }

        if ($redis_conn[$server_id]) {
            return $redis_conn[$server_id];
        }

        // $cache_keys = array_keys($redis_server_config);

        $redis_conn[$server_id] = new FRedis();
//        $redis_conn[$server_id]->connect(
//            $redis_server_config[$cache_keys[$server_id]]['ip'],
//            $redis_server_config[$cache_keys[$server_id]]['port']);

        return $redis_conn[$server_id];
    }

    public function memcacheConnect() {

        static $memcache_server_count = 0;
        static $memcache_conn = array();

        $memcache_server_config = FConfig::get('cache.memcache.server');
        if (!$memcache_server_count) {
            $memcache_server_count = count($memcache_server_config);

            if (!$memcache_server_count) {
                throw new Exception('config/cache has no memcache server !');
            }
        }

        if ($memcache_server_count == 1) {
            $server_id = 0;
        } else {
            $server_id = FMisc::str2int($this->_cache_key) % $memcache_server_count;
        }

        if ($memcache_conn[$server_id]) {
            return $memcache_conn[$server_id];
        }

        $cache_keys = array_keys($memcache_server_config);

        $memcache_conn[$server_id] = new Memcache;


        if ($memcache_server_config[$cache_keys[$server_id]]['p_connect']) {
            $memcache_conn[$server_id]->pconnect(
                $memcache_server_config[$cache_keys[$server_id]]['ip'],
                $memcache_server_config[$cache_keys[$server_id]]['port']);
        } else {
            $memcache_conn[$server_id]->connect(
                $memcache_server_config[$cache_keys[$server_id]]['ip'],
                $memcache_server_config[$cache_keys[$server_id]]['port']);
        }


        return $memcache_conn[$server_id];
    }

    private function redisSetCache($cache_key, $cache_content, $cache_time = 7200, $force = false) {
        global $_F;
        $this->_redis_conn->set($cache_key, $cache_content, 0, 0, $cache_time);
    }

    private function memcacheSetCache($cache_key, $cache_content, $cache_time = 7200, $force = false) {
        global $_F;
        if ($cache_time > 86400 * 30) {
            $cache_time = 86400 * 30;
        }
        $this->_memcache_conn->set($cache_key, $cache_content, MEMCACHE_COMPRESSED, $cache_time);
    }

    private function fileSetCache($cache_key, $cache_content, $cache_time = 7200, $force = false) {
        global $_F;

//        $cache_key = $_F['domain'] . $cache_key;

        $save_content = json_encode(array(
            'expires_time' => time() + intval($cache_time), 'content' => $cache_content));

        $cache_file = self::getFileFCachePath($cache_key);
        file_put_contents($cache_file, $save_content);
    }

    public static function getFileFCachePath($cache_key) {

        $cache_dir = self::getCacheDir();

        $hash_file_path = FFile::getHashPath($cache_key, 3, $cache_dir, true);
        $cache_file = $hash_file_path['file_path'];

        return $cache_file;
    }

    public static function getCacheDir() {
        global $_F;

        if (!defined("F_APP_ROOT")) {
            throw new Exception("FCache Exception: F_APP_ROOT not defined.");
        }

        $cache_dir = FConfig::get('global.cache_dir');

        if ($cache_dir) {
            $cache_dir = "{$cache_dir}/" . md5($cache_dir) . '/';
        } else {
            $cache_dir = F_APP_ROOT . "data/cache/";
        }

//        if ($_F['run_in'] == 'shell') {
//            $cache_dir .= $_F['run_in'] . '/';
//        } elseif ($_F['module']) {
//            $cache_dir .= $_F['module'] . '/';
//        }

        return $cache_dir;
    }

    /**
     * @param int $_cache_type
     *
     * @return FCache
     */
    public static function getInstance($_cache_type = self::CACHE_TYPE_NULL) {
        static $ins = null;

        if ($ins) {
            return $ins;
        }

        $ins = new self;
        return $ins;
    }

    public static function get($cache_key) {
        global $_F;

//        $cache_key = $_F['domain'] . $cache_key;

        return self::getInstance()->_get($cache_key);
    }

    /**
     * @param $cache_key
     *
     * @return null
     */
    public function _get($cache_key) {
        $this->_cache_key = $cache_key;

        $this->connect();

        $ret = null;
        switch ($this->_cache_type) {
            case self::CACHE_TYPE_REDIS:
                $ret = $this->redisGetCache($cache_key);
                break;
            case self::CACHE_TYPE_MEMCACHE:
                $ret = $this->memcacheGetCache($cache_key);
                break;
            case self::CACHE_TYPE_FILE:
                $ret = $this->fileGetCache($cache_key);
                break;
        }

        return $ret;
    }

    private function redisGetCache($cache_key) {
        return $this->_redis_conn->get($cache_key);
    }

    private function memcacheGetCache($cache_key) {
        global $_F;

        return $this->_memcache_conn->get($cache_key);
    }

    public function fileGetCache($cache_key) {
        $cache_file = self::getFileFCachePath($cache_key);
        if( is_file($cache_file)){
            $content = json_decode(file_get_contents($cache_file), true);

            if ($content && $content['expires_time'] >= time()) {
                return $content['content'];
            } else {
                return null;
            }
        }else{
            return null;
        }
    }

    public static function delete($cache_key) {
        self::getInstance()->_remove($cache_key);
    }

    private function _remove($cache_key) {
        $this->_cache_key = $cache_key;

        $this->connect();

        $ret = null;
        switch ($this->_cache_type) {
            case self::CACHE_TYPE_REDIS:
                $this->redisRemoveCache($cache_key);
                break;
            case self::CACHE_TYPE_MEMCACHE:
                $this->memcacheRemoveCache($cache_key);
                break;
            case self::CACHE_TYPE_FILE:
                $this->fileRemoveCache($cache_key);
                break;
        }

        return $ret;
    }

    private function redisRemoveCache($cache_key) {
        $this->_redis_conn->delete($cache_key);
    }

    private function memcacheRemoveCache($cache_key) {
        $this->_memcache_conn->delete($cache_key);
    }

    private function fileRemoveCache($cache_key) {
        $cache_file = self::getFileFCachePath($cache_key);
        unlink($cache_file);
    }

    public static function flush() {
        global $_F;

        self::getInstance()->_flush();
    }

    /**
     * 清空 cache
     */
    public function _flush() {
        global $_F;

        $this->connect();

        if (FConfig::get('global.memcache.enable')) {
            $_F['memcache']->flush();
            return;
        }

        // 清除文件缓存
        $cache_dir = dirname(self::getCacheDir() . 'file');
        if (is_dir($cache_dir)) {
            $cache_dir_new = $cache_dir . '.bak_' . $_F['http_host'] . '_' . date('Y-m-d_H_i_s') . rand(1000, 9999);
            rename($cache_dir, $cache_dir_new);
            FFile::rmDir($cache_dir_new . '/');
        }
    }

    public function fileConnect() {

    }

}